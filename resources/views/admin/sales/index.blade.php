@extends('layouts.admin')

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
@endsection

