@extends('layouts.app')

@section('title', 'マイページ - 採用・入金管理')
@section('body-class', 'page-cast-mypage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/review-modal.css') }}">
<style>
    .input-hint {
        margin-top: 6px;
        font-size: 0.72rem;
        line-height: 1.6;
        color: #9f8d8d;
    }
    .deposit-precheck {
        display: grid;
        gap: 14px;
    }
    .deposit-precheck-card {
        padding: 18px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.03);
    }
    .deposit-precheck-title {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
        font-weight: 700;
    }
    .deposit-precheck-meta,
    .deposit-precheck-note {
        font-size: 0.82rem;
        line-height: 1.7;
        color: #cdbcbc;
    }
    .deposit-precheck-note {
        margin-top: 10px;
    }
    .deposit-checklist {
        display: grid;
        gap: 10px;
        margin-top: 12px;
    }
    .deposit-check-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.9rem;
        color: #f7eded;
    }
    .deposit-review-grid {
        display: grid;
        gap: 12px;
        margin-top: 12px;
    }
    .deposit-review-card {
        padding: 14px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.02);
    }
    .deposit-review-label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.92rem;
        font-weight: 700;
        color: #fff8ea;
    }
    .deposit-review-score {
        display: grid;
        gap: 8px;
    }
    .deposit-review-score select,
    .deposit-review-grid textarea {
        width: 100%;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(14, 7, 8, 0.9);
        color: #fff;
        padding: 12px 14px;
    }
    .deposit-review-score select {
        max-width: 120px;
    }
    .bank-registration-card {
        margin-top: 14px;
        padding: 18px;
        border-radius: 22px;
        border: 1px solid rgba(212, 175, 55, 0.14);
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
    }
    .bank-registration-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 8px;
    }
    .bank-registration-title {
        margin: 0;
        font-size: 1rem;
        color: #fff8ea;
        font-weight: 700;
    }
    .bank-registration-copy {
        margin: 8px 0 0;
        font-size: 0.84rem;
        line-height: 1.8;
        color: #cdbcbc;
    }
    .bank-registration-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-top: 16px;
    }
    .bank-registration-note {
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 16px;
        background: rgba(255,255,255,0.04);
        color: #d7c8c8;
        font-size: 0.78rem;
        line-height: 1.7;
    }
    .bank-status-message {
        display: none;
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 14px;
        font-size: 0.83rem;
        line-height: 1.7;
    }
    .bank-status-message.is-success {
        display: block;
        background: rgba(34, 197, 94, 0.14);
        border: 1px solid rgba(34, 197, 94, 0.22);
        color: #dcfce7;
    }
    .bank-status-message.is-error {
        display: block;
        background: rgba(248, 113, 113, 0.12);
        border: 1px solid rgba(248, 113, 113, 0.24);
        color: #fee2e2;
    }
    @media (max-width: 640px) {
        .bank-registration-grid {
            grid-template-columns: 1fr;
        }
    }

    /* --- 口座セクション（未登録／登録済＋モーダル導線） --- */
    .payment-bank-section {
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    .payment-bank-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 1rem;
    }
    .payment-bank-label {
        font-size: 0.68rem;
        letter-spacing: 0.12em;
        color: #a89b9b;
        margin: 0 0 4px;
    }
    .payment-bank-title {
        margin: 0;
        font-size: 1rem;
        color: var(--color-text-header, #fff);
    }
    .payment-bank-change-btn {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--mypage-gold, #cda766);
        padding: 6px 12px;
        border: 1px solid rgba(205, 167, 102, 0.3);
        border-radius: 9999px;
        background: transparent;
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.2s, color 0.2s;
    }
    .payment-bank-change-btn:hover {
        background: rgba(205, 167, 102, 0.1);
    }
    .payment-bank-unregistered {
        background: rgba(26, 17, 17, 0.95);
        border: 1px dashed rgba(255, 255, 255, 0.12);
        border-radius: 1rem;
        padding: 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .payment-bank-unregistered-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #2a1d1d;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        color: #a89b9b;
        font-size: 1.25rem;
    }
    .payment-bank-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        margin-bottom: 1rem;
    }
    .payment-bank-badge-warning {
        background: #3a2828;
        color: var(--mypage-gold, #cda766);
    }
    .payment-bank-unregistered-text {
        font-size: 0.875rem;
        line-height: 1.7;
        color: #d4c8c8;
        margin: 0 0 1.5rem;
    }
    .payment-bank-register-btn {
        width: 100%;
        padding: 14px 24px;
        border-radius: 12px;
        font-weight: 700;
        color: #110a0a;
        background: linear-gradient(to right, #cda766, #b38f4a);
        box-shadow: 0 4px 15px rgba(205, 167, 102, 0.3);
        border: none;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.1s;
    }
    .payment-bank-register-btn:hover {
        opacity: 0.9;
    }
    .payment-bank-register-btn:active {
        transform: scale(0.98);
    }
    .payment-bank-registered {
        background: linear-gradient(135deg, rgba(35, 24, 24, 0.98), rgba(26, 17, 17, 0.98));
        border: 1px solid rgba(138, 110, 61, 0.4);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }
    .payment-bank-data-rows {
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .payment-bank-data-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .payment-bank-data-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .payment-bank-data-label {
        font-size: 0.75rem;
        color: #a89b9b;
    }
    .payment-bank-data-value {
        font-size: 0.875rem;
        font-weight: 500;
        color: #fff;
    }

    /* --- 口座登録モーダル --- */
    .payment-bank-modal {
        position: fixed;
        inset: 0;
        z-index: 50;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(4px);
        padding: 0;
    }
    .payment-bank-modal[hidden] {
        display: none;
    }
    @media (min-width: 640px) {
        .payment-bank-modal {
            justify-content: center;
        }
    }
    .payment-bank-modal-backdrop {
        position: absolute;
        inset: 0;
        cursor: pointer;
    }
    .payment-bank-modal-panel {
        position: relative;
        width: 100%;
        max-width: min(28rem, calc(100vw - 2rem));
        max-height: 90vh;
        background: #160d0d;
        border-top-left-radius: 1.5rem;
        border-top-right-radius: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        overflow: hidden;
        box-sizing: border-box;
    }
    @media (min-width: 640px) {
        .payment-bank-modal-panel {
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    }
    .payment-bank-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: #1a1111;
    }
    .payment-bank-modal-title {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--mypage-gold, #cda766);
        letter-spacing: 0.05em;
    }
    .payment-bank-modal-close {
        width: 2.5rem;
        height: 2.5rem;
        border: none;
        background: transparent;
        color: #a89b9b;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s, background 0.2s;
    }
    .payment-bank-modal-close:hover {
        color: #fff;
        background: #2a1d1d;
    }
    .payment-bank-modal-body {
        overflow-y: auto;
        overflow-x: hidden;
        padding: 1.5rem;
        min-width: 0;
        flex: 1 1 auto;
        box-sizing: border-box;
    }
    .payment-bank-modal-note {
        font-size: 0.75rem;
        line-height: 1.7;
        color: #a89b9b;
        margin: 0 0 1.5rem;
        padding: 1rem;
        background: #231818;
        border-radius: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .payment-bank-modal-body .management-bank-form {
        min-width: 0;
        max-width: 100%;
    }
    .payment-bank-modal-grid {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        min-width: 0;
    }
    .payment-bank-modal-grid .bank-form-row {
        margin: 0;
        min-width: 0;
    }
    .payment-bank-modal-grid .bank-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 500;
        color: #a89b9b;
        margin-bottom: 6px;
        margin-left: 4px;
    }
    .payment-bank-modal-grid .bank-input {
        width: 100%;
        min-width: 0;
        max-width: 100%;
        padding: 14px 1rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: #231818;
        color: #fff;
        font-size: 0.875rem;
        box-sizing: border-box;
    }
    .payment-bank-modal-grid .bank-input:focus {
        border-color: var(--mypage-gold, #cda766);
        outline: none;
        box-shadow: 0 0 0 1px var(--mypage-gold, #cda766);
    }
    .payment-bank-modal-footer {
        display: flex;
        gap: 0.75rem;
        padding: 1.25rem 1.5rem;
        background: #1a1111;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    .payment-bank-modal-btn {
        flex: 1;
        padding: 14px 1rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 700;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.1s;
    }
    .payment-bank-modal-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .payment-bank-modal-btn-cancel {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #d4c8c8;
    }
    .payment-bank-modal-btn-cancel:hover:not(:disabled) {
        background: #2a1d1d;
    }
    .payment-bank-modal-btn-submit {
        background: linear-gradient(to right, #cda766, #b38f4a);
        color: #110a0a;
        border: none;
        box-shadow: 0 4px 15px rgba(205, 167, 102, 0.2);
    }
    .payment-bank-modal-btn-submit:hover:not(:disabled) {
        opacity: 0.9;
    }
    .payment-bank-modal-btn:active:not(:disabled) {
        transform: scale(0.98);
    }

    .mypage-status-card-note {
        margin-top: 8px;
        font-size: 0.78rem;
        line-height: 1.55;
        color: #b8a9a9;
    }

    .mypage-status-card-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .mypage-status-card-action-btn.btn-review-post {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 9999px;
        border: 1px solid rgba(229, 193, 88, 0.35);
        background: rgba(255, 255, 255, 0.06);
        color: var(--mypage-gold, #cda766);
        font-size: 0.8rem;
        cursor: pointer;
        white-space: nowrap;
    }
    .mypage-status-card-action-btn.btn-review-post:hover {
        background: rgba(229, 193, 88, 0.12);
    }

    /* ===========================================================
       ステップバー（キャスト→店舗→運営）
       =========================================================== */
    .deposit-stepper {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 4px;
        list-style: none;
        margin: 6px 0 0;
        padding: 0;
        position: relative;
    }
    .deposit-stepper__step {
        position: relative;
        text-align: center;
        padding-top: 36px;
        font-size: 0.7rem;
    }
    .deposit-stepper__step::after {
        content: '';
        position: absolute;
        top: 14px;
        left: 50%;
        right: -50%;
        height: 2px;
        background: rgba(220, 181, 104, 0.18);
        z-index: 0;
    }
    .deposit-stepper__step:last-child::after { display: none; }
    .deposit-stepper__step.is-done::after { background: #dcb568; }
    .deposit-stepper__bullet {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.78rem;
        background: rgba(255, 255, 255, 0.04);
        border: 2px solid rgba(220, 181, 104, 0.28);
        color: rgba(248, 233, 200, 0.55);
        z-index: 1;
    }
    .deposit-stepper__step.is-done .deposit-stepper__bullet {
        background: linear-gradient(135deg, #dcb568, #b8860b);
        color: #2a1406;
        border-color: #dcb568;
        box-shadow: 0 0 0 4px rgba(220, 181, 104, 0.15);
    }
    .deposit-stepper__step.is-active .deposit-stepper__bullet {
        background: rgba(220, 181, 104, 0.25);
        color: #ffe2a3;
        border-color: #dcb568;
        animation: deposit-step-pulse 1.6s ease-in-out infinite;
    }
    .deposit-stepper__step.is-next .deposit-stepper__bullet {
        background: rgba(220, 181, 104, 0.1);
        color: #ffe2a3;
        border-color: rgba(220, 181, 104, 0.65);
    }
    @keyframes deposit-step-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220, 181, 104, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(220, 181, 104, 0); }
    }
    .deposit-stepper__label {
        display: block;
        font-weight: 800;
        color: #f8e9c8;
        font-size: 0.74rem;
        line-height: 1.3;
    }
    .deposit-stepper__step.is-done .deposit-stepper__label,
    .deposit-stepper__step.is-active .deposit-stepper__label,
    .deposit-stepper__step.is-next .deposit-stepper__label { color: #ffe2a3; }
    .deposit-stepper__desc {
        display: block;
        margin-top: 2px;
        font-size: 0.65rem;
        color: rgba(248, 233, 200, 0.55);
        line-height: 1.3;
    }
    @media (max-width: 380px) {
        .deposit-stepper { gap: 2px; }
        .deposit-stepper__label { font-size: 0.66rem; }
        .deposit-stepper__desc { display: none; }
    }

    /* ===========================================================
       フローティング CTA バー（画面下部固定）
       =========================================================== */
    .deposit-cta-bar {
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        bottom: var(--footer-height, 60px);
        z-index: 90;
        width: min(100vw, var(--max-content-width, 430px));
        max-width: 100%;
        padding: 10px var(--content-padding-x, 16px) calc(10px + env(safe-area-inset-bottom, 0));
        background: linear-gradient(180deg, rgba(74, 18, 42, 0.98), rgba(35, 8, 21, 1));
        border-top: 1px solid rgba(220, 181, 104, 0.55);
        box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.45);
        animation: deposit-cta-slide-up 0.3s ease;
    }
    @keyframes deposit-cta-slide-up {
        from { transform: translate(-50%, 100%); opacity: 0; }
        to { transform: translate(-50%, 0); opacity: 1; }
    }
    .deposit-cta-bar__inner {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .deposit-cta-bar__info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .deposit-cta-bar__amount {
        display: inline-flex;
        align-items: baseline;
        gap: 4px;
        color: #ffe2a3;
        font-weight: 800;
    }
    .deposit-cta-bar__amount strong {
        font-size: 1.3rem;
        font-weight: 900;
        color: #fff;
        font-variant-numeric: tabular-nums;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
    }
    .deposit-cta-bar__amount i {
        font-size: 0.9rem;
        color: #dcb568;
    }
    .deposit-cta-bar__label {
        font-size: 0.7rem;
        color: rgba(248, 233, 200, 0.78);
        font-weight: 600;
    }
    .deposit-cta-bar__btn {
        flex: 0 0 auto;
        margin-left: auto;
        padding: 12px 18px;
        border-radius: 999px;
        background: linear-gradient(135deg, #ffe2a3, #dcb568 48%, #b8860b);
        color: #2a1406;
        border: 0;
        font-weight: 900;
        font-size: 0.92rem;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(220, 181, 104, 0.45);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        transition: transform 0.1s ease, box-shadow 0.15s ease;
    }
    .deposit-cta-bar__btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(220, 181, 104, 0.55);
    }
    .deposit-cta-bar__btn:active { transform: translateY(0); }

    /* CTA バーの分だけ本文末尾に余白を追加（ボタンが情報を覆わないように） */
    .deposit-cta-bar ~ .deposit-cta-bar-spacer,
    body:has(.deposit-cta-bar) .cast-mypage-sub-page {
        padding-bottom: calc(var(--footer-height, 60px) + 80px) !important;
    }

    /* ===========================================================
       タップ領域・店舗カードの整理
       =========================================================== */
    .deposit-check-row {
        cursor: pointer;
        padding: 10px 12px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.06);
        transition: background 0.12s ease, border-color 0.12s ease;
        line-height: 1.5;
    }
    .deposit-check-row:hover { background: rgba(220, 181, 104, 0.06); border-color: rgba(220, 181, 104, 0.22); }
    .deposit-check-row input[type="checkbox"] {
        flex: 0 0 auto;
        margin-top: 2px;
        accent-color: #dcb568;
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .deposit-check-row span { flex: 1; cursor: pointer; }
    .deposit-check-row:has(input:checked) {
        background: rgba(220, 181, 104, 0.1);
        border-color: rgba(220, 181, 104, 0.45);
    }

    /* 店舗カードの呼吸感 */
    .mypage-status-card { padding: 14px; gap: 14px; }
    .mypage-status-card-head { gap: 10px; flex-wrap: wrap; }
    .mypage-status-card-name {
        font-size: 0.96rem;
        font-weight: 800;
        line-height: 1.4;
        word-break: break-word;
    }
    .mypage-status-card-date {
        display: block;
        margin-top: 6px;
        font-size: 0.74rem;
        color: rgba(201, 184, 184, 0.78);
    }
    .mypage-status-card-meta {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 8px;
        font-size: 0.86rem;
        color: #f5e0c4;
    }
    .mypage-status-card-meta strong {
        color: #ffe2a3;
        font-size: 1rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }
    .mypage-status-card-meta i { color: #dcb568; }
    .mypage-status-card-meta--soft {
        color: rgba(201, 184, 184, 0.7);
        font-size: 0.78rem;
        font-weight: 600;
    }

    /* ===========================================================
       振込先口座アコーディオン
       =========================================================== */
    .payment-bank-accordion {
        border: 1px solid rgba(220, 181, 104, 0.22);
        border-radius: 14px;
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        overflow: hidden;
    }
    .payment-bank-accordion__summary {
        list-style: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        user-select: none;
    }
    .payment-bank-accordion__summary::-webkit-details-marker { display: none; }
    .payment-bank-accordion__summary:hover { background: rgba(220, 181, 104, 0.05); }
    .payment-bank-accordion__icon {
        width: 36px;
        height: 36px;
        flex: 0 0 auto;
        border-radius: 50%;
        background: rgba(220, 181, 104, 0.14);
        color: #dcb568;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .payment-bank-accordion__main {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .payment-bank-accordion__label {
        font-size: 0.72rem;
        font-weight: 700;
        color: rgba(220, 181, 104, 0.85);
        letter-spacing: 0.04em;
    }
    .payment-bank-accordion__summary-text {
        font-size: 0.92rem;
        color: #f8e9c8;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .payment-bank-accordion__chev {
        color: rgba(255, 255, 255, 0.55);
        font-size: 0.85rem;
        transition: transform 0.2s ease;
    }
    .payment-bank-accordion[open] .payment-bank-accordion__chev { transform: rotate(180deg); }
    .payment-bank-accordion__body {
        padding: 12px 16px 16px;
        border-top: 1px solid rgba(220, 181, 104, 0.16);
        background: rgba(0, 0, 0, 0.18);
    }
</style>
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn">
    <div class="cast-mypage-sub-page">
        <section class="mypage-area">
            @php
                $flow = $depositFlow ?? ['cast' => '未申請','shop' => '未稼働','admin' => '未稼働'];
                $currentStatusLabel = $currentDeposit['status_label'] ?? (($canRequestDeposit ?? false) ? '入金申請が可能です' : '未申請');
            @endphp
            <a href="{{ route('cast.mypage.index') }}" class="cast-mypage-back-link">
                <i class="fas fa-chevron-left"></i> マイページへ戻る
            </a>
            <h1 class="mypage-page-title serif-font">採用・入金管理</h1>
            <div class="mypage-detail-box">
                <div class="mypage-section">
                    @php
                        $employmentCollection = collect($employments ?? []);
                        $hiredCount = $employmentCollection->filter(function ($row) {
                            return in_array((int) ($row['status_code'] ?? 0), [4, 6], true);
                        })->count();
                        $pendingCount = $employmentCollection->whereIn('status_label', ['やり取り中', '面談日調整中', '面談日決定'])->count();
                        $rejectedCount = $employmentCollection->filter(function ($row) {
                            return in_array((int) ($row['status_code'] ?? 0), [5, 7], true);
                        })->count();
                    @endphp
                    <div class="mypage-status-overview">
                        <div class="mypage-status-metric">
                            <span class="mypage-status-metric-label">採用</span>
                            <strong class="mypage-status-metric-value">{{ $hiredCount }}</strong>
                        </div>
                        <div class="mypage-status-metric">
                            <span class="mypage-status-metric-label">選考中</span>
                            <strong class="mypage-status-metric-value">{{ $pendingCount }}</strong>
                        </div>
                        <div class="mypage-status-metric">
                            <span class="mypage-status-metric-label">不採用</span>
                            <strong class="mypage-status-metric-value">{{ $rejectedCount }}</strong>
                        </div>
                    </div>
                    @if(empty($employments))
                        <p class="cast-mypage-placeholder">
                            応募した店舗の採用状況を確認できます。<br>
                            まだ応募履歴がありません。
                        </p>
                    @else
                        <h2 class="mypage-actions-title">応募した店舗</h2>
                        <ul class="mypage-status-card-list">
                            @foreach($employments as $item)
                                <li class="mypage-status-card">
                                    <div class="mypage-status-card-icon">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div class="mypage-status-card-body">
                                        <div class="mypage-status-card-head">
                                            <span class="mypage-status-card-name">{{ $item['shop_name'] }}</span>
                                            <span class="doc-status {{ $item['status_class'] ?? '' }}">
                                                {{ $item['status_label'] }}
                                            </span>
                                        </div>
                                        @if(!empty($item['applied_at']))
                                            <span class="mypage-status-card-date numeric-font">更新日: {{ $item['applied_at'] }}</span>
                                        @endif
                                        @if(!empty($item['bonus_at_apply_lines']))
                                            <div class="mypage-status-card-note">
                                                @foreach($item['bonus_at_apply_lines'] as $line)
                                                    <div class="numeric-font">{{ $line }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @php $isHiredEmployment = in_array((int) ($item['status_code'] ?? 0), [4, 6], true); @endphp
                                        @if($isHiredEmployment && !empty($item['hired_hourly_wage_display']))
                                            <span class="mypage-status-card-meta numeric-font">
                                                <i class="fas fa-yen-sign" aria-hidden="true"></i>
                                                採用時給：<strong>{{ $item['hired_hourly_wage_display'] }} 円</strong>
                                            </span>
                                        @elseif($isHiredEmployment)
                                            <span class="mypage-status-card-meta mypage-status-card-meta--soft">
                                                <i class="fas fa-clock" aria-hidden="true"></i>
                                                採用時給：店舗側で設定中です。しばらくお待ちください。
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mypage-status-card-actions">
                                        @if(in_array((int) ($item['status_code'] ?? 0), [4, 6], true))
                                            <button type="button" class="mypage-status-card-action-btn btn-review-post" data-application-id="{{ $item['application_id'] }}" title="レビュー投稿">
                                                <i class="fas fa-star"></i>
                                                <span>レビュー投稿</span>
                                            </button>
                                        @endif
                                        <a href="{{ $item['link'] ?? '#' }}" class="mypage-status-card-link">
                                            <span class="mypage-status-card-link-text">トークを見る</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="mypage-section">
                    <div class="mypage-payment-hero">
                        <span class="mypage-payment-hero-label">現在の状況</span>
                        <strong class="mypage-payment-hero-value">{{ $currentStatusLabel }}</strong>
                        <p class="mypage-payment-hero-note">
                            {{ $requestDisabledReason ?? '採用後の入金申請から、運営確認・振込完了までをここで管理できます。' }}
                        </p>
                    </div>

                    @if(session('status'))
                        <p class="management-summary-note">{{ session('status') }}</p>
                    @endif
                    @if(session('error'))
                        <p class="management-summary-note" style="color:#fca5a5;">{{ session('error') }}</p>
                    @endif
                    @if(empty($payments))
                        <p class="cast-mypage-placeholder">
                            請求履歴や入金状況を確認できます。<br>
                            まだ請求・入金の履歴がありません。
                        </p>
                    @else
                        <h2 class="mypage-actions-title">請求・入金履歴</h2>
                        <ul class="mypage-status-card-list">
                            @foreach($payments as $row)
                                <li class="mypage-status-card">
                                    <div class="mypage-status-card-icon">
                                        <i class="fas fa-money-check-alt"></i>
                                    </div>
                                    <div class="mypage-status-card-body">
                                        <div class="mypage-status-card-head">
                                            <span class="mypage-status-card-name">{{ $row['title'] }}</span>
                                            <span class="doc-status {{ $row['status_class'] ?? '' }}">
                                                {{ $row['status_label'] }}
                                            </span>
                                        </div>
                                        @if(!empty($row['date']))
                                            <span class="mypage-status-card-date numeric-font">{{ $row['date'] }}</span>
                                        @endif
                                        @if(!empty($row['amount']))
                                            <span class="mypage-status-card-meta numeric-font">振込予定額: ¥{{ number_format($row['amount']) }}</span>
                                        @endif
                                    </div>
                                    @if(!empty($row['link']))
                                        <a href="{{ $row['link'] }}" class="mypage-status-card-link">
                                            <span class="mypage-status-card-link-text">詳細</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if(!empty($requestTarget))
                    <div class="mypage-section">
                        <h2 class="mypage-actions-title">ボーナス金申請前の確認</h2>
                        <div class="deposit-precheck">
                            <div class="deposit-precheck-card">
                                <div class="deposit-precheck-title">
                                    <span>{{ $requestTarget['shop_name'] ?? '対象案件' }}</span>
                                    <span class="doc-status status-pending">採用済み案件</span>
                                </div>
                                <div class="deposit-precheck-meta">
                                    ボーナス金額: ¥{{ number_format((int) ($requestTarget['bonus_amount'] ?? 0)) }}
                                    @if(!empty($requestTarget['hired_hourly_wage']))
                                        <br>採用時給: {{ $requestTarget['hired_hourly_wage'] }}円
                                    @endif
                                </div>
                                <div class="deposit-precheck-note">
                                    {!! nl2br(e($requestTarget['bonus_condition'] ?: '求人情報に登録された条件を満たしているか確認してください。')) !!}
                                </div>
                            </div>

                            @if($canRequestDeposit ?? false)
                                @if(!empty($requestTarget['review_exists']))
                                    {{-- レビュー投稿済み：採用時点のボーナス表示＋入金依頼のみ --}}
                                    <div class="deposit-precheck-card">
                                        <div class="deposit-precheck-title">
                                            <span>確認・入金依頼</span>
                                            <span class="doc-status status-paid">レビュー投稿済み</span>
                                        </div>
                                        @if(!empty($requestTarget['review_posted_at']))
                                            <div class="deposit-precheck-note">投稿日時: {{ $requestTarget['review_posted_at'] }}</div>
                                        @endif
                                        <form method="POST" action="{{ route('cast.mypage.deposit.request') }}">
                                            @csrf
                                            <input type="hidden" name="application_id" value="{{ $requestTarget['application_id'] ?? '' }}">
                                            <input type="hidden" name="confirm_bonus_condition" value="1">
                                            <div class="deposit-checklist">
                                                @php
                                                    $bonusMeta = is_array($requestTarget['bonus_meta'] ?? null) ? ($requestTarget['bonus_meta'] ?? []) : [];
                                                    $bmDays = trim((string) ($bonusMeta['working_days'] ?? ''));
                                                    $bmHours = trim((string) ($bonusMeta['working_hours'] ?? ''));
                                                    $bmExtra = trim((string) ($bonusMeta['extra_condition'] ?? ($requestTarget['bonus_condition'] ?? '')));
                                                @endphp
                                                <label class="deposit-check-row">
                                                    <input type="checkbox" name="confirm_bonus_days" value="1" required>
                                                    <span>{{ $bmDays !== '' ? ('勤務日数（' . $bmDays . '）を完了しました') : '勤務日数条件を満たしています' }}</span>
                                                </label>
                                                <label class="deposit-check-row">
                                                    <input type="checkbox" name="confirm_bonus_hours" value="1" required>
                                                    <span>{{ $bmHours !== '' ? ('勤務時間（' . $bmHours . '）を完了しました') : '勤務時間条件を満たしています' }}</span>
                                                </label>
                                                <label class="deposit-check-row">
                                                    <input type="checkbox" name="confirm_bonus_extra" value="1" required>
                                                    <span>{{ $bmExtra !== '' ? ('その他条件（' . $bmExtra . '）を満たしています') : 'その他条件（店舗と合意した条件）を満たしています' }}</span>
                                                </label>
                                            </div>
                                            <div class="text-right mt-3">
                                                <button type="submit" class="btn-action manage">入金依頼を送信する</button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    {{-- レビュー未投稿：モーダルで投稿する導線 --}}
                                    <div class="deposit-precheck-card">
                                        <div class="deposit-precheck-title">
                                            <span>レビュー投稿・入金依頼</span>
                                            <span class="doc-status status-pending">レビュー未投稿</span>
                                        </div>
                                        <p class="deposit-precheck-note">
                                            勤務完了後、お店の雰囲気や働きやすさをレビューしてください。レビュー投稿後、ボーナス条件達成確認のうえ入金申請ができます。
                                        </p>
                                        <div class="text-right mt-3">
                                            <button type="button" class="btn-action manage btn-review-post" data-application-id="{{ $requestTarget['application_id'] ?? '' }}">
                                                <i class="fas fa-star"></i> レビュー投稿する
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @elseif(!empty($requestDisabledReason))
                                <div class="deposit-precheck-card">
                                    <p class="deposit-precheck-note">{{ $requestDisabledReason }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mypage-section">
                    <h2 class="mypage-actions-title">振込までの進捗</h2>
                    @php
                        // ステータスコード→ステップ進捗の解決
                        // 1: キャスト申請中（cast送信済み）→ step 1 完了
                        // 2: 店舗承認済み              → step 2 完了
                        // 3: 店舗入金報告              → step 3 半分
                        // 4: 店舗入金確認              → step 3 完了
                        // 5: 振込開始                  → step 4 半分
                        // 6: 振込実行                  → step 4 完了（受領待ち）
                        // 7: 完了                      → step 5 完了
                        $statusCode = (int) ($currentDeposit['status_code'] ?? 0);
                        $depositSteps = [
                            ['key' => 'cast',   'label' => 'キャスト申請', 'desc' => $flow['cast'] ?? '未申請'],
                            ['key' => 'shop',   'label' => '店舗承認',     'desc' => $flow['shop'] ?? '未稼働'],
                            ['key' => 'shop_pay','label' => '店舗入金',    'desc' => '入金確認待ち'],
                            ['key' => 'admin',  'label' => '運営振込',     'desc' => $flow['admin'] ?? '未稼働'],
                            ['key' => 'done',   'label' => '受領完了',     'desc' => '振込到着確認'],
                        ];
                        $stepStateOf = function (int $idx) use ($statusCode): string {
                            if ($statusCode <= 0) return $idx === 0 ? 'next' : 'pending';
                            if ($statusCode === 1) return $idx < 1 ? 'done' : ($idx === 1 ? 'next' : 'pending');
                            if ($statusCode === 2) return $idx < 2 ? 'done' : ($idx === 2 ? 'next' : 'pending');
                            if ($statusCode === 3) return $idx < 2 ? 'done' : ($idx === 2 ? 'active' : ($idx === 3 ? 'next' : 'pending'));
                            if ($statusCode === 4) return $idx < 3 ? 'done' : ($idx === 3 ? 'next' : 'pending');
                            if ($statusCode === 5) return $idx < 3 ? 'done' : ($idx === 3 ? 'active' : ($idx === 4 ? 'next' : 'pending'));
                            if ($statusCode === 6) return $idx < 4 ? 'done' : ($idx === 4 ? 'next' : 'pending');
                            if ($statusCode >= 7) return 'done';
                            return 'pending';
                        };
                    @endphp
                    <ol class="deposit-stepper" aria-label="入金進捗">
                        @foreach($depositSteps as $idx => $step)
                            @php $state = $stepStateOf($idx); @endphp
                            <li class="deposit-stepper__step is-{{ $state }}">
                                <span class="deposit-stepper__bullet" aria-hidden="true">
                                    @if($state === 'done')
                                        <i class="fas fa-check"></i>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </span>
                                <span class="deposit-stepper__label">{{ $step['label'] }}</span>
                                <span class="deposit-stepper__desc">{{ $step['desc'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                    <div class="text-right" style="margin-top: 12px;">
                        @if(($currentDeposit['status_code'] ?? null) === 6)
                            <form method="POST" action="{{ route('cast.mypage.deposit.confirm') }}">
                                @csrf
                                <button type="submit" class="btn-action manage">
                                    <i class="fas fa-check-circle"></i> 入金を確認しました
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="mypage-section payment-bank-section">
                    @if(empty($castBank['exists']))
                        {{-- 未登録: 視覚的に強調した CTA を残す --}}
                        <div class="payment-bank-header">
                            <div>
                                <p class="payment-bank-label">キャストの振込先口座</p>
                                <h2 class="mypage-actions-title payment-bank-title">報酬の振込先口座情報</h2>
                            </div>
                        </div>
                        <div class="payment-bank-unregistered">
                            <div class="payment-bank-unregistered-icon">
                                <i class="fas fa-university" aria-hidden="true"></i>
                            </div>
                            <span class="payment-bank-badge payment-bank-badge-warning">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i> 未登録
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
                        {{-- 登録済: アコーディオンで折りたたみ。最後の4桁だけサマリ表示 --}}
                        @php
                            $anum = $castBank['account_number'] ?? '';
                            $maskedShort = strlen($anum) > 4 ? '末尾 ' . substr($anum, -4) : $anum;
                            $maskedFull = strlen($anum) > 4 ? str_repeat('*', strlen($anum) - 4) . substr($anum, -4) : $anum;
                        @endphp
                        <details class="payment-bank-accordion">
                            <summary class="payment-bank-accordion__summary">
                                <span class="payment-bank-accordion__icon">
                                    <i class="fas fa-university" aria-hidden="true"></i>
                                </span>
                                <span class="payment-bank-accordion__main">
                                    <span class="payment-bank-accordion__label">振込先口座（登録済）</span>
                                    <span class="payment-bank-accordion__summary-text">
                                        {{ $castBank['bank_name'] ?? '' }} / {{ $maskedShort }}
                                    </span>
                                </span>
                                <span class="payment-bank-accordion__chev" aria-hidden="true">
                                    <i class="fas fa-chevron-down"></i>
                                </span>
                            </summary>
                            <div class="payment-bank-accordion__body">
                                <div class="payment-bank-data-rows">
                                    <div class="payment-bank-data-row">
                                        <span class="payment-bank-data-label">金融機関</span>
                                        <span class="payment-bank-data-value">{{ $castBank['bank_name'] ?? '' }}</span>
                                    </div>
                                    <div class="payment-bank-data-row">
                                        <span class="payment-bank-data-label">支店名</span>
                                        <span class="payment-bank-data-value">{{ $castBank['branch_name'] ?? '' }}</span>
                                    </div>
                                    <div class="payment-bank-data-row">
                                        <span class="payment-bank-data-label">口座種別</span>
                                        <span class="payment-bank-data-value">{{ $castBank['account_type_label'] ?? '普通' }}</span>
                                    </div>
                                    <div class="payment-bank-data-row">
                                        <span class="payment-bank-data-label">口座番号</span>
                                        <span class="payment-bank-data-value">{{ $maskedFull }}</span>
                                    </div>
                                    <div class="payment-bank-data-row">
                                        <span class="payment-bank-data-label">口座名義</span>
                                        <span class="payment-bank-data-value">{{ $castBank['account_name'] ?? $castBank['account_holder_name'] ?? '' }}</span>
                                    </div>
                                </div>
                                <div class="text-right" style="margin-top: 10px;">
                                    <button type="button" class="payment-bank-change-btn" data-open-bank-modal aria-label="口座情報を変更">
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

{{-- レビュー投稿モーダル（スターレーティング＋コメント） --}}
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

{{-- ボーナス条件達成確認モーダル（採用時点の焼き付け表示＋完了） --}}
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
                    <ul class="recruit-line-list" id="bonus-confirm-condition-list">
                        {{-- JS で bonus_meta から埋め込み --}}
                    </ul>
                </div>
            </div>
            <form id="bonus-confirm-form">
                <input type="hidden" name="application_id" id="bonus-confirm-application-id" value="">
                @csrf
                {{-- confirm_bonus_condition は全チェック完了時のみ JS で付与する --}}
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

{{-- 口座情報登録モーダル（未登録時はここに遷移、登録済は「変更する」で表示） --}}
<div id="cast-bank-modal" class="payment-bank-modal" role="dialog" aria-labelledby="cast-bank-modal-title" aria-modal="true" hidden>
    <div class="payment-bank-modal-backdrop" data-close-bank-modal></div>
    <div class="payment-bank-modal-panel">
        <div class="payment-bank-modal-header">
            <h3 id="cast-bank-modal-title" class="payment-bank-modal-title">振込先口座の登録</h3>
            <button type="button" class="payment-bank-modal-close" data-close-bank-modal aria-label="閉じる">
                <i class="fas fa-times"></i>
            </button>
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
            <button type="button" class="payment-bank-modal-btn payment-bank-modal-btn-cancel" data-close-bank-modal>
                キャンセル
            </button>
            <button type="submit" form="cast-bank-modal-form" class="payment-bank-modal-btn payment-bank-modal-btn-submit" id="cast-bank-modal-submit">
                登録・保存する
            </button>
        </div>
    </div>
</div>

{{-- フローティング CTA：申請可能な状態のときだけ画面下部に固定 --}}
@if(!empty($requestTarget) && ($canRequestDeposit ?? false))
    @php
        $hasReview = !empty($requestTarget['review_exists']);
        $bonusAmount = (int) ($requestTarget['bonus_amount'] ?? 0);
    @endphp
    <div class="deposit-cta-bar" id="deposit-cta-bar" data-application-id="{{ $requestTarget['application_id'] ?? '' }}">
        <div class="deposit-cta-bar__inner">
            <div class="deposit-cta-bar__info">
                <span class="deposit-cta-bar__amount">
                    <i class="fas fa-yen-sign"></i>
                    <strong>¥{{ number_format($bonusAmount) }}</strong>
                </span>
                <span class="deposit-cta-bar__label">{{ $hasReview ? '入金依頼を送信できます' : 'レビュー投稿後に入金依頼' }}</span>
            </div>
            @if($hasReview)
                <button type="button" class="deposit-cta-bar__btn" id="deposit-cta-bar-submit">
                    <i class="fas fa-paper-plane"></i> 入金依頼を送信
                </button>
            @else
                <button type="button" class="deposit-cta-bar__btn btn-review-post" data-application-id="{{ $requestTarget['application_id'] ?? '' }}">
                    <i class="fas fa-star"></i> レビューを投稿する
                </button>
            @endif
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('cast-bank-modal');
    var form = document.getElementById('cast-bank-modal-form');
    if (!modal || !form) return;

    function openModal() {
        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-open-bank-modal]').forEach(function (btn) {
        btn.addEventListener('click', openModal);
    });
    document.querySelectorAll('[data-close-bank-modal]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(form);
        var submitBtn = document.getElementById('cast-bank-modal-submit');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
        fetch('{{ route("cast.mypage.payment.bank.update") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(function (r) {
            return r.json().then(function (body) {
                return { ok: r.ok, body: body };
            });
        })
        .then(function (res) {
            if (submitBtn) submitBtn.disabled = false;
            if (res.ok) {
                closeModal();
                window.location.reload();
                return;
            }
            var errors = res.body && res.body.errors ? Object.values(res.body.errors).flat() : [];
            var msg = errors.length ? errors.join(' ') : (res.body && res.body.message ? res.body.message : '保存に失敗しました。');
            alert(msg);
        })
        .catch(function () {
            if (submitBtn) submitBtn.disabled = false;
            alert('保存に失敗しました。時間をおいて再度お試しください。');
        });
    });

    // --- レビュー投稿・ボーナス条件達成確認モーダル ---
    var reviewModal = document.getElementById('review-post-modal');
    var bonusModal = document.getElementById('bonus-confirm-modal');
    var requestTargetUrl = '{{ route("cast.mypage.deposit.request-target") }}';
    var reviewPostUrl = '{{ route("cast.mypage.deposit.review") }}';
    var depositRequestUrl = '{{ route("cast.mypage.deposit.request") }}';
    var csrfToken = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            document.getElementById('review-modal-loading').style.display = 'none';
            if (!data.success || !data.request_target) {
                document.getElementById('review-modal-error').textContent = data.message || 'データの取得に失敗しました。';
                document.getElementById('review-modal-error').style.display = 'block';
                return;
            }
            var target = data.request_target;
            if (target.review_exists) {
                closeReviewModal();
                showBonusConfirmModal(applicationId, target);
                return;
            }
            buildReviewRatingCards(target.review_contents || []);
            var cmtEl = document.getElementById('review-modal-comment');
            if (cmtEl) cmtEl.value = '';
            document.getElementById('review-modal-form-wrap').style.display = 'block';
            checkReviewFormReady();
        })
        .catch(function () {
            document.getElementById('review-modal-loading').style.display = 'none';
            document.getElementById('review-modal-error').textContent = '読み込みに失敗しました。';
            document.getElementById('review-modal-error').style.display = 'block';
        });
    }

    function buildReviewRatingCards(contents) {
        var scoresWrap = document.getElementById('review-modal-scores');
        scoresWrap.innerHTML = '';
        contents.forEach(function (c) {
            var card = document.createElement('div');
            card.className = 'review-rating-card';
            card.setAttribute('data-content-id', c.id);
            var question = document.createElement('p');
            question.className = 'review-rating-question';
            question.textContent = c.name || '';
            var row = document.createElement('div');
            row.className = 'review-rating-row';
            var stars = document.createElement('div');
            stars.className = 'review-rating-stars';
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'review_scores[' + c.id + ']';
            hidden.value = '0';
            hidden.setAttribute('data-rating-input', '1');
            for (var s = 1; s <= 5; s++) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'review-star-btn';
                btn.setAttribute('data-value', s);
                btn.innerHTML = '<i class="far fa-star"></i>';
                (function (starVal, button) {
                    button.addEventListener('click', function () {
                        hidden.value = starVal;
                        updateStarButtons(card, starVal);
                        updateRatingValueSpan(card, starVal);
                        checkReviewFormReady();
                    });
                    button.addEventListener('mouseenter', function () { setStarHover(card, starVal); });
                    button.addEventListener('mouseleave', function () { clearStarHover(card); });
                })(s, btn);
                stars.appendChild(btn);
            }
            var valueSpan = document.createElement('span');
            valueSpan.className = 'review-rating-value';
            valueSpan.textContent = '- / 5';
            row.appendChild(stars);
            row.appendChild(valueSpan);
            card.appendChild(question);
            card.appendChild(hidden);
            card.appendChild(row);
            scoresWrap.appendChild(card);
        });
    }

    function updateStarButtons(card, value) {
        var btns = card.querySelectorAll('.review-star-btn');
        btns.forEach(function (btn) {
            var v = parseInt(btn.getAttribute('data-value'), 10);
            btn.classList.toggle('active', v <= value);
            btn.querySelector('.fa-star').className = v <= value ? 'fas fa-star' : 'far fa-star';
        });
    }

    function setStarHover(card, value) {
        card.querySelectorAll('.review-rating-stars .review-star-btn').forEach(function (btn) {
            var v = parseInt(btn.getAttribute('data-value'), 10);
            btn.classList.toggle('hover', v <= value);
            if (btn.querySelector('.fa-star')) btn.querySelector('.fa-star').className = v <= value ? 'fas fa-star' : 'far fa-star';
        });
    }

    function clearStarHover(card) {
        card.querySelectorAll('.review-star-btn').forEach(function (btn) {
            btn.classList.remove('hover');
        });
        var input = card.querySelector('input[name^="review_scores"]');
        if (input && input.value !== '0') {
            updateStarButtons(card, parseInt(input.value, 10));
        }
    }

    function updateRatingValueSpan(card, value) {
        var span = card.querySelector('.review-rating-value');
        if (span) {
            span.textContent = value > 0 ? value + ' / 5' : '- / 5';
            span.classList.toggle('has-value', value > 0);
        }
    }

    function checkReviewFormReady() {
        var form = document.getElementById('review-post-form');
        if (!form) return;
        var allRated = true;
        form.querySelectorAll('input[data-rating-input="1"]').forEach(function (inp) {
            if (!inp.value || inp.value === '0') allRated = false;
        });
        var comment = (form.querySelector('#review-modal-comment') && form.querySelector('#review-modal-comment').value) || '';
        var ready = allRated && comment.trim().length > 0;
        var btn = document.getElementById('review-submit-btn');
        var hint = document.getElementById('review-footer-hint');
        if (btn) btn.disabled = !ready;
        if (hint) hint.style.display = ready ? 'none' : 'block';
    }

    document.getElementById('review-modal-comment') && document.getElementById('review-modal-comment').addEventListener('input', checkReviewFormReady);
    document.getElementById('review-modal-comment') && document.getElementById('review-modal-comment').addEventListener('change', checkReviewFormReady);

    function closeReviewModal() {
        if (reviewModal) {
            reviewModal.setAttribute('hidden', '');
            document.body.style.overflow = '';
        }
    }

    function showBonusConfirmModal(applicationId, target) {
        if (!bonusModal) return;
        document.getElementById('bonus-confirm-application-id').value = applicationId;
        document.getElementById('bonus-confirm-shop-name').textContent = target.shop_name || '—';
        document.getElementById('bonus-confirm-amount').textContent = (target.bonus_amount || 0).toLocaleString();
        var bm = target.bonus_meta || {};
        var days = (bm.working_days || '').toString().trim();
        var hours = (bm.working_hours || '').toString().trim();
        var extra = (bm.extra_condition || '').toString().trim();
        var listEl = document.getElementById('bonus-confirm-condition-list');
        if (listEl) {
            listEl.innerHTML = '';
            if (days) {
                var liDays = document.createElement('li');
                liDays.textContent = '勤務日数: ' + days;
                listEl.appendChild(liDays);
            }
            if (hours) {
                var liHours = document.createElement('li');
                liHours.textContent = '勤務時間: ' + hours;
                listEl.appendChild(liHours);
            }
            if (extra) {
                var liExtra = document.createElement('li');
                liExtra.textContent = 'その他条件: ' + extra;
                listEl.appendChild(liExtra);
            }
            if (!days && !hours && !extra) {
                var liDefault = document.createElement('li');
                liDefault.textContent = '条件は店舗との合意内容に従います。';
                listEl.appendChild(liDefault);
            }
        }
        var daysLabel = document.getElementById('bonus-confirm-check-days');
        var hoursLabel = document.getElementById('bonus-confirm-check-hours');
        var extraLabel = document.getElementById('bonus-confirm-check-extra');
        if (daysLabel) daysLabel.textContent = days ? ('勤務日数（' + days + '）を完了しました') : '勤務日数条件を満たしています';
        if (hoursLabel) hoursLabel.textContent = hours ? ('勤務時間（' + hours + '）を完了しました') : '勤務時間条件を満たしています';
        if (extraLabel) extraLabel.textContent = extra ? ('その他条件（' + extra + '）を満たしています') : 'その他条件（店舗と合意した条件）を満たしています';

        document.querySelectorAll('#bonus-confirm-form input[type="checkbox"]').forEach(function (cb) { cb.checked = false; });
        document.getElementById('bonus-confirm-error').style.display = 'none';
        bonusModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        updateBonusConfirmReadyState();
    }

    function closeBonusModal() {
        if (bonusModal) {
            bonusModal.setAttribute('hidden', '');
            document.body.style.overflow = '';
        }
    }

    function updateBonusConfirmReadyState() {
        var form = document.getElementById('bonus-confirm-form');
        var btn = document.getElementById('bonus-confirm-submit-btn');
        if (!form || !btn) return;
        var requiredChecks = form.querySelectorAll('input[type="checkbox"][required]');
        var allChecked = true;
        requiredChecks.forEach(function (cb) {
            if (!cb.checked) allChecked = false;
        });
        btn.disabled = !allChecked;
    }

    document.querySelectorAll('#bonus-confirm-form input[type="checkbox"]').forEach(function (cb) {
        cb.addEventListener('change', updateBonusConfirmReadyState);
    });

    document.querySelectorAll('.btn-review-post').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-application-id');
            if (id) openReviewModal(id);
        });
    });

    // フローティング CTA：入金依頼ボタン → ボーナス確認モーダルを開く
    var ctaSubmitBtn = document.getElementById('deposit-cta-bar-submit');
    if (ctaSubmitBtn) {
        ctaSubmitBtn.addEventListener('click', function () {
            var bar = document.getElementById('deposit-cta-bar');
            var applicationId = bar ? bar.getAttribute('data-application-id') : '';
            if (!applicationId) return;
            // request-target を取得して bonus confirm モーダルへ
            fetch(requestTargetUrl + '?application_id=' + encodeURIComponent(applicationId), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.request_target) {
                    showBonusConfirmModal(parseInt(applicationId, 10), data.request_target);
                } else {
                    alert((data && data.message) || 'データの取得に失敗しました。');
                }
            }).catch(function () {
                alert('読み込みに失敗しました。');
            });
        });
    }
    document.querySelectorAll('[data-close-review-modal]').forEach(function (el) {
        el.addEventListener('click', closeReviewModal);
    });
    document.querySelectorAll('[data-close-bonus-modal]').forEach(function (el) {
        el.addEventListener('click', closeBonusModal);
    });
    if (reviewModal) reviewModal.addEventListener('click', function (e) { if (e.target === reviewModal) closeReviewModal(); });
    if (bonusModal) bonusModal.addEventListener('click', function (e) { if (e.target === bonusModal) closeBonusModal(); });

    document.getElementById('review-post-form') && document.getElementById('review-post-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var form = this;
        var fd = new FormData(form);
        var btn = document.getElementById('review-submit-btn');
        if (btn) btn.disabled = true;
        fetch(reviewPostUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content')) || '' },
            body: fd
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (btn) btn.disabled = false;
            if (res.success && res.request_target) {
                closeReviewModal();
                showBonusConfirmModal(parseInt(form.querySelector('input[name="application_id"]').value, 10), res.request_target);
            } else {
                var re = document.getElementById('review-modal-error');
                if (re) { re.textContent = res.message || '投稿に失敗しました。'; re.classList.add('show'); }
            }
        })
        .catch(function () {
            if (btn) btn.disabled = false;
            var re = document.getElementById('review-modal-error');
            if (re) { re.textContent = '送信に失敗しました。'; re.classList.add('show'); }
        });
    });

    document.getElementById('bonus-confirm-form') && document.getElementById('bonus-confirm-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var form = this;
        var fd = new FormData(form);
        var btn = document.getElementById('bonus-confirm-submit-btn');
        if (btn) btn.disabled = true;
        var errEl = document.getElementById('bonus-confirm-error');

        // 全チェックが完了していない場合は confirm_bonus_condition を送らない
        var requiredChecks = form.querySelectorAll('input[type="checkbox"][required]');
        var allChecked = true;
        requiredChecks.forEach(function (cb) { if (!cb.checked) allChecked = false; });
        if (!allChecked) {
            if (btn) btn.disabled = false;
            errEl.textContent = 'すべての条件にチェックを入れてください。';
            errEl.style.display = 'block';
            return;
        }
        fd.set('confirm_bonus_condition', '1');

        fetch(depositRequestUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content')) || '' },
            body: fd
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (btn) btn.disabled = false;
            if (res.success) {
                closeBonusModal();
                window.location.reload();
            } else {
                errEl.textContent = res.message || '申請に失敗しました。';
                errEl.style.display = 'block';
            }
        })
        .catch(function () {
            if (btn) btn.disabled = false;
            errEl.textContent = '送信に失敗しました。';
            errEl.style.display = 'block';
        });
    });
});
</script>
@endpush
