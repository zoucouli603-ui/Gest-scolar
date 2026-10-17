<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Support\SearchFilter;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $query = Teacher::query();

        if ($currentYear) {
            $query->where('school_year_id', $currentYear->id);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            SearchFilter::apply($query, $search, ['first_name', 'last_name', 'specialty', 'email', 'phone'],
                fn ($group, $like) => $group->orWhereHas('declaredSubjects', fn ($subjects) => $subjects->where('name', 'like', $like)));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $teachers = $query->with(['assignedClasses', 'declaredSubjects'])->latest()->get();
        $totalTeachers = (clone $query)->count();
        $activeTeachers = (clone $query)->where('status', 'actif')->count();

        return view('teachers.index', compact('teachers', 'currentYear', 'totalTeachers', 'activeTeachers'));
    }

    public function create()
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('teachers.index')
                ->with('error', 'Veuillez définir une année scolaire active avant d\'ajouter un enseignant.');
        }

        $classes = $currentYear->classes()->orderBy('name')->get();
        $subjects = $currentYear->subjects()->orderBy('name')->get();

        return view('teachers.create', compact('currentYear', 'classes', 'subjects'));
    }

    public function store(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('teachers.index')
                ->with('error', 'Aucune année scolaire active trouvée.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|in:M,F',
            'phone' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('teachers', 'email')->where('school_year_id', $currentYear->id),
            ],
            'status' => 'required|in:actif,inactif,congé',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('subjects', 'id')->where('school_year_id', $currentYear->id),
            ],
            'class_ids_present' => 'required|accepted',
            'class_ids' => 'nullable|array',
            'class_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('class_rooms', 'id')->where('school_year_id', $currentYear->id),
            ],
        ]);

        $classIds = $validated['class_ids'] ?? [];
        $subjectIds = $validated['subject_ids'] ?? [];
        unset($validated['class_ids'], $validated['subject_ids'], $validated['class_ids_present']);
        $validated['school_year_id'] = $currentYear->id;

        $request->validate(['photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048']);
        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('teacher-photos', 'public');
        }

        if ($this->primaryClassesHaveOtherTeachers($classIds, $currentYear->id)) {
            return back()->withInput()->withErrors([
                'class_ids' => 'Une classe du Primaire doit conserver un seul enseignant pour toutes ses matières.',
            ]);
        }

        DB::transaction(function () use ($validated, $classIds, $subjectIds, $currentYear): void {
            $teacher = Teacher::create($validated);
            $teacher->assignedClasses()->sync(collect($classIds)->mapWithKeys(
                fn ($classId) => [$classId => ['school_year_id' => $currentYear->id]]
            ));
            $teacher->declaredSubjects()->sync($subjectIds);
        });

        return redirect()->route('teachers.index')->with('success', 'Enseignant ajouté avec succès.');
    }

    public function show(Teacher $teacher)
    {
        $teacher->load(['assignedClasses', 'declaredSubjects']);
        $attendances = $teacher->attendances()->orderByDesc('date')->limit(60)->get();

        return view('teachers.show', compact('teacher', 'attendances'));
    }

    public function attendance(Request $request)
    {
        $today = now()->toDateString();
        $date = $request->input('date', $today);
        $records = TeacherAttendance::with('teacher')
            ->whereHas('teacher')
            ->whereDate('date', $date)
            ->orderBy('check_in_at')
            ->get();
        $absentTeachers = Teacher::where('status', 'actif')
            ->whereNotIn('id', $records->pluck('teacher_id'))
            ->orderBy('last_name')
            ->get();

        return view('teachers.attendance', compact('records', 'absentTeachers', 'date', 'today'));
    }

    public function scan(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:200']);
        $token = trim($data['code']);
        $token = basename(parse_url($token, PHP_URL_PATH) ?: $token);

        $teacher = Teacher::where('qr_token', $token)->first();
        if (! $teacher) {
            return $this->scanResponse($request, false, 'Code QR inconnu ou enseignant introuvable.');
        }
        if ($teacher->status !== 'actif') {
            return $this->scanResponse($request, false, $teacher->first_name.' '.$teacher->last_name.' n\'est pas actif.');
        }

        $now = now();
        $record = TeacherAttendance::firstOrCreate(
            ['teacher_id' => $teacher->id, 'date' => $now->toDateString()],
        );
        $name = $teacher->first_name.' '.$teacher->last_name;
        $already = false;

        if (! $record->check_in_at) {
            $record->update(['check_in_at' => $now]);
            $action = 'arrivee';
            $message = 'Arrivée enregistrée à '.$now->format('H:i:s');
        } elseif (! $record->check_out_at) {
            if ($record->check_in_at->diffInSeconds($now) < 60) {
                $action = 'arrivee';
                $already = true;
                $message = 'Arrivée à '.$record->check_in_at->format('H:i:s');
            } else {
                $record->update(['check_out_at' => $now]);
                $action = 'depart';
                $message = 'Départ enregistré à '.$now->format('H:i:s');
            }
        } else {
            $action = 'depart';
            $already = true;
            $message = 'Départ à '.$record->check_out_at->format('H:i:s');
        }

        $teacher->loadMissing('declaredSubjects');
        $payload = [
            'name' => $name,
            'photo' => $teacher->photo_url,
            'initials' => mb_strtoupper(mb_substr($teacher->first_name, 0, 1).mb_substr($teacher->last_name, 0, 1)),
            'subjects' => $teacher->declaredSubjects->pluck('name')->join(', ') ?: ($teacher->specialty ?: 'Enseignant'),
            'phone' => $teacher->phone,
            'action' => $action,
            'already' => $already,
        ];

        return $this->scanResponse($request, true, $message, $payload);
    }

    private function scanResponse(Request $request, bool $ok, string $message, array $teacher = [])
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message, 'teacher' => $teacher], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'error', $message);
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load('assignedClasses');
        $teacher->load('declaredSubjects');
        $classes = $teacher->school_year_id
            ? $teacher->schoolYear->classes()->orderBy('name')->get()
            : collect();
        $subjects = $teacher->school_year_id
            ? $teacher->schoolYear->subjects()->orderBy('name')->get()
            : collect();

        return view('teachers.edit', compact('teacher', 'classes', 'subjects'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|in:M,F',
            'phone' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('teachers', 'email')->ignore($teacher->id)->where('school_year_id', $teacher->school_year_id),
            ],
            'status' => 'required|in:actif,inactif,congé',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('subjects', 'id')->where('school_year_id', $teacher->school_year_id),
            ],
            'class_ids_present' => 'required|accepted',
            'class_ids' => 'nullable|array',
            'class_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('class_rooms', 'id')->where('school_year_id', $teacher->school_year_id),
            ],
        ]);

        $classIds = $validated['class_ids'] ?? [];
        $subjectIds = $validated['subject_ids'] ?? [];
        unset($validated['class_ids'], $validated['subject_ids'], $validated['class_ids_present']);

        $request->validate(['photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048']);
        if ($request->hasFile('photo')) {
            $newPhoto = $request->file('photo')->store('teacher-photos', 'public');
            if ($teacher->photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($teacher->photo_path);
            }
            $validated['photo_path'] = $newPhoto;
        }

        if ($this->primaryClassesHaveOtherTeachers($classIds, $teacher->school_year_id, $teacher->id)) {
            return back()->withInput()->withErrors([
                'class_ids' => 'Une classe du Primaire doit conserver un seul enseignant. Retirez la classe ou réaffectez d’abord ses matières.',
            ]);
        }

        DB::transaction(function () use ($teacher, $validated, $classIds, $subjectIds): void {
            $teacher->update($validated);
            $teacher->assignedClasses()->sync(collect($classIds)->mapWithKeys(
                fn ($classId) => [$classId => ['school_year_id' => $teacher->school_year_id]]
            ));
            $teacher->declaredSubjects()->sync($subjectIds);
        });

        return redirect()->route('teachers.index')->with('success', 'Enseignant mis à jour avec succès.');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Enseignant supprimé avec succès.');
    }

    private function primaryClassesHaveOtherTeachers(array $classIds, int $schoolYearId, ?int $excludeTeacherId = null): bool
    {
        $primaryClassIds = ClassRoom::query()
            ->whereIn('id', $classIds)
            ->whereIn('cycle', ['1er Cycle', 'Primaire'])
            ->pluck('id');

        if ($primaryClassIds->isEmpty()) {
            return false;
        }

        return TeachingAssignment::query()
            ->where('school_year_id', $schoolYearId)
            ->whereIn('class_room_id', $primaryClassIds)
            ->when($excludeTeacherId, fn ($query) => $query->where('teacher_id', '!=', $excludeTeacherId))
            ->exists()
            || DB::table('teacher_class_assignments')
                ->where('school_year_id', $schoolYearId)
                ->whereIn('class_room_id', $primaryClassIds)
                ->when($excludeTeacherId, fn ($query) => $query->where('teacher_id', '!=', $excludeTeacherId))
                ->exists();
    }
}
