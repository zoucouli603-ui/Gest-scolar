<x-auth-split title="Nouveau mot de passe" subtitle="Choisissez un mot de passe sécurisé pour votre compte.">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-auth-field id="email" name="email" type="email" icon="mail" label="Adresse e-mail" :value="old('email', $request->email)" required autofocus autocomplete="username" />
        <x-auth-field id="password" name="password" type="password" icon="lock" label="Nouveau mot de passe" required autocomplete="new-password" />
        <x-auth-field id="password_confirmation" name="password_confirmation" type="password" icon="lock" label="Confirmer le mot de passe" required autocomplete="new-password" />

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/25 transition hover:bg-blue-800">
            Réinitialiser le mot de passe
        </button>
    </form>
</x-auth-split>
