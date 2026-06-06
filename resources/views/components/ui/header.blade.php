{{-- <x-ui.header title="Club Luminous" /> スクロールでグラス化（behaviors.js） --}}
@props(['title' => '', 'back' => true, 'backHref' => null, 'share' => true])
<header data-scroll-reveal
    {{ $attributes->merge(['class' => 'absolute top-0 left-0 w-full z-40 transition-all duration-300 pt-safe']) }}>
    <div class="flex justify-between items-center px-4 py-3">
        @if($back)
            <a href="{{ $backHref ?? 'javascript:history.back()' }}"
               class="w-10 h-10 rounded-full flex items-center justify-center backdrop-blur-md bg-black/50 text-white border border-white/20 shadow-md hover:scale-105 active:scale-95 transition">
                <x-ui.icon name="back" class="text-xl" />
            </a>
        @else <span class="w-10 h-10"></span> @endif

        <div data-header-title class="app-title font-bold tracking-widest text-[15px] text-white">{{ $title }}</div>

        @if($share)
            <button type="button"
                class="w-10 h-10 rounded-full flex items-center justify-center backdrop-blur-md bg-black/50 text-white border border-white/20 shadow-md hover:scale-105 active:scale-95 transition">
                <x-ui.icon name="share" class="text-lg" />
            </button>
        @else <span class="w-10 h-10"></span> @endif
    </div>
</header>
