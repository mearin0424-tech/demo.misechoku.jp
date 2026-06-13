{{-- <x-ui.menu-card icon="settings" sub="ACCOUNT" title="アカウント設定" href="..."/> --}}
@props(['href' => '#', 'icon' => null, 'title' => '', 'sub' => null])
<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'group flex items-center justify-between gap-3 p-4 rounded-panel border border-line-accent/40 bg-gradient-to-br from-surface-from to-base shadow-card-3d transition-all duration-300']) }}>
    <span class="flex items-center gap-3">
        @isset($icon)
            {{-- 左アイコン：枠なし・フラット（mauve light）。サイズは大きめに取って視認性確保。 --}}
            <x-ui.icon :name="$icon" class="text-[22px] text-accent-text shrink-0 w-9 text-center" />
        @endisset
        <span class="min-w-0">
            @isset($sub)<span class="block text-[9px] font-bold tracking-[0.1em] uppercase text-text-sub">{{ $sub }}</span>@endisset
            <span class="block app-title text-[15px] tracking-wide text-text-main">{{ $title }}</span>
        </span>
    </span>
    {{-- 右シェブロン：枠なし・フラット --}}
    <x-ui.icon name="forward" class="text-text-sub group-hover:text-accent-text group-hover:translate-x-0.5 transition-all text-base shrink-0" />
</a>
