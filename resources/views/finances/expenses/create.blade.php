<x-app-layout>
    <div class="mx-auto max-w-2xl p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Enregistrer une dépense</h1>
            <p class="mt-1 text-sm text-slate-500">Année scolaire : {{ $currentYear->name }}</p>
        </div>

        @if($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-100 p-4 text-sm text-rose-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('finances.expenses.store') }}" method="POST" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="expense_date" class="mb-1 block text-sm font-medium text-slate-700">Date</label>
                    <input id="expense_date" type="date" name="expense_date" max="{{ now()->toDateString() }}" value="{{ old('expense_date', now()->toDateString()) }}" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
                <div>
                    <label for="category" class="mb-1 block text-sm font-medium text-slate-700">Catégorie</label>
                    <select id="category" name="category" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="salary-teacher-field" class="hidden">
                <label for="teacher_id" class="mb-1 block text-sm font-medium text-slate-700">Enseignant payé</label>
                <select id="teacher_id" name="teacher_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Sélectionner un enseignant</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" data-monthly-salary="{{ (int) $teacher->monthly_salary }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->last_name }} {{ $teacher->first_name }}{{ $teacher->phone ? ' — '.$teacher->phone : '' }}</option>
                    @endforeach
                </select>
                @if($teachers->isEmpty())
                    <p class="mt-1 text-xs text-amber-700">Aucun enseignant actif n’est enregistré pour cette année scolaire.</p>
                @endif
            </div>

            <div>
                <label for="amount" class="mb-1 block text-sm font-medium text-slate-700">Montant ({{ $currency ?? 'FCFA' }})</label>
                <input id="amount" type="number" name="amount" min="1" step="1" value="{{ old('amount') }}" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Motif de la dépense</label>
                <textarea id="description" name="description" rows="4" maxlength="1000" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('description') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <a href="{{ route('finances.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"><x-icon name="x" /> Annuler</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"><x-icon name="check" /> Enregistrer la dépense</button>
            </div>
        </form>
    </div>
    <script @cspNonce>
        document.addEventListener('DOMContentLoaded', () => {
            const category = document.getElementById('category');
            const teacherField = document.getElementById('salary-teacher-field');
            const teacher = document.getElementById('teacher_id');
            const amount = document.getElementById('amount');
            if (!category || !teacherField || !teacher || !amount) return;

            const applyTeacherSalary = () => {
                const selectedTeacher = teacher.selectedOptions[0];
                if (selectedTeacher && selectedTeacher.value) {
                    amount.value = selectedTeacher.dataset.monthlySalary || '';
                }
            };

            const updateTeacherField = () => {
                const isSalary = category.value === 'salaires';
                teacherField.classList.toggle('hidden', !isSalary);
                teacher.required = isSalary;
                if (isSalary) {
                    applyTeacherSalary();
                } else {
                    teacher.value = '';
                }
            };

            category.addEventListener('change', updateTeacherField);
            teacher.addEventListener('change', applyTeacherSalary);
            updateTeacherField();
        });
    </script>
</x-app-layout>
