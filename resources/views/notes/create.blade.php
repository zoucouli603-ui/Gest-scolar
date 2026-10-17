<x-app-layout>
    <div class="mx-auto max-w-4xl p-4 md:p-5">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Ajouter une note</h1>
                <p class="text-sm text-slate-500">Enregistrer une &eacute;valuation pour un &eacute;l&egrave;ve.</p>
            </div>
            <a href="{{ route('notes.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Retour</a>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form action="{{ route('notes.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="class_room_id" class="mb-1 block text-sm font-medium text-slate-700">Classe</label>
                        <select id="class_room_id" name="class_room_id" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Choisir une classe</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_room_id') == $class->id)>{{ $class->cycle }} — {{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="student_id" class="mb-1 block text-sm font-medium text-slate-700">&Eacute;l&egrave;ve</label>
                        <select id="student_id" name="student_id" required disabled class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 disabled:cursor-not-allowed disabled:bg-slate-100">
                            <option value="">Choisir d&rsquo;abord une classe</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" data-class-room-id="{{ $student->notes_class_room_id }}" @selected(old('student_id') == $student->id)>{{ $student->last_name }} {{ $student->first_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="subject_id" class="mb-1 block text-sm font-medium text-slate-700">Mati&egrave;re</label>
                        <select id="subject_id" name="subject_id" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Choisir une mati&egrave;re</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="teacher_id" class="mb-1 block text-sm font-medium text-slate-700">Enseignant (facultatif)</label>
                        <select id="teacher_id" name="teacher_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Choisir un enseignant</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->last_name }} {{ $teacher->first_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="evaluation_type" class="mb-1 block text-sm font-medium text-slate-700">Type d&rsquo;&eacute;valuation</label>
                        <select id="evaluation_type" name="evaluation_type" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="devoir" @selected(old('evaluation_type', 'devoir') == 'devoir')>Devoir</option>
                            <option value="composition" @selected(old('evaluation_type') == 'composition')>Composition</option>
                            <option value="interrogation" @selected(old('evaluation_type') == 'interrogation')>Interrogation</option>
                            <option value="examen" @selected(old('evaluation_type') == 'examen')>Examen</option>
                        </select>
                    </div>

                    <div>
                        <label for="note_date" class="mb-1 block text-sm font-medium text-slate-700">Date</label>
                        <input id="note_date" type="date" name="note_date" value="{{ old('note_date', now()->toDateString()) }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="score" class="mb-1 block text-sm font-medium text-slate-700">Note</label>
                        <input id="score" type="number" step="0.01" min="0" max="100" name="score" value="{{ old('score') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ex. 15.50">
                    </div>

                    <div>
                        <label for="max_score" class="mb-1 block text-sm font-medium text-slate-700">Note maximale</label>
                        <input id="max_score" type="number" step="0.01" min="1" max="100" name="max_score" value="{{ old('max_score', 20) }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="appreciation" class="mb-1 block text-sm font-medium text-slate-700">Appr&eacute;ciation</label>
                    <textarea id="appreciation" name="appreciation" rows="3" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Appr&eacute;ciation du professeur...">{{ old('appreciation') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ route('notes.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Annuler</a>
                    <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const classSelect = document.getElementById('class_room_id');
            const studentSelect = document.getElementById('student_id');
            const studentOptions = Array.from(studentSelect.options).filter(option => option.value);
            const oldStudentId = @json(old('student_id'));

            function filterStudents() {
                const classRoomId = classSelect.value;
                const previousStudentId = studentSelect.value || oldStudentId;

                studentOptions.forEach(option => {
                    option.hidden = !classRoomId || option.dataset.classRoomId !== classRoomId;
                });

                studentSelect.disabled = !classRoomId;
                studentSelect.options[0].textContent = classRoomId
                    ? 'Choisir un élève'
                    : 'Choisir d’abord une classe';

                if (previousStudentId && studentOptions.some(option =>
                    option.value === String(previousStudentId) && option.dataset.classRoomId === classRoomId
                )) {
                    studentSelect.value = previousStudentId;
                } else {
                    studentSelect.value = '';
                }
            }

            classSelect.addEventListener('change', filterStudents);
            filterStudents();
        });
    </script>
</x-app-layout>
