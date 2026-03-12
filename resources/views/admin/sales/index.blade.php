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
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #e5e7eb;
        }
        .admin-description {
            font-size: 0.9rem;
            color: #cbd5f5;
            margin-bottom: 16px;
            line-height: 1.6;
        }
        .sales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .sales-card {
            padding: 12px 14px;
            border-radius: 8px;
            background: rgba(17, 24, 39, 0.95);
            border: 1px solid rgba(55, 65, 81, 0.9);
        }
        .sales-card h2 {
            font-size: 0.85rem;
            color: #e5e7eb;
            margin-bottom: 4px;
        }
        .sales-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: #bfdbfe;
        }
        .admin-note {
            margin-top: 14px;
            font-size: 0.8rem;
            color: #9ca3af;
        }
    </style>
@endsection

