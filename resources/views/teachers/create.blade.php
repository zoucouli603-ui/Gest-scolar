<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Ajouter un enseignant</h1>
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
            <form action="{{ route('teachers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prénom</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Photo (JPG, PNG, WEBP – 2 Mo max)</label>
                        <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="w-full rounded-xl border border-slate-300 text-sm shadow-sm file:mr-3 file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-indigo-700">
                        @error('photo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Genre</label>
                        <select name="gender" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                            <option value="">Sélectionner...</option>
                            <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Homme</option>
                            <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Femme</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Statut</label>
                        <select name="status" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                            <option value="actif" {{ old('status') == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('status') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                            <option value="congé" {{ old('status') == 'congé' ? 'selected' : '' }}>Congé</option>
                        </select>
                    </div>
                </div>

                <fieldset>
                    <legend class="mb-2 text-sm font-medium text-slate-700">Matière(s)</legend>
                    @if($subjects->isEmpty())
                        <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">Aucune matière n’est encore enregistrée pour {{ $currentYear->name }}. Ajoutez d’abord les matières dans le menu Matières.</p>
                    @else
                        <div class="grid grid-cols-1 gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($subjects as $subject)
                                <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" @checked(in_array($subject->id, old('subject_ids', []))) class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span>{{ $subject->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </fieldset>

                <input type="hidden" name="class_ids_present" value="1">
                <fieldset>
                    <legend class="mb-2 text-sm font-medium text-slate-700">Classes attribuées</legend>
                    @if($classes->isEmpty())
                        <p class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500">Aucune classe n’est encore créée pour {{ $currentYear->name }}.</p>
                    @else
                        <div class="grid grid-cols-1 gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($classes as $class)
                                <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50">
                                    <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" @checked(in_array($class->id, old('class_ids', []))) class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span>{{ $class->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </fieldset>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('teachers.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50 transition">
                        Annuler
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 shadow-sm transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
