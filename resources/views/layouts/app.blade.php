<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- ファビコン（データURIで404防止。色はアプリのゴールド・ダーク） --}}
    <link rel="icon" href="data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" fill="#190509"/><text x="16" y="22" font-size="18" text-anchor="middle" fill="#D4AF37">店</text></svg>') }}" type="image/svg+xml">
    <title>@yield('title'){{ isset($title) ? ' | ' . config('app.name', 'ミセチョク') : config('app.name', 'ミセチョク') }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Shippori+Mincho:wght@400;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-header.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/character-guide.css') }}">

    <script src="{{ asset('assets/js/app.js') }}" defer></script>

    @stack('styles')
</head>
    
<body class="@yield('body-class')">
    <div id="bg-layer"></div>
    <div id="menu-overlay" class="menu-overlay"></div>

    <div id="app">
        {{-- メインレイアウト部分を縦並びのFlexコンテナとして包む --}}
        <div class="main-layout-container flex-1 flex flex-col min-w-0">

            {{-- ヘッダー --}}
            @include('layouts.parts.header')

            {{-- @yield('guide_message') で各ページの設定内容を注入する --}}
            @include('layouts.parts.character-guide', ['guideMessage' => $__env->yieldContent('guide_message')])

            {{-- メインコンテンツ（プロフィール詳細は content-wrapper を使わず幅をアプリ全体に統一） --}}
            <main id="main-content">
                @if(request()->routeIs('cast.shopprofileview.show') || request()->routeIs('shop.castprofileview.show') || request()->routeIs('cast.mypage.index'))
                    @yield('content')
                @else
                    <div class="content-wrapper animate-fadeIn">
                        @yield('content')
                    </div>
                @endif
            </main>

            {{-- ボトムナビ（モバイル用固定） --}}
            @include('layouts.parts.footer')
        </div>
        {{-- サイドバー --}}
        @include('layouts.parts.sidebar')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('assets/js/character-guide.js') }}"></script>
    @stack('scripts')
</body>
</html>