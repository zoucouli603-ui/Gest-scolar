@props(['teacher'])

@php
    $schoolModel = $activeSchool ?? auth()->user()?->school;
    $subjects = $teacher->declaredSubjects->pluck('name')->join(', ') ?: ($teacher->specialty ?: '—');
    $initials = mb_strtoupper(mb_substr($teacher->first_name ?? '', 0, 1) . mb_substr($teacher->last_name ?? '', 0, 1));
    $qrSvg = (new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(
        new \BaconQrCode\Renderer\RendererStyle\RendererStyle(180, 1),
        new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
    )))->writeString((string) $teacher->qr_token);
    $qrSvg = preg_replace('/^<\?xml[^>]*\?>\s*/', '', $qrSvg);
@endphp

<div {{ $attributes->merge(['class' => 'teacher-badge overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg shadow-slate-200/70']) }}
     style="width:19rem;max-width:100%;print-color-adjust:exact;-webkit-print-color-adjust:exact;">
    <div class="flex h-1.5">
        <div class="flex-1" style="background:#16a34a"></div>
        <div class="flex-1" style="background:#facc15"></div>
        <div class="flex-1" style="background:#dc2626"></div>
    </div>

    <div class="flex items-center gap-2.5 px-4 py-2.5 text-white" style="background:linear-gradient(90deg,#3730a3,#2563eb)">
        <div class="shrink-0 overflow-hidden rounded-lg bg-white" style="width:2.5rem;height:2.5rem">
            @if ($schoolModel?->logo_path)
                <img src="{{ url('storage/' . ltrim($schoolModel->logo_path, '/')) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;padding:2px">
            @else
                <div class="flex h-full w-full items-center justify-center text-xs font-bold text-indigo-600">GS</div>
            @endif
        </div>
        <div class="min-w-0 flex-1 text-center">
            <p class="truncate text-[11px] font-bold uppercase tracking-wider">{{ $schoolModel?->name ?? 'École' }}</p>
            <p class="text-[9px] uppercase tracking-[0.2em] opacity-90">Badge Enseignant</p>
        </div>
    </div>

    <div class="flex flex-col items-center px-4 pt-4">
        <div class="overflow-hidden rounded-full border-4 border-white bg-slate-100" style="width:7rem;height:7rem;box-shadow:0 0 0 2px #c7d2fe,0 4px 10px rgba(0,0,0,.15)">
            @if ($teacher->photo_url)
                <img src="{{ $teacher->photo_url }}" alt="Photo" style="width:100%;height:100%;object-fit:cover">
            @else
                <div class="flex h-full w-full items-center justify-center text-3xl font-bold text-indigo-400">{{ $initials }}</div>
            @endif
        </div>
        <p class="mt-3 text-center text-base font-extrabold uppercase text-slate-800">{{ $teacher->last_name }}</p>
        <p class="text-center text-sm font-semibold text-slate-700">{{ $teacher->first_name }}</p>
        <p class="mt-1 rounded-full bg-indigo-50 px-3 py-0.5 text-center text-[11px] font-semibold text-indigo-700">{{ $subjects }}</p>
    </div>

    <dl class="grid grid-cols-2 gap-x-3 gap-y-1.5 px-5 pt-3 text-xs">
        <div>
            <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Téléphone</dt>
            <dd class="truncate font-semibold text-slate-800">{{ $teacher->phone ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Année scolaire</dt>
            <dd class="font-semibold text-slate-800">{{ $teacher->schoolYear?->name ?? '—' }}</dd>
        </div>
        <div class="col-span-2">
            <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Classes</dt>
            <dd class="truncate font-semibold text-slate-800">{{ $teacher->assignedClasses->pluck('name')->join(', ') ?: '—' }}</dd>
        </div>
    </dl>

    <div class="flex flex-col items-center px-4 py-3">
        <div style="width:7.5rem;height:7.5rem" class="[&>svg]:h-full [&>svg]:w-full">{!! $qrSvg !!}</div>
        <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">Scanner</p>
    </div>

    <div class="flex h-1.5">
        <div class="flex-1" style="background:#16a34a"></div>
        <div class="flex-1" style="background:#facc15"></div>
        <div class="flex-1" style="background:#dc2626"></div>
    </div>
</div>
