@extends('layouts.lp')

@section('title', 'ミセチョク｜店舗様向け｜掲載をご希望の店舗様へ')
@section('meta_description', '一般社団法人日本ナイトワーク適正化協会が運営する、採用報酬型のナイトワーク求人プラットフォーム「ミセチョク」。成約しなければ無料、求人コストを大幅にカットします。')
@section('body-class', 'lp-shop')

@push('styles')
<style>
    :root {
        --lp-bg: #fbf7ee;
        --lp-bg-alt: #ffffff;
        --lp-bg-soft: #f3ead6;
        --lp-text: #2a1d0f;
        --lp-text-soft: #6b563a;
        --lp-text-mute: #9b876a;
        --lp-gold: #eba8c8;
        --lp-gold-light: #e6cf99;
        --lp-gold-deep: #8a6d2f;
        --lp-line: #06c755;
        --lp-border: #ebe0c8;
        --lp-shadow: 0 8px 24px rgba(138, 109, 47, 0.08);
        --lp-cta-shadow: 0 10px 28px rgba(var(--accent-rgb, 214, 112, 162), 0.42);
        --lp-content-pad: 20px;
    }

    body.lp-shop { background: var(--lp-bg); color: var(--lp-text); }
    .lp-shell { max-width: 720px; margin: 0 auto; padding-bottom: 130px; }

    /* トップバー */
    .lp-topbar {
        position: sticky; top: 0; z-index: 30;
        background: rgba(251, 247, 238, 0.92);
        backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--lp-border);
    }
    .lp-topbar__inner {
        max-width: 720px; margin: 0 auto;
        padding: 12px var(--lp-content-pad);
        display: flex; align-items: center; justify-content: space-between;
    }
    .lp-topbar__logo {
        font-family: var(--font-sans);
        font-weight: 900; letter-spacing: 0.16em;
        font-size: 1.1rem; color: var(--lp-gold-deep); margin: 0;
    }
    .lp-topbar__back {
        font-size: 0.82rem; font-weight: 700; text-decoration: none;
        color: var(--lp-text-soft);
        padding: 6px 12px; border-radius: 999px;
        border: 1px solid var(--lp-border); background: #fff;
    }

    /* Hero */
    .lp-hero {
        position: relative; overflow: hidden;
        padding: 60px var(--lp-content-pad) 52px;
        text-align: center;
        background:
            radial-gradient(circle at 20% 0%, rgba(var(--accent-rgb, 214, 112, 162), 0.18), transparent 55%),
            radial-gradient(circle at 100% 100%, rgba(230, 207, 153, 0.22), transparent 55%),
            linear-gradient(180deg, #fff8e9 0%, #fbf7ee 70%);
    }
    .lp-hero::before {
        content: ''; position: absolute; inset: 18px;
        border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.25);
        border-radius: 4px; pointer-events: none;
    }
    .lp-hero__eyebrow {
        font-family: var(--font-sans);
        display: inline-block;
        font-size: 0.78rem; letter-spacing: 0.34em;
        font-weight: 700; color: var(--lp-gold-deep);
        padding: 4px 14px; border: 1px solid var(--lp-gold-light);
        border-radius: 999px; background: #fff; margin-bottom: 22px;
    }
    .lp-hero__catch {
        font-family: var(--font-sans);
        font-size: clamp(1.5rem, 6.4vw, 2.1rem);
        font-weight: 900; line-height: 1.5;
        color: var(--lp-text); margin: 0 0 26px;
        letter-spacing: 0.04em;
    }
    .lp-hero__points {
        max-width: 460px; margin: 0 auto;
        display: flex; flex-direction: column; gap: 10px;
    }
    .lp-hero__point {
        display: flex; align-items: center; gap: 12px;
        background: #fff;
        border: 1px solid var(--lp-gold-light);
        border-radius: 12px;
        padding: 14px 18px;
        font-size: 0.98rem; font-weight: 800;
        color: var(--lp-text);
        box-shadow: var(--lp-shadow);
        text-align: left;
    }
    .lp-hero__point i {
        flex-shrink: 0;
        width: 36px; height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fff5dd, var(--lp-gold));
        color: #fff; font-size: 1rem;
        display: inline-flex; align-items: center; justify-content: center;
    }

    /* セクション */
    .lp-section { padding: 56px var(--lp-content-pad); }
    .lp-section--alt { background: var(--lp-bg-alt); }
    .lp-section__head { text-align: center; margin-bottom: 32px; }
    .lp-section__eyebrow {
        font-family: var(--font-sans);
        display: inline-block; font-size: 0.78rem; letter-spacing: 0.32em;
        font-weight: 700; color: var(--lp-gold-deep); margin-bottom: 8px;
    }
    .lp-section__title {
        font-family: var(--font-sans);
        font-size: clamp(1.3rem, 5vw, 1.7rem);
        font-weight: 900; margin: 0;
    }
    .lp-section__title-ornament {
        display: flex; align-items: center; justify-content: center;
        gap: 12px; margin-top: 10px;
    }
    .lp-section__title-ornament::before,
    .lp-section__title-ornament::after {
        content: ''; flex: 0 0 32px; height: 1px; background: var(--lp-gold);
    }
    .lp-section__title-ornament span {
        font-size: 0.7rem; letter-spacing: 0.32em; color: var(--lp-gold-deep);
    }

    /* メッセージ */
    .lp-message {
        max-width: 600px; margin: 0 auto;
        background: #fff;
        border: 1px solid var(--lp-border);
        border-radius: 16px;
        padding: 28px 24px;
        box-shadow: var(--lp-shadow);
        font-size: 0.92rem;
        line-height: 2;
        color: var(--lp-text-soft);
    }
    .lp-message p { margin: 0 0 16px; }
    .lp-message p:last-child { margin-bottom: 0; }
    .lp-message strong {
        color: var(--lp-gold-deep);
        font-weight: 800;
    }

    /* STEP */
    .lp-flow {
        display: grid; gap: 22px;
        max-width: 600px; margin: 0 auto;
    }
    .lp-flow__step {
        position: relative;
        background: #fff;
        border: 1px solid var(--lp-border);
        border-radius: 16px;
        padding: 26px 22px 22px;
        box-shadow: var(--lp-shadow);
    }
    .lp-flow__step + .lp-flow__step::before {
        content: '\f078';
        font-family: 'Font Awesome 6 Free'; font-weight: 900;
        position: absolute; top: -18px; left: 50%; transform: translateX(-50%);
        color: var(--lp-gold);
        background: var(--lp-bg-alt);
        width: 28px; height: 28px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%;
        border: 1px solid var(--lp-gold-light);
    }
    .lp-flow__num {
        font-family: var(--font-sans);
        font-size: 0.78rem; letter-spacing: 0.24em; font-weight: 700;
        color: var(--lp-gold-deep);
        display: inline-block;
        padding: 4px 10px;
        border: 1px solid var(--lp-gold-light);
        border-radius: 999px;
        margin-bottom: 12px;
        background: #fff5dd;
    }
    .lp-flow__title {
        font-family: var(--font-sans);
        font-size: 1.15rem; font-weight: 900;
        color: var(--lp-text); margin: 0 0 12px;
        line-height: 1.5;
    }
    .lp-flow__body {
        font-size: 0.9rem; line-height: 1.9;
        color: var(--lp-text-soft); margin: 0;
    }
    .lp-flow__note {
        display: block; margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed var(--lp-border);
        font-size: 0.8rem; line-height: 1.7;
        color: var(--lp-text-mute);
    }

    /* CTA セクション */
    .lp-mid-cta {
        text-align: center;
        max-width: 540px; margin: 32px auto 0;
        padding: 32px 24px;
        background: linear-gradient(135deg, #fff8e9, #f3e2b5);
        border: 1px solid var(--lp-gold);
        border-radius: 18px;
        box-shadow: var(--lp-shadow);
    }
    .lp-mid-cta__lead {
        font-family: var(--font-sans);
        font-size: 1.05rem; font-weight: 800; color: var(--lp-text);
        margin: 0 0 16px;
    }
    .lp-mid-cta__btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 10px;
        background: linear-gradient(135deg, var(--lp-gold-light), var(--lp-gold) 55%, var(--lp-gold-deep));
        color: #fff; font-weight: 800; text-decoration: none;
        padding: 16px 36px; border-radius: 999px;
        font-size: 1rem; letter-spacing: 0.06em;
        box-shadow: var(--lp-cta-shadow);
        transition: transform 0.15s ease;
    }
    .lp-mid-cta__btn:hover { transform: translateY(-1px); }
    .lp-mid-cta__note {
        display: block; margin-top: 10px;
        font-size: 0.78rem; color: var(--lp-text-mute);
    }

    /* LINE 問合せ */
    .lp-contact {
        max-width: 540px; margin: 0 auto;
        background: linear-gradient(135deg, #ffffff, #f7efd9);
        border: 1px solid var(--lp-gold-light);
        border-radius: 18px; padding: 28px 22px;
        text-align: center; box-shadow: var(--lp-shadow);
    }
    .lp-contact__title { font-family: var(--font-sans); font-size: 1.1rem; font-weight: 800; margin: 0 0 8px; }
    .lp-contact__body { font-size: 0.86rem; line-height: 1.85; color: var(--lp-text-soft); margin: 0 0 18px; }
    .lp-line-btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 10px;
        background: var(--lp-line); color: #fff;
        font-weight: 800; text-decoration: none;
        padding: 14px 28px; border-radius: 999px;
        font-size: 0.95rem;
        box-shadow: 0 10px 24px rgba(6, 199, 85, 0.3);
        transition: transform 0.15s ease;
    }
    .lp-line-btn:hover { transform: translateY(-1px); }
    .lp-line-btn i { font-size: 1.2rem; }
    .lp-contact__note { display: block; margin-top: 10px; font-size: 0.76rem; color: var(--lp-text-mute); }

    /* 運営会社 */
    .lp-company {
        max-width: 640px; margin: 0 auto;
        background: #fff; border: 1px solid var(--lp-border);
        border-radius: 14px; overflow: hidden;
    }
    .lp-company__row {
        display: grid; grid-template-columns: 120px 1fr;
        padding: 14px 18px;
        font-size: 0.86rem; line-height: 1.7;
        border-bottom: 1px dashed var(--lp-border);
    }
    .lp-company__row:last-child { border-bottom: none; }
    .lp-company__row dt { font-weight: 700; color: var(--lp-gold-deep); margin: 0; }
    .lp-company__row dd { margin: 0; color: var(--lp-text-soft); }
    .lp-company__more {
        display: block; text-align: center; margin-top: 16px;
        font-size: 0.84rem; color: var(--lp-gold-deep); text-decoration: underline;
    }

    /* フッター */
    .lp-footer {
        text-align: center;
        padding: 28px var(--lp-content-pad) 32px;
        font-size: 0.78rem; color: var(--lp-text-mute);
        background: var(--lp-bg-soft);
    }
    .lp-footer a { color: var(--lp-text-soft); text-decoration: none; margin: 0 8px; }
    .lp-footer__copy { margin-top: 8px; font-size: 0.74rem; }

    /* 固定誘導バー */
    .lp-fixed-cta {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 50;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
        border-top: 1px solid var(--lp-gold-light);
        box-shadow: 0 -10px 24px rgba(138, 109, 47, 0.12);
        padding: 12px var(--lp-content-pad) calc(12px + env(safe-area-inset-bottom));
    }
    .lp-fixed-cta__inner {
        max-width: 720px; margin: 0 auto;
        display: flex; align-items: center; gap: 12px;
    }
    .lp-fixed-cta__pitch { flex: 0 0 auto; font-size: 0.78rem; line-height: 1.4; color: var(--lp-text); }
    .lp-fixed-cta__pitch strong { display: block; font-size: 0.92rem; font-weight: 800; color: var(--lp-gold-deep); }
    .lp-fixed-cta__btn {
        flex: 1 1 auto; min-width: 0;
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--lp-gold-light), var(--lp-gold) 55%, var(--lp-gold-deep));
        color: #fff; font-weight: 800; text-decoration: none;
        padding: 13px 18px; border-radius: 999px;
        font-size: 0.93rem; letter-spacing: 0.04em;
        box-shadow: var(--lp-cta-shadow);
        transition: transform 0.15s ease;
    }
    .lp-fixed-cta__btn:hover { transform: translateY(-1px); }
    .lp-fixed-cta__sub { text-align: center; margin: 8px 0 0; font-size: 0.78rem; }
    .lp-fixed-cta__sub a { color: var(--lp-text-soft); text-decoration: underline; font-weight: 700; }
    .lp-fixed-cta__sub a i { margin-right: 4px; color: var(--lp-gold-deep); }

    @media (min-width: 600px) {
        .lp-fixed-cta__pitch { font-size: 0.85rem; }
        .lp-fixed-cta__btn { padding: 14px 22px; font-size: 0.98rem; }
    }
