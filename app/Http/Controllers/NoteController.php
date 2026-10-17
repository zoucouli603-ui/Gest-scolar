<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Note;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
    private const TRIMESTERS = [
        1 => '1er trimestre',
        2 => '2e trimestre',
        3 => '3e trimestre',
    ];

    public function index(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $classes = $currentYear
            ? ClassRoom::where('school_year_id', $currentYear->id)->orderBy('name')->get()
            : collect();
        $allStudents = $currentYear
            ? Student::where('school_year_id', $currentYear->id)->orderBy('last_name')->orderBy('first_name')->get()
            : collect();
        $allStudents->each(function (Student $student) use ($classes) {
            $classRoomId = $student->class_room_id
                ?: $classes->first(fn (ClassRoom $classRoom) =>
                    mb_strtolower(trim($classRoom->name)) === mb_strtolower(trim((string) $student->class_name))
                )?->id;

            $student->setAttribute('notes_class_room_id', $classRoomId);
        });

        $selectedClassId = $request->integer('class_room_id') ?: null;
        $selectedStudentId = $request->integer('student_id') ?: null;
        $selectedSubjectId = $request->integer('subject_id') ?: null;
        $trimester = $request->integer('trimester') ?: 1;
        $evaluationMonth = $request->input('evaluation_month', now()->format('Y-m'));
        $students = collect();
        $selectedStudent = null;
        $assignments = collect();
        $periodNotes = collect();
        $selectedClass = null;
        $selectedSubject = null;
        $selectedAssignment = null;
        $selectedCoefficient = 1;
        $classGradeRows = collect();
        $classAverage = null;
        $firstAverage = null;

        if ($currentYear) {
            $request->validate([
                'class_room_id' => [
                    'nullable',
                    Rule::exists('class_rooms', 'id')->where('school_year_id', $currentYear->id),
                ],
                'student_id' => [
                    'nullable',
                    Rule::exists('students', 'id')->where('school_year_id', $currentYear->id),
                ],
                'trimester' => ['nullable', 'integer', 'between:1,3'],
                'evaluation_month' => ['nullable', 'date_format:Y-m'],
                'coefficient' => ['nullable', 'numeric', 'min:0.5', 'max:10'],
            ]);

            if ($selectedClassId) {
                $selectedClass = $classes->firstWhere('id', $selectedClassId);
                $students = $this->studentsForClass($selectedClass, $currentYear->id);
                $selectedStudent = $students->firstWhere('id', $selectedStudentId);
                $selectedStudentId = $selectedStudent?->id;

                $assignments = TeachingAssignment::with(['subject', 'teacher'])
                    ->where('school_year_id', $currentYear->id)
                    ->where('class_room_id', $selectedClass->id)
                    ->get()
                    ->unique('subject_id')
                    ->values();

                $selectedAssignment = $assignments->firstWhere('subject_id', $selectedSubjectId);
                $selectedSubject = $selectedAssignment?->subject;
                $selectedCoefficient = $request->filled('coefficient')
                    ? (float) $request->input('coefficient')
                    : (float) ($selectedAssignment?->coefficient ?? $selectedSubject?->coefficient ?? 1);
                if ($selectedSubject) {
                    $notesQuery = Note::query()
                        ->where('school_year_id', $currentYear->id)
                        ->where('class_room_id', $selectedClass->id)
                        ->where('subject_id', $selectedSubject->id);

                    if (in_array($selectedClass->cycle, ['1er Cycle', 'Primaire'], true)) {
                        $month = Carbon::createFromFormat('Y-m', $evaluationMonth);
                        $periodNotes = $notesQuery->whereBetween('note_date', [
                            $month->copy()->startOfMonth()->toDateString(),
                            $month->copy()->endOfMonth()->toDateString(),
                        ])->get();
                        $periodNotes = $periodNotes->filter(fn (Note $note) =>
                            mb_strtolower($note->evaluation_type) === 'composition'
                        )->values();
                    } else {
                        $periodNotes = $notesQuery->where('trimester', $trimester)->get();
                    }

                    $classScores = $periodNotes->filter(fn (Note $note) => in_array(mb_strtolower($note->evaluation_type), ['composition', 'devoir', 'controle', 'p1', 'p2'], true))
                        ->groupBy('student_id')->map(fn ($notes) => round($notes->avg(fn (Note $note) => (float) $note->score / max((float) $note->max_score, 1) * $selectedClass->grade_scale), 2));
                    $termScores = $periodNotes->filter(fn (Note $note) => in_array(mb_strtolower($note->evaluation_type), ['examen_trimestriel', 'examen', 'ex'], true))
                        ->groupBy('student_id')->map(fn ($notes) => round($notes->avg(fn (Note $note) => (float) $note->score / max((float) $note->max_score, 1) * $selectedClass->grade_scale), 2));
                    $scoresByStudent = $students->mapWithKeys(function (Student $student) use ($classScores, $termScores) {
                        $scores = collect([$classScores->get($student->id), $termScores->get($student->id)])->filter(fn ($score) => $score !== null);
                        return [$student->id => $scores->isNotEmpty() ? round($scores->avg(), 2) : null];
                    });
                    $orderedScores = $scoresByStudent->filter(fn ($score) => $score !== null)->sortDesc()->values();
                    $firstAverage = $orderedScores->first();
                    $classAverage = $orderedScores->isNotEmpty() ? round($orderedScores->avg(), 2) : null;
                    $appreciationsByStudent = $periodNotes->groupBy('student_id')->map(fn ($notes) => $notes->sortByDesc('note_date')->first()?->appreciation);
                    $isPrimary = in_array($selectedClass->cycle, ['1er Cycle', 'Primaire'], true);
                    $classGradeRows = $students->map(function (Student $student) use ($scoresByStudent, $classScores, $termScores, $orderedScores, $appreciationsByStudent, $isPrimary) {
                        $average = $scoresByStudent->get($student->id);
                        return [
                            'student' => $student,
                            'score' => $average,
                            'class_score' => $classScores->get($student->id),
                            'term_score' => $termScores->get($student->id),
                            'rank' => $average === null ? null : $orderedScores->search($average) + 1,
                            'appreciation' => $appreciationsByStudent->get($student->id) ?: ($this->gradeAppreciation($average, $isPrimary) ?? ''),
                        ];
                    });
                }
            }
        }

        $gradeScale = $selectedClass?->grade_scale;
        $evaluationTypes = match ($selectedClass?->cycle) {
            '1er Cycle', 'Primaire' => ['composition' => 'Composition mensuelle'],
            '2è Cycle', 'Secondaire', 'Lycée' => [
                'devoir' => 'Devoir',
                'controle' => 'Contrôle',
                'examen_trimestriel' => 'Examen trimestriel',
            ],
            default => [],
        };

        return view('notes.index', compact(
            'currentYear',
            'classes',
            'allStudents',
            'students',
            'selectedClassId',
            'selectedStudentId',
            'selectedSubjectId',
            'selectedStudent',
            'selectedClass',
            'trimester',
            'evaluationMonth',
            'assignments',
            'periodNotes',
            'gradeScale',
            'evaluationTypes',
            'selectedSubject',
            'selectedCoefficient',
            'classGradeRows',
            'classAverage',
            'firstAverage'
        ));
    }

    public function create()
    {
        return redirect()->route('notes.index');
    }

    public function store(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('notes.index')->with('error', 'Aucune année scolaire active trouvée.');
        }

        if ($request->hasAny(['class_scores', 'term_scores'])) {
            $validated = $request->validate([
                'class_room_id' => ['required', Rule::exists('class_rooms', 'id')->where('school_year_id', $currentYear->id)],
                'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_year_id', $currentYear->id)],
                'evaluation_type' => ['required', Rule::in(['composition', 'devoir', 'controle', 'examen_trimestriel'])],
                'trimester' => ['nullable', 'integer', 'between:1,3'],
                'evaluation_month' => ['nullable', 'date_format:Y-m'],
                'note_date' => ['nullable', 'date'],
                'class_scores' => ['array'],
                'class_scores.*' => ['nullable', 'numeric', 'min:0'],
                'term_scores' => ['array'],
                'term_scores.*' => ['nullable', 'numeric', 'min:0'],
                'appreciations' => ['array'],
                'appreciations.*' => ['nullable', 'string', 'max:500'],
                'coefficient' => ['nullable', 'numeric', 'min:0.5', 'max:10'],
            ]);
            $classRoom = ClassRoom::findOrFail($validated['class_room_id']);
            $gradeScale = $classRoom->grade_scale;
            abort_if($gradeScale === null, 422, 'La maternelle ne prévoit pas de notes chiffrées.');
            $students = $this->studentsForClass($classRoom, $currentYear->id)->keyBy('id');
            $assignment = TeachingAssignment::where('school_year_id', $currentYear->id)
                ->where('class_room_id', $classRoom->id)
                ->where('subject_id', $validated['subject_id'])
                ->firstOrFail();
            if (array_key_exists('coefficient', $validated) && $validated['coefficient'] !== null) {
                $assignment->coefficient = $validated['coefficient'];
                $assignment->save();
            }
            $isPrimary = in_array($classRoom->cycle, ['1er Cycle', 'Primaire'], true);
            $trimester = $isPrimary ? 1 : (int) ($validated['trimester'] ?? 1);
            $noteDate = $isPrimary
                ? Carbon::createFromFormat('Y-m', $validated['evaluation_month'] ?? now()->format('Y-m'))->startOfMonth()->toDateString()
                : ($validated['note_date'] ?? now()->toDateString());
            foreach ($students as $studentId => $student) {
                $classScore = $validated['class_scores'][$studentId] ?? null;
                $termScore = $validated['term_scores'][$studentId] ?? null;
                $scoresToSave = [];
                if ($isPrimary) {
                    $scoresToSave[] = ['score' => $classScore, 'type' => 'composition'];
                } else {
                    $scoresToSave[] = ['score' => $classScore, 'type' => 'devoir'];
                    $scoresToSave[] = ['score' => $termScore, 'type' => 'examen_trimestriel'];
                }
                foreach ($scoresToSave as $scoreData) {
                    $score = $scoreData['score'];
                    if ($score === null || $score === '') {
                        continue;
                    }
                    abort_if((float) $score > $gradeScale, 422, "La note ne peut pas dépasser {$gradeScale}.");
                abort_unless($students->has($studentId), 422, 'Un élève sélectionné n’appartient pas à cette classe.');
                $noteQuery = Note::where('school_year_id', $currentYear->id)
                    ->where('class_room_id', $classRoom->id)
                    ->where('student_id', $studentId)
                    ->where('subject_id', $validated['subject_id'])
                    ->where('evaluation_type', $scoreData['type']);
                $note = $isPrimary
                    ? $noteQuery->whereDate('note_date', $noteDate)->first()
                    : $noteQuery->where('trimester', $trimester)->first();
                $note ??= new Note;
                $note->fill([
                    'school_year_id' => $currentYear->id, 'class_room_id' => $classRoom->id,
                    'student_id' => $studentId, 'subject_id' => $validated['subject_id'],
                    'teacher_id' => $assignment->teacher_id, 'evaluation_type' => $scoreData['type'],
                    'trimester' => $trimester, 'score' => $score, 'max_score' => $gradeScale,
                    'appreciation' => $validated['appreciations'][$studentId] ?? null, 'note_date' => $noteDate,
                ])->save();
                }
                $scoresToSave = [];
            }
            return redirect()->route('notes.index', [
                'class_room_id' => $classRoom->id, 'subject_id' => $validated['subject_id'],
                ...($isPrimary ? ['evaluation_month' => $validated['evaluation_month'] ?? now()->format('Y-m')] : ['trimester' => $trimester]),
            ])->with('success', 'Les notes de la classe ont été enregistrées.');
        }

        $validated = $request->validate([
            'class_room_id' => [
                'required',
                Rule::exists('class_rooms', 'id')->where('school_year_id', $currentYear->id),
            ],
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('school_year_id', $currentYear->id),
            ],
            'subject_id' => [
                'required',
                Rule::exists('subjects', 'id')->where('school_year_id', $currentYear->id),
            ],
            'evaluation_type' => ['required', Rule::in(['composition', 'devoir', 'controle', 'examen_trimestriel'])],
            'trimester' => ['nullable', 'integer', 'between:1,3'],
            'evaluation_month' => ['nullable', 'date_format:Y-m'],
            'note_date' => ['nullable', 'date'],
            'score' => ['required', 'numeric', 'min:0'],
            'coefficient' => ['nullable', 'numeric', 'min:0.5', 'max:10'],
        ]);

        $classRoom = ClassRoom::findOrFail($validated['class_room_id']);
        $gradeScale = $classRoom->grade_scale;
        abort_if($gradeScale === null, 422, 'La maternelle ne prévoit pas de notes chiffrées.');
        abort_if((float) $validated['score'] > $gradeScale, 422, "La note ne peut pas dépasser {$gradeScale}.");

        if (in_array($classRoom->cycle, ['1er Cycle', 'Primaire'], true)) {
            abort_unless($validated['evaluation_type'] === 'composition', 422, 'Au 1er Cycle, seule la Composition mensuelle est autorisée.');
            validator($validated, [
                'evaluation_month' => ['required', 'date_format:Y-m'],
            ])->validate();
            $month = Carbon::createFromFormat('Y-m', $validated['evaluation_month']);
            $noteDate = $month->copy()->startOfMonth()->toDateString();
            $trimester = 1;
        } else {
            abort_unless(in_array($validated['evaluation_type'], ['devoir', 'controle', 'examen_trimestriel'], true), 422);
            validator($validated, [
                'trimester' => ['required', 'integer', 'between:1,3'],
                'note_date' => ['required', 'date'],
            ])->validate();
            $trimester = (int) $validated['trimester'];
            $noteDate = $validated['note_date'];
        }

        $student = Student::findOrFail($validated['student_id']);
        abort_unless(
            $this->studentsForClass($classRoom, $currentYear->id)->contains('id', $student->id),
            422,
            'Cet élève n’appartient pas à la classe sélectionnée.'
        );

        $assignment = TeachingAssignment::query()
            ->where('school_year_id', $currentYear->id)
            ->where('class_room_id', $classRoom->id)
            ->where('subject_id', $validated['subject_id'])
            ->firstOrFail();

        if (array_key_exists('coefficient', $validated) && $validated['coefficient'] !== null) {
            $assignment->coefficient = $validated['coefficient'];
            $assignment->save();
        }

        DB::transaction(function () use ($validated, $classRoom, $currentYear, $assignment, $trimester, $noteDate) {
            $note = null;
            if (in_array($classRoom->cycle, ['1er Cycle', 'Primaire'], true)) {
                $month = Carbon::parse($noteDate);
                $note = Note::query()
                    ->where('school_year_id', $currentYear->id)
                    ->where('class_room_id', $classRoom->id)
                    ->where('student_id', $validated['student_id'])
                    ->where('subject_id', $validated['subject_id'])
                    ->where('evaluation_type', 'composition')
                    ->whereBetween('note_date', [
                        $month->copy()->startOfMonth()->toDateString(),
                        $month->copy()->endOfMonth()->toDateString(),
                    ])
                    ->first();
            } elseif ($validated['evaluation_type'] === 'examen_trimestriel') {
                $note = Note::query()
                    ->where('school_year_id', $currentYear->id)
                    ->where('class_room_id', $classRoom->id)
                    ->where('student_id', $validated['student_id'])
                    ->where('subject_id', $validated['subject_id'])
                    ->where('trimester', $trimester)
                    ->where('evaluation_type', 'examen_trimestriel')
                    ->first();
            }

            $note ??= new Note;
            $note->fill([
                'school_year_id' => $currentYear->id,
                'class_room_id' => $classRoom->id,
                'student_id' => $validated['student_id'],
                'subject_id' => $validated['subject_id'],
                'teacher_id' => $assignment->teacher_id,
                'evaluation_type' => $validated['evaluation_type'],
                'trimester' => $trimester,
                'score' => $validated['score'],
                'max_score' => $classRoom->grade_scale,
                'note_date' => $noteDate,
            ])->save();
        });

        $query = [
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
        ];
        if (in_array($classRoom->cycle, ['1er Cycle', 'Primaire'], true)) {
            $query['evaluation_month'] = $validated['evaluation_month'];
        } else {
            $query['trimester'] = $trimester;
        }

        return redirect()->route('notes.index', $query)->with('success', 'L’évaluation a été enregistrée.');
    }

    public function bulletin(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->firstOrFail();
        $validated = $request->validate([
            'class_room_id' => [
                'required',
                Rule::exists('class_rooms', 'id')->where('school_year_id', $currentYear->id),
            ],
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('school_year_id', $currentYear->id),
            ],
            'trimester' => ['nullable', 'integer', 'between:1,3'],
            'evaluation_month' => ['nullable', 'date_format:Y-m'],
        ]);

        $classRoom = ClassRoom::findOrFail($validated['class_room_id']);
        $student = Student::findOrFail($validated['student_id']);
        abort_if($classRoom->grade_scale === null, 422, 'La maternelle ne prévoit pas de bulletin de notes chiffrées.');
        abort_unless(
            $this->studentsForClass($classRoom, $currentYear->id)->contains('id', $student->id),
            404
        );

        $isPrimary = in_array($classRoom->cycle, ['1er Cycle', 'Primaire'], true);
        $month = null;
        if ($isPrimary) {
            validator($validated, ['evaluation_month' => ['required', 'date_format:Y-m']])->validate();
            $month = Carbon::createFromFormat('Y-m', $validated['evaluation_month']);
            $trimester = 1;
            $periodNotes = Note::query()
                ->where('school_year_id', $currentYear->id)
                ->where('class_room_id', $classRoom->id)
                ->where('student_id', $student->id)
                ->whereBetween('note_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->get()
                ->filter(fn (Note $note) => mb_strtolower($note->evaluation_type) === 'composition')
                ->values();
            $trimesterLabel = 'Composition de '.$month->locale('fr')->translatedFormat('F Y');
        } else {
            validator($validated, ['trimester' => ['required', 'integer', 'between:1,3']])->validate();
            $trimester = (int) $validated['trimester'];
            $periodNotes = Note::query()
                ->where('school_year_id', $currentYear->id)
                ->where('class_room_id', $classRoom->id)
                ->where('student_id', $student->id)
                ->where('trimester', $trimester)
                ->get();
            $trimesterLabel = self::TRIMESTERS[$trimester];
        }

        $assignments = TeachingAssignment::with('subject')
            ->where('school_year_id', $currentYear->id)
            ->where('class_room_id', $classRoom->id)
            ->get()
            ->unique('subject_id')
            ->values();
        $weightedTotal = 0;
        $coefficientTotal = 0;
        $bulletinRows = $assignments->map(function (TeachingAssignment $assignment) use ($periodNotes, $isPrimary, $classRoom, &$weightedTotal, &$coefficientTotal) {
            $subjectNotes = $periodNotes->where('subject_id', $assignment->subject_id);
            $continuousTypes = $isPrimary ? ['composition'] : ['devoir', 'controle', 'P1', 'P2'];
            $examTypes = $isPrimary ? [] : ['examen_trimestriel', 'examen', 'EX'];
            $continuousNotes = $subjectNotes->filter(fn (Note $note) =>
                in_array(mb_strtolower($note->evaluation_type), array_map('mb_strtolower', $continuousTypes), true)
            );
            $examNotes = $subjectNotes->filter(fn (Note $note) =>
                in_array(mb_strtolower($note->evaluation_type), array_map('mb_strtolower', $examTypes), true)
            );

            $normalize = fn (Note $note) => (float) $note->score / max((float) $note->max_score, 1) * $classRoom->grade_scale;
            $continuous = $continuousNotes->isNotEmpty()
                ? round($continuousNotes->avg($normalize), 2)
                : null;
            $exam = $examNotes->isNotEmpty() ? round($examNotes->avg($normalize), 2) : null;
            $allGrades = $continuousNotes->concat($examNotes)->map($normalize);
            $average = $allGrades->isNotEmpty() ? round($allGrades->avg(), 2) : null;
            $coefficient = (float) ($assignment->coefficient ?: 1);

            if ($average !== null) {
                $weightedTotal += $average * $coefficient;
                $coefficientTotal += $coefficient;
            }

            return [
                'subject' => $assignment->subject,
                'continuous' => $continuous,
                'exam' => $exam,
                'average' => $average,
                'appreciation' => $this->gradeAppreciation($average, $isPrimary),
                'coefficient' => $coefficient,
            ];
        });

        $generalAverage = $coefficientTotal > 0 ? round($weightedTotal / $coefficientTotal, 2) : null;
        $primaryReports = $isPrimary
            ? $this->primaryReports($classRoom, $currentYear, $assignments, $this->studentsForClass($classRoom, $currentYear->id))
            : collect();
        $primaryReport = $primaryReports->get($student->id);
        $classPeriodNotesQuery = Note::query()
            ->where('school_year_id', $currentYear->id)
            ->where('class_room_id', $classRoom->id);
        if ($isPrimary) {
            $classPeriodNotesQuery->whereBetween('note_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
        } else {
            $classPeriodNotesQuery->where('trimester', $trimester);
        }
        $classPeriodNotes = $classPeriodNotesQuery->get();
        $classStudents = $this->studentsForClass($classRoom, $currentYear->id);
        $classAverages = $this->weightedClassAverages($assignments, $classPeriodNotes, $classStudents, $classRoom)
            ->filter(fn ($average) => $average !== null)
            ->sortDesc();
        $subjectRanks = $this->subjectRanks($assignments, $classPeriodNotes, $classStudents, $classRoom);
        $bulletinRows = $bulletinRows->map(function (array $row) use ($student, $subjectRanks) {
            $row['rank'] = $subjectRanks->get($row['subject']->id)?->get($student->id);
            return $row;
        });
        $studentRank = $classAverages->has($student->id) ? $classAverages->keys()->search($student->id) + 1 : null;
        $firstClassAverage = $classAverages->first();
        $classGeneralAverage = $classAverages->isNotEmpty() ? round($classAverages->avg(), 2) : null;
        $classStudentCount = $classStudents->count();
        $isSecondCycle = in_array($classRoom->cycle, ['2è Cycle', 'Secondaire'], true);
        $isHighSchool = $classRoom->cycle === 'Lycée';
        $studentAppreciation = $this->gradeAppreciation($generalAverage, $isPrimary);
        $schoolName = $request->user()->school->name;
        $gradeScale = $classRoom->grade_scale;
        $primaryAssignments = $assignments;

        return view('notes.bulletin', compact(
            'currentYear',
            'classRoom',
            'student',
            'bulletinRows',
            'generalAverage',
            'schoolName',
            'trimester',
            'trimesterLabel',
            'gradeScale',
            'isPrimary'
            ,'studentRank'
            ,'firstClassAverage'
            ,'classGeneralAverage'
            ,'studentAppreciation'
            ,'primaryReport'
            ,'primaryAssignments'
            ,'classStudentCount'
            ,'isSecondCycle'
            ,'isHighSchool'
        ));
    }

    public function classBulletin(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->firstOrFail();
        $validated = $request->validate([
            'class_room_id' => ['required', Rule::exists('class_rooms', 'id')->where('school_year_id', $currentYear->id)],
            'trimester' => ['nullable', 'integer', 'between:1,3'],
            'evaluation_month' => ['nullable', 'date_format:Y-m'],
        ]);
        $classRoom = ClassRoom::findOrFail($validated['class_room_id']);
        abort_if($classRoom->grade_scale === null, 422, 'La maternelle ne prévoit pas de bulletin chiffré.');
        $isPrimary = in_array($classRoom->cycle, ['1er Cycle', 'Primaire'], true);
        $trimester = $isPrimary ? 1 : (int) ($validated['trimester'] ?? 1);
        $month = $isPrimary
            ? Carbon::createFromFormat('Y-m', $validated['evaluation_month'] ?? now()->format('Y-m'))
            : null;
        $periodNotes = Note::query()
            ->where('school_year_id', $currentYear->id)
            ->where('class_room_id', $classRoom->id)
            ->when($isPrimary, fn ($query) => $query->whereBetween('note_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]))
            ->when(!$isPrimary, fn ($query) => $query->where('trimester', $trimester))
            ->get();
        $assignments = TeachingAssignment::with('subject')
            ->where('school_year_id', $currentYear->id)
            ->where('class_room_id', $classRoom->id)
            ->get()->unique('subject_id')->values();
        $students = $this->studentsForClass($classRoom, $currentYear->id);
        $primaryReports = $isPrimary
            ? $this->primaryReports($classRoom, $currentYear, $assignments, $students)
            : collect();
        $bulletins = $students->map(function (Student $student) use ($assignments, $periodNotes, $isPrimary, $classRoom, $primaryReports) {
            $weightedTotal = 0;
            $coefficientTotal = 0;
            $rows = $assignments->map(function (TeachingAssignment $assignment) use ($student, $periodNotes, $isPrimary, $classRoom, &$weightedTotal, &$coefficientTotal) {
                $notes = $periodNotes->where('student_id', $student->id)->where('subject_id', $assignment->subject_id);
                $continuousTypes = $isPrimary ? ['composition'] : ['devoir', 'controle', 'p1', 'p2'];
                $examTypes = $isPrimary ? [] : ['examen_trimestriel', 'examen', 'ex'];
                $normalize = fn (Note $note) => (float) $note->score / max((float) $note->max_score, 1) * $classRoom->grade_scale;
                $continuousNotes = $notes->filter(fn (Note $note) => in_array(mb_strtolower($note->evaluation_type), $continuousTypes, true));
                $examNotes = $notes->filter(fn (Note $note) => in_array(mb_strtolower($note->evaluation_type), $examTypes, true));
                $continuous = $continuousNotes->isNotEmpty() ? round($continuousNotes->avg($normalize), 2) : null;
                $exam = $examNotes->isNotEmpty() ? round($examNotes->avg($normalize), 2) : null;
                $all = $continuousNotes->concat($examNotes)->map($normalize);
                $average = $all->isNotEmpty() ? round($all->avg(), 2) : null;
                $coefficient = (float) ($assignment->coefficient ?: 1);
                if ($average !== null) {
                    $weightedTotal += $average * $coefficient;
                    $coefficientTotal += $coefficient;
                }
                return [
                    'subject' => $assignment->subject,
                    'continuous' => $continuous,
                    'exam' => $exam,
                    'average' => $average,
                    'appreciation' => $this->gradeAppreciation($average, $isPrimary),
                    'coefficient' => $coefficient,
                    'rank' => null,
                ];
            });
            return [
                'student' => $student,
                'rows' => $rows,
                'average' => $coefficientTotal > 0 ? round($weightedTotal / $coefficientTotal, 2) : null,
                'primaryReport' => $primaryReports->get($student->id),
            ];
        });
        $subjectRanks = $this->subjectRanks($assignments, $periodNotes, $students, $classRoom);
        $bulletins = $bulletins->map(function (array $bulletin) use ($subjectRanks) {
            $bulletin['rows'] = $bulletin['rows']->map(function (array $row) use ($bulletin, $subjectRanks) {
                $row['rank'] = $subjectRanks->get($row['subject']->id)?->get($bulletin['student']->id);
                return $row;
            });
            return $bulletin;
        });
        $ordered = $bulletins->filter(fn ($bulletin) => $bulletin['average'] !== null)->sortByDesc('average')->values();
        $bulletins = $bulletins->map(function ($bulletin) use ($ordered, $isPrimary) {
            $position = $ordered->search(fn ($item) => $item['student']->id === $bulletin['student']->id);
            $bulletin['rank'] = $position === false ? null : $position + 1;
            $bulletin['first_average'] = $ordered->first()['average'] ?? null;
            $bulletin['appreciation'] = $this->gradeAppreciation($bulletin['average'], $isPrimary);
            return $bulletin;
        });
        return view('notes.class-bulletin', [
            'currentYear' => $currentYear,
            'classRoom' => $classRoom,
            'bulletins' => $bulletins,
            'primaryAssignments' => $assignments,
            'trimesterLabel' => self::TRIMESTERS[$trimester],
            'gradeScale' => $classRoom->grade_scale,
            'schoolName' => $request->user()->school->name,
            'classAverage' => $ordered->isNotEmpty() ? round($ordered->avg('average'), 2) : null,
            'firstAverage' => $ordered->first()['average'] ?? null,
            'classStudentCount' => $students->count(),
            'isPrimary' => $isPrimary,
            'isSecondCycle' => in_array($classRoom->cycle, ['2è Cycle', 'Secondaire'], true),
            'isHighSchool' => $classRoom->cycle === 'Lycée',
        ]);
    }

    private function weightedClassAverages(
        Collection $assignments,
        Collection $periodNotes,
        Collection $students,
        ClassRoom $classRoom
    ): Collection {
        return $students->mapWithKeys(function (Student $student) use ($assignments, $periodNotes, $classRoom) {
            $weightedTotal = 0.0;
            $coefficientTotal = 0.0;

            foreach ($assignments as $assignment) {
                $subjectNotes = $periodNotes->where('student_id', $student->id)->where('subject_id', $assignment->subject_id);
                if ($subjectNotes->isEmpty()) {
                    continue;
                }

                $average = $subjectNotes->avg(
                    fn (Note $note) => (float) $note->score / max((float) $note->max_score, 1) * $classRoom->grade_scale
                );
                $coefficient = (float) ($assignment->coefficient ?: 1);
                $weightedTotal += $average * $coefficient;
                $coefficientTotal += $coefficient;
            }

            return [$student->id => $coefficientTotal > 0 ? round($weightedTotal / $coefficientTotal, 2) : null];
        });
    }

    private function subjectRanks(
        Collection $assignments,
        Collection $periodNotes,
        Collection $students,
        ClassRoom $classRoom
    ): Collection {
        return $assignments->mapWithKeys(function (TeachingAssignment $assignment) use ($periodNotes, $students, $classRoom) {
            $averages = $students->mapWithKeys(function (Student $student) use ($assignment, $periodNotes, $classRoom) {
                $notes = $periodNotes
                    ->where('student_id', $student->id)
                    ->where('subject_id', $assignment->subject_id)
                    ->filter(fn (Note $note) => in_array(mb_strtolower($note->evaluation_type), ['devoir', 'controle', 'p1', 'p2', 'examen_trimestriel', 'examen', 'ex'], true));

                return [$student->id => $notes->isNotEmpty()
                    ? round($notes->avg(fn (Note $note) => (float) $note->score / max((float) $note->max_score, 1) * $classRoom->grade_scale), 2)
                    : null];
            })->filter(fn ($average) => $average !== null)->sortDesc();

            $ranks = collect();
            $previousAverage = null;
            $rank = 0;
            foreach ($averages as $studentId => $average) {
                if ($previousAverage === null || $average !== $previousAverage) {
                    $rank = $ranks->count() + 1;
                    $previousAverage = $average;
                }
                $ranks->put($studentId, $rank);
            }

            return [$assignment->subject_id => $ranks];
        });
    }

    private function gradeAppreciation(?float $average, bool $isPrimary = false): ?string
    {
        if ($average === null) {
            return null;
        }

        if ($isPrimary) {
            return match (true) {
                $average < 2 => 'Nul',
                $average < 4 => 'Faible/Mauvais',
                $average < 5 => 'Insuffisant',
                $average < 6 => 'Moyen',
                $average < 7 => 'Assez-bien',
                $average < 8 => 'Bon',
                $average < 9 => 'Très bon',
                $average < 10 => 'Excellent',
                default => 'Parfait',
            };
        }

        return match (true) {
            $average < 6 => 'Médiocre/Nul',
            $average < 8 => 'Faible',
            $average < 10 => 'Insuffisant',
            $average < 12 => 'Passable',
            $average < 14 => 'Assez-bien',
            $average < 16 => 'Bien',
            $average < 18 => 'Très-bien',
            default => 'Excellent',
        };
    }

    private function primaryReports(
        ClassRoom $classRoom,
        SchoolYear $schoolYear,
        Collection $assignments,
        Collection $students
    ): Collection {
        preg_match('/^(\d{4})/', $schoolYear->name, $yearMatch);
        $startYear = isset($yearMatch[1]) ? (int) $yearMatch[1] : now()->year;
        $months = collect(range(0, 8))->map(
            fn (int $offset) => Carbon::create($startYear, 10, 1)->addMonths($offset)
        );
        $notesByMonth = Note::query()
            ->where('school_year_id', $schoolYear->id)
            ->where('class_room_id', $classRoom->id)
            ->whereBetween('note_date', [
                $months->first()->copy()->startOfMonth()->toDateString(),
                $months->last()->copy()->endOfMonth()->toDateString(),
            ])
            ->get()
            ->filter(fn (Note $note) => mb_strtolower($note->evaluation_type) === 'composition')
            ->groupBy(fn (Note $note) => $note->note_date->format('Y-m'));

        $reports = $students->mapWithKeys(fn (Student $student) => [
            $student->id => [
                'student' => $student,
                'months' => [],
                'annual_subjects' => [],
                'annual_points' => null,
                'annual_average' => null,
                'annual_rank' => null,
                'annual_appreciation' => null,
            ],
        ]);
        $monthKeys = $months->map(fn (Carbon $month) => $month->format('Y-m'));

        foreach ($monthKeys as $monthIndex => $monthKey) {
            $monthNotes = $notesByMonth->get($monthKey, collect());
            $monthAverages = [];
            foreach ($students as $student) {
                $notes = $monthNotes->where('student_id', $student->id);
                $scores = [];
                $weightedTotal = 0.0;
                $coefficientTotal = 0.0;

                foreach ($assignments as $assignment) {
                    $subjectNotes = $notes->where('subject_id', $assignment->subject_id);
                    $score = $subjectNotes->isNotEmpty()
                        ? round($subjectNotes->avg(fn (Note $note) => (float) $note->score / max((float) $note->max_score, 1) * $classRoom->grade_scale), 2)
                        : null;
                    $scores[$assignment->subject_id] = $score;

                    if ($score !== null) {
                        $coefficient = (float) ($assignment->coefficient ?: 1);
                        $weightedTotal += $score * $coefficient;
                        $coefficientTotal += $coefficient;
                    }
                }

                $average = $coefficientTotal > 0 ? round($weightedTotal / $coefficientTotal, 2) : null;
                $monthAverages[$student->id] = $average;
                $report = $reports->get($student->id);
                $report['months'][$monthIndex] = [
                    'label' => $months[$monthIndex]->locale('fr')->translatedFormat('M'),
                    'scores' => $scores,
                    'points' => $coefficientTotal > 0 ? round($weightedTotal, 2) : null,
                    'average' => $average,
                    'appreciation' => $this->gradeAppreciation($average, true),
                ];
                $reports->put($student->id, $report);
            }

            $ranked = collect($monthAverages)
                ->filter(fn ($average) => $average !== null)
                ->sortDesc()
                ->values();
            $classAverage = $ranked->isNotEmpty() ? round($ranked->avg(), 2) : null;
            $firstAverage = $ranked->first();
            foreach ($monthAverages as $studentId => $average) {
                $rank = $average === null ? null : $ranked->search($average) + 1;
                $report = $reports->get($studentId);
                $report['months'][$monthIndex]['rank'] = $rank;
                $report['months'][$monthIndex]['class_average'] = $classAverage;
                $report['months'][$monthIndex]['first_average'] = $firstAverage;
                $reports->put($studentId, $report);
            }
        }

        $annualAverages = [];
        foreach ($students as $student) {
            $report = $reports[$student->id];
            $weightedTotal = 0.0;
            $coefficientTotal = 0.0;
            foreach ($assignments as $assignment) {
                $subjectScores = collect($report['months'])
                    ->pluck('scores.'.$assignment->subject_id)
                    ->filter(fn ($score) => $score !== null);
                $subjectAverage = $subjectScores->isNotEmpty()
                    ? round($subjectScores->avg(), 2)
                    : null;
                $report['annual_subjects'][$assignment->subject_id] = $subjectAverage;

                if ($subjectAverage !== null) {
                    $coefficient = (float) ($assignment->coefficient ?: 1);
                    $weightedTotal += $subjectAverage * $coefficient;
                    $coefficientTotal += $coefficient;
                }
            }
            $report['annual_average'] = $coefficientTotal > 0
                ? round($weightedTotal / $coefficientTotal, 2)
                : null;
            $report['annual_points'] = $coefficientTotal > 0 ? round($weightedTotal, 2) : null;
            $annualAverages[$student->id] = $report['annual_average'];
            $reports->put($student->id, $report);
        }

        $rankedAnnual = collect($annualAverages)
            ->filter(fn ($average) => $average !== null)
            ->sortDesc()
            ->values();
        foreach ($reports as $studentId => $report) {
            $average = $report['annual_average'];
            $rank = $average === null ? null : $rankedAnnual->search($average) + 1;
            $report['annual_rank'] = $rank;
            $report['annual_appreciation'] = $this->gradeAppreciation($average, true);
            $reports->put($studentId, $report);
        }

        return $reports;
    }

    public function destroy(Request $request, Note $note)
    {
        abort_unless($note->school_year_id === $request->user()->school->currentSchoolYear?->id, 404);
        $classRoom = ClassRoom::find($note->class_room_id);
        $note->delete();

        $query = ['class_room_id' => $note->class_room_id, 'student_id' => $note->student_id];
        if (in_array($classRoom?->cycle, ['1er Cycle', 'Primaire'], true)) {
            $query['evaluation_month'] = $note->note_date?->format('Y-m');
        } else {
            $query['trimester'] = $note->trimester;
        }

        return redirect()->route('notes.index', $query)->with('success', 'Note supprimée avec succès.');
    }

    private function studentsForClass(ClassRoom $classRoom, int $schoolYearId)
    {
        return Student::query()
            ->where('school_year_id', $schoolYearId)
            ->where(function ($query) use ($classRoom) {
                $query->where('class_room_id', $classRoom->id)
                    ->orWhere(function ($legacyQuery) use ($classRoom) {
                        $legacyQuery->whereNull('class_room_id')
                            ->whereRaw('LOWER(TRIM(class_name)) = ?', [mb_strtolower(trim($classRoom->name))]);
                    });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }
}
