@props(['title', 'subtitle' => null])
<x-guest-layout>
    <div class="grid min-h-screen bg-white lg:grid-cols-2">
        <aside class="relative hidden overflow-hidden bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 text-white lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-sky-400/10"></div>

            <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-lg"><x-brand-mark class="h-11 w-11" /></span>
                <span class="leading-none" style="font-family: 'Poppins', 'Inter', sans-serif">
                    <span class="block text-3xl font-extrabold tracking-tight"><span class="text-[#F5B800]">YELEN</span><span class="text-white">SCOL</span></span>
                    <span class="mt-1 block text-[10px] font-medium uppercase tracking-[0.18em] text-blue-200">La lumière du savoir</span>
                </span>
            </a>

            <div class="relative z-10 max-w-md">
                <h2 class="text-4xl font-bold leading-tight">Pilotez votre établissement en toute sérénité.</h2>
                <p class="mt-4 text-blue-100">La plateforme de gestion scolaire pensée pour les directeurs, enseignants et administrateurs.</p>
                <ul class="mt-8 space-y-4 text-sm">
                    @foreach ([
                        ['Élèves, classes et enseignants centralisés', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                        ['Notes, bulletins et emplois du temps', 'M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5'],
                        ['Suivi financier : scolarité, dépenses, salaires', 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z'],
                        ['Données sécurisées, accès par rôle', 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
                    ] as [$label, $path])
                        <li class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/20">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                            </span>
                            <span>{{ $label }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative z-10 text-xs text-blue-200">© {{ date('Y') }} YELENSCOL · Tous droits réservés</p>
        </aside>

        <main class="flex items-center justify-center bg-slate-50 px-6 py-10 sm:px-10">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-8 block lg:hidden"><x-brand-logo /></a>

                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="mt-1.5 text-sm text-slate-500">{{ $subtitle }}</p>
                    @endif

                    @if ($errors->any())
                        <div class="mt-5 flex gap-2 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700" role="alert">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                            <ul class="space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    @session('status')
                        <div class="mt-5 flex gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700" role="status">{{ $value }}</div>
                    @endsession

                    <div class="mt-6">{{ $slot }}</div>
                </div>

                <p class="mt-6 text-center text-xs text-slate-400">
                    {{ request()->isSecure() ? 'Connexion chiffrée HTTPS' : 'Connexion HTTP locale — HTTPS requis en production' }} · YELENSCOL
                </p>
            </div>
        </main>
    </div>
</x-guest-layout>
