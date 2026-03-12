<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- PWA --}}
    <meta name="theme-color" content="#190509">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ミセチョク">
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/pwa/icon-192.png') }}">
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
    
<body class="@yield('body-class')" data-notification-badge="{{ isset($unreadNewsCount) ? (int) $unreadNewsCount : 0 }}">
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

            {{-- ボトムナビ（モバイル用固定）※店舗 / キャスト画面のみ表示 --}}
            @if (request()->is('shop/*') || request()->is('cast/*'))
                @include('layouts.parts.footer')
            @endif
        </div>
        {{-- サイドバー --}}
        @include('layouts.parts.sidebar')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('assets/js/character-guide.js') }}"></script>
    <script src="{{ asset('assets/js/push-notification.js') }}"></script>
    @stack('scripts')
    {{-- PWA: Service Worker 登録 --}}
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
          navigator.serviceWorker.register('{{ asset("sw.js") }}', { scope: '/' })
            .then(function (reg) { /* 登録完了 */ })
            .catch(function () { /* 登録失敗時は無視 */ });
        });
      }
    </script>
    {{-- PWA: 手動インストール（スマホでインストールマークが出ない場合用） --}}
    <script>
      (function() {
        var deferredPrompt;
        var section = document.getElementById('pwa-install-section');
        var btn = document.getElementById('pwa-install-btn');
        if (!section || !btn) return;

        window.addEventListener('beforeinstallprompt', function(e) {
          e.preventDefault();
          deferredPrompt = e;
          section.style.display = 'block';
        });

        window.addEventListener('appinstalled', function() {
          deferredPrompt = null;
          if (section) section.style.display = 'none';
        });

        btn.addEventListener('click', function() {
          if (!deferredPrompt) return;
          deferredPrompt.prompt();
          deferredPrompt.userChoice.then(function(choice) {
            if (choice.outcome === 'accepted') {
              if (section) section.style.display = 'none';
            }
            deferredPrompt = null;
          });
        });

        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
          section.style.display = 'none';
        }
      })();
    </script>
</body>
</html>