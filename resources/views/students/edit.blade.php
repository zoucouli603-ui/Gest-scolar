<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto space-y-6">
        
        <!-- En-tête -->
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Modifier l'élève</h1>
            <p class="text-xs text-slate-500 mt-1">Modifiez les informations de l'élève ci-dessous.</p>
        </div>

        <!-- Gestion des erreurs de validation -->
        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-sm shadow-sm">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulaire de modification -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <form action="{{ route('students.update', $student->id) }}"             method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2 flex items-center gap-4">
                                    @if($student->photo_path)
                                        <img id="photo-preview" src="{{ url('storage/'.ltrim($student->photo_path, '/')) }}" alt="Photo actuelle" class="h-20 w-20 rounded-full object-cover border border-slate-200 shadow-sm">
                                    @else
                                        <img id="photo-preview" src="" alt="" class="hidden h-20 w-20 rounded-full object-cover border border-slate-200 shadow-sm">
                                    @endif
                                    <div class="flex-1">
                                        <label for="photo" class="block text-sm font-medium text-slate-700 mb-1">Photo de l’élève</label>
                                        <input id="photo" type="file" name="photo" accept="image/*" class="w-full rounded-xl border-slate-300 text-sm shadow-sm"
                                               x-on:change="const f=$event.target.files[0],p=document.getElementById('photo-preview');if(f){p.src=URL.createObjectURL(f);p.classList.remove('hidden');}">
                                        <p class="mt-1 text-xs text-slate-500">Laissez vide pour conserver la photo actuelle. 5 Mo maximum.</p>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="birth_certificate" class="block text-sm font-medium text-slate-700 mb-1">Photo de l’extrait de naissance</label>
                                    <input id="birth_certificate" type="file" name="birth_certificate" accept="image/*" class="w-full rounded-xl border-slate-300 text-sm shadow-sm">
                                    @if($student->birth_certificate_path)
                                        <p class="mt-1 text-xs"><a href="{{ route('students.birth-certificate', $student) }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">Voir l’extrait actuel</a> — laissez vide pour le conserver.</p>
                                    @endif
                                </div>                    <!-- Matricule -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Matricule</label>
                        <input type="text" name="registration_number" value="{{ old('registration_number', $student->registration_number) }}" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Prénom -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prénom</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Genre -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Genre</label>
                        <select name="gender" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 shadow-sm text-slate-700">
                            <option value="M" {{ old('gender', $student->gender) == 'M' ? 'selected' : '' }}>Garçon</option>
                            <option value="F" {{ old('gender', $student->gender) == 'F' ? 'selected' : '' }}>Fille</option>
                        </select>
                    </div>

                    <!-- Date de naissance -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date de naissance</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $student->birth_date) }}" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Lieu de naissance -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Lieu de naissance</label>
                        <input type="text" name="birth_place" value="{{ old('birth_place', $student->birth_place) }}" placeholder="Ex: Bamako" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Nationalité -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nationalité</label>
                        <input type="text" name="nationality" value="{{ old('nationality', $student->nationality) }}" placeholder="Ex: Malienne" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Date d'inscription -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Date d'inscription</label>
                        <input type="date" value="{{ $student->inscription_date }}" disabled class="w-full rounded-xl border-slate-200 bg-slate-100 text-sm py-2 px-3.5 text-slate-500 shadow-sm">
                        <p class="mt-1 text-xs text-slate-500">Cette date ne peut pas être modifiée après l'inscription.</p>
                    </div>

                    <!-- Classe -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Classe</label>
                        <select id="class_room_id" name="class_room_id" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 shadow-sm text-slate-700">
                            <option value="">Sélectionner une classe</option>
                            @foreach($classes as $classRoom)
                                <option value="{{ $classRoom->id }}" data-cycle="{{ $classRoom->cycle }}" {{ old('class_room_id', $student->class_room_id) == $classRoom->id ? 'selected' : '' }}>
                                    {{ $classRoom->name }} ({{ number_format((float) $classRoom->monthly_fee, 0, ',', ' ') }} {{ $currency ?? 'FCFA' }}/mois)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="matricule-etat-field" class="hidden">
                        <label for="matricule_etat" class="block text-sm font-medium text-slate-700 mb-1">N° Matricule État</label>
                        <input id="matricule_etat" type="text" name="matricule_etat" value="{{ old('matricule_etat', $student->matricule_etat) }}" maxlength="255" class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- École -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">École</label>
                        <input type="text" name="school_name" value="{{ old('school_name', $student->school_name ?: ($currentSchool?->name ?? Auth::user()?->school?->name ?? '')) }}" class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Nom du parent -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom du parent / tuteur</label>
                        <input type="text" name="parent_name" value="{{ old('parent_name', $student->parent_name) }}" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Téléphone du parent -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone du parent</label>
                        <input type="text" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <!-- Statut -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Statut</label>
                        <select name="status" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 shadow-sm text-slate-700">
                            <option value="actif" {{ old('status', $student->status) == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('status', $student->status) == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                </div>

                <!-- Boutons d'actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition">
                        <x-icon name="x" /> Annuler
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 shadow-sm transition">
                        <x-icon name="check" /> Mettre à jour
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