@php
    $routeName = Route::currentRouteName();
    $pageId = $pageId ?? (explode('.', $routeName)[1] ?? 'home');

    // ロゴを表示する主要画面の判定（ログイン画面など一部は戻るボタンを非表示）
    $isMainPage = request()->is('*/home', '*/search', '*/interaction', '*/talk', '*/mypage');
    $isLoginPage = request()->routeIs('login.demo');
    $showBackButton = !$isMainPage && !$isLoginPage;

    $engTitles = [
        'home'       => 'HOME',
        'login'      => 'LOGIN',
        'search'     => 'SEARCH',
        'mypage'     => 'MY PAGE',
        'talk'       => 'TALK',
        'interaction'=> 'CONNECTION',
        'manage'     => 'MANAGEMENT',
        'register'   => 'ENTRY',
    ];
    $currentEngTitle = $engTitles[$pageId] ?? '-';

    $isCast = request()->is('cast/*');
@endphp

<header id="global-header">
    {{-- 左側：戻るボタン または ロゴ --}}
    <div class="header-left">
        @if($showBackButton)
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif
    </div>

    {{-- 中央：ページタイトル表示 --}}
    <div class="header-center-title">
        <span class="header-title-main">
            {{ $currentEngTitle }}
        </span>
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