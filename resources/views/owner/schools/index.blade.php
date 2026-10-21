<x-app-layout>
    <main class="mx-auto max-w-7xl space-y-6 p-5 md:p-8">
        <header>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Administration privée</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Écoles utilisant YELENSCOL</h1>
            <p class="mt-1 text-sm text-slate-600">Vue limitée aux coordonnées et à l’activité des espaces. Aucun dossier d’élève ni détail financier n’est accessible ici.</p>
        </header>

        <x-flash-messages />
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-amber-100 bg-amber-50 px-5 py-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Demandes d’ouverture en attente</h2>
                    <p class="mt-1 text-sm text-slate-600">Examinez les informations et approuvez une seule fois chaque nouvel établissement.</p>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">{{ $applications->count() }}</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($applications as $application)
                    <article class="px-5 py-5 sm:px-6">
                        <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2 xl:grid-cols-3">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Établissement</p>
                                <p class="mt-1 break-words font-semibold text-slate-900">{{ $application->name }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $application->institution_type === 'public' ? 'Public' : 'Privé' }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Demandeur</p>
                                <p class="mt-1 break-words text-sm font-medium text-slate-800">{{ $application->applicant->name }}</p>
                                <p class="mt-1 break-all text-sm text-slate-500">{{ $application->applicant->email }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pays · devise · année</p>
                                <p class="mt-1 text-sm text-slate-800">{{ $application->country }} · {{ $application->currency }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $application->school_year }}</p>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cycles demandés</p>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach($application->cycles ?? [] as $cycle)
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $cycle }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="min-w-0 xl:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Coordonnées de l’école</p>
                                <p class="mt-1 break-all text-sm text-slate-800">{{ $application->email ?: 'Email non fourni' }}</p>
                                <p class="mt-1 break-words text-sm text-slate-500">
                                    {{ $application->phone ?: 'Téléphone non fourni' }}
                                    <span class="mx-1 text-slate-300">·</span>
                                    Demande reçue le {{ $application->created_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-start sm:justify-between">
                            <details class="w-full sm:max-w-lg">
                                <summary class="cursor-pointer text-sm font-semibold text-rose-700 hover:text-rose-800">Refuser cette demande</summary>
                                <form method="POST" action="{{ route('owner.applications.reject', $application) }}" class="mt-3 space-y-2 rounded-xl border border-rose-100 bg-rose-50 p-3" x-on:submit="if (!confirm('Refuser cette demande ? Le demandeur pourra en envoyer une nouvelle.')) $event.preventDefault()">
                                    @csrf
                                    <label for="rejection_reason_{{ $application->id }}" class="block text-xs font-semibold text-rose-900">Motif à communiquer au demandeur</label>
                                    <textarea id="rejection_reason_{{ $application->id }}" name="rejection_reason" required minlength="5" maxlength="1000" rows="2" class="w-full rounded-lg border-rose-200 text-sm focus:border-rose-500 focus:ring-rose-500"></textarea>
                                    <button type="submit" class="rounded-lg bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">Confirmer le refus</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('owner.applications.approve', $application) }}" x-on:submit="if (!confirm('Approuver cette école ? Son espace sera immédiatement activé.')) $event.preventDefault()">
                                @csrf
                                <button type="submit" class="w-full rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 sm:w-auto">Approuver et activer</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Aucune demande à traiter.</p>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1150px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr><th class="px-5 py-3">Établissement</th><th class="px-5 py-3">Responsable</th><th class="px-5 py-3">Pays / devise</th><th class="px-5 py-3">Comptes utilisateurs</th><th class="px-5 py-3">État</th><th class="px-5 py-3">Abonnement</th><th class="px-5 py-3">Créé le</th><th class="px-5 py-3">Dernière activité</th><th class="px-5 py-3">Contrôles</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($schools as $school)
                            <tr>
                                <td class="px-5 py-4"><p class="font-semibold text-slate-900">{{ $school->name }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $school->institution_type === 'public' ? 'Public' : 'Privé' }} · {{ $school->email ?: $school->phone ?: 'Coordonnées non renseignées' }}</p></td>
                                <td class="px-5 py-4"><p>{{ $school->owner?->name ?? '—' }}</p><p class="text-xs text-slate-500">{{ $school->owner?->email }}</p></td>
                                <td class="px-5 py-4">{{ $school->country }} · {{ $school->currency }}</td>
                                <td class="px-5 py-4">{{ $school->users_count }}</td>
                                <td class="px-5 py-4">
                                    @if($school->access_status === 'suspended')
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">Suspendue</span>
                                        @if($school->suspension_reason)<p class="mt-1 max-w-48 text-xs text-slate-500">{{ $school->suspension_reason }}</p>@endif
                                    @else
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Active</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium">{{ \App\Models\School::SUBSCRIPTION_PLANS[$school->subscription_plan] ?? $school->subscription_plan }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ \App\Models\School::SUBSCRIPTION_STATUSES[$school->subscription_status] ?? $school->subscription_status }}</p>
                                    @if($school->subscription_starts_at || $school->subscription_ends_at)
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $school->subscription_starts_at?->format('d/m/Y') ?? 'Début non défini' }}
                                            → {{ $school->subscription_ends_at?->format('d/m/Y') ?? 'Sans échéance' }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">{{ $school->created_at->format('d/m/Y') }}</td>
                                <td class="px-5 py-4">{{ $school->last_activity_at?->format('d/m/Y H:i') ?? 'Pas encore active' }}</td>
                                <td class="space-y-2 px-5 py-4">
                                    <details>
                                        <summary class="cursor-pointer whitespace-nowrap text-sm font-semibold text-blue-700 hover:text-blue-800">Gérer l’abonnement</summary>
                                        <form method="POST" action="{{ route('owner.schools.subscription.update', $school) }}" class="mt-3 w-72 space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                            @csrf @method('PUT')
                                            <div>
                                                <label for="plan_{{ $school->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Offre</label>
                                                <select id="plan_{{ $school->id }}" name="subscription_plan" required class="w-full rounded-lg border-slate-300 text-sm">
                                                    @foreach(\App\Models\School::SUBSCRIPTION_PLANS as $plan => $planLabel)
                                                        <option value="{{ $plan }}" @selected($school->subscription_plan === $plan)>{{ $planLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label for="subscription_status_{{ $school->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Statut d’abonnement</label>
                                                <select id="subscription_status_{{ $school->id }}" name="subscription_status" required class="w-full rounded-lg border-slate-300 text-sm">
                                                    @foreach(\App\Models\School::SUBSCRIPTION_STATUSES as $status => $statusLabel)
                                                        <option value="{{ $status }}" @selected($school->subscription_status === $status)>{{ $statusLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label for="subscription_starts_at_{{ $school->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Début de validité (facultatif)</label>
                                                <input id="subscription_starts_at_{{ $school->id }}" type="date" name="subscription_starts_at" value="{{ $school->subscription_starts_at?->format('Y-m-d') }}" class="w-full rounded-lg border-slate-300 text-sm">
                                            </div>
                                            <div>
                                                <label for="subscription_ends_at_{{ $school->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Fin de validité (facultatif)</label>
                                                <input id="subscription_ends_at_{{ $school->id }}" type="date" name="subscription_ends_at" value="{{ $school->subscription_ends_at?->format('Y-m-d') }}" class="w-full rounded-lg border-slate-300 text-sm">
                                            </div>
                                            <div>
                                                <label for="subscription_note_{{ $school->id }}" class="mb-1 block text-xs font-semibold text-slate-700">Note interne (facultatif)</label>
                                                <textarea id="subscription_note_{{ $school->id }}" name="subscription_note" maxlength="1000" rows="2" class="w-full rounded-lg border-slate-300 text-sm">{{ $school->subscription_note }}</textarea>
                                            </div>
                                            <p class="text-xs leading-5 text-slate-500">Le statut est administratif : seul le contrôle « Suspendre » bloque l’accès à l’application.</p>
                                            <button type="submit" class="w-full rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-800">Enregistrer l’offre</button>
                                        </form>
                                    </details>
                                    @if($school->access_status === 'suspended')
                                        <form method="POST" action="{{ route('owner.schools.reactivate', $school) }}" x-on:submit="if (!confirm('Réactiver l’accès de cette école ?')) $event.preventDefault()">
                                            @csrf
                                            <button type="submit" class="whitespace-nowrap text-sm font-semibold text-emerald-700 hover:underline">Réactiver l’école</button>
                                        </form>
                                    @else
                                        <details>
                                            <summary class="cursor-pointer text-sm font-semibold text-rose-700 hover:text-rose-800">Suspendre l’accès</summary>
                                            <form method="POST" action="{{ route('owner.schools.suspend', $school) }}" class="mt-2 w-72 space-y-2 rounded-xl border border-rose-100 bg-rose-50 p-3" x-on:submit="if (!confirm('Suspendre l’accès de tous les utilisateurs de cette école ?')) $event.preventDefault()">
                                                @csrf
                                                <label for="suspension_reason_{{ $school->id }}" class="block text-xs font-semibold text-rose-900">Motif de suspension</label>
                                                <textarea id="suspension_reason_{{ $school->id }}" name="suspension_reason" required minlength="5" maxlength="1000" rows="2" class="w-full rounded-lg border-rose-200 text-sm"></textarea>
                                                <button type="submit" class="rounded-lg bg-rose-700 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-800">Confirmer la suspension</button>
                                            </form>
                                        </details>
                                    @endif
                                    <details>
                                        <summary class="cursor-pointer text-sm font-semibold text-rose-900 hover:text-rose-950">Supprimer l’école</summary>
                                        <form method="POST" action="{{ route('owner.schools.destroy', $school) }}" class="mt-2 w-72 space-y-2 rounded-xl border border-rose-200 bg-rose-50 p-3">
                                            @csrf
                                            @method('DELETE')
                                            <p class="text-xs leading-5 text-rose-900">Cette action est définitive : toutes les données scolaires seront supprimées et les comptes associés perdront leur accès à cette école.</p>
                                            <label for="delete_confirmation_{{ $school->id }}" class="block text-xs font-semibold text-rose-900">Saisissez « {{ $school->name }} » pour confirmer</label>
                                            <input id="delete_confirmation_{{ $school->id }}" name="confirmation" type="text" required autocomplete="off" class="w-full rounded-lg border-rose-200 text-sm focus:border-rose-500 focus:ring-rose-500">
                                            <button type="submit" class="w-full rounded-lg bg-rose-800 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-900">Supprimer définitivement</button>
                                        </form>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-5 py-12 text-center text-slate-500">Aucun espace scolaire n’a encore été créé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($schools->hasPages())<div class="border-t border-slate-100 p-4">{{ $schools->links() }}</div>@endif
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Journal des actions administratives</h2>
                <p class="mt-1 text-sm text-slate-500">Les 15 actions les plus récentes : approbations, refus, suspensions et modifications d’abonnement.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($actions as $action)
                    <article class="flex flex-col gap-1 px-5 py-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ $action->summary }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $action->school?->name ?? $action->application?->name ?? 'Demande d’école' }}
                                · par {{ $action->actor?->name ?? 'Compte supprimé' }}
                            </p>
                            @if(data_get($action->metadata, 'reason'))
                                <p class="mt-1 max-w-3xl text-sm text-slate-600">{{ data_get($action->metadata, 'reason') }}</p>
                            @endif
                            @if($action->action === 'subscription_updated')
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ data_get($action->metadata, 'previous.plan') }} / {{ data_get($action->metadata, 'previous.status') }}
                                    → {{ data_get($action->metadata, 'current.plan') }} / {{ data_get($action->metadata, 'current.status') }}
                                </p>
                            @endif
                        </div>
                        <time class="shrink-0 text-xs text-slate-500">{{ $action->created_at->format('d/m/Y H:i') }}</time>
                    </article>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-500">Aucune action enregistrée pour le moment.</p>
                @endforelse
            </div>
        </section>
    </main>
</x-app-layout>
