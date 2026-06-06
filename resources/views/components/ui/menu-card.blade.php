{{-- <x-ui.menu-card icon="settings" sub="ACCOUNT" title="アカウント設定" href="..."/> --}}
@props(['href' => '#', 'icon' => null, 'title' => '', 'sub' => null])
<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'group flex items-center justify-between gap-3 p-4 rounded-panel border border-line-accent/40 bg-gradient-to-br from-surface-from to-base shadow-card-3d transition-all duration-300']) }}>
    <span class="flex items-center gap-3">
        @isset($icon)
            <span class="w-11 h-11 rounded-xl flex items-center justify-center bg-black/20 border border-line-accent/40 shadow-input-dark">
                <x-ui.icon :name="$icon" class="text-lg text-accent-text" />
            </span>
        @endisset
        <span class="min-w-0">
            @isset($sub)<span class="block text-[9px] font-bold tracking-[0.1em] uppercase text-text-sub">{{ $sub }}</span>@endisset
            <span class="block app-title text-[15px] tracking-wide text-text-main">{{ $title }}</span>
        </span>
    </span>
    <span class="shrink-0 w-8 h-8 rounded-full border border-white/20 flex items-center justify-center text-white/50 group-hover:text-white group-hover:border-white/50 transition-colors">
        <x-ui.icon name="forward" class="text-base" />
    </span>
</a>
