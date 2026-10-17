<x-app-layout>
    <main class="mx-auto max-w-6xl space-y-6 p-5 md:p-8">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Administration de l’école</p>
                <h1 class="mt-1 flex items-center gap-2 text-2xl font-bold text-slate-900"><x-icon name="building" class="h-7 w-7 text-blue-600" /> Paramètres de l’établissement</h1>
                <p class="mt-1 text-sm text-slate-600">Gérez les informations, la localisation, les années scolaires et les photos de votre école.</p>
            </div>
            <a href="{{ route('school-years.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Gérer les années scolaires
            </a>
        </header>

        @if(session('success'))
            <div id="flash-success" role="alert" class="flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 transition-opacity duration-500">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-lg font-bold leading-none text-emerald-600 hover:text-emerald-800" aria-label="Fermer">&times;</button>
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('flash-success');
                    if (!el) return;
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 500);
                }, 4000);
            </script>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @php
            $cfg = config('appearance');
            $current = \App\Support\Appearance::settings($school);
            $tabs = ['informations' => 'Informations', 'apparence' => 'Apparence', 'carte' => 'Carte élève', 'photos' => 'Photos', 'annees' => 'Années scolaires'];
        @endphp

        <div x-data="{ tab: ['informations','apparence','carte','photos','annees'].includes(location.hash.slice(1)) ? location.hash.slice(1) : 'informations', go(t) { this.tab = t; history.replaceState(null, '', '#' + t) } }" class="space-y-6">
        <nav class="overflow-x-auto rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm" aria-label="Sections des paramètres">
            <ul class="flex min-w-max gap-1" role="tablist">
                @foreach($tabs as $id => $label)
                    <li>
                        <button type="button" role="tab" @click="go('{{ $id }}')" :aria-selected="tab === '{{ $id }}'"
                           :class="tab === '{{ $id }}' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                           class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition">{{ $label }}</button>
                    </li>
                @endforeach
            </ul>
        </nav>
        <form id="informations" x-show="tab === 'informations'" method="POST" action="{{ route('school.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Informations générales</h2>
                    <p class="mt-1 text-sm text-slate-500">Ces informations apparaissent dans votre espace et vos documents.</p>
                </div>
                <div class="grid gap-5 p-5 md:grid-cols-3">
                            <div class="md:col-span-3 flex items-center gap-4">
                                <x-school-logo :school="$school" class="h-16 w-16 rounded-xl" />
                                <div class="flex-1">
                                    <label for="logo" class="mb-1 block text-sm font-medium text-slate-700">Logo de l’école</label>
                                    <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="w-full rounded-xl border-slate-300 text-sm shadow-sm">
                                    <p class="mt-1 text-xs text-slate-500">PNG, JPG ou WebP, 2 Mo maximum. Laissez vide pour conserver le logo actuel.</p>
                                </div>
                            </div>
                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nom de l’école *</label>
                        <input id="name" name="name" value="{{ old('name', $school->name) }}" required maxlength="255" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="director_name" class="mb-1 block text-sm font-medium text-slate-700">Nom du Directeur</label>
                        <input id="director_name" name="director_name" value="{{ old('director_name', $school->director_name) }}" maxlength="255" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="establishment_type" class="mb-1 block text-sm font-medium text-slate-700">Profil d’établissement *</label>
                        <select id="establishment_type" name="establishment_type" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach(['maternelle' => 'Maternelle', 'primaire' => '1er Cycle', 'secondaire' => '2è Cycle', 'lycee' => 'Lycée', 'polyvalent' => 'Polyvalent'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('establishment_type', $school->establishment_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-cycle-fields :selected="old('cycles', $school->cycles ?? [])" :academy="$school->academy" :cap="$school->cap" />
                    <div>
                        <label for="institution_type" class="mb-1 block text-sm font-medium text-slate-700">Statut *</label>
                        <select id="institution_type" name="institution_type" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="private" @selected(old('institution_type', $school->institution_type) === 'private')>Privé</option>
                            <option value="public" @selected(old('institution_type', $school->institution_type) === 'public')>Public</option>
                        </select>
                    </div>
                    <div>
                        <label for="country" class="mb-1 block text-sm font-medium text-slate-700">Pays *</label>
                        <input id="country" name="country" value="{{ old('country', $school->country) }}" required maxlength="100" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="currency" class="mb-1 block text-sm font-medium text-slate-700">Devise (code ISO) *</label>
                        <input id="currency" name="currency" value="{{ old('currency', $school->currency) }}" required minlength="3" maxlength="3" pattern="[A-Za-z]{3}" class="w-full rounded-xl border-slate-300 text-sm uppercase shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Téléphone</label>
                        <input id="phone" name="phone" value="{{ old('phone', $school->phone) }}" maxlength="50" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $school->email) }}" maxlength="255" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="website" class="mb-1 block text-sm font-medium text-slate-700">Site web</label>
                        <input id="website" type="url" name="website" value="{{ old('website', $school->website) }}" maxlength="255" placeholder="https://exemple.com" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Localisation</h2>
                    <p class="mt-1 text-sm text-slate-500">Précisez l’adresse administrative et physique de l’établissement.</p>
                </div>
                <div class="grid gap-5 p-5 md:grid-cols-3">
                    @foreach(['province' => 'Province / région', 'city' => 'Ville / territoire', 'commune' => 'Commune', 'district' => 'District', 'subdivision' => 'Sous-division', 'inspection' => 'Inspection', 'address' => 'Adresse'] as $field => $label)
                        <div class="{{ $field === 'address' ? 'md:col-span-3' : '' }}">
                            <label for="{{ $field }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
                            <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $school->{$field}) }}" maxlength="255" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <div class="flex items-start gap-3">
                    <input id="maintenance_mode" type="checkbox" name="maintenance_mode" value="1" @checked(old('maintenance_mode', $school->maintenance_mode)) class="mt-1 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                    <div>
                        <label for="maintenance_mode" class="font-semibold text-amber-950">Mode maintenance</label>
                        <p class="mt-1 text-sm text-amber-800">Suspendre temporairement l’accès du personnel. L’administrateur conserve l’accès pour modifier les paramètres et désactiver ce mode.</p>
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Enregistrer les paramètres</button>
            </div>
        </form>

        <link href="{{ \App\Support\Appearance::fontsUrl($current, true) }}" rel="stylesheet">
        <section id="apparence" x-show="tab === 'apparence'" x-cloak class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                 x-effect="apply()"
                 x-data="{
                    palette: @js($current['palette']), font: @js($current['font']), heading: @js($current['heading_font']),
                    size: @js($current['font_size']), gradient: @js($current['gradient']), sidebar: @js($current['sidebar']), animated: @js($current['animated']),
                    scales: @js(collect($cfg['palettes'])->map(fn ($p) => collect($p['scale'])->map(fn ($hex) => implode(' ', sscanf($hex, '#%02x%02x%02x')))->all())->all()),
                    apply() {
                        const root = document.documentElement, body = document.body;
                        Object.entries(this.scales[this.palette]).forEach(([k, v]) => root.style.setProperty('--brand-' + k, v));
                        root.style.setProperty('--font-sans', this.fonts[this.font]);
                        root.style.setProperty('--font-display', this.fonts[this.heading]);
                        root.style.fontSize = this.sizes[this.size] + 'px';
                        body.className = body.className.replace(/\bys-(grad-\w+|animated|side-dark)\b/g, '').trim() + ' ys-grad-' + this.gradient + (this.animated ? ' ys-animated' : '') + (this.sidebar === 'dark' ? ' ys-side-dark' : '');
                    },
                    palettes: @js(collect($cfg['palettes'])->map(fn ($p) => ['a' => $p['scale'][600], 'b' => $p['scale'][800], 'c' => $p['scale'][100], 'd' => $p['scale'][950]])),
                    fonts: @js(collect($cfg['fonts'])->map(fn ($f) => $f['stack'])),
                    sizes: @js(collect($cfg['font_sizes'])->map(fn ($s) => $s['px'])),
                    grads: {
                        none: '#f1f5f9',
                        aurore: 'radial-gradient(60% 80% at 10% 10%, #c7d2fe, transparent), radial-gradient(50% 70% at 95% 15%, rgba(245,184,0,.35), transparent), #f8fafc',
                        soleil: 'radial-gradient(60% 80% at 90% 0%, rgba(245,184,0,.5), transparent), radial-gradient(60% 80% at 0% 100%, rgba(253,186,116,.45), transparent), #fffaf0',
                        brume: 'radial-gradient(60% 80% at 20% 20%, #e0e7ff, transparent), radial-gradient(60% 80% at 80% 80%, #e2e8f0, transparent), #f8fafc'
                    }
                 }">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 font-semibold text-slate-900">
                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42"/></svg>
                    Apparence de l’interface
                </h2>
                <p class="mt-1 text-sm text-slate-500">Personnalisez les couleurs, l’effet de fond (dégradé), les polices et le menu de votre espace. Ces réglages s’appliquent à tous les utilisateurs de votre école.</p>
            </div>

            <form method="POST" action="{{ route('school.settings.appearance') }}" class="grid gap-6 p-5 lg:grid-cols-[1fr_20rem]">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <fieldset>
                        <legend class="mb-2 text-sm font-semibold text-slate-800">Palette de couleurs</legend>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach($cfg['palettes'] as $key => $p)
                                <label class="cursor-pointer">
                                    <input type="radio" name="palette" value="{{ $key }}" x-model="palette" class="peer sr-only">
                                    <span class="flex items-center gap-2 rounded-xl border border-slate-200 p-2.5 text-sm font-medium text-slate-700 transition peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-600/30 hover:bg-slate-50">
                                        <span class="flex -space-x-1.5">
                                            @foreach([400, 600, 800] as $shade)<span class="h-5 w-5 rounded-full ring-2 ring-white" style="background: {{ $p['scale'][$shade] }}"></span>@endforeach
                                            @if($key === 'yelenscol')<span class="h-5 w-5 rounded-full ring-2 ring-white" style="background:#F5B800"></span>@endif
                                        </span>
                                        <span class="truncate">{{ $p['label'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="mb-2 text-sm font-semibold text-slate-800">Effet visuel de fond (dégradé)</legend>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach($cfg['gradients'] as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="gradient" value="{{ $key }}" x-model="gradient" class="peer sr-only">
                                    <span class="block overflow-hidden rounded-xl border border-slate-200 text-xs font-medium text-slate-700 transition peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-600/30">
                                        <span class="block h-14" :style="{ background: grads['{{ $key }}'] }"></span>
                                        <span class="block px-2 py-1.5">{{ $label }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <label class="mt-3 flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="animated" value="1" x-model="animated" class="rounded border-slate-300">
                            Animer lentement le dégradé
                        </label>
                    </fieldset>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="font" class="mb-1 block text-sm font-semibold text-slate-800">Police du texte</label>
                            <select id="font" name="font" x-model="font" class="w-full rounded-xl border-slate-300 text-sm shadow-sm">
                                @foreach($cfg['fonts'] as $key => $f)<option value="{{ $key }}">{{ $f['label'] }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="heading_font" class="mb-1 block text-sm font-semibold text-slate-800">Police des titres</label>
                            <select id="heading_font" name="heading_font" x-model="heading" class="w-full rounded-xl border-slate-300 text-sm shadow-sm">
                                @foreach($cfg['fonts'] as $key => $f)<option value="{{ $key }}">{{ $f['label'] }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="font_size" class="mb-1 block text-sm font-semibold text-slate-800">Taille du texte</label>
                            <select id="font_size" name="font_size" x-model="size" class="w-full rounded-xl border-slate-300 text-sm shadow-sm">
                                @foreach($cfg['font_sizes'] as $key => $s)<option value="{{ $key }}">{{ $s['label'] }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="sidebar" class="mb-1 block text-sm font-semibold text-slate-800">Style du menu latéral</label>
                            <select id="sidebar" name="sidebar" x-model="sidebar" class="w-full rounded-xl border-slate-300 text-sm shadow-sm">
                                @foreach($cfg['sidebars'] as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-sm font-semibold text-slate-800">Aperçu en direct</p>
                    <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm" :style="{ background: grads[gradient], fontFamily: fonts[font], fontSize: sizes[size] + 'px' }">
                        <div class="flex">
                            <div class="w-16 shrink-0 space-y-1.5 p-2" :style="{ background: sidebar === 'dark' ? palettes[palette].d : '#fff' }">
                                <div class="h-1.5 w-8 rounded bg-[#F5B800]"></div>
                                <div class="h-4 rounded" :style="{ background: palettes[palette].a }"></div>
                                <div class="h-3 rounded" :style="{ background: sidebar === 'dark' ? 'rgba(255,255,255,.15)' : '#f1f5f9' }"></div>
                                <div class="h-3 rounded" :style="{ background: sidebar === 'dark' ? 'rgba(255,255,255,.15)' : '#f1f5f9' }"></div>
                            </div>
                            <div class="flex-1 space-y-2 p-3">
                                <p class="font-bold text-slate-900" :style="{ fontFamily: fonts[heading] }">Tableau de bord</p>
                                <p class="text-xs text-slate-600">Bonjour, voici l’activité de votre école.</p>
                                <div class="rounded-lg bg-white p-2 text-xs shadow-sm"><span class="font-semibold">482</span> élèves inscrits</div>
                                <span class="inline-block rounded-lg px-3 py-1.5 text-xs font-semibold text-white" :style="{ background: palettes[palette].a }">Ajouter un élève</span>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Les changements s’affichent immédiatement sur cette page ; cliquez sur « Enregistrer l’apparence » pour les conserver pour toute l’école.</p>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-4 lg:col-span-2">
                    <button type="submit" name="reset" value="1" formnovalidate class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" onclick="return confirm('Rétablir l’apparence par défaut de YELENSCOL ?')">Réinitialiser</button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Enregistrer l’apparence</button>
                </div>
            </form>
        </section>

        <section id="carte" x-show="tab === 'carte'" x-cloak class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @php($card = array_merge(\App\Models\School::CARD_DEFAULTS, array_filter((array) ($school->card_settings ?? []), fn ($v) => $v !== null && $v !== '')))
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Carte élève</h2>
                <p class="mt-1 text-sm text-slate-500">Couleur de fond et textes de l’en-tête de la carte scolaire de votre école.</p>
            </div>
            <form method="POST" action="{{ route('school.settings.card') }}" class="grid gap-4 p-5 sm:grid-cols-2">
                @csrf
                @method('PUT')
                <div>
                    <label for="card-bg" class="mb-1 block text-sm font-medium text-slate-700">Couleur de fond</label>
                    <input id="card-bg" type="color" name="bg_color" value="{{ old('bg_color', $card['bg_color']) }}" class="h-10 w-full rounded-lg border-slate-300">
                </div>
                <div>
                    <label for="card-text-color" class="mb-1 block text-sm font-medium text-slate-700">Couleur des textes</label>
                    <input id="card-text-color" type="color" name="text_color" value="{{ old('text_color', $card['text_color']) }}" class="h-10 w-full rounded-lg border-slate-300">
                </div>
                @foreach(['line1' => 'Ligne 1 (pays)', 'line2' => 'Ligne 2 (devise)', 'line3' => 'Ligne 3 (ministère)', 'title' => 'Titre de la carte'] as $key => $label)
                    <div>
                        <label for="card-{{ $key }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
                        <input id="card-{{ $key }}" type="text" name="{{ $key }}" value="{{ old($key, $card[$key]) }}" class="w-full rounded-lg border-slate-300 text-sm">
                    </div>
                @endforeach
                <div class="flex gap-2 sm:col-span-2">
                    <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Enregistrer</button>
                    <button name="reset" value="1" formnovalidate class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm text-slate-700">Réinitialiser</button>
                </div>
            </form>
        </section>

        <section id="photos" x-show="tab === 'photos'" x-cloak class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold text-slate-900">Photos de l’école</h2>
                    <p class="mt-1 text-sm text-slate-500">Ajoutez des photos de l’établissement et choisissez la photo principale.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('school.settings.photos.store') }}" enctype="multipart/form-data" class="grid gap-4 border-b border-slate-100 p-5 sm:grid-cols-[1fr_2fr_auto] sm:items-end">
                @csrf
                <div>
                    <label for="photo-title" class="mb-1 block text-sm font-medium text-slate-700">Titre (facultatif)</label>
                    <input id="photo-title" name="title" maxlength="100" placeholder="Façade de l’école" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="photo-file" class="mb-1 block text-sm font-medium text-slate-700">Photo (JPG, PNG, GIF ou WEBP, 10 Mo max.)</label>
                    <input id="photo-file" type="file" name="photo" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" required class="w-full rounded-xl border border-slate-300 text-sm file:mr-3 file:border-0 file:bg-slate-50 file:px-3 file:py-2">
                </div>
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Ajouter la photo</button>
            </form>
            <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
                @forelse($photos as $photo)
                    <article class="overflow-hidden rounded-xl border border-slate-200">
                        <img src="{{ Storage::disk('public')->url($photo->path) }}" alt="{{ $photo->title ?: 'Photo de l’établissement' }}" class="h-40 w-full object-cover">
                        <div class="space-y-3 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-sm font-medium text-slate-800">{{ $photo->title ?: 'Photo de l’école' }}</p>
                                @if($photo->is_primary)<span class="shrink-0 rounded-full bg-amber-100 px-2 py-1 text-[10px] font-bold uppercase text-amber-800">Principale</span>@endif
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @unless($photo->is_primary)
                                    <form method="POST" action="{{ route('school.settings.photos.primary', $photo) }}">@csrf @method('PUT')<button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Définir principale</button></form>
                                @endunless
                                <form method="POST" action="{{ route('school.settings.photos.destroy', $photo) }}" onsubmit="return confirm('Supprimer cette photo ?')">@csrf @method('DELETE')<button class="rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50">Supprimer</button></form>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-500 sm:col-span-2 lg:col-span-4">Aucune photo pour le moment.</p>
                @endforelse
            </div>
        </section>

        <section id="annees" x-show="tab === 'annees'" x-cloak class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-semibold text-slate-900">Années scolaires</h2>
                    <p class="mt-1 text-sm text-slate-500">Année active : {{ $school->currentSchoolYear?->name ?? 'Aucune année active' }}</p>
                </div>
                <a href="{{ route('school-years.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Gérer les années →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($schoolYears as $schoolYear)
                    <div class="flex items-center justify-between gap-3 px-5 py-3">
                        <span class="text-sm font-medium text-slate-800">{{ $schoolYear->name }}</span>
                        @if($schoolYear->is_current)<span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Année active</span>@else<span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Inactive</span>@endif
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-500">Aucune année scolaire enregistrée.</p>
                @endforelse
            </div>
        </section>
        </div>
    </main>
</x-app-layout>
