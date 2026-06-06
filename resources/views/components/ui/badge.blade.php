{{-- <x-ui.badge variant="gold">ボーナス ￥120,000</x-ui.badge> --}}
@props(['variant' => 'solid']) {{-- solid | grad | gold --}}
@php
    $base = 'inline-flex items-center gap-1 px-4 py-2 rounded-full font-bold text-sm';
    $tone = match($variant) {
        'grad' => 'bg-gradient-to-r from-accent-grad-from to-accent-grad-to text-on-accent-strong shadow-badge-3d',
        'gold' => 'bg-gradient-to-r from-gold-from to-gold-to text-[#111] shadow-gold-3d border border-line-accent/40',
        default => 'bg-accent text-on-accent shadow-badge-3d',
    };
@endphp
<span {{ $attributes->merge(['class' => $base.' '.$tone]) }}>{{ $slot }}</span>
