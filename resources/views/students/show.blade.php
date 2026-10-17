@php
    $currency = auth()->user()?->school?->currency ?? 'XOF';
    $payments = $student->payments()->latest('payment_date')->latest('id')->get();
    $totalPaid = $payments->sum('amount');
    $schoolName = $student->school_name ?: (auth()->user()?->school?->name ?? '—');
    $gender = match ($student->gender) { 'M' => 'Masculin', 'F' => 'Féminin', default => $student->gender ?: '—' };
    $fmt = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
    $label = 'text-[11px] font-bold text-slate-400 uppercase tracking-wider';
    $value = 'mt-1 text-base font-semibold text-slate-800';
    $tabs = [
        'infos' => ['Infos', 'user'],
        'finances' => ['Finances', 'wallet'],
        'documents' => ['Documents', 'id-card'],
    ];
@endphp

<x-app-layout>
    <div class="p-6 max-w-6xl mx-auto">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between gap-4 px-6 py-5 border-b border-slate-200 bg-slate-50/70">
                <div class="flex items-center gap-4 min-w-0">
                    @if($student->photo_path)
                        <img src="{{ url('storage/'.ltrim($student->photo_path, '/')) }}" alt="Photo de {{ $student->first_name }} {{ $student->last_name }}" class="h-16 w-16 shrink-0 rounded-full object-cover shadow-sm">
                    @else
                        <div class="w-16 h-16 shrink-0 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl shadow-sm">
                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h1 class="truncate text-3xl font-black text-slate-900 uppercase tracking-tight">{{ $student->first_name }} {{ $student->last_name }}</h1>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                            <span>{{ $student->class_name ?? 'Non assignée' }}</span>
                            <span>•</span>
                            <span>{{ $student->registration_number }}</span>
                            @if($student->status === 'actif')
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-semibold rounded-full uppercase">Actif</span>
                            @elseif($student->status === 'inactif')
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-semibold rounded-full uppercase">Inactif</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('students.edit', $student) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                        <x-icon name="pencil" /> Modifier
                    </a>
                    <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition shadow-sm">
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
                {{-- Infos --}}
                <div data-panel="infos" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2 space-y-6">
                        <section class="rounded-2xl border border-slate-200 overflow-hidden">
                            <div class="flex items-center gap-2 bg-slate-50 px-4 py-3 border-b border-slate-200 text-slate-600">
                                <x-icon name="user" />
                                <h2 class="text-sm font-bold uppercase tracking-wide">Informations personnelles</h2>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                                <div><p class="{{ $label }}">Prénom</p><p class="{{ $value }}">{{ $student->first_name }}</p></div>
                                <div><p class="{{ $label }}">Nom</p><p class="{{ $value }}">{{ $student->last_name }}</p></div>
                                <div><p class="{{ $label }}">Sexe</p><p class="{{ $value }}">{{ $gender }}</p></div>
                                <div><p class="{{ $label }}">Date de naissance</p><p class="{{ $value }}">{{ $fmt($student->birth_date) }}</p></div>
                                <div><p class="{{ $label }}">Lieu de naissance</p><p class="{{ $value }}">{{ $student->birth_place ?? 'Non renseigné' }}</p></div>
                                <div><p class="{{ $label }}">Nationalité</p><p class="{{ $value }}">{{ $student->nationality ?? 'Non renseignée' }}</p></div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 overflow-hidden">
                            <div class="flex items-center gap-2 bg-slate-50 px-4 py-3 border-b border-slate-200 text-slate-600">
                                <x-icon name="users" />
                                <h2 class="text-sm font-bold uppercase tracking-wide">Parent / Tuteur</h2>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                                <div><p class="{{ $label }}">Nom complet</p><p class="{{ $value }}">{{ $student->parent_name ?? '—' }}</p></div>
                                <div><p class="{{ $label }}">Téléphone</p><p class="{{ $value }} flex items-center gap-2"><x-icon name="phone" class="h-4 w-4 text-slate-400" />{{ $student->parent_phone ?? '—' }}</p></div>
                            </div>
                        </section>
                    </div>

                    <section class="rounded-2xl border border-slate-200 overflow-hidden self-start">
                        <div class="flex items-center gap-2 bg-slate-50 px-4 py-3 border-b border-slate-200 text-slate-600">
                            <x-icon name="academic" />
                            <h2 class="text-sm font-bold uppercase tracking-wide">Scolarité</h2>
                        </div>
                        <div class="p-4 space-y-4">
                            <div><p class="{{ $label }}">École</p><p class="{{ $value }}">{{ $schoolName }}</p></div>
                            <div><p class="{{ $label }}">Matricule</p><p class="{{ $value }}">{{ $student->registration_number }}</p></div>
                            <div><p class="{{ $label }}">Classe</p><p class="{{ $value }}">{{ $student->class_name ?? 'Non assignée' }}</p></div>
                            <div><p class="{{ $label }}">Année scolaire</p><p class="{{ $value }}">{{ $student->schoolYear?->name ?? '—' }}</p></div>
                            <div><p class="{{ $label }}">Date d'inscription</p><p class="{{ $value }}">{{ $fmt($student->inscription_date) }}</p></div>
                        </div>
                    </section>
                </div>

                {{-- Finances --}}
                <div data-panel="finances" class="hidden space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="inline-flex items-center gap-3 rounded-2xl bg-emerald-50 px-4 py-3 text-emerald-800">
                            <x-icon name="banknotes" class="h-6 w-6" />
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider">Total encaissé</p>
                                <p class="text-lg font-bold">{{ number_format((float) $totalPaid, 0, ',', ' ') }} {{ $currency }}</p>
                            </div>
                        </div>
                        <a href="{{ route('finances.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            <x-icon name="plus" /> Enregistrer un paiement
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="p-3">Date</th><th class="p-3">Type</th><th class="p-3">Montant</th><th class="p-3">Mode</th><th class="p-3">Reçu</th><th class="p-3 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($payments as $payment)
                                    <tr>
                                        <td class="p-3">{{ $fmt($payment->payment_date) }}</td>
                                        <td class="p-3">{{ $payment->fee_type === 'inscription' ? 'Inscription' : 'Scolarité' }}</td>
                                        <td class="p-3 font-semibold">{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $currency }}</td>
                                        <td class="p-3">{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</td>
                                        <td class="p-3">{{ $payment->receipt_number ?: $payment->reference ?: '—' }}</td>
                                        <td class="p-3 text-right">
                                            <a href="{{ route('finances.receipt', $payment) }}" title="Afficher le reçu" class="inline-flex rounded-lg p-2 text-slate-400 hover:bg-blue-50 hover:text-blue-600"><x-icon name="receipt" class="h-5 w-5" /></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="p-8 text-center text-slate-400">Aucun paiement enregistré pour cet élève.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Documents --}}
                <div data-panel="documents" class="hidden">
                    <div id="student-card-section" class="rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between bg-slate-50 px-4 py-3 border-b border-slate-200 print:hidden">
                            <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-600"><x-icon name="id-card" /> Carte de l'élève</h2>
                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 transition">
                                <x-icon name="printer" /> Imprimer
                            </button>
                        </div>
                        <div class="flex justify-center p-6">
                            <x-student-card :student="$student" />
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 print:hidden">
                        @foreach(['inscription' => 'Fiche d’inscription', 'scolarite' => 'Certificat de scolarité', 'transfert' => 'Certificat de transfert', 'scolaire' => 'Fiche scolaire'] as $type => $title)
                            <a href="{{ route('documents.print', [$student, $type]) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 transition">
                                <x-icon name="document" class="h-5 w-5" /> {{ $title }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .student-tab.is-active { border-color: #2563eb; color: #1d4ed8; }
        @media print {
            body * { visibility: hidden; }
            #student-card-section, #student-card-section * { visibility: visible; }
            #student-card-section { position: absolute; left: 0; top: 0; border: 0; }
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
