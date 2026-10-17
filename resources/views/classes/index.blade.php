<x-app-layout>
    <div class="module-page mx-auto max-w-7xl space-y-4 p-4 md:p-5">
        
        <!-- En-tête -->
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800">
                    <x-icon name="class" class="h-6 w-6 text-amber-600" /> Gestion des Classes
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Liste des classes enregistrées pour l'année en cours
                </p>
            </div>

            @if(isset($currentYear) && $currentYear)
                <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ $currentYear->name }}
                </div>
            @endif
        </div>

        <!-- Messages Flash -->
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

        <!-- Carte Statistique -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <span class="block text-xl font-bold leading-none text-slate-800">{{ $totalClasses }}</span>
                    <span class="mt-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Total classes</span>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-sm font-bold text-blue-600">C</span>
            </div>
        </div>

        <!-- Barre de recherche et actions -->
        <div class="flex flex-col justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:flex-row sm:items-center">
            <form method="GET" action="{{ route('classes.index') }}" class="flex min-w-0 flex-1 items-center gap-2">
                <select name="cycle" aria-label="Filtrer par cycle" class="w-36 shrink-0 rounded-lg border border-slate-200 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">Tous les cycles</option>
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle }}" @selected(request('cycle') === $cycle)>{{ $cycle }}</option>
                    @endforeach
                </select>
                <div class="relative min-w-0 flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="search" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Rechercher une classe..." 
                           class="w-full rounded-lg border border-slate-200 py-2 pl-10 pr-10 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @if(request('search') || request('cycle'))
                        <a href="{{ route('classes.index') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600" title="Effacer la recherche">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                    @endif
                </div>
                <button type="submit" class="rounded-lg bg-blue-600 p-2.5 text-white shadow-sm transition hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
                <a href="{{ route('classes.index') }}" class="rounded-lg border border-slate-200 p-2.5 text-slate-600 shadow-sm transition hover:bg-slate-50" title="Réinitialiser">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
            </form>

            @if(isset($currentYear) && $currentYear)
                <a href="{{ route('classes.create') }}" class="flex shrink-0 items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                    <span class="text-base font-bold">+</span> Ajouter une classe
                </a>
            @endif
        </div>

        <!-- Grille des classes -->
        @if ($classes->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-400 shadow-sm">
                Aucune classe enregistrée pour le moment.
            </div>
        @else
            <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));">
                @foreach ($classes as $classe)
                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md">
                        <a href="{{ route('classes.show', $classe->id) }}" class="block text-center" title="Voir les élèves de {{ $classe->name }}">
                            <div class="mx-auto flex items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white" style="width:4rem;height:4rem;">
                                <svg style="width:2rem;height:2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M4 21h16M2 21h20M12 12v.01M16 8h2a2 2 0 0 1 2 2v11"/></svg>
                            </div>
                            <h3 class="mt-3 text-base font-bold text-slate-900">{{ $classe->name }}</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 font-semibold text-indigo-700">{{ $classe->cycle }}</span>
                            </p>
                            <p class="mt-2 text-xs font-medium text-slate-600">{{ number_format((float) $classe->monthly_fee, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }} / mois</p>
                            <p class="mt-1 text-xs font-semibold text-blue-700">{{ $classe->students_count }} élève(s)</p>
                        </a>
                        <div class="mt-4 flex items-center justify-center gap-2 border-t border-slate-100 pt-3">
                            <a href="{{ route('classes.edit', $classe) }}" title="Modifier la classe et ses frais" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.687a2.121 2.121 0 0 1 3 3l-1.687 1.687M16.862 4.487 7.5 13.849V17h3.151l9.362-9.362M16.862 4.487 19.013 6.64M19.5 13.5v5.25A2.25 2.25 0 0 1 17.25 21H5.25A2.25 2.25 0 0 1 3 18.75V6.75A2.25 2.25 0 0 1 5.25 4.5h5.25"/></svg>
                            </a>
                            <form action="{{ route('classes.destroy', $classe->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette classe ?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Supprimer" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    </div>
</x-app-layout>