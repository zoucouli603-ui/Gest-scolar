<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Note;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TeachingAssignment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SyncNotesRequest;

class SyncController extends Controller
{
    public function store(SyncNotesRequest $request)
    {
        $validated = $request->validated();

        $currentYear = SchoolYear::where('is_current', true)->firstOrFail();
        $saved = 0;

        foreach ($validated['notes'] as $notePayload) {
            $classRoom = ClassRoom::findOrFail($notePayload['class_room_id']);
            $gradeScale = $classRoom->grade_scale;
            abort_if($gradeScale === null, 422, 'La maternelle ne prévoit pas de notes chiffrées.');
            abort_if((float) $notePayload['score'] > $gradeScale, 422, "La note ne peut pas dépasser {$gradeScale}.");

            $student = Student::findOrFail($notePayload['student_id']);
            abort_unless(
                $this->studentsForClass($classRoom, $currentYear->id)->contains('id', $student->id),
                422,
                'Cet élève n’appartient pas à la classe sélectionnée.'
            );

            $assignment = TeachingAssignment::query()
                ->where('school_year_id', $currentYear->id)
                ->where('class_room_id', $classRoom->id)
                ->where('subject_id', $notePayload['subject_id'])
                ->firstOrFail();

            if (array_key_exists('coefficient', $notePayload) && $notePayload['coefficient'] !== null) {
                $assignment->coefficient = $notePayload['coefficient'];
                $assignment->save();
            }

            if (in_array($classRoom->cycle, ['1er Cycle', 'Primaire'], true)) {
                abort_unless($notePayload['evaluation_type'] === 'composition', 422, 'Au 1er Cycle, seule la Composition mensuelle est autorisée.');
                $month = Carbon::createFromFormat('Y-m', $notePayload['evaluation_month'] ?? now()->format('Y-m'));
                $noteDate = $month->copy()->startOfMonth()->toDateString();
                $trimester = 1;
            } else {
                abort_unless(in_array($notePayload['evaluation_type'], ['devoir', 'controle', 'examen_trimestriel'], true), 422, 'Type de note invalide.');
                $trimester = (int) ($notePayload['trimester'] ?? 1);
                $noteDate = $notePayload['note_date'] ?? now()->toDateString();
            }

            DB::transaction(function () use ($currentYear, $classRoom, $assignment, $notePayload, $gradeScale, $trimester, $noteDate) {
                $note = null;

                if (in_array($classRoom->cycle, ['1er Cycle', 'Primaire'], true)) {
                    $month = Carbon::parse($noteDate);
                    $note = Note::query()
                        ->where('school_year_id', $currentYear->id)
                        ->where('class_room_id', $classRoom->id)
                        ->where('student_id', $notePayload['student_id'])
                        ->where('subject_id', $notePayload['subject_id'])
                        ->where('evaluation_type', 'composition')
                        ->whereBetween('note_date', [
                            $month->copy()->startOfMonth()->toDateString(),
                            $month->copy()->endOfMonth()->toDateString(),
                        ])
                        ->first();
                } elseif ($notePayload['evaluation_type'] === 'examen_trimestriel') {
                    $note = Note::query()
                        ->where('school_year_id', $currentYear->id)
                        ->where('class_room_id', $classRoom->id)
                        ->where('student_id', $notePayload['student_id'])
                        ->where('subject_id', $notePayload['subject_id'])
                        ->where('trimester', $trimester)
                        ->where('evaluation_type', 'examen_trimestriel')
                        ->first();
                }

                $note ??= new Note;
                $note->fill([
                    'school_year_id' => $currentYear->id,
                    'class_room_id' => $classRoom->id,
                    'student_id' => $notePayload['student_id'],
                    'subject_id' => $notePayload['subject_id'],
                    'teacher_id' => $assignment->teacher_id,
                    'evaluation_type' => $notePayload['evaluation_type'],
                    'trimester' => $trimester,
                    'score' => $notePayload['score'],
                    'max_score' => $gradeScale,
                    'note_date' => $noteDate,
                ])->save();
            });

            $saved++;
        }

        return response()->json([
            'message' => 'Synchronisation réussie.',
            'saved' => $saved,
            'total' => count($validated['notes']),
        ]);
    }

    protected function studentsForClass(ClassRoom $classRoom, int $schoolYearId)
    {
        return Student::query()
            ->where('school_year_id', $schoolYearId)
            ->where(function ($query) use ($classRoom) {
                $query->where('class_room_id', $classRoom->id)
                    ->orWhereRaw('LOWER(class_name) = ?', [mb_strtolower(trim((string) $classRoom->name))]);
            })
            ->get();
    }
}
