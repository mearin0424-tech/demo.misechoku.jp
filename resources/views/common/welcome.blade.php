@extends('layouts.lp')

@section('title', 'ミセチョク｜水商売・夜職のキャストファースト求人')
@section('meta_description', '直接マッチングで「お祝い金」を還元する、夜職のキャストファースト求人プラットフォーム「ミセチョク」。匿名でお店とやり取りができ、入店時にボーナスがもらえます。')
@section('body-class', 'lp-welcome')

@push('styles')
<style>
    :root {
        --lp-bg: #fbf7ee;
        --lp-bg-alt: #ffffff;
        --lp-bg-soft: #f3ead6;
        --lp-text: #2a1d0f;
        --lp-text-soft: #6b563a;
        --lp-text-mute: #9b876a;
        --lp-gold: #c5a059;
        --lp-gold-light: #e6cf99;
        --lp-gold-deep: #8a6d2f;
        --lp-line: #06c755;
        --lp-border: #ebe0c8;
        --lp-shadow: 0 8px 24px rgba(138, 109, 47, 0.08);
        --lp-cta-shadow: 0 10px 28px rgba(197, 160, 89, 0.42);
        --lp-content-pad: 20px;
    }

    body.lp-welcome { background: var(--lp-bg); color: var(--lp-text); }

    .lp-shell { max-width: 720px; margin: 0 auto; padding-bottom: 120px; }

    /* ===== トップバー（軽量・ロゴと最小限のリンクのみ） ===== */
    .lp-topbar {
        position: sticky; top: 0; z-index: 30;
        background: rgba(251, 247, 238, 0.92);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--lp-border);
    }
    .lp-topbar__inner {
        max-width: 720px; margin: 0 auto;
        padding: 12px var(--lp-content-pad);
        display: flex; align-items: center; justify-content: space-between;
    }
    .lp-topbar__logo {
        font-family: 'Shippori Mincho', 'Noto Serif JP', serif;
        font-weight: 900; letter-spacing: 0.16em;
        font-size: 1.1rem; color: var(--lp-gold-deep); margin: 0;
    }
    .lp-topbar__login {
        font-size: 0.82rem; font-weight: 700; text-decoration: none;
        color: var(--lp-text-soft);
        padding: 6px 12px; border-radius: 999px;
        border: 1px solid var(--lp-border);
        background: #fff;
    }

    /* ===== Hero ===== */
    .lp-hero {
        position: relative; overflow: hidden;
        padding: 56px var(--lp-content-pad) 48px;
        text-align: center;
        background:
            radial-gradient(circle at 80% 0%, rgba(197, 160, 89, 0.18), transparent 55%),
            radial-gradient(circle at 0% 90%, rgba(230, 207, 153, 0.22), transparent 55%),
            linear-gradient(180deg, #fff8e9 0%, #fbf7ee 70%);
    }
    .lp-hero::before {
        content: ''; position: absolute;
        inset: 18px; border: 1px solid rgba(197, 160, 89, 0.25);
        border-radius: 4px; pointer-events: none;
    }
    .lp-hero__eyebrow {
        display: inline-block; font-size: 0.72rem; letter-spacing: 0.32em;
        font-weight: 700; color: var(--lp-gold-deep);
        padding: 4px 14px; border: 1px solid var(--lp-gold-light);
        border-radius: 999px; background: #fff; margin-bottom: 18px;
    }
    .lp-hero__catch {
        font-family: 'Shippori Mincho', 'Noto Serif JP', serif;
        font-size: clamp(1.45rem, 6.2vw, 2.1rem);
        font-weight: 900; line-height: 1.5;
        color: var(--lp-text); margin: 0 0 14px;
    }
    .lp-hero__catch em {
        font-style: normal;
        background: linear-gradient(transparent 65%, rgba(197, 160, 89, 0.35) 65%);
        padding: 0 4px;
    }
    .lp-hero__lead {
        font-size: 0.95rem; line-height: 1.85;
        color: var(--lp-text-soft); margin: 0 auto 28px; max-width: 480px;
    }
    .lp-hero__badges {
        display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;
        margin: 0 auto;
    }
    .lp-hero__badge {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 0.78rem; font-weight: 700;
        padding: 6px 12px; border-radius: 999px;
        background: #fff; color: var(--lp-gold-deep);
        border: 1px solid var(--lp-gold-light);
    }
    .lp-hero__badge i { color: var(--lp-gold); }

    /* ===== セクション共通 ===== */
    .lp-section { padding: 56px var(--lp-content-pad); }
    .lp-section--alt { background: var(--lp-bg-alt); }
    .lp-section__head { text-align: center; margin-bottom: 32px; }
    .lp-section__eyebrow {
        display: inline-block; font-size: 0.7rem; letter-spacing: 0.28em;
        font-weight: 800; color: var(--lp-gold-deep); margin-bottom: 8px;
    }
    .lp-section__title {
        font-family: 'Shippori Mincho', 'Noto Serif JP', serif;
        font-size: clamp(1.3rem, 5vw, 1.7rem);
        font-weight: 900; color: var(--lp-text); margin: 0;
        letter-spacing: 0.04em;
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
    .lp-section__sub {
        margin: 14px auto 0; max-width: 520px;
        font-size: 0.9rem; line-height: 1.85; color: var(--lp-text-soft);
    }

    /* ===== ベネフィット ===== */
    .lp-benefits { display: grid; gap: 16px; }
    @media (min-width: 768px) { .lp-benefits { grid-template-columns: repeat(3, 1fr); } }
    .lp-benefit {
        background: #fff;
        border: 1px solid var(--lp-border);
        border-radius: 14px;
        padding: 24px 20px;
        text-align: center;
        box-shadow: var(--lp-shadow);
    }
    .lp-benefit__icon {
        width: 56px; height: 56px; margin: 0 auto 14px;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #fff5dd, #e6cf99);
        color: var(--lp-gold-deep);
        font-size: 1.3rem;
    }
    .lp-benefit__title {
        font-family: 'Shippori Mincho', 'Noto Serif JP', serif;
        font-size: 1.05rem; font-weight: 800;
        color: var(--lp-text); margin: 0 0 8px;
    }
    .lp-benefit__body {
        font-size: 0.86rem; line-height: 1.85;
        color: var(--lp-text-soft); margin: 0;
    }
    .lp-benefit__body strong { color: var(--lp-gold-deep); font-weight: 800; }
    .lp-benefit__note {
        display: block; margin-top: 8px;
        font-size: 0.74rem; color: var(--lp-text-mute);
    }

    /* ===== ステップ ===== */
    .lp-steps { display: grid; gap: 18px; counter-reset: step; }
    @media (min-width: 768px) { .lp-steps { grid-template-columns: repeat(2, 1fr); } }
    .lp-step {
        position: relative;
        background: var(--lp-bg-alt);
        border: 1px solid var(--lp-border);
        border-radius: 14px;
        padding: 26px 20px 20px;
        list-style: none;
    }
    .lp-step__num {
        position: absolute; top: -16px; left: 18px;
        width: 36px; height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ffefc4, var(--lp-gold));
        color: #fff;
        font-family: 'Playfair Display', serif;
        font-weight: 700; font-size: 1.05rem;
        display: inline-flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 10px rgba(197, 160, 89, 0.45);
    }
    .lp-step__icon {
        font-size: 1.3rem; color: var(--lp-gold-deep); margin-bottom: 8px;
    }
    .lp-step__title {
        font-family: 'Shippori Mincho', serif;
        font-size: 1rem; font-weight: 800;
        color: var(--lp-text); margin: 0 0 6px;
    }
    .lp-step__body {
        font-size: 0.85rem; line-height: 1.8;
        color: var(--lp-text-soft); margin: 0;
    }
    .lp-step__note {
        display: block; margin-top: 6px;
        font-size: 0.74rem; color: var(--lp-text-mute);
    }

    /* ===== FAQ ===== */
    .lp-faqs { display: flex; flex-direction: column; gap: 10px; max-width: 640px; margin: 0 auto; }
    .lp-faq {
        background: #fff;
        border: 1px solid var(--lp-border);
        border-radius: 12px; overflow: hidden;
    }
    .lp-faq[open] { border-color: var(--lp-gold-light); box-shadow: var(--lp-shadow); }
    .lp-faq__summary {
        list-style: none; cursor: pointer;
        padding: 16px 48px 16px 18px;
        position: relative;
        font-size: 0.93rem; font-weight: 700; color: var(--lp-text);
        display: flex; gap: 10px; align-items: flex-start;
    }
    .lp-faq__summary::-webkit-details-marker { display: none; }
    .lp-faq__summary::before {
        content: 'Q.';
        flex-shrink: 0; color: var(--lp-gold-deep);
        font-family: 'Playfair Display', serif; font-weight: 700;
    }
    .lp-faq__summary::after {
        content: '\f078';
        font-family: 'Font Awesome 6 Free'; font-weight: 900;
        position: absolute; right: 18px; top: 50%; transform: translateY(-50%);
        font-size: 0.72rem; color: var(--lp-gold);
        transition: transform 0.2s ease;
    }
    .lp-faq[open] .lp-faq__summary::after { transform: translateY(-50%) rotate(180deg); }
    .lp-faq__body {
        padding: 0 18px 18px;
        font-size: 0.86rem; line-height: 1.9; color: var(--lp-text-soft);
        display: flex; gap: 10px;
    }
    .lp-faq__body::before {
        content: 'A.';
        flex-shrink: 0; color: var(--lp-gold);
        font-family: 'Playfair Display', serif; font-weight: 700;
    }
    .lp-faq__body p { margin: 0; }
    .lp-faq__body p + p { margin-top: 6px; }

    /* ===== LINE 問合せ ===== */
    .lp-contact {
        max-width: 540px; margin: 0 auto;
        background: linear-gradient(135deg, #ffffff, #f7efd9);
        border: 1px solid var(--lp-gold-light);
        border-radius: 18px;
        padding: 28px 22px;
        text-align: center;
        box-shadow: var(--lp-shadow);
    }
    .lp-contact__title {
        font-family: 'Shippori Mincho', serif;
        font-size: 1.1rem; font-weight: 800;
        color: var(--lp-text); margin: 0 0 8px;
    }
    .lp-contact__body {
        font-size: 0.86rem; line-height: 1.85; color: var(--lp-text-soft);
        margin: 0 0 18px;
    }
    .lp-line-btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 10px;
        background: var(--lp-line); color: #fff;
        font-weight: 800; text-decoration: none;
        padding: 14px 28px; border-radius: 999px;
        font-size: 0.95rem;
        box-shadow: 0 10px 24px rgba(6, 199, 85, 0.3);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .lp-line-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 28px rgba(6, 199, 85, 0.4); }
    .lp-line-btn i { font-size: 1.2rem; }
    .lp-contact__note {
        display: block; margin-top: 10px;
        font-size: 0.76rem; color: var(--lp-text-mute);
    }

    /* ===== 運営会社 ===== */
    .lp-company {
        max-width: 640px; margin: 0 auto;
        background: #fff;
        border: 1px solid var(--lp-border);
        border-radius: 14px;
        overflow: hidden;
    }
    .lp-company__row {
        display: grid; grid-template-columns: 120px 1fr;
        gap: 0;
        padding: 14px 18px;
        font-size: 0.86rem; line-height: 1.7;
        border-bottom: 1px dashed var(--lp-border);
    }
    .lp-company__row:last-child { border-bottom: none; }
    .lp-company__row dt {
        font-weight: 700; color: var(--lp-gold-deep); margin: 0;
    }
    .lp-company__row dd { margin: 0; color: var(--lp-text-soft); }
    .lp-company__more {
        display: block; text-align: center;
        margin-top: 16px;
        font-size: 0.84rem; color: var(--lp-gold-deep);
        text-decoration: underline;
    }

    /* ===== 末尾フッター ===== */
    .lp-footer {
        text-align: center;
        padding: 28px var(--lp-content-pad) 32px;
        font-size: 0.78rem; color: var(--lp-text-mute);
        background: var(--lp-bg-soft);
    }
    .lp-footer a { color: var(--lp-text-soft); text-decoration: none; margin: 0 8px; }
    .lp-footer__copy { margin-top: 8px; font-size: 0.74rem; }

    /* ===== 固定誘導バー ===== */
    .lp-fixed-cta {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 50;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-top: 1px solid var(--lp-gold-light);
        box-shadow: 0 -10px 24px rgba(138, 109, 47, 0.12);
        padding: 12px var(--lp-content-pad) calc(12px + env(safe-area-inset-bottom));
    }
    .lp-fixed-cta__inner {
        max-width: 720px; margin: 0 auto;
        display: flex; align-items: center; gap: 12px;
    }
    .lp-fixed-cta__pitch {
        flex: 0 0 auto;
        font-size: 0.78rem; line-height: 1.4;
        color: var(--lp-text);
    }
    .lp-fixed-cta__pitch strong {
        display: block; font-size: 0.92rem; font-weight: 800;
        color: var(--lp-gold-deep);
    }
    .lp-fixed-cta__btn {
        flex: 1 1 auto; min-width: 0;
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--lp-gold-light), var(--lp-gold) 55%, var(--lp-gold-deep));
        color: #fff;
        font-weight: 800; text-decoration: none;
        padding: 13px 18px; border-radius: 999px;
        font-size: 0.93rem; letter-spacing: 0.04em;
        box-shadow: var(--lp-cta-shadow);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .lp-fixed-cta__btn:hover { transform: translateY(-1px); }
    .lp-fixed-cta__shop {
        text-align: center; margin: 8px 0 0;
        font-size: 0.78rem;
    }
    .lp-fixed-cta__shop a {
        color: var(--lp-text-soft);
        text-decoration: underline;
        font-weight: 700;
    }
    .lp-fixed-cta__shop a i { margin-right: 4px; color: var(--lp-gold-deep); }

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
        <a href="{{ route('login.demo') }}" class="lp-topbar__login">
            <i class="fas fa-right-to-bracket"></i> ログイン
        </a>
    </div>
