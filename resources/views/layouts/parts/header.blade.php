@php
    $routeName = Route::currentRouteName();
    $pageId = $pageId ?? (explode('.', $routeName)[1] ?? 'home');

    $engTitles = [
        'home'       => 'Discovery',
        'search'     => 'Search List',
        'mypage'     => 'Profile',
        'message'    => 'Chat List',
        'review'     => 'Review List',
        'connection' => 'Connections',
        'manage'     => 'Management'
    ];
    $currentEngTitle = $engTitles[$pageId] ?? 'Dashboard';

    $footerPages = ['home', 'search', 'connection', 'message', 'mypage'];
    $showBackButton = !in_array($pageId, $footerPages);
@endphp

<header id="global-header">
    {{-- 左側：戻るボタン または ロゴ --}}
    <div class="header-left">
        @if($showBackButton)
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-chevron-left"></i>
            </a>
        @else
            <a href="{{ route('cast.home') }}">
                <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="header-logo-img">
            </a>
        @endif
    </div>

    {{-- 中央：ページタイトル --}}
    <div class="header-center-title">
        {{ $currentEngTitle }}
    </div>

    {{-- 右側：タスク / 通知 / ハンバーガーメニュー --}}
    <div class="header-right">
        {{-- タスクボタン --}}
        <button id="btn-header-task" class="header-icon-btn">
            <i class="fas fa-check-circle"></i>
            @if(isset($todoList) && count($todoList) > 0)
                <span class="badge-notify">{{ count($todoList) }}</span>
            @endif
        </button>

        {{-- 通知ボタン --}}
        <button id="btn-header-notification" class="header-icon-btn">
            <i class="fas fa-bell"></i>
            @if(isset($unreadNewsCount) && $unreadNewsCount > 0)
                <span class="badge-notify">{{ $unreadNewsCount }}</span>
            @endif
        </button>

        {{-- サイドバー用ハンバーガーボタン (app.jsのbtn-header-menuに対応) --}}
        <button id="btn-header-menu" class="header-icon-btn">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>