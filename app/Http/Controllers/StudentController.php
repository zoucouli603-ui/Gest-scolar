<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Support\SearchFilter;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $query = Student::with('classRoom');

        if ($currentYear) {
            $query->where('school_year_id', $currentYear->id);
        }

        $totalStudents  = (clone $query)->count();
        $activeStudents = (clone $query)->where('status', 'actif')->count();
        $boysCount      = (clone $query)->where('gender', 'M')->count();
        $girlsCount     = (clone $query)->where('gender', 'F')->count();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            SearchFilter::apply($query, $search, ['first_name', 'last_name', 'registration_number']);
        }

        if ($request->filled('class_room_id')) {
            $classRoom = ClassRoom::query()
                ->when($currentYear, fn ($classes) => $classes->where('school_year_id', $currentYear->id))
                ->find($request->integer('class_room_id'));
            if ($classRoom) {
                $query->where(function ($students) use ($classRoom) {
                    $students->where('class_room_id', $classRoom->id)
                        ->orWhere(function ($unlinkedStudents) use ($classRoom) {
                            $unlinkedStudents->whereNull('class_room_id')
                                ->where('class_name', $classRoom->name);
                        });
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($request->filled('class_name')) {
            $query->where('class_name', $request->class_name);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $students = $query->latest()->get();

        $classes = ClassRoom::when($currentYear, function ($query) use ($currentYear) {
            $query->where('school_year_id', $currentYear->id);
        })->orderBy('cycle')->orderBy('name')->get();

        return view('students.index', compact(
            'students', 
            'currentYear', 
            'totalStudents', 
            'activeStudents', 
            'boysCount', 
            'girlsCount',
            'classes'
        ));
    }

    public function create()
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (!$currentYear) {
            return redirect()->route('students.index')
                ->with('error', 'Veuillez définir une année scolaire active avant d\'inscrire un élève.');
        }

        $classes = ClassRoom::where('school_year_id', $currentYear->id)
            ->orderBy('name')
            ->get();

        return view('students.create', compact('currentYear', 'classes'));
    }

    public function store(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (!$currentYear) {
            return redirect()->route('students.index')
                ->with('error', 'Impossible d\'inscrire un élève : aucune année scolaire active n\'a été trouvée.');
        }

        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'gender'          => 'required|in:M,F',
            'birth_date'      => 'required|date',
            'birth_place'     => 'required|string|max:255',
            'nationality'     => 'required|string|max:255',
            'inscription_date'=> 'required|date',
            'school_name'     => 'nullable|string|max:255',
            'matricule_etat'  => 'nullable|string|max:255',
            'class_room_id'   => [
                'required',
                Rule::exists('class_rooms', 'id')->where('school_year_id', $currentYear->id),
            ],
            'parent_name'     => 'required|string|max:255',
            'parent_phone'    => 'required|string|max:255',
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
            'birth_certificate' => [
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'extensions:jpg,jpeg,png,pdf',
                'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120',
            ],
        ]);

        $classRoom = ClassRoom::findOrFail($validated['class_room_id']);

        $lastStudent = Student::latest('id')->first();

        if ($lastStudent && preg_match('/DEM-(\d+)/', $lastStudent->registration_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        $validated['class_name'] = $classRoom->name;
        $validated['matricule_etat'] = $classRoom->cycle === 'Lycée'
            ? ($validated['matricule_etat'] ?? null)
            : null;
        $validated['registration_number'] = 'DEM-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'actif';
        $validated['school_year_id'] = $currentYear->id;
        $validated['inscription_date'] = $validated['inscription_date'] ?? now()->toDateString();
        $validated['school_name'] = $request->user()->school->name;

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')
                ->storePublicly('schools/'.$request->user()->school_id.'/students', 'public');
        }

        if ($request->hasFile('birth_certificate')) {
            $validated['birth_certificate_path'] = $request->file('birth_certificate')
                ->store('schools/'.$request->user()->school_id.'/students/birth-certificates', 'private');
        }
        unset($validated['birth_certificate']);

        Student::create($validated);

        return redirect()->route('students.index')->with('success', 'Élève inscrit avec succès !');
    }

    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $classes = ClassRoom::where('school_year_id', $currentYear?->id ?? $student->school_year_id)
            ->orderBy('name')
            ->get();

        return view('students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, Student $student)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $targetYearId = $currentYear?->id ?? $student->school_year_id;

        $validated = $request->validate([
            'registration_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('students', 'registration_number')
                    ->ignore($student->id)
                    ->where('school_year_id', $targetYearId),
            ],
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'gender'           => 'required|in:M,F',
            'birth_date'       => 'required|date',
            'birth_place'      => 'required|string|max:255',
            'nationality'      => 'required|string|max:255',
            'school_name'      => 'nullable|string|max:255',
            'matricule_etat'   => 'nullable|string|max:255',
            'class_room_id'    => [
                'required',
                Rule::exists('class_rooms', 'id')
                    ->when($targetYearId, fn ($rule) => $rule->where('school_year_id', $targetYearId)),
            ],
            'parent_name'      => 'required|string|max:255',
            'parent_phone'     => 'required|string|max:255',
            'status'           => 'required|in:actif,inactif',
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120'],
            'birth_certificate' => [
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'extensions:jpg,jpeg,png,pdf',
                'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120',
            ],
        ]);

        $classRoom = ClassRoom::findOrFail($validated['class_room_id']);
        $validated['class_name'] = $classRoom->name;
        $validated['matricule_etat'] = $classRoom->cycle === 'Lycée'
            ? ($validated['matricule_etat'] ?? null)
            : null;
        $validated['school_year_id'] = $classRoom->school_year_id;
        $validated['school_name'] = $request->user()->school->name;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')
                ->storePublicly('schools/'.$request->user()->school_id.'/students', 'public');
            if ($student->photo_path && !Storage::disk('public')->delete($student->photo_path)) {
                Storage::disk('public')->delete($photoPath);
                return back()->withInput()->with('error', 'La photo précédente n’a pas pu être remplacée.');
            }
            $validated['photo_path'] = $photoPath;
        }

        if ($request->hasFile('birth_certificate')) {
            $newCertificate = $request->file('birth_certificate')
                ->store('schools/'.$request->user()->school_id.'/students/birth-certificates', 'private');
            if ($student->birth_certificate_path) {
                Storage::disk('private')->delete($student->birth_certificate_path);
            }
            $validated['birth_certificate_path'] = $newCertificate;
        }
        unset($validated['birth_certificate']);

        $student->update($validated);

        return redirect()->route('students.index')->with('success', 'Élève mis à jour avec succès !');
    }

    public function destroy(Student $student)
    {
        if ($student->payments()->exists()) {
            return redirect()->route('students.index')
                ->with('error', 'Impossible de supprimer cet élève : il possède des paiements enregistrés.');
        }

        $student->delete();

        return redirect()->route('students.index')->with('success', 'Élève supprimé avec succès !');
    }

    public function birthCertificate(Student $student)
    {
        abort_unless($student->birth_certificate_path, 404);
        $disk = Storage::disk('private');
        abort_unless($disk->exists($student->birth_certificate_path), 404);

        $stream = $disk->readStream($student->birth_certificate_path);
        abort_unless(is_resource($stream), 404);

        $sample = fread($stream, 8192);
        $detectedMimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($sample);
        $mimeType = is_string($detectedMimeType) ? $detectedMimeType : 'application/octet-stream';
        rewind($stream);

        return response()->stream(
            function () use ($stream): void {
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="birth-certificate"',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}