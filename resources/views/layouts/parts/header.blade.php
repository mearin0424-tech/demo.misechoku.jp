@php
    $routeName = Route::currentRouteName();
    $pageId = $pageId ?? (explode('.', $routeName)[1] ?? 'home');

    // ロゴを表示する主要画面の判定
    $isMainPage = request()->is('*/home', '*/search', '*/interaction', '*/talk', '*/mypage');
    $showBackButton = !$isMainPage;

    $engTitles = [
        'home'       => 'HOME',
        'search'     => 'SEARCH',
        'mypage'     => 'My PAGE',
        'talk'       => 'TALK',
        'interaction'=> 'CONNECTION',
        'manage'     => 'MANAGEMENT'
    ];
    $currentEngTitle = $engTitles[$pageId] ?? '-';
@endphp

<header id="global-header" class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[var(--max-content-width)] h-[var(--header-height)] bg-[#220a0a] border-b border-[#4d1a1a] z-[1000] flex items-center px-4">
    
    {{-- 【左側スロット】(幅 80px 固定) --}}
    <div class="w-[80px] flex items-center justify-start">
        @if($showBackButton)
            {{-- 戻るボタン：サイズを少し抑えつつホワイトを維持 --}}
            <a href="javascript:history.back()" class="flex items-center text-white no-underline hover:opacity-70 transition-opacity py-2">
                <i class="fas fa-chevron-left text-xl"></i>
            </a>
        @else
            {{-- ロゴ：高さを h-5 (20px) に縮小してスマートに --}}
            <a href="{{ route(request()->is('cast/*') ? 'cast.home' : 'shop.home') }}" class="flex items-center">
                <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="h-5 w-auto object-contain">
            </a>
        @endif
    </div>

    {{-- 【中央スロット】 --}}
    <div class="flex-1 text-center">
        @if($showBackButton)
            {{-- タイトル：text-sm (14px) に落として他と統一 --}}
            <h1 class="text-white text-sm font-bold tracking-widest serif-font mb-0 opacity-90">
                @yield('header_title', $currentEngTitle)
            </h1>
        @endif
    </div>

    {{-- 【右側スロット】(幅 120px 固定) --}}
    <div class="w-[120px] flex items-center justify-end gap-2">
        {{-- タスク --}}
        <button class="w-8 h-8 rounded-full bg-[#3d2a2a] flex items-center justify-center text-white border border-[#4d1a1a] relative">
            <i class="fas fa-check-circle text-[12px]"></i>
            @if(isset($todoList) && count($todoList) > 0)
                <span class="absolute -top-1 -right-1 bg-[#b91c1c] text-[8px] min-w-[14px] h-3.5 flex items-center justify-center rounded-full font-bold px-1">
                    {{ count($todoList) }}
                </span>
            @endif
        </button>

        {{-- 通知 --}}
        <button class="w-8 h-8 rounded-full bg-[#3d2a2a] flex items-center justify-center text-white border border-[#4d1a1a] relative">
            <i class="fas fa-bell text-[12px]"></i>
            @if(isset($unreadNewsCount) && $unreadNewsCount > 0)
                <span class="absolute -top-1 -right-1 bg-[#b91c1c] text-[8px] min-w-[14px] h-3.5 flex items-center justify-center rounded-full font-bold px-1">
                    {{ $unreadNewsCount }}
                </span>
            @endif
        </button>

        {{-- メニュー --}}
        <button id="btn-header-menu" class="w-8 h-8 rounded-full bg-[#3d2a2a] flex items-center justify-center text-white border border-[#4d1a1a]">
            <i class="fas fa-bars text-[12px]"></i>
        </button>
    </div>
</header>