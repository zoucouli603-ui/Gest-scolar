<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\FinancialTracking;

class FinanceSubsidyTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_page_loads_and_records_a_lycee_subsidy(): void
    {
        [$owner, $year, $classRoom, $student] = $this->schoolWithStudent('Lycée');

        $this->actingAs($owner)
            ->get(route('finances.index'))
            ->assertOk()
            ->assertDontSee('Ajouter une Subvention')
            ->assertDontSee('subsidy-modal')
            ->assertSee('Enregistrer un paiement');

        $this->actingAs($owner)
            ->post(route('finances.subsidies.store'), [
                'class_room_id' => $classRoom->id,
                'student_id' => $student->id,
                'amount' => 25000,
                'fee_type' => 'scolarite',
                'fee_month' => now()->format('Y-m'),
                'payment_date' => today()->toDateString(),
                'reference' => 'ETAT-2026-01',
                'description' => 'Prise en charge trimestrielle',
            ])
            ->assertRedirect(route('finances.receipt', Payment::query()->latest('id')->first()));

        $payment = Payment::query()->latest('id')->firstOrFail();
        $this->assertTrue($payment->is_subsidy);
        $this->assertSame('autre', $payment->payment_method);
        $this->assertStringStartsWith('SUB-', $payment->receipt_number);
        $this->assertSame($year->id, $payment->school_year_id);
    }

    public function test_subsidy_is_rejected_for_a_non_lycee_class(): void
    {
        [$owner, , $classRoom, $student] = $this->schoolWithStudent('1er Cycle');

        $this->actingAs($owner)
            ->from(route('finances.index'))
            ->post(route('finances.subsidies.store'), [
                'class_room_id' => $classRoom->id,
                'student_id' => $student->id,
                'amount' => 25000,
                'fee_type' => 'inscription',
                'payment_date' => today()->toDateString(),
            ])
            ->assertRedirect(route('finances.index'))
            ->assertSessionHasErrors('class_room_id');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_financial_tracking_calculates_forecast_collections_and_exemptions(): void
    {
        [$owner, $year, $classRoom, $student] = $this->schoolWithStudent('Lycée');
        Livewire::actingAs($owner)
            ->test(FinancialTracking::class)
            ->set('annualForecastInput', '300000')
            ->call('saveForecast')
            ->assertHasNoErrors()
            ->assertSet('annualForecastInput', '300000')
            ->assertSee('300 000 XOF');
        $this->assertDatabaseHas('school_years', [
            'id' => $year->id,
            'annual_forecast' => 300000,
        ]);

        Student::create([
            'registration_number' => 'FIN-002',
            'first_name' => 'Moussa',
            'last_name' => 'Traoré',
            'gender' => 'M',
            'birth_date' => '2008-07-12',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => today(),
            'class_name' => $classRoom->name,
            'class_room_id' => $classRoom->id,
            'school_year_id' => $year->id,
            'parent_name' => 'Parent Traoré',
            'parent_phone' => '70000001',
            'status' => 'actif',
            'is_exempt' => true,
        ]);
        Payment::create([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'school_year_id' => $year->id,
            'amount' => 10000,
            'fee_type' => 'scolarite',
            'fee_month' => '2026-09-01',
            'payment_date' => today(),
            'payment_method' => 'especes',
        ]);

        $response = $this->actingAs($owner)
            ->get(route('finances.tracking'))
            ->assertOk()
            ->assertSee('300 000 XOF')
            ->assertSee('10 000 XOF')
            ->assertSee('290 000 XOF')
            ->assertSee('Septembre')
            ->assertSee('50,0 %')
            ->assertSee('TOTAL / ANNUEL')
            ->assertSee('Élèves payants')
            ->assertSee('SYNTHÈSE FINANCIÈRE ET EFFECTIFS')
            ->assertSee('Tableau de suivi du recouvrement des frais scolaires')
            ->assertSee('École Finance');

        $tablePosition = strpos($response->getContent(), 'Tableau de suivi du recouvrement des frais scolaires');
        $tableHtml = substr($response->getContent(), $tablePosition);
        $columnPositions = array_map(
            fn ($heading) => strpos($tableHtml, $heading),
            ['Mois', 'Élèves payants', 'Prévisionnel', 'Montant encaissé', 'Taux de recouvrement', 'Créance cumulée']
        );
        $this->assertSame($columnPositions, array_values($columnPositions));
        $this->assertSame($columnPositions, collect($columnPositions)->sort()->values()->all());
    }

    public function test_director_can_open_financial_tracking_but_not_general_finances(): void
    {
        [$owner] = $this->schoolWithStudent('Lycée');
        $director = User::factory()->create([
            'school_id' => $owner->school_id,
            'role' => 'directeur',
        ]);

        $this->actingAs($director)
            ->get(route('finances.tracking'))
            ->assertOk()
            ->assertSee('Prévisionnel annuel manuel');

        $this->actingAs($director)
            ->get(route('finances.index'))
            ->assertForbidden();
    }

    public function test_manual_annual_forecast_is_distributed_without_rounding_loss(): void
    {
        [$owner] = $this->schoolWithStudent('Lycée');

        Livewire::actingAs($owner)
            ->test(FinancialTracking::class)
            ->set('annualForecastInput', '44339985')
            ->call('saveForecast')
            ->assertHasNoErrors()
            ->assertSee('3 694 999 XOF');
        $this->assertDatabaseHas('school_years', [
            'school_id' => $owner->school_id,
            'annual_forecast' => 44339985,
        ]);

        $this->actingAs($owner)
            ->get(route('finances.tracking'))
            ->assertOk()
            ->assertSee('44 339 985 XOF')
            ->assertSee('3 694 998 XOF');
    }

    public function test_large_manual_forecast_is_saved_and_reflected_in_financial_totals(): void
    {
        [$owner, $year] = $this->schoolWithStudent('Lycée');

        $component = Livewire::actingAs($owner)->test(FinancialTracking::class)
            ->assertSee('type="text"', false);

        foreach (['1.000.000', '1,000,000', '1 000 000'] as $formattedForecast) {
            $component->set('annualForecastInput', $formattedForecast)
                ->call('saveForecast')
                ->assertHasNoErrors()
                ->assertSet('annualForecastInput', '1000000')
                ->assertSee('1 000 000 XOF')
                ->assertSee('83 334 XOF');
        }

        $this->assertDatabaseHas('school_years', [
            'id' => $year->id,
            'annual_forecast' => 1000000,
        ]);
    }

    private function schoolWithStudent(string $cycle): array
    {
        $owner = User::factory()->create();
        $school = School::create([
            'name' => 'École Finance',
            'country' => 'Mali',
            'currency' => 'XOF',
            'institution_type' => 'private',
            'cycles' => [$cycle],
            'owner_user_id' => $owner->id,
        ]);
        $owner->forceFill(['school_id' => $school->id, 'role' => 'school_admin'])->save();
        $year = SchoolYear::create([
            'school_id' => $school->id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        $classRoom = ClassRoom::create([
            'name' => 'Terminale A',
            'cycle' => $cycle,
            'monthly_fee' => 25000,
            'school_year_id' => $year->id,
        ]);
        $student = Student::create([
            'registration_number' => 'FIN-001',
            'first_name' => 'Awa',
            'last_name' => 'Diallo',
            'gender' => 'F',
            'birth_date' => '2008-05-10',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => today(),
            'class_name' => $classRoom->name,
            'class_room_id' => $classRoom->id,
            'school_year_id' => $year->id,
            'parent_name' => 'Parent Diallo',
            'parent_phone' => '70000000',
            'status' => 'actif',
        ]);

        return [$owner, $year, $classRoom, $student];
    }
}
