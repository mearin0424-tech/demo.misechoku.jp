@extends('layouts.app')

@section('title', '管理ダッシュボード')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">管理ダッシュボード</h1>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        <section class="admin-kpi-row">
            <div class="admin-kpi-card">
                <div class="admin-kpi-label">本日の新規入金依頼</div>
                <div class="admin-kpi-value">-</div>
            </div>
            <div class="admin-kpi-card">
                <div class="admin-kpi-label">今月の売上（サブスク）</div>
                <div class="admin-kpi-value">-</div>
            </div>
            <div class="admin-kpi-card">
                <div class="admin-kpi-label">未対応問い合わせ</div>
                <div class="admin-kpi-value">-</div>
            </div>
        </section>

        <section class="admin-layout-grid">
            <div class="admin-panel">
                <h2 class="admin-panel-title">アカウント・店舗/キャスト</h2>
                <ul class="admin-link-list">
                    <li><a href="{{ route('bk.shops.index') }}">店舗管理</a></li>
                    <li><a href="{{ route('bk.casts.index') }}">キャスト管理</a></li>
                    <li><a href="{{ route('bk.admin-accounts.index') }}">運営アカウント管理</a></li>
                </ul>
            </div>

            <div class="admin-panel">
                <h2 class="admin-panel-title">オペレーション</h2>
                <ul class="admin-link-list">
                    <li><a href="{{ route('bk.deposits.index') }}">入金・振込管理</a></li>
                    <li><a href="{{ route('bk.tasks.index') }}">請求・振込タスク管理</a></li>
                    <li><a href="{{ route('bk.sales.index') }}">売上管理</a></li>
                    <li><a href="{{ route('bk.inquiries.index') }}">問合せ管理</a></li>
                </ul>
            </div>

            <div class="admin-panel">
                <h2 class="admin-panel-title">マスタ・コンテンツ</h2>
                <ul class="admin-link-list">
                    <li><a href="{{ route('bk.masters.index') }}">マスタ設定管理</a></li>
                    <li><a href="{{ route('bk.ngwords.index') }}">NGワード管理</a></li>
                    <li><a href="{{ route('bk.notices.index') }}">お知らせ管理</a></li>
                    <li><a href="{{ route('bk.columns.index') }}">コラム管理</a></li>
                </ul>
            </div>
        </section>
    </div>

    <style>
        .admin-page {
            padding: 24px 0;
        }
        .admin-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #e5e7eb;
        }
        .admin-alert {
            background: rgba(55, 65, 81, 0.6);
            border: 1px solid rgba(156, 163, 175, 0.9);
            color: #e5e7eb;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 14px;
            font-size: 0.85rem;
        }
        .admin-kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .admin-kpi-card {
            padding: 10px 12px;
            border-radius: 8px;
            background: rgba(31, 41, 55, 0.9);
            border: 1px solid rgba(55, 65, 81, 0.9);
        }
        .admin-kpi-label {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .admin-kpi-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #f9fafb;
        }
        .admin-layout-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }
        .admin-panel {
            padding: 12px 14px;
            border-radius: 8px;
            background: rgba(17, 24, 39, 0.9);
            border: 1px solid rgba(55, 65, 81, 0.9);
        }
        .admin-panel-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #e5e7eb;
            margin-bottom: 8px;
        }
        .admin-link-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .admin-link-list a {
            font-size: 0.85rem;
            color: #bfdbfe;
            text-decoration: none;
        }
        .admin-link-list a:hover {
            text-decoration: underline;
        }
    </style>
@endsection

