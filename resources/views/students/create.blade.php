<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Inscrire un élève</h1>
            @if(isset($currentYear) && $currentYear)
                <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Année : {{ $currentYear->name }}</span>
            @endif
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="photo" class="block text-sm font-medium text-slate-700 mb-1">Photo de l’élève (facultative)</label>
                        <input id="photo" type="file" name="photo" accept="image/*" class="w-full rounded-xl border-slate-300 text-sm shadow-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label for="birth_certificate" class="block text-sm font-medium text-slate-700 mb-1">Photo de l’extrait de naissance (facultative)</label>
                        <input id="birth_certificate" type="file" name="birth_certificate" accept="image/*" class="w-full rounded-xl border-slate-300 text-sm shadow-sm">
                        <p class="mt-1 text-xs text-slate-500">Formats image acceptés, 5 Mo maximum.</p>
                    </div>
                    <!-- Prénom -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prénom</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Genre -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Genre</label>
                        <select name="gender" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                            <option value="">Sélectionner...</option>
                            <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Garçon</option>
                            <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Fille</option>
                        </select>
                    </div>

                    <!-- Date de naissance -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date de naissance</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Lieu de naissance -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lieu de naissance</label>
                        <input type="text" name="birth_place" value="{{ old('birth_place') }}" placeholder="Ex: Bamako" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Nationalité -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nationalité</label>
                        <input type="text" name="nationality" value="{{ old('nationality') }}" placeholder="Ex: Malienne" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Date d'inscription -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date d'inscription</label>
                        <input type="date" name="inscription_date" value="{{ old('inscription_date', now()->toDateString()) }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Classe -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Classe</label>
                        <select id="class_room_id" name="class_room_id" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                            <option value="">Sélectionner une classe</option>
                            @foreach($classes as $classRoom)
                                <option value="{{ $classRoom->id }}" data-cycle="{{ $classRoom->cycle }}" {{ old('class_room_id') == $classRoom->id ? 'selected' : '' }}>
                                {{ $classRoom->name }} ({{ number_format((float) $classRoom->monthly_fee, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}/mois)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="matricule-etat-field" class="hidden">
                        <label for="matricule_etat" class="block text-sm font-medium text-slate-700 mb-1">N° Matricule État</label>
                        <input id="matricule_etat" type="text" name="matricule_etat" value="{{ old('matricule_etat') }}" maxlength="255" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- École -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">École</label>
                        <input type="text" name="school_name" value="{{ old('school_name', $currentSchool?->name ?? Auth::user()?->school?->name ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Nom du parent -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom du parent / tuteur</label>
                        <input type="text" name="parent_name" value="{{ old('parent_name') }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Téléphone du parent -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone du parent</label>
                        <input type="text" name="parent_phone" value="{{ old('parent_phone') }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50 transition">
                        <x-icon name="x" /> Annuler
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 shadow-sm transition">
                        <x-icon name="check" /> Enregistrer l'inscription
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script @cspNonce>
        (() => {
            const classSelect = document.getElementById('class_room_id');
            const matriculeField = document.getElementById('matricule-etat-field');
            const matriculeInput = document.getElementById('matricule_etat');
            const updateMatriculeField = () => {
                const isLycee = classSelect.selectedOptions[0]?.dataset.cycle === 'Lycée';
                matriculeField.classList.toggle('hidden', !isLycee);
                matriculeInput.disabled = !isLycee;
            };

            classSelect.addEventListener('change', updateMatriculeField);
            updateMatriculeField();
        })();
    </script>
</x-app-layout>