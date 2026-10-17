<x-app-layout>
    <div class="module-page mx-auto max-w-7xl space-y-4 p-4 md:p-5">

        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800">
                    <x-icon name="user" class="h-6 w-6 text-emerald-600" /> Gestion des Enseignants
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Liste des enseignants de l'année en cours
                </p>
            </div>

            @if(isset($currentYear) && $currentYear)
                <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ $currentYear->name }}
                </div>
            @endif
        </div>

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

        <div class="grid grid-cols-2 gap-3 xl:grid-cols-3">
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <span class="block text-xl font-bold leading-none text-slate-800">{{ $totalTeachers }}</span>
                    <span class="mt-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Total enseignants</span>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-sm font-bold text-blue-600">E</span>
            </div>

            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <span class="block text-xl font-bold leading-none text-slate-800">{{ $activeTeachers }}</span>
                    <span class="mt-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Enseignants actifs</span>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-sm font-bold text-emerald-600">✓</span>
            </div>
        </div>

        <div class="flex flex-col justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:flex-row sm:items-center">
            <form method="GET" action="{{ route('teachers.index') }}" class="flex min-w-0 flex-1 items-center gap-2">
                <div class="relative min-w-0 flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher un enseignant..." class="w-full rounded-lg border border-slate-200 py-2 pl-10 pr-10 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @if(request('search'))
                        <a href="{{ route('teachers.index') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600" title="Effacer la recherche">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                    @endif
                </div>
                <button type="submit" class="rounded-lg bg-blue-600 p-2.5 text-white shadow-sm transition hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
                <a href="{{ route('teachers.index') }}" class="rounded-lg border border-slate-200 p-2.5 text-slate-600 shadow-sm transition hover:bg-slate-50" title="Réinitialiser">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
            </form>

            @if(isset($currentYear) && $currentYear)
                <a href="{{ route('teachers.create') }}" class="flex shrink-0 items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                    <span class="text-base font-bold">+</span> Ajouter un(e) enseignant(e)
                </a>
            @endif
        </div>

        <div class="flex gap-1 border-b border-slate-200">
            <a href="{{ route('teachers.index') }}" class="-mb-px inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold" style="border-color:#2563eb;color:#1d4ed8"><x-icon name="users" /> Enseignants</a>
            <a href="{{ route('teachers.attendance') }}" class="-mb-px inline-flex items-center gap-2 border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800"><x-icon name="calendar" /> Scan QR Code</a>
        </div>

        @if ($teachers->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-400 shadow-sm">
                Aucun enseignant enregistré pour le moment.
            </div>
        @else
            <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));">
                @foreach ($teachers as $teacher)
                    <div class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md">
                        <a href="{{ route('teachers.show', $teacher->id) }}" class="block text-center" title="Voir {{ $teacher->first_name }} {{ $teacher->last_name }}">
                            <div class="mx-auto overflow-hidden rounded-full bg-indigo-50" style="width:5rem;height:5rem;box-shadow:0 0 0 2px #c7d2fe">
                                @if ($teacher->photo_url)
                                    <img src="{{ $teacher->photo_url }}" alt="Photo" style="width:100%;height:100%;object-fit:cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-xl font-bold text-indigo-500">{{ mb_strtoupper(mb_substr($teacher->first_name, 0, 1).mb_substr($teacher->last_name, 0, 1)) }}</div>
                                @endif
                            </div>
                            <h3 class="mt-3 text-base font-bold text-slate-900">{{ $teacher->first_name }} {{ $teacher->last_name }}</h3>
                            <p class="mt-1 text-xs text-slate-500">{{ $teacher->declaredSubjects->pluck('name')->join(', ') ?: ($teacher->specialty ?: 'Non défini') }}</p>
                            <p class="mt-1 text-xs text-slate-500">Classes : {{ $teacher->assignedClasses->pluck('name')->join(', ') ?: '—' }}</p>
                            <p class="mt-1 text-xs font-medium text-slate-600">{{ $teacher->phone ?? '—' }}</p>
                            <p class="mt-2">
                                @if($teacher->status === 'actif')
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full uppercase">Actif</span>
                                @elseif($teacher->status === 'inactif')
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-semibold rounded-full uppercase">Inactif</span>
                                @else
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full uppercase">Congé</span>
                                @endif
                            </p>
                        </a>
                        <div class="mt-4 flex items-center justify-center gap-2 border-t border-slate-100 pt-3">
                            <a href="{{ route('teachers.show', $teacher->id) }}#badge" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-indigo-50 hover:text-indigo-600" title="Badge QR"><x-icon name="id-card" /></a>
                            <a href="{{ route('teachers.edit', $teacher->id) }}" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600" title="Modifier"><x-icon name="pencil" /></a>
                            <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet enseignant ?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Supprimer"><x-icon name="trash" /></button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    </div>
</x-app-layout>
