@php
    $routeName = Route::currentRouteName();
    // ページIDの取得ロジックを維持
    $pageId = $pageId ?? (explode('.', $routeName)[1] ?? 'home');

    $engTitles = [
        'home'       => 'HOME',
        'search'     => 'SEARCH',
        'mypage'     => 'My PAGE',
        'message'    => 'TALK',
        'talk'       => 'TALK', // talkでもmessageでも対応できるよう追加
        'review'     => 'REVIEW',
        'connection' => 'CONNECTION',
        'interaction'=> 'CONNECTION', // 呼称の揺れに対応
        'manage'     => 'MANAGEMENT'
    ];
    $currentEngTitle = $engTitles[$pageId] ?? '-';

    /**
     * 【修正ロジック】ロゴを表示する条件
     * 1. $footerPages（主要5画面）に含まれている
     * 2. かつ、詳細画面（room等）ではない（URLが /talk や /search 等でピッタリ終わる）
     */
    $footerPages = ['home', 'search', 'connection', 'interaction', 'message', 'talk', 'mypage'];
    
    // 正確に「主要5画面」のトップであるか判定
    $isMainPage = request()->is('*/home', '*/search', '*/interaction', '*/talk', '*/mypage');
    $showBackButton = !$isMainPage;
@endphp

{{-- 
    app.cssの --max-content-width (600px) と --header-height (75px) を使用 
    fixed-ui クラスは既存の centralizing ロジックがあるものと想定
--}}
<header id="global-header" class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-[var(--max-content-width)] h-[var(--header-height)] bg-[#220a0a] border-b border-[#4d1a1a] z-[1000] flex items-center px-4">
    
    {{-- 左側：戻るボタン または ロゴ --}}
    <div class="w-12 flex items-center">
        @if($showBackButton)
            {{-- ホワイトの戻るボタン --}}
            <a href="javascript:history.back()" class="text-white no-underline active:opacity-50 transition-opacity">
                <i class="fas fa-chevron-left text-xl"></i>
            </a>
        @else
            {{-- ロゴ表示：遷移先は共通のhomeへ --}}
            <a href="{{ route(request()->is('cast/*') ? 'cast.home' : 'shop.home') }}" class="flex items-center">
                <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="h-8 w-auto object-contain">
            </a>
        @endif
    </div>

    {{-- 中央：ページタイトル（ロゴ表示ページ以外で表示） --}}
    <div class="flex-1 text-center">
        @if($showBackButton)
            <div class="text-lg font-bold text-white mb-0 serif-font">
                @yield('header_title', $currentEngTitle)
            </div>
        @endif
    </div>

    {{-- 右側：タスク / 通知 / ハンバーガーメニュー --}}
    <div class="flex items-center gap-3">
        {{-- タスクボタン --}}
        <button id="btn-header-task" class="w-8 h-8 rounded-full bg-[#3d2a2a] flex items-center justify-center text-white relative">
            <i class="fas fa-check-circle text-xs"></i>
            @if(isset($todoList) && count($todoList) > 0)
                <span class="absolute -top-1 -right-1 bg-[#b91c1c] text-[9px] min-w-[16px] h-4 flex items-center justify-center rounded-full font-bold px-1 border border-[#220a0a]">
                    {{ count($todoList) }}
                </span>
            @endif
        </button>

        {{-- 通知ボタン --}}
        <button id="btn-header-notification" class="w-8 h-8 rounded-full bg-[#3d2a2a] flex items-center justify-center text-white relative">
            <i class="fas fa-bell text-xs"></i>
            @if(isset($unreadNewsCount) && $unreadNewsCount > 0)
                <span class="absolute -top-1 -right-1 bg-[#b91c1c] text-[9px] min-w-[16px] h-4 flex items-center justify-center rounded-full font-bold px-1 border border-[#220a0a]">
                    {{ $unreadNewsCount }}
                </span>
            @endif
        </button>

        {{-- メニューボタン --}}
        <button id="btn-header-menu" class="w-8 h-8 rounded-full bg-[#3d2a2a] flex items-center justify-center text-white">
            <i class="fas fa-bars text-xs"></i>
        </button>
    </div>
</header>