<x-app-layout>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        @media print {
            html, body, main { background: #fff !important; height: auto !important; overflow: visible !important; }
            body > div > aside, .no-print, #global-search { display: none !important; }
            body > div { display: block !important; height: auto !important; min-height: 0 !important; overflow: visible !important; }
            main { height: auto !important; padding: 0 !important; overflow: visible !important; }
            .timetable-print .overflow-x-auto { overflow: visible !important; }
            .timetable-print { max-width: none !important; padding: 0 !important; }
            .timetable-print table { min-width: 0 !important; }
            .timetable-print th, .timetable-print td { padding: 8px !important; }
            .timetable-print section { border-color: #cbd5e1 !important; box-shadow: none !important; }
            .timetable-print { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .screen-subtitle, .timetable-class-label { display: none !important; }
            .print-heading { display: block !important; }
        }
        .print-heading { display: none; }
    </style>
    <div class="module-page timetable-print mx-auto max-w-7xl space-y-4 p-4 md:p-5">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800"><x-icon name="calendar" class="h-6 w-6 text-sky-600" /> Emploi du temps</h1>
                <p class="screen-subtitle mt-1 text-sm text-slate-500">
                    {{ $selectedClass ? 'Horaire hebdomadaire de '.$selectedClass->name : 'Consultez les horaires hebdomadaires des classes.' }}
                    @if($currentYear) · {{ $currentYear->name }} @endif
                </p>
                <div class="print-heading mt-2 border-t border-slate-300 pt-2 text-sm font-semibold text-slate-700">
                    <x-doc-logo style="display:block;margin:0 0 6px;height:56px;max-width:160px;object-fit:contain" />
                    {{ auth()->user()?->school?->name }}<br>                    Classe : {{ $selectedClass?->name ?? 'Toutes les classes' }}
                    @if($currentYear) <span class="font-normal">· Année scolaire : {{ $currentYear->name }}</span> @endif
                </div>
            </div>
            <div class="no-print flex items-center gap-2">
                @if($currentYear)
                    <span class="w-fit rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm">
                        Année scolaire : {{ $currentYear->name }}
                    </span>
                @endif
                <button type="button" onclick="window.print()" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Imprimer
                </button>
            </div>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition class="no-print flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-100 p-3 text-sm text-emerald-700">
                <span>{{ session('success') }}</span>
                <button type="button" @click="show = false" class="font-bold text-emerald-700" aria-label="Fermer">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition class="no-print flex items-center justify-between rounded-xl border border-rose-200 bg-rose-100 p-3 text-sm text-rose-700">
                <span>{{ session('error') }}</span>
                <button type="button" @click="show = false" class="font-bold text-rose-700" aria-label="Fermer">&times;</button>
            </div>
        @endif
        @if($errors->any())
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition class="no-print flex items-start justify-between gap-3 rounded-xl border border-rose-200 bg-rose-100 p-3 text-sm text-rose-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" @click="show = false" class="font-bold text-rose-700" aria-label="Fermer">&times;</button>
            </div>
        @endif

        @if(!$currentYear)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Définissez d’abord une année scolaire active pour gérer l’emploi du temps.
            </div>
        @else
            <section class="no-print rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-sm font-semibold text-slate-800">Ajouter un cours</h2>
                @if($assignments->isEmpty())
                    <p class="text-sm text-slate-500">
                        Créez d’abord les classes, les matières et les affectations pédagogiques de {{ $currentYear->name }}.
                    </p>
                @else
                    <form action="{{ route('timetable.store') }}" method="POST" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 lg:items-end">
                        @csrf
                        <div class="sm:col-span-2 lg:col-span-2">
                            <label for="teaching_assignment_id" class="mb-1 block text-xs font-medium text-slate-700">Classe · Matière · Enseignant</label>
                            <select id="teaching_assignment_id" name="teaching_assignment_id" required class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Choisir une affectation</option>
                                @foreach($assignments as $assignment)
                                    <option value="{{ $assignment->id }}" @selected(old('teaching_assignment_id') == $assignment->id)>
                                        {{ $assignment->classRoom->name }} — {{ $assignment->subject->name }} ({{ $assignment->teacher->first_name }} {{ $assignment->teacher->last_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="day_of_week" class="mb-1 block text-xs font-medium text-slate-700">Jour</label>
                            <select id="day_of_week" name="day_of_week" required class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Choisir un jour</option>
                                @foreach($days as $dayNumber => $dayName)
                                    <option value="{{ $dayNumber }}" @selected(old('day_of_week') == $dayNumber)>{{ $dayName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="starts_at" class="mb-1 block text-xs font-medium text-slate-700">Début</label>
                            <input id="starts_at" type="time" name="starts_at" value="{{ old('starts_at') }}" required class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="ends_at" class="mb-1 block text-xs font-medium text-slate-700">Fin</label>
                            <input id="ends_at" type="time" name="ends_at" value="{{ old('ends_at') }}" required class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="flex gap-2">
                            <div class="min-w-0 flex-1">
                                <label for="room" class="mb-1 block text-xs font-medium text-slate-700">Salle (facultatif)</label>
                                <input id="room" type="text" name="room" maxlength="100" value="{{ old('room') }}" placeholder="Ex. Salle 2" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <button type="submit" class="self-end whitespace-nowrap rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Ajouter
                            </button>
                        </div>
                    </form>
                @endif
            </section>

            <section class="no-print rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <form action="{{ route('timetable.index') }}" method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                    <div class="w-full sm:max-w-xs">
                        <label for="filter_class_room_id" class="mb-1 block text-xs font-medium text-slate-700">Afficher une classe</label>
                        <select id="filter_class_room_id" name="class_room_id" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Toutes les classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected($selectedClassId == $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Filtrer</button>
                </form>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] border-collapse text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="w-32 border border-slate-200 px-3 py-3 text-center">Heures</th>
                                @foreach($days as $dayName)
                                    <th class="border border-slate-200 px-3 py-3 text-center">{{ $dayName }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="text-slate-700">
                            @forelse($timeSlots as $slot)
                                @if($slot['afternoon_before'])
                                    <tr class="schedule-break">
                                        <th colspan="6" class="border border-slate-300 bg-slate-100 px-3 py-2 text-center font-bold tracking-[0.35em] text-slate-700">APRÈS-MIDI</th>
                                    </tr>
                                @endif
                                <tr>
                                    <th class="whitespace-nowrap border border-slate-200 bg-slate-50 px-3 py-3 text-center text-xs font-semibold tabular-nums text-slate-700">
                                        {{ $slot['starts_at'] }} – {{ $slot['ends_at'] }}
                                    </th>
                                    @foreach($days as $dayNumber => $dayName)
                                        @php($cellEntries = $entriesByTimeAndDay->get($slot['key'], collect())->get($dayNumber, collect()))
                                        <td class="min-w-32 border border-slate-200 p-1.5 align-top">
                                            @forelse($cellEntries as $entry)
                                                <div class="mb-1 rounded-md border-l-2 border-blue-600 bg-blue-50 px-2 py-1.5 last:mb-0">
                                                    <p class="text-xs font-semibold text-slate-800">{{ $entry->teachingAssignment->subject->name }}</p>
                                                    <p class="mt-0.5 text-[11px] text-slate-600">
                                                        {{ $entry->teachingAssignment->teacher->first_name }} {{ $entry->teachingAssignment->teacher->last_name }}
                                                    </p>
                                                    @unless($selectedClass)
                                                        <p class="timetable-class-label text-[11px] font-medium text-blue-700">{{ $entry->teachingAssignment->classRoom->name }}</p>
                                                    @endunless
                                                    @if($entry->room)
                                                        <p class="text-[11px] text-slate-500">{{ $entry->room }}</p>
                                                    @endif
                                                    <form class="no-print mt-1 text-right" action="{{ route('timetable.destroy', $entry) }}" method="POST" onsubmit="return confirm('Supprimer ce cours de l’emploi du temps ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-[11px] font-medium text-rose-600 hover:text-rose-800">Supprimer</button>
                                                    </form>
                                                </div>
                                            @empty
                                                <span class="text-slate-300">—</span>
                                            @endforelse
                                        </td>
                                    @endforeach
                                </tr>
                                @if($slot['recreation_after'])
                                    <tr class="schedule-break">
                                        <th colspan="6" class="border border-slate-300 bg-slate-100 px-3 py-2 text-center font-bold tracking-[0.35em] text-slate-700">RÉCRÉATION</th>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="border border-slate-200 px-4 py-10 text-center text-sm text-slate-500">
                                        Aucun créneau n’est encore enregistré. Ajoutez un cours pour commencer à remplir la grille.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