</div>

<div class="lp-shell">
    {{-- Hero --}}
    <header class="lp-hero">
        <span class="lp-hero__eyebrow">CAST FIRST PLATFORM</span>
        <h2 class="lp-hero__catch">
            お祝い金がもらえる、<br>
            <em>キャストファースト</em>の<br class="sm:hidden">夜職マッチング。
        </h2>
        <p class="lp-hero__lead">
            お店と直接やり取りできるから、スカウトを通さず手取りアップ。<br>
            匿名でメッセージできるので、初めての方も安心です。
        </p>
        <div class="lp-hero__badges">
            <span class="lp-hero__badge"><i class="fas fa-yen-sign"></i> 完全無料</span>
            <span class="lp-hero__badge"><i class="fas fa-user-secret"></i> 匿名OK</span>
            <span class="lp-hero__badge"><i class="fas fa-gift"></i> 採用報酬あり</span>
        </div>
    </header>

    {{-- 特長 --}}
    <section class="lp-section" aria-labelledby="lp-benefits-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">FEATURES</span>
            <h2 id="lp-benefits-title" class="lp-section__title">ミセチョクの特長</h2>
            <div class="lp-section__title-ornament"><span>◆</span></div>
            <p class="lp-section__sub">完全無料でご希望の業種の店舗様とマッチング。</p>
        </div>
        <div class="lp-benefits">
            <div class="lp-benefit">
                <span class="lp-benefit__icon"><i class="fas fa-map-location-dot"></i></span>
                <h3 class="lp-benefit__title">全国のお店を探せる</h3>
                <p class="lp-benefit__body">
                    わずらわしいやり取りなしでスムーズにアプローチ。サイト内メッセージなので、連絡先を交換せず気軽に問い合わせできます。
                </p>
            </div>
            <div class="lp-benefit">
                <span class="lp-benefit__icon"><i class="fas fa-coins"></i></span>
                <h3 class="lp-benefit__title">採用報酬がもらえる</h3>
                <p class="lp-benefit__body">
                    入店が決まると、本来スカウト会社へ支払われる「スカウトバック」を<strong>採用報酬</strong>としてご本人が受け取れます。
                    <span class="lp-benefit__note">※ 条件は店舗ごとに異なります。</span>
                </p>
            </div>
            <div class="lp-benefit">
                <span class="lp-benefit__icon"><i class="fas fa-circle-check"></i></span>
                <h3 class="lp-benefit__title">完全無料で使える</h3>
                <p class="lp-benefit__body">
                    マッチング・利用は完全無料。入店準備金で美容や衣装に自己投資し、お仕事のパフォーマンスもアップ。
                </p>
            </div>
        </div>
    </section>

    {{-- 使い方 --}}
    <section class="lp-section lp-section--alt" aria-labelledby="lp-guide-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">HOW TO USE</span>
            <h2 id="lp-guide-title" class="lp-section__title">使い方は簡単</h2>
            <div class="lp-section__title-ornament"><span>◆</span></div>
            <p class="lp-section__sub">入店準備金ゲットまで、たった4ステップ。</p>
        </div>
        <ol class="lp-steps" style="padding:0; margin:0;">
            <li class="lp-step">
                <span class="lp-step__num">1</span>
                <i class="fab fa-line lp-step__icon" style="color:#06c755;"></i>
                <h3 class="lp-step__title">アカウント登録</h3>
                <p class="lp-step__body">LINEで簡単に登録。いくつかの質問に答えるだけで、希望条件にあうお店に出会いやすくなります。</p>
            </li>
            <li class="lp-step">
                <span class="lp-step__num">2</span>
                <i class="fas fa-magnifying-glass lp-step__icon"></i>
                <h3 class="lp-step__title">気になるお店を探す</h3>
                <p class="lp-step__body">条件検索で自分に合ったお店を絞り込み、気になるお店をチェック。</p>
            </li>
            <li class="lp-step">
                <span class="lp-step__num">3</span>
                <i class="fas fa-comments lp-step__icon"></i>
                <h3 class="lp-step__title">チョクでメッセージ</h3>
                <p class="lp-step__body">
                    お店の担当者に直接メッセージで質問・相談。面接日や体入日もここでOK。
                    <span class="lp-step__note">※ サイト内メッセージなので、連絡先は秘密のままやり取り可能。</span>
                </p>
            </li>
            <li class="lp-step">
                <span class="lp-step__num">4</span>
                <i class="fas fa-gift lp-step__icon"></i>
                <h3 class="lp-step__title">入店決定！ボーナスゲット</h3>
                <p class="lp-step__body">入店が決まれば、店舗からボーナス（採用報酬）が届きます。良い条件で働きつつ、お祝い金まで受け取れるのがミセチョクの醍醐味です。</p>
            </li>
        </ol>
    </section>

    {{-- FAQ --}}
    <section class="lp-section" aria-labelledby="lp-faq-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">FAQ</span>
            <h2 id="lp-faq-title" class="lp-section__title">よくある質問</h2>
            <div class="lp-section__title-ornament"><span>◆</span></div>
        </div>
        <div class="lp-faqs">
            <details class="lp-faq">
                <summary class="lp-faq__summary">未経験者です。安全に使えますか？</summary>
                <div class="lp-faq__body">
                    <div>
                        <p>ミセチョクはキャストさんファーストを目指しています。</p>
                        <p>店舗様と匿名でやり取りでき、連絡先の交換も不要です。未経験・経験者問わず安心してご利用いただけます。</p>
                    </div>
                </div>
            </details>
            <details class="lp-faq">
                <summary class="lp-faq__summary">登録は誰でもできますか？</summary>
                <div class="lp-faq__body">
                    <div><p>18歳以上であればどなたでもご利用いただけます。簡単登録で自分に合ったお店を探してみてください。</p></div>
                </div>
            </details>
            <details class="lp-faq">
                <summary class="lp-faq__summary">どんな職種が登録されていますか？</summary>
                <div class="lp-faq__body">
                    <div><p>キャバクラ、クラブ、ガールズバー、コンカフェ、スナック、ニュークラブなど幅広く登録があります。</p></div>
                </div>
            </details>
            <details class="lp-faq">
                <summary class="lp-faq__summary">掲載店舗は優良ですか？</summary>
                <div class="lp-faq__body">
                    <div><p>掲載店舗は当協会が営業許可証や代表者の確認、風営法の許可などをチェックしています。いわゆるモグリ店の登録はございません。</p></div>
                </div>
            </details>
            <details class="lp-faq">
                <summary class="lp-faq__summary">入店時にボーナスがもらえるのはナゼ？</summary>
                <div class="lp-faq__body">
                    <div>
                        <p>これまで店舗様がスカウトマンや求人広告会社に支払っていた費用を、キャストさんに還元しているからです。</p>
                        <p>グレーな業界の慣習をなくしたい思いで、ミセチョクは各店舗と連携し、キャストさんファーストを実現しようとしています。</p>
                    </div>
                </div>
            </details>
            <details class="lp-faq">
                <summary class="lp-faq__summary">お店側に身バレしないですか？</summary>
                <div class="lp-faq__body">
                    <div><p>ミセチョクでは匿名でのやり取りが可能です。安心してメッセージのやり取りをしてください。</p></div>
                </div>
            </details>
        </div>
    </section>

    {{-- LINE 問い合わせ --}}
    <section class="lp-section lp-section--alt" aria-labelledby="lp-contact-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">CONTACT</span>
            <h2 id="lp-contact-title" class="lp-section__title">LINEでのお問い合わせ</h2>
            <div class="lp-section__title-ornament"><span>◆</span></div>
        </div>
        <div class="lp-contact">
            <h3 class="lp-contact__title">登録前のご相談はLINEでOK</h3>
            <p class="lp-contact__body">
                「未経験でも大丈夫？」「どんなお店があるの？」など、登録前のご質問もお気軽にどうぞ。<br>
                ミセチョク公式アカウントから24時間受付中です。
            </p>
            <a href="https://lin.ee/misechoku" target="_blank" rel="noopener" class="lp-line-btn">
                <i class="fab fa-line"></i> LINEで問い合わせる
            </a>
            <span class="lp-contact__note">@misechoku ／ 返信は営業日 10:00–19:00</span>
        </div>
    </section>

    {{-- 運営会社 --}}
    <section class="lp-section" aria-labelledby="lp-company-title">
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
                <dt>お問合せ</dt>
                <dd>LINE公式アカウント @misechoku ／ support@misechoku.jp</dd>
            </div>
        </dl>
        <a href="{{ url('/about') }}" class="lp-company__more">運営会社の詳細を見る →</a>
    </section>

    {{-- フッター --}}
    <footer class="lp-footer">
        <div>
            <a href="{{ url('/about') }}">運営会社</a>・
            <a href="{{ url('/terms') }}">利用規約</a>・
            <a href="{{ url('/privacy') }}">プライバシーポリシー</a>
        </div>
        <div class="lp-footer__copy">© {{ date('Y') }} 一般社団法人 日本ナイトワーク適正化協会</div>
    </footer>
</div>

{{-- 固定誘導バー（キャスト新規登録） --}}
<div class="lp-fixed-cta" role="region" aria-label="キャスト新規登録">
    <div class="lp-fixed-cta__inner">
        <div class="lp-fixed-cta__pitch">
            <strong>無料登録</strong>
            <span>30秒で完了</span>
        </div>
        <a href="{{ route('cast.register') }}" class="lp-fixed-cta__btn">
            <i class="fas fa-user-plus"></i> キャストとしてはじめる
        </a>
    </div>
    <p class="lp-fixed-cta__shop">
        <a href="{{ route('welcome.shop') }}"><i class="fas fa-store"></i>掲載希望の店舗はコチラ</a>
    </p>
</div>
@endsection
