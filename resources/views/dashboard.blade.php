<x-app-layout>
    <main class="mx-auto max-w-7xl space-y-6 p-5 md:p-8">
        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    @if ($school->logo_path)
                        <x-school-logo :school="$school" class="h-16 w-16 shrink-0 rounded-xl border border-slate-200 p-1" />
                    @else
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><x-icon name="building" class="h-8 w-8" /></span>
                    @endif
                    <div class="min-w-0">
                        <h1 class="truncate text-2xl font-bold text-slate-900">{{ $school->name }}</h1>
                        <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1"><x-icon name="calendar" class="h-4 w-4" /> {{ $schoolYear ? $schoolYear->name : 'Aucune année scolaire' }}</span>
                            <span>{{ $school->country }} · {{ $school->currency }}</span>
                            <span>{{ $school->institution_type === 'public' ? 'Établissement public' : 'Établissement privé' }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if(Auth::user()->canAccess('students'))
                        <a href="{{ route('students.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"><x-icon name="plus" /> Ajouter un élève</a>
                    @endif
                    @if($canViewFinancialDashboard)
                        <a href="{{ route('finances.create') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"><x-icon name="banknotes" /> Enregistrer un paiement</a>
                    @endif
                </div>
            </div>
        </section>

        @if(!$schoolYear)
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <h2 class="font-semibold text-amber-950">Aucune année scolaire configurée</h2>
                <p class="mt-1 text-sm text-amber-800">Ajoutez une année scolaire pour commencer à enregistrer et consulter les activités de l’établissement.</p>
                <a href="{{ route('school-years.index') }}" class="mt-3 inline-flex rounded-lg bg-amber-700 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-800">Configurer les années scolaires</a>
            </section>
        @endif

        @php
            $subStatus = $school->subscription_status;
            $subTone = match ($subStatus) { 'active', 'actif' => 'bg-emerald-50 text-emerald-700', 'trial', 'essai' => 'bg-amber-50 text-amber-700', default => 'bg-rose-50 text-rose-700' };
            $daysLeft = $school->subscription_ends_at ? (int) now()->startOfDay()->diffInDays($school->subscription_ends_at->startOfDay(), false) : null;
            $inactiveCount = max(0, $studentCount - $activeStudentCount);
            $girlPct = $studentCount ? round($girlCount * 100 / $studentCount) : 0;
            $boyPct = $studentCount ? round($boyCount * 100 / $studentCount) : 0;
            $balance = $totalCollected - $totalExpenses;
            $monthBalance = $collectedThisMonth - $expensesThisMonth;
            $icons = [
                'calendar' => 'M6.75 2.75a.75.75 0 0 1 1.5 0V4h3.5V2.75a.75.75 0 0 1 1.5 0V4h.75A2.25 2.25 0 0 1 16 6.25v8.5A2.25 2.25 0 0 1 13.75 17h-7.5A2.25 2.25 0 0 1 4 14.75v-8.5A2.25 2.25 0 0 1 6.25 4h.5V2.75ZM5.5 8v6.75c0 .41.34.75.75.75h7.5c.41 0 .75-.34.75-.75V8h-9Z',
                'badge' => 'M10 1.5 3 4.2v5.1c0 4 2.7 7.3 7 9.2 4.3-1.9 7-5.2 7-9.2V4.2l-7-2.7Zm3.3 6.9-3.9 4a.75.75 0 0 1-1.06 0l-1.6-1.6a.75.75 0 1 1 1.06-1.06l1.07 1.07 3.37-3.47a.75.75 0 1 1 1.07 1.06Z',
                'users' => 'M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3 16a5 5 0 0 1 10 0v1H3v-1ZM15 8a2.5 2.5 0 1 0-.001-5.001A2.5 2.5 0 0 0 15 8ZM14.7 11.1a4.5 4.5 0 0 1 2.3 3.9v2h-2v-1a6.9 6.9 0 0 0-1.6-4.4c.4-.2.8-.4 1.3-.5Z',
                'check' => 'M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm3.7 6.2-4.5 5a.75.75 0 0 1-1.1.02L6.3 11.4a.75.75 0 1 1 1.1-1.04l1.2 1.27 3.97-4.4a.75.75 0 1 1 1.13 1Z',
                'female' => 'M10 1.5a4.25 4.25 0 0 0-1 8.38V12H7.25a.75.75 0 0 0 0 1.5H9v3.75a.75.75 0 0 0 1.5 0V13.5h1.75a.75.75 0 0 0 0-1.5H10.5V9.88A4.25 4.25 0 0 0 10 1.5Zm-2.75 4.25a2.75 2.75 0 1 1 5.5 0 2.75 2.75 0 0 1-5.5 0Z',
                'male' => 'M12 2.75a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0V4.56l-3.4 3.4a5.25 5.25 0 1 1-1.06-1.06l3.4-3.4H12.75A.75.75 0 0 1 12 2.75ZM8.25 7.5a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5Z',
                'cap' => 'M10 2 2 6l8 4 6-3v5h2V6l-8-4ZM5 9.5V14c3.3 2.7 6.7 2.7 10 0V9.5l-5 2.5-5-2.5Z',
                'board' => 'M3.5 2A1.5 1.5 0 0 0 2 3.5V4h16v-.5A1.5 1.5 0 0 0 16.5 2h-13ZM2 5.5h16v8A1.5 1.5 0 0 1 16.5 15H11v.6l2.2 2.4a.75.75 0 1 1-1.1 1L10 16.7 7.9 19a.75.75 0 1 1-1.1-1L9 15.6V15H3.5A1.5 1.5 0 0 1 2 13.5v-8Z',
                'trend' => 'M2.5 14.25a.75.75 0 0 1 .22-.53l4.5-4.5a.75.75 0 0 1 1.06 0L10.5 11l4.2-4.2h-1.45a.75.75 0 0 1 0-1.5h3.5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V8.3l-4.73 4.73a.75.75 0 0 1-1.06 0L7.75 10.8l-3.97 3.97a.75.75 0 0 1-1.28-.52Z',
                'wallet' => 'M3 5.5A2.5 2.5 0 0 1 5.5 3h8A1.5 1.5 0 0 1 15 4.5V6h.5A1.5 1.5 0 0 1 17 7.5v7a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 3 14.5v-9Zm2.5-1a1 1 0 0 0 0 2h8v-2h-8Zm8.75 7a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z',
                'coins' => 'M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm.75 12h-1.5v-1.1c-1.2-.2-2-.9-2.1-2h1.6c.1.5.5.8 1.2.8.6 0 1-.3 1-.7 0-.5-.5-.7-1.4-1-1.1-.3-2.2-.8-2.2-2 0-1 .7-1.7 1.9-1.9V5h1.5v1.1c1.1.2 1.8.9 1.9 1.9h-1.6c-.1-.5-.4-.7-1-.7-.6 0-.9.2-.9.6 0 .4.4.6 1.3.9 1.3.4 2.3.9 2.3 2.1 0 1-.8 1.8-2 2V14Z',
                'clock' => 'M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm.75 4.25v3.69l2.28 1.52a.75.75 0 1 1-.83 1.25l-2.62-1.75a.75.75 0 0 1-.33-.62V6.25a.75.75 0 0 1 1.5 0Z',
            ];
            $cards = [
                ['Date du jour', null, null, 'sky', 'calendar', null, 'date'],
                ['Abonnement', null, null, 'amber', 'badge', null, 'sub'],
                ['Total des élèves', number_format($studentCount, 0, ',', ' '), $girlCount.' filles · '.$boyCount.' garçons', 'blue', 'users', route('students.index')],
                ['Élèves actifs', number_format($activeStudentCount, 0, ',', ' '), $inactiveCount.' inactif(s)', 'emerald', 'check', route('students.index')],
                ['Filles', (string) $girlCount, $girlPct.' % des élèves', 'rose', 'female', null, 'bar-rose', $girlPct],
                ['Garçons', (string) $boyCount, $boyPct.' % des élèves', 'blue', 'male', null, 'bar-blue', $boyPct],
                ['Enseignants', number_format($teacherCount, 0, ',', ' '), 'Année en cours', 'emerald', 'cap', route('teachers.index')],
                ['Classes', number_format($classCount, 0, ',', ' '), 'Réparties par cycle scolaire', 'violet', 'board', route('classes.index')],
            ];
            if ($canViewFinancialDashboard) {
                $cards = array_merge($cards, [
                    ['Solde du mois', number_format($monthBalance, 0, ',', ' ').' '.$school->currency, '+'.number_format($collectedThisMonth, 0, ',', ' ').' / −'.number_format($expensesThisMonth, 0, ',', ' '), $monthBalance >= 0 ? 'emerald' : 'rose', 'trend', route('reports.index')],
                    ['Solde de l’année', number_format($balance, 0, ',', ' ').' '.$school->currency, '+'.number_format($totalCollected, 0, ',', ' ').' / −'.number_format($totalExpenses, 0, ',', ' '), $balance >= 0 ? 'emerald' : 'rose', 'wallet', route('reports.index')],
                    ['Reste dû du mois', number_format($monthlyOutstanding, 0, ',', ' ').' '.$school->currency, 'Scolarité mensuelle non réglée', 'amber', 'clock', route('reports.index')],
                    ['Recettes du mois', number_format($collectedThisMonth, 0, ',', ' ').' '.$school->currency, 'Dépenses : '.number_format($expensesThisMonth, 0, ',', ' '), 'sky', 'coins', route('finances.index')],
                ]);
            }
            $tones = [
                'sky' => 'bg-sky-50 text-sky-700', 'amber' => 'bg-amber-50 text-amber-700', 'blue' => 'bg-blue-50 text-blue-700',
                'emerald' => 'bg-emerald-50 text-emerald-700', 'rose' => 'bg-rose-50 text-rose-700', 'violet' => 'bg-violet-50 text-violet-700',
            ];
        @endphp
        <section class="grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($cards as $card)
                @php [$label, $value, $hint, $tone, $icon] = $card; $href = $card[5] ?? null; $kind = $card[6] ?? null; @endphp
                <{!! $href ? 'a href="'.e($href).'"' : 'div' !!} class="group flex items-start justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition {{ $href ? 'hover:border-blue-300 hover:shadow-md' : '' }}">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                        @if($kind === 'date')
                            <p class="mt-2 text-lg font-bold capitalize leading-tight text-slate-900">{{ now()->translatedFormat('l') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ now()->translatedFormat('d F Y') }}</p>
                        @elseif($kind === 'sub')
                            <p class="mt-2 text-lg font-bold leading-tight text-slate-900">{{ \App\Models\School::SUBSCRIPTION_PLANS[$school->subscription_plan] ?? ($school->subscription_plan ?: '—') }}</p>
                            <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $subTone }}">{{ \App\Models\School::SUBSCRIPTION_STATUSES[$subStatus] ?? ($subStatus ?: '—') }}</span>
                            <p class="mt-1 text-xs text-slate-500">
                                @if($school->subscription_ends_at)
                                    Échéance {{ $school->subscription_ends_at->format('d/m/Y') }}@if($daysLeft !== null) · {{ $daysLeft >= 0 ? $daysLeft.' j restants' : 'expiré' }}@endif
                                @else Sans échéance @endif
                            </p>
                        @else
                            <p class="mt-2 truncate text-2xl font-bold text-slate-900">{{ $value }}</p>
                            @if(in_array($kind, ['bar-rose', 'bar-blue'], true))
                                <div class="mt-1.5 h-1.5 rounded-full bg-slate-100"><div class="h-1.5 rounded-full {{ $kind === 'bar-rose' ? 'bg-rose-400' : 'bg-blue-500' }}" style="width: {{ $card[7] }}%"></div></div>
                            @endif
                            <p class="mt-1 truncate text-xs text-slate-500">{{ $hint }}</p>
                        @endif
                    </div>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] }}"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="{{ $icons[$icon] }}"/></svg></span>
                </{{ $href ? 'a' : 'div' }}>
            @endforeach
        </section>


        @if($canViewFinancialDashboard)
        <section class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><h2 class="font-semibold text-slate-900">Aperçu financier</h2><p class="mt-1 text-sm text-slate-500">Recettes et dépenses des six derniers mois</p></div>
                    <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Voir les rapports →</a>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-emerald-50 p-4"><p class="text-xs font-medium text-emerald-800">Recettes ce mois</p><p class="mt-1 text-xl font-bold text-emerald-900">{{ number_format($collectedThisMonth, 0, ',', ' ') }} {{ $school->currency }}</p></div>
                    <div class="rounded-xl bg-rose-50 p-4"><p class="text-xs font-medium text-rose-800">Dépenses ce mois</p><p class="mt-1 text-xl font-bold text-rose-900">{{ number_format($expensesThisMonth, 0, ',', ' ') }} {{ $school->currency }}</p></div>
                </div>
                <div class="mt-6 flex h-44 items-end justify-between gap-3 border-b border-slate-100 px-1">
                    @foreach($monthlyChart as $month)
                        @php
                            $collectedHeight = $month['collected'] > 0 ? max(4, ($month['collected'] / $chartMax) * 100) : 0;
                            $expenseHeight = $month['expenses'] > 0 ? max(4, ($month['expenses'] / $chartMax) * 100) : 0;
                        @endphp
                        <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end">
                            <div class="flex h-full w-full items-end justify-center gap-1">
                                <div title="Recettes : {{ number_format($month['collected'], 0, ',', ' ') }} {{ $school->currency }}" class="w-3 rounded-t bg-blue-600 sm:w-5" style="height: {{ $collectedHeight }}%"></div>
                                <div title="Dépenses : {{ number_format($month['expenses'], 0, ',', ' ') }} {{ $school->currency }}" class="w-3 rounded-t bg-rose-300 sm:w-5" style="height: {{ $expenseHeight }}%"></div>
                            </div>
                            <span class="py-2 text-[11px] font-medium text-slate-500">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 flex gap-5 text-xs text-slate-500"><span class="inline-flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-sm bg-blue-600"></i>Recettes</span><span class="inline-flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-sm bg-rose-300"></i>Dépenses</span></div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-3"><div><h2 class="font-semibold text-slate-900">Effectifs par classe</h2><p class="mt-1 text-sm text-slate-500">Répartition de l’année scolaire</p></div><a href="{{ route('classes.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Classes →</a></div>
                <div class="mt-5 space-y-4">
                    @forelse($classCounts->take(6) as $row)
                        @php($width = $studentCount > 0 ? min(100, ($row['students'] / $studentCount) * 100) : 0)
                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-2 text-sm"><span class="truncate font-medium text-slate-700">{{ $row['class']->name }} <span class="text-xs text-slate-400">· {{ $row['class']->cycle }}</span></span><span class="shrink-0 font-semibold text-slate-900">{{ $row['students'] }}</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-600" style="width: {{ $width }}%"></div></div>
                        </div>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-5 text-center"><p class="text-sm text-slate-600">Aucune classe enregistrée pour cette année.</p><a href="{{ route('classes.create') }}" class="mt-2 inline-block text-sm font-semibold text-blue-700">Créer une classe</a></div>
                    @endforelse
                </div>
                @if($classCounts->count() > 6)<p class="mt-4 text-xs text-slate-500">Affichage des 6 classes les plus récentes par ordre de cycle.</p>@endif
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h2 class="font-semibold text-slate-900">Derniers paiements</h2><p class="mt-1 text-xs text-slate-500">Activité récente de l’année scolaire active</p></div><a href="{{ route('finances.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Finances →</a></div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentPayments as $payment)
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <div class="min-w-0"><p class="truncate text-sm font-medium text-slate-800">{{ $payment->student ? $payment->student->first_name.' '.$payment->student->last_name : 'Élève' }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $payment->payment_date?->format('d/m/Y') }} · {{ ucfirst($payment->fee_type) }}</p></div>
                            <span class="shrink-0 text-sm font-bold text-emerald-700">+{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $school->currency }}</span>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Aucun paiement enregistré cette année.</p>
                    @endforelse
                </div>
            </article>
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h2 class="font-semibold text-slate-900">Dernières dépenses</h2><p class="mt-1 text-xs text-slate-500">Suivi des sorties de caisse</p></div><a href="{{ route('finances.expenses.create') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Ajouter →</a></div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentExpenses as $expense)
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <div class="min-w-0"><p class="truncate text-sm font-medium text-slate-800">{{ $expense->description ?: $expense->category }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $expense->expense_date?->format('d/m/Y') }} · {{ $expense->category }}</p></div>
                            <span class="shrink-0 text-sm font-bold text-rose-700">−{{ number_format((float) $expense->amount, 0, ',', ' ') }} {{ $school->currency }}</span>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Aucune dépense enregistrée cette année.</p>
                    @endforelse
                </div>
            </article>
        </section>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h2 class="font-semibold text-slate-900">Accès rapides</h2><p class="mt-1 text-sm text-slate-500">Retrouvez rapidement les principales fonctions de gestion.</p></div>
                <div class="flex flex-wrap gap-2">
                    @if(Auth::user()->canAccess('students'))
                        <a href="{{ route('students.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Élèves</a>
                    @endif
                    @if(Auth::user()->canAccess('notes'))
                        <a href="{{ route('notes.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Notes</a>
                    @endif
                    @if(Auth::user()->canAccess('timetable'))
                        <a href="{{ route('timetable.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Emploi du temps</a>
                    @endif
                    @if(Auth::user()->canAccess('documents'))
                        <a href="{{ route('documents.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Documents</a>
                    @endif
                    @if(Auth::user()->canAccess('reports'))
                        <a href="{{ route('reports.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Rapports</a>
                    @endif
                </div>
            </div>
        </section>
    </main>
</x-app-layout>
