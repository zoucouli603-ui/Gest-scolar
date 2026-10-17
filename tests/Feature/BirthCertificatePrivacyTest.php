<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BirthCertificatePrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_birth_certificates_are_stored_privately_and_served_only_within_the_school(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        [$owner, , $classRoom] = $this->schoolWithClass('École A', 'owner-a@certificate.test');
        [$otherOwner] = $this->schoolWithClass('École B', 'owner-b@certificate.test');

        $this->actingAs($owner)
            ->post(route('students.store'), [
                'first_name' => 'Awa',
                'last_name' => 'Diallo',
                'gender' => 'F',
                'birth_date' => '2015-01-01',
                'birth_place' => 'Bamako',
                'nationality' => 'Malienne',
                'inscription_date' => '2026-09-01',
                'class_room_id' => $classRoom->id,
                'parent_name' => 'Parent Diallo',
                'parent_phone' => '70000000',
                'birth_certificate' => $this->pngUpload(),
            ])
            ->assertRedirect(route('students.index'));

        $student = Student::withoutGlobalScopes()->where('first_name', 'Awa')->firstOrFail();
        $this->assertTrue(Storage::disk('private')->exists($student->birth_certificate_path));
        $this->assertFalse(Storage::disk('public')->exists($student->birth_certificate_path));

        $response = $this->actingAs($owner)
            ->get(route('students.birth-certificate', $student))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        $this->actingAs($otherOwner)
            ->get(route('students.birth-certificate', $student))
            ->assertNotFound();
    }

    public function test_guests_cannot_access_birth_certificates(): void
    {
        Storage::fake('private');
        [$owner, $year, $classRoom] = $this->schoolWithClass('École privée', 'owner-private@certificate.test');
        $path = "schools/{$owner->school_id}/students/birth-certificates/secret.png";
        Storage::disk('private')->put($path, 'private certificate');
        $student = Student::create([
            'registration_number' => 'CERT-PRIVATE',
            'first_name' => 'Awa',
            'last_name' => 'Diallo',
            'gender' => 'F',
            'birth_date' => '2015-01-01',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => '2026-09-01',
            'class_name' => $classRoom->name,
            'class_room_id' => $classRoom->id,
            'school_year_id' => $year->id,
            'parent_name' => 'Parent Diallo',
            'parent_phone' => '70000000',
            'status' => 'actif',
            'birth_certificate_path' => $path,
        ]);

        $this->get(route('students.birth-certificate', $student))
            ->assertRedirect(route('login'));
    }

    public function test_legacy_public_birth_certificates_can_be_moved_to_private_storage(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        [$owner, $year, $classRoom] = $this->schoolWithClass('École Migration', 'owner-migration@certificate.test');
        $legacyPath = "schools/{$owner->school_id}/students/birth-certificates/legacy.png";
        Storage::disk('public')->put($legacyPath, 'legacy certificate');

        $student = Student::create([
            'registration_number' => 'CERT-LEGACY',
            'first_name' => 'Moussa',
            'last_name' => 'Traoré',
            'gender' => 'M',
            'birth_date' => '2015-01-01',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => '2026-09-01',
            'class_name' => $classRoom->name,
            'class_room_id' => $classRoom->id,
            'school_year_id' => $year->id,
            'parent_name' => 'Parent Traoré',
            'parent_phone' => '70000001',
            'status' => 'actif',
            'birth_certificate_path' => $legacyPath,
        ]);

        $this->artisan('students:migrate-private-birth-certificates')
            ->expectsOutputToContain('1 extrait(s) à déplacer')
            ->assertSuccessful();
        $this->assertTrue(Storage::disk('public')->exists($legacyPath));

        $this->artisan('students:migrate-private-birth-certificates', ['--apply' => true])
            ->expectsOutputToContain('1 extrait(s) déplacé(s)')
            ->assertSuccessful();

        $this->assertTrue(Storage::disk('private')->exists($student->birth_certificate_path));
        $this->assertFalse(Storage::disk('public')->exists($legacyPath));
    }

    private function schoolWithClass(string $name, string $email): array
    {
        $owner = User::factory()->create(['email' => $email]);
        $school = School::create([
            'name' => $name,
            'country' => 'Mali',
            'currency' => 'XOF',
            'institution_type' => 'private',
            'cycles' => ['1er Cycle'],
            'owner_user_id' => $owner->id,
        ]);
        $owner->forceFill(['school_id' => $school->id, 'role' => 'school_admin'])->save();
        $year = SchoolYear::create([
            'school_id' => $school->id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        $classRoom = ClassRoom::create([
            'name' => 'CM1',
            'cycle' => '1er Cycle',
            'monthly_fee' => 0,
            'school_year_id' => $year->id,
        ]);

        return [$owner, $year, $classRoom];
    }

    private function pngUpload(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'birth-certificate.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jWZkAAAAASUVORK5CYII=')
        );
    }
}
