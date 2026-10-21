<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-4 p-4 md:p-5">
        
        <!-- En-tête de la page -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Gestion des Années Scolaires</h1>
                <p class="text-sm text-slate-500">Configure et active l'année académique en cours.</p>
            </div>
        </div>

        <!-- Message Flash de Succès (Disparition auto) -->
        @if (session('success'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 4000)" 
                 x-show="show"
                 x-transition.duration.500ms
                 class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-100 p-3 text-sm text-emerald-700">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 font-bold text-lg leading-none">&times;</button>
            </div>
        @endif

        <!-- Message Flash d'Erreur (Disparition auto) -->
        @if (session('error'))
            <div x-data="{ show: true }" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-show="show"
                 x-transition.duration.500ms
                 class="flex items-center justify-between rounded-lg border border-rose-200 bg-rose-100 p-3 text-sm text-rose-700">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="text-rose-500 hover:text-rose-700 font-bold text-lg leading-none">&times;</button>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            
            <!-- Formulaire d'ajout d'une année scolaire -->
            <div class="h-fit rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-base font-bold text-slate-800">Ajouter une année</h2>

                @if ($errors->any())
                    <div x-data="{ show: true }" 
                         x-init="setTimeout(() => show = false, 5000)" 
                         x-show="show"
                         x-transition.duration.500ms
                         class="mb-3 flex items-center justify-between rounded-lg border border-rose-200 bg-rose-100 p-3 text-xs text-rose-700">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
                    </div>
                @endif

                <form action="{{ route('school-years.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Libellé</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Ex: 2026-2027" required class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 font-medium text-sm text-white shadow-sm transition hover:bg-blue-700">
                            Enregistrer l'année
                        </button>
                    </div>
                </form>

                @if($schoolYears->where('is_current', true)->isNotEmpty())
                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <h2 class="text-base font-bold text-slate-800">Clôturer l’année en cours</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Les classes, élèves, enseignants, matières et affectations seront reportés. Les notes, finances, emploi du temps et rapports resteront propres à l’ancienne année.
                        </p>
                        <form action="{{ route('school-years.close') }}" method="POST" class="mt-3 space-y-3" x-on:submit="if (!confirm('Clôturer l’année actuelle et créer la nouvelle année ?')) $event.preventDefault()">
                            @csrf
                            <input type="text" name="name" placeholder="Ex : 2027-2028" required class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <button type="submit" class="w-full rounded-lg bg-amber-600 px-4 py-2 font-medium text-sm text-white shadow-sm transition hover:bg-amber-700">
                                Clôturer et préparer la nouvelle année
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Tableau de la liste des années scolaires -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                <table class="w-full text-left text-sm border-collapse">
                    <thead class="bg-slate-50 text-slate-400 font-semibold text-xs uppercase border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3">Année Scolaire</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($schoolYears as $year)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $year->name }}</td>
                                <td class="px-4 py-3">
                                    @if($year->is_current)
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full uppercase">Active</span>
                                    @else
                                        <span class="px-3 py-1 bg-slate-100 text-slate-500 text-xs font-semibold rounded-full uppercase">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @unless($year->is_current)
                                            <form action="{{ route('school-years.set-current', $year->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-medium transition">
                                                    Définir active
                                                </button>
                                            </form>
                                        @endunless

                                        <form action="{{ route('school-years.destroy', $year->id) }}" method="POST" x-on:submit="if (!confirm('Êtes-vous sûr de vouloir supprimer cette année scolaire ?')) $event.preventDefault()" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Supprimer" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-10 text-center text-slate-400">
                                    Aucune année scolaire enregistrée pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>