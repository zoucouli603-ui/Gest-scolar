<x-app-layout>
    <main class="mx-auto max-w-5xl space-y-6 p-5 md:p-8">
        <header>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Administration de l’école</p>
            <h1 class="mt-1 flex items-center gap-2 text-2xl font-bold text-slate-900"><x-icon name="users" class="h-7 w-7 text-violet-600" /> Partager l’espace de {{ $currentSchool->name }}</h1>
            <p class="mt-1 text-sm text-slate-600">Invitez le Directeur, le Censeur, le Comptable ou le Surveillant. Chacun disposera de son compte et d’une interface adaptée à son rôle.</p>
        </header>

        <x-flash-messages />
        @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900">Inviter un membre du personnel</h2>
            <form method="POST" action="{{ route('school.team.invitations.store') }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                @csrf
                <label for="invite-email" class="sr-only">Adresse email du collègue</label>
                <input id="invite-email" type="email" name="email" required value="{{ old('email') }}" placeholder="collegue@ecole.com" class="min-w-0 flex-1 rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <label for="invite-role" class="sr-only">Rôle</label>
                <select id="invite-role" name="role" required class="rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', 'directeur') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Envoyer l’invitation</button>
            </form>
            <p class="mt-2 text-xs text-slate-500">Le lien est envoyé par email et expire après 7 jours. Le membre crée son propre compte ou rejoint l’école avec un compte existant non rattaché à un autre établissement. Les onglets visibles dépendent de son rôle.</p>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <h2 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-900">Membres de l’espace</h2>
            <div class="divide-y divide-slate-100">
                @foreach($members as $member)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div><p class="font-medium text-slate-900">{{ $member->name }}</p><p class="text-sm text-slate-500">{{ $member->email }}</p></div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $member->isSchoolAdmin() ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' }}">{{ $member->roleLabel() }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        @if($invitations->isNotEmpty())
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <h2 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-900">Invitations en attente</h2>
                <div class="divide-y divide-slate-100">
                    @foreach($invitations as $invitation)
                        <div class="flex items-center justify-between gap-4 px-5 py-4 text-sm"><span>{{ $invitation->email }}</span><span class="text-slate-500">Expire le {{ $invitation->expires_at->format('d/m/Y') }}</span></div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</x-app-layout>
