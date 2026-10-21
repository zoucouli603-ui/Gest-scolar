<x-app-layout>
    <main class="mx-auto max-w-7xl space-y-5 p-4 md:p-6">
        <header class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-800"><x-icon name="academic" class="h-6 w-6 text-indigo-600" /> Notes et Bulletins</h1>
                <p class="mt-1 text-sm text-slate-500">Sélectionnez une classe, une période et une matière pour saisir les notes de toute la classe.</p>
            </div>
            @if($currentYear)
                <span class="w-fit rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm">Année scolaire : {{ $currentYear->name }}</span>
            @endif
        </header>
        <x-flash-messages />
        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        @if(!$currentYear)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">Définissez d’abord une année scolaire active.</div>
        @else
            <form method="GET" action="{{ route('notes.index') }}" class="max-w-6xl rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-[1.2fr_1fr_1.3fr_.6fr_auto] xl:items-end">
                    <div>
                        <label for="class_room_id" class="mb-1 block text-xs font-medium text-slate-700">Classe</label>
                        <select id="class_room_id" name="class_room_id" required class="w-full rounded-lg border-slate-300 text-sm">
                            <option value="">Choisir une classe</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" data-cycle="{{ $class->cycle }}" @selected($selectedClassId == $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="trimester-filter">
                        <label for="trimester" class="mb-1 block text-xs font-medium text-slate-700">Période</label>
                        <div id="trimester-select-wrapper">
                            <select id="trimester" name="trimester" class="w-full rounded-lg border-slate-300 text-sm">
                                <option value="1" @selected($trimester === 1)>1er trimestre</option>
                                <option value="2" @selected($trimester === 2)>2e trimestre</option>
                                <option value="3" @selected($trimester === 3)>3e trimestre</option>
                            </select>
                        </div>
                        <input id="evaluation_month" type="month" name="evaluation_month" value="{{ $evaluationMonth }}" class="hidden w-full rounded-lg border-slate-300 text-sm">
                    </div>
                    <div>
                        <label for="subject_id" class="mb-1 block text-xs font-medium text-slate-700">Matière</label>
                        <select id="subject_id" name="subject_id" required @disabled(!$selectedClassId) class="w-full rounded-lg border-slate-300 text-sm disabled:bg-slate-100">
                            <option value="">Choisir une matière</option>
                            @foreach($assignments as $assignment)
                                <option value="{{ $assignment->subject_id }}" @selected($selectedSubjectId == $assignment->subject_id)>{{ $assignment->subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="coefficient" class="mb-1 block text-xs font-medium text-slate-700">Coefficient</label>
                        <input id="coefficient" name="coefficient" type="number" min="0.5" max="10" step="0.5"
                               value="{{ $selectedCoefficient }}" @disabled(!$selectedSubjectId) required
                               class="w-full rounded-lg border-slate-300 text-sm disabled:bg-slate-100">
                    </div>
                    <button type="submit" class="whitespace-nowrap rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Afficher la liste</button>
                </div>
            </form>

            @if($selectedClass && $selectedSubject)
                @php
                    $isPrimary = in_array($selectedClass->cycle, ['1er Cycle', 'Primaire'], true);
                    $selectedEvaluationType = $isPrimary ? 'composition' : 'devoir';
                @endphp
                <section class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-blue-800 bg-blue-700 px-4 py-3 text-white shadow-sm">
                    <div>
                        <h2 class="font-semibold">{{ $selectedClass->name }} — {{ $selectedSubject->name }}</h2>
                        <p class="mt-1 text-xs text-blue-100">{{ $isPrimary ? 'Composition du mois '.\Illuminate\Support\Carbon::createFromFormat('Y-m', $evaluationMonth)->locale('fr')->translatedFormat('F Y') : [1 => '1er trimestre', 2 => '2e trimestre', 3 => '3e trimestre'][$trimester] }} · {{ $students->count() }} élève(s)</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <select id="bulletin-student" class="rounded-lg border-0 px-3 py-2 text-sm text-slate-800">
                            <option value="">Choisir l’élève</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->last_name }} {{ $student->first_name }}</option>
                            @endforeach
                        </select>
                        <a id="bulletin-link" href="#" target="_blank" class="pointer-events-none rounded-lg bg-white px-3 py-2 text-sm font-semibold text-blue-800 opacity-50">Bulletin</a>
                        <a href="{{ route('notes.class-bulletin', ['class_room_id' => $selectedClass->id, 'trimester' => $trimester, 'evaluation_month' => $evaluationMonth]) }}" target="_blank" class="rounded-lg border border-white/70 px-3 py-2 text-sm font-semibold text-white hover:bg-white/10">Tous les bulletins</a>
                    </div>
                </section>

                <form method="POST" action="{{ route('notes.store') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @csrf
                    <input type="hidden" name="class_room_id" value="{{ $selectedClass->id }}">
                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                    <input type="hidden" name="evaluation_type" value="{{ $selectedEvaluationType }}">
                    <input type="hidden" name="coefficient" value="{{ $selectedCoefficient }}">
                    @if($isPrimary)
                        <input type="hidden" name="evaluation_month" value="{{ $evaluationMonth }}">
                    @else
                        <input type="hidden" name="trimester" value="{{ $trimester }}">
                        <input type="hidden" name="note_date" value="{{ now()->toDateString() }}">
                    @endif
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-4">
                        <div>
                            <h2 class="font-semibold text-slate-900">Saisie des notes</h2>
                            <p class="text-xs text-slate-500">Barème : /{{ $gradeScale }} · {{ $isPrimary ? 'Saisissez une seule note de composition par élève.' : 'Saisissez la note de classe et la note du trimestre.' }}</p>
                        </div>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Enregistrer les notes</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-600">
                                <tr>
                                    <th class="px-4 py-3">Élève</th>
                                    @if($isPrimary)
                                        <th class="px-4 py-3 text-center">NOTE /10</th>
                                    @else
                                        <th class="px-4 py-3 text-center">Note de classe /{{ $gradeScale }}</th>
                                        <th class="px-4 py-3 text-center">Note du trimestre /{{ $gradeScale }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($classGradeRows as $row)
                                    @php($existing = $row['score'])
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $row['student']->last_name }} {{ $row['student']->first_name }}</td>
                                        <td class="px-4 py-3"><input type="number" name="class_scores[{{ $row['student']->id }}]" value="{{ old('class_scores.'.$row['student']->id, $row['class_score']) }}" min="0" max="{{ $gradeScale }}" step="0.01" class="mx-auto block w-28 rounded-lg border-slate-300 text-center text-sm"></td>
                                        @unless($isPrimary)
                                            <td class="px-4 py-3"><input type="number" name="term_scores[{{ $row['student']->id }}]" value="{{ old('term_scores.'.$row['student']->id, $row['term_score']) }}" min="0" max="{{ $gradeScale }}" step="0.01" class="mx-auto block w-28 rounded-lg border-slate-300 text-center text-sm"></td>
                                        @endunless
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $isPrimary ? 2 : 3 }}" class="px-4 py-6 text-center text-slate-500">Aucun élève inscrit dans cette classe.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            @elseif($selectedClass)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">Choisissez une matière pour afficher la liste des élèves.</div>
            @endif
        @endif
    </main>
    <script @cspNonce>
        const queuedNotesStore = {
            name: 'YenScolOffline',
            store: 'pendingNotes',
            open() {
                if (!('indexedDB' in window)) {
                    return Promise.resolve(null);
                }

                return new Promise((resolve, reject) => {
                    const request = indexedDB.open(this.name, 1);

                    request.onupgradeneeded = () => {
                        const database = request.result;
                        if (!database.objectStoreNames.contains(this.store)) {
                            database.createObjectStore(this.store, { keyPath: 'id', autoIncrement: true });
                        }
                    };

                    request.onsuccess = () => resolve(request.result);
                    request.onerror = () => reject(request.error);
                });
            },
            async all() {
                const database = await this.open();
                if (!database) return [];

                return new Promise((resolve, reject) => {
                    const transaction = database.transaction(this.store, 'readonly');
                    const request = transaction.objectStore(this.store).getAll();
                    request.onsuccess = () => resolve(request.result);
                    request.onerror = () => reject(request.error);
                    transaction.oncomplete = () => database.close();
                });
            },
            async add(payload) {
                const database = await this.open();
                if (!database) return null;

                return new Promise((resolve, reject) => {
                    const transaction = database.transaction(this.store, 'readwrite');
                    const request = transaction.objectStore(this.store).add({
                        ...payload,
                        created_at: new Date().toISOString(),
                    });
                    request.onsuccess = () => resolve(request.result);
                    request.onerror = () => reject(request.error);
                    transaction.oncomplete = () => database.close();
                });
            },
            async deleteMany(ids) {
                const database = await this.open();
                if (!database || !ids.length) return;

                const transaction = database.transaction(this.store, 'readwrite');
                const store = transaction.objectStore(this.store);

                ids.forEach((id) => store.delete(id));
                transaction.oncomplete = () => database.close();
            }
        };

        const collectOfflineNotes = (form) => {
            const metadata = {
                class_room_id: form.querySelector('input[name="class_room_id"]')?.value,
                subject_id: form.querySelector('input[name="subject_id"]')?.value,
                evaluation_type: form.querySelector('input[name="evaluation_type"]')?.value,
                trimester: form.querySelector('input[name="trimester"]')?.value,
                evaluation_month: form.querySelector('input[name="evaluation_month"]')?.value,
                note_date: form.querySelector('input[name="note_date"]')?.value,
                coefficient: form.querySelector('input[name="coefficient"]')?.value,
            };

            const noteEntries = [];
            const inputs = [
                ...Array.from(form.querySelectorAll('input[name^="class_scores["]')),
                ...Array.from(form.querySelectorAll('input[name^="term_scores["]')),
            ];

            inputs.forEach((input) => {
                const value = input.value.trim();
                if (value === '') {
                    return;
                }

                const match = input.name.match(/\[(\d+)\]/);
                const studentId = match ? Number(match[1]) : null;
                if (!studentId) {
                    return;
                }

                const type = input.name.startsWith('term_scores[') ? 'examen_trimestriel' : metadata.evaluation_type;
                const payload = {
                    ...metadata,
                    student_id: studentId,
                    evaluation_type: type,
                    score: Number(value),
                };

                if (!payload.class_room_id || !payload.subject_id) {
                    return;
                }

                noteEntries.push(payload);
            });

            return noteEntries;
        };

        const syncQueuedNotes = async () => {
            if (!navigator.onLine) {
                return;
            }

            const pendingNotes = await queuedNotesStore.all();
            if (!pendingNotes.length) {
                return;
            }

            const payload = pendingNotes.flatMap((entry) => entry.notes ?? []);
            if (!payload.length) {
                return;
            }

            try {
                const response = await fetch('{{ route('sync.notes') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ notes: payload }),
                });

                if (!response.ok) {
                    throw new Error('La synchronisation a échoué.');
                }

                const ids = pendingNotes.map((note) => note.id).filter((id) => id !== undefined);
                await queuedNotesStore.deleteMany(ids);
                window.alert('Toutes vos saisies hors ligne ont été synchronisées avec succès !');
            } catch (error) {
                console.error(error);
            }
        };

        document.addEventListener('DOMContentLoaded', async () => {
            const classSelect = document.getElementById('class_room_id');
            const subjectSelect = document.getElementById('subject_id');
            classSelect?.addEventListener('change', () => { subjectSelect.value = ''; subjectSelect.disabled = !classSelect.value; classSelect.form.submit(); });
            const cycle = classSelect?.selectedOptions[0]?.dataset.cycle;
            const monthInput = document.getElementById('evaluation_month');
            const trimesterWrapper = document.getElementById('trimester-select-wrapper');
            const isPrimaryCycle = ['1er Cycle', 'Primaire'].includes(cycle);
            if (monthInput) monthInput.classList.toggle('hidden', !isPrimaryCycle);
            if (trimesterWrapper) trimesterWrapper.classList.toggle('hidden', isPrimaryCycle);
            const bulletinStudent = document.getElementById('bulletin-student');
            const bulletinLink = document.getElementById('bulletin-link');
            const classId = @json($selectedClassId);
            const trimester = @json($trimester);
            const month = @json($evaluationMonth);
            const updateBulletinLink = () => {
                if (!bulletinStudent.value) {
                    bulletinLink.href = '#';
                    bulletinLink.classList.add('pointer-events-none', 'opacity-50');
                    return;
                }
                const params = new URLSearchParams({ class_room_id: classId, student_id: bulletinStudent.value });
                if (['1er Cycle', 'Primaire'].includes(cycle)) params.set('evaluation_month', month);
                else params.set('trimester', trimester);
                bulletinLink.href = `{{ route('notes.bulletin') }}?${params.toString()}`;
                bulletinLink.classList.remove('pointer-events-none', 'opacity-50');
            };
            bulletinStudent?.addEventListener('change', updateBulletinLink);
            updateBulletinLink();

            const saveForm = document.querySelector('form[action="{{ route('notes.store') }}"]');
            if (saveForm) {
                saveForm.addEventListener('submit', async (event) => {
                    if (navigator.onLine) {
                        return;
                    }

                    event.preventDefault();
                    const notes = collectOfflineNotes(saveForm);
                    if (!notes.length) {
                        return;
                    }

                    await queuedNotesStore.add({ notes });
                    const message = 'Les notes ont été enregistrées hors ligne. Elles seront envoyées automatiquement dès que la connexion reviendra.';
                    window.alert(message);
                    saveForm.reset();
                });
            }

            await syncQueuedNotes();
            window.addEventListener('online', syncQueuedNotes);
        });
    </script>
</x-app-layout>
