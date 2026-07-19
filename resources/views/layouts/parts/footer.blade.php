@php
    // キャストか店舗かをURLから自動判定
    $prefix = request()->is('cast/*') ? 'cast' : 'shop';

    // 現在のページ判定（クラス付与用）
    // ルート名に関わらず、URLのキーワードで判定するので確実です
    $isHome        = request()->is("*/home*");
    $isSearch      = request()->is("*/search*");
    $isTalk        = request()->is("*/talk*");
    $isMypage      = request()->is("*/mypage*");
@endphp

{{-- ボトムナビゲーション --}}
<nav id="bottom-nav" class="fixed-ui bottom-0 bg-base border-t border-line h-[var(--footer-height)] z-[1000] flex items-center justify-around px-2">
    {{-- ホーム（おすすめのスワイプ表示） --}}
    <a href="{{ route($prefix . '.home') }}" class="nav-item {{ $isHome ? 'active' : '' }}">
        <i class="fas fa-home"></i>
        <span>SWIPE</span>
    </a>

    {{-- さがす（統合検索画面へ） --}}
    <a href="{{ $prefix === 'cast' ? route('cast.search.index', ['tab' => 'list']) : route('shop.search.index') }}" class="nav-item {{ $isSearch ? 'active' : '' }}">
        <i class="fas fa-search"></i>
        <span>SEARCH</span>
    </a>

    {{-- メッセージ (Talk) --}}
    <a href="{{ route($prefix .'.talk.index') }}" class="nav-item {{ $isTalk ? 'active' : '' }}">
        <i class="fas fa-comment-dots"></i>
        <span>TALK</span>
    </a>

    {{-- マイページ --}}
    <a href="{{ route($prefix .'.mypage.index') }}" class="nav-item {{ $isMypage ? 'active' : '' }}">
        <i class="fas fa-user"></i>
        <span>MY PAGE</span>
    </a>
</nav>

{{-- 大事なポップオーバー類の読み込みは保持 --}}
@include('layouts.parts.header_popover.notification')
@include('layouts.parts.header_popover.task')