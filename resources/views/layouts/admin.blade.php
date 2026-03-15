@php
    $currentRoute = optional(request()->route())->getName();
    $pageTitle = trim($__env->yieldContent('admin_page_title')) ?: trim($__env->yieldContent('title')) ?: 'ダッシュボード';
    $menuGroups = [
        [
            'title' => 'オペレーション',
            'items' => [
                ['label' => '請求書発行', 'route' => 'admin.invoices.index', 'icon' => 'fa-file-invoice', 'badge' => null, 'badge_class' => ''],
                ['label' => '入金・振込管理', 'route' => 'admin.deposits.index', 'icon' => 'fa-money-bill-transfer', 'badge' => null, 'badge_class' => ''],
                ['label' => '本人・書類審査管理', 'route' => 'admin.verification.index', 'icon' => 'fa-shield-halved', 'badge' => '7', 'badge_class' => 'badge-gold'],
                ['label' => '入金・振込タスク管理', 'route' => 'admin.tasks.index', 'icon' => 'fa-wallet', 'badge' => '13', 'badge_class' => 'badge-red'],
                ['label' => '問合せ管理', 'route' => 'admin.inquiries.index', 'icon' => 'fa-triangle-exclamation', 'badge' => null, 'badge_class' => ''],
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
    ];
    $urgentItems = [
        ['title' => '【期限超過】リナの振込エラー対応', 'time' => '10分前', 'icon' => 'fa-triangle-exclamation', 'class' => 'is-danger'],
        ['title' => '【新規問合せ】THE GOLDSTONEより', 'time' => '1時間前', 'icon' => 'fa-comments', 'class' => 'is-warning'],
        ['title' => '【期限間近】愛華の本人確認審査', 'time' => '2時間前', 'icon' => 'fa-clock', 'class' => 'is-gold'],
    ];
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
    <style>
        :root {
            --admin-bg: #0d0405;
            --admin-sidebar: #080203;
            --admin-card: #150a0b;
            --admin-card-hover: #1a0c0e;
            --admin-header: rgba(13, 4, 5, 0.9);
            --admin-line: #2a1518;
            --admin-line-soft: rgba(230, 208, 128, 0.12);
            --admin-blue: #60a5fa;
            --admin-purple: #a78bfa;
            --admin-green: #34d399;
            --admin-yellow: #fbbf24;
            --admin-red: #f87171;
            --admin-gold: #e6d080;
            --admin-gold-strong: #c99b2e;
            --admin-text: #f5e6e6;
            --admin-sub: #bfaeaf;
            --admin-muted: #8a7577;
            --admin-accent: #f5e6e6;
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
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #2a1518;
            border-radius: 999px;
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
            width: 224px;
            flex: 0 0 224px;
            background: var(--admin-sidebar);
            border-right: 1px solid var(--admin-line-soft);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 60;
        }
        .admin-sidebar-header {
            min-height: 52px;
            padding: 14px 16px;
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
            letter-spacing: 0.12em;
        }
        .admin-brand-title {
            font-size: 1rem;
            color: var(--admin-text);
        }
        .admin-brand-badge {
            padding: 3px 6px;
            border-radius: 6px;
            background: linear-gradient(135deg, #e5c158, #b38a22);
            color: #120405;
            font-size: 0.55rem;
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
            padding: 16px 12px 18px;
        }
        .admin-home-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            text-decoration: none;
            background: rgba(230, 208, 128, 0.08);
            border: 1px solid rgba(230, 208, 128, 0.18);
            color: var(--admin-gold);
            margin-bottom: 18px;
            font-size: 0.82rem;
            font-weight: 700;
        }
        .admin-home-link.is-active {
            background: rgba(230, 208, 128, 0.12);
            border-color: rgba(230, 208, 128, 0.26);
        }
        .admin-nav-group {
            margin-bottom: 20px;
        }
        .admin-nav-group-title {
            font-size: 0.63rem;
            font-weight: 700;
            color: var(--admin-muted);
            letter-spacing: 0.12em;
            padding: 0 8px;
            margin-bottom: 6px;
        }
        .admin-nav-list {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .admin-nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 10px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--admin-muted);
            transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
            border: 1px solid transparent;
        }
        .admin-nav-link:hover,
        .admin-nav-link.is-active {
            background: var(--admin-card);
            color: var(--admin-gold);
            border-color: rgba(230, 208, 128, 0.12);
        }
        .admin-nav-link-main {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .admin-nav-link-label {
            font-size: 0.76rem;
            font-weight: 600;
        }
        .admin-badge {
            font-size: 0.56rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 999px;
        }
        .badge-gold {
            background: var(--admin-gold);
            color: #120405;
        }
        .badge-red {
            background: rgba(248, 113, 113, 0.2);
            color: #fff;
        }
        .admin-sidebar-user {
            margin: 14px 4px 0;
            padding: 14px;
            border-radius: 14px;
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
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: linear-gradient(135deg, #e5c158, #b38a22);
            color: #120405;
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
            font-size: 0.68rem;
            color: var(--admin-muted);
            letter-spacing: 0.08em;
        }
        .admin-logout-btn {
            width: 100%;
            padding: 9px 12px;
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
            margin-left: 224px;
            display: flex;
            flex-direction: column;
        }
        .admin-header {
            position: sticky;
            top: 0;
            z-index: 15;
            background: var(--admin-header);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--admin-line-soft);
            padding: 0 16px 0 18px;
            min-height: 52px;
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
        .admin-breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.74rem;
            font-weight: 700;
            color: var(--admin-muted);
        }
        .admin-breadcrumb-current {
            color: var(--admin-gold);
        }
        .admin-header-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .admin-search-wrap {
            position: relative;
            width: 192px;
        }
        .admin-search-wrap i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--admin-muted);
            font-size: 0.78rem;
        }
        .admin-search-input {
            width: 100%;
            padding: 8px 12px 8px 30px;
            border-radius: 8px;
            border: 1px solid var(--admin-line);
            background: var(--admin-card);
            color: var(--admin-text);
            font-size: 0.75rem;
            outline: none;
        }
        .admin-search-input:focus {
            border-color: rgba(230, 208, 128, 0.4);
        }
        .admin-search-input::placeholder {
            color: var(--admin-muted);
        }
        .admin-header-icon {
            position: relative;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }
        .admin-header-icon:hover {
            background: var(--admin-card);
            border-color: rgba(230, 208, 128, 0.12);
            color: var(--admin-gold);
        }
        .admin-header-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--admin-red);
            border: 2px solid var(--admin-bg);
        }
        .admin-task-popover {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 288px;
            background: var(--admin-card);
            border: 1px solid rgba(230, 208, 128, 0.24);
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            display: none;
            z-index: 80;
        }
        .admin-task-popover.is-open {
            display: block;
        }
        .admin-task-popover-head,
        .admin-task-popover-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 14px;
            background: rgba(0, 0, 0, 0.16);
            border-bottom: 1px solid var(--admin-line);
        }
        .admin-task-popover-foot {
            border-top: 1px solid var(--admin-line);
            border-bottom: 0;
        }
        .admin-task-popover-title {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }
        .admin-task-popover-badge {
            padding: 3px 8px;
            border-radius: 999px;
            background: rgba(248, 113, 113, 0.15);
            color: var(--admin-red);
            font-size: 0.64rem;
            font-weight: 700;
        }
        .admin-task-popover-list {
            max-height: 320px;
            overflow-y: auto;
        }
        .admin-task-item {
            display: flex;
            gap: 12px;
            padding: 12px 14px;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }
        .admin-task-item:hover {
            background: var(--admin-card-hover);
        }
        .admin-task-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }
        .admin-task-item-icon.is-danger {
            background: rgba(248, 113, 113, 0.12);
            color: var(--admin-red);
        }
        .admin-task-item-icon.is-warning {
            background: rgba(251, 191, 36, 0.12);
            color: var(--admin-yellow);
        }
        .admin-task-item-icon.is-gold {
            background: rgba(230, 208, 128, 0.12);
            color: var(--admin-gold);
        }
        .admin-task-item-title {
            margin: 0 0 4px;
            font-size: 0.74rem;
            font-weight: 700;
            color: var(--admin-text);
        }
        .admin-task-item-time {
            margin: 0;
            font-size: 0.65rem;
            color: var(--admin-muted);
        }
        .admin-task-popover-link {
            width: 100%;
            border: 0;
            background: transparent;
            color: var(--admin-gold);
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
            padding: 0;
            text-align: center;
        }
        .admin-header-avatar {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: linear-gradient(135deg, #e5c158, #b38a22);
            color: #120405;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: 700;
        }
        .admin-content {
            padding: 16px;
        }
        .admin-content-shell {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
        }
        .admin-page {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .admin-title {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--admin-text);
            letter-spacing: 0.02em;
        }
        .admin-description {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.7;
            color: var(--admin-sub);
        }
        .admin-alert {
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(230, 208, 128, 0.08);
            border: 1px solid rgba(230, 208, 128, 0.2);
            color: #f6ead6;
            font-size: 0.88rem;
        }
        .admin-panel {
            border-radius: 18px;
            border: 1px solid rgba(230, 208, 128, 0.1);
            background: var(--admin-card);
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.28);
            padding: 18px;
        }
        .admin-panel-title {
            margin: 0 0 14px;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--admin-text);
        }
        .table-wrapper {
            overflow-x: auto;
            border-radius: 18px;
            border: 1px solid rgba(230, 208, 128, 0.1);
            background: var(--admin-card);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.28);
        }
        .admin-table {
            width: 100%;
            min-width: 680px;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .admin-table thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--admin-muted);
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid rgba(230, 208, 128, 0.08);
            white-space: nowrap;
        }
        .admin-table tbody td {
            padding: 13px 16px;
            color: var(--admin-text);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            white-space: nowrap;
            vertical-align: middle;
        }
        .admin-table tbody tr:hover {
            background: var(--admin-card-hover);
        }
        .text-center {
            text-align: center;
        }
        .admin-note {
            font-size: 0.8rem;
            color: var(--admin-muted);
        }
        .admin-grid,
        .sales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
        }
        .admin-card,
        .sales-card {
            padding: 16px;
            border-radius: 16px;
            background: var(--admin-card);
            border: 1px solid rgba(230, 208, 128, 0.1);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.24);
        }
        .admin-card h2,
        .sales-card h2 {
            margin: 0 0 8px;
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--admin-text);
        }
        .admin-card p,
        .sales-card p {
            margin: 0;
            color: var(--admin-sub);
            line-height: 1.6;
            font-size: 0.84rem;
        }
        .sales-amount {
            margin-top: 10px;
            font-size: 1.5rem;
            font-weight: 800;
            color: #f3f4f6;
            letter-spacing: 0.02em;
        }
        .admin-form-row {
            margin-bottom: 14px;
        }
        .admin-label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--admin-sub);
        }
        .admin-input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--admin-line);
            background: rgba(255, 255, 255, 0.03);
            color: var(--admin-text);
            font-size: 0.88rem;
            outline: none;
        }
        .admin-input:focus {
            border-color: rgba(230, 208, 128, 0.3);
            box-shadow: 0 0 0 3px rgba(230, 208, 128, 0.07);
        }
        .admin-form-actions,
        .management-actions {
            margin-top: 14px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-action.manage {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 12px;
            border: 1px solid rgba(230, 208, 128, 0.22);
            background: rgba(230, 208, 128, 0.08);
            color: var(--admin-gold);
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-action.manage:hover {
            background: var(--admin-gold);
            color: #120405;
        }
        .text-xs {
            font-size: 0.75rem;
        }
        .text-gray-400 {
            color: var(--admin-muted);
        }
        .admin-mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(3px);
            z-index: 50;
        }
        .admin-mobile-overlay.is-open {
            display: block;
        }
        @media (max-width: 1023px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.22s ease;
                width: min(86vw, 320px);
                z-index: 60;
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
                padding: 14px;
            }
        }
        @media (max-width: 640px) {
            .admin-header {
                padding: 0 12px;
            }
            .admin-breadcrumb {
                font-size: 0.68rem;
            }
            .admin-content {
                padding: 12px;
            }
            .admin-task-popover {
                width: min(88vw, 288px);
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
                        <button type="button" class="admin-header-icon" id="admin-task-toggle" aria-label="緊急タスク">
                            <i class="fas fa-list-check"></i>
                            <span class="admin-header-dot"></span>
                        </button>
                        <div id="admin-task-popover" class="admin-task-popover" aria-hidden="true">
                            <div class="admin-task-popover-head">
                                <div class="admin-task-popover-title">緊急タスク・問合せ</div>
                                <div class="admin-task-popover-badge">{{ count($urgentItems) }}件</div>
                            </div>
                            <div class="admin-task-popover-list">
                                @foreach ($urgentItems as $item)
                                    <a href="{{ route('admin.tasks.index') }}" class="admin-task-item">
                                        <span class="admin-task-item-icon {{ $item['class'] }}">
                                            <i class="fas {{ $item['icon'] }}"></i>
                                        </span>
                                        <span>
                                            <span class="admin-task-item-title">{{ $item['title'] }}</span>
                                            <span class="admin-task-item-time">{{ $item['time'] }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                            <div class="admin-task-popover-foot">
                                <a href="{{ route('admin.tasks.index') }}" class="admin-task-popover-link">タスク管理画面へ</a>
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
            var taskToggle = document.getElementById('admin-task-toggle');
            var taskPopover = document.getElementById('admin-task-popover');

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
    @stack('admin-scripts')
</body>
</html>

