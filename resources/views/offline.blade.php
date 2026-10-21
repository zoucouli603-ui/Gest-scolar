<x-guest-layout>
    <div class="flex min-h-screen items-center justify-center bg-slate-100 px-6 py-12">
        <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-3xl">📵</div>
            <h1 class="text-2xl font-bold text-slate-900">Vous êtes hors ligne</h1>
            <p class="mt-3 text-sm text-slate-600">
                Vos notes saisies sont enregistrées localement et seront synchronisées automatiquement dès que votre connexion Internet revient.
            </p>
            <a href="{{ url('/') }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Revenir à l’accueil
            </a>
        </div>
    </div>
</x-guest-layout>
