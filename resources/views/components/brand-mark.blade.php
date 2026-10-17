@props(['class' => 'h-10 w-10'])
@php
    $rays = [-160, -125, -90, -55, -20];
    $pts = collect($rays)->map(fn ($a) => [
        'a' => $a,
        'x1' => round(32 + cos(deg2rad($a)) * 9.5, 2), 'y1' => round(21 + sin(deg2rad($a)) * 9.5, 2),
        'x2' => round(32 + cos(deg2rad($a)) * 14, 2), 'y2' => round(21 + sin(deg2rad($a)) * 14, 2),
        'nx' => round(32 + cos(deg2rad($a)) * 17.5, 2), 'ny' => round(21 + sin(deg2rad($a)) * 17.5, 2),
    ]);
    $poly = $pts->map(fn ($p) => $p['nx'].','.$p['ny'])->implode(' ');
@endphp
<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Logo YELENSCOL" {{ $attributes->merge(['class' => $class]) }}>
    <polyline points="{{ $poly }}" stroke="#F5B800" stroke-opacity=".45" stroke-width="1" stroke-linejoin="round"/>
    @foreach ($pts as $p)
        <line x1="{{ $p['x1'] }}" y1="{{ $p['y1'] }}" x2="{{ $p['x2'] }}" y2="{{ $p['y2'] }}" stroke="#F5B800" stroke-width="2.4" stroke-linecap="round"/>
        <circle cx="{{ $p['nx'] }}" cy="{{ $p['ny'] }}" r="1.9" fill="#F5B800"/>
    @endforeach
    <circle cx="32" cy="21" r="6.5" fill="#F5B800"/>
    <circle cx="32" cy="21" r="6.5" fill="url(#ys-glow)"/>
    <path d="M32 55C25.5 50.5 15.5 49.5 6 51.5V32.5C15.5 30.5 25.5 31.5 32 36Z" fill="#312E81"/>
    <path d="M32 55C38.5 50.5 48.5 49.5 58 51.5V32.5C48.5 30.5 38.5 31.5 32 36Z" fill="#1E1B6B"/>
    <path d="M10 37c6-1 12 0 17 2.6M10 42c6-1 12 0 17 2.6M54 37c-6-1-12 0-17 2.6M54 42c-6-1-12 0-17 2.6" stroke="#fff" stroke-opacity=".35" stroke-width="1.3" stroke-linecap="round"/>
    <path d="M32 36v19" stroke="#F5B800" stroke-width="1.6" stroke-linecap="round"/>
    <path d="M4 56.5C14 54 24 55.5 32 59c8-3.5 18-5 28-2.500" stroke="#F5B800" stroke-width="1.6" stroke-linecap="round"/>
    <defs>
        <radialGradient id="ys-glow" cx=".4" cy=".35" r=".8"><stop stop-color="#FFE58A"/><stop offset="1" stop-color="#F5B800" stop-opacity="0"/></radialGradient>
    </defs>
</svg>
