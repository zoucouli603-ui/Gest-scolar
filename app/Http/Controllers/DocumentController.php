<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Support\SearchFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DocumentController extends Controller
{
    private const DOCUMENTS = [
        'inscription' => 'Fiche d’inscription',
        'scolarite' => 'Certificat de scolarité',
        'transfert' => 'Certificat de transfert',
        'scolaire' => 'Fiche scolaire',
    ];

    public function index(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $query = Student::with(['classRoom', 'schoolYear'])
            ->when($currentYear, fn ($students) => $students->where('school_year_id', $currentYear->id));
        $totalStudents = (clone $query)->count();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            SearchFilter::apply($query, $search, ['first_name', 'last_name', 'registration_number', 'parent_name']);
        }

        if ($request->filled('class_name')) {
            $query->where('class_name', $request->string('class_name')->toString());
        }

        return view('documents.index', [
            'currentYear' => $currentYear,
            'students' => $query->orderBy('last_name')->orderBy('first_name')->paginate(12)->withQueryString(),
            'classes' => Student::query()
                ->when($currentYear, fn ($students) => $students->where('school_year_id', $currentYear->id))
                ->whereNotNull('class_name')
                ->distinct()
                ->orderBy('class_name')
                ->pluck('class_name'),
            'totalStudents' => $totalStudents,
        ]);
    }

    public function print(Request $request, Student $student, string $type)
    {
        abort_unless(array_key_exists($type, self::DOCUMENTS), 404);

        $validated = $request->validate([
            'destination' => ['nullable', 'string', 'max:255'],
            'transfer_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $student->load(['classRoom', 'schoolYear']);
        $school = $request->user()->school;
        $schoolHistory = $type === 'scolaire'
            ? Student::query()
                ->with(['schoolYear', 'classRoom'])
                ->where('registration_number', $student->registration_number)
                ->whereHas('schoolYear', fn ($query) => $query->where('school_id', $school->id))
                ->orderBy('school_year_id')
                ->orderBy('id')
                ->get()
                ->map(fn (Student $record) => $this->schoolHistoryRow($record))
                ->sortBy('school_year', SORT_NATURAL)
                ->values()
            : collect();
        $classKey = fn (array $row): string => mb_strtolower(trim($row['class']));
        $classAttempts = $schoolHistory->countBy($classKey);
        $classOccurrences = [];
        $schoolHistory = $schoolHistory->map(function (array $row) use (&$classOccurrences, $classAttempts, $classKey): array {
            $key = $classKey($row);
            $classOccurrences[$key] = ($classOccurrences[$key] ?? 0) + 1;
            $row['repeated'] = $classAttempts->get($key, 0) > 1 && $classOccurrences[$key] > 1;

            return $row;
        });
        $directorName = $school->director_name
            ?: $school->users()->where('role', 'directeur')->value('name')
            ?: $school->owner?->name;

        return view('documents.print', [
            'student' => $student,
            'documentType' => $type,
            'documentTitle' => self::DOCUMENTS[$type],
            'schoolName' => $request->user()->school->name,
            'schoolYear' => $student->schoolYear,
            'schoolHistory' => $schoolHistory,
            'directorName' => $directorName,
            'transferDetails' => $validated,
        ]);
    }

    private function schoolHistoryRow(Student $student): array
    {
        $className = $student->classRoom?->name ?: $student->class_name;
        $classRoom = $student->classRoom ?: ClassRoom::query()
            ->where('school_year_id', $student->school_year_id)
            ->where('name', $className)
            ->first();
        $gradeScale = $classRoom?->grade_scale;

        if (!$classRoom || !$gradeScale) {
            return [
                'class' => $className,
                'school_year' => $student->schoolYear?->name ?? '—',
                'average' => null,
                'grade_scale' => $gradeScale,
            ];
        }

        $notes = Note::query()
            ->where('student_id', $student->id)
            ->where('school_year_id', $student->school_year_id)
            ->get();
        $isPrimary = in_array($classRoom->cycle, ['1er Cycle', 'Primaire'], true);
        $continuousTypes = $isPrimary ? ['composition'] : ['devoir', 'controle', 'p1', 'p2'];
        $examTypes = $isPrimary ? [] : ['examen_trimestriel', 'examen', 'ex'];
        $normalize = fn (Note $note): float => (float) $note->score / max((float) $note->max_score, 1) * $gradeScale;
        $eligibleNotes = $notes->filter(fn (Note $note) => in_array(mb_strtolower($note->evaluation_type), [...$continuousTypes, ...$examTypes], true));

        if ($isPrimary) {
            $subjectAverages = $eligibleNotes
                ->groupBy('subject_id')
                ->map(fn (Collection $subjectNotes): float => (float) $subjectNotes->avg($normalize));
        } else {
            $subjectAverages = $eligibleNotes
                ->groupBy('subject_id')
                ->map(function (Collection $subjectNotes) use ($continuousTypes, $examTypes, $normalize): ?float {
                    $termAverages = $subjectNotes
                        ->groupBy('trimester')
                        ->map(function (Collection $termNotes) use ($continuousTypes, $examTypes, $normalize): ?float {
                            $continuous = $termNotes
                                ->filter(fn (Note $note) => in_array(mb_strtolower($note->evaluation_type), $continuousTypes, true))
                                ->map($normalize);
                            $exams = $termNotes
                                ->filter(fn (Note $note) => in_array(mb_strtolower($note->evaluation_type), $examTypes, true))
                                ->map($normalize);
                            $parts = collect([
                                $continuous->isNotEmpty() ? $continuous->avg() : null,
                                $exams->isNotEmpty() ? $exams->avg() : null,
                            ])->filter(fn (?float $score) => $score !== null);

                            return $parts->isNotEmpty() ? (float) $parts->avg() : null;
                        })
                        ->filter(fn (?float $average) => $average !== null);

                    return $termAverages->isNotEmpty() ? (float) $termAverages->avg() : null;
                })
                ->filter(fn (?float $average) => $average !== null);
        }

        $assignments = TeachingAssignment::query()
            ->where('school_year_id', $student->school_year_id)
            ->where('class_room_id', $classRoom->id)
            ->get()
            ->keyBy('subject_id');
        $weightedTotal = 0.0;
        $coefficientTotal = 0.0;

        foreach ($subjectAverages as $subjectId => $average) {
            $coefficient = (float) ($assignments->get($subjectId)?->coefficient ?: 1);
            $weightedTotal += $average * $coefficient;
            $coefficientTotal += $coefficient;
        }

        return [
            'class' => $className,
            'school_year' => $student->schoolYear?->name ?? '—',
            'average' => $coefficientTotal > 0 ? round($weightedTotal / $coefficientTotal, 2) : null,
            'grade_scale' => $gradeScale,
        ];
    }
}
