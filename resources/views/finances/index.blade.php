<x-app-layout>
    <style>
        .finance-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr));
            gap: 0.75rem;
        }
    </style>
    <div class="module-page mx-auto max-w-7xl space-y-4 p-4 md:p-5">
        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800"><x-icon name="banknotes" class="h-6 w-6 text-emerald-600" /> Finances</h1>
                <p class="mt-1 text-sm text-slate-500">Encaissements, dépenses et solde de l'année scolaire.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($currentYear)
                    <span class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm">
                        {{ $currentYear->name }}
                    </span>
                @endif
                <a href="{{ route('finances.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                    <x-icon name="plus" /> Enregistrer un paiement
                </a>
                <a href="{{ route('finances.expenses.create') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2" style="background-color: #dc2626; color: #fff;">
                    <x-icon name="banknotes" /> Enregistrer une dépense
                </a>
            </div>
        </div>

        <x-flash-messages />

        <nav class="flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
            <a href="#tableau-de-bord" class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white">Tableau de bord</a>
            <a href="#paiements" class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Encaissements</a>
            <a href="#depenses" class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Dépenses</a>
        </nav>

        <div id="tableau-de-bord" class="finance-stat-grid">
            <section class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Total encaissé</p>
                    <p class="mt-1 text-xl font-bold text-slate-800">{{ number_format((float) $totalCollected, 0, ',', ' ') }} <span class="text-xs font-medium">{{ $currency ?? 'FCFA' }}</span></p>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600"><x-icon name="download" class="h-5 w-5" /></span>
            </section>
            <section class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Dépenses</p>
                    <p class="mt-1 text-xl font-bold text-amber-600">{{ number_format((float) $totalExpenses, 0, ',', ' ') }} <span class="text-xs font-medium">{{ $currency ?? 'FCFA' }}</span></p>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><x-icon name="banknotes" class="h-5 w-5" /></span>
            </section>
            <section class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Solde net</p>
                    <p class="mt-1 text-xl font-bold text-emerald-700">{{ number_format((float) $totalCollected - (float) $totalExpenses, 0, ',', ' ') }} <span class="text-xs font-medium">{{ $currency ?? 'FCFA' }}</span></p>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><x-icon name="wallet" class="h-5 w-5" /></span>
            </section>
            <section class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Aujourd'hui</p>
                    <p class="mt-1 text-xl font-bold text-slate-800">{{ number_format((float) $todayCollected, 0, ',', ' ') }} <span class="text-xs font-medium">{{ $currency ?? 'FCFA' }}</span></p>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"><x-icon name="calendar" class="h-5 w-5" /></span>
            </section>
            <section class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Ce mois</p>
                    <p class="mt-1 text-xl font-bold text-slate-800">{{ number_format((float) $monthlyCollected, 0, ',', ' ') }} <span class="text-xs font-medium">{{ $currency ?? 'FCFA' }}</span></p>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-600"><x-icon name="calendar" class="h-5 w-5" /></span>
            </section>
            <section class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Encaissements</p>
                    <p class="mt-1 text-xl font-bold text-slate-800">{{ $paymentsCount }}</p>
                </div>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><x-icon name="receipt" class="h-5 w-5" /></span>
            </section>
        </div>

        <section id="paiements" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-4 lg:flex-row lg:items-center lg:justify-between">
                <h2 class="shrink-0 font-bold text-slate-800">Encaissements récents</h2>
                <form method="GET" action="{{ route('finances.index') }}" class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                    <select name="class_room_id" class="w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-44">
                        <option value="">Toutes les classes</option>
                        @foreach($classes as $classRoom)
                            <option value="{{ $classRoom->id }}" @selected($classId == $classRoom->id)>{{ $classRoom->name }}</option>
                        @endforeach
                    </select>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Élève, matricule ou référence" class="min-w-0 flex-1 rounded-lg border-slate-200 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-56">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700"><x-icon name="search" /> Rechercher</button>
                    @if(request('search') || $classId)
                        <a href="{{ route('finances.index') }}" class="shrink-0 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">Effacer</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="p-4">Date</th>
                            <th class="p-4">Élève</th>
                            <th class="p-4">Classe</th>
                            <th class="p-4">Type de frais</th>
                            <th class="p-4">Mois concerné</th>
                            <th class="p-4">Montant</th>
                            <th class="p-4">Mode</th>
                            <th class="p-4">N° reçu</th>
                            <th class="p-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50/70">
                                <td class="whitespace-nowrap p-4">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td class="p-4">
                                    <div class="font-semibold text-slate-800">{{ $payment->student->first_name }} {{ $payment->student->last_name }}</div>
                                    <div class="text-xs text-slate-400">{{ $payment->student->registration_number }}</div>
                                </td>
                                <td class="p-4">{{ $payment->classRoom->name ?? $payment->student->classRoom->name ?? $payment->student->class_name }}</td>
                                <td class="p-4">{{ $payment->fee_type === 'inscription' ? 'Inscription' : 'Scolarité' }}</td>
                                <td class="p-4">{{ $payment->fee_month?->format('m/Y') ?? '—' }}</td>
                                <td class="whitespace-nowrap p-4 font-semibold">{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                <td class="p-4">{{ $payment->is_subsidy ? 'Subvention État' : str_replace('_', ' ', ucfirst($payment->payment_method)) }}</td>
                                <td class="p-4">{{ $payment->receipt_number ?: $payment->reference ?: '—' }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('finances.receipt', $payment) }}" title="Afficher le reçu" class="inline-flex rounded-lg p-2 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"></path></svg>
                                    </a>
                                    <form action="{{ route('finances.destroy', $payment) }}" method="POST" onsubmit="return confirm('Supprimer cet enregistrement de paiement ?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Supprimer le paiement" class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-10 text-center text-slate-500">Aucun paiement enregistré pour cette année scolaire.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section id="depenses" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 p-4">
                <h2 class="font-bold text-slate-800">Dépenses récentes</h2>
                <a href="{{ route('finances.expenses.create') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-700 hover:text-amber-800"><x-icon name="plus" /> Ajouter une dépense</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="p-4">Date</th>
                            <th class="p-4">Catégorie</th>
                            <th class="p-4">Motif</th>
                            <th class="p-4">Montant</th>
                            <th class="p-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($expenses as $expense)
                            <tr>
                                <td class="p-4">{{ $expense->expense_date->format('d/m/Y') }}</td>
                                <td class="p-4">{{ $expenseCategories[$expense->category] ?? $expense->category }}</td>
                                <td class="p-4">
                                    @if($expense->teacher)
                                        <span class="mb-1 block text-xs font-semibold text-slate-700">{{ $expense->teacher->last_name }} {{ $expense->teacher->first_name }}</span>
                                    @endif
                                    {{ $expense->description }}
                                </td>
                                <td class="whitespace-nowrap p-4 font-semibold text-amber-700">{{ number_format((float) $expense->amount, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                <td class="p-4 text-right">
                                    <form action="{{ route('finances.expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Supprimer cette dépense ?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Supprimer la dépense" class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 011-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-10 text-center text-slate-500">Aucune dépense enregistrée pour cette année scolaire.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

</x-app-layout>
