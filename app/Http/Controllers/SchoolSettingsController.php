<?php

namespace App\Http\Controllers;

use App\Models\SchoolPhoto;
use App\Models\School;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class SchoolSettingsController extends Controller
{
    public function index(Request $request)
    {
        $school = $request->user()->school;

        return view('schools.settings', [
            'school' => $school,
            'schoolYears' => $school->schoolYears()->orderByDesc('name')->get(),
            'photos' => $school->photos()->orderByDesc('is_primary')->latest()->get(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'director_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048'],
            'cycles' => ['required', 'array', 'min:1', School::exclusiveLyceeRule()],
            'cycles.*' => ['required', 'string', Rule::in([...School::CYCLES, 'Primaire', 'Secondaire']), 'distinct'],
            ...School::academyRules($request->all()),
            'establishment_type' => ['required', Rule::in(['maternelle', 'primaire', 'secondaire', 'lycee', 'polyvalent'])],
            'institution_type' => ['required', Rule::in(['public', 'private'])],
            'country' => ['required', 'string', 'max:100'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'subdivision' => ['nullable', 'string', 'max:100'],
            'inspection' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'maintenance_mode' => ['nullable', 'boolean'],
        ]);

        $validated['cycles'] = School::normalizeCycles($validated['cycles']);
        if (! School::includesFundamental($validated['cycles'])) {
            $validated['cap'] = null;
        }
        if (! School::includesLycee($validated['cycles']) && ! School::includesFundamental($validated['cycles'])) {
            $validated['academy'] = null;
        }
        $school = $request->user()->school;
        $activeCycles = ClassRoom::query()
            ->whereHas('schoolYear', fn ($query) => $query->where('school_id', $school->id))
            ->pluck('cycle')
            ->map(fn ($c) => School::normalizeCycle($c))
            ->unique();
        if ($activeCycles->diff($validated['cycles'])->isNotEmpty()) {
            return back()->withInput()->withErrors([
                'cycles' => 'Un cycle ne peut pas être retiré tant qu’il contient des classes.',
            ]);
        }

        $validated['currency'] = strtoupper($validated['currency']);
        $validated['maintenance_mode'] = $request->boolean('maintenance_mode');
        unset($validated['logo']);

        if ($request->hasFile('logo')) {
            $newLogo = $request->file('logo')->store('school-logos', 'public');
            $oldLogo = $school->logo_path;
            $validated['logo_path'] = $newLogo;
        }

        $school->update($validated);

        if (isset($oldLogo) && $oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return redirect()->route('school.settings.index')->with('success', 'Les paramètres de l’établissement ont été enregistrés.');
    }

    public function updateCard(Request $request)
    {
        $school = $request->user()->school;

        if ($request->boolean('reset')) {
            $school->update(['card_settings' => null]);

            return redirect()->to(route('school.settings.index').'#carte')->with('success', 'La carte élève a été réinitialisée.');
        }

        $validated = $request->validate([
            'bg_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'text_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'line1' => ['nullable', 'string', 'max:80'],
            'line2' => ['nullable', 'string', 'max:80'],
            'line3' => ['nullable', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:60'],
        ]);

        $school->update(['card_settings' => $validated]);

        return redirect()->to(route('school.settings.index').'#carte')->with('success', 'La carte élève a été mise à jour.');
    }

    public function updateAppearance(Request $request)
    {
        $school = $request->user()->school;

        if ($request->boolean('reset')) {
            $school->update(['appearance' => null]);

            return redirect()->to(route('school.settings.index').'#apparence')->with('success', 'L’apparence a été réinitialisée.');
        }

        $validated = $request->validate([
            'palette' => ['required', Rule::in(array_keys(config('appearance.palettes')))],
            'font' => ['required', Rule::in(array_keys(config('appearance.fonts')))],
            'heading_font' => ['required', Rule::in(array_keys(config('appearance.fonts')))],
            'font_size' => ['required', Rule::in(array_keys(config('appearance.font_sizes')))],
            'gradient' => ['required', Rule::in(array_keys(config('appearance.gradients')))],
            'sidebar' => ['required', Rule::in(array_keys(config('appearance.sidebars')))],
        ]);
        $validated['animated'] = $request->boolean('animated');

        $school->update(['appearance' => $validated]);

        return redirect()->to(route('school.settings.index').'#apparence')->with('success', 'L’apparence de l’interface a été enregistrée.');
    }

    public function storePhoto(Request $request)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'extensions:jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);

        $school = $request->user()->school;
        $path = $request->file('photo')->storePublicly('schools/'.$school->id.'/photos', 'public');

        if (!$path) {
            throw new RuntimeException('La photo de l’établissement n’a pas pu être enregistrée.');
        }

        $photo = DB::transaction(function () use ($school, $validated, $path): SchoolPhoto {
            $isFirstPhoto = !$school->photos()->exists();

            return $school->photos()->create([
                'title' => $validated['title'] ?? null,
                'path' => $path,
                'is_primary' => $isFirstPhoto,
            ]);
        });

        return redirect()->route('school.settings.index')->with('success', 'La photo a été ajoutée.');
    }

    public function setPrimaryPhoto(Request $request, SchoolPhoto $photo)
    {
        $this->ensurePhotoBelongsToSchool($request, $photo);

        DB::transaction(function () use ($request, $photo): void {
            $request->user()->school->photos()->update(['is_primary' => false]);
            $photo->update(['is_primary' => true]);
        });

        return redirect()->route('school.settings.index')->with('success', 'La photo principale a été mise à jour.');
    }

    public function destroyPhoto(Request $request, SchoolPhoto $photo)
    {
        $this->ensurePhotoBelongsToSchool($request, $photo);

        if (!Storage::disk('public')->delete($photo->path)) {
            throw new RuntimeException('La photo n’a pas pu être supprimée du stockage.');
        }

        DB::transaction(function () use ($request, $photo): void {
            $wasPrimary = $photo->is_primary;
            $school = $request->user()->school;
            $photo->delete();

            if ($wasPrimary) {
                $school->photos()->orderBy('id')->first()?->update(['is_primary' => true]);
            }
        });

        return redirect()->route('school.settings.index')->with('success', 'La photo a été supprimée.');
    }

    private function ensurePhotoBelongsToSchool(Request $request, SchoolPhoto $photo): void
    {
        abort_unless($photo->school_id === $request->user()->school_id, 404);
    }
}
