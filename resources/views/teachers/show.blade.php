@php
    $currency = auth()->user()?->school?->currency ?? 'XOF';
    $label = 'text-[11px] font-bold text-slate-400 uppercase tracking-wider';
    $value = 'mt-1 text-base font-semibold text-slate-800';
    $subjects = $teacher->declaredSubjects->pluck('name')->join(', ') ?: ($teacher->specialty ?: 'Aucune matière définie');
    $tabs = [
        'infos' => ['Infos', 'user'],
        'badge' => ['Badge & QR', 'id-card'],
        'presences' => ['Présences', 'calendar'],
    ];
@endphp

<x-app-layout>
    <div class="p-6 max-w-6xl mx-auto">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between gap-4 px-6 py-5 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center gap-4 min-w-0">
                    @if($teacher->photo_url)
                        <img src="{{ $teacher->photo_url }}" alt="Photo" class="h-16 w-16 shrink-0 rounded-full object-cover shadow-sm" style="width:4rem;height:4rem">
                    @else
                        <div class="shrink-0 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl shadow-sm" style="width:4rem;height:4rem">
                            {{ mb_strtoupper(mb_substr($teacher->first_name, 0, 1).mb_substr($teacher->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h1 class="truncate text-2xl font-black text-slate-900 uppercase tracking-tight">{{ $teacher->first_name }} {{ $teacher->last_name }}</h1>
                        <p class="mt-1 text-sm text-slate-500">{{ $subjects }}</p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('teachers.edit', $teacher) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                        <x-icon name="pencil" /> Modifier
                    </a>
                    <a href="{{ route('teachers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition shadow-sm">
                        <x-icon name="arrow-left" /> Retour
                    </a>
                </div>
            </div>

            <div class="flex gap-1 border-b border-slate-200 px-6 print:hidden" role="tablist">
                @foreach($tabs as $key => [$title, $icon])
                    <button type="button" role="tab" data-tab="{{ $key }}"
                            class="student-tab -mb-px inline-flex items-center gap-2 border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800">
                        <x-icon :name="$icon" /> {{ $title }}
                    </button>
                @endforeach
            </div>

            <div class="p-6">
                <div data-panel="infos" class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div><p class="{{ $label }}">Prénom</p><p class="{{ $value }}">{{ $teacher->first_name }}</p></div>
                    <div><p class="{{ $label }}">Nom</p><p class="{{ $value }}">{{ $teacher->last_name }}</p></div>
                    <div><p class="{{ $label }}">Téléphone</p><p class="{{ $value }}">{{ $teacher->phone ?? '—' }}</p></div>
                    <div><p class="{{ $label }}">Email</p><p class="{{ $value }}">{{ $teacher->email ?? '—' }}</p></div>
                    <div><p class="{{ $label }}">Matière(s)</p><p class="{{ $value }}">{{ $subjects }}</p></div>
                     
                    <div><p class="{{ $label }}">Statut</p><p class="{{ $value }}">{{ ucfirst($teacher->status) }}</p></div>
                    <div><p class="{{ $label }}">Classes attribuées</p><p class="{{ $value }}">{{ $teacher->assignedClasses->pluck('name')->join(', ') ?: 'Aucune classe attribuée' }}</p></div>
                </div>

                <div data-panel="badge" class="hidden">
                    <div id="teacher-badge-section" class="rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between bg-slate-50 px-4 py-3 border-b border-slate-200 print:hidden">
                            <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-600"><x-icon name="id-card" /> Badge de l'enseignant</h2>
                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 transition">
                                <x-icon name="printer" /> Imprimer
                            </button>
                        </div>
                        <div class="flex justify-center p-6">
                            <x-teacher-badge :teacher="$teacher" />
                        </div>
                    </div>
                </div>

                <div data-panel="presences" class="hidden">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Historique des présences</h2>
                        <a href="{{ route('teachers.attendance') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 transition">
                            <x-icon name="calendar" /> Scanner les présences
                        </a>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-400">
                                <tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Arrivée</th><th class="px-4 py-3">Départ</th><th class="px-4 py-3">Durée</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($attendances as $a)
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium">{{ $a->date->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2.5">{{ $a->check_in_at?->format('H:i') ?? '—' }}</td>
                                        <td class="px-4 py-2.5">{{ $a->check_out_at?->format('H:i') ?? '—' }}</td>
                                        <td class="px-4 py-2.5">
                                            @if($a->check_in_at && $a->check_out_at)
                                                {{ intdiv($a->check_in_at->diffInMinutes($a->check_out_at), 60) }}h{{ str_pad($a->check_in_at->diffInMinutes($a->check_out_at) % 60, 2, '0', STR_PAD_LEFT) }}
                                            @else — @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="p-8 text-center text-slate-400">Aucune présence enregistrée.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .student-tab.is-active { border-color: #2563eb; color: #1d4ed8; }
        @media print {
            body * { visibility: hidden; }
            #teacher-badge-section, #teacher-badge-section * { visibility: visible; }
            #teacher-badge-section { position: absolute; left: 0; top: 0; border: 0; }
        }
    </style>

    <script>
        (() => {
            const tabs = document.querySelectorAll('.student-tab');
            const panels = document.querySelectorAll('[data-panel]');
            const show = (key) => {
                if (!document.querySelector(`[data-panel="${key}"]`)) key = 'infos';
                panels.forEach(p => p.classList.toggle('hidden', p.dataset.panel !== key));
                tabs.forEach(t => t.classList.toggle('is-active', t.dataset.tab === key));
                history.replaceState(null, '', '#' + key);
            };
            tabs.forEach(t => t.addEventListener('click', () => show(t.dataset.tab)));
            show(location.hash.slice(1) || 'infos');
        })();
    </script>
</x-app-layout>
