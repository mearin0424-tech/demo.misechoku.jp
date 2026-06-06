{{-- <x-ui.input tone="dark" placeholder="メッセージを入力" /> tone: dark | light --}}
@props(['tone' => 'dark', 'type' => 'text'])
@php
    $shape = 'w-full px-4 py-3 rounded-panel transition-all duration-300 outline-none';
    $skin = $tone === 'light'
        ? 'bg-input-light text-input-light-text placeholder-gray-500 shadow-input-light border border-transparent'
        : 'bg-accent/10 text-text-main placeholder-gray-400 shadow-input-dark border border-line-accent/40';
@endphp
<input type="{{ $type }}" {{ $attributes->merge(['class' => $shape.' '.$skin]) }} />
