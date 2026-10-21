<x-app-layout>
    <div class="module-page mx-auto max-w-7xl space-y-4 p-4 md:p-5">
        
        <!-- En-tête de la page -->
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800">
                    <x-icon name="academic" class="h-6 w-6 text-indigo-600" /> Gestion des Élèves
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Cliquez sur un élève pour accéder à son profil complet • {{ $students->count() }} élève(s)
                </p>
            </div>

            @if(isset($currentYear) && $currentYear)
                <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ $currentYear->name }}
                </div>
            @endif
        </div>

        <!-- Messages Flash de notification -->
        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-100 p-3 text-sm text-emerald-700">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-emerald-500 font-bold">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition class="flex items-center justify-between rounded-lg border border-rose-200 bg-rose-100 p-3 text-sm text-rose-700">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="text-rose-500 font-bold">&times;</button>
            </div>
        @endif

        <!-- 1. Cartes Statistiques (Total, Actifs, Garçons, Filles) -->
        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            
            <!-- TOTAL -->
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <span class="block text-xl font-bold leading-none text-slate-800">{{ $totalStudents }}</span>
                    <span class="mt-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Total</span>
                </div>
            </div>

            <!-- ACTIFS -->
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <span class="block text-xl font-bold leading-none text-slate-800">{{ $activeStudents }}</span>
                    <span class="mt-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Actifs</span>
                </div>
            </div>

            <!-- GARÇONS -->
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 font-bold">
                    ♂
                </div>
                <div>
                    <span class="block text-xl font-bold leading-none text-slate-800">{{ $boysCount }}</span>
                    <span class="mt-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Garçons</span>
                </div>
            </div>

            <!-- FILLES -->
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 font-bold">
                    ♀
                </div>
                <div>
                    <span class="block text-xl font-bold leading-none text-slate-800">{{ $girlsCount }}</span>
                    <span class="mt-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Filles</span>
                </div>
            </div>
        </div>

        <!-- 2. Barre de filtres et de recherche complète -->
        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
            <form method="GET" action="{{ route('students.index') }}" class="flex flex-wrap items-center gap-2">
                
                <!-- Champ Recherche -->
                <div class="relative min-w-[200px] flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="search" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Rechercher..." 
                           class="w-full rounded-lg border border-slate-200 py-2 pl-10 pr-10 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @if(request('search'))
                        <a href="{{ route('students.index') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600" title="Effacer la recherche">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                    @endif
                </div>

                <!-- Filtre Toutes classes -->
                <select name="class_room_id" class="min-w-[140px] rounded-lg border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm">
                    <option value="">Toutes classes</option>
                    @foreach($classes as $classRoom)
                        <option value="{{ $classRoom->id }}" @selected(request('class_room_id') == $classRoom->id)>{{ $classRoom->name }}</option>
                    @endforeach
                </select>

                <!-- Filtre Statut -->
                <select name="status" class="min-w-[125px] rounded-lg border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm">
                    <option value="">Actifs / Tous</option>
                    <option value="actif" {{ request('status') == 'actif' ? 'selected' : '' }}>Actifs</option>
                    <option value="inactif" {{ request('status') == 'inactif' ? 'selected' : '' }}>Inactifs</option>
                </select>

                <!-- Filtre Genre -->
                <select name="gender" class="min-w-[100px] rounded-lg border-slate-200 px-3 py-2 text-sm text-slate-700 shadow-sm">
                    <option value="">Tous</option>
                    <option value="M" {{ request('gender') == 'M' ? 'selected' : '' }}>Garçons</option>
                    <option value="F" {{ request('gender') == 'F' ? 'selected' : '' }}>Filles</option>
                </select>

                <!-- Bouton Recherche (Loupe) -->
                <button type="submit" title="Rechercher" class="rounded-lg bg-blue-600 p-2.5 text-white shadow-sm transition hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>

                <!-- Bouton Réinitialiser -->
                <a href="{{ route('students.index') }}" title="Réinitialiser les filtres" class="rounded-lg border border-slate-200 p-2.5 text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
            </form>
        </div>

        <!-- 3. Barre d'actions (+ Inscrire) -->
        <div class="flex flex-wrap items-center gap-2">
            @if(isset($currentYear) && $currentYear)
                <a href="{{ route('students.create') }}" class="flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                    <x-icon name="plus" /> Inscrire un élève                </a>
            @endif
        </div>

        <!-- Tableau des élèves -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 text-slate-400 font-semibold text-xs uppercase border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">Matricule</th>
                        <th class="px-4 py-3">Élève</th>
                        <th class="px-4 py-3">Classe</th>
                        <th class="px-4 py-3">Parent</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($students as $student)
                        <tr class="hover:bg-slate-50/50 transition cursor-pointer" x-on:click="window.location='{{ route('students.show', $student->id) }}'">
                            <td class="px-4 py-3 font-mono text-xs font-medium text-slate-900">{{ $student->registration_number }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-100 text-xs font-bold text-blue-600" style="width:2.25rem;height:2.25rem">@if($student->photo_path)<img src="{{ url('storage/'.ltrim($student->photo_path, '/')) }}" alt="" style="width:100%;height:100%;object-fit:cover">@else{{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}@endif</div>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $student->gender == 'M' ? 'Garçon' : 'Fille' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ $student->classRoom?->name ?? $student->class_name }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $student->parent_name }}</p>
                                <p class="text-xs text-slate-400">{{ $student->parent_phone }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($student->status === 'actif')
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full uppercase">Actif</span>
                                @elseif($student->status === 'inactif')
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-semibold rounded-full uppercase">Inactif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right" x-on:click.stop>
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('students.show', $student->id) }}" title="Voir les détails" class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a href="{{ route('students.edit', $student->id) }}" title="Modifier" class="p-1.5 text-slate-400 hover:text-amber-600 rounded-lg hover:bg-slate-100 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <form action="{{ route('students.destroy', $student->id) }}" method="POST" x-on:submit="if (!confirm('Êtes-vous sûr de vouloir supprimer cet élève ?')) $event.preventDefault()" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Supprimer" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-slate-400">
                                Aucun élève trouvé pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>