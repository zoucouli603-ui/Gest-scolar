@php
    $icons = [
        'users' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
        'cap' => 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
        'doc' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        'cash' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z',
        'chart' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
        'shield' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
        'building' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z',
        'check' => 'm4.5 12.75 6 6 9-13.5',
        'clock' => 'M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z',
        'lock' => 'M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Zm5.25 4.125v2.25',
        'bolt' => 'm13 2-3 8h7l-6 12 1-9H5l8-11Z',
        'cloud' => 'M2.25 15a4.5 4.5 0 0 0 4.5 4.5h10.5a4.5 4.5 0 0 0 .75-8.938A6.75 6.75 0 0 0 5.25 9.75 5.25 5.25 0 0 0 2.25 15Z',
        'handshake' => 'm8.25 10.5 2.25 2.25a2.121 2.121 0 0 0 3 0l2.25-2.25M3 12l4.5-4.5 4.5 1.5 4.5-1.5L21 12m-18 0 5.25 5.25a2.121 2.121 0 0 0 3 0l.75-.75m3-3 3.75 3.75a2.121 2.121 0 0 1-3 3L13.5 17.25',
        'globe' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.25-2.46 3.375-5.46 3.375-9S14.25 5.46 12 3m0 18c-2.25-2.46-3.375-5.46-3.375-9S9.75 5.46 12 3m-8.25 9h16.5',
    ];
    $svg = fn ($name, $cls = 'h-6 w-6') => '<svg class="'.$cls.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="'.$icons[$name].'"/></svg>';
    $features = [
        ['users', 'Élèves & inscriptions', 'Dossiers complets, photos, cartes d’élève et suivi des inscriptions par année scolaire.'],
        ['cap', 'Enseignants & matières', 'Affectations par classe, présences, fiches enseignants et gestion des salaires.'],
        ['calendar', 'Emplois du temps', 'Planning hebdomadaire par classe, sans conflit, prêt à imprimer en un clic.'],
        ['doc', 'Notes & bulletins', 'Saisie des notes, moyennes automatiques et bulletins individuels ou par classe.'],
        ['cash', 'Finances', 'Scolarité, dépenses, salaires et reçus numérotés pour une comptabilité claire.'],
        ['chart', 'Rapports', 'Tableaux de bord et statistiques pour piloter l’établissement en temps réel.'],
    ];
