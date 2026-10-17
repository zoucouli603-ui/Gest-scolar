@props(['variant' => 'light', 'size' => 'md', 'tagline' => false])
@php
    $mark = ['sm' => 'h-8 w-8', 'md' => 'h-10 w-10', 'lg' => 'h-14 w-14'][$size] ?? 'h-10 w-10';
    $text = ['sm' => 'text-lg', 'md' => 'text-xl', 'lg' => 'text-3xl'][$size] ?? 'text-xl';
    $scol = $variant === 'dark' ? 'text-white' : 'text-[#1E1B6B]';
    $sub = $variant === 'dark' ? 'text-blue-200' : 'text-slate-500';
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <x-brand-mark :class="$mark.' shrink-0'" />
    <span class="leading-none" style="font-family: 'Poppins', 'Inter', ui-sans-serif, system-ui, sans-serif">
        <span class="block font-extrabold tracking-tight {{ $text }}"><span class="text-[#F5B800]">YELEN</span><span class="{{ $scol }}">SCOL</span></span>
        @if ($tagline)
            <span class="mt-1 block text-[10px] font-medium uppercase tracking-[0.18em] {{ $sub }}">La lumière du savoir</span>
        @endif
    </span>
</span>
