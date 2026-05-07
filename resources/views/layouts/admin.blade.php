@php
    $currentRoute = optional(request()->route())->getName();
    $pageTitle = trim($__env->yieldContent('admin_page_title')) ?: trim($__env->yieldContent('title')) ?: 'ダッシュボード';
    $menuGroups = [
        [
            'title' => 'オペレーション',
            'items' => [
                ['label' => '請求書発行', 'route' => 'admin.invoices.index', 'icon' => 'fa-file-invoice', 'badge' => null, 'badge_class' => ''],
                ['label' => '入金確認・振込', 'route' => 'admin.deposits.index', 'icon' => 'fa-money-bill-wave', 'badge' => null, 'badge_class' => ''],
                ['label' => '身分証・書類審査', 'route' => 'admin.verification.index', 'icon' => 'fa-id-card', 'badge' => null, 'badge_class' => ''],
                ['label' => '問合せ対応', 'route' => 'admin.inquiries.index', 'icon' => 'fa-triangle-exclamation', 'badge' => null, 'badge_class' => ''],
            ],
        ],
        [
            'title' => 'コンテンツ',
            'items' => [
                ['label' => 'お知らせ管理', 'route' => 'admin.notices.index', 'icon' => 'fa-bell', 'badge' => null, 'badge_class' => ''],
                ['label' => 'コラム管理', 'route' => 'admin.columns.index', 'icon' => 'fa-pen-nib', 'badge' => null, 'badge_class' => ''],
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
        [
            'title' => 'アカウント管理',
            'items' => [
                ['label' => '店舗管理', 'route' => 'admin.shops.index', 'icon' => 'fa-building', 'badge' => null, 'badge_class' => ''],
                ['label' => 'キャスト管理', 'route' => 'admin.casts.index', 'icon' => 'fa-users', 'badge' => null, 'badge_class' => ''],
                ['label' => '運営アカウント管理', 'route' => 'admin.admin-accounts.index', 'icon' => 'fa-user-gear', 'badge' => null, 'badge_class' => ''],
            ],
        ],
        [
            'title' => '規約管理',
            'items' => [
                ['label' => '運営協会', 'route' => 'admin.policies.show', 'route_params' => ['key' => 'about'], 'icon' => 'fa-landmark', 'badge' => null, 'badge_class' => ''],
                ['label' => '利用規約', 'route' => 'admin.policies.show', 'route_params' => ['key' => 'terms'], 'icon' => 'fa-file-contract', 'badge' => null, 'badge_class' => ''],
                ['label' => 'プライバシーポリシー', 'route' => 'admin.policies.show', 'route_params' => ['key' => 'privacy'], 'icon' => 'fa-user-shield', 'badge' => null, 'badge_class' => ''],
            ],
        ],
    ];
    $opBadges = $adminOperationBadges ?? [];
    $opAchievements = $adminOperationAchievements ?? [];
    foreach ($menuGroups as &$group) {
        if (($group['title'] ?? '') !== 'オペレーション') {
            continue;
        }
        foreach ($group['items'] as &$item) {
            $route = $item['route'] ?? '';
            if (! isset($opBadges[$route])) {
                continue;
            }
            $n = (int) $opBadges[$route];
            $item['badge'] = $n > 0 ? (string) $n : null;
            $item['badge_class'] = $n > 0 ? 'badge-gold' : '';
        }
        unset($item);
    }
    unset($group);
    $sectionMap = [
        'admin.dashboard' => 'ダッシュボード',
        'admin.invoices.*' => 'オペレーション',
        'admin.deposits.*' => 'オペレーション',
        'admin.verification.*' => 'オペレーション',
        'admin.tasks.*' => 'オペレーション',
        'admin.inquiries.*' => 'オペレーション',
        'admin.notices.*' => 'コンテンツ',
        'admin.columns.*' => 'コンテンツ',
        'admin.ngwords.*' => 'マスタ設定',
        'admin.masters.*' => 'マスタ設定',
        'admin.sales.*' => 'アナリティクス',
        'admin.shops.*' => 'アカウント管理',
        'admin.casts.*' => 'アカウント管理',
        'admin.admin-accounts.*' => 'アカウント管理',
        'admin.bank.*' => 'アカウント管理',
        'admin.policies.*' => '規約管理',
    ];
    $headerSection = '管理画面';
    foreach ($sectionMap as $pattern => $label) {
        if (request()->routeIs($pattern)) {
            $headerSection = $label;
            break;
        }
    }
    $headerDetail = $currentRoute === 'admin.dashboard' ? '全体サマリー' : $pageTitle;
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | 管理画面</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Noto+Sans+JP:wght@300;400;500;600;700;800&family=Shippori+Mincho:wght@500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
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
                        <div class="admin-nav-group-title">
                            <span class="admin-nav-group-title-text">{{ $group['title'] }}</span>
                            <span class="admin-nav-group-title-line" aria-hidden="true"></span>
                        </div>
                        <nav class="admin-nav-list">
                            @foreach ($group['items'] as $item)
                                @php
                                    $navRouteParams = $item['route_params'] ?? [];
                                    $navHref = route($item['route'], $navRouteParams);
                                    $navActive = isset($item['route_params']['key'])
                                        ? request()->routeIs('admin.policies.show') && (string) request()->route('key') === (string) $item['route_params']['key']
                                        : request()->routeIs($item['route']);
                                @endphp
                                <a href="{{ $navHref }}" class="admin-nav-link {{ $navActive ? 'is-active' : '' }}">
                                    <span class="admin-nav-link-main">
                                        <i class="fas {{ $item['icon'] }}"></i>
                                        @if(($group['title'] ?? '') === 'オペレーション')
                                            <span class="admin-nav-link-text-wrap">
                                                <span class="admin-nav-link-label">{{ $item['label'] }}</span>
                                                <span class="admin-nav-link-achievement">実績 {{ number_format((int) ($opAchievements[$item['route']] ?? 0)) }}件</span>
                                            </span>
                                        @else
                                            <span class="admin-nav-link-label">{{ $item['label'] }}</span>
                                        @endif
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
                    <div class="admin-breadcrumb">
                        <span class="admin-breadcrumb-current">{{ $headerSection }}</span>
                        <span>/</span>
                        <span>{{ $headerDetail }}</span>
                    </div>
                </div>

                <div class="admin-header-right">
                    <div class="admin-search-wrap">
                        <i class="fas fa-magnifying-glass"></i>
                        <input type="text" class="admin-search-input" placeholder="検索...">
                    </div>
                    <div style="position: relative;">
                        @php
                            $nAdminNotify = (int) ($adminNotificationCount ?? 0);
                        @endphp
                        <button type="button" class="admin-header-icon" id="admin-notification-toggle" aria-label="通知">
                            <i class="fas fa-bell"></i>
                            @if ($nAdminNotify > 0)
                                @if ($nAdminNotify > 99)
                                    <span class="admin-header-notification-count" aria-hidden="true">99+</span>
                                @else
                                    <span class="admin-header-notification-count" aria-hidden="true">{{ $nAdminNotify }}</span>
                                @endif
                            @endif
                        </button>
                        <div id="admin-notification-popover" class="admin-task-popover" aria-hidden="true">
                            <div class="admin-task-popover-head">
                                <div class="admin-task-popover-title">通知</div>
                                <div class="admin-task-popover-badge">{{ $nAdminNotify }}件</div>
                            </div>
                            <div class="admin-task-popover-list">
                                @forelse ($adminNotifications ?? [] as $item)
                                    <a href="{{ $item['url'] }}" class="admin-task-item">
                                        <span class="admin-task-item-icon {{ $item['class'] }}">
                                            <i class="fas {{ $item['icon'] }}"></i>
                                        </span>
                                        <span>
                                            <span class="admin-task-item-title">{{ $item['title'] }}</span>
                                            <span class="admin-task-item-time">{{ $item['time_label'] }}</span>
                                        </span>
                                    </a>
                                @empty
                                    <div class="admin-task-popover-empty">現在、表示する通知はありません。</div>
                                @endforelse
                            </div>
                            <div class="admin-task-popover-foot">
                                <a href="{{ route('admin.dashboard') }}" class="admin-task-popover-link">ダッシュボードで全体を確認</a>
                            </div>
                        </div>
                    </div>
                    <span class="admin-header-avatar">AD</span>
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
            var taskToggle = document.getElementById('admin-notification-toggle');
            var taskPopover = document.getElementById('admin-notification-popover');

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

            function closeTaskPopover() {
                if (!taskPopover) return;
                taskPopover.classList.remove('is-open');
                taskPopover.setAttribute('aria-hidden', 'true');
            }

            function toggleTaskPopover(event) {
                if (!taskPopover) return;
                if (event) event.stopPropagation();
                var isOpen = taskPopover.classList.toggle('is-open');
                taskPopover.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            }

            if (openBtn) openBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (overlay) {
                overlay.addEventListener('click', function () {
                    closeSidebar();
                    closeTaskPopover();
                });
            }
            if (taskToggle) taskToggle.addEventListener('click', toggleTaskPopover);
            document.addEventListener('click', function (event) {
                if (!taskPopover || !taskToggle) return;
                if (taskPopover.contains(event.target) || taskToggle.contains(event.target)) return;
                closeTaskPopover();
            });
        })();
    </script>
    @include('partials.bank-autocomplete-scripts')
    @stack('admin-scripts')
</body>
</html>
