@extends('layouts.app')

@section('title', 'アカウント設定')

@section('content')
<div class="setting-page">
    <div class="setting-header">
        <h1 class="setting-title">アカウント設定</h1>
        <p class="setting-lead">メールアドレス変更・パスワード変更・LINE連携・退会手続きに関する設定をまとめています。</p>
    </div>

    <section class="setting-section">
        <h2 class="setting-section-title">メールアドレス変更</h2>
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
                <label for="email-password">現在のパスワード</label>
                <input id="email-password" type="password" placeholder="セキュリティ確認のため入力" disabled>
            </div>
            <button type="submit" class="setting-submit" disabled>メールアドレスを変更する（デモ）</button>
        </form>
    </section>

    <section class="setting-section">
        <h2 class="setting-section-title">パスワード変更</h2>
        <form onsubmit="event.preventDefault(); alert('デモ環境のため変更は行われません。');">
            <div class="setting-form-group">
                <label for="current-password">現在のパスワード</label>
                <input id="current-password" type="password" placeholder="現在のパスワード" disabled>
            </div>
            <div class="setting-form-group">
                <label for="new-password">新しいパスワード</label>
                <input id="new-password" type="password" placeholder="半角英数字8文字以上を推奨" disabled>
            </div>
            <div class="setting-form-group">
                <label for="new-password-confirm">新しいパスワード（確認用）</label>
                <input id="new-password-confirm" type="password" placeholder="もう一度入力してください" disabled>
            </div>
            <button type="submit" class="setting-submit" disabled>パスワードを変更する（デモ）</button>
        </form>
    </section>

    <section class="setting-section">
        <h2 class="setting-section-title">LINE連携</h2>
        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">LINEでログイン・通知</div>
                <div class="setting-row-desc">
                    LINEと連携すると、LINEでログインでき、リマインダー通知をLINEにも送れます。
                    @if ($lineLinked ?? false)
                        <strong class="setting-linked">連携済み</strong>
                    @endif
                </div>
            </div>
            @if ($lineLinked ?? false)
                <button type="button" class="setting-btn setting-btn-unlink" disabled>連携解除（準備中）</button>
            @else
                <a href="{{ $lineLinkUrl ?? '#' }}" class="setting-btn setting-btn-line">LINEと連携</a>
            @endif
        </div>
    </section>

    <section class="setting-section">
        <h2 class="setting-section-title">退会手続き</h2>
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
            <label class="withdraw-check">
                <input type="checkbox" disabled>
                <span>退会後はアカウントを元に戻せないことを理解しました。</span>
            </label>
            <button type="submit" class="setting-submit setting-submit-danger" disabled>退会を申し込む（デモ）</button>
        </form>
    </section>
</div>
@endsection

@push('styles')
<style>
.setting-page { padding: 24px 16px 32px; color: #f9f5f5; }
@media (min-width: 768px) { .setting-page { padding: 32px 24px 40px; } }
.setting-header { margin-bottom: 24px; }
.setting-title { font-family: 'Shippori Mincho','Noto Sans JP',sans-serif; font-size: 1.4rem; margin-bottom: 8px; color: var(--color-gold, #d4af37); }
.setting-lead { font-size: 0.9rem; line-height: 1.6; color: #d1c1c1; }
.setting-section { margin-bottom: 18px; background: rgba(20, 7, 15, 0.9); border-radius: 16px; padding: 14px 12px 16px; border: 1px solid rgba(212, 175, 55, 0.4); }
@media (min-width: 768px) { .setting-section { padding: 16px 16px 18px; } }
.setting-section-title { font-size: 0.95rem; margin-bottom: 10px; color: #f9f5f5; }
.setting-form-group { margin-bottom: 12px; }
.setting-form-group label { display: block; font-size: 0.8rem; margin-bottom: 4px; color: #f9f5f5; }
.setting-form-group input, .setting-form-group textarea { width: 100%; border-radius: 10px; border: 1px solid rgba(212,175,55,0.4); background: rgba(8,4,6,0.9); padding: 8px 10px; font-size: 0.85rem; color: #f9f5f5; }
.setting-form-group textarea { resize: vertical; min-height: 100px; }
.setting-form-group input::placeholder, .setting-form-group textarea::placeholder { color: #9b8585; }
.setting-submit { width: 100%; margin-top: 4px; padding: 10px 12px; border-radius: 999px; border: none; font-size: 0.9rem; font-weight: 600; background: linear-gradient(135deg, #4b5563, #6b7280); color: #f9f5f5; opacity: 0.75; cursor: not-allowed; }
.setting-submit-danger { background: linear-gradient(135deg, #b91c1c, #7f1d1d); }
.setting-row { padding: 8px 0; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.setting-row-main { flex: 1; min-width: 0; }
.setting-row-label { font-size: 0.9rem; margin-bottom: 2px; }
.setting-row-desc { font-size: 0.78rem; color: #b69f9f; }
.setting-linked { color: var(--color-gold, #d4af37); }
.setting-btn { display: inline-block; padding: 10px 18px; border-radius: 12px; font-size: 0.88rem; font-weight: 600; text-decoration: none; border: none; transition: opacity 0.2s; cursor: pointer; }
.setting-btn:hover { opacity: 0.9; }
.setting-btn-line { background: #06c755; color: #fff; }
.setting-btn-unlink { background: #4b5563; color: #f9f5f5; cursor: not-allowed; opacity: 0.75; }
.withdraw-warning { display: flex; gap: 10px; padding: 8px 10px; border-radius: 8px; background: rgba(248,113,113,0.1); color: #fecaca; font-size: 0.8rem; margin-bottom: 14px; }
.withdraw-warning i { margin-top: 2px; }
.withdraw-warning-small { margin-top: 4px; font-size: 0.75rem; opacity: 0.8; }
.withdraw-check { display: flex; align-items: flex-start; gap: 8px; font-size: 0.8rem; color: #f9f5f5; margin-bottom: 10px; }
.withdraw-check input { margin-top: 3px; }
</style>
@endpush
