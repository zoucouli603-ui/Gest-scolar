<x-app-layout>
    <div class="module-page mx-auto max-w-7xl space-y-4 p-4 md:p-5">
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-3">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-slate-800"><x-icon name="class" class="h-6 w-6 text-amber-600" /> Affectations pédagogiques</h1>
                <p class="mt-1 text-sm text-slate-500">Associez une matière et un enseignant à une classe.</p>
            </div>
            @if($currentYear)
                <span class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    Année scolaire : {{ $currentYear->name }}
                </span>
            @endif
        </div>

        <x-flash-messages />
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-100 p-4 text-sm text-rose-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-800">Créer une affectation</h2>
            @if(!$currentYear)
                <p class="text-sm text-slate-500">Définissez d’abord une année scolaire active.</p>
            @elseif($teachers->isEmpty() || $subjects->isEmpty() || $classes->isEmpty())
                <p class="text-sm text-slate-500">Ajoutez au moins un enseignant, une matière et une classe pour {{ $currentYear->name }} avant de créer une affectation.</p>
            @else
                <form action="{{ route('teaching-assignments.store') }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-4 md:items-end">
                    @csrf
                    <div>
                        <label for="teacher_id" class="mb-1 block text-sm font-medium text-slate-700">Enseignant</label>
                        <select id="teacher_id" name="teacher_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Choisir un enseignant</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="subject_id" class="mb-1 block text-sm font-medium text-slate-700">Matière</label>
                        <select id="subject_id" name="subject_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Choisir une matière</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="class_room_id" class="mb-1 block text-sm font-medium text-slate-700">Classe</label>
                        <select id="class_room_id" name="class_room_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Choisir une classe</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_room_id') == $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        Affecter
                    </button>
                </form>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="font-bold text-slate-800">Affectations de l’année</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="p-4">Matière</th>
                            <th class="p-4">Enseignant</th>
                            <th class="p-4">Classe</th>
                            <th class="p-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($assignments as $assignment)
                            <tr>
                                <td class="p-4 font-semibold">{{ $assignment->subject->name }}</td>
                                <td class="p-4">{{ $assignment->teacher->first_name }} {{ $assignment->teacher->last_name }}</td>
                                <td class="p-4">{{ $assignment->classRoom->name }}</td>
                                <td class="p-4 text-right">
                                    <form action="{{ route('teaching-assignments.destroy', $assignment) }}" method="POST" onsubmit="return confirm('Supprimer cette affectation ?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Supprimer l’affectation">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-10 text-center text-slate-500">Aucune affectation n’a encore été créée pour cette année.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
