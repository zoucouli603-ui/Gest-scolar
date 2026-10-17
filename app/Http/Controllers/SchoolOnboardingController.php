<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SchoolOnboardingController extends Controller
{
    public function create(Request $request)
    {
        abort_unless($request->user(), 403);

        if ($request->user()->isSuperAdmin()) {
            return redirect()->route('owner.dashboard');
        }

        if ($request->user()->school_id) {
            return redirect()->route('dashboard');
        }

        $application = $request->user()->schoolApplication()
            ->where('status', 'pending')
            ->latest()
            ->first();
        if ($application) {
            return view('schools.application-pending', compact('application'));
        }

        $rejectedApplication = $request->user()->schoolApplication()
            ->where('status', 'rejected')
            ->latest()
            ->first();

        return view('schools.setup', compact('rejectedApplication'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user && !$user->isSuperAdmin() && !$user->school_id, 403);
        abort_if(
            $user->schoolApplication()->where('status', 'pending')->exists(),
            409,
            'Une demande de création d’école est déjà en attente.'
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in(array_keys(User::schoolCreationRoles()))],
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'institution_type' => ['required', Rule::in(['public', 'private'])],
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'cycles' => ['required', 'array', 'min:1', School::exclusiveLyceeRule()],
            'cycles.*' => ['required', 'string', Rule::in([...School::CYCLES, 'Primaire', 'Secondaire']), 'distinct'],
            ...School::academyRules($request->all()),
        ]);

        $validated['cycles'] = School::normalizeCycles($validated['cycles']);
        $validated['academy'] ??= null;
        $validated['cap'] ??= null;
        if (! School::includesFundamental($validated['cycles'])) {
            $validated['cap'] = null;
        }
        $logoPath = $request->file('logo')->store('school-logos', 'public');

        try {
            DB::transaction(function () use ($validated, $user, $logoPath): SchoolApplication {
            $lockedUser = \App\Models\User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_if(
                $lockedUser->schoolApplication()->where('status', 'pending')->exists(),
                409,
                'Une demande de création d’école est déjà en attente.'
            );

            return $lockedUser->schoolApplication()->create([
                'role' => $validated['role'],
                'name' => $validated['name'],
                'logo_path' => $logoPath,
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'country' => $validated['country'],
                'province' => $validated['province'] ?? null,
                'academy' => $validated['academy'],
                'cap' => $validated['cap'],
                'currency' => strtoupper($validated['currency']),
                'institution_type' => $validated['institution_type'],
                'school_year' => $validated['school_year'],
                'cycles' => array_values($validated['cycles']),
                'status' => 'pending',
            ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($logoPath);
            throw $e;
        }

        return redirect()->route('school.setup.create')
            ->with('success', 'Votre demande a été envoyée au super-administrateur pour approbation.');
    }
}
