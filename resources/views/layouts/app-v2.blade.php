<!DOCTYPE html>
{{-- ポイント色（ボタン/アイコン/アクティブ状態）をアメジスト紫に統一（2026-07-12） --}}
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
            'home' => 'SWIPE', 'login' => 'LOGIN', 'search' => 'SEARCH', 'mypage' => 'MY PAGE',
            'talk' => 'TALK', 'interaction' => 'KEEPS', 'manage' => 'MANAGEMENT',
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
    <link rel="icon" href="data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" fill="#050505"/><text x="16" y="22" font-size="18" text-anchor="middle" fill="#a855f7">店</text></svg>') }}" type="image/svg+xml">
    <title>{{ $resolvedTitle }}</title>

    {{-- 共通: Font Awesome（sidebar partial が依存） / Swiper / Noto Sans JP --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- layout-sidebar.css が依存する CSS 変数（旧 app.css 由来）の最小フォールバック。
         サイドメニューはビューポート右端から出すため --max-content-width を 100vw にして
         layout-sidebar.css の `right: max(0px, calc(50vw - var(--max-content-width)/2))` を 0 に解決させる。 --}}
    <style>
        :root {
            /* メインコンテンツの最大幅。ヘッダー・フッターと同様に全画面で横幅いっぱいを使う。
               header / sub-header / main / content-wrapper / bottom-nav すべてがこの値を共有する。
               （固定要素の calc(50vw - w/2) 系は 0 に解決され、自然に全幅へ展開される） */
            --max-content-width: 100%;
            --content-padding-x: 16px;
            --footer-height: 75px;
            --header-height: 60px;
            --sub-header-height: 46px;

            /* layout-header.css / layout-sidebar.css 等が var(--gold) を参照する。
               アンビエント紫はオリジナルのまま保持（=> ヘッダータイトル・サイドメニュー・アイコンの hover が紫）。
               ボタン／バッジ／FAB はそれ自体が bg-accent / text-accent-text を使うので、
               --accent 側のモーブピンクで描画される（このブロックでは触らない）。 */
            --gold:               #a78bfa;
            --gold-light:         #c4b5fd;
            --gold-deep:          #7c3aed;
            --color-text-header:  #f5f5f5;
            --color-text-muted:   #a0a0a0;
            --color-text:         #f5f5f5;
            --color-border:        rgba(168, 85, 247, 0.20);
            --color-border-strong: rgba(168, 85, 247, 0.45);
            --color-card-strong:  #1a1a1a;
        }
        /* タブレット〜デスクトップ：幅は 100% のまま、左右の余白だけ少し広げる */
        @media (min-width: 768px) {
            :root { --content-padding-x: 24px; }
        }
        @media (min-width: 1024px) {
            :root { --content-padding-x: 28px; }
        }

        /* --- 認証/ログイン画面：ヘッダー・フッター・サイドメニュー・キャラガイドを非表示にして
              ロゴ＋フォームだけの全画面表示に。デザイン側は共通の紫アクセント（global override で適用済み） --- */
        body.page-demo-login #global-header,
        body.page-demo-login nav[data-bottom-nav],
        body.page-demo-login #character-guide,
        body.page-demo-login #side-menu,
        body.page-demo-login #menu-overlay,
        body.page-demo-login #header-task-popup,
        body.page-demo-login #header-notification-popup,
        body.page-auth-login #global-header,
        body.page-auth-login nav[data-bottom-nav],
        body.page-auth-login #character-guide,
        body.page-auth-login #side-menu,
        body.page-auth-login #menu-overlay,
        body.page-auth-login #header-task-popup,
        body.page-auth-login #header-notification-popup {
            display: none !important;
        }
        body.page-demo-login main#main-content,
        body.page-auth-login main#main-content {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            min-height: 100vh !important;
            max-width: 100% !important;
        }

        /* ============================================================
           レイアウト崩れ修正バッチ
           ============================================================ */

        /* Fix 4: 本人確認 _identity_form のカスタムファイル入力 */
        .visually-hidden {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            overflow: hidden !important;
            clip: rect(0,0,0,0) !important;
            white-space: nowrap !important;
        }
        .file-upload-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 8px 16px !important;
            background: linear-gradient(135deg, var(--color-accent-grad-from, #c4b5fd), var(--color-accent-grad-to, #7c3aed)) !important;
            color: #fff !important;
            border-radius: 999px !important;
            cursor: pointer !important;
            font-size: 0.875rem !important;
            font-weight: 700 !important;
            box-shadow: var(--shadow-btn-3d) !important;
        }
        .file-upload-btn:active { transform: translateY(1px); }
        .file-name-display {
            display: block !important;
            font-size: 0.78rem !important;
            color: var(--color-text-sub) !important;
            margin-top: 6px !important;
            word-break: break-all !important;
        }

        /* Fix 1 + 2: SEARCH 画面の上余白
           タブあり（cast）は .tab-page-body の margin-top で sub-header を回避済み。
           タブなし（shop）はサブヘッダーが無く、main の padding-top で global-header を回避済み。
           → 二重余白を防ぐため、ここでの padding-top 強制は撤去 */

        /* Fix 3: /cast/profile/edit の身長・体重・3サイズフィールド
           input と単位（cm/kg）を横並びに */
        .metric-input-wrap {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        .metric-input-wrap input[type="number"] {
            width: 80px !important;
            flex-shrink: 0 !important;
        }
        .metric-unit {
            flex-shrink: 0 !important;
            font-size: 0.875rem !important;
            color: inherit !important;
        }

        /* Fix 5: /cast/talk の店舗名が 2 行に折り返すのを 1 行省略へ */
        .talk-name {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            max-width: 100% !important;
        }

        /* Fix 6: /cast/mypage/management の下部ボーナス申請バーで店舗名が切れる
           Option 2 採用：info を flex:1 + min-width:0 で縮小許可、内側で省略 */
        .deposit-cta-bar__inner {
            gap: 8px !important;
        }
        .deposit-cta-bar__info {
            flex: 1 !important;
            min-width: 0 !important;
            overflow: hidden !important;
        }
        .deposit-cta-bar__amount {
            overflow: hidden !important;
        }
        .deposit-cta-bar__amount strong {
            display: block !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* Fix 9: SWIPE の求人カード店舗名が 2 行に折り返すのを 1 行省略へ */
        .rc-shop-name {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* --- SWIPE カード上のテキスト：ひとまわり小さく + もう少し画面下へ寄せる --- */
        .card-bottom-info {
            padding-bottom: 12px !important;   /* 旧 24px → 12px に縮めて画面下へ */
            padding-top: 48px !important;      /* 上方のグラデ領域を少し短く */
            min-height: 96px !important;       /* 旧 120px → 96px */
        }
        .cast-card:not(.cast-card--recruit) .card-bottom-info {
            padding-top: 44px !important;
            min-height: 90px !important;
        }
        .cast-name {
            font-size: clamp(1.05rem, 4.4vw, 1.45rem) !important;
            margin-bottom: 1px !important;
        }
        .cast-name .age { font-size: 0.92em !important; }
        .card-shop-name-main {
            font-size: clamp(0.92rem, 3.4vw, 1.2rem) !important;
            margin: 0 0 3px !important;
        }
        .card-shop-name-sub {
            font-size: clamp(0.68rem, 2.2vw, 0.78rem) !important;
        }
        .card-location {
            font-size: clamp(0.74rem, 2.6vw, 0.85rem) !important;
            margin-bottom: 6px !important;
        }
        .card-recruit-bonus-line {
            font-size: clamp(0.82rem, 3vw, 0.98rem) !important;
        }
        .card-shop-meta { margin-bottom: 7px !important; }
        .card-rating-cnt { font-size: clamp(0.54rem, 1.6vw, 0.62rem) !important; }
        @media (max-width: 480px) {
            .card-bottom-info {
                padding-bottom: 8px !important;
                padding-top: 40px !important;
                min-height: 82px !important;
            }
        }

        /* --- SWIPE カード右側のアクションボタン群を小さく
              旧 home.css は 56px（PC）/ 52px（タブ）/ 48px（モバイル）。これを全体的に縮める --- */
        .action-circle-btn,
        .action-btn-message,
        .card-recruit-btn {
            width: 42px !important;
            height: 42px !important;
            min-width: 42px !important;
            min-height: 42px !important;
            font-size: 1.05rem !important;
        }
        .action-circle-btn.like {
            height: auto !important;
            min-height: 42px !important;
            padding-top: 6px !important;
            padding-bottom: 5px !important;
        }
        .action-circle-btn.like .action-btn-count {
            font-size: 0.62rem !important;
        }
        .card-actions-overlay {
            gap: 8px !important;
        }
        @media (max-width: 480px) {
            .action-circle-btn,
            .action-btn-message,
            .card-recruit-btn {
                width: 38px !important;
                height: 38px !important;
                min-width: 38px !important;
                min-height: 38px !important;
                font-size: 0.95rem !important;
            }
            .action-circle-btn.like {
                min-height: 38px !important;
            }
            .card-actions-overlay {
                gap: 7px !important;
            }
        }

        /* --- 詳細検索 FAB：画面（ビューポート）右下に固定。
              旧 search.css は 430px センターのコンテンツ右端基準で left 計算していたため、
              デスクトップだと中央寄りに出てしまう。ここで右端基準に上書き。 */
        .search-detail-fab {
            left: auto !important;
            right: calc(16px + env(safe-area-inset-right, 0px)) !important;
            bottom: calc(var(--footer-height, 75px) + 16px + env(safe-area-inset-bottom, 0px)) !important;
        }

        /* --- TALK ROOM 専用：ボトムナビを隠し、メッセージ入力欄をルームコンテナ最下部に固定。
              さらに main / body のスクロールを抑止して二重スクロールバーを解消する。
              chat-input-area は position: fixed ではなく、position: relative の #talk-room-container を
              基準に position: absolute で貼り付けることで、祖先要素の transform / filter / backdrop-filter
              による「fixed が absolute 化する」問題を避け、スクロール中もブレずに最下部に貼り付く。 --- */
        body.page-talk-room { overflow: hidden; }
        body.page-talk-room nav[data-bottom-nav] { display: none !important; }
        body.page-talk-room main#main-content {
            height: 100vh !important;
            min-height: 0 !important;
            padding-bottom: 0 !important;      /* ボトムナビが無いので確保不要 */
            overflow: hidden !important;
        }
        body.page-talk-room #talk-room-container {
            height: calc(100vh - var(--header-height, 60px)) !important;
            min-height: 0 !important;
            position: relative !important;     /* chat-input-area の absolute 基準 */
        }
        body.page-talk-room #talk-room-container .chat-input-area {
            position: absolute !important;     /* 祖先 transform/filter で fixed が壊れる問題を回避 */
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            top: auto !important;
            width: 100% !important;
            max-width: 100% !important;
            transform: none !important;        /* talk.css の translateX(-50%) を無効化（left:0/right:0 で全幅化） */
        }

        /* --- サブヘッダー（TALK / LIKES 等のタブ）をヘッダーと同じく
              全幅展開＋中身は --max-content-width センターに揃え、ヘッダー直下に "1本のバー" として密着させる。
              左右ガターは #global-header と同じ計算式（16px or 中央寄せ）でブレなく揃える。
              背景・blur をヘッダーと完全に揃え、間の継ぎ目を消す。 --- */
        .sub-header-wrapper {
            top: var(--header-height, 60px) !important;
            left: 0 !important;
            transform: none !important;
            width: 100% !important;
            max-width: 100% !important;
            padding-left:  max(var(--content-padding-x, 16px), calc(50vw - var(--max-content-width, 430px) / 2)) !important;
            padding-right: max(var(--content-padding-x, 16px), calc(50vw - var(--max-content-width, 430px) / 2)) !important;
            z-index: 1600 !important;
            /* ヘッダーと完全に同じ背景（同 rgba + 同 blur）にして継ぎ目を消す */
            background: rgba(10, 10, 10, 0.92) !important;
            backdrop-filter: blur(18px) !important;
            -webkit-backdrop-filter: blur(18px) !important;
            margin-top: 0 !important;
            border-top: 0 !important;
            /* 下端にゴールドの極細ライン + 軽い影でコンテンツとの境界を明示。
               コンテンツに被って暗く落ちないよう影は控えめに。 */
            box-shadow:
                inset 0 -1px 0 rgba(168, 85, 247, 0.22),
                0 2px 6px rgba(0, 0, 0, 0.20) !important;
        }
        .sub-header-tabs {
            background-color: transparent !important;
        }

        /* --- TALK ROOM：他画面と同じ --max-content-width に揃えつつ、内側コンテナはフル幅で背景を敷く --- */
        body.page-talk-room #talk-room-container {
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            background:
                radial-gradient(circle at top, rgba(168, 85, 247, 0.10), transparent 40%),
                linear-gradient(180deg, var(--color-surface-from, #1a1a1a) 0%, var(--color-base, #050505) 100%) !important;
        }

        /* --- ヘッダー本体：背景は全幅、中身は --max-content-width センター（sub-header / bottom-nav と幅が揃う）。
              下端の border / 強い影は撤去。さらに左右上端から落とす radial-gradient（紫グロー）は
              下端まで色が残ってしまい、無地のサブヘッダーと角で段差が出るので、上から下へ向けて
              透明にフェードする linear-gradient に置き換える。これでヘッダー下端の色 = サブヘッダーの色
              （rgba(10,10,10,0.92)）になり、継ぎ目が消える。 --- */
        #global-header {
            width: 100% !important;
            max-width: 100% !important;
            left: 0 !important;
            transform: none !important;
            /* 左右パディングは sub-header と同じ計算式で完全一致させる（モバイルでは 16px ガター） */
            padding-left:  max(var(--content-padding-x, 16px), calc(50vw - var(--max-content-width, 430px) / 2)) !important;
            padding-right: max(var(--content-padding-x, 16px), calc(50vw - var(--max-content-width, 430px) / 2)) !important;
            background:
                /* 上端だけに紫グローを残し、ヘッダー下端 = サブヘッダー上端でぴったり同色にする */
                linear-gradient(180deg, rgba(168, 85, 247, 0.22) 0%, rgba(168, 85, 247, 0) 100%),
                rgba(10, 10, 10, 0.92) !important;
            /* 下端 1px のアクセント線は消す（サブヘッダーがある場合に隙間に見える） */
            border-bottom: 0 !important;
            /* 下方向の重い影は出さない。サブヘッダーがあるページではこの影が継ぎ目に見えるため。
               紫の周辺グローだけ薄く残してヘッダーの存在感を維持する */
            box-shadow: 0 0 18px rgba(168, 85, 247, 0.18) !important;
        }
        .header-icon-btn:hover,
        .header-icon-btn:focus-visible {
            background-color: rgba(168, 85, 247, 0.10) !important;
        }
        .header-icon-btn:active {
            background-color: rgba(168, 85, 247, 0.18) !important;
        }

        /* --- サイドメニュー本体：背景グラデ・シャドウを紫トーンへ + 右端はビューポート右端固定 --- */
        #side-menu {
            right: 0 !important;
            background:
                radial-gradient(circle at 0% 0%, rgba(168, 85, 247, 0.20), transparent 55%),
                linear-gradient(180deg, rgba(20, 20, 20, 0.96), rgba(10, 10, 10, 0.98)) !important;
            box-shadow:
                -12px 0 36px rgba(0,0,0,0.65),
                -2px 0 18px rgba(168, 85, 247, 0.28) !important;
            border-left: 1px solid rgba(168, 85, 247, 0.35) !important;
        }
        .sidebar-header {
            border-bottom-color: rgba(168, 85, 247, 0.25) !important;
        }
        .sidebar-footer {
            border-top-color: rgba(168, 85, 247, 0.25) !important;
        }
        .sidebar-sub-menu a:hover {
            background: rgba(168, 85, 247, 0.14) !important;
        }
        .btn-sidebar-close:hover {
            background: rgba(168, 85, 247, 0.18) !important;
        }
    </style>

    {{-- サイドバー partial の位置決めCSS（右端からスライド） --}}
    <link rel="stylesheet" href="{{ asset('assets/css/layout-sidebar.css') }}">

    {{-- ヘッダーのポップアップ（通知 / タスク）専用CSS：app.js が #btn-header-* で togglePopup する --}}
    <link rel="stylesheet" href="{{ asset('assets/css/layout-header.css') }}">

    {{-- 通知 / やることリスト ポップアップを mypage のカード調に上書き（不透明 + アクセント枠 + 3Dシャドウ） --}}
    <style>
        #header-task-popup,
        #header-notification-popup {
            background: linear-gradient(to bottom right, var(--color-surface-from), var(--color-base)) !important;
            border: 1px solid rgba(168, 85, 247, 0.4) !important;
            box-shadow: var(--shadow-card-3d) !important;
            border-radius: var(--radius-card) !important;
            opacity: 1 !important;
        }
        .task-popup-header {
            background: rgba(168, 85, 247, 0.12) !important;
            border-bottom: 1px solid rgba(168, 85, 247, 0.30) !important;
        }
        .task-popup-header h4 {
            color: var(--color-accent-text) !important;
            font-family: var(--font-display) !important;
            letter-spacing: 0.10em !important;
        }
        .notif-popup-section-label {
            background: rgba(168, 85, 247, 0.08) !important;
            color: var(--color-accent-text) !important;
            border-bottom-color: rgba(168, 85, 247, 0.20) !important;
        }
        .notif-popup-item {
            border-bottom-color: rgba(168, 85, 247, 0.20) !important;
            color: var(--color-text-main) !important;
        }
        a.notif-popup-item:hover {
            background: rgba(168, 85, 247, 0.10) !important;
        }
        .notif-popup-empty {
            color: var(--color-text-sub) !important;
            border-bottom-color: rgba(168, 85, 247, 0.20) !important;
        }

        /* --- 旧 layouts.app の main / content-wrapper / animate-fadeIn を v2 でも再現 ---
             ヘッダー/フッターが fixed で被さるため、旧 app.css と同じく main に
             padding-top: --header-height、padding-bottom: --footer-height を確保する。 --}}
        */
        main#main-content {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 0;
            min-height: 100vh;
            padding-top: var(--header-height, 60px);
            padding-bottom: calc(var(--footer-height, 75px) + env(safe-area-inset-bottom, 0px));
            box-sizing: border-box;
        }
        .content-wrapper {
            width: 100%;
            max-width: var(--max-content-width, 430px);
            min-width: 0;
            margin-left: auto;
            margin-right: auto;
            padding: 0 var(--content-padding-x, 16px);
            box-sizing: border-box;
            overflow-x: hidden;
        }
        .animate-fadeIn { animation: fadeIn 0.4s ease-out forwards; }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* --- スクロールバー非表示（全要素） : 機能は維持して見た目だけ消す --- */
        * {
            -ms-overflow-style: none;   /* IE / Edge */
            scrollbar-width: none;      /* Firefox */
        }
        *::-webkit-scrollbar {
            display: none;              /* Chrome / Safari / Webkit */
            width: 0;
            height: 0;
        }

        /* --- フォント統一：全要素を Noto Sans JP に。<i> は Font Awesome / Phosphor のアイコンフォントを保つため除外 --- */
        :root {
            --font-display: "Noto Sans JP", sans-serif;
            --font-sans:    "Noto Sans JP", sans-serif;
            --font-serif:   "Noto Sans JP", sans-serif;
        }
        html, body, body *:not(i):not(svg):not(svg *) {
            font-family: "Noto Sans JP", sans-serif !important;
        }
        /* serif-font / .header-title-serif 等の旧クラスも上書き */
        .serif-font,
        .header-title-serif,
        .header-title-main.header-title-serif,
        .mypage-page-title.serif-font,
        .mypage-shop-name.serif-font {
            font-family: "Noto Sans JP", sans-serif !important;
        }
        /* app-title (Montserrat 想定) も Noto Sans JP に */
        .app-title {
            font-family: "Noto Sans JP", sans-serif !important;
        }
    </style>

    {{-- キャラクターガイド（オコジョ）専用CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/character-guide.css') }}?v={{ $assetVersion }}">
    {{-- 操作モーダルの視認性一括改善（各モーダル CSS の後に読み込んで override する） --}}
    <link rel="stylesheet" href="{{ asset('assets/css/modal-readability.css') }}?v={{ $assetVersion }}">

    {{-- アプリ既存 JS（CSRFやサイドメニュー開閉などはここに集約） --}}
    <script src="{{ asset('assets/js/app.js') }}" defer></script>
    {{-- グローバル トースト：alert() の置き換えに使う window.appToast(msg, variant) --}}
    <script src="{{ asset('assets/js/app-toast.js') }}" defer></script>

    {{-- 新デザインシステム（tailwind.css / Phosphor / Montserrat / behaviors.js）
         x-ui.assets は @push 経由なので、必ず @stack('head-styles') より前に呼ぶ（後だとスタックが空のまま head が確定する）。 --}}
    <x-ui.assets />
    @stack('styles')
    @stack('head-styles')

    {{-- ボタン/CTA の役割ベース統一（全ページCSSの後に読み込んで上書き。DESIGN.md §10） --}}
    <link rel="stylesheet" href="{{ asset('assets/css/ui-consistency.css') }}?v={{ $assetVersion }}">
    {{-- 入力コンポーネントの全画面統一（文字列/文章/数値/日付/選択） --}}
    <link rel="stylesheet" href="{{ asset('assets/css/form-controls.css') }}?v={{ $assetVersion }}">
    {{-- モーション基盤（タブ/モーダル/画像/リビールのなめらか化。Step1） --}}
    <link rel="stylesheet" href="{{ asset('assets/css/motion.css') }}?v={{ $assetVersion }}">
    <script src="{{ asset('assets/js/motion.js') }}?v={{ $assetVersion }}" defer></script>
</head>
<body class="@yield('body-class') bg-base text-text-main"
      data-notification-badge="{{ isset($unreadNewsCount) ? (int) $unreadNewsCount : 0 }}">

    {{-- サイドメニュー開閉用オーバーレイ（app.js が #menu-overlay を操作） --}}
    <div id="menu-overlay" class="menu-overlay"></div>

    <div id="app">

        {{-- ヘッダー：旧画面とサイズ・フォント・配色を完全に揃えるため、
             layouts/parts/header を流用する（layout-header.css と Font Awesome に依存）。 --}}
        @include('layouts.parts.header', ['headerTitle' => $pageTitle])

        {{-- オコジョガイド：表示／文言は運営管理画面で設定 --}}
        @include('layouts.parts.character-guide')

        {{-- メイン：--max-content-width に追従して中央寄せ（モバイル430 → タブレット600 → デスクトップ720）。
             旧 layouts.app と同じ条件で content-wrapper を被せ、移行画面のレイアウトを大きく崩さないようにする。 --}}
        <main id="main-content" class="max-w-[var(--max-content-width)] mx-auto">
            @if(request()->routeIs('cast.shopprofile.show', 'shop.castprofileview.show', 'cast.mypage.index', 'shop.mypage.index', 'shop.recruits.show', 'share.cast.show', 'share.recruit.show'))
                @yield('content')
            @else
                <div class="content-wrapper animate-fadeIn">
                    @yield('content')
                </div>
            @endif
        </main>

        {{-- ============================================================
             新ボトムナビ（behaviors.js が data-bottom-nav を見る）
             active 判定は URL 階層から（旧 footer と同等）。href は実ルート。
             ============================================================ --}}
        <nav data-bottom-nav data-nav-style="neon"
             class="fixed bottom-0 left-0 w-full z-50 h-[75px] pb-[env(safe-area-inset-bottom)] box-border bg-deep-purple/30 backdrop-blur-md border-t border-line-accent/40 shadow-footer">
            <div class="flex justify-around items-center h-full px-2 max-w-[var(--max-content-width)] mx-auto">
                <a href="{{ route($navPrefix . '.home') }}"
                   class="nav-item flex flex-col items-center justify-center transition-all duration-300 {{ $navIsHome ? 'is-active' : '' }}">
                    <span class="nav-icon-wrap flex items-center justify-center mb-1 transition-all">
                        <x-ui.icon name="home" class="nav-icon text-[22px] transition-all" />
                    </span>
                    <span class="app-title text-[10px] font-bold tracking-wider">SWIPE</span>
                </a>
                <a href="{{ $searchHref }}"
                   class="nav-item flex flex-col items-center justify-center transition-all duration-300 {{ $navIsSearch ? 'is-active' : '' }}">
                    <span class="nav-icon-wrap flex items-center justify-center mb-1 transition-all">
                        <x-ui.icon name="search" class="nav-icon text-[22px] transition-all" />
                    </span>
                    <span class="app-title text-[10px] font-bold tracking-wider">SEARCH</span>
                </a>
                <a href="{{ route($navPrefix . '.interaction.index') }}"
                   class="nav-item flex flex-col items-center justify-center transition-all duration-300 {{ $navIsInteraction ? 'is-active' : '' }}">
                    <span class="nav-icon-wrap flex items-center justify-center mb-1 transition-all">
                        <x-ui.icon name="likes" class="nav-icon text-[22px] transition-all" />
                    </span>
                    <span class="app-title text-[10px] font-bold tracking-wider">KEEPS</span>
                </a>
                <a href="{{ route($navPrefix . '.talk.index') }}"
                   class="nav-item flex flex-col items-center justify-center transition-all duration-300 {{ $navIsTalk ? 'is-active' : '' }}">
                    <span class="nav-icon-wrap flex items-center justify-center mb-1 transition-all">
                        <x-ui.icon name="talk" class="nav-icon text-[22px] transition-all" />
                    </span>
                    <span class="app-title text-[10px] font-bold tracking-wider">TALK</span>
                </a>
                <a href="{{ route($navPrefix . '.mypage.index') }}"
                   class="nav-item flex flex-col items-center justify-center transition-all duration-300 {{ $navIsMypage ? 'is-active' : '' }}">
                    <span class="nav-icon-wrap flex items-center justify-center mb-1 transition-all">
                        <x-ui.icon name="mypage" class="nav-icon text-[22px] transition-all" />
                    </span>
                    <span class="app-title text-[10px] font-bold tracking-wider">MY PAGE</span>
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
