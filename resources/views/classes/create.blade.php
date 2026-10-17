<x-app-layout>
    <div class="p-6 max-w-2xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-800">Ajouter une nouvelle classe</h1>
            @if(isset($currentYear) && $currentYear)
                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Année : {{ $currentYear->name }}</span>
            @endif
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
            <form action="{{ route('classes.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="cycle" class="mb-1 block text-sm font-semibold text-slate-700">Cycle scolaire</label>
                    <select id="cycle" name="cycle" required class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Sélectionner le cycle avant la classe</option>
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle }}" @selected(old('cycle') === $cycle)>{{ $cycle }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Le cycle situe la classe et détermine le tarif annuel à appliquer.</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nom de la classe</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Ex: 7ème A, 8ème B..." required class="w-full rounded-xl border-slate-200 text-sm py-2.5 px-3.5 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                </div>

                <div>
                    <label for="monthly_fee" class="mb-1 block text-sm font-medium text-slate-700">Frais mensuels de scolarité ({{ $currency ?? 'FCFA' }})</label>
                    <input id="monthly_fee" type="number" name="monthly_fee" min="0" step="1" value="{{ old('monthly_fee', 0) }}" required class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-slate-500">Définissez le tarif mensuel de cette classe ; il pourra être ajusté plus tard.</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('classes.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition">
                        <x-icon name="x" /> Annuler
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 shadow-sm transition">
                        <x-icon name="check" /> Enregistrer la classe
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>