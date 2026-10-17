<x-guest-layout>
    <main class="mx-auto max-w-3xl p-5 md:p-8">
        <section class="rounded-2xl border border-amber-200 bg-white p-6 shadow-sm md:p-8">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Demande en cours d’examen</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Votre établissement attend l’approbation</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">
                La demande de création de <strong>{{ $application->name }}</strong> a été envoyée au super-administrateur le
                {{ $application->created_at->format('d/m/Y à H:i') }}.
                Vous recevrez l’accès à votre espace dès son approbation. Aucune action supplémentaire n’est requise pour le moment.
            </p>
            <div class="mt-6 grid gap-3 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">
                <div><span class="text-slate-500">Responsable</span><p class="mt-1 font-medium text-slate-800">{{ auth()->user()->name }}</p></div>
                <div><span class="text-slate-500">Année scolaire demandée</span><p class="mt-1 font-medium text-slate-800">{{ $application->school_year }}</p></div>
                <div><span class="text-slate-500">Pays</span><p class="mt-1 font-medium text-slate-800">{{ $application->country }}</p></div>
                <div><span class="text-slate-500">Statut</span><p class="mt-1 font-semibold text-amber-700">En attente</p></div>
            </div>
            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-5">
                <p class="text-xs leading-5 text-slate-500">Votre espace scolaire et ses modules seront activés après validation.</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Déconnexion</button>
                </form>
            </div>
        </section>
    </main>
</x-guest-layout>
