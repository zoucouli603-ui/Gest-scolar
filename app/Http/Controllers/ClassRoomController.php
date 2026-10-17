<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Support\SearchFilter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassRoomController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $query = ClassRoom::query();
        $cycles = array_values(array_intersect(ClassRoom::CYCLES, School::normalizeCycles($request->user()->school->cycles ?? ClassRoom::CYCLES)));

        if ($currentYear) {
            $query->where('school_year_id', $currentYear->id);
        }

        if ($request->filled('cycle') && in_array($request->cycle, $cycles, true)) {
            $query->where('cycle', $request->cycle);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            SearchFilter::apply($query, $search, ['name', 'cycle']);
        }

        $classes = $query->orderBy('name')->get();
        $totalClasses = $classes->count();

        $classes->each(function ($classe) {
            $classe->students_count = $classe->students()->count();
        });

        return view('classes.index', compact('classes', 'currentYear', 'totalClasses', 'cycles'));
    }

    public function create()
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (!$currentYear) {
            return redirect()->route('classes.index')
                ->with('error', 'Veuillez définir une année scolaire active avant d\'ajouter une classe.');
        }

        $cycles = array_values(array_intersect(ClassRoom::CYCLES, School::normalizeCycles(request()->user()->school->cycles ?? ClassRoom::CYCLES)));

        return view('classes.create', compact('currentYear', 'cycles'));
    }

    public function store(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (!$currentYear) {
            return redirect()->route('classes.index')
                ->with('error', 'Aucune année scolaire active trouvée.');
        }

        $availableCycles = [...School::normalizeCycles($request->user()->school->cycles ?? ClassRoom::CYCLES), 'Primaire', 'Secondaire'];
        $validated = $request->validate([
            'cycle' => ['required', Rule::in($availableCycles)],
            'name' => ['required', 'string', 'max:255'],
            'monthly_fee' => ['required', 'integer', 'min:0', 'max:999999999'],
        ]);

        $validated['cycle'] = School::normalizeCycle($validated['cycle']);
        $validated['school_year_id'] = $currentYear->id;

        ClassRoom::create($validated);

        return redirect()->route('classes.index')->with('success', 'Classe créée avec succès !');
    }

    public function edit(Request $request, ClassRoom $classe)
    {
        $cycles = array_values(array_intersect(ClassRoom::CYCLES, School::normalizeCycles($request->user()->school->cycles ?? ClassRoom::CYCLES)));

        return view('classes.edit', compact('classe', 'cycles'));
    }

    public function update(Request $request, ClassRoom $classe)
    {
        $availableCycles = [...School::normalizeCycles($request->user()->school->cycles ?? ClassRoom::CYCLES), 'Primaire', 'Secondaire'];
        $validated = $request->validate([
            'cycle' => ['required', Rule::in($availableCycles)],
            'name' => ['required', 'string', 'max:255'],
            'monthly_fee' => ['required', 'integer', 'min:0', 'max:999999999'],
        ]);

        $validated['cycle'] = School::normalizeCycle($validated['cycle']);
        $classe->update($validated);

        return redirect()->route('classes.index')->with('success', 'Classe mise à jour avec succès.');
    }

    public function show(ClassRoom $classe)
    {
        $teachingAssignments = $classe->teachingAssignments()
            ->with(['teacher', 'subject'])
            ->get();
        $assignedTeachers = $classe->assignedTeachers()
            ->wherePivot('school_year_id', $classe->school_year_id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $students = $this->sortedStudents($classe);
        $canPrintGradeSheet = $this->supportsGradeSheet($classe);

        return view('classes.show', compact('classe', 'students', 'teachingAssignments', 'assignedTeachers', 'canPrintGradeSheet'));
    }

    public function printStudents(Request $request, ClassRoom $classe)
    {
        return view('classes.print-students', [
            'classe' => $classe,
            'students' => $this->sortedStudents($classe),
            'schoolName' => $request->user()->school->name,
            'schoolYear' => $classe->schoolYear,
        ]);
    }

    public function printGradeSheet(Request $request, ClassRoom $classe)
    {
        abort_unless($this->supportsGradeSheet($classe), 404, 'La fiche de notes est disponible uniquement pour le 2è Cycle et le Lycée.');

        return view('classes.print-grade-sheet', [
            'classe' => $classe,
            'students' => $this->sortedStudents($classe),
            'schoolName' => $request->user()->school->name,
            'schoolYear' => $classe->schoolYear,
        ]);
    }

    private function supportsGradeSheet(ClassRoom $classe): bool
    {
        return in_array(School::normalizeCycle((string) $classe->cycle), ['2è Cycle', 'Lycée'], true);
    }

    private function sortedStudents(ClassRoom $classe)
    {
        return Student::query()
            ->where(function ($query) use ($classe) {
                $query->where('class_room_id', $classe->id)
                    ->orWhere(function ($unlinkedStudents) use ($classe) {
                        $unlinkedStudents->whereNull('class_room_id')
                            ->where(function ($names) use ($classe) {
                                $names->where('class_name', $classe->name)
                                    ->orWhereRaw('LOWER(TRIM(class_name)) = ?', [mb_strtolower(trim($classe->name))])
                                    ->orWhereRaw('LOWER(REPLACE(TRIM(class_name), " ", "")) = ?', [str_replace(' ', '', mb_strtolower(trim($classe->name)))]);
                            });
                    });
            })
            ->where(function ($query) use ($classe) {
                $query->where('school_year_id', $classe->school_year_id)
                    ->orWhereNull('school_year_id');
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function destroy(ClassRoom $classe)
    {
        if (!$classe->delete()) {
            return redirect()->route('classes.index')
                ->with('error', 'La classe n’a pas pu être supprimée.');
        }

        return redirect()->route('classes.index')->with('success', 'Classe supprimée avec succès !');
    }
}