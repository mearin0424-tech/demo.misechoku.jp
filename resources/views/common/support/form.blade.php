@extends('layouts.app-v2')

@section('title', '問い合わせ窓口')

@section('content')
<div class="support-form-page">
    <div class="support-form-header">
        {{-- タイトルはヘッダー中央に表示（統一方針）。ページ内はリード文のみ --}}
        <p class="page-lead">
            ミセチョクに関するご質問・ご要望・不具合のご連絡などはこちらからお送りください。<br>
            内容を確認のうえ、ご記入いただいたメールアドレス宛に運営よりご返信いたします。
        </p>
    </div>

    @if(session('support_inquiry_success'))
        <div class="support-form-success" role="status">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('support_inquiry_success') }}</span>
        </div>
    @endif

    <div class="support-form-card">
        @if ($errors->any())
            <div class="support-form-errors" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pages.support.form.submit') }}" novalidate>
            @csrf

            <div class="support-form-group">
                <label for="support-type">お問い合わせ種別<span class="req">必須</span></label>
                <select id="support-type" name="category" required>
                    @foreach(\App\Models\SupportInquiry::CATEGORY_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="support-form-group">
                <label for="support-email">返信用メールアドレス<span class="req">必須</span></label>
                <input id="support-email" type="email" name="email" maxlength="255" required
                       placeholder="example@mail.com" value="{{ old('email') }}"
                       autocomplete="email">
            </div>

            <div class="support-form-group">
                <label for="support-body">お問い合わせ内容<span class="req">必須</span></label>
                <textarea id="support-body" name="body" rows="6" minlength="10" maxlength="2000" required
                          placeholder="できるだけ詳しい状況・日時・ご利用環境などをご記入ください。">{{ old('body') }}</textarea>
                <p class="support-form-hint">10〜2000 文字。バグ報告の場合は端末・ブラウザ・発生時刻も記載してください。</p>
            </div>

            <button type="submit" class="support-form-submit">
                <i class="fas fa-paper-plane"></i>
                この内容で送信する
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
                    メールアドレス・パスワードに誤りがないかをご確認ください。それでも解決しない場合は、上記フォームより「アカウント・ログインについて」を選択しお問い合わせください。
                </div>
            </details>
            <details class="support-faq-item">
                <summary class="support-faq-question">
                    <span>{{ $isCast ? 'お店からのオファーはどこで確認できますか？' : 'キャストからの応募やメッセージはどこで確認できますか？' }}</span>
                    <i class="fas fa-chevron-down"></i>
                </summary>
                <div class="support-faq-answer">
                    つながり（KEEPS）タブとトーク画面から、やりとり中の相手を一覧で確認できます。
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
    color: #f5f5f5;
}
@media (min-width: 768px) {
    .support-form-page {
        padding: 32px 24px 40px;
    }
}

.support-form-header {
    margin-bottom: 20px;
}

.support-form-title {
    font-family: var(--font-sans);
    font-size: 1.4rem;
    margin-bottom: 8px;
    color: var(--color-gold, #a78bfa);
}

.support-form-lead {
    font-size: 0.9rem;
    line-height: 1.65;
    color: #c0c0c0;
}

.support-form-success {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 16px;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid rgba(110, 231, 183, 0.5);
    background: rgba(16, 185, 129, 0.10);
    color: #a7f3d0;
    font-size: 0.86rem;
    line-height: 1.55;
}
.support-form-success i { margin-top: 1px; }

.support-form-errors {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 14px;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid rgba(248, 113, 113, 0.5);
    background: rgba(220, 38, 38, 0.08);
    color: #fecaca;
    font-size: 0.82rem;
    line-height: 1.55;
}
.support-form-errors ul { margin: 0; padding-left: 4px; list-style: none; display: flex; flex-direction: column; gap: 2px; }

.support-form-card {
    background: rgba(20, 7, 15, 0.9);
    border-radius: 16px;
    padding: 16px 14px 18px;
    border: 1px solid rgba(168, 85, 247, 0.4);
}
.support-form-faq { margin-top: 16px; background: rgba(20, 7, 15, 0.9); border-radius: 16px; padding: 14px 12px; border: 1px solid rgba(168, 85, 247, 0.4); }
.support-form-faq-title { font-size: 1rem; margin-bottom: 8px; color: #f5f5f5; }
.support-faq-list { display: flex; flex-direction: column; gap: 10px; }
.support-faq-item { background: rgba(8, 4, 6, 0.75); border-radius: 12px; border: 1px solid rgba(168, 85, 247, 0.24); padding: 4px 8px; }
.support-faq-question { list-style: none; display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 0.88rem; padding: 10px 6px; cursor: pointer; }
.support-faq-question::-webkit-details-marker { display: none; }
.support-faq-question i { font-size: 0.8rem; opacity: 0.7; transition: transform 0.2s ease; }
.support-faq-item[open] .support-faq-question i { transform: rotate(180deg); }
.support-faq-answer { padding: 0 6px 10px; font-size: 0.82rem; line-height: 1.65; color: #d4d4d4; }
@media (min-width: 768px) {
    .support-form-card {
        padding: 18px 20px 22px;
    }
}

.support-form-group {
    margin-bottom: 14px;
}

.support-form-group label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 4px;
    color: #f5f5f5;
}
.support-form-group label .req {
    margin-left: 6px;
    font-size: 0.66rem;
    font-weight: 700;
    color: #fca5a5;
    background: rgba(220, 38, 38, 0.12);
    padding: 1px 6px;
    border-radius: 6px;
    vertical-align: middle;
}

.support-form-group input,
.support-form-group select,
.support-form-group textarea {
    width: 100%;
    border-radius: 10px;
    border: 1px solid rgba(168, 85, 247, 0.4);
    background: rgba(8, 4, 6, 0.9);
    padding: 9px 11px;
    font-size: 0.88rem;
    color: #f5f5f5;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.support-form-group input:focus,
.support-form-group select:focus,
.support-form-group textarea:focus {
    outline: none;
    border-color: var(--accent, #d670a2);
    box-shadow: 0 0 0 3px rgba(214, 112, 162, 0.20);
}

.support-form-group textarea {
    resize: vertical;
    min-height: 130px;
}

.support-form-group input::placeholder,
.support-form-group textarea::placeholder {
    color: rgba(255, 255, 255, 0.40);
}

.support-form-hint {
    margin: 4px 2px 0;
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.55);
    line-height: 1.45;
}

.support-form-submit {
    width: 100%;
    margin-top: 6px;
    padding: 12px 16px;
    border-radius: 999px;
    border: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.95rem;
    font-weight: 800;
    background: var(--accent, #d670a2);
    color: var(--on-accent, #1a0814);
    cursor: pointer;
    box-shadow: 0 6px 14px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.20), inset 0 -1px 0 rgba(0,0,0,.18);
    transition: filter .15s ease, transform .12s ease;
}
.support-form-submit:hover { filter: brightness(1.06); }
.support-form-submit:active { transform: scale(.97); box-shadow: 0 2px 5px rgba(0,0,0,.45), inset 0 2px 4px rgba(0,0,0,.2); }
</style>
@endpush
