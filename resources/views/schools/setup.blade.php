<x-guest-layout>
    <main class="min-h-screen bg-slate-50 px-4 py-10">
        <section class="mx-auto max-w-3xl rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60 sm:p-10">
            <div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-5">
                <a href="{{ route('home') }}" aria-label="YELENSCOL, accueil"><x-brand-logo /></a>
            </div>
            <a href="{{ route('logout') }}" class="text-sm font-medium text-blue-700 hover:underline" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Déconnexion</a>
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>

            <div class="mt-6">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Configuration · 1 étape</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">Créez l'espace de votre école</h1>
                <p class="mt-2 text-sm text-slate-600">Soumettez les informations de votre établissement. Son espace sera activé après approbation par le super-administrateur ; votre rôle choisi sera alors activé.</p>
            </div>

            @if($rejectedApplication)
                <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                    <p class="font-semibold">Votre demande précédente n'a pas été approuvée.</p>
                    @if($rejectedApplication->rejection_reason)
                        <p class="mt-1">{{ $rejectedApplication->rejection_reason }}</p>
                    @endif
                    <p class="mt-2 text-xs text-rose-700">Vous pouvez corriger les informations ci-dessous et soumettre une nouvelle demande.</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form             method="POST" action="{{ route('school.setup.store') }}" enctype="multipart/form-data" class="mt-8 space-y-6">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label for="logo" class="mb-1 block text-sm font-medium text-slate-700">Logo de l'établissement *</label>
                                    <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm">
                                    <p class="mt-1 text-xs text-slate-500">PNG, JPG ou WebP, 2 Mo maximum. Il apparaîtra sur le tableau de bord et les cartes d'élèves.</p>
                                </div>
                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nom de l'établissement *</label>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autofocus class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="role" class="mb-1 block text-sm font-medium text-slate-700">Votre rôle *</label>
                        <select id="role" name="role" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Sélectionner</option>
                            @foreach(\App\Models\User::schoolCreationRoles() as $value => $label)
                                <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Ce rôle déterminera vos accès dans l'application.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="mb-1 block text-sm font-medium text-slate-700">Adresse physique</label>
                        <input id="address" name="address" value="{{ old('address') }}" maxlength="255" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Téléphone de l'école</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}" maxlength="50" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email de l'école</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="country" class="mb-1 block text-sm font-medium text-slate-700">Pays *</label>
                        <input id="country" name="country" value="{{ old('country') }}" required maxlength="100" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="province" class="mb-1 block text-sm font-medium text-slate-700">Province / région</label>
                        <input id="province" name="province" value="{{ old('province') }}" maxlength="100" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="currency" class="mb-1 block text-sm font-medium text-slate-700">Devise (code ISO) *</label>
                        <input id="currency" name="currency" value="{{ old('currency', 'XOF') }}" required minlength="3" maxlength="3" pattern="[A-Za-z]{3}" placeholder="XOF" class="w-full rounded-xl border-slate-300 text-sm uppercase shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-slate-500">Exemples : XOF, XAF, CDF, EUR.</p>
                    </div>
                    <div>
                        <label for="institution_type" class="mb-1 block text-sm font-medium text-slate-700">Type d'établissement *</label>
                        <select id="institution_type" name="institution_type" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Sélectionner</option>
                            <option value="private" @selected(old('institution_type') === 'private')">Privé</option>
                            <option value="public" @selected(old('institution_type') === 'public')">Public</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="school_year" class="mb-1 block text-sm font-medium text-slate-700">Première année scolaire *</label>
                        @php($year = now()->year)
                        <input id="school_year" name="school_year" value="{{ old('school_year', $year.'-'.($year + 1)) }}" required pattern="\d{4}-\d{4}" placeholder="2026-2027" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <x-cycle-fields :selected="old('cycles', [])" />
                </div>

                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
                    Votre compte, <strong>{{ auth()->user()->name }}</strong>, aura le rôle sélectionné dans cet espace. Les autres écoles et leurs dossiers resteront séparés.
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button type="submit" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Créer mon espace et ouvrir le tableau de bord</button>
                </div>
            </form>
        </section>
    </main>
</x-guest-layout>
