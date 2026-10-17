<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Subject;
use App\Support\SearchFilter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $query = Subject::query();

        if ($currentYear) {
            $query->where('school_year_id', $currentYear->id);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            SearchFilter::apply($query, $search, ['name', 'code', 'description']);
        }

        $subjects = $query->latest()->get();
        $totalSubjects = (clone $query)->count();

        return view('subject_index', compact('subjects', 'currentYear', 'totalSubjects'));
    }

    public function create()
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('subjects.index')
                ->with('error', 'Veuillez définir une année scolaire active avant d\'ajouter une matière.');
        }

        return view('subject_create', compact('currentYear'));
    }

    public function store(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('subjects.index')
                ->with('error', 'Aucune année scolaire active trouvée.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')->where('school_year_id', $currentYear->id),
            ],
            'coefficient' => 'nullable|numeric|min:0.5|max:10',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['school_year_id'] = $currentYear->id;

        Subject::create($validated);

        return redirect()->route('subjects.index')
            ->with('success', 'Matière ajoutée avec succès.');
    }

    public function show(Subject $subject)
    {
        return view('subject_show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        return view('subject_edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')->ignore($subject->id)->where('school_year_id', $subject->school_year_id),
            ],
            'coefficient' => 'nullable|numeric|min:0.5|max:10',
            'description' => 'nullable|string|max:1000',
        ]);

        $subject->update($validated);

        return redirect()->route('subjects.index')
            ->with('success', 'Matière mise à jour avec succès.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Matière supprimée avec succès.');
    }
}
