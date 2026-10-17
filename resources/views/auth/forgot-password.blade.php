<x-auth-split title="Mot de passe oublié ?" subtitle="Indiquez votre adresse e-mail : nous vous enverrons un lien pour choisir un nouveau mot de passe.">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <x-auth-field id="email" name="email" type="email" icon="mail" label="Adresse e-mail" :value="old('email')" placeholder="vous@exemple.com" required autofocus autocomplete="username" />

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/25 transition hover:bg-blue-800">
            Envoyer le lien de réinitialisation
        </button>
    </form>

    <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm">
        <a href="{{ route('login') }}" class="font-semibold text-blue-700 hover:text-blue-800">← Retour à la connexion</a>
    </p>
</x-auth-split>
