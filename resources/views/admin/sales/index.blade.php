@extends('layouts.app')

@section('title', '売上管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">売上管理</h1>
        <p class="admin-description">
            サブスクリプション料金と仲介料の売上を集計・可視化する画面です。<br>
            現時点ではダミー指標のみ表示しており、後続で実際の課金データ連携を行う想定です。
        </p>

        <div class="sales-grid">
            <div class="sales-card">
                <h2>今月 サブスクリプション売上</h2>
                <p class="sales-amount">{{ number_format($summary['subscription_monthly_total']) }} 円</p>
            </div>
            <div class="sales-card">
                <h2>今月 仲介料売上</h2>
                <p class="sales-amount">{{ number_format($summary['commission_monthly_total']) }} 円</p>
            </div>
            <div class="sales-card">
                <h2>先月 サブスクリプション売上</h2>
                <p class="sales-amount">{{ number_format($summary['subscription_last_month_total']) }} 円</p>
            </div>
            <div class="sales-card">
                <h2>先月 仲介料売上</h2>
                <p class="sales-amount">{{ number_format($summary['commission_last_month_total']) }} 円</p>
            </div>
        </div>

        <p class="admin-note">
            ※ 実装時には、期間指定（年月や日付範囲）・CSV エクスポート・グラフ表示などを拡張予定です。
        </p>
    </div>

    <style>
        .admin-page {
            padding: 24px 0;
        }
        .admin-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #FDF0B2;
        }
        .admin-description {
            font-size: 0.9rem;
            color: #e5d4d4;
            margin-bottom: 18px;
            line-height: 1.6;
        }
        .sales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
        }
        .sales-card {
            padding: 14px 16px;
            border-radius: 14px;
            background: radial-gradient(circle at 0% 0%, rgba(56, 189, 248, 0.08), rgba(15, 23, 42, 0.95));
            border: 1px solid rgba(56, 189, 248, 0.5);
        }
        .sales-card h2 {
            font-size: 0.9rem;
            color: #e0f2fe;
            margin-bottom: 6px;
        }
        .sales-amount {
            font-size: 1.3rem;
            font-weight: 700;
            color: #7dd3fc;
        }
        .admin-note {
            margin-top: 16px;
            font-size: 0.8rem;
            color: #9ca3af;
        }
    </style>
@endsection

