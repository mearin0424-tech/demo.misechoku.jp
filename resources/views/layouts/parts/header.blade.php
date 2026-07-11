@php
    $routeName = Route::currentRouteName();
    $pageId = $pageId ?? (explode('.', $routeName)[1] ?? 'home');

    // SWIPE / SEARCH / LIKES / TALK / MYPAGE のトップ画面はバックボタンを出さず
    // タスク・通知・メニューだけの共通ヘッダーで統一する。
    // それ以外（プロフィール詳細・採用管理など 1 階層以上深い画面）はバック付きで統一。
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

    // ============================================================
    // 英語タイトル決定ロジック（最終セグメント優先 → 第2セグメント）
    // 個人名表示が必要なトークルーム以外は英語に統一
    // ============================================================
    $segments = explode('.', $routeName ?: '');
    $lastSegment = end($segments) ?: '';
    $secondSegment = $segments[1] ?? '';

    $engByLast = [
        'about' => 'ABOUT',
        'terms' => 'TERMS',
        'privacy' => 'PRIVACY',
        'htu' => 'HOW TO USE',
        'column' => 'COLUMN',
        'form' => 'CONTACT',
        'notices' => 'NOTICES',
        'subscription' => 'SUBSCRIPTION',
        'account' => 'ACCOUNT',
        'notification' => 'NOTIFICATION',
        'identity' => 'IDENTITY',
        'reviews' => 'REVIEWS',
        'review' => 'REVIEW',
        'management' => 'MANAGEMENT',
        'employment' => 'EMPLOYMENT',
        'documents' => 'DOCUMENTS',
        'jobdescription' => 'JOB',
        'castprofileview' => 'CAST PROFILE',
    ];
    $engBySecond = [
        'home'        => 'SWIPE',
        'login'       => 'LOGIN',
        'search'      => 'SEARCH',
        'mypage'      => 'MY PAGE',
        'talk'        => 'TALK',
        'interaction' => 'KEEPS',
        'manage'      => 'MANAGEMENT',
        'register'    => 'ENTRY',
        'profile'     => 'PROFILE',
        'recruit'     => 'RECRUIT',
        'recruits'    => 'RECRUIT',
        'setting'     => 'SETTING',
        'support'     => 'SUPPORT',
        'official'    => 'OFFICIAL',
        'share'       => 'SHARE',
        'column'      => 'COLUMN',
    ];

    $resolveEnglishTitle = function () use ($engByLast, $engBySecond, $lastSegment, $secondSegment) {
        if (isset($engByLast[$lastSegment])) return $engByLast[$lastSegment];
        if (isset($engBySecond[$secondSegment])) return $engBySecond[$secondSegment];
        return '';
    };
    $currentEngTitle = $resolveEnglishTitle();

    // ============================================================
    // 表示タイトル決定
    //   1. トークルーム → header_title（個人名）
    //   2. それ以外 → 英語ラベル（あれば）
    //   3. 英語が無い → header_title or @section('title') の和文
    // ============================================================
    $headerTitleCustom = trim((string) ($__env->yieldContent('header_title') ?? ''));
    $headerTitleFromPage = isset($headerTitle) ? trim((string) $headerTitle) : '';

    if ($isTalkRoomPage) {
        $displayTitle = $headerTitleCustom !== '' ? $headerTitleCustom : ($headerTitleFromPage !== '' ? $headerTitleFromPage : 'TALK');
    } elseif ($currentEngTitle !== '') {
        $displayTitle = $currentEngTitle;
    } else {
        $displayTitle = $headerTitleCustom !== '' ? $headerTitleCustom : ($headerTitleFromPage !== '' ? $headerTitleFromPage : '');
    }

    $headerAvatar = trim((string) ($__env->yieldContent('header_avatar') ?? ''));
    $isCast = request()->is('cast/*');
@endphp

<header id="global-header">
    {{-- 左側：戻るボタン または トーク相手アイコン --}}
    <div class="header-left">
        @if($showBackButton)
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif
        @if($isTalkRoomPage && $headerAvatar !== '')
            <div class="header-talk-identity">
                <img src="{{ $headerAvatar }}" alt="" class="header-talk-avatar">
                <span class="header-talk-name">{{ $displayTitle }}</span>
            </div>
        @endif
    </div>

    {{-- 中央：ページタイトル（英語＋ゴールド・グラデーション、トークルーム以外） --}}
    <div class="header-center-title">
        @if(!$isTalkRoomPage)
            <span class="header-title-main header-title-serif">
                {{ $displayTitle }}
            </span>
        @endif
    </div>

    {{-- 右側：タスク / 通知 / ハンバーガーメニュー --}}
    <div class="header-right">
        @php
            $taskTotal = isset($todoList) ? count($todoList) : 0;
            $taskHigh  = isset($taskHighCount) ? (int) $taskHighCount : 0;
            $bellCount = isset($unreadNewsCount) ? (int) $unreadNewsCount : 0;
        @endphp
        <button id="btn-header-task"
                class="header-icon-btn {{ $taskHigh > 0 ? 'is-urgent' : '' }} {{ $taskTotal > 0 ? 'has-badge' : '' }}"
                aria-label="やることリスト（{{ $taskTotal }}件）"
                aria-haspopup="true">
            <i class="fas fa-list-check header-icon-btn__ico"></i>
            @if($taskTotal > 0)
                <span class="header-badge {{ $taskHigh > 0 ? 'is-urgent' : '' }}" aria-hidden="true">
                    {{ $taskTotal > 99 ? '99+' : $taskTotal }}
                </span>
            @endif
        </button>

        <button id="btn-header-notification"
                class="header-icon-btn {{ $bellCount > 0 ? 'has-badge is-ringing' : '' }}"
                aria-label="お知らせ（未読 {{ $bellCount }}件）"
                aria-haspopup="true">
            <i class="fas fa-bell header-icon-btn__ico"></i>
            @if($bellCount > 0)
                <span class="header-badge is-accent" aria-hidden="true">
                    {{ $bellCount > 99 ? '99+' : $bellCount }}
                </span>
            @endif
        </button>

        <button id="btn-header-menu" class="header-icon-btn" aria-label="メニュー">
            <i class="fas fa-bars header-icon-btn__ico"></i>
        </button>
    </div>
</header>