@endphp
<x-guest-layout>
    <div class="min-h-screen bg-white text-slate-900">
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-3.5 lg:px-8">
                <a href="{{ route('home') }}" aria-label="YELENSCOL, accueil">
                    <x-brand-logo />
                </a>
                <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex" aria-label="Navigation principale">
                    <a href="#fonctionnalites" class="hover:text-blue-700">Fonctionnalités</a>
                    <a href="#espaces" class="hover:text-blue-700">Espaces</a>
                    <a href="#tarifs" class="hover:text-blue-700">Tarifs</a>
                    <a href="#demarrer" class="hover:text-blue-700">Démarrer</a>
                    <a href="#contacts" class="hover:text-blue-700">Contacts</a>
                </nav>
                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route(Auth::user()->homeRoute()) }}" class="inline-flex items-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">Ouvrir mon espace</a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 text-white">
                <div class="pointer-events-none absolute -right-32 -top-32 h-[28rem] w-[28rem] rounded-full bg-gold-400/15 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-40 -left-20 h-[26rem] w-[26rem] rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-5 py-16 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-24">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-blue-100">
                            {!! $svg('shield', 'h-4 w-4') !!} Plateforme de gestion scolaire sécurisée
                        </span>
                        <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-[3.4rem]">
                            Gérez votre école <span class="bg-gradient-to-r from-gold-300 to-gold-500 bg-clip-text text-transparent">simplement</span>, de l’inscription au bulletin.
                        </h1>
                        <p class="mt-5 max-w-xl text-lg leading-8 text-blue-100/90">
                            Élèves, enseignants, emplois du temps, notes et finances réunis dans un seul espace professionnel, accessible à toute votre équipe.
                        </p>
                        <div class="mt-9 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route(Auth::user()->homeRoute()) }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-blue-800 shadow-lg hover:bg-blue-50">Accéder au tableau de bord</a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-blue-800 shadow-lg hover:bg-blue-50">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                    Se connecter
                                </a>
                                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-6 py-3.5 text-sm font-semibold text-white hover:bg-white/20">Créer l’espace de mon école</a>
                            @endauth
                        </div>
                        <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-blue-100">
                            @foreach (['Données séparées par école', '100 % en français', 'Accès par rôle'] as $point)
                                <li class="flex items-center gap-2">{!! $svg('check', 'h-4 w-4 text-emerald-300') !!} {{ $point }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="relative" aria-hidden="true">
                        <div class="absolute -right-5 -top-7 h-32 w-32 rounded-full bg-gold-400/20 blur-2xl"></div>
                        <div class="absolute -bottom-8 -left-6 h-36 w-36 rounded-full bg-sky-400/20 blur-2xl"></div>
                        <div class="relative rounded-[2rem] border border-white/20 bg-white/10 p-3 shadow-2xl backdrop-blur">
                            <div class="rounded-[1.5rem] bg-gradient-to-br from-white to-blue-50 p-5 text-slate-900 sm:p-7">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-900 text-gold-300 shadow-lg shadow-blue-900/20">{!! $svg('cap', 'h-6 w-6') !!}</span>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-800">YELENSCOL</p>
                                            <p class="mt-0.5 text-xs text-slate-500">La gestion scolaire, autrement</p>
                                        </div>
                                    </div>
                                    <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-[11px] font-bold text-emerald-800">Pensé pour votre école</span>
                                </div>
                                <div class="mt-6 rounded-2xl bg-gradient-to-br from-blue-800 via-blue-700 to-indigo-700 p-5 text-white shadow-xl shadow-blue-900/20 sm:p-6">
                                    <div class="flex items-start gap-4">
                                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gold-400 text-blue-950 shadow-lg shadow-blue-950/20">{!! $svg('clock', 'h-7 w-7') !!}</span>
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Votre école, en mouvement</p>
                                            <h2 class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">Rapide</h2>
                                            <p class="mt-1 text-sm leading-6 text-blue-50">Accédez en quelques clics à toutes vos informations.</p>
                                        </div>
                                    </div>
                                    <div class="mt-5 flex items-center gap-2">
                                        <span class="h-1.5 flex-1 rounded-full bg-gold-400"></span>
                                        <span class="h-1.5 flex-1 rounded-full bg-white/60"></span>
                                        <span class="h-1.5 flex-1 rounded-full bg-white/35"></span>
                                        <span class="h-1.5 flex-1 rounded-full bg-white/20"></span>
                                        <span class="h-1.5 flex-1 rounded-full bg-white/10"></span>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    @foreach ([
                                        ['shield', 'Sécurisé', 'Vos données, notre priorité', 'bg-emerald-50 text-emerald-700'],
                                        ['bolt', 'Flexible', 'S’adapte à vos besoins', 'bg-amber-50 text-amber-700'],
                                        ['cloud', 'Accessible', 'Partout, tout le temps', 'bg-sky-50 text-sky-700'],
                                        ['handshake', 'Local', 'Pensé pour nos écoles', 'bg-indigo-50 text-indigo-700'],
                                    ] as [$icon, $title, $description, $color])
                                        <div class="flex min-h-20 items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $color }}">{!! $svg($icon, 'h-5 w-5') !!}</span>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-bold text-slate-900">{{ $title }}</span>
                                                <span class="mt-0.5 block text-xs leading-4 text-slate-500">{{ $description }}</span>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="fonctionnalites" class="mx-auto max-w-7xl px-5 py-20 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-semibold uppercase tracking-widest text-blue-700">Fonctionnalités</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Tout ce qu’il faut pour piloter votre établissement</h2>
                    <p class="mt-4 text-slate-600">Des outils pensés pour le quotidien des directions, des secrétariats et des enseignants.</p>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($features as [$icon, $title, $text])
                        <article class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-100">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-700 transition group-hover:bg-blue-700 group-hover:text-white">{!! $svg($icon) !!}</span>
                            <h3 class="mt-5 text-lg font-semibold">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $text }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="espaces" class="bg-slate-50 py-20">
                <div class="mx-auto max-w-7xl px-5 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-sm font-semibold uppercase tracking-widest text-blue-700">Deux espaces</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Chacun accède à ce dont il a besoin</h2>
                    </div>
                    <div class="mt-12 grid gap-6 lg:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-700 text-white">{!! $svg('building') !!}</span>
                            <h3 class="mt-5 text-xl font-bold">Espace école</h3>
                            <p class="mt-1 text-sm text-slate-500">Pour les directeurs, administrateurs et le personnel.</p>
                            <ul class="mt-5 space-y-2.5 text-sm text-slate-700">
                                @foreach (['Gestion des élèves, classes et enseignants', 'Notes, bulletins et emplois du temps', 'Finances, reçus et rapports', 'Invitation des membres de l’équipe'] as $p)
                                    <li class="flex gap-2">{!! $svg('check', 'mt-0.5 h-4 w-4 shrink-0 text-emerald-600') !!} {{ $p }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-700 text-white">{!! $svg('shield') !!}</span>
                            <h3 class="mt-5 text-xl font-bold">Espace super-administrateur</h3>
                            <p class="mt-1 text-sm text-slate-500">Pour le propriétaire de la plateforme.</p>
                            <ul class="mt-5 space-y-2.5 text-sm text-slate-700">
                                @foreach (['Validation des demandes d’établissements', 'Activation et suspension des écoles', 'Vue d’ensemble de toutes les écoles', 'Contrôle global de la plateforme'] as $p)
                                    <li class="flex gap-2">{!! $svg('check', 'mt-0.5 h-4 w-4 shrink-0 text-emerald-600') !!} {{ $p }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section id="tarifs" class="scroll-mt-20 bg-slate-50 px-5 py-20 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-sm font-semibold uppercase tracking-widest text-blue-700">Tarifs trimestriels</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Une offre adaptée à vos cycles</h2>
                        <p class="mt-4 text-slate-600">Les paiements d’abonnement se font tous les trois mois.</p>
                    </div>
                    <div class="mt-12 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ([
                            ['Éveil & Fondamental', 'Maternelle · 1er Cycle · 2è Cycle', '25 000', 'Pour les écoles qui accompagnent les élèves dès leurs premiers apprentissages.', 'users', 'from-amber-50 to-white', 'text-amber-700'],
                            ['Parcours Excellence', '1er Cycle · 2è Cycle · Lycée', '30 000', 'Un parcours scolaire complet, du fondamental jusqu’au lycée.', 'cap', 'from-indigo-50 to-white', 'text-indigo-700'],
                            ['Fondamental', '1er Cycle · 2è Cycle', '20 000', 'L’essentiel pour gérer efficacement les deux cycles fondamentaux.', 'doc', 'from-emerald-50 to-white', 'text-emerald-700'],
                            ['Cap Lycée', 'Lycée', '25 000', 'Tous les outils nécessaires pour piloter votre établissement secondaire.', 'chart', 'from-sky-50 to-white', 'text-sky-700'],
                        ] as [$plan, $cycles, $price, $description, $icon, $gradient, $iconColor])
                            <article class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br {{ $gradient }} p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-100">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white {{ $iconColor }} shadow-sm ring-1 ring-slate-200/70">{!! $svg($icon, 'h-6 w-6') !!}</span>
                                    <span class="rounded-full bg-white/80 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-blue-800 ring-1 ring-slate-200/70">Offre</span>
                                </div>
                                <h3 class="mt-5 text-xl font-extrabold text-slate-900">{{ $plan }}</h3>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-blue-700">{{ $cycles }}</p>
                                <p class="mt-3 min-h-12 text-sm leading-6 text-slate-600">{{ $description }}</p>
                                <div class="mt-auto border-t border-slate-200/80 pt-5">
                                    <p class="text-3xl font-extrabold tracking-tight text-blue-800">{{ $price }} <span class="text-sm font-semibold text-slate-500">FCFA</span></p>
                                    <p class="mt-1 text-sm font-medium text-slate-500">Pour 3 mois d’accès</p>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        <article class="flex items-center gap-4 rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-white p-6 shadow-sm sm:p-7">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">{!! $svg('check', 'h-6 w-6') !!}</span>
                            <div>
                                <h3 class="font-bold text-amber-950">Votre fidélité récompensée</h3>
                                <p class="mt-2 text-sm leading-6 text-amber-900">Un trimestre offert au 3è année d'utilisation, puis tous les 2 ans.</p>
                            </div>
                        </article>
                    </div>
                    <p class="mt-6 flex items-center justify-center gap-2 rounded-xl bg-blue-900 px-5 py-4 text-center text-sm font-semibold text-white shadow-sm">
                        {!! $svg('calendar', 'h-5 w-5 shrink-0 text-gold-300') !!}
                        Accès illimité toute l’année sans paiement pendant les vacances.
                    </p>
                </div>
            </section>

            <section id="avantages" class="border-y border-blue-900/10 bg-blue-950 px-5 py-16 text-white lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-sm font-semibold uppercase tracking-widest text-amber-300">YELENSCOL au quotidien</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-white">Tout ce qu’il faut pour gérer votre école sereinement</h2>
                        <p class="mt-3 text-blue-100">Une plateforme complète, conçue pour accompagner votre établissement au quotidien.</p>
                    </div>
                    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach ([
                            ['clock', 'Rapide', 'Accédez en quelques clics à toutes vos informations.'],
                            ['shield', 'Sécurisé', 'Vos données scolaires sont au cœur de nos priorités.'],
                            ['bolt', 'Flexible', 'La plateforme s’adapte aux besoins de votre école.'],
                            ['cloud', 'Accessible', 'Retrouvez votre espace partout et à tout moment.'],
                            ['handshake', 'Local', 'Une solution pensée pour nos établissements.'],
                        ] as [$icon, $title, $description])
                            <article class="group rounded-2xl border border-white/15 bg-white/10 p-5 transition duration-200 hover:-translate-y-1 hover:border-amber-300/60 hover:bg-white/15">
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-400 text-blue-950 shadow-lg shadow-amber-950/20">{!! $svg($icon, 'h-6 w-6') !!}</span>
                                <h3 class="mt-4 text-lg font-bold text-white">{{ $title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-blue-100">{{ $description }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="demarrer" class="mx-auto max-w-7xl px-5 py-20 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-semibold uppercase tracking-widest text-blue-700">Démarrer</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Prêt en trois étapes</h2>
                </div>
                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    @foreach ([['1', 'Créez votre compte', 'Inscrivez-vous et renseignez les informations de votre établissement.'], ['2', 'Faites valider votre demande', 'Votre espace est activé après validation par l’équipe YELENSCOL.'], ['3', 'Gérez votre école', 'Ajoutez vos classes, élèves et enseignants, et invitez votre équipe.']] as [$n, $t, $d])
                        <div class="relative rounded-2xl border border-slate-200 p-6">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-700 text-sm font-bold text-white">{{ $n }}</span>
                            <h3 class="mt-4 font-semibold">{{ $t }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-12 rounded-3xl bg-gradient-to-r from-blue-700 to-indigo-800 px-8 py-12 text-center text-white shadow-xl">
                    <h2 class="text-2xl font-bold sm:text-3xl">Modernisez la gestion de votre école dès aujourd’hui</h2>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        @auth
                            <a href="{{ route(Auth::user()->homeRoute()) }}" class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-blue-800 hover:bg-blue-50">Ouvrir mon espace</a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-blue-800 hover:bg-blue-50">Créer l’espace de mon école</a>
                            <a href="{{ route('login') }}" class="rounded-xl border border-white/40 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">Se connecter</a>
                        @endauth
                    </div>
                </div>
            </section>

            <section id="contacts" class="scroll-mt-20 bg-slate-50 py-20">
                <div class="mx-auto max-w-5xl px-5 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="text-sm font-semibold uppercase tracking-widest text-blue-700">Contacts</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Une question ? Contactez-nous</h2>
                        <p class="mt-3 text-slate-600">Appel, message WhatsApp ou e-mail : nous vous répondons rapidement.</p>
                    </div>
                    <div class="mt-12 grid gap-6 md:grid-cols-3">
                        <a href="tel:+22370775158" class="group rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-700 text-white shadow-lg shadow-blue-700/30">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.6 21 3 13.4 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1L6.6 10.8Z"/></svg>
                            </span>
                            <h3 class="mt-4 font-semibold text-slate-900">Appel</h3>
                            <p class="mt-1 text-lg font-bold text-blue-700">70-77-51-58</p>
                            <p class="mt-1 text-xs text-slate-500">Appuyez pour appeler</p>
                        </a>
                        <a href="https://wa.me/22360042610" target="_blank" rel="noopener" class="group rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#25D366] text-white shadow-lg shadow-emerald-500/30">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-8 w-8"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.9L2 22l5.25-1.38A9.9 9.9 0 1 0 12.04 2Zm0 18.1a8.1 8.1 0 0 1-4.13-1.13l-.3-.18-3.1.82.83-3.02-.2-.31a8.1 8.1 0 1 1 6.9 3.82Zm4.45-6.06c-.24-.12-1.44-.71-1.66-.79-.22-.08-.39-.12-.55.12-.16.24-.63.79-.77.95-.14.16-.28.18-.52.06a6.6 6.6 0 0 1-1.95-1.2 7.3 7.3 0 0 1-1.35-1.68c-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.32-.75-1.8-.2-.47-.4-.4-.55-.41h-.47c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.69 2.58 4.1 3.62 2.4 1.04 2.4.69 2.84.65.44-.04 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28Z"/></svg>
                            </span>
                            <h3 class="mt-4 font-semibold text-slate-900">WhatsApp</h3>
                            <p class="mt-1 text-lg font-bold text-emerald-600">60-04-26-10</p>
                            <p class="mt-1 text-xs text-slate-500">Écrire sur WhatsApp</p>
                        </a>
                        <a href="mailto:zoucouli603@gmail.com" class="group rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-lg ring-1 ring-slate-200">
                                <svg viewBox="0 0 24 24" class="h-8 w-8"><path fill="#EA4335" d="M2 6.5v11A1.5 1.5 0 0 0 3.5 19H6V9.5l6 4.5 6-4.5V19h2.5a1.5 1.5 0 0 0 1.5-1.5v-11a1.5 1.5 0 0 0-2.4-1.2L12 11 4.4 5.3A1.5 1.5 0 0 0 2 6.5Z"/><path fill="#4285F4" d="M18 9.5V19h2.5a1.5 1.5 0 0 0 1.5-1.5V6.5L18 9.5Z"/><path fill="#34A853" d="M6 19V9.5L2 6.5v11A1.5 1.5 0 0 0 3.5 19H6Z"/><path fill="#FBBC04" d="M18 5.3v4.2l4-3a1.5 1.5 0 0 0-2.4-1.2L18 5.3Z"/></svg>
                            </span>
                            <h3 class="mt-4 font-semibold text-slate-900">Gmail</h3>
                            <p class="mt-1 break-all text-base font-bold text-red-600">zoucouli603@gmail.com</p>
                            <p class="mt-1 text-xs text-slate-500">Envoyer un e-mail</p>
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-slate-50">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <span class="flex items-center gap-2 font-semibold text-slate-800">{!! $svg('cap', 'h-5 w-5 text-blue-700') !!} YELENSCOL</span>
                <span>Gestion scolaire pour les établissements et leurs équipes.</span>
                <span>© {{ date('Y') }} YELENSCOL. Tous droits réservés.</span>
            </div>
        </footer>
    </div>
</x-guest-layout>
