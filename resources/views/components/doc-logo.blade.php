@php($docSchool = $activeSchool ?? auth()->user()?->school)
@if ($docSchool?->logo_path)
    <img src="{{ url('storage/' . ltrim($docSchool->logo_path, '/')) }}" alt="Logo {{ $docSchool->name }}"
         {{ $attributes->merge(['style' => 'display:block;margin:0 auto 8px;height:64px;max-width:180px;object-fit:contain;print-color-adjust:exact;-webkit-print-color-adjust:exact']) }}>
@endif
