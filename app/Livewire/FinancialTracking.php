<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FinancialTracking extends Component
{
    #[Validate('required|regex:/^\d+$/|integer|min:0|max:999999999999')]
    public string $annualForecastInput = '';

    public ?string $forecastMessage = null;

    public function mount(): void
    {
        $this->annualForecastInput = (string) (
            SchoolYear::query()->where('is_current', true)->value('annual_forecast') ?? '0'
        );
    }

    public function saveForecast(): void
    {
        abort_unless(
            auth()->user()?->canAccess('finances') || auth()->user()?->role === 'directeur',
            403
        );

        $this->annualForecastInput = preg_replace(
            '/[^0-9]+/',
            '',
            trim($this->annualForecastInput)
        ) ?? $this->annualForecastInput;

        $validated = $this->validate();
        $currentYear = SchoolYear::query()->where('is_current', true)->first();

        if (! $currentYear) {
            $this->addError('annualForecastInput', 'Aucune année scolaire active n’est définie.');
            return;
        }

        $forecast = (int) $validated['annualForecastInput'];
        $currentYear->annual_forecast = $forecast;
        $currentYear->saveOrFail();
        $currentYear->refresh();
        $this->annualForecastInput = (string) $forecast;
        $this->resetValidation('annualForecastInput');
        $this->forecastMessage = 'Prévisionnel annuel enregistré.';
    }

    public function render()
    {
        $currentYear = SchoolYear::query()->where('is_current', true)->first();
        $storedAnnualForecast = (float) ($currentYear?->annual_forecast ?? 0);
        $students = $currentYear
            ? Student::query()
                ->where('school_year_id', $currentYear->id)
                ->where('status', 'actif')
                ->get(['class_room_id', 'class_name', 'is_exempt'])
            : collect();

        $payingStudents = 0;
        $exemptStudents = 0;

        foreach ($students as $student) {
            if ($student->is_exempt) {
                $exemptStudents++;
                continue;
            }

            $payingStudents++;
        }

        $annualForecast = $payingStudents > 0 ? (int) $storedAnnualForecast : 0;
        $monthlyForecastBase = intdiv($annualForecast, 12);
        $monthlyForecastRemainder = $annualForecast % 12;
        $payments = $currentYear
            ? Payment::query()
                ->where('school_year_id', $currentYear->id)
                ->where('fee_type', 'scolarite')
                ->get(['amount', 'fee_month'])
            : collect();
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
        $annualCollected = 0.0;

        foreach ($months as $monthNumber => $monthName) {
            $monthlyForecast = $monthlyForecastBase
                + ($monthNumber <= $monthlyForecastRemainder ? 1 : 0);
            $collected = $monthlyCollections->get($monthNumber, 0.0);
            $cumulativeForecast += $monthlyForecast;
            $cumulativeCollection += $collected;
            $annualCollected += $collected;

            $monthlyTracking[] = [
                'name' => $monthName,
                'paying_students' => $payingStudents,
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
            'annualForecastInput' => $this->annualForecastInput,
            'realizedTotal' => $annualCollected,
            'remainingClaim' => max(0, $annualForecast - $annualCollected),
            'globalRecoveryRate' => $annualForecast > 0
                ? ($annualCollected / $annualForecast) * 100
                : 0,
            'annualCollected' => $annualCollected,
            'forecastMessage' => $this->forecastMessage,
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
