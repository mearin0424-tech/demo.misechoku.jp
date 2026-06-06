{{-- <x-ui.button variant="grad" icon="like">応募する</x-ui.button> --}}
@props([
    'variant' => 'solid', // solid | grad
    'as'      => 'button', // button | a
    'href'    => null,
    'type'    => 'button',
    'icon'    => null,     // x-ui.icon の name
])
@php
    $base = 'inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-bold shadow-btn-3d active:translate-y-1.5 active:shadow-btn-3d-active transition-all duration-300';
    // ↓ 両分岐とも完全リテラルなので purge されない
    $tone = $variant === 'grad'
        ? 'bg-gradient-to-r from-accent-grad-from to-accent-grad-to text-on-accent-strong'
        : 'bg-accent text-on-accent';
    $tag = $as === 'a' ? 'a' : 'button';
@endphp
<{{ $tag }}
    @if($tag === 'a') href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $base.' '.$tone]) }}>
    @isset($icon)<x-ui.icon :name="$icon" class="text-lg" />@endisset
    {{ $slot }}
</{{ $tag }}>
