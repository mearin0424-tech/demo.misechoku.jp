@extends('layouts.app')

@section('title', '退会手続き')

@section('content')
<div class="setting-page">
    <div class="setting-header">
        <h1 class="setting-title">退会手続き</h1>
        <p class="setting-lead">
            ミセチョクからの退会を申し込む画面のイメージです。<br>
            デモ環境のため、実際にアカウントが削除されることはありません。
        </p>
    </div>

    <div class="setting-card">
        <div class="withdraw-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <p>退会すると、これまでのやりとり履歴やプロフィール情報などが閲覧できなくなる想定です。</p>
                <p class="withdraw-warning-small">実際の運用時には、利用規約・個人情報保護方針に基づき一定期間データを保管する場合があります。</p>
            </div>
        </div>

        <form onsubmit="event.preventDefault(); alert('デモ環境のため退会処理は行われません。');">
            <div class="setting-form-group">
                <label for="reason">退会理由（任意）</label>
                <textarea id="reason" rows="4" placeholder="サービス改善のため、差し支えなければ理由をお聞かせください。" disabled></textarea>
            </div>

            <div class="setting-form-group">
                <label class="withdraw-check">
                    <input type="checkbox" disabled>
                    <span>退会後はアカウントを元に戻せないことを理解しました。</span>
                </label>
            </div>

            <button type="submit" class="setting-submit setting-submit--danger" disabled>
                退会を申し込む（デモ）
            </button>
        </form>
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

.withdraw-warning {
    display: flex;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 8px;
    background: rgba(248, 113, 113, 0.1);
    color: #fecaca;
    font-size: 0.8rem;
    margin-bottom: 14px;
}

.withdraw-warning i {
    margin-top: 2px;
}

.withdraw-warning-small {
    margin-top: 4px;
    font-size: 0.75rem;
    opacity: 0.8;
}

.setting-form-group {
    margin-bottom: 14px;
}

.setting-form-group label {
    display: block;
    font-size: 0.8rem;
    margin-bottom: 4px;
    color: #f9f5f5;
}

.setting-form-group textarea {
    width: 100%;
    border-radius: 10px;
    border: 1px solid rgba(212, 175, 55, 0.4);
    background: rgba(8, 4, 6, 0.9);
    padding: 8px 10px;
    font-size: 0.85rem;
    color: #f9f5f5;
    resize: vertical;
    min-height: 100px;
}

.setting-form-group textarea::placeholder {
    color: #9b8585;
}

.withdraw-check {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 0.8rem;
    color: #f9f5f5;
}

.withdraw-check input {
    margin-top: 3px;
}

.setting-submit {
    width: 100%;
    margin-top: 4px;
    padding: 10px 12px;
    border-radius: 999px;
    border: none;
    font-size: 0.9rem;
    font-weight: 600;
    color: #f9f5f5;
    opacity: 0.75;
    cursor: not-allowed;
}

.setting-submit--danger {
    background: linear-gradient(135deg, #b91c1c, #7f1d1d);
}
</style>
@endpush

