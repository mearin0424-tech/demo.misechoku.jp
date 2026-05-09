@extends('layouts.app')

@section('title', 'ミセチョク｜水商売・夜職のキャストファースト求人')
@section('meta_description', '直接マッチングで「お祝い金」を還元する、夜職のキャストファースト求人プラットフォーム「ミセチョク」。匿名でお店とやり取りができ、入店時にボーナスがもらえます。')
@section('body-class', 'page-welcome')

@push('styles')
<style>
    .welcome-shell { padding: 0 0 64px; color: #f5e0c4; }

    /* ===== Hero (LP) ===== */
    .welcome-hero {
        position: relative;
        padding: 56px var(--content-padding-x, 16px) 48px;
        text-align: center;
        background:
            radial-gradient(circle at 50% 0%, rgba(220, 181, 104, 0.22), transparent 55%),
            linear-gradient(180deg, rgba(82, 20, 47, 1), rgba(35, 8, 21, 1));
        overflow: hidden;
    }
    .welcome-hero::after {
        content: '';
        position: absolute;
        inset: -40% -20% auto auto;
        width: 320px; height: 320px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(220,181,104,0.18), transparent 60%);
        pointer-events: none;
    }
    .welcome-hero__logo {
        font-family: 'Shippori Mincho', serif;
        font-weight: 900;
        font-size: 2.2rem;
        letter-spacing: 0.18em;
        color: #ffe2a3;
        margin: 0 0 8px;
        text-shadow: 0 2px 12px rgba(220,181,104,0.4);
    }
    .welcome-hero__catch {
        font-family: 'Shippori Mincho', serif;
        font-size: clamp(1.05rem, 4.6vw, 1.45rem);
        font-weight: 800;
        line-height: 1.55;
        margin: 0 0 14px;
        color: #fff;
    }
    .welcome-hero__catch em {
        color: #ffd47a;
        font-style: normal;
        background: linear-gradient(transparent 60%, rgba(220,181,104,0.32) 60%);
        padding: 0 4px;
    }
    .welcome-hero__lead {
        font-size: 0.92rem;
        line-height: 1.75;
        color: rgba(245, 224, 196, 0.86);
        margin: 0 auto 28px;
        max-width: 520px;
    }
    .welcome-cta-grid {
        display: grid;
        gap: 12px;
        max-width: 380px;
        margin: 0 auto;
    }
    .welcome-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 14px 18px;
        border-radius: 999px;
        font-weight: 800;
        text-decoration: none;
        font-size: 0.95rem;
        letter-spacing: 0.04em;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .welcome-cta--cast {
        background: linear-gradient(135deg, #ffe2a3, #dcb568 50%, #b8860b);
        color: #2a1406;
        box-shadow: 0 6px 18px rgba(220,181,104,0.45);
    }
    .welcome-cta--shop {
        background: rgba(20, 12, 18, 0.6);
        color: #ffe2a3;
        border: 1px solid rgba(220,181,104,0.65);
    }
    .welcome-cta--login {
        background: transparent;
        color: rgba(245,224,196,0.82);
        border: 1px solid rgba(245,224,196,0.32);
        font-weight: 700;
    }
    .welcome-cta:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(220,181,104,0.5); }

    /* ===== セクション共通 ===== */
    .welcome-section { padding: 40px var(--content-padding-x, 16px); }
    .welcome-section + .welcome-section { padding-top: 24px; }
    .welcome-section__heading {
        text-align: center;
        margin: 0 0 24px;
    }
    .welcome-section__eyebrow {
        display: inline-block;
        font-size: 0.7rem;
        letter-spacing: 0.24em;
        font-weight: 800;
        color: rgba(220,181,104,0.85);
        margin-bottom: 6px;
    }
    .welcome-section__title {
        font-family: 'Shippori Mincho', serif;
        font-size: 1.35rem;
        font-weight: 800;
        color: #f8e9c8;
        margin: 0;
    }
    .welcome-section__subtitle {
        margin: 8px auto 0;
        font-size: 0.84rem;
        line-height: 1.7;
        color: rgba(245, 224, 196, 0.7);
        max-width: 560px;
    }

    /* ===== ベネフィット ===== */
    .welcome-benefits {
        display: grid;
        gap: 12px;
    }
    @media (min-width: 768px) {
        .welcome-benefits { grid-template-columns: repeat(3, 1fr); }
    }
    .welcome-benefit {
        padding: 18px 16px;
        border-radius: 16px;
        border: 1px solid rgba(220,181,104,0.22);
        background: linear-gradient(180deg, rgba(220,181,104,0.06), rgba(255,255,255,0.02));
    }
    .welcome-benefit i {
        font-size: 1.2rem;
        color: #dcb568;
        margin-bottom: 8px;
    }
    .welcome-benefit__title {
        font-size: 0.95rem;
        font-weight: 800;
        margin: 0 0 6px;
        color: #ffe2a3;
    }
    .welcome-benefit__body {
        font-size: 0.82rem;
        line-height: 1.7;
        color: rgba(245, 224, 196, 0.78);
        margin: 0;
    }
    .welcome-benefit__body strong { color: #ffe2a3; font-weight: 800; }
    .welcome-benefit__note {
        display: block;
        margin-top: 6px;
        font-size: 0.72rem;
        color: rgba(245, 224, 196, 0.55);
    }

    /* ===== ガイド（使い方） ===== */
    .welcome-guide {
        display: grid;
        gap: 14px;
    }
    @media (min-width: 768px) {
        .welcome-guide { grid-template-columns: repeat(4, 1fr); }
    }
    .welcome-step {
        position: relative;
        padding: 18px 16px 16px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(220,181,104,0.18);
    }
    .welcome-step__num {
        position: absolute;
        top: -12px;
        left: 14px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ffe2a3, #b8860b);
        color: #2a1406;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        box-shadow: 0 4px 10px rgba(220,181,104,0.45);
    }
    .welcome-step__icon { font-size: 1.4rem; color: #dcb568; margin: 6px 0 8px; }
    .welcome-step__title { font-size: 0.92rem; font-weight: 800; color: #ffe2a3; margin: 0 0 4px; }
    .welcome-step__body { font-size: 0.8rem; line-height: 1.65; color: rgba(245,224,196,0.72); margin: 0; }
    .welcome-step__note {
        display: block;
        margin-top: 6px;
        font-size: 0.7rem;
        color: rgba(245, 224, 196, 0.5);
    }

    /* ===== FAQ ===== */
    .welcome-faq-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 720px;
        margin: 0 auto;
    }
    .welcome-faq-item {
        border: 1px solid rgba(220,181,104,0.22);
        border-radius: 14px;
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        overflow: hidden;
    }
    .welcome-faq-item[open] {
        border-color: rgba(220,181,104,0.5);
        box-shadow: 0 4px 14px rgba(0,0,0,0.25);
    }
    .welcome-faq-summary {
        list-style: none;
        cursor: pointer;
        padding: 14px 48px 14px 16px;
        position: relative;
        font-size: 0.92rem;
        font-weight: 800;
        color: #ffe2a3;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        line-height: 1.55;
    }
    .welcome-faq-summary::-webkit-details-marker { display: none; }
    .welcome-faq-summary::before {
        content: 'Q.';
        flex-shrink: 0;
        color: #dcb568;
        font-family: 'Shippori Mincho', serif;
        font-size: 1rem;
        font-weight: 900;
    }
    .welcome-faq-summary::after {
        content: '\f078';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(220,181,104,0.65);
        font-size: 0.75rem;
        transition: transform 0.2s ease;
    }
    .welcome-faq-item[open] .welcome-faq-summary::after {
        transform: translateY(-50%) rotate(180deg);
    }
    .welcome-faq-body {
        padding: 0 16px 16px 16px;
        font-size: 0.86rem;
        line-height: 1.85;
        color: rgba(245, 224, 196, 0.85);
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .welcome-faq-body::before {
        content: 'A.';
        flex-shrink: 0;
        color: #6ee7b7;
        font-family: 'Shippori Mincho', serif;
        font-size: 1rem;
        font-weight: 900;
        margin-top: 2px;
    }
    .welcome-faq-body p { margin: 0; }
    .welcome-faq-body p + p { margin-top: 6px; }

    /* ===== 末尾 CTA ===== */
    .welcome-bottom-cta {
        text-align: center;
        margin-top: 28px;
        padding: 28px 16px;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(220,181,104,0.12), rgba(74,18,42,0.55));
        border: 1px solid rgba(220,181,104,0.4);
    }
    .welcome-bottom-cta p {
        margin: 0 0 14px;
        font-size: 0.92rem;
        line-height: 1.7;
        color: #ffe2a3;
    }
</style>
@endpush

@section('content')
<div class="welcome-shell">
    {{-- Hero（LP） --}}
    <header class="welcome-hero">
        <h1 class="welcome-hero__logo">ミセチョク</h1>
        <p class="welcome-hero__catch">
            お祝い金がもらえる、<br>
            <em>キャストファースト</em>の夜職マッチング。
        </p>
        <p class="welcome-hero__lead">
            お店と直接やり取りできるから、スカウトを通さず手取りアップ。<br class="hidden-sm">
            匿名でメッセージできるので、初めての方も安心です。
        </p>
        <div class="welcome-cta-grid">
            <a href="{{ route('cast.register') }}" class="welcome-cta welcome-cta--cast">
                <i class="fas fa-user-plus"></i> キャストとしてはじめる
            </a>
            <a href="{{ route('shop.register') }}" class="welcome-cta welcome-cta--shop">
                <i class="fas fa-store"></i> 店舗としてはじめる
            </a>
            <a href="{{ route('login.demo') }}" class="welcome-cta welcome-cta--login">
                <i class="fas fa-right-to-bracket"></i> ログインはこちら
            </a>
        </div>
    </header>

    {{-- 特長 ===== --}}
    <section class="welcome-section" aria-labelledby="welcome-benefits-title">
        <div class="welcome-section__heading">
            <span class="welcome-section__eyebrow">FEATURES</span>
            <h2 id="welcome-benefits-title" class="welcome-section__title">ミセチョクの特長</h2>
            <p class="welcome-section__subtitle">完全無料でご希望の業種の店舗様とマッチング。</p>
        </div>
        <div class="welcome-benefits">
            <div class="welcome-benefit">
                <i class="fas fa-map-location-dot"></i>
                <h3 class="welcome-benefit__title">全国のお店を探せる</h3>
                <p class="welcome-benefit__body">
                    わずらわしいやり取りなしで、スムーズにお店へアプローチ。
                    サイト内メッセージなので連絡先を交換せず、気軽に問い合わせできます。
                </p>
            </div>
            <div class="welcome-benefit">
                <i class="fas fa-coins"></i>
                <h3 class="welcome-benefit__title">採用報酬がもらえる</h3>
                <p class="welcome-benefit__body">
                    面接にパスして入店が決まると、本来スカウト会社へ支払われる「スカウトバック」を、
                    <strong>採用報酬</strong>としてご本人が受け取れます。
                    <span class="welcome-benefit__note">※ 条件は店舗ごとに異なります。各店舗の条件をご確認ください。</span>
                </p>
            </div>
            <div class="welcome-benefit">
                <i class="fas fa-circle-check"></i>
                <h3 class="welcome-benefit__title">完全無料で使える</h3>
                <p class="welcome-benefit__body">
                    マッチング・利用は完全無料。入店準備金で美容や衣装に自己投資し、お仕事のパフォーマンスもアップ。
                    個人情報は当協会のセキュリティで厳重に管理しています。
                </p>
            </div>
        </div>
    </section>

    {{-- 使い方ガイド ===== --}}
    <section class="welcome-section" aria-labelledby="welcome-guide-title">
        <div class="welcome-section__heading">
            <span class="welcome-section__eyebrow">HOW TO USE</span>
            <h2 id="welcome-guide-title" class="welcome-section__title">使い方は簡単！</h2>
            <p class="welcome-section__subtitle">入店準備金ゲットまで、たった4ステップ。</p>
        </div>
        <ol class="welcome-guide" style="list-style:none; padding:0; margin:0;">
            <li class="welcome-step">
                <span class="welcome-step__num">1</span>
                <i class="fab fa-line welcome-step__icon" style="color:#06c755;"></i>
                <h3 class="welcome-step__title">アカウント登録</h3>
                <p class="welcome-step__body">
                    LINEで簡単に登録。いくつかの質問に答えるだけで、希望条件にあうお店に出会いやすくなります。
                </p>
            </li>
            <li class="welcome-step">
                <span class="welcome-step__num">2</span>
                <i class="fas fa-magnifying-glass welcome-step__icon"></i>
                <h3 class="welcome-step__title">気になるお店を探す</h3>
                <p class="welcome-step__body">
                    条件検索で自分に合ったお店を絞り込み、気になるお店をチェック♪
                </p>
            </li>
            <li class="welcome-step">
                <span class="welcome-step__num">3</span>
                <i class="fas fa-comments welcome-step__icon"></i>
                <h3 class="welcome-step__title">チョクでメッセージ</h3>
                <p class="welcome-step__body">
                    お店の担当者に直接メッセージで質問・相談。面接日や体入日もここでOK。
                    <span class="welcome-step__note">※ サイト内メッセージBOXなので、連絡先を秘密にしたままやり取りできます。</span>
                </p>
            </li>
            <li class="welcome-step">
                <span class="welcome-step__num">4</span>
                <i class="fas fa-gift welcome-step__icon"></i>
                <h3 class="welcome-step__title">入店決定！ボーナスゲット</h3>
                <p class="welcome-step__body">
                    入店が決まれば、店舗からボーナス（採用報酬）が届きます。良い条件で働きつつ、お祝い金まで受け取れるのがミセチョクの醍醐味です。
                </p>
            </li>
        </ol>
    </section>

    {{-- FAQ ===== --}}
    <section class="welcome-section" aria-labelledby="welcome-faq-title">
        <div class="welcome-section__heading">
            <span class="welcome-section__eyebrow">FAQ</span>
            <h2 id="welcome-faq-title" class="welcome-section__title">よくある質問</h2>
        </div>
        <div class="welcome-faq-list">
            <details class="welcome-faq-item">
                <summary class="welcome-faq-summary">未経験者です。安全に使えますか？</summary>
                <div class="welcome-faq-body">
                    <div>
                        <p>ミセチョクはキャストさんファーストを目指しています。</p>
                        <p>店舗様と匿名でやり取りもでき、連絡先の交換なども不要です。未経験、経験者問わず安心して使用できます。</p>
                    </div>
                </div>
            </details>

            <details class="welcome-faq-item">
                <summary class="welcome-faq-summary">登録は誰でもできますか？</summary>
                <div class="welcome-faq-body">
                    <div>
                        <p>18歳以上であればどなたでも使えます。</p>
                        <p>簡単登録で自分に合ったお店を探してみてください。</p>
                    </div>
                </div>
            </details>

            <details class="welcome-faq-item">
                <summary class="welcome-faq-summary">どんな職種が登録されていますか？</summary>
                <div class="welcome-faq-body">
                    <div>
                        <p>キャバクラ、クラブ、ガールズバー、コンカフェ、スナック、ニュークラブなど幅広く店舗様の登録があります。</p>
                    </div>
                </div>
            </details>

            <details class="welcome-faq-item">
                <summary class="welcome-faq-summary">求人広告は騙しが怖いです。掲載店舗は優良ですか？</summary>
                <div class="welcome-faq-body">
                    <div>
                        <p>掲載店舗はミセチョクが営業許可証や代表者の確認、風営法の許可などをチェックしております。いわゆるモグリ店の登録はございません。</p>
                    </div>
                </div>
            </details>

            <details class="welcome-faq-item">
                <summary class="welcome-faq-summary">ミセチョクを使うと入店時にボーナスがもらえるのはナゼですか？</summary>
                <div class="welcome-faq-body">
                    <div>
                        <p>今まで店舗様がスカウトマンや求人広告会社に支払っていた費用をキャストさんに還元しているからです。</p>
                        <p>グレーな業界の慣習をなくしたい思いで、ミセチョクは各店舗と連携をとり、キャストさんファーストを実現しようとしてます。我々の理念です。</p>
                    </div>
                </div>
            </details>

            <details class="welcome-faq-item">
                <summary class="welcome-faq-summary">毎回スカウトマンにお店を紹介してもらっています。紹介を通せば、時給が高くなったりメリットはありますよね？</summary>
                <div class="welcome-faq-body">
                    <div>
                        <p>残念ながらキャストさんにメリットはほぼありません。逆にお店側は高いスカウトバックを払わなければならないのでしわ寄せはキャストさんやお客さんに来てしまっているのが現状です。</p>
                    </div>
                </div>
            </details>

            <details class="welcome-faq-item">
                <summary class="welcome-faq-summary">お店側に身バレしないですか？</summary>
                <div class="welcome-faq-body">
                    <div>
                        <p>ミセチョクでは匿名でのやり取りが可能です。</p>
                        <p>安心してメッセージのやり取りをしてください。</p>
                    </div>
                </div>
            </details>

            <details class="welcome-faq-item">
                <summary class="welcome-faq-summary">ミセチョクの最終目標はなんですか？</summary>
                <div class="welcome-faq-body">
                    <div>
                        <p>我々は全国のキャストさんが1番良い環境で働ける環境作りを目指しています。</p>
                        <p>店舗様の求人コストを減らし、キャストさんに還元。給与や待遇、臨時ボーナスがある。</p>
                        <p>そんなシンプルかつ大胆な仕組みを真面目に取り組んでおります。</p>
                    </div>
                </div>
            </details>
        </div>

        <div class="welcome-bottom-cta">
            <p>あなたに合った働き方を、ミセチョクではじめてみませんか？</p>
            <div class="welcome-cta-grid" style="max-width:340px;">
                <a href="{{ route('cast.register') }}" class="welcome-cta welcome-cta--cast">
                    <i class="fas fa-user-plus"></i> キャスト登録（無料）
                </a>
                <a href="{{ route('shop.register') }}" class="welcome-cta welcome-cta--shop">
                    <i class="fas fa-store"></i> 店舗登録（無料）
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
