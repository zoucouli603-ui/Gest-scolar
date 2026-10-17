<x-app-layout>
    <div class="p-6 max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Ajouter une matière</h1>
            <p class="text-sm text-slate-500">Renseignez les informations de la matière pour l'année en cours.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-4 bg-rose-100 border border-rose-200 text-rose-700 rounded-xl text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('subjects.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nom de la matière</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Coefficient</label>
                    <input type="number" name="coefficient" step="0.5" min="0.5" max="10" value="{{ old('coefficient', 1) }}" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Année scolaire</label>
                    <input type="text" value="{{ $currentYear->name ?? '' }}" disabled class="w-full rounded-xl bg-slate-100 border-slate-200 text-sm text-slate-600 shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Description de la matière...">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('subjects.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition"><x-icon name="x" /> Annuler</a>
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl font-medium text-sm hover:bg-blue-700 transition shadow-sm"><x-icon name="check" /> Enregistrer</button>
            </div>
        </form>
    </div>
</x-app-layout>
