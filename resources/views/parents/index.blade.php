<x-app-layout>
    <div class="module-page mx-auto max-w-7xl space-y-5 p-4 md:p-5">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800"><x-icon name="users" class="h-6 w-6 text-violet-600" /> Gestion des parents</h1>
                <p class="mt-1 text-sm text-slate-500">Retrouvez les familles et les élèves rattachés pour l’année scolaire active.</p>
            </div>
            @if($currentYear)
                <span class="w-fit rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm">
                    Année scolaire : {{ $currentYear->name }}
                </span>
            @endif
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                <span>{{ session('success') }}</span>
                <button type="button" @click="show = false" class="font-bold" aria-label="Fermer">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition class="flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                <span>{{ session('error') }}</span>
                <button type="button" @click="show = false" class="font-bold" aria-label="Fermer">&times;</button>
            </div>
        @endif

        @if(!$currentYear)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Définissez une année scolaire active pour consulter les parents des élèves.
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <section class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Familles référencées</p>
                        <p class="mt-1 text-2xl font-bold text-slate-800">{{ $totalParents }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-lg font-bold text-blue-700">P</span>
                </section>
                <section class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Élèves rattachés</p>
                        <p class="mt-1 text-2xl font-bold text-slate-800">{{ $totalChildren }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-lg font-bold text-emerald-700">É</span>
                </section>
            </div>

            <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <form method="GET" action="{{ route('parents.index') }}" class="flex flex-col gap-2 sm:flex-row">
                    <label for="parent-search" class="sr-only">Rechercher un parent ou un élève</label>
                    <input id="parent-search" type="search" name="search" value="{{ $search }}" placeholder="Nom du parent, téléphone, élève ou classe" class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Rechercher</button>
                        @if($search)
                            <a href="{{ route('parents.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Effacer</a>
                        @endif
                    </div>
                </form>
            </section>

            @if(count($parents) === 0)
                <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500 shadow-sm">
                    {{ $search ? 'Aucun parent ou élève ne correspond à cette recherche.' : 'Aucun parent référencé pour cette année scolaire.' }}
                </div>
            @else
                <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(19rem, 1fr));">
                    @foreach($parents as $parent)
                        <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                            <div class="flex items-center gap-3">
                                <div class="flex shrink-0 items-center justify-center rounded-full bg-indigo-50 font-bold text-indigo-600" style="width:3rem;height:3rem">
                                    {{ mb_strtoupper(mb_substr($parent['name'], 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-bold text-slate-900">{{ $parent['name'] }}</h3>
                                    @if($parent['phone'])
                                        <a href="tel:{{ $parent['phone'] }}" class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 hover:text-blue-900"><x-icon name="phone" class="h-3.5 w-3.5" /> {{ $parent['phone'] }}</a>
                                    @else
                                        <span class="text-xs text-slate-400">Téléphone non renseigné</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 flex-1 space-y-1.5">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Élèves rattachés ({{ count($parent['children']) }})</p>
                                @foreach($parent['children'] as $child)
                                    <a href="{{ route('students.show', $child) }}" class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                        <span>{{ $child->first_name }} {{ $child->last_name }}</span>
                                        <span class="text-slate-400">{{ $child->classRoom?->name ?? $child->class_name }}</span>
                                    </a>
                                @endforeach
                            </div>
                            <a href="{{ route('parents.edit', $parent['key']) }}" class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                <x-icon name="pencil" /> Modifier les coordonnées
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
