<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
