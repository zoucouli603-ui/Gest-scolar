<x-app-layout>
    <div class="module-page mx-auto max-w-7xl space-y-5 p-4 md:p-5">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center print:hidden">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800"><x-icon name="receipt" class="h-6 w-6 text-rose-600" /> Rapports et statistiques</h1>
                <p class="mt-1 text-sm text-slate-500">Rapport financier imprimable avec recettes, frais scolaires, salaires et dépenses.</p>
            </div>
            <button type="button" onclick="window.print()" class="rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                Imprimer la fiche
            </button>
        </div>

        @if($schoolYears->isEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 print:hidden">
                Créez une année scolaire avant de consulter les rapports.
                <a href="{{ route('school-years.index') }}" class="ml-1 font-semibold underline">Gérer les années scolaires</a>
            </div>
        @else
            <section class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm print:hidden">
                <form id="report-filter-form" method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 gap-2 md:grid-cols-[1fr_1fr_1fr_auto]">
                    <label for="school-year-filter" class="sr-only">Année scolaire</label>
                    <select id="school-year-filter" name="school_year_id" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($schoolYears as $year)
                            <option value="{{ $year->id }}" @selected($schoolYear?->id === $year->id)>{{ $year->name }}{{ $year->is_current ? ' (active)' : '' }}</option>
                        @endforeach
                    </select>
                    <label for="cycle-filter" class="sr-only">Cycle</label>
                    <select id="cycle-filter" name="cycle" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tous les cycles</option>
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle }}" @selected($selectedCycle === $cycle)>{{ $cycle }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <label for="period-type-filter" class="sr-only">Période du rapport</label>
                        <select id="period-type-filter" name="period_type" class="min-w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="daily" @selected($periodType === 'daily')>Jour</option>
                            <option value="monthly" @selected($periodType === 'monthly')>Mois</option>
                            <option value="yearly" @selected($periodType === 'yearly')>Année</option>
                        </select>
                        <label for="period-filter" class="sr-only">Date ou période</label>
                        <input id="period-filter" name="period" type="{{ $periodType === 'daily' ? 'date' : ($periodType === 'yearly' ? 'number' : 'month') }}" value="{{ $period }}" @if($periodType === 'yearly') min="2000" max="2100" @endif required class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Actualiser</button>
                </form>
            </section>

            <section id="report-sheet" class="space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:p-6">
                <header class="border-b-2 border-slate-800 pb-4 text-center">
                    <x-doc-logo />
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-600">Établissement scolaire</p>
                    <h1 class="mt-1 text-xl font-bold uppercase text-slate-900">{{ $schoolName }}</h1>
                    <h2 class="mt-3 text-lg font-bold uppercase text-slate-900 underline">Rapport financier {{ $periodType === 'daily' ? 'journalier' : ($periodType === 'yearly' ? 'annuel' : 'mensuel') }}</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-700">Période : {{ $periodLabel }} · Année scolaire : {{ $schoolYear?->name ?? '—' }}</p>
                </header>

                <section class="grid grid-cols-1 gap-x-8 gap-y-1 rounded-lg border border-slate-300 p-3 text-xs sm:grid-cols-2">
                    <p><strong>Établissement :</strong> {{ $schoolName }}</p>
                    <p><strong>Cycle :</strong> {{ $selectedCycle ?? 'Tous les cycles' }}</p>
                    <p><strong>Édité le :</strong> {{ $generatedAt->format('d/m/Y à H:i') }}</p>
                    <p><strong>Généré par :</strong> {{ $reportAuthor }}</p>
                </section>

                <section>
                    <h2 class="report-section-title">I. Résumé financier global</h2>
                    <div class="overflow-x-auto">
                        <table class="report-table w-full min-w-[640px]">
                            <tbody>
                                <tr><th>Total des recettes encaissées</th><td>{{ number_format($totalCollected, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td></tr>
                                <tr><th>Nombre de paiements élèves</th><td>{{ $payments->count() }}</td></tr>
                                <tr><th>Élèves ayant payé pendant la période</th><td>{{ $paidStudentsCount }} / {{ $students->count() }}</td></tr>
                                <tr><th>Élèves sans paiement pendant la période</th><td>{{ $unpaidStudentsCount }}</td></tr>
                                @if($periodType === 'monthly')
                                    <tr><th>Frais mensuels attendus (selon classe)</th><td>{{ number_format($expectedMonthlyFees, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td></tr>
                                    <tr><th>Reste dû du mois (scolarité uniquement)</th><td>{{ number_format($debtorRows->sum('remaining'), 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td></tr>
                                    <tr><th>Élèves débiteurs</th><td>{{ $debtorRows->count() }}</td></tr>
                                @endif
                                <tr><th>Total des dépenses, salaires inclus</th><td>{{ number_format($totalExpenses, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td></tr>
                                <tr><th>Nombre de dépenses</th><td>{{ $expenses->count() }}</td></tr>
                                <tr><th>Total des salaires enseignants payés</th><td>{{ number_format($salaryTotal, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td></tr>
                                <tr><th>Nombre de paiements de salaires</th><td>{{ $salaryExpenses->count() }}</td></tr>
                                <tr><th>Masse salariale mensuelle prévue</th><td>{{ number_format($expectedMonthlyPayroll, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td></tr>
                                <tr class="report-total"><th>Solde net établissement (tous cycles, recettes − dépenses)</th><td>{{ number_format($netBalance, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section>
                    <h2 class="report-section-title">II. Recettes par type de frais</h2>
                    <div class="overflow-x-auto">
                        <table class="report-table w-full min-w-[580px]">
                            <thead><tr><th>N°</th><th>Type de frais</th><th>Nombre</th><th>Montant total</th><th>% recettes</th></tr></thead>
                            <tbody>
                                @php($typeLabels = ['inscription' => 'Inscription', 'scolarite' => 'Scolarité mensuelle'])
                                @forelse($paymentTypes as $type => $stats)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $typeLabels[$type] ?? ucfirst($type) }}</td>
                                        <td>{{ $stats['count'] }}</td>
                                        <td>{{ number_format($stats['total'], 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                        <td>{{ $totalCollected > 0 ? number_format($stats['total'] / $totalCollected * 100, 1, ',', ' ') : '0,0' }} %</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">Aucun paiement sur la période.</td></tr>
                                @endforelse
                                <tr class="report-total"><th colspan="2">TOTAL</th><th>{{ $payments->count() }}</th><th>{{ number_format($totalCollected, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</th><th>100 %</th></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                @if($periodType === 'monthly')
                    <section>
                        <h2 class="report-section-title">III. Élèves débiteurs du mois</h2>
                        <div class="overflow-x-auto">
                            <table class="report-table w-full min-w-[700px]">
                                <thead><tr><th>N°</th><th>Élève</th><th>Cycle / classe</th><th>Parent / tuteur</th><th>Attendu</th><th>Payé</th><th>Reste</th></tr></thead>
                                <tbody>
                                    @forelse($debtorRows as $row)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $row['student']->last_name }} {{ $row['student']->first_name }}</td>
                                            <td>{{ $row['class']?->name ?? $row['student']->class_name }}</td>
                                            <td>{{ $row['student']->parent_name ?: '—' }}</td>
                                            <td class="text-right">{{ number_format($row['due'], 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                            <td class="text-right">{{ number_format($row['paid'], 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                            <td class="text-right font-semibold">{{ number_format($row['remaining'], 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center">Aucun élève débiteur pour ce mois.</td></tr>
                                    @endforelse
                                    <tr class="report-total"><th colspan="6">TOTAL RESTANT DÛ</th><th class="text-right">{{ number_format($debtorRows->sum('remaining'), 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</th></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                <section>
                    <h2 class="report-section-title">{{ $periodType === 'monthly' ? 'IV' : 'III' }}. Salaires des enseignants payés</h2>
                    <div class="mb-3 overflow-x-auto">
                        <table class="report-table w-full min-w-[500px]">
                            <thead><tr><th>Enseignant</th><th>Matière(s)</th><th>Statut</th></tr></thead>
                            <tbody>
                                @forelse($salaryPayrollRows as $row)
                                    <tr>
                                        <td>{{ $row['teacher']->last_name }} {{ $row['teacher']->first_name }}</td>
                                        <td>{{ $row['teacher']->declaredSubjects->pluck('name')->join(', ') ?: ($row['teacher']->specialty ?: '—') }}</td>
                                        <td>{{ ucfirst($row['teacher']->status) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center">Aucun enseignant enregistré pour cette année scolaire.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="report-table w-full min-w-[700px]">
                            <thead><tr><th>Date</th><th>Enseignant</th><th>Motif / période</th><th>Montant payé</th></tr></thead>
                            <tbody>
                                @forelse($salaryExpenses as $salary)
                                    <tr>
                                        <td>{{ $salary->expense_date->format('d/m/Y') }}</td>
                                        <td>{{ $salary->teacher ? $salary->teacher->last_name.' '.$salary->teacher->first_name : 'Enseignant non associé' }}</td>
                                        <td>{{ $salary->description }}</td>
                                        <td class="text-right">{{ number_format((float) $salary->amount, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">Aucun paiement de salaire enregistré sur cette période.</td></tr>
                                @endforelse
                                <tr class="report-total"><th colspan="3">TOTAL SALAIRES PAYÉS · {{ $salaryExpenses->count() }} paiement(s)</th><th class="text-right">{{ number_format($salaryTotal, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</th></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section>
                    <h2 class="report-section-title">{{ $periodType === 'monthly' ? 'V' : 'IV' }}. Liste détaillée des autres dépenses</h2>
                    <div class="overflow-x-auto">
                        <table class="report-table w-full min-w-[700px]">
                            <thead><tr><th>Date</th><th>Catégorie</th><th>Motif</th><th>Montant</th></tr></thead>
                            <tbody>
                                @forelse($otherExpenses as $expense)
                                    <tr>
                                        <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                                        <td>{{ \App\Http\Controllers\ExpenseController::CATEGORIES[$expense->category] ?? $expense->category }}</td>
                                        <td>{{ $expense->description }}</td>
                                        <td class="text-right">{{ number_format((float) $expense->amount, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">Aucune autre dépense sur cette période.</td></tr>
                                @endforelse
                                <tr class="report-total"><th colspan="3">TOTAL DES AUTRES DÉPENSES</th><th class="text-right">{{ number_format($otherExpenses->sum(fn ($expense) => (float) $expense->amount), 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</th></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                @if($selectedCycle)
                    <p class="text-xs text-slate-600">Les dépenses et le solde net sont affichés pour l’établissement entier, car les dépenses ne sont pas attribuées à un cycle. Les recettes de la fiche restent filtrées sur {{ $selectedCycle }}.</p>
                @endif

                <section>
                    <h2 class="report-section-title">{{ $periodType === 'monthly' ? 'VI' : 'V' }}. Synthèse des dépenses par catégorie</h2>
                    <div class="overflow-x-auto">
                        <table class="report-table w-full min-w-[560px]">
                            <thead><tr><th>Catégorie</th><th>Nombre d’opérations</th><th>Montant total</th><th>% dépenses</th></tr></thead>
                            <tbody>
                                @forelse($expensesByCategory as $category => $stats)
                                    <tr>
                                        <td>{{ \App\Http\Controllers\ExpenseController::CATEGORIES[$category] ?? ucfirst($category) }}</td>
                                        <td>{{ $stats['count'] }}</td>
                                        <td>{{ number_format($stats['total'], 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                        <td>{{ $totalExpenses > 0 ? number_format($stats['total'] / $totalExpenses * 100, 1, ',', ' ') : '0,0' }} %</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">Aucune dépense sur la période.</td></tr>
                                @endforelse
                                <tr class="report-total"><th>TOTAL DÉPENSES</th><th>{{ $expenses->count() }}</th><th>{{ number_format($totalExpenses, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</th><th>100 %</th></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section>
                    <h2 class="report-section-title">{{ $periodType === 'monthly' ? 'VII' : 'VI' }}. Effectifs et frais mensuels par cycle et par classe</h2>
                    <div class="overflow-x-auto">
                        <table class="report-table w-full min-w-[650px]">
                            <thead><tr><th>Cycle / classe</th><th>Effectif</th><th>Tarif mensuel</th><th>Total mensuel attendu</th><th>Recettes de la période</th></tr></thead>
                            <tbody>
                                @forelse($classStats as $stat)
                                    <tr>
                                        <td>{{ $stat['class']->name }}</td>
                                        <td>{{ $stat['students_count'] }}</td>
                                        <td class="text-right">{{ number_format((float) $stat['class']->monthly_fee, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                        <td class="text-right">{{ number_format($stat['expected_monthly'], 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                        <td class="text-right">{{ number_format($stat['collected'], 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">Aucune classe dans ce périmètre.</td></tr>
                                @endforelse
                                @foreach($cycleStats as $stat)
                                    @if($stat['classes_count'] > 0)
                                        <tr class="report-subtotal">
                                            <th>Sous-total</th>
                                            <th>{{ $stat['students_count'] }}</th>
                                            <td></td>
                                            <th class="text-right">{{ number_format($stat['expected_monthly'], 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</th>
                                            <th class="text-right">{{ number_format($stat['collected'], 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}</th>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <footer class="grid grid-cols-1 gap-8 pt-8 text-center text-xs sm:grid-cols-3">
                    <div class="signature">Fait à __________________<br>Le {{ $generatedAt->format('d/m/Y') }}<br><br>Le comptable / gestionnaire</div>
                    <div class="signature">Cachet de l’établissement</div>
                    <div class="signature">Vu et approuvé<br><br>La direction</div>
                </footer>
            </section>
        @endif
    </div>

    <style>
        .report-section-title { margin: 0 0 0.5rem; border-bottom: 2px solid #334155; padding-bottom: 0.35rem; font-size: 0.9rem; font-weight: 700; color: #1e293b; }
        .report-table { border-collapse: collapse; font-size: 0.73rem; }
        .report-table th, .report-table td { border: 1px solid #94a3b8; padding: 0.4rem 0.5rem; vertical-align: top; }
        .report-table thead th { background: #e2e8f0; text-transform: uppercase; color: #334155; }
        .report-table tbody th { text-align: left; font-weight: 600; }
        .report-table tbody td { text-align: right; }
        .report-table tbody td:first-child:not([colspan]) { text-align: left; }
        .report-table .report-total th, .report-table .report-total td { border-top: 2px solid #334155; background: #f1f5f9; font-weight: 700; }
        .report-table .report-subtotal th { background: #f8fafc; font-weight: 700; }
        .signature { border-top: 1px solid #64748b; padding-top: 0.5rem; }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            html, body { width: 100% !important; height: auto !important; min-height: 0 !important; overflow: visible !important; background: #fff !important; }
            body > div { width: 100% !important; height: auto !important; min-height: 0 !important; overflow: visible !important; }
            body > div > aside { display: none !important; }
            body > div > main { width: 100% !important; height: auto !important; min-height: 0 !important; overflow: visible !important; padding: 0 !important; }
            .print\:hidden { display: none !important; }
            #report-sheet { max-width: none !important; margin: 0 !important; padding: 0 !important; border: 0 !important; box-shadow: none !important; }
            #report-sheet section, #report-sheet header, #report-sheet table { break-inside: avoid; }
            #report-sheet .report-table { font-size: 8pt; }
            #report-sheet .report-table th, #report-sheet .report-table td { padding: 4px; }
            .module-page { max-width: none !important; padding: 0 !important; }
            .report-table th, .report-table td { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('report-filter-form');
            const periodType = document.getElementById('period-type-filter');
            const period = document.getElementById('period-filter');
            if (!form || !periodType || !period) return;

            periodType.addEventListener('change', () => {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                if (periodType.value === 'daily') {
                    period.type = 'date';
                    period.value = `${year}-${month}-${day}`;
                } else if (periodType.value === 'yearly') {
                    period.type = 'number';
                    period.min = '2000';
                    period.max = '2100';
                    period.value = String(year);
                } else {
                    period.type = 'month';
                    period.removeAttribute('min');
                    period.removeAttribute('max');
                    period.value = `${year}-${month}`;
                }
            });
        });
    </script>
</x-app-layout>
