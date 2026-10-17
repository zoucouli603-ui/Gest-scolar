<x-app-layout>
    <div class="p-6 max-w-4xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Matière : {{ $subject->name }}</h1>
                <p class="text-sm text-slate-500">Détails de la matière.</p>
            </div>
            <a href="{{ route('subjects.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition"><x-icon name="arrow-left" /> Retour</a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
                <div class="p-5 border-b md:border-b-0 md:border-r border-slate-200">
                    <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-2">Nom</p>
                    <p class="text-lg font-bold text-slate-900">{{ $subject->name }}</p>
                </div>
                <div class="p-5 border-b border-slate-200">
                    <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-2">Code</p>
                    <p class="text-lg font-bold text-blue-700">{{ $subject->code }}</p>
                </div>
                <div class="p-5 border-b md:border-b-0 md:border-r border-slate-200">
                    <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-2">Coefficient</p>
                    <p class="text-lg font-bold text-slate-900">{{ $subject->coefficient ?? 1 }}</p>
                </div>
                <div class="p-5 border-b border-slate-200">
                    <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-2">Année scolaire</p>
                    <p class="text-lg font-bold text-slate-900">{{ $subject->schoolYear->name ?? '—' }}</p>
                </div>
                <div class="p-5 md:col-span-2">
                    <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-2">Description</p>
                    <p class="text-slate-700 leading-relaxed">{{ $subject->description ?? 'Aucune description disponible.' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
