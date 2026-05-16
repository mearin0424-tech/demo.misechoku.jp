@extends('layouts.app')

@section('title', 'プラン設定')

@section('content')
<div class="setting-page">
    <div class="setting-header">
        <h1 class="setting-title">プラン設定（店舗向け）</h1>
        <p class="setting-lead">
            ミセチョクの利用プランを確認・変更する画面のイメージです。<br>
            デモ環境のため、プランを切り替えても実際の請求は発生しません。
        </p>
    </div>

    <div class="setting-card">
        <div class="plan-current">
            <div class="plan-current-label">現在のプラン</div>
            <div class="plan-current-name">デモプラン（無料）</div>
        </div>

        <div class="plan-list">
            <div class="plan-item plan-item--active">
                <div class="plan-item-header">
                    <div>
                        <div class="plan-name">デモプラン</div>
                        <div class="plan-price">¥0 / 月</div>
                    </div>
                    <span class="plan-badge">適用中</span>
                </div>
                <ul class="plan-features">
                    <li>基本的な画面遷移・UIの確認</li>
                    <li>ダミーデータによる検索・トーク体験</li>
                    <li>サポートコンテンツの閲覧</li>
                </ul>
            </div>

            <div class="plan-item">
                <div class="plan-item-header">
                    <div>
                        <div class="plan-name">スタンダードプラン（例）</div>
                        <div class="plan-price">¥xx,xxx / 月</div>
                    </div>
                    <span class="plan-badge plan-badge--disabled">デモのみ</span>
                </div>
                <ul class="plan-features">
                    <li>実際の応募・オファー管理</li>
                    <li>詳細な分析レポート</li>
                    <li>優先サポート</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.setting-page {
    padding: 24px 16px 32px;
    color: #f9f5f5;
}
@media (min-width: 768px) {
    .setting-page {
        padding: 32px 24px 40px;
    }
}

.setting-header {
    margin-bottom: 24px;
}

.setting-title {
    font-family: var(--font-sans);
    font-size: 1.4rem;
    margin-bottom: 8px;
    color: var(--color-gold, #d4af37);
}

.setting-lead {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #d1c1c1;
}

.setting-card {
    background: rgba(20, 7, 15, 0.9);
    border-radius: 16px;
    padding: 16px 14px 18px;
    border: 1px solid rgba(212, 175, 55, 0.4);
}
@media (min-width: 768px) {
    .setting-card {
        padding: 18px 20px 22px;
    }
}

.plan-current {
    margin-bottom: 16px;
    padding: 10px 10px;
    border-radius: 10px;
    background: rgba(37, 99, 235, 0.15);
}

.plan-current-label {
    font-size: 0.75rem;
    color: #bfdbfe;
}

.plan-current-name {
    font-size: 0.95rem;
    font-weight: 600;
    margin-top: 2px;
}

.plan-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.plan-item {
    border-radius: 14px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    padding: 12px 10px 10px;
    background: rgba(0, 0, 0, 0.35);
}

.plan-item--active {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.22), rgba(24, 16, 6, 0.95));
}

.plan-item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 6px;
}

.plan-name {
    font-size: 0.95rem;
}

.plan-price {
    font-size: 0.8rem;
    color: #d1c1c1;
}

.plan-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 999px;
    background: rgba(34, 197, 94, 0.18);
    color: #bbf7d0;
}

.plan-badge--disabled {
    background: rgba(148, 163, 184, 0.2);
    color: #e5e7eb;
}

.plan-features {
    list-style: none;
    padding-left: 0;
    margin: 0;
    font-size: 0.8rem;
    color: #efe3e3;
}

.plan-features li + li {
    margin-top: 4px;
}
</style>
@endpush

