{{-- <x-ui.bottom-nav active="swipe" style="neon" /> style: neon | flat | 3d --}}
@props(['active' => 'swipe', 'style' => 'neon'])
@php
    // href は実ルートに差し替える（例: route('swipe')）
    $items = [
        ['id' => 'swipe',  'icon' => 'home',   'label' => 'SWIPE',  'href' => '#'],
        ['id' => 'search', 'icon' => 'search', 'label' => 'SEARCH', 'href' => '#'],
        ['id' => 'likes',  'icon' => 'likes',  'label' => 'LIKES',  'href' => '#'],
        ['id' => 'talk',   'icon' => 'talk',   'label' => 'TALK',   'href' => '#'],
        ['id' => 'mypage', 'icon' => 'mypage', 'label' => 'MY PAGE','href' => '#'],
    ];
@endphp
<nav data-bottom-nav data-nav-style="{{ $style }}"
     {{ $attributes->merge(['class' => 'fixed bottom-0 left-0 w-full z-50 pb-safe bg-deep-purple/30 backdrop-blur-md border-t border-line-accent/40 shadow-footer']) }}>
    <div class="flex justify-around items-center pt-3 pb-2 px-2 max-w-[var(--max-content-width)] mx-auto">
        @foreach($items as $item)
            <a href="{{ $item['href'] }}"
               class="nav-item flex flex-col items-center p-2 transition-all duration-300 {{ $active === $item['id'] ? 'is-active' : '' }}">
                <span class="nav-icon-wrap w-12 h-12 rounded-full flex items-center justify-center mb-0.5 transition-all">
                    <x-ui.icon :name="$item['icon']" class="nav-icon text-[28px] transition-all" />
                </span>
                <span class="app-title text-[9px] font-bold tracking-wider">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
