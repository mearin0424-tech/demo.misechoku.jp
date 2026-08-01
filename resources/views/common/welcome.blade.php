@extends('layouts.lp')

@section('title', 'ミセチョク｜水商売・夜職のキャストファースト求人')
@section('meta_description', '直接マッチングで「お祝い金」を還元する、夜職のキャストファースト求人プラットフォーム「ミセチョク」。匿名でお店とやり取りができ、入店時にボーナスがもらえます。')
@section('body-class', 'lp-welcome')

@push('styles')
<style>
    /* =========================================================
       LP tokens — DESIGN.md（ダーク × アメジスト × ゴールド）追従
       ========================================================= */
    :root {
        --lp-bg:            #050505;
        --lp-surface-from:  #1a1a1a;
        --lp-surface-to:    #0a0a0a;
        --lp-text:          #f5f5f5;
        --lp-text-sub:      #a0a0a0;
        --lp-text-mute:     #6b6b6b;

        --lp-accent:        #a78bfa;   /* amethyst light */
        --lp-accent-deep:   #7c3aed;   /* amethyst deep */
        --lp-accent-glow:   rgba(168, 85, 247, 0.55);
        --lp-accent-soft:   rgba(168, 85, 247, 0.12);
        --lp-line:          #2a2a2a;
        --lp-line-accent:   rgba(168, 85, 247, 0.40);

        --lp-gold-from:     #f6d36a;
        --lp-gold:          #d4af37;
        --lp-gold-to:       #b8860b;
        --lp-gold-glow:     rgba(212, 175, 55, 0.45);

        --lp-line-green:    #06c755;

        --lp-shadow-card:   6px 6px 16px rgba(0,0,0,.9), -2px -2px 6px rgba(255,255,255,.04), inset 1px 1px 1px rgba(255,255,255,.06);
        --lp-shadow-btn:    inset 0 4px 6px rgba(255,255,255,.35), inset 0 -6px 6px rgba(0,0,0,.45), 0 12px 28px rgba(124,58,237,.45);
        --lp-shadow-gold:   inset 0 4px 6px rgba(255,255,255,.35), inset 0 -6px 6px rgba(0,0,0,.45), 0 10px 22px rgba(212,175,55,.4);
        --lp-shadow-glass:  0 4px 12px rgba(0,0,0,.6), inset 0 1px 1px rgba(255,255,255,.12), inset 0 -1px 1px rgba(0,0,0,.5);

        --font-display: "Montserrat", "Noto Sans JP", sans-serif;
        --font-serif:   "Cormorant Garamond", "Noto Serif JP", serif;
        --font-sans:    "Noto Sans JP", sans-serif;

        --lp-content-pad: 22px;
    }

    body.lp-welcome {
        background:
            radial-gradient(60% 40% at 80% 0%, rgba(124, 58, 237, .25), transparent 70%),
            radial-gradient(50% 30% at 0% 30%, rgba(168, 85, 247, .15), transparent 70%),
            radial-gradient(40% 30% at 100% 60%, rgba(212, 175, 55, .08), transparent 70%),
            var(--lp-bg);
        background-attachment: fixed;
        color: var(--lp-text);
    }

    .lp-shell { max-width: 720px; margin: 0 auto; padding-bottom: 140px; position: relative; }

    /* =========================================================
       Topbar — glass header
       ========================================================= */
    .lp-topbar {
        position: sticky; top: 0; z-index: 30;
        background: rgba(147, 51, 234, .15);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-bottom: 1px solid var(--lp-line-accent);
        box-shadow: var(--lp-shadow-glass);
    }
    .lp-topbar__inner {
        max-width: 720px; margin: 0 auto;
        padding: 12px var(--lp-content-pad);
        display: flex; align-items: center; justify-content: space-between;
    }
    .lp-topbar__logo {
        font-family: var(--font-display);
        font-weight: 800; letter-spacing: 0.22em;
        font-size: 1.05rem;
        background: linear-gradient(135deg, #fff 0%, var(--lp-accent) 60%, var(--lp-accent-deep) 100%);
        -webkit-background-clip: text; background-clip: text;
        color: transparent;
        margin: 0;
    }
    .lp-topbar__logo::after {
        content: '✦'; margin-left: 8px;
        color: var(--lp-gold);
        -webkit-text-fill-color: var(--lp-gold);
        font-size: 0.75rem; vertical-align: middle;
    }
    .lp-topbar__login {
        font-size: 0.8rem; font-weight: 700; text-decoration: none;
        color: var(--lp-text);
        padding: 7px 14px; border-radius: 999px;
        border: 1px solid var(--lp-line-accent);
        background: rgba(255,255,255,.04);
        backdrop-filter: blur(6px);
        transition: all .2s ease;
    }
    .lp-topbar__login:hover { background: var(--lp-accent-soft); border-color: var(--lp-accent); }
    .lp-topbar__login i { margin-right: 4px; color: var(--lp-accent); }

    /* =========================================================
       Hero
       ========================================================= */
    .lp-hero {
        position: relative; overflow: hidden;
        padding: 64px var(--lp-content-pad) 56px;
        text-align: center;
    }
    .lp-hero::before {
        content: ''; position: absolute;
        inset: 16px;
        border: 1px solid var(--lp-line-accent);
        border-radius: 6px; pointer-events: none;
        background:
            linear-gradient(135deg, rgba(168, 85, 247,.08), transparent 40%),
            linear-gradient(315deg, rgba(212,175,55,.06), transparent 40%);
    }
    .lp-hero::after {
        content: ''; position: absolute;
        top: 14%; left: 8%; width: 6px; height: 6px;
        background: var(--lp-gold);
        border-radius: 50%;
        box-shadow:
            0 0 12px var(--lp-gold-glow),
            180px 60px 0 -1px rgba(168, 85, 247,.8), 180px 60px 8px rgba(168, 85, 247,.5),
            -30px 220px 0 -2px rgba(255,255,255,.6), -30px 220px 8px rgba(255,255,255,.3),
            220px 280px 0 -1px var(--lp-gold), 220px 280px 8px var(--lp-gold-glow);
        animation: lp-twinkle 3.6s ease-in-out infinite;
    }
    @keyframes lp-twinkle {
        0%, 100% { opacity: .85; }
        50%      { opacity: .3; }
    }

    .lp-hero__eyebrow {
        position: relative; z-index: 1;
        display: inline-flex; align-items: center; gap: 10px;
        font-family: var(--font-display);
        font-size: 0.72rem; letter-spacing: 0.36em;
        font-weight: 700; color: var(--lp-accent);
        padding: 6px 16px; border-radius: 999px;
        border: 1px solid var(--lp-line-accent);
        background: rgba(168, 85, 247, .08);
        backdrop-filter: blur(4px);
        margin-bottom: 22px;
        text-transform: uppercase;
    }
    .lp-hero__eyebrow::before,
    .lp-hero__eyebrow::after {
        content: '✦'; color: var(--lp-gold); font-size: 0.7rem; letter-spacing: 0;
    }

    .lp-hero__catch {
        position: relative; z-index: 1;
        font-family: var(--font-sans);
        font-size: clamp(1.55rem, 6.6vw, 2.3rem);
        font-weight: 900; line-height: 1.55;
        color: var(--lp-text); margin: 0 0 18px;
        letter-spacing: 0.02em;
        text-shadow: 0 2px 12px rgba(0,0,0,.6);
    }
    .lp-hero__catch em {
        font-style: normal;
        background: linear-gradient(135deg, var(--lp-accent) 0%, var(--lp-accent-deep) 100%);
        -webkit-background-clip: text; background-clip: text;
        color: transparent;
        position: relative;
        padding: 0 4px;
    }
    .lp-hero__catch em::after {
        content: ''; position: absolute;
        left: 4px; right: 4px; bottom: -2px;
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--lp-accent) 50%, transparent);
        border-radius: 999px;
        filter: blur(2px);
    }
    .lp-hero__catch .lp-gold {
        background: linear-gradient(135deg, var(--lp-gold-from), var(--lp-gold) 60%, var(--lp-gold-to));
        -webkit-background-clip: text; background-clip: text;
        color: transparent;
        font-style: italic;
        font-family: var(--font-serif);
        font-weight: 600;
        padding: 0 2px;
    }

    .lp-hero__lead {
        position: relative; z-index: 1;
        font-size: 0.95rem; line-height: 1.95;
        color: var(--lp-text-sub); margin: 0 auto 28px; max-width: 460px;
    }

    .lp-hero__badges {
        position: relative; z-index: 1;
        display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;
        margin: 0 auto;
    }
    .lp-hero__badge {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 0.78rem; font-weight: 700;
        padding: 8px 14px; border-radius: 999px;
        background: rgba(255,255,255,.04);
        color: var(--lp-text);
        border: 1px solid var(--lp-line-accent);
        backdrop-filter: blur(4px);
    }
    .lp-hero__badge i { color: var(--lp-accent); }
    .lp-hero__badge--gold i { color: var(--lp-gold); }

    /* =========================================================
       Hero CTA — ファーストビューで登録導線を完結させる
       ========================================================= */
    .lp-hero__cta {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        position: relative;
        z-index: 1;
    }
    .lp-hero__cta-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: min(360px, 100%);
        padding: 17px 28px;
        border-radius: 999px;
        font-family: var(--font-sans);
        font-size: 1.02rem;
        font-weight: 900;
        letter-spacing: 0.04em;
        text-decoration: none;
        color: #1a0f2e;
        background: linear-gradient(135deg, #e9d5ff 0%, var(--lp-accent) 45%, var(--lp-accent-deep) 100%);
        box-shadow: var(--lp-shadow-btn);
        transition: transform 0.15s ease, box-shadow 0.2s ease, filter 0.15s ease;
        position: relative;
        overflow: hidden;
    }
    .lp-hero__cta-primary::after {
        content: '';
        position: absolute;
        top: 0; left: -80%;
        width: 60%; height: 100%;
        background: linear-gradient(105deg, transparent, rgba(255,255,255,0.45), transparent);
        transform: skewX(-20deg);
        animation: lp-cta-sheen 3.6s ease-in-out infinite;
    }
    @keyframes lp-cta-sheen {
        0%, 60%, 100% { left: -80%; }
        75%           { left: 130%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .lp-hero__cta-primary::after { animation: none; }
    }
    .lp-hero__cta-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
    .lp-hero__cta-primary:active { transform: translateY(1px); }
    .lp-hero__cta-primary i { font-size: 1.1rem; }

    .lp-hero__cta-micro {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        font-size: 0.74rem;
        font-weight: 700;
        color: var(--lp-text-sub);
        letter-spacing: 0.04em;
    }
    .lp-hero__cta-micro i { color: var(--lp-gold); font-size: 0.7rem; margin-right: 3px; }

    .lp-hero__cta-shop {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--lp-text-sub);
        text-decoration: none;
        border-bottom: 1px dashed var(--lp-line-accent);
        padding-bottom: 2px;
        transition: color 0.15s ease, border-color 0.15s ease;
    }
    .lp-hero__cta-shop:hover { color: var(--lp-accent); border-color: var(--lp-accent); }
    .lp-hero__cta-shop i { margin-right: 4px; color: var(--lp-accent); }

    /* =========================================================
       Mid-page CTA band — 感情の高まった位置での再誘導
       ========================================================= */
    .lp-cta-band {
        margin: 0 var(--lp-content-pad);
        padding: 30px 22px;
        border-radius: 18px;
        text-align: center;
        background:
            radial-gradient(80% 100% at 50% 0%, rgba(168, 85, 247, 0.22), transparent 70%),
            linear-gradient(180deg, var(--lp-surface-from), var(--lp-surface-to));
        border: 1px solid var(--lp-line-accent);
        box-shadow: var(--lp-shadow-card);
        position: relative;
        overflow: hidden;
    }
    .lp-cta-band::before {
        content: '✦';
        position: absolute;
        top: 12px; right: 16px;
        color: var(--lp-gold);
        font-size: 0.8rem;
        opacity: 0.7;
    }
    .lp-cta-band__title {
        margin: 0 0 6px;
        font-family: var(--font-sans);
        font-size: 1.12rem;
        font-weight: 900;
        color: var(--lp-text);
        line-height: 1.5;
    }
    .lp-cta-band__title .lp-gold {
        background: linear-gradient(135deg, var(--lp-gold-from), var(--lp-gold-to));
        -webkit-background-clip: text; background-clip: text; color: transparent;
    }
    .lp-cta-band__sub {
        margin: 0 0 18px;
        font-size: 0.8rem;
        color: var(--lp-text-sub);
        line-height: 1.7;
    }
    .lp-cta-band .lp-hero__cta-primary { width: min(320px, 100%); }
    .lp-cta-band__micro {
        display: block;
        margin-top: 10px;
        font-size: 0.7rem;
        color: var(--lp-text-mute);
    }

    /* =========================================================
       Stats — 数字で見るミセチョク（信頼感の補強）
       ========================================================= */
    .lp-stats {
        margin: 8px var(--lp-content-pad) 0;
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        padding: 22px 16px;
        background: linear-gradient(135deg, rgba(124,58,237,.14), rgba(5,5,5,.6));
        border: 1px solid var(--lp-line-accent);
        border-radius: 18px;
        backdrop-filter: blur(6px);
        box-shadow: var(--lp-shadow-card);
    }
    .lp-stat { text-align: center; position: relative; }
    .lp-stat + .lp-stat::before {
        content: ''; position: absolute; left: -6px; top: 8px; bottom: 8px;
        width: 1px;
        background: linear-gradient(180deg, transparent, var(--lp-line-accent), transparent);
    }
    .lp-stat__num {
        font-family: var(--font-display);
        font-size: clamp(1.45rem, 5.4vw, 1.9rem);
        font-weight: 800; line-height: 1.1;
        background: linear-gradient(135deg, var(--lp-gold-from), var(--lp-gold) 60%, var(--lp-gold-to));
        -webkit-background-clip: text; background-clip: text;
        color: transparent;
        letter-spacing: 0.02em;
    }
    .lp-stat__num small {
        font-size: 0.55em; font-weight: 700;
        margin-left: 2px;
        background: inherit; -webkit-background-clip: text; background-clip: text; color: transparent;
    }
    .lp-stat__label {
        margin-top: 4px;
        font-size: 0.72rem; color: var(--lp-text-sub);
        letter-spacing: 0.06em;
    }

    /* =========================================================
       Section common
       ========================================================= */
    .lp-section { padding: 64px var(--lp-content-pad); position: relative; }
    .lp-section--alt::before {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(180deg, rgba(124,58,237,.06), transparent 30%, transparent 70%, rgba(124,58,237,.06));
        pointer-events: none;
    }
    .lp-section__head { text-align: center; margin-bottom: 36px; position: relative; z-index: 1; }
    .lp-section__eyebrow {
        display: inline-block;
        font-family: var(--font-display);
        font-size: 0.7rem; letter-spacing: 0.4em;
        font-weight: 700; color: var(--lp-accent);
        margin-bottom: 10px;
        text-transform: uppercase;
    }
    .lp-section__title {
        font-family: var(--font-sans);
        font-size: clamp(1.35rem, 5.2vw, 1.8rem);
        font-weight: 900; color: var(--lp-text); margin: 0;
        letter-spacing: 0.05em;
        text-shadow: 0 2px 12px rgba(0,0,0,.5);
    }
    .lp-section__title-ornament {
        display: flex; align-items: center; justify-content: center;
        gap: 14px; margin-top: 12px;
    }
    .lp-section__title-ornament::before,
    .lp-section__title-ornament::after {
        content: ''; flex: 0 0 40px; height: 1px;
        background: linear-gradient(90deg, transparent, var(--lp-accent), transparent);
    }
    .lp-section__title-ornament span {
        font-size: 0.85rem; color: var(--lp-gold);
        text-shadow: 0 0 8px var(--lp-gold-glow);
    }
    .lp-section__sub {
        margin: 16px auto 0; max-width: 480px;
        font-size: 0.9rem; line-height: 1.9; color: var(--lp-text-sub);
        position: relative; z-index: 1;
    }

    /* =========================================================
       Benefits
       ========================================================= */
    .lp-benefits {
        position: relative; z-index: 1;
        display: grid; gap: 16px;
    }
    @media (min-width: 768px) { .lp-benefits { grid-template-columns: repeat(3, 1fr); } }

    .lp-benefit {
        position: relative;
        background: linear-gradient(135deg, var(--lp-surface-from), var(--lp-surface-to));
        border: 1px solid var(--lp-line-accent);
        border-radius: 20px;
        padding: 28px 22px 24px;
        text-align: center;
        box-shadow: var(--lp-shadow-card);
        transition: transform .3s ease, box-shadow .3s ease;
        overflow: hidden;
    }
    .lp-benefit::before {
        content: ''; position: absolute;
        top: 0; left: 0; right: 0; height: 2px;
        background: linear-gradient(90deg, transparent, var(--lp-accent), transparent);
        opacity: .8;
    }
    .lp-benefit:hover { transform: translateY(-3px); }
    .lp-benefit__icon {
        width: 64px; height: 64px; margin: 0 auto 16px;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, var(--lp-accent), var(--lp-accent-deep));
        color: #fff;
        font-size: 1.4rem;
        box-shadow:
            inset 0 2px 4px rgba(255,255,255,.4),
            inset 0 -3px 4px rgba(0,0,0,.3),
            0 8px 20px var(--lp-accent-glow);
    }
    .lp-benefit--gold .lp-benefit__icon {
        background: linear-gradient(135deg, var(--lp-gold-from), var(--lp-gold) 60%, var(--lp-gold-to));
        color: #2a1d0f;
        box-shadow:
            inset 0 2px 4px rgba(255,255,255,.45),
            inset 0 -3px 4px rgba(0,0,0,.35),
            0 8px 20px var(--lp-gold-glow);
    }
    .lp-benefit__title {
        font-family: var(--font-sans);
        font-size: 1.08rem; font-weight: 800;
        color: var(--lp-text); margin: 0 0 10px;
        letter-spacing: 0.02em;
    }
    .lp-benefit__body {
        font-size: 0.86rem; line-height: 1.9;
        color: var(--lp-text-sub); margin: 0;
    }
    .lp-benefit__body strong {
        background: linear-gradient(135deg, var(--lp-gold-from), var(--lp-gold-to));
        -webkit-background-clip: text; background-clip: text;
        color: transparent;
        font-weight: 800;
    }
    .lp-benefit__note {
        display: block; margin-top: 8px;
        font-size: 0.72rem; color: var(--lp-text-mute);
    }

    /* =========================================================
       Steps
       ========================================================= */
    .lp-steps {
        position: relative; z-index: 1;
        display: grid; gap: 22px;
        padding: 0; margin: 0;
    }
    @media (min-width: 768px) { .lp-steps { grid-template-columns: repeat(2, 1fr); } }
    .lp-step {
        position: relative;
        background: linear-gradient(135deg, var(--lp-surface-from), var(--lp-surface-to));
        border: 1px solid var(--lp-line-accent);
        border-radius: 18px;
        padding: 30px 22px 22px;
        list-style: none;
        box-shadow: var(--lp-shadow-card);
    }
    .lp-step__num {
        position: absolute; top: -18px; left: 20px;
        width: 40px; height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--lp-accent), var(--lp-accent-deep));
        color: #fff;
        font-family: var(--font-display);
        font-weight: 800; font-size: 1.1rem;
        display: inline-flex; align-items: center; justify-content: center;
        box-shadow:
            inset 0 2px 4px rgba(255,255,255,.4),
            inset 0 -3px 4px rgba(0,0,0,.4),
            0 6px 16px var(--lp-accent-glow);
        border: 1px solid rgba(255,255,255,.15);
    }
    .lp-step__num::before {
        content: ''; position: absolute; inset: -6px;
        border: 1px solid var(--lp-accent);
        border-radius: 50%;
        opacity: .4;
    }
    .lp-step__icon {
        font-size: 1.4rem; color: var(--lp-accent); margin: 6px 0 10px;
        filter: drop-shadow(0 0 6px var(--lp-accent-glow));
    }
    .lp-step__icon.lp-step__icon--line { color: var(--lp-line-green); filter: drop-shadow(0 0 6px rgba(6,199,85,.5)); }
    .lp-step__icon.lp-step__icon--gold { color: var(--lp-gold); filter: drop-shadow(0 0 8px var(--lp-gold-glow)); }
    .lp-step__title {
        font-family: var(--font-sans);
        font-size: 1.05rem; font-weight: 800;
        color: var(--lp-text); margin: 0 0 8px;
    }
    .lp-step__body {
        font-size: 0.86rem; line-height: 1.9;
        color: var(--lp-text-sub); margin: 0;
    }
    .lp-step__note {
        display: block; margin-top: 8px;
        font-size: 0.72rem; color: var(--lp-text-mute);
    }

    /* =========================================================
       Voices — 先輩キャストの声
       ========================================================= */
    .lp-voices {
        position: relative; z-index: 1;
        display: grid; gap: 14px;
    }
    @media (min-width: 768px) { .lp-voices { grid-template-columns: repeat(2, 1fr); } }
    .lp-voice {
        position: relative;
        background: linear-gradient(135deg, rgba(168, 85, 247,.08), rgba(5,5,5,.7));
        border: 1px solid var(--lp-line-accent);
        border-radius: 18px;
        padding: 22px 20px 20px;
        box-shadow: var(--lp-shadow-card);
    }
    .lp-voice::before {
        content: '\201C';
        position: absolute; top: -8px; left: 16px;
        font-family: var(--font-serif);
        font-size: 3.2rem; line-height: 1;
        color: var(--lp-accent);
        text-shadow: 0 0 12px var(--lp-accent-glow);
    }
    .lp-voice__body {
        font-size: 0.88rem; line-height: 1.95;
        color: var(--lp-text); margin: 0 0 14px;
        padding-top: 14px;
    }
    .lp-voice__meta {
        display: flex; align-items: center; gap: 10px;
        font-size: 0.78rem; color: var(--lp-text-sub);
        padding-top: 12px;
        border-top: 1px dashed var(--lp-line);
    }
    .lp-voice__avatar {
        width: 32px; height: 32px; border-radius: 50%;
        background: linear-gradient(135deg, var(--lp-accent), var(--lp-accent-deep));
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 0.8rem;
        box-shadow: 0 0 8px var(--lp-accent-glow);
    }
    .lp-voice__bonus {
        margin-left: auto;
        font-family: var(--font-display);
        font-weight: 800; font-size: 0.78rem;
        background: linear-gradient(135deg, var(--lp-gold-from), var(--lp-gold-to));
        -webkit-background-clip: text; background-clip: text;
        color: transparent;
    }

    /* =========================================================
       FAQ
       ========================================================= */
    .lp-faqs {
        position: relative; z-index: 1;
        display: flex; flex-direction: column; gap: 10px;
        max-width: 640px; margin: 0 auto;
    }
    .lp-faq {
        background: linear-gradient(135deg, var(--lp-surface-from), var(--lp-surface-to));
        border: 1px solid var(--lp-line);
        border-radius: 14px; overflow: hidden;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .lp-faq[open] {
        border-color: var(--lp-line-accent);
        box-shadow: 0 6px 20px rgba(0,0,0,.5), 0 0 0 1px var(--lp-line-accent);
    }
    .lp-faq__summary {
        list-style: none; cursor: pointer;
        padding: 18px 50px 18px 20px;
        position: relative;
        font-size: 0.93rem; font-weight: 700; color: var(--lp-text);
        display: flex; gap: 12px; align-items: flex-start;
    }
    .lp-faq__summary::-webkit-details-marker { display: none; }
    .lp-faq__summary::before {
        content: 'Q.';
        flex-shrink: 0;
        font-family: var(--font-display);
        font-weight: 800;
        color: var(--lp-accent);
    }
    .lp-faq__summary::after {
        content: '\f078';
        font-family: 'Font Awesome 6 Free'; font-weight: 900;
        position: absolute; right: 20px; top: 50%; transform: translateY(-50%);
        font-size: 0.72rem; color: var(--lp-accent);
        transition: transform 0.2s ease;
    }
    .lp-faq[open] .lp-faq__summary::after { transform: translateY(-50%) rotate(180deg); }
    .lp-faq__body {
        padding: 0 20px 20px;
        font-size: 0.86rem; line-height: 1.95; color: var(--lp-text-sub);
        display: flex; gap: 12px;
    }
    .lp-faq__body::before {
        content: 'A.';
        flex-shrink: 0;
        font-family: var(--font-display);
        font-weight: 800;
        color: var(--lp-gold);
    }
    .lp-faq__body p { margin: 0; }
    .lp-faq__body p + p { margin-top: 6px; }

    /* =========================================================
       LINE Contact
       ========================================================= */
    .lp-contact {
        position: relative; z-index: 1;
        max-width: 540px; margin: 0 auto;
        background: linear-gradient(135deg, rgba(6,199,85,.08), rgba(5,5,5,.8));
        border: 1px solid rgba(6,199,85,.35);
        border-radius: 22px;
        padding: 32px 24px;
        text-align: center;
        box-shadow: var(--lp-shadow-card);
        overflow: hidden;
    }
    .lp-contact::before {
        content: ''; position: absolute;
        top: 0; left: 0; right: 0; height: 2px;
        background: linear-gradient(90deg, transparent, var(--lp-line-green), transparent);
    }
    .lp-contact__title {
        font-family: var(--font-sans);
        font-size: 1.12rem; font-weight: 800;
        color: var(--lp-text); margin: 0 0 10px;
    }
    .lp-contact__body {
        font-size: 0.86rem; line-height: 1.9; color: var(--lp-text-sub);
        margin: 0 0 20px;
    }
    .lp-line-btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 10px;
        background: var(--lp-line-green);
        color: #fff;
        font-weight: 800; text-decoration: none;
        padding: 15px 32px; border-radius: 999px;
        font-size: 0.96rem;
        box-shadow:
            inset 0 2px 4px rgba(255,255,255,.3),
            inset 0 -3px 4px rgba(0,0,0,.25),
            0 12px 28px rgba(6, 199, 85, 0.45);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .lp-line-btn:hover { transform: translateY(-1px); box-shadow: 0 14px 32px rgba(6, 199, 85, 0.55); }
    .lp-line-btn i { font-size: 1.25rem; }
    .lp-contact__note {
        display: block; margin-top: 12px;
        font-size: 0.74rem; color: var(--lp-text-mute);
    }

    /* =========================================================
       Company
       ========================================================= */
    .lp-company {
        position: relative; z-index: 1;
        max-width: 640px; margin: 0 auto;
        background: linear-gradient(135deg, var(--lp-surface-from), var(--lp-surface-to));
        border: 1px solid var(--lp-line-accent);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--lp-shadow-card);
    }
    .lp-company__row {
        display: grid; grid-template-columns: 110px 1fr;
        gap: 0;
        padding: 16px 20px;
        font-size: 0.86rem; line-height: 1.7;
        border-bottom: 1px dashed var(--lp-line);
    }
    .lp-company__row:last-child { border-bottom: none; }
    .lp-company__row dt {
        font-weight: 700;
        font-family: var(--font-display);
        color: var(--lp-accent);
        letter-spacing: 0.04em;
        margin: 0;
    }
    .lp-company__row dd { margin: 0; color: var(--lp-text-sub); }
    .lp-company__more {
        position: relative; z-index: 1;
        display: block; text-align: center;
        margin-top: 18px;
        font-size: 0.84rem; color: var(--lp-accent);
        text-decoration: none;
        font-weight: 700;
        letter-spacing: 0.04em;
    }
    .lp-company__more:hover { color: var(--lp-accent-deep); }

    /* =========================================================
       Footer
       ========================================================= */
    .lp-footer {
        text-align: center;
        padding: 32px var(--lp-content-pad) 36px;
        font-size: 0.78rem; color: var(--lp-text-mute);
        background: rgba(0,0,0,.4);
        border-top: 1px solid var(--lp-line);
    }
    .lp-footer a { color: var(--lp-text-sub); text-decoration: none; margin: 0 8px; transition: color .2s ease; }
    .lp-footer a:hover { color: var(--lp-accent); }
    .lp-footer__copy { margin-top: 10px; font-size: 0.74rem; color: var(--lp-text-mute); }

    /* =========================================================
       Fixed CTA
       ========================================================= */
    .lp-fixed-cta {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 50;
        background: rgba(10, 5, 20, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-top: 1px solid var(--lp-line-accent);
        box-shadow: 0 -10px 30px rgba(0,0,0,.7), inset 0 1px 1px rgba(255,255,255,.1);
        padding: 14px var(--lp-content-pad) calc(14px + env(safe-area-inset-bottom));
    }
    .lp-fixed-cta__inner {
        max-width: 720px; margin: 0 auto;
        display: flex; align-items: center; gap: 14px;
    }
    .lp-fixed-cta__pitch {
        flex: 0 0 auto;
        font-size: 0.78rem; line-height: 1.4;
        color: var(--lp-text);
    }
    .lp-fixed-cta__pitch strong {
        display: block; font-size: 0.95rem; font-weight: 800;
        background: linear-gradient(135deg, var(--lp-gold-from), var(--lp-gold), var(--lp-gold-to));
        -webkit-background-clip: text; background-clip: text;
        color: transparent;
        letter-spacing: 0.04em;
    }
    .lp-fixed-cta__btn {
        position: relative;
        flex: 1 1 auto; min-width: 0;
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--lp-accent) 0%, var(--lp-accent-deep) 100%);
        color: #fff;
        font-weight: 800; text-decoration: none;
        padding: 14px 20px; border-radius: 999px;
        font-size: 0.95rem; letter-spacing: 0.06em;
        box-shadow: var(--lp-shadow-btn);
        border: 1px solid rgba(255,255,255,.15);
        overflow: hidden;
        transition: transform 0.15s ease;
    }
    .lp-fixed-cta__btn::before {
        content: ''; position: absolute;
        top: 0; left: -100%; height: 100%; width: 50%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent);
        animation: lp-shimmer 3s infinite;
    }
    @keyframes lp-shimmer {
        0%   { left: -100%; }
        60%  { left: 140%; }
        100% { left: 140%; }
    }
    .lp-fixed-cta__btn:hover { transform: translateY(-1px); }
    .lp-fixed-cta__btn:active { transform: translateY(1px); }
    .lp-fixed-cta__shop {
        text-align: center; margin: 10px 0 0;
        font-size: 0.78rem;
    }
    .lp-fixed-cta__shop a {
        color: var(--lp-text-sub);
        text-decoration: none;
        font-weight: 700;
        border-bottom: 1px dashed var(--lp-line);
        padding-bottom: 2px;
    }
    .lp-fixed-cta__shop a:hover { color: var(--lp-accent); border-color: var(--lp-accent); }
    .lp-fixed-cta__shop a i { margin-right: 4px; color: var(--lp-accent); }

    @media (min-width: 600px) {
        .lp-fixed-cta__pitch { font-size: 0.85rem; }
        .lp-fixed-cta__btn { padding: 15px 24px; font-size: 1rem; }
    }

    /* =========================================================
       小ユーティリティ
       ========================================================= */
    .lp-divider-mark {
        text-align: center; margin: 0 auto;
        color: var(--lp-gold);
        letter-spacing: 1em;
        font-size: 0.7rem;
        opacity: .7;
    }
