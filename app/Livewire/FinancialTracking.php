<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use Livewire\Component;

class FinancialTracking extends Component
{
    public function render()
    {
        $currentYear = SchoolYear::query()->where('is_current', true)->first();
        $classes = $currentYear
            ? $currentYear->classes()->get(['id', 'name', 'monthly_fee'])
            : collect();
        $students = $currentYear
            ? Student::query()
                ->where('school_year_id', $currentYear->id)
                ->where('status', 'actif')
                ->get(['class_room_id', 'class_name', 'is_exempt'])
            : collect();

        $classesById = $classes->keyBy('id');
        $classesByName = $classes->keyBy(fn ($class) => mb_strtolower(trim($class->name)));
        $monthlyForecast = 0.0;
        $payingStudents = 0;
        $exemptStudents = 0;

        foreach ($students as $student) {
            if ($student->is_exempt) {
                $exemptStudents++;
                continue;
            }

            $payingStudents++;
            $classRoom = $classesById->get($student->class_room_id)
                ?? $classesByName->get(mb_strtolower(trim($student->class_name)));

            if ($classRoom) {
                $monthlyForecast += (float) $classRoom->monthly_fee;
            }
        }

        $annualForecast = $monthlyForecast * 12;
        $payments = $currentYear
            ? Payment::query()
                ->where('school_year_id', $currentYear->id)
                ->where('fee_type', 'scolarite')
                ->get(['amount', 'fee_month'])
            : collect();
        $realizedTotal = (float) $payments->sum('amount');
        $monthlyCollections = $payments
            ->filter(fn ($payment) => $payment->fee_month !== null)
            ->groupBy(fn ($payment) => (int) $payment->fee_month->format('n'))
            ->map(fn ($monthPayments) => (float) $monthPayments->sum('amount'));

        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];
        $monthlyTracking = [];
        $cumulativeForecast = 0.0;
        $cumulativeCollection = 0.0;

        foreach ($months as $monthNumber => $monthName) {
            $collected = $monthlyCollections->get($monthNumber, 0.0);
            $cumulativeForecast += $monthlyForecast;
            $cumulativeCollection += $collected;

            $monthlyTracking[] = [
                'name' => $monthName,
                'forecast' => $monthlyForecast,
                'collected' => $collected,
                'recovery_rate' => $monthlyForecast > 0
                    ? ($collected / $monthlyForecast) * 100
                    : 0,
                'remaining_claim' => max(0, $cumulativeForecast - $cumulativeCollection),
            ];
        }

        $totalStudents = $students->count();

        return view('livewire.financial-tracking', [
            'currentYear' => $currentYear,
            'annualForecast' => $annualForecast,
            'realizedTotal' => $realizedTotal,
            'remainingClaim' => max(0, $annualForecast - $realizedTotal),
            'globalRecoveryRate' => $annualForecast > 0
                ? ($realizedTotal / $annualForecast) * 100
                : 0,
            'totalStudents' => $totalStudents,
            'payingStudents' => $payingStudents,
            'exemptStudents' => $exemptStudents,
            'exemptionRate' => $totalStudents > 0
                ? ($exemptStudents / $totalStudents) * 100
                : 0,
            'monthlyTracking' => $monthlyTracking,
            'currency' => auth()->user()?->school?->currency ?? 'XOF',
        ])->layout('layouts.app');
    }
}
