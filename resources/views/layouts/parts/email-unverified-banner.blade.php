{{-- メール未認証ユーザー向けバナー。InjectHeaderBadges が $emailUnverified を全ページに共有。 --}}
@if(!empty($emailUnverified))
<div class="email-unverified-banner-wrap">
    <div class="email-unverified-banner" role="alert">
        <span class="email-unverified-banner__icon" aria-hidden="true"><i class="fas fa-envelope"></i></span>
        <div class="email-unverified-banner__body">
            <p class="email-unverified-banner__title">メールアドレスが未認証です</p>
            <p class="email-unverified-banner__lead">
                登録時のメールに届いた認証リンクを開いてください。
                届いていない場合は下のボタンから再送信できます。
            </p>
        </div>
        <form method="POST" action="{{ route('auth.email.send') }}" class="email-unverified-banner__form">
            @csrf
            <button type="submit" class="email-unverified-banner__btn">
                <i class="fas fa-paper-plane"></i> 再送信
            </button>
        </form>
    </div>
</div>

<style>
.email-unverified-banner-wrap { padding: 8px 12px 0; }
.email-unverified-banner {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px;
    background: linear-gradient(90deg, rgba(2, 132, 199, 0.14), rgba(2, 132, 199, 0.06));
    border: 1px solid rgba(2, 132, 199, 0.35);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.14);
    flex-wrap: wrap;
}
.email-unverified-banner__icon {
    flex: 0 0 auto;
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(2, 132, 199, 0.20);
    color: #0369a1;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.95rem;
}
.email-unverified-banner__body { flex: 1 1 auto; min-width: 200px; }
.email-unverified-banner__title {
    margin: 0 0 2px;
    font-size: 0.86rem; font-weight: 800;
    color: #0369a1;
    line-height: 1.3;
}
body:not(.theme-light) .email-unverified-banner__title { color: #7dd3fc; }
.email-unverified-banner__lead {
    margin: 0;
    font-size: 0.74rem;
    color: rgba(2, 132, 199, 0.85);
    line-height: 1.5;
}
body:not(.theme-light) .email-unverified-banner__lead { color: rgba(125, 211, 252, 0.85); }
.email-unverified-banner__form { flex: 0 0 auto; margin: 0; }
.email-unverified-banner__btn {
    padding: 8px 14px; border-radius: 999px;
    background: #0369a1; color: #fff;
    border: 0; font-size: 0.8rem; font-weight: 700;
    cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
.email-unverified-banner__btn:hover { background: #075985; }
</style>
@endif
