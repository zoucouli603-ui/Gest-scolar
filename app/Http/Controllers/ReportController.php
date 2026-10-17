<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $periodType = $request->input('period_type', 'monthly');
        $periodFormat = match ($periodType) {
            'daily' => 'Y-m-d',
            'yearly' => 'Y',
            default => 'Y-m',
        };

        $validated = $request->validate([
            'school_year_id' => [
                'nullable',
                'integer',
                Rule::exists('school_years', 'id')->where('school_id', $request->user()->school_id),
            ],
            'cycle' => ['nullable', Rule::in($request->user()->school->cycles ?? ClassRoom::CYCLES)],
            'period_type' => ['nullable', Rule::in(['daily', 'monthly', 'yearly'])],
            'period' => ['nullable', 'date_format:'.$periodFormat],
        ]);

        $periodType = $validated['period_type'] ?? 'monthly';
        $period = $validated['period'] ?? match ($periodType) {
            'daily' => now()->format('Y-m-d'),
            'yearly' => now()->format('Y'),
            default => now()->format('Y-m'),
        };
        $periodStart = match ($periodType) {
            'daily' => CarbonImmutable::createFromFormat('!Y-m-d', $period),
            'yearly' => CarbonImmutable::createFromFormat('!Y', $period),
            default => CarbonImmutable::createFromFormat('!Y-m', $period),
        };
        if (!$periodStart) {
            throw new RuntimeException('La période du rapport n’a pas pu être interprétée.');
        }
        $periodEnd = match ($periodType) {
            'daily' => $periodStart->endOfDay(),
            'yearly' => $periodStart->endOfYear(),
            default => $periodStart->endOfMonth(),
        };
        $schoolYears = SchoolYear::orderByDesc('id')->get();
        $currentYear = $schoolYears->firstWhere('is_current', true);
        $schoolYearId = $validated['school_year_id'] ?? $currentYear?->id ?? $schoolYears->first()?->id;
        $schoolYear = $schoolYearId ? $schoolYears->firstWhere('id', (int) $schoolYearId) : null;
        $selectedCycle = $validated['cycle'] ?? null;

        $classes = $schoolYear
            ? ClassRoom::where('school_year_id', $schoolYear->id)->orderBy('cycle')->orderBy('name')->get()
            : collect();
        $classesByName = $classes->groupBy(fn (ClassRoom $classRoom) => $this->normalizeClassName($classRoom->name));
        $students = $schoolYear
            ? Student::where('school_year_id', $schoolYear->id)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
            : collect();

        $resolveClass = function (?Student $student, ?int $paymentClassId = null) use ($classes, $classesByName): ?ClassRoom {
            $classId = $paymentClassId ?? $student?->class_room_id;
            $classRoom = $classId ? $classes->firstWhere('id', $classId) : null;
            if ($classRoom) {
                return $classRoom;
            }

            $matchingClasses = $student
                ? $classesByName->get($this->normalizeClassName($student->class_name), collect())
                : collect();

            return $matchingClasses->count() === 1 ? $matchingClasses->first() : null;
        };

        $visibleClasses = $selectedCycle
            ? $classes->where('cycle', $selectedCycle)->values()
            : $classes->values();
        $visibleClassIds = $visibleClasses->pluck('id')->all();
        $studentsWithClass = $students->map(fn (Student $student) => [
            'student' => $student,
            'class' => $resolveClass($student),
        ]);
        $visibleStudentRows = $studentsWithClass->filter(fn (array $row) =>
            !$selectedCycle || in_array($row['class']?->id, $visibleClassIds, true)
        )->values();

        $periodPayments = $schoolYear
            ? Payment::with(['student.classRoom', 'classRoom'])
                ->where('school_year_id', $schoolYear->id)
                ->whereBetween('payment_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->orderBy('payment_date')
                ->orderBy('id')
                ->get()
            : collect();
        $payments = $periodPayments->filter(function (Payment $payment) use ($resolveClass, $selectedCycle) {
            if (!$selectedCycle) {
                return true;
            }

            return $resolveClass($payment->student, $payment->class_room_id ? (int) $payment->class_room_id : null)?->cycle === $selectedCycle;
        })->values();
        $totalCollected = (float) $payments->sum(fn (Payment $payment) => (float) $payment->amount);
        $allPeriodCollected = (float) $periodPayments->sum(fn (Payment $payment) => (float) $payment->amount);
        $paymentTypes = $payments->groupBy('fee_type')->map(fn ($items) => [
            'count' => $items->count(),
            'total' => (float) $items->sum(fn (Payment $payment) => (float) $payment->amount),
        ]);
        $paidStudentIds = $payments->pluck('student_id')->unique()->values();
        $expectedMonthlyFees = (float) $visibleStudentRows->sum(function (array $row) {
            return (float) ($row['class']?->monthly_fee ?? 0);
        });

        $debtorRows = collect();
        if ($periodType === 'monthly') {
            $schoolFeePaymentsByStudent = $schoolYear
                ? Payment::query()
                    ->where('school_year_id', $schoolYear->id)
                    ->where('fee_type', 'scolarite')
                    ->whereDate('fee_month', $periodStart->toDateString())
                    ->whereDate('payment_date', '<=', $periodEnd->toDateString())
                    ->get()
                    ->filter(function (Payment $payment) use ($students, $resolveClass, $selectedCycle) {
                        $student = $students->firstWhere('id', $payment->student_id);

                        return !$selectedCycle
                            || $resolveClass($student, $payment->class_room_id ? (int) $payment->class_room_id : null)?->cycle === $selectedCycle;
                    })
                : collect();
            $schoolFeePaymentsByStudent = $schoolFeePaymentsByStudent
                ->groupBy('student_id')
                ->map(fn ($items) => (float) $items->sum(fn (Payment $payment) => (float) $payment->amount));

            $debtorRows = $visibleStudentRows
                ->map(function (array $row) use ($schoolFeePaymentsByStudent) {
                    $student = $row['student'];
                    $classRoom = $row['class'];
                    $due = (float) ($classRoom?->monthly_fee ?? 0);
                    $paid = (float) $schoolFeePaymentsByStudent->get($student->id, 0);

                    return [
                        'student' => $student,
                        'class' => $classRoom,
                        'due' => $due,
                        'paid' => $paid,
                        'remaining' => max(0, $due - $paid),
                    ];
                })
                ->filter(fn (array $row) => $row['remaining'] > 0)
                ->values();
        }

        $expenses = $schoolYear
            ? Expense::with('teacher')
                ->where('school_year_id', $schoolYear->id)
                ->whereBetween('expense_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                ->orderBy('expense_date')
                ->orderBy('id')
                ->get()
            : collect();
        $salaryExpenses = $expenses->where('category', 'salaires')->values();
        $otherExpenses = $expenses->where('category', '<>', 'salaires')->values();
        $totalExpenses = (float) $expenses->sum(fn (Expense $expense) => (float) $expense->amount);
        $salaryTotal = (float) $salaryExpenses->sum(fn (Expense $expense) => (float) $expense->amount);
        $expensesByCategory = $expenses->groupBy('category')->map(fn ($items) => [
            'count' => $items->count(),
            'total' => (float) $items->sum(fn (Expense $expense) => (float) $expense->amount),
        ]);
        $salaryPayrollRows = $schoolYear
            ? Teacher::with('declaredSubjects')
                ->where('school_year_id', $schoolYear->id)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->map(fn (Teacher $teacher) => ['teacher' => $teacher])
            : collect();
        $expectedMonthlyPayroll = 0.0;

        $classStats = $visibleClasses->map(function (ClassRoom $classRoom) use ($visibleStudentRows, $periodPayments, $resolveClass) {
            $classStudents = $visibleStudentRows->filter(fn (array $row) => $row['class']?->id === $classRoom->id);
            $classPayments = $periodPayments->filter(fn (Payment $payment) =>
                $resolveClass($payment->student, $payment->class_room_id ? (int) $payment->class_room_id : null)?->id === $classRoom->id
            );

            return [
                'class' => $classRoom,
                'students_count' => $classStudents->count(),
                'expected_monthly' => (float) $classRoom->monthly_fee * $classStudents->count(),
                'collected' => (float) $classPayments->sum(fn (Payment $payment) => (float) $payment->amount),
            ];
        });

        $cycles = $request->user()->school->cycles ?? ClassRoom::CYCLES;
        $cycleStats = collect($cycles)->map(function (string $cycle) use ($classStats) {
            $cycleClasses = $classStats->filter(fn (array $row) => $row['class']->cycle === $cycle);

            return [
                'cycle' => $cycle,
                'classes_count' => $cycleClasses->count(),
                'students_count' => (int) $cycleClasses->sum('students_count'),
                'expected_monthly' => (float) $cycleClasses->sum('expected_monthly'),
                'collected' => (float) $cycleClasses->sum('collected'),
            ];
        });

        $schoolName = $request->user()->school->name;
        $paidStudentsCount = $visibleStudentRows->filter(fn (array $row) => $paidStudentIds->contains($row['student']->id))->count();

        return view('reports.index', [
            'schoolYears' => $schoolYears,
            'schoolYear' => $schoolYear,
            'schoolName' => $schoolName,
            'cycles' => $cycles,
            'selectedCycle' => $selectedCycle,
            'periodType' => $periodType,
            'period' => $period,
            'periodLabel' => match ($periodType) {
                'daily' => $periodStart->format('d/m/Y'),
                'yearly' => $periodStart->format('Y'),
                default => $periodStart->format('m/Y'),
            },
            'cycleStats' => $cycleStats,
            'classStats' => $classStats,
            'students' => $visibleStudentRows,
            'payments' => $payments,
            'paymentTypes' => $paymentTypes,
            'paidStudentsCount' => $paidStudentsCount,
            'unpaidStudentsCount' => max(0, $visibleStudentRows->count() - $paidStudentsCount),
            'expectedMonthlyFees' => $expectedMonthlyFees,
            'debtorRows' => $debtorRows,
            'expenses' => $expenses,
            'salaryExpenses' => $salaryExpenses,
            'salaryPayrollRows' => $salaryPayrollRows,
            'expectedMonthlyPayroll' => $expectedMonthlyPayroll,
            'otherExpenses' => $otherExpenses,
            'expensesByCategory' => $expensesByCategory,
            'salaryTotal' => $salaryTotal,
            'totalExpenses' => $totalExpenses,
            'totalCollected' => $totalCollected,
            'netBalance' => $allPeriodCollected - $totalExpenses,
            'allPeriodCollected' => $allPeriodCollected,
            'reportAuthor' => $request->user()->name,
            'generatedAt' => now(),
        ]);
    }

    private function normalizeClassName(?string $name): string
    {
        return mb_strtolower(trim((string) $name));
    }
}
