{{--
    バッジ。CTA とは違い「触れない・状態を表す小さなラベル」。
    シャドウは控えめ（flat / 弱いドロップシャドウのみ）。

    使い方:
        <x-ui.badge>採用ボーナス ¥120,000</x-ui.badge>
        <x-ui.badge variant="success">承認済み</x-ui.badge>
        <x-ui.badge variant="warning">承認待ち</x-ui.badge>
        <x-ui.badge variant="danger">却下</x-ui.badge>
        <x-ui.badge variant="outline">タグ</x-ui.badge>

    Props:
        - variant : 'solid' (既定 / mauve) | 'success' | 'warning' | 'danger' | 'info' | 'outline'
                    | 'gold' (※後方互換 / solid と同じ描画)
        - size    : 'sm' | 'md' (既定)
--}}
@props([
    'variant' => 'solid',
    'size'    => 'md',
])
@php
    $sizeClass = match($size) {
        'sm' => 'px-2.5 py-0.5 text-[10px]',
        default => 'px-3 py-1 text-[12px]',
    };

    $base = 'inline-flex items-center gap-1 rounded-full font-bold leading-none';

    $tone = match($variant) {
        'success' => 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/40',
        'warning' => 'bg-yellow-400/15 text-yellow-300 border border-yellow-400/40',
        'danger'  => 'bg-rose-500/15 text-rose-300 border border-rose-500/40',
        'info'    => 'bg-sky-500/15 text-sky-300 border border-sky-500/40',
        'outline' => 'bg-transparent text-text-main border border-line-accent/40',
        // 'gold' は後方互換 — 値は solid と同じ（旧 warm gold は撤去済み）
        'gold'    => 'bg-accent text-on-accent shadow-[0_2px_6px_rgba(0,0,0,0.35)]',
        default   => 'bg-accent text-on-accent shadow-[0_2px_6px_rgba(0,0,0,0.35)]',
    };
@endphp
<span {{ $attributes->merge(['class' => $base.' '.$sizeClass.' '.$tone]) }}>{{ $slot }}</span>
