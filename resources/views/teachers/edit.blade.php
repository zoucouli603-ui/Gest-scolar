<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Modifier l'enseignant</h1>
            <p class="text-xs text-slate-500 mt-1">{{ $teacher->first_name }} {{ $teacher->last_name }}</p>
        </div>

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-sm shadow-sm">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <form action="{{ route('teachers.update', $teacher->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prénom</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $teacher->first_name) }}" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <div class="md:col-span-2 flex items-center gap-4">
                        <div class="shrink-0 overflow-hidden rounded-full bg-indigo-50" style="width:4.5rem;height:4.5rem;box-shadow:0 0 0 2px #c7d2fe">
                            @if($teacher->photo_url)
                                <img src="{{ $teacher->photo_url }}" alt="Photo" style="width:100%;height:100%;object-fit:cover">
                            @endif
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Changer la photo (JPG, PNG, WEBP – 2 Mo max)</label>
                            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" class="w-full rounded-xl border border-slate-300 text-sm shadow-sm file:mr-3 file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-indigo-700">
                            @error('photo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nom</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $teacher->last_name) }}" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Genre</label>
                        <select name="gender" class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 shadow-sm text-slate-700">
                            <option value="M" {{ old('gender', $teacher->gender) == 'M' ? 'selected' : '' }}>Homme</option>
                            <option value="F" {{ old('gender', $teacher->gender) == 'F' ? 'selected' : '' }}>Femme</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}" class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $teacher->email) }}" class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Statut</label>
                        <select name="status" required class="w-full rounded-xl border-slate-200 text-sm py-2 px-3.5 shadow-sm text-slate-700">
                            <option value="actif" {{ old('status', $teacher->status) == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('status', $teacher->status) == 'inactif' ? 'selected' : '' }}>Inactif</option>
                            <option value="congé" {{ old('status', $teacher->status) == 'congé' ? 'selected' : '' }}>Congé</option>
                        </select>
                    </div>
                </div>

                <fieldset>
                    <legend class="mb-2 text-sm font-medium text-slate-700">Matière(s)</legend>
                    @if($subjects->isEmpty())
                        <p class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500">Aucune matière disponible pour l’année scolaire de cet enseignant.</p>
                    @else
                        <div class="grid grid-cols-1 gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($subjects as $subject)
                                <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" @checked(in_array($subject->id, old('subject_ids', $teacher->declaredSubjects->pluck('id')->all()))) class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
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
                        <p class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500">Aucune classe disponible pour l’année scolaire de cet enseignant.</p>
                    @else
                        <div class="grid grid-cols-1 gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($classes as $class)
                                <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50">
                                    <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" @checked(in_array($class->id, old('class_ids', $teacher->assignedClasses->pluck('id')->all()))) class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <span>{{ $class->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </fieldset>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('teachers.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition">
                        Annuler
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 shadow-sm transition">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
