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

    // デモ用：キャスト／お店の切り替え用URL（現在のパスを他方のプレフィックスに変換）
    $path = request()->path();
    $isCast = request()->is('cast/*');
    $castUrl = '/cast/home';
    $shopUrl = '/shop/home';
    if (request()->is('cast/*')) {
        $shopUrl = '/' . preg_replace('#^cast/#', 'shop/', $path);
    } elseif (request()->is('shop/*')) {
        if (preg_match('#^shop/(recruits|profile/store)#', $path)) {
            $castUrl = '/cast/home';
        } else {
            $castUrl = '/' . preg_replace('#^shop/#', 'cast/', $path);
        }
    }
@endphp

<header id="global-header">
    {{-- 左側：戻るボタン または ロゴ --}}
    <div class="header-left">
        @if($showBackButton)
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-chevron-left"></i>
            </a>
        @else
            <a href="{{ route(request()->is('cast/*') ? 'cast.home' : 'shop.home') }}" class="header-demo-badge" aria-label="デモトップへ">
                <span class="header-demo-badge__text">demo</span>
            </a>
        @endif
    </div>

    {{-- 中央：デモ用キャスト／お店スイッチ（タイトルは一時非表示） --}}
    <div class="header-center-title">
        <nav class="demo-mode-switch" role="tablist" aria-label="デモモード切り替え">
            <span class="demo-mode-switch__label">表示中</span>
            <div class="demo-mode-switch__track">
                <a href="{{ $castUrl }}" class="demo-mode-switch__btn {{ $isCast ? 'is-active' : '' }}" role="tab" aria-selected="{{ $isCast ? 'true' : 'false' }}">
                    <span class="demo-mode-switch__btn-icon" aria-hidden="true"><i class="fas fa-user"></i></span>
                    <span class="demo-mode-switch__btn-text">キャスト</span>
                </a>
                <a href="{{ $shopUrl }}" class="demo-mode-switch__btn {{ !$isCast ? 'is-active' : '' }}" role="tab" aria-selected="{{ !$isCast ? 'true' : 'false' }}">
                    <span class="demo-mode-switch__btn-icon" aria-hidden="true"><i class="fas fa-store"></i></span>
                    <span class="demo-mode-switch__btn-text">お店</span>
                </a>
            </div>
        </nav>
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