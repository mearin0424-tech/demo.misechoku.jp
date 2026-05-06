@extends('layouts.app')

@section('title', '問い合わせ窓口')

@section('content')
<div class="support-form-page">
    <div class="support-form-header">
        <h1 class="support-form-title">問い合わせ窓口</h1>
        <p class="support-form-lead">
            ミセチョクに関するご質問・ご要望・不具合のご連絡などはこちらからお送りいただく想定です。<br>
            デモ環境のため、以下のフォームは送信されませんが、画面イメージとしてご確認いただけます。
        </p>
    </div>

    <div class="support-form-card">
        <div class="support-form-alert">
            <i class="fas fa-info-circle"></i>
            <span>このデモでは、送信ボタンを押しても実際の送信処理は行われません。</span>
        </div>

        <form onsubmit="event.preventDefault(); alert('デモ環境のため送信は行われません。');">
            <div class="support-form-group">
                <label for="support-type">お問い合わせ種別</label>
                <select id="support-type" name="type" disabled>
                    <option>アカウント・ログインについて</option>
                    <option>機能や使い方について</option>
                    <option>不具合の報告</option>
                    <option>ご意見・ご要望</option>
                    <option>その他</option>
                </select>
            </div>

            <div class="support-form-group">
                <label for="support-email">返信用メールアドレス</label>
                <input id="support-email" type="email" name="email" placeholder="example@mail.com" disabled>
            </div>

            <div class="support-form-group">
                <label for="support-body">お問い合わせ内容</label>
                <textarea id="support-body" name="body" rows="5" placeholder="できるだけ詳しい状況・日時・ご利用環境などをご記入ください。" disabled></textarea>
            </div>

            <button type="submit" class="support-form-submit" disabled>
                <i class="fas fa-paper-plane"></i>
                この内容で送信する（デモ）
            </button>
        </form>
    </div>

    <div class="support-form-faq">
        <h2 class="support-form-faq-title">よくある質問</h2>
        <div class="support-faq-list">
            <details class="support-faq-item">
                <summary class="support-faq-question">
                    <span>ログインできない場合はどうすれば良いですか？</span>
                    <i class="fas fa-chevron-down"></i>
                </summary>
                <div class="support-faq-answer">
                    メールアドレス・パスワードに誤りがないかをご確認ください。デモ環境では再設定機能は画面イメージのみです。
                </div>
            </details>
            <details class="support-faq-item">
                <summary class="support-faq-question">
                    <span>{{ $isCast ? 'お店からのオファーはどこで確認できますか？' : 'キャストからの応募やメッセージはどこで確認できますか？' }}</span>
                    <i class="fas fa-chevron-down"></i>
                </summary>
                <div class="support-faq-answer">
                    つながり（LIKES）タブとトーク画面から、やりとり中の相手を一覧で確認できます。
                </div>
            </details>
            <details class="support-faq-item">
                <summary class="support-faq-question">
                    <span>通知のオン／オフはどこで変更できますか？</span>
                    <i class="fas fa-chevron-down"></i>
                </summary>
                <div class="support-faq-answer">
                    サイドメニュー内の「SETTING &gt; 通知設定」から、リマインダー通知の受け取り設定を変更できます。
                </div>
            </details>
            <details class="support-faq-item">
                <summary class="support-faq-question">
                    <span>退会やアカウント削除はできますか？</span>
                    <i class="fas fa-chevron-down"></i>
                </summary>
                <div class="support-faq-answer">
                    サイドメニュー内の「SETTING &gt; アカウント設定」から退会手続きへ進めます。
                </div>
            </details>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.support-form-page {
    padding: 24px 16px 32px;
    color: #f9f5f5;
}
@media (min-width: 768px) {
    .support-form-page {
        padding: 32px 24px 40px;
    }
}

.support-form-header {
    margin-bottom: 24px;
}

.support-form-title {
    font-family: 'Shippori Mincho', 'Noto Sans JP', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 1.4rem;
    margin-bottom: 8px;
    color: var(--color-gold, #d4af37);
}

.support-form-lead {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #d1c1c1;
}

.support-form-card {
    background: rgba(20, 7, 15, 0.9);
    border-radius: 16px;
    padding: 16px 14px 18px;
    border: 1px solid rgba(212, 175, 55, 0.4);
}
.support-form-faq { margin-top: 16px; background: rgba(20, 7, 15, 0.9); border-radius: 16px; padding: 14px 12px; border: 1px solid rgba(212, 175, 55, 0.4); }
.support-form-faq-title { font-size: 1rem; margin-bottom: 8px; color: #f9f5f5; }
.support-faq-list { display: flex; flex-direction: column; gap: 10px; }
.support-faq-item { background: rgba(8, 4, 6, 0.75); border-radius: 12px; border: 1px solid rgba(212, 175, 55, 0.24); padding: 4px 8px; }
.support-faq-question { list-style: none; display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 0.88rem; padding: 10px 6px; cursor: pointer; }
.support-faq-question::-webkit-details-marker { display: none; }
.support-faq-question i { font-size: 0.8rem; opacity: 0.7; transition: transform 0.2s ease; }
.support-faq-item[open] .support-faq-question i { transform: rotate(180deg); }
.support-faq-answer { padding: 0 6px 10px; font-size: 0.82rem; line-height: 1.7; color: #efe3e3; }
@media (min-width: 768px) {
    .support-form-card {
        padding: 18px 20px 22px;
    }
}

.support-form-alert {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 0.8rem;
    padding: 8px 10px;
    border-radius: 8px;
    background: rgba(212, 175, 55, 0.12);
    color: #f5e9c4;
    margin-bottom: 16px;
}

.support-form-alert i {
    margin-top: 1px;
}

.support-form-group {
    margin-bottom: 14px;
}

.support-form-group label {
    display: block;
    font-size: 0.8rem;
    margin-bottom: 4px;
    color: #f9f5f5;
}

.support-form-group input,
.support-form-group select,
.support-form-group textarea {
    width: 100%;
    border-radius: 10px;
    border: 1px solid rgba(212, 175, 55, 0.4);
    background: rgba(8, 4, 6, 0.9);
    padding: 8px 10px;
    font-size: 0.85rem;
    color: #f9f5f5;
}

.support-form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.support-form-group input::placeholder,
.support-form-group textarea::placeholder {
    color: #9b8585;
}

.support-form-submit {
    width: 100%;
    margin-top: 4px;
    padding: 10px 12px;
    border-radius: 999px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    background: linear-gradient(135deg, #4a1a2a, #b91c1c);
    color: #f9f5f5;
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
@endpush

