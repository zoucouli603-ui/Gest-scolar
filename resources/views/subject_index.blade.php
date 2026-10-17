<x-app-layout>
    <div class="module-page mx-auto max-w-7xl space-y-4 p-4 md:p-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800">
                    <x-icon name="book" class="h-6 w-6 text-blue-600" /> Gestion des Matières
                </h1>
                <p class="mt-1 text-xs text-slate-500">{{ $totalSubjects ?? 0 }} matière(s) enregistrée(s) pour l'année en cours</p>
            </div>

            @if(isset($currentYear) && $currentYear)
                <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm">
                    <x-icon name="calendar" class="h-4 w-4 text-slate-400" />
                    {{ $currentYear->name }}
                </div>
            @endif
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-100 p-3 text-sm text-emerald-700">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="font-bold text-emerald-500">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition class="flex items-center justify-between rounded-lg border border-rose-200 bg-rose-100 p-3 text-sm text-rose-700">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="font-bold text-rose-500">&times;</button>
            </div>
        @endif

        <div class="flex flex-col justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:flex-row sm:items-center">
            <form method="GET" action="{{ route('subjects.index') }}" class="flex min-w-0 flex-1 items-center gap-2">
                <div class="relative min-w-0 flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><x-icon name="search" /></span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher une matière..." class="w-full rounded-lg border border-slate-200 py-2 pl-10 pr-10 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @if(request('search'))
                        <a href="{{ route('subjects.index') }}" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600" title="Effacer la recherche"><x-icon name="x" /></a>
                    @endif
                </div>
                <button type="submit" class="rounded-lg bg-blue-600 p-2.5 text-white shadow-sm transition hover:bg-blue-700"><x-icon name="search" /></button>
                <a href="{{ route('subjects.index') }}" class="rounded-lg border border-slate-200 p-2.5 text-slate-600 shadow-sm transition hover:bg-slate-50" title="Réinitialiser"><x-icon name="refresh" /></a>
            </form>

            @if(isset($currentYear) && $currentYear)
                <a href="{{ route('subjects.create') }}" class="flex shrink-0 items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                    <x-icon name="plus" /> Ajouter une Matière                </a>
            @endif
        </div>

        @if ($subjects->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-400 shadow-sm">Aucune matière enregistrée pour le moment.</div>
        @else
            <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));">
                @foreach ($subjects as $subject)
                    <div class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md">
                        <a href="{{ route('subjects.show', $subject->id) }}" class="block text-center" title="Voir {{ $subject->name }}">
                            <div class="mx-auto flex items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white" style="width:4rem;height:4rem;">
                                <x-icon name="book" class="h-8 w-8" />
                            </div>
                            <h3 class="mt-3 text-base font-bold text-slate-900">{{ $subject->name }}</h3>
                            <p class="mt-1"><span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">{{ $subject->code }}</span></p>
                            <p class="mt-2 text-xs font-medium text-slate-600">Coefficient : {{ $subject->coefficient ?? 1 }}</p>
                            <p class="mt-1 truncate text-xs text-slate-400">{{ $subject->description ?: 'Aucune description' }}</p>
                        </a>
                        <div class="mt-4 flex items-center justify-center gap-2 border-t border-slate-100 pt-3">
                            <a href="{{ route('subjects.edit', $subject->id) }}" title="Modifier" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"><x-icon name="pencil" /></a>
                            <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette matière ?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Supprimer" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"><x-icon name="trash" /></button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
