<x-app-layout>
    <div class="module-page mx-auto max-w-7xl space-y-5 p-4 md:p-5">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800"><x-icon name="document" class="h-6 w-6 text-blue-600" /> Documents scolaires</h1>
                <p class="mt-1 text-sm text-slate-500">Préparez et imprimez les documents administratifs de chaque élève.</p>
            </div>
            @if($currentYear)
                <span class="w-fit rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm">
                    Année scolaire : {{ $currentYear->name }}
                </span>
            @endif
        </div>

        @if(!$currentYear)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Définissez une année scolaire active pour retrouver les élèves et préparer leurs documents.
                <a href="{{ route('school-years.index') }}" class="ml-1 font-semibold underline">Gérer les années scolaires</a>
            </div>
        @else
            <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Élèves de l’année</p>
                    <p class="mt-1 text-2xl font-bold text-slate-800">{{ $totalStudents }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Documents disponibles</p>
                    <p class="mt-1 text-sm font-medium text-slate-700">Fiche d’inscription · Certificat de scolarité · Certificat de transfert · Fiche scolaire</p>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <form method="GET" action="{{ route('documents.index') }}" class="grid grid-cols-1 gap-2 md:grid-cols-[1fr_220px_auto_auto]">
                    <label for="document-search" class="sr-only">Rechercher un élève</label>
                    <input id="document-search" type="search" name="search" value="{{ request('search') }}" placeholder="Nom, matricule ou parent..." class="min-w-0 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="class-filter" class="sr-only">Filtrer par classe</label>
                    <select id="class-filter" name="class_name" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Toutes les classes</option>
                        @foreach($classes as $className)
                            <option value="{{ $className }}" @selected(request('class_name') === $className)>{{ $className }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"><x-icon name="search" /> Rechercher</button>
                    @if(request()->filled('search') || request()->filled('class_name'))
                        <a href="{{ route('documents.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Effacer</a>
                    @endif
                </form>
            </section>

            <section>
                <div class="mb-3">
                    <h2 class="text-sm font-semibold text-slate-800">Choisir un élève</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Chaque document s’ouvre dans un format prêt à imprimer.</p>
                </div>
                @if($students->isEmpty())
                    <p class="rounded-xl border border-slate-200 bg-white px-4 py-10 text-center text-sm text-slate-500 shadow-sm">Aucun élève ne correspond à cette recherche pour l’année scolaire active.</p>
                @else
                    <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(19rem, 1fr));">
                        @foreach($students as $student)
                            <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                                <div class="flex items-center gap-3">
                                    <div class="flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-indigo-50 font-bold text-indigo-600" style="width:3.25rem;height:3.25rem">
                                        @if($student->photo_path)
                                            <img src="{{ url('storage/'.ltrim($student->photo_path, '/')) }}" alt="" style="width:100%;height:100%;object-fit:cover">
                                        @else
                                            {{ mb_strtoupper(mb_substr($student->first_name, 0, 1).mb_substr($student->last_name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-900">{{ $student->first_name }} {{ $student->last_name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $student->classRoom?->name ?? $student->class_name }} · {{ $student->registration_number }}</p>
                                        <p class="truncate text-xs text-slate-400">Parent : {{ $student->parent_name ?: 'Non renseigné' }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <a href="{{ route('documents.print', [$student, 'inscription']) }}" class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-2 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"><x-icon name="id-card" /> Inscription</a>
                                    <a href="{{ route('documents.print', [$student, 'scolarite']) }}" class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-2 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"><x-icon name="academic" /> Scolarité</a>
                                    <a href="{{ route('documents.print', [$student, 'transfert']) }}" class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-2 text-xs font-semibold text-slate-700 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700"><x-icon name="refresh" /> Transfert</a>
                                    <a href="{{ route('documents.print', [$student, 'scolaire']) }}" class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-2 text-xs font-semibold text-slate-700 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"><x-icon name="book" /> Fiche scolaire</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
                @if($students->hasPages())
                    <div class="mt-4">{{ $students->links() }}</div>
                @endif
            </section>
        @endif
    </div>
</x-app-layout>
