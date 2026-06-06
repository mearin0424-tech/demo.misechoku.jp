{{-- <x-ui.card>...</x-ui.card>  flat 指定でフラット（背景・影なし） --}}
@props(['flat' => false])
@php
    $base = 'rounded-card overflow-hidden border border-line-accent/40 transition-all duration-300';
    $surface = $flat
        ? 'bg-transparent'
        : 'bg-gradient-to-br from-surface-from to-base shadow-card-3d';
@endphp
<div {{ $attributes->merge(['class' => $base.' '.$surface]) }}>{{ $slot }}</div>
