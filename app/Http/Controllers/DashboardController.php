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

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $school = $user->school;
        $canViewFinancialDashboard = $user->canAccess('finances');
        $primaryPhoto = $school->photos()->where('is_primary', true)->first();
        $schoolYears = SchoolYear::orderByDesc('id')->get();
        $schoolYear = $schoolYears->firstWhere('is_current', true) ?? $schoolYears->first();
        $monthStart = CarbonImmutable::now()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();
        $students = collect();
        $classes = collect();
        $teachers = collect();
        $recentPayments = collect();
        $recentExpenses = collect();
        $studentCount = 0;
        $activeStudentCount = 0;
        $teacherCount = 0;
        $classCount = 0;
        $collectedThisMonth = 0.0;
        $expensesThisMonth = 0.0;
        $monthlyOutstanding = 0.0;
        $classCounts = collect();
        $girlCount = 0;
        $boyCount = 0;
        $totalCollected = 0.0;
        $totalExpenses = 0.0;

        if ($schoolYear) {
            $students = Student::where('school_year_id', $schoolYear->id)
                ->with('classRoom')
                ->orderBy('last_name')
                ->get();
            $classes = ClassRoom::where('school_year_id', $schoolYear->id)
                ->orderBy('cycle')
                ->orderBy('name')
                ->get();
            $teachers = Teacher::where('school_year_id', $schoolYear->id)->get();
            $studentCount = $students->count();
            $activeStudentCount = $students->where('status', 'actif')->count();
            $girlCount = $students->where('gender', 'F')->count();
            $boyCount = $students->where('gender', 'M')->count();
            $teacherCount = $teachers->count();
            $classCount = $classes->count();
            if ($canViewFinancialDashboard) {
                $payments = Payment::where('school_year_id', $schoolYear->id);
                $expenses = Expense::where('school_year_id', $schoolYear->id);
                $totalCollected = (float) (clone $payments)->sum('amount');
                $totalExpenses = (float) (clone $expenses)->sum('amount');
                $collectedThisMonth = (float) (clone $payments)
                    ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->sum('amount');
                $expensesThisMonth = (float) (clone $expenses)
                    ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->sum('amount');
                $recentPayments = (clone $payments)
                    ->with('student')
                    ->latest('payment_date')
                    ->latest('id')
                    ->limit(5)
                    ->get();
                $recentExpenses = (clone $expenses)
                    ->latest('expense_date')
                    ->latest('id')
                    ->limit(5)
                    ->get();
            }

            $classesByName = $classes->groupBy(fn (ClassRoom $classRoom) => mb_strtolower(trim($classRoom->name)));
            $classCounts = $classes->map(function (ClassRoom $classRoom) use ($students, $classesByName): array {
                $studentCountForClass = $students->filter(function (Student $student) use ($classRoom, $classesByName): bool {
                    if ($student->class_room_id) {
                        return (int) $student->class_room_id === $classRoom->id;
                    }

                    $matchingClasses = $classesByName->get(mb_strtolower(trim($student->class_name)), collect());

                    return $matchingClasses->count() === 1 && $matchingClasses->first()->id === $classRoom->id;
                })->count();

                return [
                    'class' => $classRoom,
                    'students' => $studentCountForClass,
                ];
            });

            $classById = $classes->keyBy('id');
            if ($canViewFinancialDashboard) {
                $paymentsByStudent = Payment::where('school_year_id', $schoolYear->id)
                    ->where('fee_type', 'scolarite')
                    ->whereDate('fee_month', $monthStart->toDateString())
                    ->whereDate('payment_date', '<=', $monthEnd->toDateString())
                    ->get()
                    ->groupBy('student_id')
                    ->map(fn ($studentPayments): float => (float) $studentPayments->sum('amount'));

                foreach ($students as $student) {
                    $classRoom = $student->class_room_id
                        ? $classById->get($student->class_room_id)
                        : (($matches = $classesByName->get(mb_strtolower(trim($student->class_name)), collect()))->count() === 1
                            ? $matches->first()
                            : null);
                    $monthlyOutstanding += max(
                        0,
                        (float) ($classRoom?->monthly_fee ?? 0) - (float) $paymentsByStudent->get($student->id, 0)
                    );
                }
            }
        }

        $chartStart = $monthStart->subMonths(5);
        $monthlyChart = $canViewFinancialDashboard
            ? collect(range(0, 5))->map(function (int $offset) use ($schoolYear, $chartStart): array {
                $month = $chartStart->addMonths($offset);
                $from = $month->startOfMonth()->toDateString();
                $to = $month->endOfMonth()->toDateString();
                $collected = $schoolYear
                    ? (float) Payment::where('school_year_id', $schoolYear->id)->whereBetween('payment_date', [$from, $to])->sum('amount')
                    : 0.0;
                $spent = $schoolYear
                    ? (float) Expense::where('school_year_id', $schoolYear->id)->whereBetween('expense_date', [$from, $to])->sum('amount')
                    : 0.0;

                return [
                    'label' => $month->translatedFormat('M'),
                    'collected' => $collected,
                    'expenses' => $spent,
                ];
            })
            : collect();
        $chartMax = max(1, (float) $monthlyChart->max(fn (array $month) => max($month['collected'], $month['expenses'])));

        return view('dashboard', compact(
            'school',
            'canViewFinancialDashboard',
            'primaryPhoto',
            'schoolYear',
            'schoolYears',
            'studentCount',
            'activeStudentCount',
            'teacherCount',
            'classCount',
            'collectedThisMonth',
            'expensesThisMonth',
            'monthlyOutstanding',
            'classCounts',
            'recentPayments',
            'recentExpenses',
            'monthlyChart',
            'chartMax',
            'monthStart',
            'girlCount',
            'boyCount',
            'totalCollected',
            'totalExpenses'
        ));
    }
}
