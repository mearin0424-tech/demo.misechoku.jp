@extends('layouts.app')

@section('title', 'メールアドレス変更')

@section('content')
<div class="setting-page">
    <div class="setting-header">
        <h1 class="setting-title">メールアドレス変更</h1>
        <p class="setting-lead">
            ログイン用メールアドレスを変更する画面のイメージです。<br>
            デモ環境のため、入力内容を送信しても実際の変更は行われません。
        </p>
    </div>

    <div class="setting-card">
        <form onsubmit="event.preventDefault(); alert('デモ環境のため変更は行われません。');">
            <div class="setting-form-group">
                <label for="current-email">現在のメールアドレス</label>
                <input id="current-email" type="email" value="demo@example.com" disabled>
            </div>

            <div class="setting-form-group">
                <label for="new-email">新しいメールアドレス</label>
                <input id="new-email" type="email" placeholder="new@example.com" disabled>
            </div>

            <div class="setting-form-group">
                <label for="password">現在のパスワード</label>
                <input id="password" type="password" placeholder="セキュリティ確認のため入力" disabled>
            </div>

            <button type="submit" class="setting-submit" disabled>
                メールアドレスを変更する（デモ）
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

.setting-form-group {
    margin-bottom: 14px;
}

.setting-form-group label {
    display: block;
    font-size: 0.8rem;
    margin-bottom: 4px;
    color: #f9f5f5;
}

.setting-form-group input {
    width: 100%;
    border-radius: 10px;
    border: 1px solid rgba(212, 175, 55, 0.4);
    background: rgba(8, 4, 6, 0.9);
    padding: 8px 10px;
    font-size: 0.85rem;
    color: #f9f5f5;
}

.setting-form-group input::placeholder {
    color: #9b8585;
}

.setting-submit {
    width: 100%;
    margin-top: 4px;
    padding: 10px 12px;
    border-radius: 999px;
    border: none;
    font-size: 0.9rem;
    font-weight: 600;
    background: linear-gradient(135deg, #4b5563, #6b7280);
    color: #f9f5f5;
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
@endpush

