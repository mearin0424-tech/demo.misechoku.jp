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
        // キャスト→お店に切り替え: cast/shopprofileview/{id} は shop/castprofileview/{id} へ、それ以外は cast/ → shop/
        if (preg_match('#^cast/shopprofileview/(\d+)$#', $path, $m)) {
            $shopUrl = '/shop/castprofileview/' . $m[1];
        } else {
            $shopUrl = '/' . preg_replace('#^cast/#', 'shop/', $path);
        }
    } elseif (request()->is('shop/*')) {
        if (preg_match('#^shop/(recruits|profile/store)#', $path)) {
            $castUrl = '/cast/home';
        } elseif (preg_match('#^shop/castprofileview/(\d+)$#', $path, $m)) {
            // お店→キャストに切り替え: shop/castprofileview/{id} は cast/shopprofileview/{id} へ
            $castUrl = '/cast/shopprofileview/' . $m[1];
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
        @endif
    </div>

    {{-- 中央：デモ用キャスト／お店スイッチ（ピル型トグル） --}}
    <div class="header-center-title">
        <nav class="demo-pill-switch" role="tablist" aria-label="デモモード切り替え">
            <a href="{{ $castUrl }}" class="demo-pill-switch__segment {{ $isCast ? 'is-active' : '' }}" role="tab" aria-selected="{{ $isCast ? 'true' : 'false' }}">キャスト</a>
            <a href="{{ $shopUrl }}" class="demo-pill-switch__segment {{ !$isCast ? 'is-active' : '' }}" role="tab" aria-selected="{{ !$isCast ? 'true' : 'false' }}">お店</a>
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