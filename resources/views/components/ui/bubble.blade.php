{{-- <x-ui.bubble from="me">こんにちは</x-ui.bubble> from: me | other | system --}}
@props(['from' => 'other'])
@php
    $base = 'px-4 py-2.5 font-medium text-[13px] leading-relaxed w-fit max-w-[80%]';
    $skin = match($from) {
        'me'     => 'rounded-[20px] rounded-tr-sm bg-accent text-on-accent shadow-badge-3d ml-auto',
        'system' => 'rounded-[20px] mx-auto bg-gradient-to-br from-surface-from to-base border border-line-accent/40 shadow-card-3d text-text-main',
        default  => 'rounded-[20px] rounded-tl-sm bg-gradient-to-br from-surface-from to-base border border-line-accent/40 shadow-card-3d text-text-main',
    };
@endphp
<div {{ $attributes->merge(['class' => $base.' '.$skin]) }}>{{ $slot }}</div>
