<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolApplication;
use App\Models\OwnerAdminAction;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OwnerSchoolController extends Controller
{
    public function index()
    {
        $schools = School::query()
            ->with('owner:id,name,email')
            ->withCount('users')
            ->orderByDesc('created_at')
            ->paginate(25);
        $applications = SchoolApplication::query()
            ->with('applicant:id,name,email')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();
        $actions = OwnerAdminAction::query()
            ->with(['actor:id,name,email', 'school:id,name', 'application:id,name'])
            ->latest()
            ->limit(15)
            ->get();

        return view('owner.schools.index', compact('schools', 'applications', 'actions'));
    }

    public function approveApplication(Request $request, SchoolApplication $application)
    {
        DB::transaction(function () use ($request, $application): void {
            $application = SchoolApplication::query()
                ->whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($application->status === 'pending', 409, 'Cette demande a déjà été traitée.');

            $owner = User::query()->whereKey($application->user_id)->lockForUpdate()->firstOrFail();
            abort_if($owner->school_id || $owner->isSuperAdmin(), 409, 'Le demandeur ne peut plus créer cet espace scolaire.');

            $school = School::create([
                'name' => $application->name,
                'logo_path' => $application->logo_path,
                'address' => $application->address,
                'phone' => $application->phone,
                'email' => $application->email,
                'country' => $application->country,
                'province' => $application->province,
                'academy' => $application->academy,
                'cap' => $application->cap,
                'currency' => $application->currency,
                'institution_type' => $application->institution_type,
                'cycles' => $application->cycles,
                'owner_user_id' => $owner->id,
            ]);

            $school->schoolYears()->create([
                'name' => $application->school_year,
                'is_current' => true,
            ]);

            $owner->school_id = $school->id;
            $owner->role = $application->role;
            $owner->save();

            $application->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'approved_school_id' => $school->id,
            ]);

            $this->recordAction($request, $school, $application, 'school_approved', 'École approuvée et activée.');
        });

        return redirect()->route('owner.dashboard')->with('success', 'La demande a été approuvée. L’établissement peut maintenant utiliser YELENSCOL.');
    }

    public function rejectApplication(Request $request, SchoolApplication $application)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $application, $validated): void {
            $lockedApplication = SchoolApplication::query()
                ->whereKey($application->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_unless($lockedApplication->status === 'pending', 409, 'Cette demande a déjà été traitée.');

            $lockedApplication->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $this->recordAction(
                $request,
                null,
                $lockedApplication,
                'application_rejected',
                'Demande d’école refusée.',
                ['reason' => $validated['rejection_reason']]
            );
        });

        return redirect()->route('owner.dashboard')->with('success', 'La demande a été refusée. Le demandeur pourra soumettre une nouvelle demande.');
    }

    public function suspendSchool(Request $request, School $school)
    {
        $validated = $request->validate([
            'suspension_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $school, $validated): void {
            $school = School::query()->whereKey($school->id)->lockForUpdate()->firstOrFail();
            abort_unless($school->access_status === 'active', 409, 'Cette école est déjà suspendue.');

            $school->update([
                'access_status' => 'suspended',
                'suspended_at' => now(),
                'suspension_reason' => $validated['suspension_reason'],
            ]);

            $this->recordAction(
                $request,
                $school,
                null,
                'school_suspended',
                'Accès de l’école suspendu.',
                ['reason' => $validated['suspension_reason']]
            );
        });

        return redirect()->route('owner.dashboard')->with('success', 'L’accès à l’école a été suspendu.');
    }

    public function reactivateSchool(Request $request, School $school)
    {
        DB::transaction(function () use ($request, $school): void {
            $school = School::query()->whereKey($school->id)->lockForUpdate()->firstOrFail();
            abort_unless($school->access_status === 'suspended', 409, 'Cette école n’est pas suspendue.');

            $school->update([
                'access_status' => 'active',
                'suspended_at' => null,
                'suspension_reason' => null,
            ]);

            $this->recordAction($request, $school, null, 'school_reactivated', 'Accès de l’école réactivé.');
        });

        return redirect()->route('owner.dashboard')->with('success', 'L’école a été réactivée.');
    }

    public function updateSubscription(Request $request, School $school)
    {
        $validated = $request->validate([
            'subscription_plan' => ['required', Rule::in(array_keys(School::SUBSCRIPTION_PLANS))],
            'subscription_status' => ['required', Rule::in(array_keys(School::SUBSCRIPTION_STATUSES))],
            'subscription_starts_at' => ['nullable', 'date'],
            'subscription_ends_at' => ['nullable', 'date'],
            'subscription_note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $school, $validated): void {
            $school = School::query()->whereKey($school->id)->lockForUpdate()->firstOrFail();
            $previous = [
                'plan' => $school->subscription_plan,
                'status' => $school->subscription_status,
                'starts_at' => $school->subscription_starts_at?->toDateString(),
                'ends_at' => $school->subscription_ends_at?->toDateString(),
            ];

            $school->update($validated);

            $this->recordAction(
                $request,
                $school,
                null,
                'subscription_updated',
                'Offre ou abonnement mis à jour.',
                [
                    'previous' => $previous,
                    'current' => [
                        'plan' => $school->subscription_plan,
                        'status' => $school->subscription_status,
                        'starts_at' => $school->subscription_starts_at?->toDateString(),
                        'ends_at' => $school->subscription_ends_at?->toDateString(),
                    ],
                ]
            );
        });

        return redirect()->route('owner.dashboard')->with('success', 'Les informations d’abonnement ont été enregistrées.');
    }

    public function destroySchool(Request $request, School $school)
    {
        $validated = $request->validate([
            'confirmation' => ['required', 'string', Rule::in([$school->name])],
        ], [
            'confirmation.in' => 'Saisissez exactement le nom de l’école pour confirmer sa suppression.',
        ]);

        $schoolId = $school->id;

        DB::transaction(function () use ($request, $schoolId, $validated): void {
            $school = School::query()->whereKey($schoolId)->lockForUpdate()->firstOrFail();
            abort_unless($validated['confirmation'] === $school->name, 422);

            $schoolYearIds = DB::table('school_years')->where('school_id', $school->id)->pluck('id');
            $schoolName = $school->name;

            OwnerAdminAction::create([
                'actor_user_id' => $request->user()->id,
                'school_id' => $school->id,
                'action' => 'school_deleted',
                'summary' => 'École supprimée définitivement.',
                'metadata' => [
                    'school_id' => $school->id,
                    'school_name' => $schoolName,
                ],
            ]);

            User::query()
                ->where('school_id', $school->id)
                ->update(['school_id' => null, 'role' => 'pending']);

            if ($schoolYearIds->isNotEmpty()) {
                Payment::query()->whereIn('school_year_id', $schoolYearIds)->delete();
                Expense::query()->whereIn('school_year_id', $schoolYearIds)->delete();
                Student::withoutGlobalScopes()->whereIn('school_year_id', $schoolYearIds)->delete();
                Teacher::withoutGlobalScopes()->whereIn('school_year_id', $schoolYearIds)->delete();
                Subject::withoutGlobalScopes()->whereIn('school_year_id', $schoolYearIds)->delete();
            }

            DB::table('school_years')->where('school_id', $school->id)->delete();
            DB::table('school_invitations')->where('school_id', $school->id)->delete();
            DB::table('school_photos')->where('school_id', $school->id)->delete();
            DB::table('school_applications')
                ->where('approved_school_id', $school->id)
                ->update(['approved_school_id' => null]);
            DB::table('owner_admin_actions')
                ->where('school_id', $school->id)
                ->update(['school_id' => null]);
            $school->delete();
        });

        $directory = 'schools/'.$schoolId;
        $publicFilesRemoved = Storage::disk('public')->deleteDirectory($directory);
        $privateFilesRemoved = Storage::disk('private')->deleteDirectory($directory);

        if (!$publicFilesRemoved || !$privateFilesRemoved) {
            Log::warning('School deleted but some school files could not be removed.', [
                'school_id' => $schoolId,
                'public_files_removed' => $publicFilesRemoved,
                'private_files_removed' => $privateFilesRemoved,
            ]);

            return redirect()->route('owner.dashboard')
                ->with('error', 'L’école et ses données ont été supprimées, mais certains fichiers n’ont pas pu être effacés. Consultez les journaux de l’application.');
        }

        return redirect()->route('owner.dashboard')->with('success', 'L’école et toutes ses données ont été supprimées définitivement.');
    }

    private function recordAction(
        Request $request,
        ?School $school,
        ?SchoolApplication $application,
        string $action,
        string $summary,
        array $metadata = []
    ): void {
        OwnerAdminAction::create([
            'actor_user_id' => $request->user()->id,
            'school_id' => $school?->id,
            'school_application_id' => $application?->id,
            'action' => $action,
            'summary' => $summary,
            'metadata' => $metadata ?: null,
        ]);
    }
}