</style>
@endpush

@section('content')
{{-- トップバー --}}
<div class="lp-topbar">
    <div class="lp-topbar__inner">
        <h1 class="lp-topbar__logo">MISECHOKU</h1>
        <a href="{{ route('cast.login') }}" class="lp-topbar__login">
            <i class="fas fa-right-to-bracket"></i> ログイン
        </a>
    </div>
</div>

<div class="lp-shell">
    {{-- Hero --}}
    <header class="lp-hero">
        <span class="lp-hero__eyebrow">Cast First Platform</span>
        <h2 class="lp-hero__catch">
            <span class="lp-gold">お祝い金</span>がもらえる、<br>
            <em>キャストファースト</em>の<br class="sm:hidden">
            夜職マッチング。
        </h2>
        <p class="lp-hero__lead">
            お店と直接やり取りできるから、スカウトを通さず手取りアップ。<br>
            匿名でメッセージできるので、初めての方も安心です。
        </p>
        <div class="lp-hero__badges">
            <span class="lp-hero__badge"><i class="fas fa-yen-sign"></i> 完全無料</span>
            <span class="lp-hero__badge"><i class="fas fa-user-secret"></i> 匿名OK</span>
            <span class="lp-hero__badge lp-hero__badge--gold"><i class="fas fa-gift"></i> 採用報酬あり</span>
        </div>

        {{-- ファーストビュー CTA：スクロールさせずに登録導線を完結 --}}
        <div class="lp-hero__cta">
            <a href="{{ route('cast.register') }}" class="lp-hero__cta-primary">
                <i class="fas fa-wand-magic-sparkles"></i> 無料でお店を探してみる
            </a>
            <span class="lp-hero__cta-micro">
                <span><i class="fas fa-stopwatch"></i>登録は30秒</span>
                <span><i class="fas fa-yen-sign"></i>ずっと無料</span>
                <span><i class="fas fa-user-secret"></i>匿名OK</span>
            </span>
            <a href="{{ route('welcome.shop') }}" class="lp-hero__cta-shop">
                <i class="fas fa-store"></i>掲載をご希望の店舗さまはこちら
            </a>
        </div>
    </header>

    {{-- Stats（信頼の数字） --}}
    <div class="lp-stats" aria-label="ミセチョクの実績">
        <div class="lp-stat">
            <div class="lp-stat__num">10<small>万円〜</small></div>
            <div class="lp-stat__label">最大お祝い金</div>
        </div>
        <div class="lp-stat">
            <div class="lp-stat__num">100<small>%</small></div>
            <div class="lp-stat__label">完全無料</div>
        </div>
        <div class="lp-stat">
            <div class="lp-stat__num">24<small>h</small></div>
            <div class="lp-stat__label">匿名でやり取り</div>
        </div>
    </div>

    {{-- 特長 --}}
    <section class="lp-section" aria-labelledby="lp-benefits-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">Features</span>
            <h2 id="lp-benefits-title" class="lp-section__title">ミセチョクの特長</h2>
            <div class="lp-section__title-ornament"><span>✦</span></div>
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
            <div class="lp-benefit lp-benefit--gold">
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
            <span class="lp-section__eyebrow">How to Use</span>
            <h2 id="lp-guide-title" class="lp-section__title">使い方は簡単</h2>
            <div class="lp-section__title-ornament"><span>✦</span></div>
            <p class="lp-section__sub">入店準備金ゲットまで、たった4ステップ。</p>
        </div>
        <ol class="lp-steps">
            <li class="lp-step">
                <span class="lp-step__num">1</span>
                <i class="fab fa-line lp-step__icon lp-step__icon--line"></i>
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
                <i class="fas fa-gift lp-step__icon lp-step__icon--gold"></i>
                <h3 class="lp-step__title">入店決定！ボーナスゲット</h3>
                <p class="lp-step__body">入店が決まれば、店舗からボーナス（採用報酬）が届きます。良い条件で働きつつ、お祝い金まで受け取れるのがミセチョクの醍醐味です。</p>
            </li>
        </ol>
    </section>

    {{-- 先輩キャストの声 --}}
    <section class="lp-section" aria-labelledby="lp-voices-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">Voices</span>
            <h2 id="lp-voices-title" class="lp-section__title">先輩キャストの声</h2>
            <div class="lp-section__title-ornament"><span>✦</span></div>
            <p class="lp-section__sub">実際にミセチョクで入店した先輩キャストのリアルな声。</p>
        </div>
        <div class="lp-voices">
            <div class="lp-voice">
                <p class="lp-voice__body">
                    スカウトに振り回されず、自分のペースでお店を選べました。お祝い金で新しいドレスが買えて、初出勤からテンションMAX。
                </p>
                <div class="lp-voice__meta">
                    <span class="lp-voice__avatar">A</span>
                    <span>Aさん／22歳・キャバ未経験</span>
                    <span class="lp-voice__bonus">¥80,000</span>
                </div>
            </div>
            <div class="lp-voice">
                <p class="lp-voice__body">
                    匿名でやり取りできるから、お店探しのストレスがゼロ。気になる質問もチョクで聞けて、面接もスムーズでした。
                </p>
                <div class="lp-voice__meta">
                    <span class="lp-voice__avatar">M</span>
                    <span>Mさん／25歳・クラブ勤務2年</span>
                    <span class="lp-voice__bonus">¥100,000</span>
                </div>
            </div>
        </div>
    </section>

    {{-- 中間 CTA：先輩の声で感情が動いたタイミングで再誘導 --}}
    <div class="lp-cta-band" role="region" aria-label="無料登録の案内">
        <h2 class="lp-cta-band__title">
            次にお祝い金を受け取るのは、<br><span class="lp-gold">あなた</span>かもしれません。
        </h2>
        <p class="lp-cta-band__sub">最大10万円のお祝い金。登録も利用もずっと無料です。</p>
        <a href="{{ route('cast.register') }}" class="lp-hero__cta-primary">
            <i class="fas fa-wand-magic-sparkles"></i> 30秒で無料登録する
        </a>
        <span class="lp-cta-band__micro">しつこい連絡は一切ありません。匿名のままお店を探せます。</span>
    </div>

    {{-- FAQ --}}
    <section class="lp-section lp-section--alt" aria-labelledby="lp-faq-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">FAQ</span>
            <h2 id="lp-faq-title" class="lp-section__title">よくある質問</h2>
            <div class="lp-section__title-ornament"><span>✦</span></div>
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
    <section class="lp-section" aria-labelledby="lp-contact-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">Contact</span>
            <h2 id="lp-contact-title" class="lp-section__title">LINEでのお問い合わせ</h2>
            <div class="lp-section__title-ornament"><span>✦</span></div>
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
    <section class="lp-section lp-section--alt" aria-labelledby="lp-company-title">
        <div class="lp-section__head">
            <span class="lp-section__eyebrow">Company</span>
            <h2 id="lp-company-title" class="lp-section__title">運営会社</h2>
            <div class="lp-section__title-ornament"><span>✦</span></div>
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
            <strong>お祝い金 最大10万円</strong>
            <span>登録30秒・ずっと無料</span>
        </div>
        <a href="{{ route('cast.register') }}" class="lp-fixed-cta__btn">
            <i class="fas fa-wand-magic-sparkles"></i> 無料ではじめる
        </a>
    </div>
    <p class="lp-fixed-cta__shop">
        <a href="{{ route('welcome.shop') }}"><i class="fas fa-store"></i>掲載希望の店舗はコチラ</a>
    </p>
</div>
@endsection
