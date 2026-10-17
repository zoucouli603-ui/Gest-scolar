<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_current_year_metrics_and_only_the_signed_in_school_data(): void
    {
        $owner = $this->createSchoolOwner('dashboard-owner@tenant.test', 'École Tableau');
        $schoolYear = SchoolYear::create([
            'school_id' => $owner->school_id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        $classRoom = ClassRoom::create([
            'name' => 'CM1',
            'cycle' => 'Primaire',
            'monthly_fee' => 100000,
            'school_year_id' => $schoolYear->id,
        ]);
        $student = Student::create([
            'registration_number' => 'DASH-0001',
            'first_name' => 'Awa',
            'last_name' => 'Diallo',
            'gender' => 'F',
            'birth_date' => '2015-01-01',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => today(),
            'class_name' => 'CM1',
            'class_room_id' => $classRoom->id,
            'parent_name' => 'Parent Diallo',
            'parent_phone' => '70000000',
            'school_year_id' => $schoolYear->id,
            'status' => 'actif',
        ]);
        Teacher::create([
            'first_name' => 'Moussa',
            'last_name' => 'Traoré',
            'monthly_salary' => 75000,
            'status' => 'actif',
            'school_year_id' => $schoolYear->id,
        ]);
        Payment::create([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'school_year_id' => $schoolYear->id,
            'amount' => 40000,
            'fee_type' => 'scolarite',
            'fee_month' => today()->startOfMonth(),
            'payment_date' => today(),
            'payment_method' => 'espèces',
        ]);
        Expense::create([
            'school_year_id' => $schoolYear->id,
            'expense_date' => today(),
            'category' => 'Fournitures',
            'amount' => 12000,
            'description' => 'Cahiers',
        ]);

        $otherOwner = $this->createSchoolOwner('dashboard-other@tenant.test', 'Autre école');
        $otherYear = SchoolYear::create([
            'school_id' => $otherOwner->school_id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        Student::create([
            'registration_number' => 'DASH-0002',
            'first_name' => 'Autre',
            'last_name' => 'Élève',
            'gender' => 'M',
            'birth_date' => '2015-01-01',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => today(),
            'class_name' => 'CM1',
            'parent_name' => 'Parent Autre',
            'parent_phone' => '70000001',
            'school_year_id' => $otherYear->id,
            'status' => 'actif',
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('École Tableau')
            ->assertSee('2026-2027')
            ->assertSee('Élèves actifs')
            ->assertSee('class="mt-2 truncate text-2xl font-bold text-slate-900">1</p>', false)
            ->assertSee('60 000')
            ->assertSee('40 000')
            ->assertSee('12 000')
            ->assertSee('Awa Diallo')
            ->assertDontSee('Autre Élève');
    }

    public function test_dashboard_explains_when_no_school_year_exists(): void
    {
        $owner = $this->createSchoolOwner('dashboard-empty@tenant.test', 'École sans année');

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Aucune année scolaire configurée')
            ->assertSee('Configurer les années scolaires');
    }

    public function test_lycee_dashboard_hides_financial_information_from_non_accounting_staff(): void
    {
        $owner = $this->createSchoolOwner('dashboard-lycee-owner@tenant.test', 'Lycée Tableau');
        $owner->school->update(['cycles' => ['Lycée']]);
        $schoolYear = SchoolYear::create([
            'school_id' => $owner->school_id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        $classRoom = ClassRoom::create([
            'name' => 'Terminale',
            'cycle' => 'Lycée',
            'monthly_fee' => 50000,
            'school_year_id' => $schoolYear->id,
        ]);
        $student = Student::create([
            'registration_number' => 'LYCEE-DASH-1',
            'first_name' => 'Aminata',
            'last_name' => 'Traoré',
            'gender' => 'F',
            'birth_date' => '2008-01-01',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => today(),
            'class_name' => 'Terminale',
            'class_room_id' => $classRoom->id,
            'parent_name' => 'Parent Test',
            'parent_phone' => '70000000',
            'school_year_id' => $schoolYear->id,
            'status' => 'actif',
        ]);
        Teacher::create([
            'first_name' => 'Moussa',
            'last_name' => 'Traoré',
            'monthly_salary' => 75000,
            'status' => 'actif',
            'school_year_id' => $schoolYear->id,
        ]);
        Payment::create([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'school_year_id' => $schoolYear->id,
            'amount' => 12345678,
            'fee_type' => 'scolarite',
            'fee_month' => today()->startOfMonth(),
            'payment_date' => today(),
            'payment_method' => 'espèces',
        ]);
        Expense::create([
            'school_year_id' => $schoolYear->id,
            'expense_date' => today(),
            'category' => 'Fournitures',
            'amount' => 2345678,
            'description' => 'Dépense confidentielle de test',
        ]);

        foreach (['directeur', 'censeur', 'surveillant'] as $role) {
            $staff = User::factory()->create([
                'school_id' => $owner->school_id,
                'role' => $role,
            ]);

            $this->actingAs($staff)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Élèves actifs')
                ->assertSee('Enseignants')
                ->assertSee('Classes')
                ->assertDontSee('Solde du mois')
                ->assertDontSee('Solde de l’année')
                ->assertDontSee('Reste dû du mois')
                ->assertDontSee('Recettes du mois')
                ->assertDontSee('Aperçu financier')
                ->assertDontSee('Derniers paiements')
                ->assertDontSee('Dernières dépenses')
                ->assertDontSee('12 345 678')
                ->assertDontSee('2 345 678')
                ->assertDontSee('Dépense confidentielle de test');
        }
    }

    public function test_lycee_accountant_sees_financial_dashboard_and_has_the_requested_navigation(): void
    {
        $owner = $this->createSchoolOwner('dashboard-accountant-owner@tenant.test', 'Lycée Comptable');
        $owner->school->update(['cycles' => ['Lycée']]);
        $schoolYear = SchoolYear::create([
            'school_id' => $owner->school_id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        $classRoom = ClassRoom::create([
            'name' => 'Terminale',
            'cycle' => 'Lycée',
            'school_year_id' => $schoolYear->id,
        ]);
        $student = Student::create([
            'registration_number' => 'LYCEE-DASH-2',
            'first_name' => 'Mariam',
            'last_name' => 'Coulibaly',
            'gender' => 'F',
            'birth_date' => '2008-01-01',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => today(),
            'class_name' => 'Terminale',
            'class_room_id' => $classRoom->id,
            'parent_name' => 'Parent Test',
            'parent_phone' => '70000000',
            'school_year_id' => $schoolYear->id,
            'status' => 'actif',
        ]);
        Payment::create([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'school_year_id' => $schoolYear->id,
            'amount' => 7654321,
            'fee_type' => 'scolarite',
            'fee_month' => today()->startOfMonth(),
            'payment_date' => today(),
            'payment_method' => 'espèces',
        ]);

        $accountant = User::factory()->create([
            'school_id' => $owner->school_id,
            'role' => 'comptable',
        ]);

        $response = $this->actingAs($accountant)->get(route('dashboard'));
        $response->assertOk()
            ->assertSee('Aperçu financier')
            ->assertSee('Solde du mois')
            ->assertSee('Solde de l’année')
            ->assertSee('Reste dû du mois')
            ->assertSee('Recettes du mois')
            ->assertSee('Derniers paiements')
            ->assertSee('7 654 321');

        foreach (['students.index', 'teachers.index', 'classes.index', 'finances.index', 'reports.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    private function createSchoolOwner(string $email, string $schoolName): User
    {
        $owner = User::factory()->create(['email' => $email]);
        $school = School::create([
            'name' => $schoolName,
            'country' => 'Mali',
            'currency' => 'XOF',
            'institution_type' => 'private',
            'owner_user_id' => $owner->id,
        ]);

        $owner->school_id = $school->id;
        $owner->role = 'school_admin';
        $owner->save();

        return $owner;
    }
}