</style>
@endpush

@section('content')
{{-- トップバー --}}
<div class="lp-topbar">
    <div class="lp-topbar__inner">
        <h1 class="lp-topbar__logo">ミセチョク</h1>
        <a href="{{ route('welcome') }}" class="lp-topbar__back">
            <i class="fas fa-arrow-left"></i> キャスト向け
        </a>
    </div>
</div>

<div class="lp-shell">
    {{-- Hero --}}
    <header class="lp-hero">
        <span class="lp-hero__eyebrow">STORE LISTING</span>
        <h2 class="lp-hero__catch">掲載をご希望の店舗様</h2>
        <div class="lp-hero__points">
            <div class="lp-hero__point">
                <i class="fas fa-bullhorn"></i>
                <span>もっとお店を知ってもらえる！</span>
            </div>
            <div class="lp-hero__point">
                <i class="fas fa-yen-sign"></i>
                <span>求人コストを大幅にカット！</span>
            </div>
            <div class="lp-hero__point">
                <i class="fas fa-handshake"></i>
                <span>成約しなければ無料！</span>
            </div>
        </div>
    </header>

    {{-- 協会からのメッセージ --}}
    <section class="lp-section" aria-labelledby="lp-message-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">MESSAGE</span>
            <h2 id="lp-message-title" class="lp-section__title">店舗様へ</h2>
            <div class="lp-section__title-ornament"><span>◆</span></div>
        </div>
        <div class="lp-message">
            <p>この度は当協会の理念にご賛同、システムに興味を持っていただきありがとうございます。<strong>一般社団法人日本ナイトワーク適正化協会</strong>では、今までよりコストを抑えて良いキャストさんとマッチングできる新しいサービスを実現いたしました。</p>
            <p>このシステムを常識化する事で求人にかけるコストを抑える事はもちろん、求人の生命線をスカウト会社が半ば独占しているようなナイトワーク業界の常識を覆したいと考えております。その為には店舗様の協力が不可欠になります。</p>
            <p>これからナイトワークの世界に飛び込みたいキャストさん、新しいステップに挑戦したいキャストさんを後押しできるサービスにする事で、キャストさんの仕事に対する意識が変わり、店舗様の運用もより強固になる良循環が起きると信じております。</p>
            <p>スカウト会社自体が悪というわけではなく、スカウト会社に頼り高額な求人単価を支払うアナログな業界の常識や昔からの慣習が、店舗様の首を絞めている要員の一つである事は事実かと思います。皆様のご協力で当協会のサービスの運営は成り立っております。改めて感謝申し上げます。</p>
        </div>
    </section>

    {{-- ご登録の流れ --}}
    <section class="lp-section lp-section--alt" aria-labelledby="lp-flow-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">FLOW</span>
            <h2 id="lp-flow-title" class="lp-section__title">ご登録の流れ</h2>
            <div class="lp-section__title-ornament"><span>◆</span></div>
        </div>
        <div class="lp-flow">
            <div class="lp-flow__step">
                <span class="lp-flow__num">STEP-1</span>
                <h3 class="lp-flow__title">フォームに必要情報を入力</h3>
                <p class="lp-flow__body">必要情報を入力の上、ご登録ください。登録には営業許可証を提出いただきます。</p>
            </div>
            <div class="lp-flow__step">
                <span class="lp-flow__num">STEP-2</span>
                <h3 class="lp-flow__title">店舗様管理画面をお渡しします</h3>
                <p class="lp-flow__body">店舗プロフィールや求人情報を入力。写真や動画でお店の魅力を伝えましょう。操作方法は非常に簡単です。</p>
                <span class="lp-flow__note">※ 求人情報を入力するとキャスト側に店舗情報が表示されます。また、キャストと面談を行うためにも求人情報のご入力が必要です。</span>
            </div>
            <div class="lp-flow__step">
                <span class="lp-flow__num">STEP-3</span>
                <h3 class="lp-flow__title">チョクでメッセージしましょう</h3>
                <p class="lp-flow__body">キャストと直接メッセージのやり取りを行い、気になることを確認しましょう。面接日を決め、採用・不採用まで行えます。</p>
            </div>
        </div>

        <div class="lp-mid-cta">
            <p class="lp-mid-cta__lead">店舗登録申請はこちら</p>
            <a href="{{ route('shop.register') }}" class="lp-mid-cta__btn">
                <i class="fas fa-store"></i> 店舗登録申請に進む
            </a>
            <span class="lp-mid-cta__note">登録は無料・成約しなければ費用は発生しません</span>
        </div>
    </section>

    {{-- LINE 問い合わせ --}}
    <section class="lp-section" aria-labelledby="lp-contact-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">CONTACT</span>
            <h2 id="lp-contact-title" class="lp-section__title">店舗様向けLINE窓口</h2>
            <div class="lp-section__title-ornament"><span>◆</span></div>
        </div>
        <div class="lp-contact">
            <h3 class="lp-contact__title">導入相談・資料請求はLINEで</h3>
            <p class="lp-contact__body">
                料金詳細や採用報酬の相場、導入事例の資料をLINEでお送りします。<br>
                お見積もり・運用相談もお気軽にどうぞ。
            </p>
            <a href="https://lin.ee/misechoku-shop" target="_blank" rel="noopener" class="lp-line-btn">
                <i class="fab fa-line"></i> LINEで導入相談する
            </a>
            <span class="lp-contact__note">@misechoku-shop ／ 営業日 10:00–19:00</span>
        </div>
    </section>

    {{-- 運営会社 --}}
    <section class="lp-section lp-section--alt" aria-labelledby="lp-company-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">COMPANY</span>
            <h2 id="lp-company-title" class="lp-section__title">運営会社</h2>
            <div class="lp-section__title-ornament"><span>◆</span></div>
        </div>
        <dl class="lp-company">
            <div class="lp-company__row">
                <dt>会社名</dt>
                <dd>一般社団法人 日本ナイトワーク適正化協会</dd>
            </div>
            <div class="lp-company__row">
                <dt>所在地</dt>
                <dd>〒107-0062 東京都港区南青山 2-2-15 ウィン青山942</dd>
            </div>
            <div class="lp-company__row">
                <dt>代表者</dt>
                <dd>代表理事 ミセチョク 太郎</dd>
            </div>
            <div class="lp-company__row">
                <dt>設立</dt>
                <dd>2024年</dd>
            </div>
            <div class="lp-company__row">
                <dt>事業内容</dt>
                <dd>ナイトワーク業界向けキャスティングプラットフォーム「ミセチョク」の企画・運営</dd>
            </div>
            <div class="lp-company__row">
                <dt>店舗様窓口</dt>
                <dd>LINE @misechoku-shop ／ shop@misechoku.jp</dd>
            </div>
        </dl>
        <a href="{{ url('/about') }}" class="lp-company__more">運営会社の詳細を見る →</a>
    </section>

    <footer class="lp-footer">
        <div>
            <a href="{{ url('/about') }}">運営会社</a>・
            <a href="{{ url('/terms') }}">利用規約</a>・
            <a href="{{ url('/privacy') }}">プライバシーポリシー</a>
        </div>
        <div class="lp-footer__copy">© {{ date('Y') }} 一般社団法人 日本ナイトワーク適正化協会</div>
    </footer>
</div>

{{-- 固定誘導バー（店舗登録） --}}
<div class="lp-fixed-cta" role="region" aria-label="店舗登録申請">
    <div class="lp-fixed-cta__inner">
        <div class="lp-fixed-cta__pitch">
            <strong>登録無料</strong>
            <span>成約まで0円</span>
        </div>
        <a href="{{ route('shop.register') }}" class="lp-fixed-cta__btn">
            <i class="fas fa-store"></i> 店舗登録申請はこちら
        </a>
    </div>
    <p class="lp-fixed-cta__sub">
        <a href="{{ route('welcome') }}"><i class="fas fa-user"></i>キャストの方はコチラ</a>
    </p>
</div>
@endsection
