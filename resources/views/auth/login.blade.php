<x-auth-split title="Bon retour parmi nous" subtitle="Connectez-vous pour accéder à votre espace YELENSCOL.">
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-auth-field id="email" name="email" type="email" icon="mail" label="Adresse e-mail" :value="old('email')" placeholder="vous@exemple.com" required autofocus autocomplete="username" />
        <x-auth-field id="password" name="password" type="password" icon="lock" label="Mot de passe" placeholder="Votre mot de passe" required autocomplete="current-password" />

        <div class="flex items-center justify-between text-sm">
            <label for="remember_me" class="flex items-center gap-2 text-slate-600">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                Se souvenir de moi
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-semibold text-blue-700 hover:text-blue-800">Mot de passe oublié ?</a>
            @endif
        </div>

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/25 transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
            Se connecter
        </button>
    </form>

    <div class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-500">
        Votre école n’a pas encore d’espace ?
        <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-800">Créer un compte</a>
    </div>
    <p class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-center text-xs text-slate-500">
        Connectez-vous avec votre compte. L’administration centrale est réservée au compte super-administrateur et vérifiée par le serveur.
    </p>
</x-auth-split>
