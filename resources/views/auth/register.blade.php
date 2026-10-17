<x-auth-split title="Créer votre compte" subtitle="Inscrivez-vous pour configurer l’espace de votre école.">
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <x-auth-field id="name" name="name" icon="user" label="Nom complet" :value="old('name')" placeholder="Prénom et nom" required autofocus autocomplete="name" />
        <x-auth-field id="email" name="email" type="email" icon="mail" label="Adresse e-mail" :value="old('email')" placeholder="vous@exemple.com" required autocomplete="username" />
        <x-auth-field id="password" name="password" type="password" icon="lock" label="Mot de passe" placeholder="Au moins 8 caractères" required autocomplete="new-password" />
        <x-auth-field id="password_confirmation" name="password_confirmation" type="password" icon="lock" label="Confirmer le mot de passe" placeholder="Retapez le mot de passe" required autocomplete="new-password" />

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/25 transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
            Créer mon compte
        </button>
    </form>

    <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-500">
        Vous avez déjà un compte ?
        <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:text-blue-800">Se connecter</a>
    </p>
</x-auth-split>
