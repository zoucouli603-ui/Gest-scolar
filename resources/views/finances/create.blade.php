<x-app-layout>
    <div class="mx-auto max-w-3xl p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Enregistrer un paiement</h1>
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

        @if($students->isEmpty() || $classes->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
                @if($classes->isEmpty())
                    Aucune classe n’est enregistrée pour cette année scolaire. Créez une classe avant d’ajouter un paiement.
                @else
                    Aucun élève n’est enregistré pour cette année scolaire. Inscrivez un élève avant d’ajouter un paiement.
                @endif
            </div>
        @else
            <form action="{{ route('finances.store') }}" method="POST" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                    <label for="class_room_id" class="mb-1 block text-sm font-medium text-slate-700">Classe</label>
                    <select id="class_room_id" name="class_room_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Sélectionner une classe</option>
                        @foreach($classes as $classRoom)
                            <option value="{{ $classRoom->id }}" data-fee="{{ (int) $classRoom->monthly_fee }}" @selected(old('class_room_id') == $classRoom->id)>{{ $classRoom->cycle }} — {{ $classRoom->name }} ({{ number_format((float) $classRoom->monthly_fee, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}/mois)</option>
                        @endforeach
                    </select>
                    </div>
                    <div>
                    <label for="student_id" class="mb-1 block text-sm font-medium text-slate-700">Élève</label>
                    <select id="student_id" name="student_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Sélectionner d’abord une classe</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" data-class="{{ $student->class_room_id }}" @selected(old('student_id') == $student->id)>
                                {{ $student->first_name }} {{ $student->last_name }} — {{ $student->registration_number }}
                            </option>
                        @endforeach
                    </select>
                    </div>
                </div>

                <div>
                    <label for="fee_type" class="mb-1 block text-sm font-medium text-slate-700">Type de frais</label>
                    <select id="fee_type" name="fee_type" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Sélectionner le type de frais</option>
                        <option value="inscription" @selected(old('fee_type') === 'inscription')>Frais d’inscription</option>
                        <option value="scolarite" @selected(old('fee_type') === 'scolarite')>Frais de scolarité</option>
                    </select>
                </div>

                <div id="fee-month-field" class="hidden">
                    <label for="fee_month" class="mb-1 block text-sm font-medium text-slate-700">Mois de scolarité concerné</label>
                    <input id="fee_month" type="month" name="fee_month" value="{{ old('fee_month', now()->format('Y-m')) }}" max="{{ now()->format('Y-m') }}" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-slate-500">Indique le mois réglé, même si le paiement est effectué plus tard.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="amount" class="mb-1 block text-sm font-medium text-slate-700">Montant ({{ $currency ?? 'FCFA' }})</label>
                        <input id="amount" type="number" name="amount" min="1" step="1" value="{{ old('amount') }}" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p id="monthly-fee-hint" class="mt-1 text-xs text-slate-500">Pour les frais de scolarité, le tarif mensuel de la classe sera proposé ; vous pouvez saisir un paiement partiel.</p>
                    </div>
                    <div>
                        <label for="payment_date" class="mb-1 block text-sm font-medium text-slate-700">Date du paiement</label>
                        <input id="payment_date" type="date" name="payment_date" max="{{ now()->toDateString() }}" value="{{ old('payment_date', now()->toDateString()) }}" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="payment_method" class="mb-1 block text-sm font-medium text-slate-700">Mode de paiement</label>
                        <select id="payment_method" name="payment_method" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Sélectionner un mode</option>
                            <option value="espèces" @selected(old('payment_method') === 'espèces')>Espèces</option>
                            <option value="mobile_money" @selected(old('payment_method') === 'mobile_money')>Mobile Money</option>
                            <option value="virement" @selected(old('payment_method') === 'virement')>Virement</option>
                            <option value="carte" @selected(old('payment_method') === 'carte')>Carte bancaire</option>
                            <option value="autre" @selected(old('payment_method') === 'autre')>Autre</option>
                        </select>
                    </div>
                    <div>
                        <label for="reference" class="mb-1 block text-sm font-medium text-slate-700">Référence transaction (facultative)</label>
                        <input id="reference" type="text" name="reference" value="{{ old('reference') }}" maxlength="100" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Motif / note (facultatif)</label>
                    <textarea id="description" name="description" rows="3" maxlength="1000" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ route('finances.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"><x-icon name="x" /> Annuler</a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"><x-icon name="check" /> Enregistrer le paiement</button>
                </div>
            </form>
        @endif
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const classSelect = document.getElementById('class_room_id');
            const studentSelect = document.getElementById('student_id');
            const feeTypeSelect = document.getElementById('fee_type');
            const amountInput = document.getElementById('amount');
            const feeMonthField = document.getElementById('fee-month-field');
            const feeMonthInput = document.getElementById('fee_month');
            if (!classSelect || !studentSelect) return;

            const studentOptions = Array.from(studentSelect.options).slice(1);
            let suggestedAmount = '';

            const updateSuggestedFee = () => {
                if (!amountInput || !feeTypeSelect) return;
                if (suggestedAmount !== '' && amountInput.value === suggestedAmount) amountInput.value = '';
                suggestedAmount = '';
                if (feeTypeSelect.value !== 'scolarite') return;
                const selectedClass = classSelect.options[classSelect.selectedIndex];
                const fee = selectedClass?.dataset.fee;
                if (!fee || Number(fee) <= 0) return;
                if (amountInput.value !== '') return;
                suggestedAmount = fee;
                amountInput.value = fee;
            };

            const updateFeeMonth = () => {
                if (!feeTypeSelect || !feeMonthField || !feeMonthInput) return;
                const isTuition = feeTypeSelect.value === 'scolarite';
                feeMonthField.classList.toggle('hidden', !isTuition);
                feeMonthInput.required = isTuition;
            };

            const updateStudents = () => {
                const classId = classSelect.value;
                let selectedStudentIsVisible = false;

                studentOptions.forEach((option) => {
                    const visible = classId !== '' && option.dataset.class === classId;
                    option.hidden = !visible;
                    option.disabled = !visible;
                    if (option.selected && visible) selectedStudentIsVisible = true;
                });

                if (!selectedStudentIsVisible) studentSelect.value = '';
                studentSelect.options[0].textContent = classId
                    ? 'Sélectionner un élève'
                    : 'Sélectionner d’abord une classe';
            };

            classSelect.addEventListener('change', updateStudents);
            classSelect.addEventListener('change', updateSuggestedFee);
            feeTypeSelect?.addEventListener('change', updateSuggestedFee);
            feeTypeSelect?.addEventListener('change', updateFeeMonth);
            updateStudents();
            updateSuggestedFee();
            updateFeeMonth();
        });
    </script>
</x-app-layout>
