@php($formatAmount = fn ($amount) => number_format((float) $amount, 0, ',', ' ').' '.$currency)
    <style>
        @media print {
            @page { margin: 12mm; }
            html, body, body > div { height: auto !important; min-height: 0 !important; overflow: visible !important; background: #fff !important; }
            body > div { display: block !important; }
            .timetable-sidebar, #global-search, .financial-no-print { display: none !important; }
            main { display: block !important; width: 100% !important; height: auto !important; min-width: 0 !important; overflow: visible !important; padding: 0 !important; }
            .financial-report { max-width: none !important; padding: 0 !important; }
            .financial-print-heading { display: block !important; }
            .financial-report section, .financial-report article { break-inside: avoid; box-shadow: none !important; }
            .financial-report section, .financial-report .overflow-x-auto { overflow: visible !important; }
            .financial-report { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        .financial-print-heading { display: none; }
    </style>
    <div id="financial-report" class="financial-report mx-auto max-w-7xl space-y-6 p-4 md:p-6">
        <div class="financial-no-print flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Synthèse financière et recouvrement</h1>
                <p class="mt-1 text-sm text-slate-500">
                    @if($currentYear)
                        Année scolaire {{ $currentYear->name }} · Prévision calculée sur 12 mois
                    @else
                        Aucune année scolaire active n’est définie.
                    @endif
                </p>
            </div>
            <a href="{{ route('finances.index') }}" class="inline-flex w-fit items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Retour aux finances
            </a>
        </div>

        <header class="financial-print-heading border-b border-slate-300 pb-4 text-center">
            <x-doc-logo style="display:block;margin:0 auto 8px;height:64px;max-width:180px;object-fit:contain" />
            <h1 class="text-xl font-bold text-slate-900">{{ auth()->user()?->school?->name ?? 'Établissement scolaire' }}</h1>
            <p class="mt-1 text-sm text-slate-700">Synthèse financière et recouvrement · {{ $currentYear?->name ?? 'Année scolaire' }}</p>
        </header>

        <div class="financial-no-print flex justify-end">
            <button type="button" data-print class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Imprimer la fiche
            </button>
        </div>

        <section class="financial-no-print rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
            <form wire:submit="saveForecast" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="annual-forecast" class="block text-sm font-semibold text-indigo-950">Prévisionnel annuel manuel ({{ $currency }})</label>
                    <p class="mt-1 text-xs text-indigo-800">Saisissez le montant annuel attendu. Il sera réparti sur les 12 mois du tableau.</p>
                    <input id="annual-forecast" type="number" min="0" step="1" wire:model="annualForecastInput"
                           class="mt-2 w-full rounded-lg border-indigo-200 bg-white px-3 py-2.5 text-lg font-semibold text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-md"
                           @disabled(!$currentYear)>
                    @error('annualForecastInput')
                        <p class="mt-1 text-sm font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                    @if($forecastMessage)
                        <p class="mt-1 text-sm font-medium text-emerald-700" role="status">{{ $forecastMessage }}</p>
                    @endif
                </div>
                <button type="submit" @disabled(!$currentYear)
                        class="rounded-lg bg-indigo-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800 disabled:cursor-not-allowed disabled:opacity-50">
                    Enregistrer le prévisionnel
                </button>
            </form>
        </section>

        <section class="space-y-4" aria-labelledby="financial-summary-title">
            <h2 id="financial-summary-title" class="text-xl font-bold uppercase tracking-wide text-slate-900">SYNTHÈSE FINANCIÈRE ET EFFECTIFS</h2>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicateurs financiers">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Prévisionnel annuel</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $formatAmount($annualForecast) }}</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Réalisé total</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $formatAmount($realizedTotal) }}</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Créance restante</p>
                    <p class="mt-2 text-2xl font-bold text-rose-700">{{ $formatAmount($remainingClaim) }}</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Taux global de recouvrement</p>
                    <p class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($globalRecoveryRate, 1, ',', ' ') }} %</p>
                </article>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Effectifs de l’année active</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Effectif total actif</p>
                        <p class="mt-1 text-xl font-bold text-slate-900">{{ $totalStudents }}</p>
                    </div>
                    <div class="rounded-xl bg-blue-50 p-4">
                        <p class="text-sm text-blue-700">Élèves payants</p>
                        <p class="mt-1 text-xl font-bold text-blue-900">{{ $payingStudents }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-4">
                        <p class="text-sm text-amber-700">Élèves exonérés</p>
                        <p class="mt-1 text-xl font-bold text-amber-900">
                            {{ $exemptStudents }}
                            <span class="text-sm font-medium">({{ number_format($exemptionRate, 1, ',', ' ') }} %)</span>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Tableau de suivi du recouvrement des frais scolaires</h2>
                <p class="mt-1 text-sm text-slate-500">Les encaissements sont ventilés selon le mois de scolarité facturé.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-5 py-3">Mois</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 text-center">Élèves payants</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 text-right">Prévisionnel</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 text-right">Montant encaissé</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 text-right">Taux de recouvrement</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-3 text-right">Créance cumulée</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($monthlyTracking as $month)
                            <tr wire:key="financial-month-{{ $loop->index }}" class="hover:bg-slate-50">
                                <th scope="row" class="whitespace-nowrap px-5 py-3 font-semibold text-slate-800">{{ $month['name'] }}</th>
                                <td class="whitespace-nowrap px-5 py-3 text-center text-slate-700">{{ $month['paying_students'] }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-slate-700">{{ $formatAmount($month['forecast']) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-emerald-700">{{ $formatAmount($month['collected']) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-slate-700">{{ number_format($month['recovery_rate'], 1, ',', ' ') }} %</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-rose-700">{{ $formatAmount($month['remaining_claim']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-300 bg-slate-100 font-bold text-slate-900">
                        <tr>
                            <th scope="row" class="whitespace-nowrap px-5 py-3 text-left">TOTAL / ANNUEL</th>
                            <td class="whitespace-nowrap px-5 py-3 text-center">{{ $payingStudents }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">{{ $formatAmount($annualForecast) }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-emerald-800">{{ $formatAmount($annualCollected) }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">{{ number_format($globalRecoveryRate, 1, ',', ' ') }} %</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-rose-800">{{ $formatAmount($remainingClaim) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>
    <script @cspNonce>
        document.querySelectorAll('[data-print]').forEach((button) => {
            button.addEventListener('click', () => window.print());
        });
    </script>
