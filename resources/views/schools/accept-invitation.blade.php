<x-guest-layout>
    <main class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10">
        <section class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Invitation d’équipe</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Rejoindre {{ $invitation->school->name }}</h1>
            <p class="mt-2 text-sm text-slate-600">
                @if($existingUser)
                    Votre compte donnera accès à l’espace de cette école uniquement.
                @else
                    Créez votre compte pour accéder uniquement à l’espace de cette école.
                @endif
            </p>
            <div class="mt-5 rounded-xl bg-slate-50 p-3 text-sm text-slate-700">{{ $invitation->email }}</div>

            @if($errors->any())
                <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('school.invitations.accept', $token) }}" class="mt-6 space-y-4">
                @csrf
                @unless($existingUser)
                    <div><label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nom complet</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autofocus class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></div>
                    <div><label for="password" class="mb-1 block text-sm font-medium text-slate-700">Mot de passe</label><input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></div>
                    <div><label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirmer le mot de passe</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></div>
                @endunless
                <button class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">Accepter et rejoindre l’école</button>
            </form>
        </section>
    </main>
</x-guest-layout>
