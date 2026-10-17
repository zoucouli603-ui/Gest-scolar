@props(['school', 'fallback' => 'GS'])

@if ($school?->logo_path)
    <img src="{{ url('storage/' . ltrim($school->logo_path, '/')) }}" alt="Logo {{ $school->name }}"
         {{ $attributes->merge(['class' => 'object-contain bg-white']) }}>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center justify-center bg-blue-600 font-bold text-white']) }}>{{ $fallback }}</div>
@endif
