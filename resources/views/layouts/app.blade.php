<!DOCTYPE html>
<html lang="ja">
<head>
    @php
        $pageTitle = trim($__env->yieldContent('title'));
        $metaTitle = trim($__env->yieldContent('meta_title'));
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: 'ミセチョクのデモサイトです。';
        $metaImage = trim($__env->yieldContent('meta_image')) ?: asset('assets/images/pwa/icon-512.png');
        $canonicalUrl = trim($__env->yieldContent('canonical')) ?: url()->current();
        $assetVersion = '20260320-pwa-install';
        $resolvedTitle = $metaTitle !== ''
            ? $metaTitle
            : ($pageTitle !== '' ? $pageTitle . ' | ' . config('app.name', 'ミセチョク') : config('app.name', 'ミセチョク'));
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="{{ config('app.name', 'ミセチョク') }}">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $resolvedTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $resolvedTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">
    {{-- PWA --}}
    <meta name="theme-color" content="#190509">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ミセチョク">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/pwa/icon-192.png') }}">
    {{-- ファビコン（データURIで404防止。色はアプリのゴールド・ダーク） --}}
    <link rel="icon" href="data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" fill="#190509"/><text x="16" y="22" font-size="18" text-anchor="middle" fill="#D4AF37">店</text></svg>') }}" type="image/svg+xml">
    <title>{{ $resolvedTitle }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Shippori+Mincho:wght@400;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-header.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/character-guide.css') }}?v={{ $assetVersion }}">

    <script src="{{ asset('assets/js/app.js') }}" defer></script>

    @stack('styles')
</head>
    
<body class="@yield('body-class')" data-notification-badge="{{ isset($unreadNewsCount) ? (int) $unreadNewsCount : 0 }}">
    <div id="bg-layer"></div>
    <div id="menu-overlay" class="menu-overlay"></div>

    <div id="app">
        {{-- メインレイアウト部分を縦並びのFlexコンテナとして包む --}}
        <div class="main-layout-container flex-1 flex flex-col min-w-0">

            {{-- ヘッダー（ページタイトルを渡して表示） --}}
            @include('layouts.parts.header', ['headerTitle' => trim($__env->yieldContent('title'))])

            {{-- @yield('guide_message') で各ページの設定内容を注入する --}}
            @include('layouts.parts.character-guide', ['guideMessage' => $__env->yieldContent('guide_message')])

            {{-- メインコンテンツ（プロフィール詳細は content-wrapper を使わず幅をアプリ全体に統一） --}}
            <main id="main-content">
                @if(request()->routeIs('cast.shopprofileview.show', 'shop.castprofileview.show', 'cast.mypage.index', 'share.cast.show', 'share.recruit.show'))
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
    <script src="{{ asset('assets/js/character-guide.js') }}?v={{ $assetVersion }}"></script>
    <script src="{{ asset('assets/js/push-notification.js') }}"></script>

    {{-- 画像フルスクリーン用ライトボックス（全画面共通・オーバーレイクリックで閉じる） --}}
    <div id="global-lightbox-overlay" class="lightbox-overlay" onclick="window._closeGlobalLightbox && window._closeGlobalLightbox(event)">
        <img id="global-lightbox-image" src="" alt="" class="lightbox-image">
    </div>

    @stack('scripts')
    <script>
    (function () {
        function formatPostalCode(value) {
            var digits = String(value || '').replace(/\D+/g, '').slice(0, 7);

            if (digits.length <= 3) {
                return digits;
            }

            return digits.slice(0, 3) + '-' + digits.slice(3);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[data-postal-code]').forEach(function (input) {
                var sync = function () {
                    input.value = formatPostalCode(input.value);
                };

                input.addEventListener('input', sync);
                input.addEventListener('blur', sync);
                sync();
            });
        });
    })();
    </script>
    @include('partials.bank-autocomplete-scripts')
    <script>
    (function () {
        var overlay = document.getElementById('global-lightbox-overlay');
        var img = document.getElementById('global-lightbox-image');
        if (!overlay || !img) return;

        window.openImageLightbox = function (src) {
            if (!src) return;
            img.src = src;
            overlay.classList.add('is-open');
        };

        window._closeGlobalLightbox = function (e) {
            if (e) {
                if (e.target && !e.target.classList.contains('lightbox-overlay')) {
                    return;
                }
                e.stopPropagation();
            }
            overlay.classList.remove('is-open');
            img.src = '';
        };

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-lightbox-target').forEach(function (el) {
                el.style.cursor = 'zoom-in';
                el.addEventListener('click', function () {
                    window.openImageLightbox(el.currentSrc || el.src);
                });
            });
        });
    })();
    </script>
    {{-- PWA: Service Worker 登録 --}}
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
          navigator.serviceWorker.register('/sw.js?v={{ $assetVersion }}', { scope: '/' })
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
        var inlineBtn = document.getElementById('pwa-install-inline-btn');
        var iosGuide = document.getElementById('pwa-ios-guide');

        function isIosSafari() {
          var ua = window.navigator.userAgent || '';
          var isIos = /iphone|ipad|ipod/i.test(ua);
          var isSafari = /safari/i.test(ua) && !/crios|fxios|edgios|opr\//i.test(ua);
          return isIos && isSafari;
        }

        function hideInstallUi() {
          if (section) section.style.display = 'none';
          if (inlineBtn) inlineBtn.style.display = 'none';
          if (iosGuide) iosGuide.style.display = 'none';
        }

        window.triggerPwaInstall = function() {
          if (!deferredPrompt) {
            alert('ブラウザのメニュー（⋮）から「アプリをインストール」または「ホーム画面に追加」を選んでください。');
            return false;
          }
          deferredPrompt.prompt();
          deferredPrompt.userChoice.then(function(choice) {
            if (choice.outcome === 'accepted') {
              hideInstallUi();
            }
            deferredPrompt = null;
          });
          return true;
        };

        // Chrome（Android含む）: preventDefault すると「アドレスバーのインストール」やミニバーが出なくなる。
        // カスタムボタン用にイベントは保持するが、デフォルトのインストール案内は出す。
        window.addEventListener('beforeinstallprompt', function(e) {
          deferredPrompt = e;
          if (section) section.style.display = 'block';
          if (inlineBtn) inlineBtn.style.display = 'inline-flex';
        });

        window.addEventListener('appinstalled', function() {
          deferredPrompt = null;
          hideInstallUi();
        });

        if (btn) {
          btn.addEventListener('click', function() { window.triggerPwaInstall(); });
        }

        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
          hideInstallUi();
          return;
        }

        if (isIosSafari() && iosGuide) {
          iosGuide.style.display = 'block';
        }
      })();
    </script>
</body>
</html>