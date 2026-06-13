@extends('layouts.app-v2')

@section('title', 'マイページ - 採用・入金管理')
@section('body-class', 'page-cast-mypage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/review-modal.css') }}">
<style>
    /* ========================================================
       採用・入金 統合タイムライン
       ======================================================== */
    .case-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 16px;
    }
    .case-summary-card {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(168, 85, 247, 0.2);
        border-radius: 12px;
        padding: 10px 12px;
        text-align: center;
    }
    .case-summary-card__label { display: block; font-size: 0.66rem; color: rgba(196, 181, 253, 0.7); letter-spacing: 0.06em; font-weight: 700; margin-bottom: 4px; }
    .case-summary-card__value { display: block; font-size: 1.4rem; font-weight: 800; color: #c4b5fd; font-variant-numeric: tabular-nums; line-height: 1.1; }
    .case-summary-card.is-action { border-color: rgba(168, 85, 247, 0.65); background: rgba(168, 85, 247, 0.10); }
    .case-summary-card.is-action .case-summary-card__value { color: #c4b5fd; }

    .case-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        border: 1px solid var(--color-border);
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 10px;
        position: relative;
        box-shadow: 0 2px 10px rgba(0,0,0,0.18);
    }
    .case-card.is-actionable {
        border-color: var(--color-border-strong);
        background: linear-gradient(180deg, rgba(168, 85, 247, 0.10), rgba(168, 85, 247, 0.03));
        box-shadow: 0 2px 14px rgba(168, 85, 247, 0.16), inset 0 1px 0 rgba(168, 85, 247, 0.08);
    }
    .case-card.is-completed { opacity: 0.78; }

    .case-card__head { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .case-card__icon {
        width: 32px; height: 32px; flex: 0 0 auto;
        border-radius: 8px; background: rgba(168, 85, 247, 0.12); color: var(--gold);
        display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem;
    }
    .case-card.is-completed .case-card__icon { background: var(--color-success-bg); color: var(--color-success); }
    .case-card__main { flex: 1; min-width: 0; }
    .case-card__shop-name { font-size: 0.94rem; font-weight: 800; color: var(--color-text-header); line-height: 1.3; word-break: break-word; margin: 0 0 2px; }
    .case-card__meta { font-size: 0.7rem; color: var(--color-text-muted); display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; }
    .case-card__meta i { color: var(--gold); font-size: 0.62rem; margin-right: 2px; }
    .case-card__meta strong { color: var(--color-text-header); font-weight: 800; }
    .case-card__meta-muted { color: var(--color-text-muted); }

    /* 横長 7 ステップパイプライン */
    .case-pipeline {
        list-style: none; margin: 0 0 10px; padding: 2px 0 4px;
        display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; position: relative;
    }
    .case-pipeline__step { position: relative; text-align: center; padding-top: 22px; font-size: 0.6rem; }
    .case-pipeline__step::after {
        content: ''; position: absolute; top: 8px; left: 50%; right: -50%;
        height: 2px; background: rgba(168, 85, 247, 0.16); z-index: 0;
    }
    .case-pipeline__step:last-child::after { display: none; }
    .case-pipeline__step.is-done::after,
    .case-pipeline__step.is-current::after { background: var(--gold); }

    .case-pipeline__bullet {
        position: absolute; top: 0; left: 50%; transform: translateX(-50%);
        width: 18px; height: 18px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.6rem; font-weight: 800;
        background: rgba(255,255,255,0.04); border: 2px solid rgba(168, 85, 247, 0.28);
        color: var(--color-text-muted); z-index: 1;
    }
    .case-pipeline__step.is-done .case-pipeline__bullet {
        background: var(--accent, #d670a2); color: var(--on-accent, #1a0814); border-color: var(--accent, #d670a2);
    }
    .case-pipeline__step.is-current .case-pipeline__bullet {
        background: rgba(168, 85, 247, 0.22); color: var(--gold-light); border-color: var(--gold);
        animation: case-pulse 1.6s ease-in-out infinite;
        box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.18);
    }
    @keyframes case-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(168, 85, 247, 0.45); }
        50% { box-shadow: 0 0 0 5px rgba(168, 85, 247, 0); }
    }
    .case-pipeline__label { display: block; font-size: 0.58rem; color: var(--color-text-muted); line-height: 1.2; }
    .case-pipeline__step.is-done .case-pipeline__label,
    .case-pipeline__step.is-current .case-pipeline__label { color: var(--color-text-header); font-weight: 700; }

    .case-card__highlights {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px;
        margin: 8px 0 2px;
    }
    .case-card__highlight {
        background: rgba(0,0,0,0.18); border-radius: 8px; padding: 6px 8px;
        font-size: 0.68rem; color: var(--color-text-muted);
    }
    .case-card__highlight strong {
        display: block; margin-top: 1px; font-size: 0.88rem; color: var(--color-text-header);
        font-variant-numeric: tabular-nums; font-weight: 800;
    }
    .case-card__highlight i { color: var(--gold); margin-right: 4px; font-size: 0.66rem; }

    .case-card__action-row {
        display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
        margin-top: 10px; padding-top: 10px;
        border-top: 1px dashed var(--color-border);
    }
    .case-card__waiting {
        font-size: 0.74rem; color: var(--color-text-muted);
        display: inline-flex; align-items: center; gap: 6px;
    }
    .case-card__waiting i { color: var(--gold); }
    .case-card__waiting--done { color: var(--color-success); }
    .case-card__waiting--done i { color: var(--color-success); }
    .case-card__action-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 14px; border-radius: 999px;
        background: var(--accent, #d670a2);
        color: var(--on-accent, #1a0814); border: 0; font-weight: 800; font-size: 0.82rem; cursor: pointer;
        box-shadow: 0 6px 14px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.20), inset 0 -1px 0 rgba(0,0,0,.18);
        margin-left: auto;
    }
    .case-card__action-btn:hover { filter: brightness(1.06); }
    .case-card__action-btn:active { transform: scale(.97); box-shadow: 0 2px 4px rgba(0,0,0,.4), inset 0 2px 4px rgba(0,0,0,.2); }
    .case-card__view-talk {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 0.74rem; color: var(--color-text-muted); text-decoration: none;
    }
    .case-card__view-talk:hover { color: var(--color-text-header); text-decoration: underline; }

    /* セクション見出し：左に小さなアクセント線、右に細い区切り線。
       "ラベル＋ホライズン" の構成で、CTA でも見出しでもない中庸な存在感に。 */
    .mypage-stage-heading {
        margin: 28px 0 12px;
        font-size: 0.72rem; font-weight: 800;
        color: var(--accent-text, #f0a6c4); letter-spacing: 0.14em; text-transform: uppercase;
        display: flex; align-items: center; gap: 10px;
    }
    .mypage-stage-heading i {
        color: var(--accent-text, #f0a6c4);
        font-size: 0.7rem;
        flex-shrink: 0;
        opacity: 0.85;
    }
    /* 右に伸びる細い区切り線（label + horizon の構成） */
    .mypage-stage-heading::after {
        content: '';
        flex: 1 1 auto;
        height: 1px;
        background: linear-gradient(to right, rgba(var(--accent-rgb, 214, 112, 162), 0.35), transparent);
        opacity: 0.6;
    }

    /* チェックボックス */
    .deposit-precheck { display: grid; gap: 14px; }
    .deposit-precheck-card { padding: 18px; border-radius: 18px; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.03); }
    .deposit-precheck-title { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 10px; font-weight: 700; }
    .deposit-precheck-meta, .deposit-precheck-note { font-size: 0.82rem; line-height: 1.7; color: #cdbcbc; }
    .deposit-checklist { display: grid; gap: 10px; margin-top: 12px; }
    .deposit-check-row {
        display: flex; align-items: flex-start; gap: 10px;
        font-size: 0.9rem; color: #f7eded;
        cursor: pointer; padding: 10px 12px; border-radius: 10px;
        background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);
        line-height: 1.5;
    }
    .deposit-check-row:hover { background: rgba(168, 85, 247, 0.06); border-color: rgba(168, 85, 247, 0.22); }
    .deposit-check-row input[type="checkbox"] { flex: 0 0 auto; margin-top: 2px; accent-color: #a78bfa; width: 18px; height: 18px; cursor: pointer; }
    .deposit-check-row span { flex: 1; cursor: pointer; }
    .deposit-check-row:has(input:checked) { background: rgba(168, 85, 247, 0.1); border-color: rgba(168, 85, 247, 0.45); }

    /* フローティング CTA */
    .deposit-cta-bar {
        position: fixed; left: 50%; transform: translateX(-50%);
        bottom: var(--footer-height, 60px); z-index: 90;
        width: min(100vw, var(--max-content-width, 430px)); max-width: 100%;
        padding: 10px var(--content-padding-x, 16px) calc(10px + env(safe-area-inset-bottom, 0));
        background: rgba(10, 10, 10, 0.96);
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 -8px 24px rgba(0,0,0,0.45);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        animation: deposit-cta-slide-up 0.3s ease;
    }
    @keyframes deposit-cta-slide-up {
        from { transform: translate(-50%, 100%); opacity: 0; }
        to { transform: translate(-50%, 0); opacity: 1; }
    }
    .deposit-cta-bar__inner { display: flex; align-items: center; gap: 12px; }
    .deposit-cta-bar__info { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .deposit-cta-bar__amount { display: inline-flex; align-items: baseline; gap: 4px; color: #c4b5fd; font-weight: 800; }
    .deposit-cta-bar__amount strong { font-size: 1.05rem; font-weight: 900; color: #fff; text-shadow: 0 1px 4px rgba(0,0,0,0.4); overflow: hidden; text-overflow: ellipsis; max-width: 50vw; white-space: nowrap; }
    .deposit-cta-bar__amount i { font-size: 0.9rem; color: #a78bfa; }
    .deposit-cta-bar__label { font-size: 0.7rem; color: rgba(196, 181, 253, 0.78); font-weight: 600; }
    .deposit-cta-bar__btn {
        flex: 0 0 auto; margin-left: auto; padding: 12px 18px; border-radius: 999px;
        background: var(--accent, #d670a2);
        color: var(--on-accent, #1a0814); border: 0; font-weight: 900; font-size: 0.92rem; cursor: pointer;
        box-shadow: 0 6px 14px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.20), inset 0 -1px 0 rgba(0,0,0,.18);
        display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
    }
    .deposit-cta-bar__btn:hover { filter: brightness(1.06); }
    .deposit-cta-bar__btn:active { transform: scale(.96); box-shadow: 0 2px 5px rgba(0,0,0,.45), inset 0 2px 4px rgba(0,0,0,.2); }
    body:has(.deposit-cta-bar) .cast-mypage-sub-page { padding-bottom: calc(var(--footer-height, 60px) + 80px) !important; }

    /* 振込先口座アコーディオン */
    .payment-bank-section { padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.08); margin-top: 18px; }
    .payment-bank-accordion {
        border: 1px solid rgba(168, 85, 247, 0.22); border-radius: 14px;
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        overflow: hidden;
    }
    .payment-bank-accordion__summary {
        list-style: none; cursor: pointer; display: flex; align-items: center; gap: 12px;
        padding: 14px 16px; user-select: none;
    }
    .payment-bank-accordion__summary::-webkit-details-marker { display: none; }
    .payment-bank-accordion__summary:hover { background: rgba(168, 85, 247, 0.05); }
    .payment-bank-accordion__icon {
        width: 36px; height: 36px; flex: 0 0 auto;
        border-radius: 50%; background: rgba(168, 85, 247, 0.14); color: #a78bfa;
        display: inline-flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .payment-bank-accordion__main { flex: 1; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .payment-bank-accordion__label { font-size: 0.72rem; font-weight: 700; color: rgba(168, 85, 247, 0.85); letter-spacing: 0.04em; }
    .payment-bank-accordion__summary-text { font-size: 0.92rem; color: #e6dffc; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .payment-bank-accordion__chev { color: rgba(255,255,255,0.55); font-size: 0.85rem; transition: transform 0.2s ease; }
    .payment-bank-accordion[open] .payment-bank-accordion__chev { transform: rotate(180deg); }
    .payment-bank-accordion__body { padding: 12px 16px 16px; border-top: 1px solid rgba(168, 85, 247, 0.16); background: rgba(0,0,0,0.18); }

    .payment-bank-unregistered { background: rgba(26,17,17,0.95); border: 1px dashed rgba(255,255,255,0.12); border-radius: 1rem; padding: 1.6rem 1.2rem; display: flex; flex-direction: column; align-items: center; text-align: center; }
    .payment-bank-unregistered-icon { width: 44px; height: 44px; border-radius: 50%; background: #2a1d1d; display: flex; align-items: center; justify-content: center; margin-bottom: 0.8rem; color: #a0a0a0; font-size: 1.1rem; }
    .payment-bank-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 0.8rem; background: #3a2828; color: #d670a2; }
    .payment-bank-unregistered-text { font-size: 0.84rem; line-height: 1.7; color: #d4d4d4; margin: 0 0 1rem; }
    .payment-bank-register-btn { width: 100%; padding: 12px 24px; border-radius: 12px; font-weight: 700; color: var(--on-accent, #1a0814); background: var(--accent, #d670a2); box-shadow: 0 6px 14px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.20), inset 0 -1px 0 rgba(0,0,0,.18); border: none; cursor: pointer; transition: filter .15s, transform .12s; }
    .payment-bank-register-btn:hover { filter: brightness(1.06); }
    .payment-bank-register-btn:active { transform: scale(.97); box-shadow: 0 2px 5px rgba(0,0,0,.45), inset 0 2px 4px rgba(0,0,0,.2); }
    .payment-bank-data-rows { display: flex; flex-direction: column; gap: 0; }
    .payment-bank-data-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.06); }
    .payment-bank-data-row:last-child { border-bottom: none; }
    .payment-bank-data-label { font-size: 0.74rem; color: #a0a0a0; }
    .payment-bank-data-value { font-size: 0.86rem; font-weight: 500; color: #fff; }
    .payment-bank-change-btn { font-size: 0.78rem; padding: 8px 14px; border: 1px solid rgba(168, 85, 247, 0.4); border-radius: 9999px; background: transparent; color: #a78bfa; cursor: pointer; }

    /* 口座登録モーダル */
    .payment-bank-modal { position: fixed; inset: 0; z-index: 50; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; background: rgba(0, 0, 0, 0.78); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); padding: 0; }
    .payment-bank-modal[hidden] { display: none; }
    @media (min-width: 640px) { .payment-bank-modal { justify-content: center; } }
    .payment-bank-modal-backdrop { position: absolute; inset: 0; cursor: pointer; }
    .payment-bank-modal-panel { position: relative; width: 100%; max-width: min(28rem, calc(100vw - 2rem)); max-height: 90vh; background: #0a0a0a; border-top-left-radius: 1.5rem; border-top-right-radius: 1.5rem; border: 1px solid rgba(255,255,255,0.08); display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); overflow: hidden; box-sizing: border-box; }
    .payment-bank-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); background: #141414; }
    .payment-bank-modal-title { margin: 0; font-size: 1.05rem; font-weight: 700; color: #d670a2; letter-spacing: 0.04em; }
    .payment-bank-modal-close { width: 2.5rem; height: 2.5rem; border: none; background: transparent; color: #a0a0a0; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .payment-bank-modal-body { overflow-y: auto; overflow-x: hidden; padding: 1.5rem; min-width: 0; flex: 1 1 auto; box-sizing: border-box; }
    .payment-bank-modal-note { font-size: 0.74rem; line-height: 1.7; color: #a0a0a0; margin: 0 0 1.5rem; padding: 1rem; background: #1a1a1a; border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.08); }
    .payment-bank-modal-grid { display: flex; flex-direction: column; gap: 1.25rem; min-width: 0; }
    .payment-bank-modal-grid .bank-form-row { margin: 0; min-width: 0; }
    .payment-bank-modal-grid .bank-label { display: block; font-size: 0.74rem; font-weight: 500; color: #a0a0a0; margin-bottom: 6px; margin-left: 4px; }
    .payment-bank-modal-grid .bank-input { width: 100%; min-width: 0; max-width: 100%; padding: 14px 1rem; border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.08); background: #1a1a1a; color: #fff; font-size: 0.86rem; box-sizing: border-box; }
    .payment-bank-modal-footer { display: flex; gap: 0.75rem; padding: 1.25rem 1.5rem; background: #141414; border-top: 1px solid rgba(255,255,255,0.08); }
    .payment-bank-modal-btn { flex: 1; padding: 14px 1rem; border-radius: 0.75rem; font-size: 0.86rem; font-weight: 700; cursor: pointer; }
    .payment-bank-modal-btn-cancel { background: transparent; border: 1px solid rgba(255,255,255,0.08); color: #d4d4d4; }
    .payment-bank-modal-btn-submit { background: var(--accent, #d670a2); color: var(--on-accent, #1a0814); border: none; box-shadow: 0 6px 14px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.20), inset 0 -1px 0 rgba(0,0,0,.18); transition: filter .15s, transform .12s; }
    .payment-bank-modal-btn-submit:hover { filter: brightness(1.06); }
    .payment-bank-modal-btn-submit:active { transform: scale(.97); box-shadow: 0 2px 5px rgba(0,0,0,.45), inset 0 2px 4px rgba(0,0,0,.2); }

    /* 進行中／不採用の応募リスト（mini） */
    .mypage-mini-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
    .mypage-mini-row {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 12px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.06);
        text-decoration: none; color: inherit;
    }
    .mypage-mini-row:hover { border-color: rgba(168, 85, 247, 0.3); background: rgba(168, 85, 247, 0.04); }
    .mypage-mini-row__name { flex: 1; font-size: 0.88rem; font-weight: 700; color: #f0a6c4; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mypage-mini-row__status { flex: 0 0 auto; font-size: 0.7rem; padding: 3px 8px; border-radius: 999px; background: rgba(168, 85, 247, 0.1); color: #a78bfa; }
    .mypage-mini-row__status.is-rejected { background: rgba(220,38,38,0.12); color: #fca5a5; }
    .mypage-mini-row__chev { color: rgba(196, 181, 253, 0.4); font-size: 0.72rem; }

    /* === ボーナスヒーローカード — サービスの肝。ページの視覚 MVP として
           リッチなゴールドで堂々と "稼げる感" を打ち出す。 === */
    .employment-bonus-hero {
        position: relative;
        margin: 4px 0 26px;
        padding: 22px 24px 24px;
        border-radius: 22px;
        background:
            radial-gradient(circle at 0% 0%, rgba(246, 211, 106, 0.22), transparent 55%),
            radial-gradient(circle at 100% 100%, rgba(184, 134, 11, 0.15), transparent 60%),
            linear-gradient(180deg, rgba(246, 211, 106, 0.10), rgba(35, 26, 8, 0.85));
        border: 1px solid rgba(246, 211, 106, 0.45);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow:
            0 10px 28px rgba(0, 0, 0, 0.45),
            0 0 36px rgba(246, 211, 106, 0.18),
            inset 0 1px 0 rgba(255, 255, 255, 0.12),
            inset 0 -1px 0 rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        gap: 6px;
        overflow: hidden;
    }
    /* 右上の subtle shimmer（ゴールドが光ってる感じ） */
    .employment-bonus-hero::after {
        content: '';
        position: absolute;
        top: -20px; right: -20px;
        width: 110px; height: 110px;
        background: radial-gradient(circle, rgba(255, 245, 220, 0.22), transparent 65%);
        pointer-events: none;
        z-index: 0;
    }
    .employment-bonus-hero > * { position: relative; z-index: 1; }
    .employment-bonus-hero__label {
        font-size: 11px;
        letter-spacing: 0.18em;
        color: var(--bonus-gold, #f6d36a);
        font-weight: 800;
        text-transform: uppercase;
        margin: 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-shadow: 0 0 12px rgba(246, 211, 106, 0.4);
    }
    .employment-bonus-hero__label::before {
        content: '◆';
        color: var(--bonus-gold, #f6d36a);
        font-size: 0.85em;
        text-shadow: 0 0 6px rgba(246, 211, 106, 0.7);
    }
    /* 金額はゴールドグラデーションテキスト + 強グロー */
    .employment-bonus-hero__amount {
        font-size: clamp(2.2rem, 8.8vw, 2.8rem);
        font-weight: 900;
        background: linear-gradient(135deg,
            var(--bonus-gold-light, #fff5dc) 0%,
            var(--bonus-gold, #f6d36a) 45%,
            var(--bonus-gold-mid, #e3b94a) 75%,
            var(--bonus-gold-deep, #b8860b) 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        color: transparent;
        font-feature-settings: 'tnum' 1, 'lnum' 1, 'ss01' 1;
        font-variant-numeric: tabular-nums lining-nums;
        letter-spacing: -0.025em;
        line-height: 1;
        filter:
            drop-shadow(0 0 24px rgba(246, 211, 106, 0.35))
            drop-shadow(0 2px 6px rgba(0, 0, 0, 0.35));
    }
</style>
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn">
    <div class="cast-mypage-sub-page">
        <section class="mypage-area">
            <h1 class="mypage-page-title serif-font">採用・入金管理</h1>

            @php
                $hiredCases = $hiredCases ?? [];
                $ongoingApplications = $ongoingApplications ?? [];
                $rejectedApplications = $rejectedApplications ?? [];
                $activeCases = collect($hiredCases)->filter(fn ($c) => empty($c['is_completed']))->values();
                $completedCases = collect($hiredCases)->filter(fn ($c) => !empty($c['is_completed']))->values();
                $actionableCount = $activeCases->filter(fn ($c) => !empty($c['actionable']))->count();
                $bonusTotal = $bonusTotal ?? 0;
            @endphp

            {{-- 獲得ボーナス金合計：ピル → ヒーローカードへ格上げ。
                 ラベル小・金額大の縦積みで "総額が主役" の印象を作る。 --}}
            <div class="employment-bonus-hero">
                <span class="employment-bonus-hero__label">獲得ボーナス金合計</span>
                <span class="employment-bonus-hero__amount">¥{{ number_format($bonusTotal) }}</span>
            </div>

            <div class="mypage-detail-box">
                {{-- サマリー --}}
                <div class="case-summary">
                    <div class="case-summary-card {{ $actionableCount > 0 ? 'is-action' : '' }}">
                        <span class="case-summary-card__label">要対応</span>
                        <span class="case-summary-card__value">{{ $actionableCount }}</span>
                    </div>
                    <div class="case-summary-card">
                        <span class="case-summary-card__label">進行中</span>
                        <span class="case-summary-card__value">{{ max($activeCases->count() - $actionableCount, 0) }}</span>
                    </div>
                    <div class="case-summary-card">
                        <span class="case-summary-card__label">完了</span>
                        <span class="case-summary-card__value">{{ $completedCases->count() }}</span>
                    </div>
                </div>

                @if(session('status'))
                    <p class="management-summary-note">{{ session('status') }}</p>
                @endif
                @if(session('error'))
                    <p class="management-summary-note" style="color:#fca5a5;">{{ session('error') }}</p>
                @endif

                @if($activeCases->isNotEmpty())
                    <h2 class="mypage-stage-heading"><i class="fas fa-fire"></i> 進行中の案件</h2>
                    @foreach($activeCases as $case)
                        @include('casts.mypage._case_card', ['case' => $case])
                    @endforeach
                @endif

                @if($completedCases->isNotEmpty())
                    <h2 class="mypage-stage-heading"><i class="fas fa-check-circle"></i> 完了した案件</h2>
                    @foreach($completedCases as $case)
                        @include('casts.mypage._case_card', ['case' => $case])
                    @endforeach
                @endif

                @if(!empty($ongoingApplications))
                    <h2 class="mypage-stage-heading"><i class="fas fa-comments"></i> 選考中・やり取り中</h2>
                    <ul class="mypage-mini-list">
                        @foreach($ongoingApplications as $app)
                            <a href="{{ $app['link'] ?? '#' }}" class="mypage-mini-row">
                                <i class="fas fa-store" style="color:#a78bfa;"></i>
                                <span class="mypage-mini-row__name">{{ $app['shop_name'] }}</span>
                                <span class="mypage-mini-row__status">{{ $app['status_label'] }}</span>
                                <i class="fas fa-chevron-right mypage-mini-row__chev"></i>
                            </a>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($rejectedApplications))
                    <h2 class="mypage-stage-heading"><i class="fas fa-times-circle"></i> 不採用となった応募</h2>
                    <ul class="mypage-mini-list">
                        @foreach($rejectedApplications as $app)
                            <a href="{{ $app['link'] ?? '#' }}" class="mypage-mini-row">
                                <i class="fas fa-store" style="color:#a0a0a0;"></i>
                                <span class="mypage-mini-row__name">{{ $app['shop_name'] }}</span>
                                <span class="mypage-mini-row__status is-rejected">{{ $app['status_label'] }}</span>
                                <i class="fas fa-chevron-right mypage-mini-row__chev"></i>
                            </a>
                        @endforeach
                    </ul>
                @endif

                @if(empty($hiredCases) && empty($ongoingApplications) && empty($rejectedApplications))
                    <p class="cast-mypage-placeholder">
                        まだ応募履歴がありません。<br>
                        ホームから気になるお店を探して応募してみましょう。
                    </p>
                @endif

                <div class="mypage-section payment-bank-section">
                    @if(empty($castBank['exists']))
                        <h2 class="mypage-stage-heading"><i class="fas fa-university"></i> 振込先口座</h2>
                        <div class="payment-bank-unregistered">
                            <div class="payment-bank-unregistered-icon"><i class="fas fa-university"></i></div>
                            <span class="payment-bank-badge">
                                <i class="fas fa-exclamation-circle"></i> 未登録
                            </span>
                            <p class="payment-bank-unregistered-text">
                                報酬を受け取るための口座が登録されていません。<br>
                                申請を行う前に、口座情報の登録をお願いします。
                            </p>
                            <button type="button" class="payment-bank-register-btn" data-open-bank-modal>
                                口座情報を登録する
                            </button>
                        </div>
                    @else
                        @php
                            $anum = $castBank['account_number'] ?? '';
                            $maskedShort = strlen($anum) > 4 ? '末尾 ' . substr($anum, -4) : $anum;
                            $maskedFull = strlen($anum) > 4 ? str_repeat('*', strlen($anum) - 4) . substr($anum, -4) : $anum;
                        @endphp
                        <details class="payment-bank-accordion">
                            <summary class="payment-bank-accordion__summary">
                                <span class="payment-bank-accordion__icon"><i class="fas fa-university"></i></span>
                                <span class="payment-bank-accordion__main">
                                    <span class="payment-bank-accordion__label">振込先口座（登録済）</span>
                                    <span class="payment-bank-accordion__summary-text">{{ $castBank['bank_name'] ?? '' }} / {{ $maskedShort }}</span>
                                </span>
                                <span class="payment-bank-accordion__chev"><i class="fas fa-chevron-down"></i></span>
                            </summary>
                            <div class="payment-bank-accordion__body">
                                <div class="payment-bank-data-rows">
                                    <div class="payment-bank-data-row"><span class="payment-bank-data-label">金融機関</span><span class="payment-bank-data-value">{{ $castBank['bank_name'] ?? '' }}</span></div>
                                    <div class="payment-bank-data-row"><span class="payment-bank-data-label">支店名</span><span class="payment-bank-data-value">{{ $castBank['branch_name'] ?? '' }}</span></div>
                                    <div class="payment-bank-data-row"><span class="payment-bank-data-label">口座種別</span><span class="payment-bank-data-value">{{ $castBank['account_type_label'] ?? '普通' }}</span></div>
                                    <div class="payment-bank-data-row"><span class="payment-bank-data-label">口座番号</span><span class="payment-bank-data-value">{{ $maskedFull }}</span></div>
                                    <div class="payment-bank-data-row"><span class="payment-bank-data-label">口座名義</span><span class="payment-bank-data-value">{{ $castBank['account_name'] ?? $castBank['account_holder_name'] ?? '' }}</span></div>
                                </div>
                                <div class="text-right" style="margin-top:10px;">
                                    <button type="button" class="payment-bank-change-btn" data-open-bank-modal>
                                        <i class="fas fa-pen"></i> 変更する
                                    </button>
                                </div>
                            </div>
                        </details>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

@php $primaryActionable = $activeCases->filter(fn ($c) => !empty($c['actionable']))->first(); @endphp
@if($primaryActionable && !empty($castBank['exists']))
    <div class="deposit-cta-bar" id="deposit-cta-bar"
         data-application-id="{{ $primaryActionable['application_id'] }}"
         data-action="{{ $primaryActionable['actionable'] }}">
        <div class="deposit-cta-bar__inner">
            <div class="deposit-cta-bar__info">
                <span class="deposit-cta-bar__amount">
                    <i class="fas fa-store"></i>
                    <strong>{{ $primaryActionable['shop_name'] }}</strong>
                </span>
                <span class="deposit-cta-bar__label">{{ $primaryActionable['actionable_label'] }}</span>
            </div>
            <button type="button" class="deposit-cta-bar__btn" id="deposit-cta-bar-submit">
                <i class="fas {{ $primaryActionable['actionable'] === 'request' ? 'fa-paper-plane' : 'fa-check-circle' }}"></i>
                {{ $primaryActionable['actionable_label'] }}
            </button>
        </div>
    </div>
@endif

{{-- レビュー投稿モーダル --}}
<div id="review-post-modal" class="payment-bank-modal" role="dialog" aria-labelledby="review-post-modal-title" aria-modal="true" hidden>
    <div class="payment-bank-modal-backdrop" data-close-review-modal></div>
    <div class="payment-bank-modal-panel review-modal-wrap">
        <header class="review-modal-header">
            <h3 id="review-post-modal-title" class="review-modal-title">レビュー投稿</h3>
            <button type="button" class="review-modal-close-btn" data-close-review-modal aria-label="閉じる"><i class="fas fa-times"></i></button>
        </header>
        <div class="payment-bank-modal-body review-modal-body">
            <p id="review-modal-loading" class="review-modal-loading">読み込み中...</p>
            <div id="review-modal-form-wrap" style="display:none;">
                <p class="review-modal-intro">勤務完了後、お店の雰囲気や働きやすさをレビューしてください。</p>
                <form id="review-post-form">
                    <input type="hidden" name="application_id" id="review-form-application-id" value="">
                    @csrf
                    <div class="review-rating-list" id="review-modal-scores"></div>
                    <div class="review-comment-card">
                        <label class="review-comment-label" for="review-modal-comment">レビューコメント</label>
                        <textarea id="review-modal-comment" name="review_comment" class="review-comment-textarea" rows="4" placeholder="働いてみた感想、雰囲気、条件の印象などを入力してください。" required></textarea>
                    </div>
                    <p id="review-modal-error" class="review-modal-error"></p>
                </form>
                <div class="review-modal-footer">
                    <button type="submit" form="review-post-form" class="review-submit-btn" id="review-submit-btn" disabled>
                        <i class="fas fa-paper-plane"></i> 投稿する
                    </button>
                    <p class="review-footer-hint" id="review-footer-hint">すべての項目を評価すると送信できます</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ボーナス条件達成確認モーダル --}}
<div id="bonus-confirm-modal" class="payment-bank-modal" role="dialog" aria-labelledby="bonus-confirm-modal-title" aria-modal="true" hidden>
    <div class="payment-bank-modal-backdrop" data-close-bonus-modal></div>
    <div class="payment-bank-modal-panel">
        <div class="payment-bank-modal-header">
            <h3 id="bonus-confirm-modal-title" class="payment-bank-modal-title">ボーナス条件達成確認</h3>
            <button type="button" class="payment-bank-modal-close" data-close-bonus-modal aria-label="閉じる"><i class="fas fa-times"></i></button>
        </div>
        <div class="payment-bank-modal-body">
            <p class="deposit-precheck-note">採用された時点のボーナス金・達成条件です。内容を確認のうえ「完了」で入金申請を行ってください。</p>
            <div class="deposit-precheck-card">
                <div class="deposit-precheck-title">
                    <span id="bonus-confirm-shop-name">—</span>
                    <span class="doc-status status-pending">採用済み案件</span>
                </div>
                <div class="deposit-precheck-meta">ボーナス金額: ¥<span id="bonus-confirm-amount">0</span></div>
                <div class="deposit-precheck-note" id="bonus-confirm-condition">
                    <ul class="recruit-line-list" id="bonus-confirm-condition-list"></ul>
                </div>
            </div>
            <form id="bonus-confirm-form">
                <input type="hidden" name="application_id" id="bonus-confirm-application-id" value="">
                @csrf
                <div class="deposit-checklist" id="bonus-confirm-checklist">
                    <label class="deposit-check-row">
                        <input type="checkbox" name="confirm_bonus_days" value="1" required>
                        <span id="bonus-confirm-check-days">勤務日数条件を満たしています</span>
                    </label>
                    <label class="deposit-check-row">
                        <input type="checkbox" name="confirm_bonus_hours" value="1" required>
                        <span id="bonus-confirm-check-hours">勤務時間条件を満たしています</span>
                    </label>
                    <label class="deposit-check-row">
                        <input type="checkbox" name="confirm_bonus_extra" value="1" required>
                        <span id="bonus-confirm-check-extra">その他条件（店舗と合意した条件）を満たしています</span>
                    </label>
                </div>
                <p id="bonus-confirm-error" class="deposit-precheck-note" style="color:#fca5a5; display:none;"></p>
                <div class="text-right mt-3">
                    <button type="submit" class="btn-action manage" id="bonus-confirm-submit-btn" disabled>完了</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 口座情報登録モーダル --}}
<div id="cast-bank-modal" class="payment-bank-modal" role="dialog" aria-labelledby="cast-bank-modal-title" aria-modal="true" hidden>
    <div class="payment-bank-modal-backdrop" data-close-bank-modal></div>
    <div class="payment-bank-modal-panel">
        <div class="payment-bank-modal-header">
            <h3 id="cast-bank-modal-title" class="payment-bank-modal-title">振込先口座の登録</h3>
            <button type="button" class="payment-bank-modal-close" data-close-bank-modal aria-label="閉じる"><i class="fas fa-times"></i></button>
        </div>
        <div class="payment-bank-modal-body">
            <p class="payment-bank-modal-note">
                金融機関と支店は正式名称で入力してください。<br>
                口座名義カナは、銀行側に登録している表記に合わせると照合がスムーズです。
            </p>
            <form id="cast-bank-modal-form" class="management-bank-form" data-bank-autocomplete>
                @csrf
                <div class="payment-bank-modal-grid">
                    @include('partials.bank-account-form-fields', [
                        'variant' => 'management',
                        'bankListId' => 'cast-bank-modal-suggestions',
                        'branchListId' => 'cast-bank-modal-branch-suggestions',
                        'inputIdPrefix' => 'cast-bank-modal',
                        'bankValues' => $castBank ?? [],
                    ])
                </div>
            </form>
        </div>
        <div class="payment-bank-modal-footer">
            <button type="button" class="payment-bank-modal-btn payment-bank-modal-btn-cancel" data-close-bank-modal>キャンセル</button>
            <button type="submit" form="cast-bank-modal-form" class="payment-bank-modal-btn payment-bank-modal-btn-submit" id="cast-bank-modal-submit">登録・保存する</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content')) || '';

    // 口座モーダル
    var bankModal = document.getElementById('cast-bank-modal');
    var bankForm = document.getElementById('cast-bank-modal-form');
    if (bankModal && bankForm) {
        function openBankModal() { bankModal.removeAttribute('hidden'); document.body.style.overflow = 'hidden'; }
        function closeBankModal() { bankModal.setAttribute('hidden', ''); document.body.style.overflow = ''; }
        document.querySelectorAll('[data-open-bank-modal]').forEach(function (b) { b.addEventListener('click', openBankModal); });
        document.querySelectorAll('[data-close-bank-modal]').forEach(function (b) { b.addEventListener('click', closeBankModal); });
        bankModal.addEventListener('click', function (e) { if (e.target === bankModal) closeBankModal(); });
        bankForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(bankForm);
            var btn = document.getElementById('cast-bank-modal-submit');
            if (btn) btn.disabled = true;
            fetch('{{ route("cast.mypage.payment.bank.update") }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
                .then(function (r) { return r.json().then(function (b) { return { ok: r.ok, body: b }; }); })
                .then(function (res) {
                    if (btn) btn.disabled = false;
                    if (res.ok) { closeBankModal(); window.location.reload(); return; }
                    var errs = res.body && res.body.errors ? Object.values(res.body.errors).flat() : [];
                    (window.appToast || window.alert)(errs.length ? errs.join(' ') : (res.body && res.body.message ? res.body.message : '保存に失敗しました。'), 'error');
                }).catch(function () { if (btn) btn.disabled = false; (window.appToast || window.alert)('保存に失敗しました。', 'error'); });
        });
    }

    // レビュー & ボーナス確認 & 入金確認
    var reviewModal = document.getElementById('review-post-modal');
    var bonusModal = document.getElementById('bonus-confirm-modal');
    var requestTargetUrl = '{{ route("cast.mypage.deposit.request-target") }}';
    var reviewPostUrl = '{{ route("cast.mypage.deposit.review") }}';
    var depositRequestUrl = '{{ route("cast.mypage.deposit.request") }}';
    var depositConfirmUrl = '{{ route("cast.mypage.deposit.confirm") }}';

    function openReviewModal(applicationId) {
        if (!reviewModal) return;
        document.getElementById('review-form-application-id').value = applicationId;
        document.getElementById('review-modal-loading').style.display = 'block';
        document.getElementById('review-modal-form-wrap').style.display = 'none';
        var errEl = document.getElementById('review-modal-error');
        if (errEl) { errEl.textContent = ''; errEl.classList.remove('show'); }
        reviewModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        fetch(requestTargetUrl + '?application_id=' + encodeURIComponent(applicationId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json(); })
        .then(function (data) {
            document.getElementById('review-modal-loading').style.display = 'none';
            if (!data.success || !data.request_target) {
                document.getElementById('review-modal-error').textContent = data.message || 'データの取得に失敗しました。';
                return;
            }
            var target = data.request_target;
            if (target.review_exists) { closeReviewModal(); showBonusConfirmModal(applicationId, target); return; }
            buildReviewRatingCards(target.review_contents || []);
            var cmtEl = document.getElementById('review-modal-comment'); if (cmtEl) cmtEl.value = '';
            document.getElementById('review-modal-form-wrap').style.display = 'block';
            checkReviewFormReady();
        }).catch(function () {
            document.getElementById('review-modal-loading').style.display = 'none';
            document.getElementById('review-modal-error').textContent = '読み込みに失敗しました。';
        });
    }
    function buildReviewRatingCards(contents) {
        var wrap = document.getElementById('review-modal-scores');
        wrap.innerHTML = '';
        contents.forEach(function (c) {
            var card = document.createElement('div');
            card.className = 'review-rating-card';
            card.setAttribute('data-content-id', c.id);
            var q = document.createElement('p'); q.className = 'review-rating-question'; q.textContent = c.name || '';
            var row = document.createElement('div'); row.className = 'review-rating-row';
            var stars = document.createElement('div'); stars.className = 'review-rating-stars';
            var hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = 'review_scores[' + c.id + ']'; hidden.value = '0'; hidden.setAttribute('data-rating-input', '1');
            for (var s = 1; s <= 5; s++) {
                var btn = document.createElement('button'); btn.type = 'button'; btn.className = 'review-star-btn'; btn.setAttribute('data-value', s);
                btn.innerHTML = '<i class="far fa-star"></i>';
                (function (sv, b) { b.addEventListener('click', function () { hidden.value = sv; updateStarButtons(card, sv); updateRatingValueSpan(card, sv); checkReviewFormReady(); }); })(s, btn);
                stars.appendChild(btn);
            }
            var v = document.createElement('span'); v.className = 'review-rating-value'; v.textContent = '- / 5';
            row.appendChild(stars); row.appendChild(v);
            card.appendChild(q); card.appendChild(hidden); card.appendChild(row);
            wrap.appendChild(card);
        });
    }
    function updateStarButtons(card, value) {
        card.querySelectorAll('.review-star-btn').forEach(function (b) {
            var v = parseInt(b.getAttribute('data-value'), 10);
            b.classList.toggle('active', v <= value);
            b.querySelector('.fa-star').className = v <= value ? 'fas fa-star' : 'far fa-star';
        });
    }
    function updateRatingValueSpan(card, value) {
        var span = card.querySelector('.review-rating-value');
        if (span) { span.textContent = value > 0 ? value + ' / 5' : '- / 5'; span.classList.toggle('has-value', value > 0); }
    }
    function checkReviewFormReady() {
        var f = document.getElementById('review-post-form'); if (!f) return;
        var allRated = true;
        f.querySelectorAll('input[data-rating-input="1"]').forEach(function (i) { if (!i.value || i.value === '0') allRated = false; });
        var c = (f.querySelector('#review-modal-comment') && f.querySelector('#review-modal-comment').value) || '';
        var ready = allRated && c.trim().length > 0;
        var btn = document.getElementById('review-submit-btn'); if (btn) btn.disabled = !ready;
        var hint = document.getElementById('review-footer-hint'); if (hint) hint.style.display = ready ? 'none' : 'block';
    }
    var cmt = document.getElementById('review-modal-comment'); if (cmt) cmt.addEventListener('input', checkReviewFormReady);
    function closeReviewModal() { if (reviewModal) { reviewModal.setAttribute('hidden', ''); document.body.style.overflow = ''; } }

    function showBonusConfirmModal(applicationId, target) {
        if (!bonusModal) return;
        document.getElementById('bonus-confirm-application-id').value = applicationId;
        document.getElementById('bonus-confirm-shop-name').textContent = target.shop_name || '—';
        document.getElementById('bonus-confirm-amount').textContent = (target.bonus_amount || 0).toLocaleString();
        var bm = target.bonus_meta || {};
        var d = (bm.working_days || '').toString().trim();
        var h = (bm.working_hours || '').toString().trim();
        var x = (bm.extra_condition || '').toString().trim();
        var l = document.getElementById('bonus-confirm-condition-list');
        if (l) {
            l.innerHTML = '';
            if (d) { var li = document.createElement('li'); li.textContent = '勤務日数: ' + d; l.appendChild(li); }
            if (h) { var li = document.createElement('li'); li.textContent = '勤務時間: ' + h; l.appendChild(li); }
            if (x) { var li = document.createElement('li'); li.textContent = 'その他条件: ' + x; l.appendChild(li); }
            if (!d && !h && !x) { var li = document.createElement('li'); li.textContent = '条件は店舗との合意内容に従います。'; l.appendChild(li); }
        }
        var dl = document.getElementById('bonus-confirm-check-days'); if (dl) dl.textContent = d ? ('勤務日数（' + d + '）を完了しました') : '勤務日数条件を満たしています';
        var hl = document.getElementById('bonus-confirm-check-hours'); if (hl) hl.textContent = h ? ('勤務時間（' + h + '）を完了しました') : '勤務時間条件を満たしています';
        var xl = document.getElementById('bonus-confirm-check-extra'); if (xl) xl.textContent = x ? ('その他条件（' + x + '）を満たしています') : 'その他条件（店舗と合意した条件）を満たしています';
        document.querySelectorAll('#bonus-confirm-form input[type="checkbox"]').forEach(function (c) { c.checked = false; });
        document.getElementById('bonus-confirm-error').style.display = 'none';
        bonusModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        updateBonusReady();
    }
    function closeBonusModal() { if (bonusModal) { bonusModal.setAttribute('hidden', ''); document.body.style.overflow = ''; } }
    function updateBonusReady() {
        var f = document.getElementById('bonus-confirm-form'); var btn = document.getElementById('bonus-confirm-submit-btn');
        if (!f || !btn) return;
        var ok = true;
        f.querySelectorAll('input[type="checkbox"][required]').forEach(function (c) { if (!c.checked) ok = false; });
        btn.disabled = !ok;
    }
    document.querySelectorAll('#bonus-confirm-form input[type="checkbox"]').forEach(function (c) { c.addEventListener('change', updateBonusReady); });

    function confirmDepositReceived() {
        if (!confirm('入金を確認しました。よろしいですか？')) return;
        var fd = new FormData(); fd.append('_token', csrfToken);
        fetch(depositConfirmUrl, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
            .then(function (r) { return r.json(); }).then(function () { window.location.reload(); })
            .catch(function () { (window.appToast || window.alert)('処理に失敗しました。', 'error'); });
    }

    document.querySelectorAll('[data-case-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var action = btn.getAttribute('data-case-action');
            var id = btn.getAttribute('data-application-id');
            if (action === 'request' && id) openReviewModal(id);
            else if (action === 'confirm') confirmDepositReceived();
        });
    });
    document.querySelectorAll('[data-close-review-modal]').forEach(function (e) { e.addEventListener('click', closeReviewModal); });
    document.querySelectorAll('[data-close-bonus-modal]').forEach(function (e) { e.addEventListener('click', closeBonusModal); });
    if (reviewModal) reviewModal.addEventListener('click', function (e) { if (e.target === reviewModal) closeReviewModal(); });
    if (bonusModal) bonusModal.addEventListener('click', function (e) { if (e.target === bonusModal) closeBonusModal(); });

    var ctaBar = document.getElementById('deposit-cta-bar');
    var ctaBtn = document.getElementById('deposit-cta-bar-submit');
    if (ctaBar && ctaBtn) {
        ctaBtn.addEventListener('click', function () {
            var action = ctaBar.getAttribute('data-action');
            var id = ctaBar.getAttribute('data-application-id');
            if (action === 'request' && id) openReviewModal(id);
            else if (action === 'confirm') confirmDepositReceived();
        });
    }

    var rf = document.getElementById('review-post-form');
    if (rf) {
        rf.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(rf);
            var btn = document.getElementById('review-submit-btn'); if (btn) btn.disabled = true;
            fetch(reviewPostUrl, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }, body: fd })
                .then(function (r) { return r.json(); }).then(function (res) {
                    if (btn) btn.disabled = false;
                    if (res.success && res.request_target) {
                        closeReviewModal();
                        showBonusConfirmModal(parseInt(rf.querySelector('input[name="application_id"]').value, 10), res.request_target);
                    } else {
                        var re = document.getElementById('review-modal-error');
                        if (re) { re.textContent = res.message || '投稿に失敗しました。'; re.classList.add('show'); }
                    }
                }).catch(function () {
                    if (btn) btn.disabled = false;
                    var re = document.getElementById('review-modal-error');
                    if (re) { re.textContent = '送信に失敗しました。'; re.classList.add('show'); }
                });
        });
    }

    var bf = document.getElementById('bonus-confirm-form');
    if (bf) {
        bf.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(bf);
            var btn = document.getElementById('bonus-confirm-submit-btn'); if (btn) btn.disabled = true;
            var errEl = document.getElementById('bonus-confirm-error');
            var ok = true;
            bf.querySelectorAll('input[type="checkbox"][required]').forEach(function (c) { if (!c.checked) ok = false; });
            if (!ok) { if (btn) btn.disabled = false; errEl.textContent = 'すべての条件にチェックを入れてください。'; errEl.style.display = 'block'; return; }
            fd.set('confirm_bonus_condition', '1');
            fetch(depositRequestUrl, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }, body: fd })
                .then(function (r) { return r.json(); }).then(function (res) {
                    if (btn) btn.disabled = false;
                    if (res.success) { closeBonusModal(); window.location.reload(); }
                    else { errEl.textContent = res.message || '申請に失敗しました。'; errEl.style.display = 'block'; }
                }).catch(function () {
                    if (btn) btn.disabled = false;
                    errEl.textContent = '送信に失敗しました。'; errEl.style.display = 'block';
                });
        });
    }
});
</script>
@endpush
