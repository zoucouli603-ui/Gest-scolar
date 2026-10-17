<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $students = $currentYear
            ? Student::with('classRoom')
                ->where('school_year_id', $currentYear->id)
                ->whereNotNull('parent_name')
                ->where('parent_name', '<>', '')
                ->orderBy('parent_name')
                ->orderBy('last_name')
                ->get()
            : collect();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $students = $students->filter(fn (Student $student) =>
                str_contains(mb_strtolower($student->parent_name), mb_strtolower($search))
                || str_contains(mb_strtolower((string) $student->parent_phone), mb_strtolower($search))
                || str_contains(mb_strtolower($student->first_name.' '.$student->last_name), mb_strtolower($search))
                || str_contains(mb_strtolower((string) $student->class_name), mb_strtolower($search))
            )->values();
        }

        $parents = $students
            ->groupBy(fn (Student $student) => trim($student->parent_name).'|'.trim((string) $student->parent_phone))
            ->map(function ($children) {
                $firstChild = $children->first();

                return [
                    'key' => $this->parentKey($firstChild->parent_name, $firstChild->parent_phone),
                    'name' => $firstChild->parent_name,
                    'phone' => $firstChild->parent_phone,
                    'children' => $children->sortBy(fn (Student $student) => mb_strtolower($student->last_name.' '.$student->first_name))->values(),
                ];
            })
            ->sortBy(fn (array $parent) => mb_strtolower($parent['name']))
            ->values();

        return view('parents.index', [
            'currentYear' => $currentYear,
            'parents' => $parents,
            'totalParents' => $parents->count(),
            'totalChildren' => $students->count(),
            'search' => $request->query('search', ''),
        ]);
    }

    public function edit(string $parentKey)
    {
        $currentYear = SchoolYear::where('is_current', true)->firstOrFail();
        $parent = $this->findParent($parentKey, $currentYear->id);

        return view('parents.edit', compact('currentYear', 'parent'));
    }

    public function update(Request $request, string $parentKey)
    {
        $currentYear = SchoolYear::where('is_current', true)->firstOrFail();
        $parent = $this->findParent($parentKey, $currentYear->id);
        $validated = $request->validate([
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_phone' => ['required', 'string', 'max:255'],
        ]);

        Student::where('school_year_id', $currentYear->id)
            ->where('parent_name', $parent['name'])
            ->where('parent_phone', $parent['phone'])
            ->update($validated);

        return redirect()->route('parents.index')->with('success', 'Les coordonnées du parent et de ses élèves ont été mises à jour.');
    }

    private function findParent(string $parentKey, int $schoolYearId): array
    {
        $children = Student::query()
            ->where('school_year_id', $schoolYearId)
            ->whereNotNull('parent_name')
            ->where('parent_name', '<>', '')
            ->orderBy('last_name')
            ->get();
        $parent = $children->first(fn (Student $student) => hash_equals(
            $parentKey,
            $this->parentKey($student->parent_name, $student->parent_phone)
        ));

        abort_unless($parent, 404);

        return [
            'name' => $parent->parent_name,
            'phone' => $parent->parent_phone,
            'children' => $children->filter(fn (Student $student) =>
                $student->parent_name === $parent->parent_name
                && $student->parent_phone === $parent->parent_phone
            )->values(),
        ];
    }

    private function parentKey(string $name, ?string $phone): string
    {
        return hash('sha256', trim($name).'|'.trim((string) $phone));
    }
}
