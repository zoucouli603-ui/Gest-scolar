<x-app-layout>
    <div class="module-page mx-auto max-w-3xl space-y-5 p-4 md:p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Modifier les coordonnées du parent</h1>
                <p class="mt-1 text-sm text-slate-500">La mise à jour s’appliquera aux élèves liés à ce parent pour {{ $currentYear->name }}.</p>
            </div>
            <a href="{{ route('parents.index') }}" class="shrink-0 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Retour</a>
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

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form action="{{ route('parents.update', request()->route('parentKey')) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="parent_name" class="mb-1 block text-sm font-medium text-slate-700">Nom du parent / tuteur</label>
                        <input id="parent_name" type="text" name="parent_name" value="{{ old('parent_name', $parent['name']) }}" required maxlength="255" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="parent_phone" class="mb-1 block text-sm font-medium text-slate-700">Téléphone</label>
                        <input id="parent_phone" type="text" name="parent_phone" value="{{ old('parent_phone', $parent['phone']) }}" required maxlength="255" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <h2 class="text-sm font-semibold text-blue-900">Élèves rattachés ({{ $parent['children']->count() }})</h2>
                    <ul class="mt-2 divide-y divide-blue-100">
                        @foreach($parent['children'] as $child)
                            <li class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
                                <span class="font-medium text-slate-800">{{ $child->first_name }} {{ $child->last_name }}</span>
                                <span class="text-xs text-slate-600">{{ $child->registration_number }} · {{ $child->classRoom?->cycle ? $child->classRoom->cycle.' — ' : '' }}{{ $child->classRoom?->name ?? $child->class_name }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-xs text-blue-800">Les coordonnées de tous ces élèves seront mises à jour ensemble.</p>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <a href="{{ route('parents.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Annuler</a>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Enregistrer</button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
