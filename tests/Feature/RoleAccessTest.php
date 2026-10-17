<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolYear;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function matrix(): array
    {
        return [
            'promoteur lycée all' => ['promoteur', ['Lycée'], ['finances', 'reports', 'school-years', 'notes'], []],
            'maternelle lycée all' => ['maternelle', ['Lycée'], ['finances', 'reports', 'school-years'], []],
            'directeur lycée' => ['directeur', ['Lycée'], ['school-years', 'notes', 'documents'], ['finances', 'reports']],
            'censeur lycée' => ['censeur', ['Lycée'], ['notes', 'documents', 'teaching-assignments'], ['school-years', 'finances', 'reports']],
            'surveillant lycée' => ['surveillant', ['Lycée'], ['students', 'classes', 'reports'], ['school-years', 'finances', 'teaching-assignments', 'notes', 'documents']],
            'comptable lycée' => ['comptable', ['Lycée'], ['dashboard', 'students', 'teachers', 'classes', 'finances', 'reports'], ['notes', 'documents', 'school-years', 'timetable', 'teaching-assignments', 'parents', 'updates']],
            'directeur premier cycle full access' => ['directeur', ['1er Cycle'], ['school-years', 'notes', 'documents', 'finances', 'reports'], []],
            'directeur second cycle full access' => ['directeur', ['2è Cycle'], ['school-years', 'notes', 'documents', 'finances', 'reports'], []],
            'directeur maternelle full access' => ['directeur', ['Maternelle'], ['school-years', 'notes', 'documents', 'finances', 'reports'], []],
            'comptable non-lycee full access' => ['comptable', ['1er Cycle'], ['students', 'classes', 'notes', 'dashboard', 'finances', 'reports'], []],
        ];
    }

    #[DataProvider('matrix')]
    public function test_role_access(string $role, array $cycles, array $allowed, array $denied): void
    {
        $user = new User(['name' => 'x']);
        $user->role = $role;
        $user->setRelation('school', new School(['cycles' => $cycles]));

        foreach ($allowed as $module) {
            $this->assertTrue($user->canAccess($module), "$role should access $module");
        }
        foreach ($denied as $module) {
            $this->assertFalse($user->canAccess($module), "$role should not access $module");
        }
    }

    public function test_roles_are_enforced_on_direct_routes_and_accountant_can_follow_the_dashboard(): void
    {
        $owner = User::factory()->create();
        $school = School::create([
            'name' => 'École Rôles',
            'country' => 'Mali',
            'currency' => 'XOF',
            'institution_type' => 'private',
            'cycles' => ['Lycée'],
            'owner_user_id' => $owner->id,
        ]);
        $owner->forceFill(['school_id' => $school->id, 'role' => 'school_admin'])->save();
        SchoolYear::create([
            'school_id' => $school->id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        $director = User::factory()->create(['school_id' => $school->id, 'role' => 'directeur']);
        $accountant = User::factory()->create(['school_id' => $school->id, 'role' => 'comptable']);

        $this->actingAs($director)
            ->get(route('finances.index'))
            ->assertForbidden();
        $this->actingAs($accountant)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Aperçu financier')
            ->assertSee('Accès rapides');
        $this->actingAs($accountant)->get(route('students.index'))->assertOk();
        $this->actingAs($accountant)->get(route('teachers.index'))->assertOk();
        $this->actingAs($accountant)->get(route('classes.index'))->assertOk();
        $this->actingAs($accountant)->get(route('finances.index'))->assertOk();
        $this->actingAs($accountant)->get(route('reports.index'))->assertOk();
        $this->actingAs($accountant)->get(route('notes.index'))->assertForbidden();
    }

    public function test_super_admin_home_links_open_the_owner_dashboard(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $regularUser = User::factory()->create(['role' => 'school_admin']);

        $this->actingAs($superAdmin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('owner.dashboard').'"', false);

        $this->get(route('dashboard'))
            ->assertRedirect(route('owner.dashboard'));

        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Écoles utilisant YELENSCOL')
            ->assertDontSee('href="'.route('students.index').'"', false);

        $this->actingAs($regularUser)
            ->get(route('owner.dashboard'))
            ->assertForbidden();
    }
}
