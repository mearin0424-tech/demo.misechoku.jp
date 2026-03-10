@extends('layouts.app')

@section('title', 'よくある質問（FAQ）')

@section('content')
<div class="support-faq-page">
    <div class="support-faq-header">
        <h1 class="support-faq-title">
            よくある質問（FAQ）
            <span class="support-faq-badge">
                {{ $isCast ? 'キャスト向け' : '店舗向け' }}
            </span>
        </h1>
        <p class="support-faq-lead">
            ミセチョクについて、よくいただくご質問をまとめました。<br>
            詳細なご相談やトラブルシュートが必要な場合は、「問い合わせ窓口」からご連絡ください。（※デモ環境のため実際の送信は行われません）
        </p>
    </div>

    <div class="support-faq-list">
        <details class="support-faq-item">
            <summary class="support-faq-question">
                <span>ログインできない場合はどうすれば良いですか？</span>
                <i class="fas fa-chevron-down"></i>
            </summary>
            <div class="support-faq-answer">
                <p>
                    メールアドレス・パスワードに誤りがないかをご確認ください。
                    パスワードをお忘れの場合は、ログイン画面の「パスワードを忘れた方へ」から再設定を行う想定です。（デモ環境では画面のみのご用意です）
                </p>
            </div>
        </details>

        <details class="support-faq-item">
            <summary class="support-faq-question">
                <span>{{ $isCast ? 'お店からのオファーはどこで確認できますか？' : 'キャストからの応募やメッセージはどこで確認できますか？' }}</span>
                <i class="fas fa-chevron-down"></i>
            </summary>
            <div class="support-faq-answer">
                <p>
                    つながり（LIKES）タブとトーク画面から、やりとり中の相手を一覧で確認できます。
                    気になる相手がいる場合は、プロフィール画面からもトークへ遷移できるような導線を想定しています。
                </p>
            </div>
        </details>

        <details class="support-faq-item">
            <summary class="support-faq-question">
                <span>通知のオン／オフはどこで変更できますか？</span>
                <i class="fas fa-chevron-down"></i>
            </summary>
            <div class="support-faq-answer">
                <p>
                    サイドメニュー内の「SETTING &gt; 通知設定」から、プッシュ通知やメール通知の受け取り設定を変更できる想定です。
                    デモ環境では、UIのみ確認できる状態です。
                </p>
            </div>
        </details>

        <details class="support-faq-item">
            <summary class="support-faq-question">
                <span>退会やアカウント削除はできますか？</span>
                <i class="fas fa-chevron-down"></i>
            </summary>
            <div class="support-faq-answer">
                <p>
                    アカウント設定内の「退会手続き」から申請できる導線を想定しています。
                    実際の運用時には、利用規約・個人情報保護方針に基づき、一定期間のデータ保管なども含めて設計されます。
                </p>
            </div>
        </details>
    </div>
</div>
@endsection

@push('styles')
<style>
.support-faq-page {
    padding: 24px 16px 32px;
    color: #f9f5f5;
}
@media (min-width: 768px) {
    .support-faq-page {
        padding: 32px 24px 40px;
    }
}

.support-faq-header {
    margin-bottom: 24px;
}

.support-faq-title {
    font-family: 'Shippori Mincho', 'Noto Sans JP', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 1.4rem;
    margin-bottom: 8px;
    color: var(--color-gold, #d4af37);
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.support-faq-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 999px;
    border: 1px solid rgba(212, 175, 55, 0.6);
    color: #f9f5f5;
}

.support-faq-lead {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #d1c1c1;
}

.support-faq-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.support-faq-item {
    background: rgba(20, 7, 15, 0.9);
    border-radius: 16px;
    border: 1px solid rgba(212, 175, 55, 0.4);
    padding: 4px 8px;
}

.support-faq-question {
    list-style: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 0.9rem;
    padding: 10px 6px;
    cursor: pointer;
}

.support-faq-question::-webkit-details-marker {
    display: none;
}

.support-faq-question i {
    font-size: 0.8rem;
    opacity: 0.7;
    transition: transform 0.2s ease;
}

.support-faq-item[open] .support-faq-question i {
    transform: rotate(180deg);
}

.support-faq-answer {
    padding: 0 6px 10px;
    font-size: 0.85rem;
    line-height: 1.7;
    color: #efe3e3;
}
</style>
@endpush

