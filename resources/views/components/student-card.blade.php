@props(['student'])

@php
    $className = $student->class_name ?: ($student->classRoom?->name ?? '');
    $normalized = \Illuminate\Support\Str::of($className)->ascii()->lower()->toString();
    $cycle = \Illuminate\Support\Str::of($student->classRoom?->cycle ?? '')->ascii()->lower()->toString();
    $isSecondCycle = str_contains($cycle, '2') || str_contains($cycle, 'secondaire');
    $isLycee = str_contains($cycle, 'lyc');
    $isNinth = preg_match('/^\s*9(?!\d)/', $normalized) === 1 && ($isSecondCycle || $cycle === '');
    $isTerminal = str_contains($normalized, 'terminal')
        || preg_match('/^\s*T[A-Z]{1,6}\d*\s*$/', $className) === 1;
    $needsExamFields = $isNinth || ($isTerminal && ($isLycee || $cycle === ''));
    $showMatriculeField = $isTerminal && $isLycee;
    $photo = $student->photo_path ? url('storage/' . ltrim($student->photo_path, '/')) : null;
    $initials = mb_strtoupper(mb_substr($student->first_name ?? '', 0, 1) . mb_substr($student->last_name ?? '', 0, 1));
    $schoolModel = $activeSchool ?? auth()->user()?->school;
    $school = $student->school_name ?: ($schoolModel?->name ?? '—');
    $gender = match ($student->gender) { 'M' => 'Masculin', 'F' => 'Féminin', default => $student->gender ?: '—' };
    $birth = $student->birth_date ? \Illuminate\Support\Carbon::parse($student->birth_date)->format('d/m/Y') : '—';
    $card = array_merge(\App\Models\School::CARD_DEFAULTS, array_filter((array) ($schoolModel?->card_settings ?? []), fn ($v) => $v !== null && $v !== ''));
@endphp

<div {{ $attributes->merge(['class' => 'student-card max-w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg shadow-slate-200/70 print:shadow-none']) }}
     style="width:34rem;print-color-adjust: exact; -webkit-print-color-adjust: exact;">

    <div class="flex h-1.5">
        <div class="flex-1" style="background:#16a34a"></div>
        <div class="flex-1" style="background:#facc15"></div>
        <div class="flex-1" style="background:#dc2626"></div>
    </div>

    <div class="flex items-center gap-3 px-5 py-2.5" style="background:{{ $card['bg_color'] }};color:{{ $card['text_color'] }}">
        <div class="shrink-0 overflow-hidden rounded-lg bg-white" style="width:3rem;height:3rem">
            @if ($schoolModel?->logo_path)
                <img src="{{ url('storage/' . ltrim($schoolModel->logo_path, '/')) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;padding:2px">
            @else
                <div class="flex h-full w-full items-center justify-center text-sm font-bold text-indigo-600">GS</div>
            @endif
        </div>
        <div class="min-w-0 flex-1 text-center">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em]">{{ $card['line1'] }}</p>
            <p class="text-[9px] italic tracking-wider opacity-90">{{ $card['line2'] }}</p>
            <p class="mt-0.5 text-[9px] uppercase tracking-wider opacity-80">{{ $card['line3'] }}</p>
            <p class="mt-0.5 text-base font-extrabold uppercase tracking-[0.2em]">{{ $card['title'] }}</p>
        </div>
        <div style="width:3rem" class="shrink-0"></div>
    </div>

    <div class="flex gap-5 px-5 py-4">
        <div class="shrink-0">
            <div class="shrink-0 overflow-hidden rounded-full border-4 border-white bg-slate-100" style="width:8rem;height:8rem;box-shadow:0 0 0 2px #c7d2fe,0 4px 10px rgba(0,0,0,.15)">
                @if ($photo)
                    <img src="{{ $photo }}" alt="Photo de {{ $student->first_name }}" class="object-cover" style="width:100%;height:100%;object-fit:cover">
                @else
                    <div class="flex h-full w-full items-center justify-center text-4xl font-bold text-indigo-400">{{ $initials }}</div>
                @endif
            </div>
        </div>

        <div class="min-w-0 flex-1">
            <dl class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                <div>
                    <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Nom</dt>
                    <dd class="truncate text-sm font-extrabold uppercase text-slate-800">{{ $student->last_name }}</dd>
                </div>
                <div>
                    <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Prénom</dt>
                    <dd class="truncate text-sm font-bold text-slate-800">{{ $student->first_name }}</dd>
                </div>
                <div>
                    <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Classe</dt>
                    <dd class="truncate font-semibold text-slate-800">{{ $className ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Année scolaire</dt>
                    <dd class="font-semibold text-slate-800">{{ $student->schoolYear?->name ?? '—' }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">École</dt>
                    <dd class="truncate font-semibold text-slate-800">{{ $school }}</dd>
                </div>
                <div>
                    <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Sexe</dt>
                    <dd class="font-semibold text-slate-800">{{ $gender }}</dd>
                </div>
                <div>
                    <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Né(e) le</dt>
                    <dd class="font-semibold text-slate-800">{{ $birth }}</dd>
                </div>
                <div>
                    <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Lieu de naissance</dt>
                    <dd class="truncate font-semibold text-slate-800">{{ $student->birth_place ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Parent / Tuteur</dt>
                    <dd class="truncate font-semibold text-slate-800">{{ $student->parent_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Téléphone</dt>
                    <dd class="truncate font-semibold text-slate-800">{{ $student->parent_phone ?: '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    @if ($needsExamFields)
        <div class="px-5 pb-4">
            <div class="grid gap-3 {{ $showMatriculeField ? 'grid-cols-2' : 'grid-cols-1' }}" style="max-width: {{ $showMatriculeField ? '70%' : '50%' }};">
                <label class="block">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">N° Place</span>
                    <input type="text" placeholder="………………………"
                           class="mt-0.5 w-full rounded-md border border-dashed border-slate-400 bg-transparent px-2 py-1.5 text-sm font-bold text-slate-800 placeholder-slate-300 focus:border-indigo-500 focus:ring-0 print:placeholder-transparent">
                </label>
                @if ($showMatriculeField)
                    <label class="block">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">N° Matricule</span>
                        <input type="text" placeholder="………………………"
                               class="mt-0.5 w-full rounded-md border border-dashed border-slate-400 bg-transparent px-2 py-1.5 text-sm font-bold text-slate-800 placeholder-slate-300 focus:border-indigo-500 focus:ring-0 print:placeholder-transparent">
                    </label>
                @endif
            </div>
        </div>
    @endif

    <div class="flex h-1.5">
        <div class="flex-1" style="background:#16a34a"></div>
        <div class="flex-1" style="background:#facc15"></div>
        <div class="flex-1" style="background:#dc2626"></div>
    </div>
</div>
