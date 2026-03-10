@extends('layouts.app')

@section('title', '通知設定')

@section('content')
<div class="setting-page">
    <div class="setting-header">
        <h1 class="setting-title">通知設定</h1>
        <p class="setting-lead">
            ミセチョクからのお知らせやトーク通知の受け取り方法を設定する画面のイメージです。<br>
            デモ環境のため、オン・オフを切り替えても実際の通知状態は変わりません。
        </p>
    </div>

    <section class="setting-section">
        <h2 class="setting-section-title">プッシュ通知</h2>
        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">新着メッセージ</div>
                <div class="setting-row-desc">トークで新しいメッセージを受信したときに通知します。</div>
            </div>
            <button type="button" class="setting-toggle setting-toggle--demo" aria-pressed="true">
                ON
            </button>
        </div>

        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">{{ $isCast ? 'お店からのオファー' : 'キャストからの応募・問い合わせ' }}</div>
                <div class="setting-row-desc">
                    {{ $isCast ? '気になるお店からオファーが届いたときに通知します。' : '候補のキャストから応募やメッセージが届いたときに通知します。' }}
                </div>
            </div>
            <button type="button" class="setting-toggle setting-toggle--demo" aria-pressed="true">
                ON
            </button>
        </div>
    </section>

    <section class="setting-section">
        <h2 class="setting-section-title">メール通知</h2>
        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">重要なお知らせ</div>
                <div class="setting-row-desc">メンテナンス情報や重要なお知らせのみメールで受け取ります。</div>
            </div>
            <button type="button" class="setting-toggle setting-toggle--demo" aria-pressed="false">
                OFF
            </button>
        </div>
    </section>
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
    font-family: 'Shippori Mincho', 'Noto Sans JP', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 1.4rem;
    margin-bottom: 8px;
    color: var(--color-gold, #d4af37);
}

.setting-lead {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #d1c1c1;
}

.setting-section {
    margin-bottom: 18px;
    background: rgba(20, 7, 15, 0.9);
    border-radius: 16px;
    padding: 14px 12px 10px;
    border: 1px solid rgba(212, 175, 55, 0.4);
}
@media (min-width: 768px) {
    .setting-section {
        padding: 16px 16px 12px;
    }
}

.setting-section-title {
    font-size: 0.95rem;
    margin-bottom: 8px;
    color: #f9f5f5;
}

.setting-row {
    padding: 10px 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.setting-row + .setting-row {
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.setting-row-main {
    flex: 1;
    min-width: 0;
}

.setting-row-label {
    font-size: 0.9rem;
    margin-bottom: 2px;
}

.setting-row-desc {
    font-size: 0.78rem;
    color: #b69f9f;
}

.setting-toggle {
    min-width: 70px;
    border-radius: 999px;
    border: none;
    padding: 6px 10px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: default;
    text-align: center;
}

.setting-toggle--demo[aria-pressed="true"] {
    background: linear-gradient(135deg, #15803d, #16a34a);
    color: #f9f5f5;
}

.setting-toggle--demo[aria-pressed="false"] {
    background: rgba(55, 65, 81, 0.8);
    color: #e5e7eb;
}
</style>
@endpush

