<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeachingAssignmentController extends Controller
{
    public function index()
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        $assignments = TeachingAssignment::with(['teacher', 'subject', 'classRoom'])
            ->when($currentYear, fn ($query) => $query->where('school_year_id', $currentYear->id))
            ->latest()
            ->get();

        $teachers = $currentYear
            ? Teacher::where('school_year_id', $currentYear->id)->orderBy('last_name')->orderBy('first_name')->get()
            : collect();
        $subjects = $currentYear
            ? Subject::where('school_year_id', $currentYear->id)->orderBy('name')->get()
            : collect();
        $classes = $currentYear
            ? ClassRoom::where('school_year_id', $currentYear->id)->orderBy('name')->get()
            : collect();

        return view('teaching_assignments.index', compact(
            'assignments',
            'currentYear',
            'teachers',
            'subjects',
            'classes'
        ));
    }

    public function store(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('teaching-assignments.index')
                ->with('error', 'Veuillez définir une année scolaire active avant de créer une affectation.');
        }

        $validated = $request->validate([
            'teacher_id' => [
                'required',
                Rule::exists('teachers', 'id')->where('school_year_id', $currentYear->id),
            ],
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')->where('school_year_id', $currentYear->id),
            ],
            'class_room_id' => [
                'required',
                Rule::exists('class_rooms', 'id')->where('school_year_id', $currentYear->id),
                Rule::unique('teaching_assignments', 'class_room_id')
                    ->where(fn (Builder $query) => $query
                        ->where('school_year_id', $currentYear->id)
                        ->where('subject_id', $request->input('subject_id'))
                        ->where('teacher_id', $request->input('teacher_id'))),
            ],
        ], [
            'teacher_id.exists' => 'Choisissez un enseignant de l’année scolaire active.',
            'subject_id.exists' => 'Choisissez une matière de l’année scolaire active.',
            'class_room_id.exists' => 'Choisissez une classe de l’année scolaire active.',
            'class_room_id.unique' => 'Cette matière est déjà affectée à cet enseignant dans cette classe.',
        ]);

        $validated['school_year_id'] = $currentYear->id;

        $created = DB::transaction(function () use ($validated, $currentYear): bool {
            $classRoom = ClassRoom::query()->whereKey($validated['class_room_id'])->lockForUpdate()->firstOrFail();

            if (in_array($classRoom->cycle, ['1er Cycle', 'Primaire'], true)) {
                $assignedTeacherIds = TeachingAssignment::query()
                    ->where('school_year_id', $currentYear->id)
                    ->where('class_room_id', $classRoom->id)
                    ->distinct()
                    ->pluck('teacher_id');
                $directTeacherIds = DB::table('teacher_class_assignments')
                    ->where('school_year_id', $currentYear->id)
                    ->where('class_room_id', $classRoom->id)
                    ->pluck('teacher_id');
                $assignedTeacherIds = $assignedTeacherIds->merge($directTeacherIds)->unique();

                if ($assignedTeacherIds->isNotEmpty() && ! $assignedTeacherIds->contains((int) $validated['teacher_id'])) {
                    return false;
                }
            }

            TeachingAssignment::create($validated);

            return true;
        });

        if (! $created) {
            return back()->withInput()->withErrors([
                'teacher_id' => 'Une classe du 1er Cycle doit conserver un seul enseignant pour toutes ses matières.',
            ]);
        }

        return redirect()->route('teaching-assignments.index')
            ->with('success', 'Affectation créée avec succès.');
    }

    public function destroy(TeachingAssignment $teachingAssignment)
    {
        $teachingAssignment->delete();

        return redirect()->route('teaching-assignments.index')
            ->with('success', 'Affectation supprimée avec succès.');
    }
}
