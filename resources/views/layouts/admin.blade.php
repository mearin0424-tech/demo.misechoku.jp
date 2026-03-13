@php
    $currentRoute = optional(request()->route())->getName();
    $pageTitle = trim($__env->yieldContent('admin_page_title')) ?: trim($__env->yieldContent('title')) ?: 'ダッシュボード';
    $menuGroups = [
        [
            'title' => 'ユーザー管理',
            'items' => [
                ['label' => '登録店舗', 'route' => 'admin.shops.index', 'icon' => 'fa-building', 'badge' => null, 'badge_class' => ''],
                ['label' => '登録キャスト', 'route' => 'admin.casts.index', 'icon' => 'fa-users', 'badge' => null, 'badge_class' => ''],
                ['label' => '運営者アカウント管理', 'route' => 'admin.admin-accounts.index', 'icon' => 'fa-user-gear', 'badge' => null, 'badge_class' => ''],
            ],
        ],
        [
            'title' => '運営業務管理',
            'items' => [
                ['label' => '本人・書類審査', 'route' => 'admin.verification.index', 'icon' => 'fa-shield-halved', 'badge' => '7', 'badge_class' => 'badge-green'],
                ['label' => '請求・振込タスク', 'route' => 'admin.tasks.index', 'icon' => 'fa-wallet', 'badge' => '13', 'badge_class' => 'badge-red'],
            ],
        ],
        [
            'title' => '記事作成',
            'items' => [
                ['label' => 'お知らせ投稿', 'route' => 'admin.notices.index', 'icon' => 'fa-bell', 'badge' => null, 'badge_class' => ''],
                ['label' => 'コラム投稿', 'route' => 'admin.columns.index', 'icon' => 'fa-pen-nib', 'badge' => null, 'badge_class' => ''],
            ],
        ],
        [
            'title' => 'マスタ設定',
            'items' => [
                ['label' => 'NGワード管理', 'route' => 'admin.ngwords.index', 'icon' => 'fa-ban', 'badge' => null, 'badge_class' => ''],
                ['label' => 'マスタメンテナンス', 'route' => 'admin.masters.index', 'icon' => 'fa-database', 'badge' => null, 'badge_class' => ''],
            ],
        ],
        [
            'title' => 'アナリティクス',
            'items' => [
                ['label' => '売上・ユーザー数増減', 'route' => 'admin.sales.index', 'icon' => 'fa-chart-column', 'badge' => null, 'badge_class' => ''],
            ],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | 管理画面</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --admin-bg: #16181d;
            --admin-sidebar: #0f1115;
            --admin-card: #20242b;
            --admin-card-hover: #262b33;
            --admin-header: #1b1f26;
            --admin-line: #313843;
            --admin-line-soft: rgba(255, 255, 255, 0.08);
            --admin-text: #f3f4f6;
            --admin-sub: #aeb6c2;
            --admin-muted: #8a92a0;
            --admin-accent: #d7dde7;
            --admin-accent-soft: rgba(255, 255, 255, 0.08);
            --admin-blue: #60a5fa;
            --admin-purple: #a78bfa;
            --admin-green: #34d399;
            --admin-yellow: #fbbf24;
            --admin-red: #f87171;
        }
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            min-height: 100%;
        }
        body {
            font-family: "Helvetica Neue", Arial, "Hiragino Sans", "Meiryo", sans-serif;
            background: var(--admin-bg);
            color: var(--admin-text);
        }
        a {
            color: inherit;
        }
        .admin-layout {
            min-height: 100vh;
            display: flex;
            background: var(--admin-bg);
        }
        .admin-sidebar {
            width: 272px;
            flex: 0 0 272px;
            background: var(--admin-sidebar);
            border-right: 1px solid var(--admin-line-soft);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 30;
        }
        .admin-sidebar-header {
            padding: 24px 22px;
            border-bottom: 1px solid var(--admin-line-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .admin-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
        }
        .admin-brand-title {
            font-size: 1.05rem;
        }
        .admin-brand-badge {
            padding: 3px 8px;
            border-radius: 999px;
            background: #2d333d;
            color: #f3f4f6;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.14em;
        }
        .admin-sidebar-close,
        .admin-menu-toggle,
        .admin-header-icon {
            appearance: none;
            border: 0;
            background: transparent;
            color: var(--admin-sub);
            cursor: pointer;
        }
        .admin-sidebar-close {
            display: none;
            font-size: 1.1rem;
        }
        .admin-sidebar-body {
            flex: 1;
            overflow-y: auto;
            padding: 18px 16px 18px;
        }
        .admin-sidebar-body::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
        .admin-home-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 14px;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: var(--admin-text);
            margin-bottom: 22px;
        }
        .admin-home-link.is-active {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .admin-nav-group {
            margin-bottom: 18px;
        }
        .admin-nav-group-title {
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--admin-muted);
            letter-spacing: 0.12em;
            padding: 0 12px;
            margin-bottom: 8px;
        }
        .admin-nav-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .admin-nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 12px;
            text-decoration: none;
            color: var(--admin-sub);
            transition: background 0.18s ease, color 0.18s ease;
        }
        .admin-nav-link:hover,
        .admin-nav-link.is-active {
            background: var(--admin-card);
            color: var(--admin-text);
        }
        .admin-nav-link-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .admin-nav-link-label {
            font-size: 0.88rem;
            font-weight: 600;
        }
        .admin-badge {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 999px;
        }
        .badge-green {
            background: rgba(52, 211, 153, 0.16);
            color: var(--admin-green);
        }
        .badge-red {
            background: rgba(248, 113, 113, 0.16);
            color: #fff;
        }
        .admin-sidebar-user {
            margin-top: 18px;
            padding: 14px;
            border-radius: 16px;
            background: var(--admin-card);
            border: 1px solid var(--admin-line-soft);
        }
        .admin-sidebar-user-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .admin-user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            background: #d1d5db;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .admin-user-name {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--admin-text);
        }
        .admin-user-role {
            font-size: 0.72rem;
            color: var(--admin-muted);
            letter-spacing: 0.08em;
        }
        .admin-logout-btn {
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--admin-line);
            background: transparent;
            color: var(--admin-text);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 700;
        }
        .admin-logout-btn:hover {
            background: rgba(248, 113, 113, 0.12);
            border-color: rgba(248, 113, 113, 0.45);
        }
        .admin-main {
            flex: 1;
            min-width: 0;
            margin-left: 272px;
            display: flex;
            flex-direction: column;
        }
        .admin-header {
            position: sticky;
            top: 0;
            z-index: 15;
            background: rgba(27, 31, 38, 0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--admin-line-soft);
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }
        .admin-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .admin-menu-toggle {
            display: none;
            font-size: 1.15rem;
        }
        .admin-header-title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }
        .admin-header-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .admin-search-wrap {
            position: relative;
            width: 320px;
            max-width: 42vw;
        }
        .admin-search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--admin-muted);
            font-size: 0.85rem;
        }
        .admin-search-input {
            width: 100%;
            padding: 10px 14px 10px 36px;
            border-radius: 999px;
            border: 1px solid var(--admin-line);
            background: #12151a;
            color: var(--admin-text);
            font-size: 0.85rem;
            outline: none;
        }
        .admin-search-input::placeholder {
            color: var(--admin-muted);
        }
        .admin-header-icon {
            position: relative;
            width: 38px;
            height: 38px;
            border-radius: 999px;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .admin-header-icon:hover {
            background: var(--admin-card);
            border-color: var(--admin-line-soft);
            color: var(--admin-text);
        }
        .admin-header-dot {
            position: absolute;
            top: 9px;
            right: 9px;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--admin-red);
            border: 2px solid var(--admin-header);
        }
        .admin-content {
            padding: 22px;
        }
        .admin-content-shell {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
        }
        .admin-mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.72);
            z-index: 40;
        }
        .admin-mobile-overlay.is-open {
            display: block;
        }
        @media (max-width: 1023px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.22s ease;
                z-index: 50;
            }
            .admin-sidebar.is-open {
                transform: translateX(0);
            }
            .admin-sidebar-close,
            .admin-menu-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .admin-main {
                margin-left: 0;
            }
            .admin-search-wrap {
                display: none;
            }
            .admin-content {
                padding: 16px;
            }
        }
        @media (max-width: 640px) {
            .admin-header {
                padding: 12px 14px;
            }
            .admin-header-title {
                font-size: 0.95rem;
            }
            .admin-content {
                padding: 14px;
            }
        }
    </style>
    @stack('admin-styles')
