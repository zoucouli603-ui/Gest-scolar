@php($formatAmount = fn ($amount) => number_format((float) $amount, 0, ',', ' ').' '.$currency)
    <div class="mx-auto max-w-7xl space-y-6 p-4 md:p-6">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
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

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicateurs financiers">
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
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Effectifs de l’année active</h2>
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
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Suivi mensuel</h2>
                <p class="mt-1 text-sm text-slate-500">Les encaissements sont ventilés selon le mois de scolarité facturé.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="whitespace-nowrap px-5 py-3">Mois</th>
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
                                <td class="whitespace-nowrap px-5 py-3 text-right text-slate-700">{{ $formatAmount($month['forecast']) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-emerald-700">{{ $formatAmount($month['collected']) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-slate-700">{{ number_format($month['recovery_rate'], 1, ',', ' ') }} %</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-rose-700">{{ $formatAmount($month['remaining_claim']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
