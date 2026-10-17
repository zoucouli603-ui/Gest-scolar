<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolApplication;
use App\Models\SchoolInvitation;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\OwnerAdminAction;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SchoolTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_request_requires_one_time_super_admin_approval_before_activation(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get(route('school.setup.create'))
            ->assertOk()
            ->assertSee('Cycles proposés par l’école')
            ->assertDontSee('<option value="school_admin"', false)
            ->assertDontSee('<option value="maternelle"', false)
            ->assertDontSee('<option value="premier_cycle"', false)
            ->assertDontSee('<option value="second_cycle"', false);

        $this->actingAs($owner)
            ->post(route('school.setup.store'), [
                'name' => 'École Horizon',
                'address' => 'Centre-ville',
                'phone' => '70000000',
                'email' => 'contact@horizon.test',
                'country' => 'Mali',
                'province' => 'Bamako',
                'currency' => 'XOF',
                'institution_type' => 'private',
                'school_year' => '2026-2027',
                'role' => 'promoteur',
                'cycles' => ['1er Cycle', '2è Cycle'],
                'academy' => 'Académie de Bamako',
                'cap' => 'CAP Commune IV',
                'logo' => \Illuminate\Http\UploadedFile::fake()->create('logo.png', 1, 'image/png'),
            ])
            ->assertRedirect(route('school.setup.create'))
            ->assertSessionHas('success');

        $owner = $owner->fresh();
        $this->assertNull($owner->fresh()->school_id);
        $application = SchoolApplication::where('user_id', $owner->id)->firstOrFail();
        $this->assertSame('pending', $application->status);
        $this->assertSame(['1er Cycle', '2è Cycle'], $application->cycles);
        $this->assertSame('CAP Commune IV', $application->cap);
        $this->assertDatabaseMissing('schools', ['name' => 'École Horizon']);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertRedirect(route('school.setup.create'));
        $this->get(route('school.setup.create'))->assertOk()->assertSee('attend l’approbation');

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('1er Cycle')
            ->assertSee('2è Cycle');

        $this->actingAs($superAdmin)
            ->post(route('owner.applications.approve', $application))
            ->assertRedirect(route('owner.dashboard'));

        $school = School::where('name', 'École Horizon')->firstOrFail();
        $owner->refresh();

        $this->assertSame($school->id, $owner->school_id);
        $this->assertSame('promoteur', $owner->role);
        $this->assertSame('approved', $application->fresh()->status);
        $this->assertSame($superAdmin->id, $application->fresh()->reviewed_by);
        $this->assertDatabaseHas('school_years', [
            'school_id' => $school->id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('owner.applications.approve', $application))
            ->assertConflict();
    }

    public function test_super_admin_can_reject_a_school_request_and_applicant_can_resubmit(): void
    {
        $owner = User::factory()->create();
        $payload = [
            'name' => 'École à vérifier',
            'country' => 'Mali',
            'currency' => 'XOF',
            'institution_type' => 'private',
            'school_year' => '2026-2027',
            'role' => 'promoteur',
            'logo' => \Illuminate\Http\UploadedFile::fake()->create('logo.png', 1, 'image/png'),
            'cycles' => ['1er Cycle'],
            'academy' => 'Académie de Bamako',
            'cap' => 'CAP Commune IV',
        ];
        $this->actingAs($owner)->post(route('school.setup.store'), $payload)
            ->assertRedirect(route('school.setup.create'));

        $application = SchoolApplication::where('user_id', $owner->id)->firstOrFail();
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin)->post(route('owner.applications.reject', $application), [
            'rejection_reason' => 'Merci de corriger les coordonnées de l’établissement.',
        ])->assertRedirect(route('owner.dashboard'));

        $this->assertSame('rejected', $application->fresh()->status);
        $this->assertDatabaseHas('owner_admin_actions', [
            'action' => 'application_rejected',
            'school_application_id' => $application->id,
            'actor_user_id' => $superAdmin->id,
        ]);
        $this->actingAs($owner)->get(route('school.setup.create'))
            ->assertOk()
            ->assertSee('Merci de corriger les coordonnées');

        $this->post(route('school.setup.store'), array_merge($payload, ['name' => 'École corrigée']))
            ->assertRedirect(route('school.setup.create'));
        $this->assertSame(2, SchoolApplication::where('user_id', $owner->id)->count());
        $this->assertSame(1, SchoolApplication::where('user_id', $owner->id)->where('status', 'pending')->count());
    }

    public function test_super_admin_can_suspend_and_reactivate_a_school_and_actions_are_logged(): void
    {
        [$owner] = $this->createSchool('École suspension', 'owner-suspend@tenant.test');
        $staff = User::factory()->create([
            'school_id' => $owner->school_id,
            'role' => 'staff',
        ]);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $school = $owner->school;

        $this->actingAs($owner)->post(route('owner.schools.suspend', $school), [
            'suspension_reason' => 'Une action réservée.',
        ])->assertForbidden();

        $this->actingAs($superAdmin)->post(route('owner.schools.suspend', $school), [
            'suspension_reason' => 'Demande de vérification administrative.',
        ])->assertRedirect(route('owner.dashboard'));

        $this->assertSame('suspended', $school->fresh()->access_status);
        $this->actingAs($staff)->get(route('dashboard'))->assertStatus(503);
        $this->actingAs($owner->fresh())->get(route('school.settings.index'))->assertStatus(503);

        $this->actingAs($superAdmin)->post(route('owner.schools.reactivate', $school))
            ->assertRedirect(route('owner.dashboard'));

        $this->assertSame('active', $school->fresh()->access_status);
        $this->assertNull($school->fresh()->suspension_reason);
        $this->actingAs($staff->fresh())->get(route('dashboard'))->assertOk();
        $this->assertSame(2, OwnerAdminAction::where('school_id', $school->id)->count());
        $this->actingAs($superAdmin)->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Journal des actions administratives')
            ->assertSee('Demande de vérification administrative.');
    }

    public function test_super_admin_can_manually_manage_subscription_without_blocking_school(): void
    {
        [$owner] = $this->createSchool('École abonnement', 'owner-plan@tenant.test');
        $school = $owner->school;
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)->put(route('owner.schools.subscription.update', $school), [
            'subscription_plan' => 'professionnel',
            'subscription_status' => 'trial',
            'subscription_starts_at' => '2026-10-01',
            'subscription_ends_at' => '2026-12-31',
            'subscription_note' => 'Période d’essai convenue.',
        ])->assertRedirect(route('owner.dashboard'));

        $this->assertSame('professionnel', $school->fresh()->subscription_plan);
        $this->assertSame('trial', $school->fresh()->subscription_status);
        $this->assertSame('2026-10-01', $school->fresh()->subscription_starts_at->toDateString());
        $this->assertSame('2026-12-31', $school->fresh()->subscription_ends_at->toDateString());
        $this->assertDatabaseHas('owner_admin_actions', [
            'school_id' => $school->id,
            'action' => 'subscription_updated',
            'actor_user_id' => $superAdmin->id,
        ]);
        $this->actingAs($owner)->get(route('dashboard'))->assertOk();
    }

    public function test_only_super_admin_can_permanently_delete_a_school_and_its_data(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        [$owner, $year] = $this->createSchool('École à supprimer', 'owner-delete@tenant.test');
        $school = $owner->school;
        $staff = User::factory()->create([
            'school_id' => $school->id,
            'role' => 'directeur',
        ]);
        $this->createStudent($year, 'DEM-DELETE', 'Élève à supprimer');
        $student = Student::where('registration_number', 'DEM-DELETE')->firstOrFail();
        Payment::create([
            'student_id' => $student->id,
            'school_year_id' => $year->id,
            'amount' => 1000,
            'payment_date' => '2026-10-01',
            'payment_method' => 'especes',
            'reference' => 'DELETE-1',
        ]);
        Expense::create([
            'school_year_id' => $year->id,
            'expense_date' => '2026-10-01',
            'category' => 'autre',
            'amount' => 500,
            'description' => 'Dépense de test',
        ]);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('Supprimer l’école')
            ->assertSee(route('owner.schools.destroy', $school), false);

        $this->actingAs($owner)
            ->delete(route('owner.schools.destroy', $school), ['confirmation' => $school->name])
            ->assertForbidden();
        $this->actingAs($superAdmin)
            ->delete(route('owner.schools.destroy', $school), ['confirmation' => 'Nom incorrect'])
            ->assertSessionHasErrors('confirmation');

        $this->actingAs($superAdmin)
            ->delete(route('owner.schools.destroy', $school), ['confirmation' => $school->name])
            ->assertRedirect(route('owner.dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('schools', ['id' => $school->id]);
        $this->assertDatabaseMissing('school_years', ['id' => $year->id]);
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('payments', ['student_id' => $student->id]);
        $this->assertDatabaseMissing('expenses', ['school_year_id' => $year->id]);
        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'school_id' => null,
            'role' => 'pending',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'school_id' => null,
            'role' => 'pending',
        ]);
        $this->assertDatabaseHas('owner_admin_actions', [
            'actor_user_id' => $superAdmin->id,
            'school_id' => null,
            'action' => 'school_deleted',
        ]);

        $action = OwnerAdminAction::query()->where('action', 'school_deleted')->firstOrFail();
        $this->assertSame($school->id, data_get($action->metadata, 'school_id'));
        $this->assertSame($school->name, data_get($action->metadata, 'school_name'));
    }

    public function test_school_users_cannot_see_students_from_another_school(): void
    {
        [$firstOwner, $firstYear] = $this->createSchool('École A', 'owner-a@tenant.test');
        [$secondOwner, $secondYear] = $this->createSchool('École B', 'owner-b@tenant.test');
        $this->createStudent($firstYear, 'DEM-0001', 'Élève A');
        $this->createStudent($secondYear, 'DEM-0002', 'Élève B');

        $response = $this->actingAs($firstOwner)->get(route('students.index'));

        $response->assertOk()
            ->assertSee('Élève A')
            ->assertDontSee('Élève B')
            ->assertSee('École A');

        $this->assertSame(1, Student::count());
        $this->assertNotSame($firstYear->school_id, $secondYear->school_id);
    }

    public function test_school_navigation_uses_the_requested_tab_order(): void
    {
        [$owner] = $this->createSchool('École Navigation', 'owner-navigation@tenant.test');
        $this->enableLyceeTeam($owner);

        $response = $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk();

        preg_match_all('/<a\b[^>]*style="order:\s*(\d+)"[^>]*>(.*?)<\/a>/s', $response->getContent(), $matches, PREG_SET_ORDER);
        $orderedLabels = collect($matches)
            ->sortBy(fn (array $match) => (int) $match[1])
            ->map(fn (array $match) => trim(preg_replace('/\s+/u', ' ', strip_tags($match[2]))))
            ->values()
            ->all();

        $this->assertSame([
            'Tableau de bord',
            'Année Scolaire',
            'Classes',
            'Matières',
            'Enseignants',
            'Élèves',
            'Emploi du temps',
            'Finances',
            'Affectations',
            'Notes et Bulletins',
            'Parents',
            'Documents',
            'Rapports',
            'Partager l’espace',
            'Paramètres',
            'Mise à Jour',
        ], $orderedLabels);
    }

    public function test_super_admin_sees_school_metadata_but_cannot_open_school_records(): void
    {
        [$owner] = $this->createSchool('École Visible', 'owner-visible@tenant.test');
        $owner->role = 'super_admin';
        $owner->school_id = null;
        $owner->save();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('École Visible')
            ->assertSee('owner-visible@tenant.test')
            ->assertDontSee('DEM-');

        $this->get(route('students.index'))
            ->assertRedirect(route('owner.dashboard'));
    }

    public function test_super_admin_role_can_only_be_granted_from_the_server_command(): void
    {
        $user = User::factory()->create();

        $this->artisan('app:make-super-admin '.$user->email.' --force')
            ->expectsOutput('Le rôle super-administrateur a été accordé. Cet utilisateur sera redirigé vers l’espace privé de contrôle.')
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->isSuperAdmin());
    }

    public function test_existing_user_can_accept_an_invitation_after_signing_in(): void
    {
        [$owner] = $this->createSchool('École Invitation', 'owner-invitation@tenant.test');
        $member = User::factory()->create(['email' => 'member@tenant.test']);
        $token = 'invitation-token-for-existing-user';
        $invitation = SchoolInvitation::create([
            'school_id' => $owner->school_id,
            'email' => $member->email,
            'token' => hash('sha256', $token),
            'invited_by' => $owner->id,
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($member)
            ->get(route('school.invitations.show', $token))
            ->assertOk()
            ->assertSee('Accepter et rejoindre l’école');

        $this->post(route('school.invitations.accept', $token))
            ->assertRedirect(route('dashboard'));

        $this->assertSame($owner->school_id, $member->fresh()->school_id);
        $this->assertSame('staff', $member->fresh()->role);
        $this->assertNotNull($invitation->fresh()->accepted_at);
    }

    public function test_lycee_promoter_can_invite_an_existing_user_without_a_school(): void
    {
        Mail::fake();
        [$owner] = $this->createSchool('École Invitation compte existant', 'owner-existing@tenant.test');
        $this->enableLyceeTeam($owner);
        $member = User::factory()->create(['email' => 'existing-member@tenant.test']);

        $this->actingAs($owner)
            ->post(route('school.team.invitations.store'), ['email' => $member->email, 'role' => 'directeur'])
            ->assertRedirect(route('school.team.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('school_invitations', [
            'school_id' => $owner->school_id,
            'email' => $member->email,
            'accepted_at' => null,
        ]);
    }

    public function test_lycee_promoter_cannot_invite_a_user_who_already_belongs_to_a_school(): void
    {
        Mail::fake();
        [$owner] = $this->createSchool('École Invitation refusée', 'owner-refused@tenant.test');
        $this->enableLyceeTeam($owner);
        [$member] = $this->createSchool('Autre école', 'member-other-school@tenant.test');

        $this->actingAs($owner)
            ->from(route('school.team.index'))
            ->post(route('school.team.invitations.store'), ['email' => $member->email, 'role' => 'directeur'])
            ->assertRedirect(route('school.team.index'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('school_invitations', [
            'school_id' => $owner->school_id,
            'email' => $member->email,
        ]);
    }

    public function test_only_a_lycee_promoter_can_share_the_school_interface(): void
    {
        [$owner] = $this->createSchool('École sans lycée', 'owner-no-lycee@tenant.test');
        $owner->forceFill(['role' => 'promoteur'])->save();

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Partager l’espace')
            ->assertDontSee(route('school.team.index'));

        $this->get(route('school.team.index'))->assertForbidden();
        $this->post(route('school.team.invitations.store'), [
            'email' => 'staff@no-lycee.test',
            'role' => 'directeur',
        ])->assertForbidden();

        $this->enableLyceeTeam($owner);
        $this->get(route('school.team.index'))
            ->assertOk()
            ->assertSee('Partager l’espace')
            ->assertSee('<option value="directeur"', false)
            ->assertSee('<option value="censeur"', false)
            ->assertSee('<option value="comptable"', false)
            ->assertSee('<option value="surveillant"', false)
            ->assertDontSee('<option value="promoteur"', false)
            ->assertDontSee('<option value="maternelle"', false);
    }

    public function test_lycee_promoter_can_invite_only_the_four_school_staff_roles(): void
    {
        Mail::fake();
        [$owner] = $this->createSchool('Lycée du personnel', 'owner-lycee-team@tenant.test');
        $this->enableLyceeTeam($owner);

        foreach (['directeur', 'censeur', 'comptable', 'surveillant'] as $index => $role) {
            $email = "staff{$index}@lycee.test";
            $this->actingAs($owner)
                ->post(route('school.team.invitations.store'), ['email' => $email, 'role' => $role])
                ->assertRedirect(route('school.team.index'))
                ->assertSessionHasNoErrors();

            $this->assertDatabaseHas('school_invitations', [
                'school_id' => $owner->school_id,
                'email' => $email,
                'role' => $role,
            ]);
        }

        $this->actingAs($owner)
            ->from(route('school.team.index'))
            ->post(route('school.team.invitations.store'), [
                'email' => 'invalid-role@lycee.test',
                'role' => 'promoteur',
            ])
            ->assertRedirect(route('school.team.index'))
            ->assertSessionHasErrors('role');
    }

    public function test_expired_school_invitation_is_rejected(): void
    {
        [$owner] = $this->createSchool('École Expirée', 'owner-expired@tenant.test');
        $token = 'expired-school-invitation';
        SchoolInvitation::create([
            'school_id' => $owner->school_id,
            'email' => 'new-member@tenant.test',
            'token' => hash('sha256', $token),
            'invited_by' => $owner->id,
            'expires_at' => now()->subMinute(),
        ]);

        $this->get(route('school.invitations.show', $token))
            ->assertGone();
    }

    public function test_school_admin_can_update_settings_and_enable_maintenance_without_blocking_themselves(): void
    {
        [$owner, $year] = $this->createSchool('École Paramètres', 'owner-settings@tenant.test');
        $staff = User::factory()->create([
            'school_id' => $owner->school_id,
            'role' => 'staff',
        ]);

        $this->actingAs($owner)
            ->put(route('school.settings.update'), [
                'name' => 'École Paramètres Modifiée',
                'director_name' => 'Mamadou Traoré',
                'establishment_type' => 'primaire',
                'cycles' => ['1er Cycle'],
                'academy' => 'Académie de Kinshasa',
                'cap' => 'CAP Nord',
                'institution_type' => 'public',
                'country' => 'Congo',
                'currency' => 'cdf',
                'website' => 'https://ecole.test',
                'city' => 'Kinshasa',
                'maintenance_mode' => '1',
            ])
            ->assertRedirect(route('school.settings.index'));

        $this->assertDatabaseHas('schools', [
            'id' => $owner->school_id,
            'name' => 'École Paramètres Modifiée',
            'director_name' => 'Mamadou Traoré',
            'establishment_type' => 'primaire',
            'currency' => 'CDF',
            'maintenance_mode' => true,
        ]);
        $this->assertSame(['1er Cycle'], $owner->school->fresh()->cycles);

        $this->actingAs($staff)->get(route('dashboard'))->assertStatus(503);
        $this->actingAs($owner)->get(route('school.settings.index'))
            ->assertOk()
            ->assertSee('Nom du Directeur');
        $this->assertSame($owner->school_id, $year->fresh()->school_id);
    }

    public function test_school_settings_and_photos_are_limited_to_the_school_admin(): void
    {
        Storage::fake('public');
        [$owner] = $this->createSchool('École Photos', 'owner-photos@tenant.test');
        $otherOwner = $this->createSchool('Autre école photos', 'owner-other-photos@tenant.test')[0];

        $this->actingAs($owner)
            ->post(route('school.settings.photos.store'), [
                'title' => 'Façade',
                'photo' => UploadedFile::fake()->createWithContent(
                    'facade.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jWZkAAAAASUVORK5CYII=')
                ),
            ])
            ->assertRedirect(route('school.settings.index'));

        $photo = $owner->school->photos()->firstOrFail();
        Storage::fake('public')->assertExists($photo->path);
        $this->actingAs($otherOwner)
            ->put(route('school.settings.photos.primary', $photo))
            ->assertNotFound();

        $staff = User::factory()->create([
            'school_id' => $owner->school_id,
            'role' => 'staff',
        ]);
        $this->actingAs($staff)->get(route('school.settings.index'))->assertForbidden();

        foreach (['directeur', 'censeur'] as $role) {
            $manager = User::factory()->create([
                'school_id' => $owner->school_id,
                'role' => $role,
            ]);

            $this->actingAs($manager)
                ->get(route('school.settings.index'))
                ->assertOk()
                ->assertSee('Paramètres de l’établissement');

            $this->actingAs($manager)
                ->get(route('school.team.index'))
                ->assertForbidden();
        }
    }

    private function createSchool(string $name, string $email): array
    {
        $owner = User::factory()->create(['email' => $email]);
        $school = School::create([
            'name' => $name,
            'country' => 'Mali',
            'currency' => 'XOF',
            'institution_type' => 'private',
            'owner_user_id' => $owner->id,
        ]);
        $owner->school_id = $school->id;
        $owner->role = 'school_admin';
        $owner->save();
        $year = SchoolYear::create([
            'school_id' => $school->id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);

        return [$owner, $year];
    }

    private function enableLyceeTeam(User $owner): void
    {
        $owner->forceFill(['role' => 'promoteur'])->save();
        $owner->school->update(['cycles' => ['Lycée']]);
    }

    private function createStudent(SchoolYear $year, string $registrationNumber, string $firstName): void
    {
        Student::create([
            'registration_number' => $registrationNumber,
            'first_name' => $firstName,
            'last_name' => 'Test',
            'gender' => 'M',
            'birth_date' => '2015-01-01',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => '2026-09-01',
            'class_name' => 'CM1',
            'parent_name' => 'Parent Test',
            'parent_phone' => '70000000',
            'school_year_id' => $year->id,
            'status' => 'actif',
        ]);
    }
}
