@php
    $styles = [
        'success' => ['box' => 'border-emerald-200 bg-emerald-50 text-emerald-700', 'close' => 'text-emerald-500 hover:text-emerald-700', 'delay' => 4000],
        'error' => ['box' => 'border-rose-200 bg-rose-50 text-rose-700', 'close' => 'text-rose-500 hover:text-rose-700', 'delay' => 6000],
    ];
@endphp
@foreach ($styles as $type => $style)
    @if (session($type))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, {{ $style['delay'] }})" x-show="show" x-transition.duration.500ms role="alert"
             class="no-print flex items-center justify-between gap-3 rounded-xl border p-3 text-sm {{ $style['box'] }}">
            <span>{{ session($type) }}</span>
            <button type="button" @click="show = false" class="text-lg font-bold leading-none {{ $style['close'] }}" aria-label="Fermer">&times;</button>
        </div>
    @endif
@endforeach
