@php
    $routeName = Route::currentRouteName();
    $pageId = $pageId ?? (explode('.', $routeName)[1] ?? 'home');

    // ロゴを表示する主要画面の判定
    $isMainPage = request()->is('*/home', '*/search', '*/interaction', '*/talk', '*/mypage');
    $showBackButton = !$isMainPage;

    $engTitles = [
        'home'       => 'HOME',
        'search'     => 'SEARCH',
        'mypage'     => 'MY PAGE',
        'talk'       => 'TALK',
        'interaction'=> 'CONNECTION',
        'manage'     => 'MANAGEMENT'
    ];
    $currentEngTitle = $engTitles[$pageId] ?? '-';
@endphp

<header id="global-header">
    {{-- 左側：戻るボタン または ロゴ --}}
    <div class="header-left">
        @if($showBackButton)
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-chevron-left"></i>
            </a>
        @else
            <a href="{{ route(request()->is('cast/*') ? 'cast.home' : 'shop.home') }}">
                <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="header-logo-img">
            </a>
        @endif
    </div>

    {{-- 中央：ページタイトル（戻るボタンがある時のみ表示） --}}
    <div class="header-center-title">
        @yield('header_title', $currentEngTitle)
    </div>

    {{-- 右側：タスク / 通知 / ハンバーガーメニュー --}}
    <div class="header-right">
        <button id="btn-header-task" class="header-icon-btn">
            <i class="fas fa-check-circle"></i>
            @if(isset($todoList) && count($todoList) > 0)
                <span class="badge-notify">{{ count($todoList) }}</span>
            @endif
        </button>

        <button id="btn-header-notification" class="header-icon-btn">
            <i class="fas fa-bell"></i>
            @if(isset($unreadNewsCount) && $unreadNewsCount > 0)
                <span class="badge-notify">{{ $unreadNewsCount }}</span>
            @endif
        </button>

        <button id="btn-header-menu" class="header-icon-btn">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>