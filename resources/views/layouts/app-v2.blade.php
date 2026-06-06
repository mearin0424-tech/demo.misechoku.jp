<!DOCTYPE html>
<html lang="ja" data-theme="amethyst">
<head>
    @php
        $pageTitle = trim($__env->yieldContent('title'));
        $metaTitle = trim($__env->yieldContent('meta_title'));
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: 'ミセチョクのデモサイトです。';
        $metaImage = trim($__env->yieldContent('meta_image')) ?: asset('assets/images/pwa/icon-512.png');
        $canonicalUrl = trim($__env->yieldContent('canonical')) ?: url()->current();
        $assetVersion = '20260509-ai-bubble-tone';
        $resolvedTitle = $metaTitle !== ''
            ? $metaTitle
            : ($pageTitle !== '' ? $pageTitle . ' | ' . config('app.name', 'ミセチョク') : config('app.name', 'ミセチョク'));

        // ===== ヘッダー：タイトル / バックボタンの決定（旧 layouts.parts.header と同一ロジック） =====
        $routeName = Route::currentRouteName();
        $mainRouteNames = [
            'cast.home', 'shop.home',
            'cast.search.index', 'shop.search.index',
            'cast.interaction.index', 'shop.interaction.index',
            'cast.talk.index', 'shop.talk.index',
            'cast.mypage.index', 'shop.mypage.index',
        ];
        $isMainPage = in_array($routeName, $mainRouteNames, true);
        $isLoginPage = request()->routeIs('login.demo');
        $showBackButton = !$isMainPage && !$isLoginPage;
        $isTalkRoomPage = request()->routeIs('cast.talk.room', 'shop.talk.room');

        $segments = explode('.', $routeName ?: '');
        $lastSegment = end($segments) ?: '';
        $secondSegment = $segments[1] ?? '';

        $engByLast = [
            'about' => 'ABOUT', 'terms' => 'TERMS', 'privacy' => 'PRIVACY', 'htu' => 'HOW TO USE',
            'column' => 'COLUMN', 'form' => 'CONTACT', 'notices' => 'NOTICES', 'subscription' => 'SUBSCRIPTION',
            'account' => 'ACCOUNT', 'notification' => 'NOTIFICATION', 'identity' => 'IDENTITY',
            'reviews' => 'REVIEWS', 'review' => 'REVIEW', 'management' => 'MANAGEMENT',
            'employment' => 'EMPLOYMENT', 'documents' => 'DOCUMENTS', 'jobdescription' => 'JOB',
            'castprofileview' => 'CAST PROFILE',
        ];
        $engBySecond = [
            'home' => 'ENCOUNT', 'login' => 'LOGIN', 'search' => 'SEARCH', 'mypage' => 'MY PAGE',
            'talk' => 'TALK', 'interaction' => 'CONNECTION', 'manage' => 'MANAGEMENT',
            'register' => 'ENTRY', 'profile' => 'PROFILE', 'recruit' => 'RECRUIT', 'recruits' => 'RECRUIT',
            'setting' => 'SETTING', 'support' => 'SUPPORT', 'official' => 'OFFICIAL',
            'share' => 'SHARE', 'column' => 'COLUMN',
        ];
        $currentEngTitle = $engByLast[$lastSegment] ?? ($engBySecond[$secondSegment] ?? '');

        $headerTitleCustom = trim((string) ($__env->yieldContent('header_title') ?? ''));
        if ($isTalkRoomPage) {
            $displayTitle = $headerTitleCustom !== '' ? $headerTitleCustom : 'TALK';
        } elseif ($currentEngTitle !== '') {
            $displayTitle = $currentEngTitle;
        } else {
            $displayTitle = $headerTitleCustom !== '' ? $headerTitleCustom : $pageTitle;
        }

        // ===== ボトムナビ：アクティブ判定（旧 layouts.parts.footer と同一ロジック） =====
        $navPrefix       = request()->is('cast/*') ? 'cast' : 'shop';
        $navIsHome        = request()->is("*/home*");
        $navIsSearch      = request()->is("*/search*");
        $navIsInteraction = request()->is("*/interaction*");
        $navIsTalk        = request()->is("*/talk*");
        $navIsMypage      = request()->is("*/mypage*");
        $searchHref = $navPrefix === 'cast'
            ? route('cast.search.index', ['tab' => 'list'])
            : route('shop.search.index');
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
    <meta name="theme-color" content="#050505">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ミセチョク">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/pwa/icon-192.png') }}">
    <link rel="icon" href="data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" fill="#050505"/><text x="16" y="22" font-size="18" text-anchor="middle" fill="#A78BFA">店</text></svg>') }}" type="image/svg+xml">
    <title>{{ $resolvedTitle }}</title>

    {{-- 共通: Font Awesome（sidebar partial が依存） / Swiper / Noto Sans JP --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- layout-sidebar.css が依存する CSS 変数（旧 app.css 由来）の最小フォールバック --}}
    <style>
        :root {
            --max-content-width: 430px;
            --footer-height: 75px;
            --color-border-strong: rgba(197, 160, 89, 0.4);
        }
    </style>

    {{-- サイドバー partial の位置決めCSS（右端からスライド） --}}
    <link rel="stylesheet" href="{{ asset('assets/css/layout-sidebar.css') }}">

    {{-- ヘッダーのポップアップ（通知 / タスク）専用CSS：app.js が #btn-header-* で togglePopup する --}}
    <link rel="stylesheet" href="{{ asset('assets/css/layout-header.css') }}">

    {{-- キャラクターガイド（オコジョ）専用CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/character-guide.css') }}?v={{ $assetVersion }}">

    {{-- アプリ既存 JS（CSRFやサイドメニュー開閉などはここに集約） --}}
    <script src="{{ asset('assets/js/app.js') }}" defer></script>

    {{-- 新デザインシステム（tailwind.css / Phosphor / Montserrat / behaviors.js）
         x-ui.assets は @push 経由なので、必ず @stack('head-styles') より前に呼ぶ（後だとスタックが空のまま head が確定する）。 --}}
    <x-ui.assets />
    @stack('styles')
    @stack('head-styles')
</head>
<body class="@yield('body-class') bg-base text-text-main"
      data-notification-badge="{{ isset($unreadNewsCount) ? (int) $unreadNewsCount : 0 }}">

    {{-- サイドメニュー開閉用オーバーレイ（app.js が #menu-overlay を操作） --}}
    <div id="menu-overlay" class="menu-overlay"></div>

    <div id="app">

        {{-- ============================================================
             新ヘッダー：常時グラス表示（最上部スクロール位置でも見える）
             3つのボタン id は旧と同じものを維持（既存JSがそのまま使える）
             ============================================================ --}}
        <header class="fixed top-0 left-0 w-full z-40 pt-safe bg-deep-purple/30 backdrop-blur-md border-b border-line-accent/40 shadow-header">
            <div class="flex justify-between items-center gap-2 px-3 py-2 max-w-[430px] mx-auto">
                {{-- 左：戻る（条件付き） --}}
                <div class="w-10 h-10 flex items-center justify-center shrink-0">
                    @if($showBackButton)
                        <a href="javascript:history.back()"
                           class="w-10 h-10 rounded-full flex items-center justify-center bg-black/50 backdrop-blur-md text-white border border-white/20 shadow-md">
                            <x-ui.icon name="back" class="text-xl" />
                        </a>
                    @endif
                </div>

                {{-- 中央：タイトル（常時表示。app-title=Montserrat） --}}
                <div class="app-title font-bold tracking-widest text-[13px] text-white text-center flex-1 truncate">
                    {{ $displayTitle }}
                </div>

                {{-- 右：タスク / 通知 / ハンバーガー（id は旧 layouts.parts.header と同一） --}}
                <div class="flex items-center gap-1 shrink-0">
                    <button id="btn-header-task" type="button"
                            class="relative w-10 h-10 rounded-full flex items-center justify-center bg-black/40 backdrop-blur-md text-white border border-white/10">
                        <x-ui.icon name="task" class="text-lg" />
                        @if(isset($todoList) && count($todoList) > 0)
                            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-4 h-4 px-1 rounded-full bg-accent text-on-accent text-[9px] font-bold shadow-badge-3d">{{ count($todoList) }}</span>
                        @endif
                    </button>
                    <button id="btn-header-notification" type="button"
                            class="relative w-10 h-10 rounded-full flex items-center justify-center bg-black/40 backdrop-blur-md text-white border border-white/10">
                        <x-ui.icon name="bell" class="text-lg" />
                        @if(isset($unreadNewsCount) && $unreadNewsCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-4 h-4 px-1 rounded-full bg-accent text-on-accent text-[9px] font-bold shadow-badge-3d">{{ $unreadNewsCount }}</span>
                        @endif
                    </button>
                    <button id="btn-header-menu" type="button"
                            class="w-10 h-10 rounded-full flex items-center justify-center bg-black/40 backdrop-blur-md text-white border border-white/10">
                        <x-ui.icon name="list" class="text-xl" />
                    </button>
                </div>
            </div>
        </header>

        {{-- オコジョガイド：表示／文言は運営管理画面で設定 --}}
        @include('layouts.parts.character-guide')

        {{-- メイン --}}
        <main id="main-content">
            @yield('content')
        </main>

        {{-- ============================================================
             新ボトムナビ（behaviors.js が data-bottom-nav を見る）
             active 判定は URL 階層から（旧 footer と同等）。href は実ルート。
             ============================================================ --}}
        <nav data-bottom-nav data-nav-style="neon"
             class="fixed bottom-0 left-0 w-full z-50 pb-safe bg-deep-purple/30 backdrop-blur-md border-t border-line-accent/40 shadow-footer">
            <div class="flex justify-around items-center pt-3 pb-2 px-2 max-w-[430px] mx-auto">
                <a href="{{ route($navPrefix . '.home') }}"
                   class="nav-item flex flex-col items-center p-2 transition-all duration-300 {{ $navIsHome ? 'is-active' : '' }}">
                    <span class="nav-icon-wrap w-12 h-12 rounded-full flex items-center justify-center mb-0.5 transition-all">
                        <x-ui.icon name="home" class="nav-icon text-[28px] transition-all" />
                    </span>
                    <span class="app-title text-[9px] font-bold tracking-wider">ENCOUNT</span>
                </a>
                <a href="{{ $searchHref }}"
                   class="nav-item flex flex-col items-center p-2 transition-all duration-300 {{ $navIsSearch ? 'is-active' : '' }}">
                    <span class="nav-icon-wrap w-12 h-12 rounded-full flex items-center justify-center mb-0.5 transition-all">
                        <x-ui.icon name="search" class="nav-icon text-[28px] transition-all" />
                    </span>
                    <span class="app-title text-[9px] font-bold tracking-wider">SEARCH</span>
                </a>
                <a href="{{ route($navPrefix . '.interaction.index') }}"
                   class="nav-item flex flex-col items-center p-2 transition-all duration-300 {{ $navIsInteraction ? 'is-active' : '' }}">
                    <span class="nav-icon-wrap w-12 h-12 rounded-full flex items-center justify-center mb-0.5 transition-all">
                        <x-ui.icon name="likes" class="nav-icon text-[28px] transition-all" />
                    </span>
                    <span class="app-title text-[9px] font-bold tracking-wider">LIKES</span>
                </a>
                <a href="{{ route($navPrefix . '.talk.index') }}"
                   class="nav-item flex flex-col items-center p-2 transition-all duration-300 {{ $navIsTalk ? 'is-active' : '' }}">
                    <span class="nav-icon-wrap w-12 h-12 rounded-full flex items-center justify-center mb-0.5 transition-all">
                        <x-ui.icon name="talk" class="nav-icon text-[28px] transition-all" />
                    </span>
                    <span class="app-title text-[9px] font-bold tracking-wider">TALK</span>
                </a>
                <a href="{{ route($navPrefix . '.mypage.index') }}"
                   class="nav-item flex flex-col items-center p-2 transition-all duration-300 {{ $navIsMypage ? 'is-active' : '' }}">
                    <span class="nav-icon-wrap w-12 h-12 rounded-full flex items-center justify-center mb-0.5 transition-all">
                        <x-ui.icon name="mypage" class="nav-icon text-[28px] transition-all" />
                    </span>
                    <span class="app-title text-[9px] font-bold tracking-wider">MY PAGE</span>
                </a>
            </div>
        </nav>

        {{-- サイドバー（既存 partial をそのまま利用：#side-menu 内に inline style 同梱） --}}
        @include('layouts.parts.sidebar')
    </div>

    {{-- 通知 / タスク用ポップオーバー（旧 footer の最下部で読み込まれていたものを継承） --}}
    @include('layouts.parts.header_popover.notification')
    @include('layouts.parts.header_popover.task')

    {{-- 共通ライトボックス（プロフィール画像クリック等） --}}
    <div id="global-lightbox-overlay" class="lightbox-overlay" onclick="window._closeGlobalLightbox && window._closeGlobalLightbox(event)">
        <img id="global-lightbox-image" src="" alt="" class="lightbox-image">
    </div>

    {{-- 既存共通 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('assets/js/character-guide.js') }}?v={{ $assetVersion }}"></script>
    <script src="{{ asset('assets/js/push-notification.js') }}"></script>
    <script src="{{ asset('assets/js/location-modal.js') }}" defer></script>
    <script src="{{ asset('assets/js/share-menu.js') }}" defer></script>

    @stack('scripts')
    @stack('foot-scripts')

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
                if (e.target && !e.target.classList.contains('lightbox-overlay')) return;
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
            .then(function () {}).catch(function () {});
        });
      }
    </script>
</body>
</html>
