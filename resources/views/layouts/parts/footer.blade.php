@php
    $pageId = $pageId ?? (explode('.', Route::currentRouteName())[1] ?? 'home');
    $hideFabPages = ['message', 'connection'];
    $prefix = 'shop';
@endphp

{{-- ボトムナビゲーション --}}
<nav id="bottom-nav">
    <a href="{{ route($prefix . '.home') }}" class="nav-item {{ $pageId === 'home' ? 'active' : '' }}"><i class="fas fa-home"></i><span>ホーム</span></a>
    <a href="{{ route($prefix .'.search.index') }}" class="nav-item {{ $pageId === 'search' ? 'active' : '' }}"><i class="fas fa-search"></i><span>さがす</span></a>
    <a href="{{ route($prefix .'.interaction.index') }}" class="nav-item {{ $pageId === 'connection' ? 'active' : '' }}"><i class="fas fa-users"></i><span>つながり</span></a>
    <a href="{{ route($prefix .'.talk.index') }}" class="nav-item {{ $pageId === 'message' ? 'active' : '' }}"><i class="fas fa-comment-dots"></i><span>メッセージ</span></a>
    <a href="{{ route($prefix .'.mypage.index') }}" class="nav-item {{ $pageId === 'mypage' ? 'active' : '' }}"><i class="fas fa-user"></i><span>マイページ</span></a>
</nav>

{{-- ポップオーバー類 --}}
@include('layouts.parts.header_popover.notification')
@include('layouts.parts.header_popover.task')