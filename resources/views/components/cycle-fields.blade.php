@props(['selected' => [], 'academy' => null, 'cap' => null, 'inputClass' => 'w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500'])

@php($selected = \App\Models\School::normalizeCycles((array) $selected))
<div id="cycle-fields" class="sm:col-span-2 md:col-span-3 space-y-4">
    <fieldset>
        <legend class="mb-2 block text-sm font-medium text-slate-700">Cycles proposés par l’école *</legend>
        <p class="mb-3 text-xs text-slate-500">Le <strong>Lycée</strong> est exclusif : il ne peut pas être combiné avec Maternelle, 1er Cycle ou 2è Cycle.</p>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach(\App\Models\School::CYCLES as $cycle)
                <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 text-sm text-slate-700">
                    <input type="checkbox" name="cycles[]" value="{{ $cycle }}" data-cycle="{{ $cycle }}" @checked(in_array($cycle, $selected, true)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    {{ $cycle }}
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="grid gap-4 sm:grid-cols-2">
        <div data-academy-field>
            <label for="academy" class="mb-1 block text-sm font-medium text-slate-700">Académie d’enseignement *</label>
            <input id="academy" name="academy" value="{{ old('academy', $academy) }}" maxlength="150" class="{{ $inputClass }}">
        </div>
        <div data-cap-field>
            <label for="cap" class="mb-1 block text-sm font-medium text-slate-700">CAP (Centre d’Animation Pédagogique) *</label>
            <input id="cap" name="cap" value="{{ old('cap', $cap) }}" maxlength="150" class="{{ $inputClass }}">
        </div>
    </div>
</div>

<script>
    (() => {
        const root = document.getElementById('cycle-fields');
        const boxes = [...root.querySelectorAll('input[data-cycle]')];
        const lycee = boxes.find((b) => b.dataset.cycle === 'Lycée');
        const others = boxes.filter((b) => b !== lycee);
        const academy = root.querySelector('[data-academy-field]');
        const cap = root.querySelector('[data-cap-field]');
        const fundamental = boxes.filter((b) => ['1er Cycle', '2è Cycle'].includes(b.dataset.cycle));

        const refresh = () => {
            const hasFundamental = fundamental.some((b) => b.checked);
            academy.hidden = !(lycee.checked || hasFundamental);
            cap.hidden = !hasFundamental;
            academy.querySelector('input').required = !academy.hidden;
            cap.querySelector('input').required = !cap.hidden;
        };

        boxes.forEach((box) => box.addEventListener('change', () => {
            if (box === lycee && lycee.checked) others.forEach((b) => (b.checked = false));
            if (box !== lycee && box.checked) lycee.checked = false;
            refresh();
        }));
        refresh();
    })();
</script>
