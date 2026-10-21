<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Classe : {{ $classe->name }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ $classe->cycle }} • Frais mensuels : {{ number_format((float) $classe->monthly_fee, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }} • {{ $students->count() }} élève(s) inscrit(s)</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('classes.print-students', $classe) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                    <x-icon name="printer" /> Imprimer la liste (A-Z)
                </a>
                @if($canPrintGradeSheet)
                    <a href="{{ route('classes.print-grade-sheet', $classe) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                        <x-icon name="document" /> Imprimer la fiche de notes
                    </a>
                @endif
                <a href="{{ route('classes.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition shadow-sm">
                    <x-icon name="arrow-left" /> Retour
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-xs font-semibold uppercase text-slate-500">Enseignants attribués directement</h2>
            @forelse($assignedTeachers as $teacher)
                <span class="mr-2 inline-flex rounded-full bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700">
                    {{ $teacher->first_name }} {{ $teacher->last_name }}
                </span>
            @empty
                <p class="text-sm text-slate-400">Aucun enseignant n’a été attribué directement à cette classe.</p>
            @endforelse
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50 font-semibold text-xs text-slate-500 uppercase">
                Matières et enseignants affectés
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-slate-50/50 text-slate-400 font-semibold text-xs uppercase border-b border-slate-100">
                        <tr>
                            <th class="p-4">Matière</th>
                            <th class="p-4">Enseignant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($teachingAssignments as $assignment)
                            <tr>
                                <td class="p-4 font-semibold">{{ $assignment->subject->name }}</td>
                                <td class="p-4">{{ $assignment->teacher->first_name }} {{ $assignment->teacher->last_name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="p-6 text-center text-slate-400">
                                    Aucune matière ni aucun enseignant n’est affecté à cette classe.
                                    <a href="{{ route('teaching-assignments.index') }}" class="ml-1 font-semibold text-blue-600 hover:text-blue-700">Gérer les affectations</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50 font-semibold text-xs text-slate-400 uppercase">
                Liste des élèves de la classe
            </div>
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50/50 text-slate-400 font-semibold text-xs uppercase border-b border-slate-100">
                    <tr>
                        <th class="p-4">Élève</th>
                        <th class="p-4">Genre</th>
                        <th class="p-4">Date de naissance</th>
                        <th class="p-4">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($students as $student)
                        <tr class="hover:bg-slate-50/50 transition cursor-pointer" x-on:click="window.location='{{ route('students.show', $student->id) }}'">
                            <td class="p-4 font-semibold text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td class="p-4">{{ $student->gender == 'M' ? 'Garçon' : 'Fille' }}</td>
                            <td class="p-4">{{ \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') }}</td>
                            <td class="p-4">
                                @if($student->status === 'actif')
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full uppercase">Actif</span>
                                @else
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-semibold rounded-full uppercase">{{ $student->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-10 text-center text-slate-400">
                                Aucun élève n'est inscrit dans cette classe pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>