</head>
<body>
    <div id="admin-mobile-overlay" class="admin-mobile-overlay"></div>
    <div class="admin-layout">
        <aside id="admin-sidebar" class="admin-sidebar">
            <div class="admin-sidebar-header">
                <div class="admin-brand">
                    <span class="admin-brand-title">ミセチョク</span>
                    <span class="admin-brand-badge">ADMIN</span>
                </div>
                <button type="button" class="admin-sidebar-close" id="admin-sidebar-close" aria-label="メニューを閉じる">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="admin-sidebar-body">
                <a href="{{ route('admin.dashboard') }}" class="admin-home-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <i class="fas fa-table-columns"></i>
                    <span>ダッシュボード</span>
                </a>

                @foreach ($menuGroups as $group)
                    <section class="admin-nav-group">
                        <div class="admin-nav-group-title">{{ $group['title'] }}</div>
                        <nav class="admin-nav-list">
                            @foreach ($group['items'] as $item)
                                <a href="{{ route($item['route']) }}" class="admin-nav-link {{ request()->routeIs($item['route']) ? 'is-active' : '' }}">
                                    <span class="admin-nav-link-main">
                                        <i class="fas {{ $item['icon'] }}"></i>
                                        <span class="admin-nav-link-label">{{ $item['label'] }}</span>
                                    </span>
                                    @if (!empty($item['badge']))
                                        <span class="admin-badge {{ $item['badge_class'] }}">{{ $item['badge'] }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </nav>
                    </section>
                @endforeach

                <div class="admin-sidebar-user">
                    <div class="admin-sidebar-user-row">
                        <div class="admin-user-avatar">AD</div>
                        <div>
                            <div class="admin-user-name">管理者 太郎</div>
                            <div class="admin-user-role">SUPER ADMIN</div>
                        </div>
                    </div>
                    <button type="button" class="admin-logout-btn" onclick="if(confirm('ログアウトしますか？')) location.href='{{ route('login.demo') }}'">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>ログアウト</span>
                    </button>
                </div>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-header-left">
                    <button type="button" class="admin-menu-toggle" id="admin-menu-toggle" aria-label="メニューを開く">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="admin-header-title">{{ $pageTitle }}</div>
                </div>

                <div class="admin-header-right">
                    <div class="admin-search-wrap">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="text" class="admin-search-input" placeholder="ID、ユーザー名で検索...">
                    </div>
                    <button type="button" class="admin-header-icon" aria-label="通知">
                        <i class="fas fa-bell"></i>
                        <span class="admin-header-dot"></span>
                    </button>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-content-shell">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <script>
        (function () {
            var sidebar = document.getElementById('admin-sidebar');
            var overlay = document.getElementById('admin-mobile-overlay');
            var openBtn = document.getElementById('admin-menu-toggle');
            var closeBtn = document.getElementById('admin-sidebar-close');

            function openSidebar() {
                if (!sidebar || !overlay) return;
                sidebar.classList.add('is-open');
                overlay.classList.add('is-open');
            }

            function closeSidebar() {
                if (!sidebar || !overlay) return;
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-open');
            }

            if (openBtn) openBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (overlay) overlay.addEventListener('click', closeSidebar);
        })();
    </script>
    @stack('admin-scripts')
</body>
</html>

