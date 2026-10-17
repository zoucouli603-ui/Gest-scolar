<x-app-layout>
    <div class="module-page mx-auto max-w-6xl space-y-4 p-4 md:p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800"><x-icon name="calendar" /> Scan QR Code</h1>
                <p class="mt-1 text-xs text-slate-500">Pointeuse des enseignants : scannez le badge pour enregistrer l'arrivée puis le départ.</p>
            </div>
        </div>

        <div class="flex gap-1 border-b border-slate-200">
            <a href="{{ route('teachers.index') }}" class="-mb-px inline-flex items-center gap-2 border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800"><x-icon name="users" /> Enseignants</a>
            <a href="{{ route('teachers.attendance') }}" class="-mb-px inline-flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold" style="border-color:#2563eb;color:#1d4ed8"><x-icon name="calendar" /> Scan QR Code</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-2 px-5 py-3 text-sm font-bold text-white" style="background:linear-gradient(90deg,#3730a3,#2563eb)">
                <x-icon name="calendar" /> Scanner un QR Code
            </div>

            <div class="grid gap-5 p-5 lg:grid-cols-2">
                {{-- Caméra --}}
                <div class="rounded-2xl p-4" style="background:#0b1220">
                    <div class="relative mx-auto overflow-hidden rounded-xl" style="aspect-ratio:1/1;max-width:26rem;background:#111827">
                        <video id="camera" playsinline muted style="width:100%;height:100%;object-fit:cover;display:none"></video>
                        <div id="camera-frame" class="pointer-events-none absolute inset-6" style="display:none;border:2px dashed rgba(255,255,255,.7);border-radius:.75rem"></div>
                        <div id="camera-idle" class="flex h-full w-full items-center justify-center text-sm text-slate-400">Caméra à l'arrêt</div>
                    </div>
                    <canvas id="canvas" class="hidden"></canvas>
                    <button type="button" id="camera-btn" class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white shadow" style="background:#16a34a">
                        <span id="camera-btn-label">▶ Démarrer</span>
                    </button>
                    <p id="camera-error" class="mt-2 hidden text-center text-xs" style="color:#fca5a5"></p>

                    <form id="scan-form" method="POST" action="{{ route('teachers.attendance.scan') }}" class="mt-4 flex gap-2">
                        @csrf
                        <input id="scan-code" type="text" name="code" autocomplete="off" placeholder="Lecteur USB : scannez ici" class="min-w-0 flex-1 rounded-xl border-0 bg-slate-800 text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 text-sm font-medium text-white hover:bg-blue-700">OK</button>
                    </form>
                </div>

                {{-- Résultat --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <h2 class="mb-3 flex items-center gap-2 border-b border-slate-200 pb-2 text-sm font-bold text-slate-600"><x-icon name="user" /> Résultat du scan</h2>

                    <div id="result-wait" class="flex flex-col items-center justify-center py-14 text-center text-slate-400">
                        <x-icon name="id-card" class="h-10 w-10" />
                        <p class="mt-2 text-base font-semibold text-slate-500">En attente de scan</p>
                        <p class="text-xs">Scannez un QR code</p>
                    </div>

                    <div id="result-error" class="hidden rounded-xl border border-rose-200 bg-rose-50 p-6 text-center text-sm font-semibold text-rose-700"></div>

                    <div id="result-card" class="hidden rounded-2xl bg-white p-5 text-center shadow-sm">
                        <div id="r-photo-wrap" class="mx-auto overflow-hidden rounded-full bg-indigo-500" style="width:6rem;height:6rem;box-shadow:0 0 0 3px #c7d2fe">
                            <img id="r-photo" alt="Photo" style="width:100%;height:100%;object-fit:cover;display:none">
                            <div id="r-initials" class="flex h-full w-full items-center justify-center text-3xl font-bold text-white"></div>
                        </div>
                        <p id="r-name" class="mt-3 text-lg font-extrabold uppercase text-slate-800"></p>
                        <p id="r-subjects" class="text-xs text-slate-500"></p>
                        <p id="r-phone" class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600"></p>
                        <p class="mt-3"><span id="r-badge" class="inline-block rounded-full px-4 py-1.5 text-xs font-bold text-white"></span></p>
                        <p id="r-already" class="mt-1 hidden text-xs font-semibold text-amber-600">Pointage déjà effectué</p>
                        <button type="button" id="r-ok" class="mt-4 w-full rounded-full bg-blue-600 py-2.5 text-sm font-bold text-white hover:bg-blue-700">✓ OK</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Historique des présences</h2>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}" class="rounded-lg border-slate-200 text-sm shadow-sm">
                <button class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">Afficher</button>
            </form>
        </div>

        <div id="history" class="space-y-4">
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-400">
                    <tr><th class="px-4 py-3">Enseignant</th><th class="px-4 py-3">Arrivée</th><th class="px-4 py-3">Départ</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($records as $r)
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $r->teacher->first_name }} {{ $r->teacher->last_name }}</td>
                            <td class="px-4 py-2.5"><span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ $r->check_in_at?->format('H:i') ?? '—' }}</span></td>
                            <td class="px-4 py-2.5">
                                @if($r->check_out_at)
                                    <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">{{ $r->check_out_at->format('H:i') }}</span>
                                @else — @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-8 text-center text-slate-400">Aucun pointage pour cette date.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($absentTeachers->isNotEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                <p class="mb-1 font-semibold">Pas encore pointés ({{ $absentTeachers->count() }})</p>
                <p>{{ $absentTeachers->map(fn ($t) => $t->first_name.' '.$t->last_name)->join(', ') }}</p>
            </div>
        @endif
    </div>

    <script>
        (() => {
            const $ = (id) => document.getElementById(id);
            const form = $('scan-form'), input = $('scan-code');
            const token = form.querySelector('input[name=_token]').value;
            const video = $('camera'), canvas = $('canvas'), ctx = canvas.getContext('2d', { willReadFrequently: true });
            let stream = null, timer = null, busy = false, jsQR = null, lastCode = '', lastAt = 0, dirty = false;

            const show = (which) => {
                ['result-wait', 'result-error', 'result-card'].forEach(id => $(id).classList.toggle('hidden', id !== which));
            };

            const render = (data) => {
                if (!data.ok) {
                    $('result-error').textContent = data.message || 'Erreur.';
                    show('result-error');
                    return;
                }
                const t = data.teacher;
                dirty = true;
                $('r-name').textContent = t.name;
                $('r-subjects').textContent = t.subjects;
                $('r-phone').textContent = t.phone ? '📞 ' + t.phone : 'Téléphone non renseigné';
                $('r-initials').textContent = t.initials;
                $('r-photo').style.display = t.photo ? 'block' : 'none';
                $('r-initials').style.display = t.photo ? 'none' : 'flex';
                if (t.photo) $('r-photo').src = t.photo;
                const arrival = t.action === 'arrivee';
                $('r-badge').textContent = data.message;
                $('r-badge').style.background = arrival ? '#16a34a' : '#f59e0b';
                $('r-already').classList.toggle('hidden', !t.already);
                show('result-card');
            };

            const send = async (code) => {
                code = (code || '').trim();
                if (!code || busy) return;
                if (code === lastCode && Date.now() - lastAt < 4000) return;
                busy = true; lastCode = code; lastAt = Date.now();
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ code }),
                    });
                    render(await res.json().catch(() => ({ ok: false, message: 'Erreur inattendue.' })));
                } catch (e) {
                    render({ ok: false, message: 'Connexion impossible.' });
                } finally {
                    input.value = '';
                    busy = false;
                }
            };

            const refreshHistory = async () => {
                try {
                    const html = await (await fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })).text();
                    const fresh = new DOMParser().parseFromString(html, 'text/html').getElementById('history');
                    if (fresh) $('history').innerHTML = fresh.innerHTML;
                } catch (e) {}
            };

            const tick = () => {
                if (!stream || video.readyState < 2 || busy) return;
                const w = video.videoWidth, h = video.videoHeight;
                if (!w) return;
                canvas.width = w; canvas.height = h;
                ctx.drawImage(video, 0, 0, w, h);
                const img = ctx.getImageData(0, 0, w, h);
                const found = jsQR(img.data, w, h, { inversionAttempts: 'dontInvert' });
                if (found && found.data) send(found.data);
            };

            const stop = () => {
                clearInterval(timer);
                if (stream) stream.getTracks().forEach(t => t.stop());
                stream = null;
                video.style.display = 'none'; $('camera-frame').style.display = 'none'; $('camera-idle').style.display = 'flex';
                $('camera-btn-label').textContent = '▶ Démarrer'; $('camera-btn').style.background = '#16a34a';
            };

            const start = async () => {
                const err = $('camera-error'); err.classList.add('hidden');
                if (!navigator.mediaDevices?.getUserMedia) {
                    err.textContent = "Caméra indisponible : ouvrez l'application en http://localhost / 127.0.0.1 ou en HTTPS.";
                    err.classList.remove('hidden'); return;
                }
                try {
                    jsQR = jsQR || await window.loadJsQR();
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
                    video.srcObject = stream;
                    await video.play();
                    video.style.display = 'block'; $('camera-frame').style.display = 'block'; $('camera-idle').style.display = 'none';
                    $('camera-btn-label').textContent = '■ Arrêter'; $('camera-btn').style.background = '#dc2626';
                    timer = setInterval(tick, 250);
                } catch (e) {
                    err.textContent = "Impossible d'accéder à la caméra. Autorisez-la dans le navigateur.";
                    err.classList.remove('hidden');
                }
            };

            $('camera-btn').addEventListener('click', () => stream ? stop() : start());
            $('r-ok').addEventListener('click', () => { show('result-wait'); lastCode = ''; if (dirty) { dirty = false; refreshHistory(); } });
            form.addEventListener('submit', (e) => { e.preventDefault(); send(input.value); });
            window.addEventListener('beforeunload', stop);
        })();
    </script>
</x-app-layout>
