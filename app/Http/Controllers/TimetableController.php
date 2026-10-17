<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\TeachingAssignment;
use App\Models\TimetableEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TimetableController extends Controller
{
    private const DAYS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
    ];

    public function index(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $classes = $currentYear
            ? ClassRoom::where('school_year_id', $currentYear->id)->orderBy('name')->get()
            : collect();
        $selectedClassId = null;
        $entries = collect();
        $assignments = collect();
        $selectedClass = null;

        if ($currentYear) {
            $request->validate([
                'class_room_id' => [
                    'nullable',
                    Rule::exists('class_rooms', 'id')->where('school_year_id', $currentYear->id),
                ],
            ]);

            $selectedClassId = $request->integer('class_room_id') ?: null;
            $selectedClass = $selectedClassId
                ? $classes->firstWhere('id', $selectedClassId)
                : null;
            $assignments = TeachingAssignment::with(['teacher', 'subject', 'classRoom'])
                ->where('school_year_id', $currentYear->id)
                ->whereHas('classRoom', fn ($query) => $query->where('school_year_id', $currentYear->id))
                ->orderBy('class_room_id')
                ->get();

            $entries = TimetableEntry::with([
                'teachingAssignment.teacher',
                'teachingAssignment.subject',
                'teachingAssignment.classRoom',
            ])
                ->where('school_year_id', $currentYear->id)
                ->when($selectedClassId, fn ($query) => $query->whereHas(
                    'teachingAssignment',
                    fn ($assignment) => $assignment->where('class_room_id', $selectedClassId)
                ))
                ->orderBy('day_of_week')
                ->orderBy('starts_at')
                ->get();
        }

        $timeSlots = $entries
            ->map(fn (TimetableEntry $entry) => [
                'key' => substr($entry->starts_at, 0, 5).'|'.substr($entry->ends_at, 0, 5),
                'starts_at' => substr($entry->starts_at, 0, 5),
                'ends_at' => substr($entry->ends_at, 0, 5),
            ])
            ->unique('key')
            ->sortBy('starts_at')
            ->values();
        $timeSlots = $timeSlots->map(function (array $slot, int $index) use ($timeSlots) {
            $previousSlot = $timeSlots->get($index - 1);
            $nextSlot = $timeSlots->get($index + 1);
            $slot['recreation_after'] = $slot['starts_at'] < '09:45'
                && ($slot['ends_at'] >= '09:45' || ($nextSlot['starts_at'] ?? '14:00') >= '09:45');
            $slot['afternoon_before'] = $previousSlot !== null
                && $slot['starts_at'] >= '14:00'
                && $previousSlot['ends_at'] <= '14:00';

            return $slot;
        });
        $entriesByTimeAndDay = $entries
            ->groupBy(fn (TimetableEntry $entry) => substr($entry->starts_at, 0, 5).'|'.substr($entry->ends_at, 0, 5))
            ->map(fn ($slotEntries) => $slotEntries->groupBy('day_of_week'));

        return view('timetable.index', [
            'currentYear' => $currentYear,
            'classes' => $classes,
            'selectedClassId' => $selectedClassId,
            'selectedClass' => $selectedClass,
            'assignments' => $assignments,
            'timeSlots' => $timeSlots,
            'entriesByTimeAndDay' => $entriesByTimeAndDay,
            'days' => self::DAYS,
        ]);
    }

    public function store(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('timetable.index')
                ->with('error', 'Veuillez définir une année scolaire active avant de créer un cours.');
        }

        $validated = $request->validate([
            'teaching_assignment_id' => [
                'required',
                Rule::exists('teaching_assignments', 'id')->where('school_year_id', $currentYear->id),
            ],
            'day_of_week' => ['required', 'integer', 'between:1,5'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'room' => ['nullable', 'string', 'max:100'],
        ], [
            'teaching_assignment_id.exists' => 'Choisissez une affectation de l’année scolaire active.',
            'day_of_week.between' => 'Choisissez un jour valide du lundi au vendredi.',
            'ends_at.after' => 'L’heure de fin doit être postérieure à l’heure de début.',
        ]);

        $assignment = TeachingAssignment::where('school_year_id', $currentYear->id)
            ->findOrFail($validated['teaching_assignment_id']);

        $hasConflict = TimetableEntry::query()
            ->where('school_year_id', $currentYear->id)
            ->where('day_of_week', $validated['day_of_week'])
            ->where('starts_at', '<', $validated['ends_at'])
            ->where('ends_at', '>', $validated['starts_at'])
            ->whereHas('teachingAssignment', fn ($query) => $query
                ->where('class_room_id', $assignment->class_room_id)
                ->orWhere('teacher_id', $assignment->teacher_id))
            ->exists();

        if ($hasConflict) {
            return redirect()->route('timetable.index')
                ->withErrors(['starts_at' => 'Ce créneau chevauche déjà un cours de cette classe ou de cet enseignant.'])
                ->withInput();
        }

        TimetableEntry::create([
            ...$validated,
            'school_year_id' => $currentYear->id,
        ]);

        return redirect()->route('timetable.index')
            ->with('success', 'Cours ajouté à l’emploi du temps.');
    }

    public function destroy(TimetableEntry $timetableEntry)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        abort_unless(
            $currentYear && (int) $timetableEntry->school_year_id === (int) $currentYear->id,
            404
        );

        $timetableEntry->delete();

        return redirect()->route('timetable.index')
            ->with('success', 'Cours supprimé de l’emploi du temps.');
    }
}
