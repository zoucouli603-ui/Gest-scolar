<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolYearController extends Controller
{
    public function index()
    {
        $schoolYears = SchoolYear::latest()->get();
        return view('school_years.index', compact('schoolYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('school_years', 'name')->where('school_id', $request->user()->school_id),
            ],
        ], [
        'name.required' => 'Le libellé de l\'année scolaire est obligatoire.',
        'name.unique'   => 'Cette année scolaire existe déjà.',
        'name.max'      => 'Le libellé ne doit pas dépasser 255 caractères.',
    ]);

        // Si c'est la première année créée, on la met automatiquement active
        $isFirst = SchoolYear::count() === 0;

        SchoolYear::create([
            'name' => $validated['name'],
            'is_current' => $isFirst,
            'school_id' => $request->user()->school_id,
        ]);

        return redirect()->route('school-years.index')
            ->with('success', 'Année scolaire créée avec succès !');
    }

    public function setCurrent(Request $request, SchoolYear $schoolYear)
    {
        abort_unless($schoolYear->school_id === $request->user()->school_id, 404);

        // Désactiver toutes les autres années
        SchoolYear::where('school_id', $request->user()->school_id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        // Activer l'année sélectionnée
        $schoolYear->update(['is_current' => true]);

        return redirect()->route('school-years.index')
            ->with('success', "L'année {$schoolYear->name} est maintenant l'année en cours.");
    }

    public function close(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('school_years', 'name')->where('school_id', $request->user()->school_id),
            ],
        ], [
            'name.required' => 'Le libellé de la nouvelle année scolaire est obligatoire.',
            'name.unique' => 'Cette année scolaire existe déjà.',
        ]);

        $currentYear = SchoolYear::where('is_current', true)->first();
        if (! $currentYear) {
            return redirect()->route('school-years.index')
                ->with('error', 'Aucune année scolaire active ne peut être clôturée.');
        }

        DB::transaction(function () use ($currentYear, $validated, $request) {
            $newYear = SchoolYear::create([
                'name' => $validated['name'],
                'is_current' => true,
                'school_id' => $request->user()->school_id,
            ]);

            $classMap = [];
            foreach (ClassRoom::where('school_year_id', $currentYear->id)->get() as $oldClass) {
                $newClass = $oldClass->replicate();
                $newClass->school_year_id = $newYear->id;
                $newClass->save();
                $classMap[$oldClass->id] = $newClass->id;
            }

            $teacherMap = [];
            foreach (Teacher::where('school_year_id', $currentYear->id)->get() as $oldTeacher) {
                $newTeacher = $oldTeacher->replicate();
                $newTeacher->school_year_id = $newYear->id;
                $newTeacher->qr_token = null;
                $newTeacher->save();
                $teacherMap[$oldTeacher->id] = $newTeacher->id;
            }

            $subjectMap = [];
            foreach (Subject::where('school_year_id', $currentYear->id)->get() as $oldSubject) {
                $newSubject = $oldSubject->replicate();
                $newSubject->school_year_id = $newYear->id;
                $newSubject->save();
                $subjectMap[$oldSubject->id] = $newSubject->id;
            }

            foreach (Student::where('school_year_id', $currentYear->id)->get() as $oldStudent) {
                $newStudent = $oldStudent->replicate();
                $newStudent->school_year_id = $newYear->id;
                $newStudent->class_room_id = $classMap[$oldStudent->class_room_id] ?? null;
                $newStudent->class_name = $newStudent->class_room_id
                    ? ClassRoom::find($newStudent->class_room_id)?->name
                    : $oldStudent->class_name;
                $newStudent->inscription_date = now()->toDateString();
                $newStudent->save();
            }

            foreach (TeachingAssignment::where('school_year_id', $currentYear->id)->get() as $oldAssignment) {
                $newAssignment = $oldAssignment->replicate();
                $newAssignment->school_year_id = $newYear->id;
                $newAssignment->class_room_id = $classMap[$oldAssignment->class_room_id] ?? null;
                $newAssignment->teacher_id = $teacherMap[$oldAssignment->teacher_id] ?? null;
                $newAssignment->subject_id = $subjectMap[$oldAssignment->subject_id] ?? null;
                if ($newAssignment->class_room_id && $newAssignment->teacher_id && $newAssignment->subject_id) {
                    $newAssignment->save();
                }
            }

            foreach (DB::table('teacher_class_assignments')->where('school_year_id', $currentYear->id)->get() as $oldAssignment) {
                $newTeacherId = $teacherMap[$oldAssignment->teacher_id] ?? null;
                $newClassId = $classMap[$oldAssignment->class_room_id] ?? null;
                if ($newTeacherId && $newClassId) {
                    DB::table('teacher_class_assignments')->insert([
                        'teacher_id' => $newTeacherId,
                        'class_room_id' => $newClassId,
                        'school_year_id' => $newYear->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            SchoolYear::where('school_id', $request->user()->school_id)
                ->where('id', $currentYear->id)
                ->update(['is_current' => false]);
        });

        return redirect()->route('school-years.index')
            ->with('success', "L'année {$currentYear->name} a été clôturée. Les classes, élèves, enseignants, matières et affectations ont été reportés vers {$validated['name']}. Les notes, finances, emploi du temps et rapports repartent à zéro.");
    }

    public function destroy(Request $request, SchoolYear $schoolYear)
    {
        abort_unless($schoolYear->school_id === $request->user()->school_id, 404);

        // Empêcher la suppression de l'année active par sécurité
        if ($schoolYear->is_current) {
            return redirect()->route('school-years.index')
                ->with('error', "Impossible de supprimer l'année scolaire active.");
        }

        if ($schoolYear->payments()->exists()) {
            return redirect()->route('school-years.index')
                ->with('error', "Impossible de supprimer cette année scolaire : elle contient des paiements enregistrés.");
        }

        if ($schoolYear->expenses()->exists()) {
            return redirect()->route('school-years.index')
                ->with('error', "Impossible de supprimer cette année scolaire : elle contient des dépenses enregistrées.");
        }

        $schoolYear->delete();

        return redirect()->route('school-years.index')
            ->with('success', 'Année scolaire supprimée avec succès !');
    }
}