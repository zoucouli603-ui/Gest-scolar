<x-app-layout>
    <div class="mx-auto max-w-2xl space-y-6 p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Modifier la classe</h1>
                <p class="mt-1 text-sm text-slate-500">Mettez à jour son cycle et ses frais mensuels.</p>
            </div>
            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $classe->schoolYear?->name }}</span>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('classes.update', $classe) }}" method="POST" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            <div>
                <label for="cycle" class="mb-1 block text-sm font-semibold text-slate-700">Cycle scolaire</label>
                <select id="cycle" name="cycle" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle }}" @selected(old('cycle', $classe->cycle) === $cycle)>{{ $cycle }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nom de la classe</label>
                <input id="name" name="name" value="{{ old('name', $classe->name) }}" required maxlength="255" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="monthly_fee" class="mb-1 block text-sm font-medium text-slate-700">Frais mensuels de scolarité ({{ $currency ?? 'FCFA' }})</label>
                <input id="monthly_fee" type="number" name="monthly_fee" min="0" step="1" value="{{ old('monthly_fee', (int) $classe->monthly_fee) }}" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <a href="{{ route('classes.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"><x-icon name="x" /> Annuler</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700"><x-icon name="check" /> Enregistrer</button>
            </div>
        </form>
    </div>
</x-app-layout>
