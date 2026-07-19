@extends('layouts.app-v2')

@section('title', '採用・入金管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/management.css') }}?v=20260508">
<link rel="stylesheet" href="{{ asset('assets/css/case-flow.css') }}?v=20260719-case-light">
<style>
    /* ========================================================
       採用・入金 統合タイムライン（店舗側）
       ======================================================== */
    .shop-management-shell { padding: 0 16px 16px; }

    /* 固定ヘッダー分のアンカー余白 */
    .mypage-stage-heading, .case-card { scroll-margin-top: calc(var(--header-height, 60px) + 12px); }

    .case-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 16px;
    }
    .case-summary-card {
        background: #ffffff;
        border: 1px solid rgba(124, 58, 237, 0.20);
        border-radius: 12px;
        padding: 10px 12px;
        text-align: center;
        cursor: default;
        font: inherit;
        width: 100%;
    }
    button.case-summary-card[data-scroll-target] { cursor: pointer; transition: transform .12s, box-shadow .15s; }
    button.case-summary-card[data-scroll-target]:active { transform: scale(.96); }
    .case-summary-card__label { display: block; font-size: 0.66rem; color: #6d6685; letter-spacing: 0.06em; font-weight: 700; margin-bottom: 4px; }
    .case-summary-card__value { display: block; font-size: 1.4rem; font-weight: 800; color: #6d28d9; font-variant-numeric: tabular-nums; line-height: 1.1; }
    /* 要対応 > 0 は暖色で最優先の注意喚起 */
    .case-summary-card.is-action { border-color: rgba(217, 119, 6, 0.55); background: rgba(217, 119, 6, 0.07); }
    .case-summary-card.is-action .case-summary-card__label { color: #b45309; }
    .case-summary-card.is-action .case-summary-card__value { color: #b45309; }
    .case-summary-card__hint {
        display: block; font-size: 0.58rem; color: rgba(109, 102, 133, 0.75);
        margin-top: 3px; font-weight: 600;
    }
    .case-summary-card.is-action .case-summary-card__hint { color: rgba(180, 83, 9, 0.8); }

    .case-card {
        background: #ffffff;
        border: 1px solid var(--color-border);
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 10px;
        position: relative;
        box-shadow: 0 2px 10px rgba(76, 29, 149, 0.08);
    }
    .case-card.is-actionable {
        border-color: var(--color-border-strong);
        background: linear-gradient(180deg, rgba(168, 85, 247, 0.06), #ffffff 40%);
        box-shadow: 0 2px 14px rgba(124, 58, 237, 0.18);
    }
    .case-card.is-completed { opacity: 0.82; }

    .case-card__head { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .case-card__icon {
        width: 32px; height: 32px; flex: 0 0 auto;
        border-radius: 8px; background: rgba(168, 85, 247, 0.12); color: var(--gold);
        display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem;
    }
    .case-card__avatar {
        width: 32px; height: 32px; flex: 0 0 auto;
        border-radius: 8px; object-fit: cover;
        border: 1px solid var(--color-border);
    }
    .case-card.is-completed .case-card__icon { background: var(--color-success-bg); color: var(--color-success); }
    .case-card__main { flex: 1; min-width: 0; }
    .case-card__shop-name { font-size: 0.94rem; font-weight: 800; color: var(--color-text-header); line-height: 1.3; word-break: break-word; margin: 0 0 2px; }
    .case-card__meta { font-size: 0.7rem; color: var(--color-text-muted); display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; }
    .case-card__meta i { color: var(--gold); font-size: 0.62rem; margin-right: 2px; }
    .case-card__meta strong { color: var(--color-text-header); font-weight: 800; }

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
        background: #ffffff; border: 2px solid rgba(124, 58, 237, 0.28);
        color: var(--color-text-muted); z-index: 1;
    }
    .case-pipeline__step.is-done .case-pipeline__bullet {
        background: linear-gradient(135deg, var(--gold), var(--gold-deep)); color: #ffffff; border-color: var(--gold);
    }
    .case-pipeline__step.is-current .case-pipeline__bullet {
        background: #ffffff; color: var(--gold-deep, #6d28d9); border-color: var(--gold);
        animation: case-pulse 1.6s ease-in-out infinite;
        box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.18);
    }
    @keyframes case-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(168, 85, 247, 0.45); }
        50% { box-shadow: 0 0 0 5px rgba(168, 85, 247, 0); }
    }
    .case-pipeline__label { display: block; font-size: 0.6rem; color: var(--color-text-muted); line-height: 1.2; }
    .case-pipeline__step.is-done .case-pipeline__label,
    .case-pipeline__step.is-current .case-pipeline__label { color: var(--color-text-header); font-weight: 700; }

    .case-card__highlights {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px;
        margin: 8px 0 2px;
    }
    .case-card__highlight {
        background: rgba(124, 58, 237, 0.06); border-radius: 8px; padding: 6px 8px;
        font-size: 0.68rem; color: var(--color-text-muted);
    }
    .case-card__highlight strong {
        display: block; margin-top: 1px; font-size: 0.88rem; color: var(--color-text-header);
        font-variant-numeric: tabular-nums; font-weight: 800;
    }
    .case-card__highlight i { color: var(--gold); margin-right: 4px; font-size: 0.66rem; }

    .case-card__invoice-link-row {
        margin: 8px 0 0; padding-top: 8px;
        border-top: 1px dashed var(--color-border);
    }
    .case-card__invoice-link {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 0.74rem; padding: 6px 12px; border-radius: 999px;
        background: rgba(168, 85, 247, 0.08); border: 1px solid var(--color-border-strong);
        color: var(--gold-light); text-decoration: none;
    }
    .case-card__invoice-link:hover { background: rgba(168, 85, 247, 0.16); color: var(--color-text-header); }

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
    /* 主要アクション：ひと回り大きく・グラデ＋アクセントグローで最優先の操作として目立たせる */
    .case-card__action-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 12px 20px; border-radius: 999px; min-height: 46px;
        background: linear-gradient(135deg, var(--accent-grad-from, #a78bfa), var(--accent-grad-to, #7c3aed));
        color: var(--on-accent-strong, #ffffff); border: 0; font-weight: 800; font-size: 0.92rem; cursor: pointer;
        box-shadow: 0 8px 20px rgba(var(--accent-rgb, 139, 92, 246), 0.38), inset 0 1px 0 rgba(255,255,255,.28), inset 0 -1px 0 rgba(0,0,0,.15);
        margin-left: auto;
        transition: filter .15s, transform .12s, box-shadow .15s;
    }
    .case-card__action-btn:hover { filter: brightness(1.07); box-shadow: 0 10px 26px rgba(var(--accent-rgb, 139, 92, 246), 0.50), inset 0 1px 0 rgba(255,255,255,.28); }
    .case-card__action-btn:active { transform: scale(.97); box-shadow: 0 3px 8px rgba(var(--accent-rgb, 139, 92, 246), .35), inset 0 2px 4px rgba(0,0,0,.2); }
    .case-card__view-talk {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 0.74rem; color: var(--color-text-muted); text-decoration: none;
    }
    .case-card__view-talk:hover { color: var(--color-text-header); text-decoration: underline; }
    .case-card__waiting--done { color: var(--color-success); }
    .case-card__waiting--done i { color: var(--color-success); }

    /* セクション見出し：ラベル＋ホライズンの構成（cast 側と同じ） */
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
    .mypage-stage-heading::after {
        content: '';
        flex: 1 1 auto;
        height: 1px;
        background: linear-gradient(to right, rgba(var(--accent-rgb, 214, 112, 162), 0.35), transparent);
        opacity: 0.6;
    }

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
    .mypage-mini-row__avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex: 0 0 auto; }
    .mypage-mini-row__avatar-fallback {
        width: 32px; height: 32px; border-radius: 50%; background: rgba(168, 85, 247, 0.14); color: #a78bfa;
        display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto;
    }
    .mypage-mini-row__name { flex: 1; font-size: 0.88rem; font-weight: 700; color: #f0a6c4; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mypage-mini-row__sub { font-size: 0.7rem; color: rgba(201,184,184,0.6); }
    .mypage-mini-row__status { flex: 0 0 auto; font-size: 0.7rem; padding: 3px 8px; border-radius: 999px; background: rgba(168, 85, 247, 0.1); color: #a78bfa; }
    .mypage-mini-row__status.is-rejected { background: rgba(220,38,38,0.12); color: #fca5a5; }
    .mypage-mini-row__status.is-overdue { background: rgba(220,38,38,0.12); color: #fca5a5; }
    .mypage-mini-row__chev { color: rgba(196, 181, 253, 0.4); font-size: 0.72rem; }

    /* 空状態 */
    .shop-management-empty {
        padding: 40px 12px; text-align: center;
        font-size: 0.86rem; color: rgba(201,184,184,0.6);
        border: 1px dashed rgba(255,255,255,0.08);
        border-radius: 14px; background: rgba(255,255,255,0.02);
    }

    /* セッション通知 */
    .management-summary-note {
        margin: 12px 0; padding: 10px 14px;
        border-radius: 10px;
        background: rgba(124, 58, 237, 0.08);
        border: 1px solid rgba(124, 58, 237, 0.30);
        color: #6d28d9; font-size: 0.82rem; line-height: 1.5;
    }

    /* フローティング CTA（ライト画面用の白バー） */
    .deposit-cta-bar {
        position: fixed; left: 50%; transform: translateX(-50%);
        bottom: var(--footer-height, 60px); z-index: 90;
        width: min(100vw, var(--max-content-width, 430px)); max-width: 100%;
        padding: 10px var(--content-padding-x, 16px) calc(10px + env(safe-area-inset-bottom, 0));
        background: rgba(255, 255, 255, 0.97);
        border-top: 1px solid var(--color-line, #e6e0f3);
        box-shadow: 0 -8px 24px rgba(76, 29, 149, 0.15);
        animation: deposit-cta-slide-up 0.3s ease;
    }
    @keyframes deposit-cta-slide-up {
        from { transform: translate(-50%, 100%); opacity: 0; }
        to { transform: translate(-50%, 0); opacity: 1; }
    }
    .deposit-cta-bar__inner { display: flex; align-items: center; gap: 12px; }
    .deposit-cta-bar__info { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
    .deposit-cta-bar__amount { display: inline-flex; align-items: baseline; gap: 4px; color: #6d28d9; font-weight: 800; }
    .deposit-cta-bar__amount strong { font-size: 1.05rem; font-weight: 900; color: #241f33; overflow: hidden; text-overflow: ellipsis; max-width: 50vw; white-space: nowrap; }
    .deposit-cta-bar__amount i { font-size: 0.9rem; color: #7c3aed; }
    .deposit-cta-bar__label { font-size: 0.7rem; color: #5f5876; font-weight: 600; }
    .deposit-cta-bar__btn {
        flex: 0 0 auto; margin-left: auto; padding: 12px 18px; border-radius: 999px;
        background: linear-gradient(135deg, #c4b5fd, #a78bfa 48%, #7c3aed);
        color: #1a0814; border: 0; font-weight: 900; font-size: 0.92rem; cursor: pointer;
        box-shadow: 0 6px 16px rgba(168, 85, 247, 0.45);
        display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
    }
    .deposit-cta-bar__btn:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(168, 85, 247, 0.55); }
    body:has(.deposit-cta-bar) .shop-management-shell { padding-bottom: calc(var(--footer-height, 60px) + 80px) !important; }

    /* モーダル（承認・入金処理共通）：ライト画面に追従して白パネル */
    .shop-action-modal { position: fixed; inset: 0; z-index: 50; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; background: rgba(20, 10, 35, 0.55); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); padding: 0; }
    .shop-action-modal[hidden] { display: none; }
    @media (min-width: 640px) { .shop-action-modal { justify-content: center; } }
    .shop-action-modal-backdrop { position: absolute; inset: 0; cursor: pointer; }
    .shop-action-modal-panel { position: relative; width: 100%; max-width: min(28rem, calc(100vw - 2rem)); max-height: 90vh; background: #ffffff; border-top-left-radius: 1.5rem; border-top-right-radius: 1.5rem; border: 1px solid rgba(124, 58, 237, 0.30); display: flex; flex-direction: column; box-shadow: 0 25px 60px -12px rgba(76, 29, 149, 0.35); overflow: hidden; box-sizing: border-box; }
    .shop-action-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid rgba(124, 58, 237, 0.20); background: #f7f4fc; }
    .shop-action-modal-title { margin: 0; font-size: 1.05rem; font-weight: 700; color: #241f33; letter-spacing: 0.04em; }
    .shop-action-modal-close { width: 2.5rem; height: 2.5rem; border: none; background: transparent; color: #6d6685; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .shop-action-modal-body { overflow-y: auto; overflow-x: hidden; padding: 1.5rem; min-width: 0; flex: 1 1 auto; box-sizing: border-box; }
    .shop-action-modal-note { font-size: 0.78rem; line-height: 1.7; color: #5f5876; margin: 0 0 1.2rem; padding: 0.9rem; background: #f7f4fc; border-radius: 0.75rem; border: 1px solid rgba(124, 58, 237, 0.16); }
    .shop-action-modal-checklist { display: grid; gap: 10px; margin-bottom: 1rem; }
    .shop-action-modal-check {
        display: flex; align-items: flex-start; gap: 10px;
        font-size: 0.86rem; color: #241f33;
        cursor: pointer; padding: 10px 12px; border-radius: 10px;
        background: #ffffff; border: 1px solid rgba(124, 58, 237, 0.16);
        line-height: 1.5;
    }
    .shop-action-modal-check:hover { background: rgba(124, 58, 237, 0.05); border-color: rgba(124, 58, 237, 0.30); }
    .shop-action-modal-check input[type="checkbox"] { flex: 0 0 auto; margin-top: 2px; accent-color: #7c3aed; width: 18px; height: 18px; cursor: pointer; }
    .shop-action-modal-check span { flex: 1; cursor: pointer; }
    .shop-action-modal-check:has(input:checked) { background: rgba(124, 58, 237, 0.08); border-color: rgba(124, 58, 237, 0.50); }
    .shop-action-modal-field { margin-bottom: 1rem; }
    .shop-action-modal-label { display: block; font-size: 0.74rem; font-weight: 600; color: #5f5876; margin-bottom: 6px; }
    .shop-action-modal-input { width: 100%; padding: 12px 14px; border-radius: 0.75rem; border: 1px solid rgba(124, 58, 237, 0.30); background: #ffffff; color: #241f33; font-size: 0.92rem; box-sizing: border-box; }
    .shop-action-modal-input:focus { outline: none; border-color: rgba(124, 58, 237, 0.60); }
    .shop-action-modal-error { color: #dc2626; font-size: 0.82rem; line-height: 1.5; display: none; margin: 0 0 0.6rem; }
    .shop-action-modal-error.show { display: block; }
    .shop-action-modal-footer { display: flex; gap: 0.75rem; padding: 1.1rem 1.5rem; background: #f7f4fc; border-top: 1px solid rgba(124, 58, 237, 0.20); }
    .shop-action-modal-btn { flex: 1; padding: 13px 1rem; border-radius: 0.75rem; font-size: 0.9rem; font-weight: 700; cursor: pointer; border: 0; }
    .shop-action-modal-btn-cancel { background: transparent; border: 1px solid rgba(124, 58, 237, 0.25); color: #5f5876; }
    .shop-action-modal-btn-submit { background: linear-gradient(135deg, #c4b5fd, #a78bfa 48%, #7c3aed); color: #1a0814; box-shadow: 0 4px 14px rgba(168, 85, 247, 0.35); }
    .shop-action-modal-btn-submit:disabled { opacity: 0.45; cursor: not-allowed; box-shadow: none; }
</style>
@endpush

@section('content')
<div class="shop-management-shell animate-fadeIn">
    {{-- タイトルはヘッダー中央、説明はオコジョガイド（character_guide_settings）に集約 --}}

    @php
        $hiredCases = $hiredCases ?? [];
        $ongoingApplications = $ongoingApplications ?? [];
        $rejectedApplications = $rejectedApplications ?? [];
        $activeCases = collect($hiredCases)->filter(fn ($c) => empty($c['is_completed']))->values();
        $completedCases = collect($hiredCases)->filter(fn ($c) => !empty($c['is_completed']))->values();
        $actionableCount = $activeCases->filter(fn ($c) => !empty($c['actionable']))->count();
        $primaryActionable = $activeCases->filter(fn ($c) => !empty($c['actionable']))->first();
    @endphp

    {{-- サマリー（タップで該当セクションへ移動） --}}
    <div class="case-summary">
        <button type="button" class="case-summary-card {{ $actionableCount > 0 ? 'is-action' : '' }}"
                @if($actionableCount > 0) data-scroll-target=".case-card.is-actionable" @endif>
            <span class="case-summary-card__label">要対応</span>
            <span class="case-summary-card__value">{{ $actionableCount }}</span>
            @if($actionableCount > 0)
                <span class="case-summary-card__hint">タップで移動</span>
            @endif
        </button>
        <button type="button" class="case-summary-card"
                @if($activeCases->isNotEmpty()) data-scroll-target="#section-active-cases" @endif>
            <span class="case-summary-card__label">進行中</span>
            <span class="case-summary-card__value">{{ max($activeCases->count() - $actionableCount, 0) }}</span>
        </button>
        <button type="button" class="case-summary-card"
                @if($completedCases->isNotEmpty()) data-scroll-target="#section-completed-cases" @endif>
            <span class="case-summary-card__label">完了</span>
            <span class="case-summary-card__value">{{ $completedCases->count() }}</span>
        </button>
    </div>

    @if(session('status'))
        <p class="management-summary-note">{{ session('status') }}</p>
    @endif
    @if(session('error'))
        <p class="management-summary-note" style="color:#fca5a5; background: rgba(220,38,38,0.12); border-color: rgba(220,38,38,0.4);">{{ session('error') }}</p>
    @endif

    @if($activeCases->isNotEmpty())
        <h2 class="mypage-stage-heading" id="section-active-cases"><i class="fas fa-fire"></i> 進行中の案件</h2>
        @foreach($activeCases as $case)
            @include('shops.mypage._case_card', ['case' => $case])
        @endforeach
    @endif

    @if($completedCases->isNotEmpty())
        <h2 class="mypage-stage-heading" id="section-completed-cases"><i class="fas fa-check-circle"></i> 完了した案件</h2>
        @foreach($completedCases as $case)
            @include('shops.mypage._case_card', ['case' => $case])
        @endforeach
    @endif

    @if(!empty($ongoingApplications))
        <h2 class="mypage-stage-heading"><i class="fas fa-comments"></i> 選考中・やり取り中</h2>
        <ul class="mypage-mini-list">
            @foreach($ongoingApplications as $app)
                <a href="{{ !empty($app['cast_id']) ? route('shop.talk.room', $app['cast_id']) : '#' }}" class="mypage-mini-row">
                    @if(!empty($app['cast_avatar_url']))
                        <img src="{{ $app['cast_avatar_url'] }}" alt="" class="mypage-mini-row__avatar">
                    @else
                        <span class="mypage-mini-row__avatar-fallback"><i class="fas fa-user"></i></span>
                    @endif
                    <span class="mypage-mini-row__name">
                        {{ $app['cast_name'] }}
                        @if(!empty($app['job_kind_label']))
                            <span class="mypage-mini-row__sub">／{{ $app['job_kind_label'] }}</span>
                        @endif
                    </span>
                    <span class="mypage-mini-row__status {{ !empty($app['is_decision_overdue']) ? 'is-overdue' : '' }}">
                        {{ $app['status_display_label'] ?? $app['status_label'] ?? '' }}
                    </span>
                    <i class="fas fa-chevron-right mypage-mini-row__chev"></i>
                </a>
            @endforeach
        </ul>
    @endif

    @if(!empty($rejectedApplications))
        <h2 class="mypage-stage-heading"><i class="fas fa-times-circle"></i> 不採用となった応募</h2>
        <ul class="mypage-mini-list">
            @foreach($rejectedApplications as $app)
                <a href="{{ !empty($app['cast_id']) ? route('shop.talk.room', $app['cast_id']) : '#' }}" class="mypage-mini-row">
                    @if(!empty($app['cast_avatar_url']))
                        <img src="{{ $app['cast_avatar_url'] }}" alt="" class="mypage-mini-row__avatar">
                    @else
                        <span class="mypage-mini-row__avatar-fallback"><i class="fas fa-user"></i></span>
                    @endif
                    <span class="mypage-mini-row__name">
                        {{ $app['cast_name'] }}
                        @if(!empty($app['job_kind_label']))
                            <span class="mypage-mini-row__sub">／{{ $app['job_kind_label'] }}</span>
                        @endif
                    </span>
                    <span class="mypage-mini-row__status is-rejected">{{ $app['status_display_label'] ?? $app['status_label'] ?? '' }}</span>
                    <i class="fas fa-chevron-right mypage-mini-row__chev"></i>
                </a>
            @endforeach
        </ul>
    @endif

    @if(empty($hiredCases) && empty($ongoingApplications) && empty($rejectedApplications))
        <div class="shop-management-empty">
            応募・採用案件がまだありません。<br>
            求人票を公開してキャストからの応募を受け付けましょう。
        </div>
    @endif
</div>

@if($primaryActionable)
    <div class="deposit-cta-bar" id="deposit-cta-bar"
         data-application-id="{{ $primaryActionable['application_id'] }}"
         data-deposit-id="{{ $primaryActionable['deposit']['id'] ?? '' }}"
         data-action="{{ $primaryActionable['actionable'] }}">
        <div class="deposit-cta-bar__inner">
            <div class="deposit-cta-bar__info">
                <span class="deposit-cta-bar__amount">
                    <i class="fas fa-user"></i>
                    <strong>{{ $primaryActionable['cast_name'] }}</strong>
                </span>
                <span class="deposit-cta-bar__label">{{ $primaryActionable['actionable_label'] }}</span>
            </div>
            <button type="button" class="deposit-cta-bar__btn" id="deposit-cta-bar-submit">
                <i class="fas {{ $primaryActionable['actionable'] === 'approve' ? 'fa-check-circle' : 'fa-yen-sign' }}"></i>
                {{ $primaryActionable['actionable_label'] }}
            </button>
        </div>
    </div>
@endif

{{-- 承認モーダル --}}
<div id="shop-approve-modal" class="shop-action-modal" role="dialog" aria-labelledby="shop-approve-modal-title" aria-modal="true" hidden>
    <div class="shop-action-modal-backdrop" data-close-approve-modal></div>
    <div class="shop-action-modal-panel">
        <div class="shop-action-modal-header">
            <h3 id="shop-approve-modal-title" class="shop-action-modal-title">ボーナス申請の承認</h3>
            <button type="button" class="shop-action-modal-close" data-close-approve-modal aria-label="閉じる"><i class="fas fa-times"></i></button>
        </div>
        <div class="shop-action-modal-body">
            <p class="shop-action-modal-note">
                キャストから提出されたレビューと達成条件を確認のうえ、承認を行ってください。<br>
                承認後は、運営から請求書が発行されます。
            </p>
            <form id="shop-approve-form" action="{{ route('shop.mypage.deposit.approve') }}" method="POST">
                @csrf
                <div class="shop-action-modal-checklist">
                    <label class="shop-action-modal-check">
                        <input type="checkbox" name="confirm_review_checked" value="1" required>
                        <span>キャストが投稿したレビュー内容を確認しました</span>
                    </label>
                    <label class="shop-action-modal-check">
                        <input type="checkbox" name="confirm_bonus_condition" value="1" required>
                        <span>求人票に登録したボーナス達成条件を満たしていることを確認しました</span>
                    </label>
                </div>
                <p class="shop-action-modal-error" id="shop-approve-error"></p>
            </form>
        </div>
        <div class="shop-action-modal-footer">
            <button type="button" class="shop-action-modal-btn shop-action-modal-btn-cancel" data-close-approve-modal>キャンセル</button>
            <button type="submit" form="shop-approve-form" class="shop-action-modal-btn shop-action-modal-btn-submit" id="shop-approve-submit" disabled>
                承認する
            </button>
        </div>
    </div>
</div>

{{-- 入金処理モーダル --}}
<div id="shop-pay-modal" class="shop-action-modal" role="dialog" aria-labelledby="shop-pay-modal-title" aria-modal="true" hidden>
    <div class="shop-action-modal-backdrop" data-close-pay-modal></div>
    <div class="shop-action-modal-panel">
        <div class="shop-action-modal-header">
            <h3 id="shop-pay-modal-title" class="shop-action-modal-title">入金処理の報告</h3>
            <button type="button" class="shop-action-modal-close" data-close-pay-modal aria-label="閉じる"><i class="fas fa-times"></i></button>
        </div>
        <div class="shop-action-modal-body">
            <p class="shop-action-modal-note">
                請求書のお支払いが完了したら、振込日と金額を入力して報告してください。<br>
                報告内容は運営側で確認後、キャストへの振込が実行されます。
            </p>
            <form id="shop-pay-form" action="{{ route('shop.mypage.deposit.pay') }}" method="POST">
                @csrf
                <div class="shop-action-modal-field">
                    <label class="shop-action-modal-label" for="shop-pay-amount">振込金額（円）<span style="color:#a78bfa;">*</span></label>
                    <input id="shop-pay-amount" type="number" name="reported_amount" min="1" step="1" required class="shop-action-modal-input" inputmode="numeric" placeholder="例: 50000">
                </div>
                <div class="shop-action-modal-field">
                    <label class="shop-action-modal-label" for="shop-pay-at">振込日時<span style="color:#a78bfa;">*</span></label>
                    <input id="shop-pay-at" type="datetime-local" name="reported_at" required class="shop-action-modal-input">
                </div>
                <div class="shop-action-modal-field">
                    <label class="shop-action-modal-label" for="shop-pay-ref">振込番号・参考情報（任意）</label>
                    <input id="shop-pay-ref" type="text" name="reference" maxlength="255" class="shop-action-modal-input" placeholder="振込番号、メモ等">
                </div>
                <p class="shop-action-modal-error" id="shop-pay-error"></p>
            </form>
        </div>
        <div class="shop-action-modal-footer">
            <button type="button" class="shop-action-modal-btn shop-action-modal-btn-cancel" data-close-pay-modal>キャンセル</button>
            <button type="submit" form="shop-pay-form" class="shop-action-modal-btn shop-action-modal-btn-submit" id="shop-pay-submit">
                報告する
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var approveModal = document.getElementById('shop-approve-modal');
    var payModal = document.getElementById('shop-pay-modal');

    // サマリーカード → 該当セクションへスクロール
    document.querySelectorAll('.case-summary-card[data-scroll-target]').forEach(function (card) {
        card.addEventListener('click', function () {
            var target = document.querySelector(card.getAttribute('data-scroll-target'));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    function openModal(el) { if (el) { el.removeAttribute('hidden'); document.body.style.overflow = 'hidden'; } }
    function closeModal(el) { if (el) { el.setAttribute('hidden', ''); document.body.style.overflow = ''; } }

    // 承認モーダル
    if (approveModal) {
        var approveForm = document.getElementById('shop-approve-form');
        var approveSubmit = document.getElementById('shop-approve-submit');
        function syncApproveReady() {
            var ok = true;
            approveForm.querySelectorAll('input[type="checkbox"][required]').forEach(function (c) { if (!c.checked) ok = false; });
            if (approveSubmit) approveSubmit.disabled = !ok;
        }
        approveForm.querySelectorAll('input[type="checkbox"]').forEach(function (c) { c.addEventListener('change', syncApproveReady); });
        document.querySelectorAll('[data-close-approve-modal]').forEach(function (b) { b.addEventListener('click', function () { closeModal(approveModal); }); });
        approveModal.addEventListener('click', function (e) { if (e.target === approveModal) closeModal(approveModal); });
    }

    // 入金処理モーダル
    if (payModal) {
        var payForm = document.getElementById('shop-pay-form');
        document.querySelectorAll('[data-close-pay-modal]').forEach(function (b) { b.addEventListener('click', function () { closeModal(payModal); }); });
        payModal.addEventListener('click', function (e) { if (e.target === payModal) closeModal(payModal); });
        // 振込日時のデフォルトを「いま」にしておく
        var payAt = document.getElementById('shop-pay-at');
        if (payAt && !payAt.value) {
            var now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            payAt.value = now.toISOString().slice(0, 16);
        }
    }

    function openApproveForCase(applicationId) {
        if (!approveModal) return;
        approveModal.querySelectorAll('input[type="checkbox"]').forEach(function (c) { c.checked = false; });
        var btn = document.getElementById('shop-approve-submit'); if (btn) btn.disabled = true;
        var err = document.getElementById('shop-approve-error'); if (err) { err.textContent = ''; err.classList.remove('show'); }
        openModal(approveModal);
    }
    function openPayForCase(invoiceAmount) {
        if (!payModal) return;
        var amt = document.getElementById('shop-pay-amount');
        if (amt && invoiceAmount) amt.value = invoiceAmount;
        var err = document.getElementById('shop-pay-error'); if (err) { err.textContent = ''; err.classList.remove('show'); }
        openModal(payModal);
    }

    document.querySelectorAll('[data-case-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var action = btn.getAttribute('data-case-action');
            var appId = btn.getAttribute('data-application-id');
            if (action === 'approve') {
                openApproveForCase(appId);
            } else if (action === 'pay') {
                var card = btn.closest('.case-card');
                var amountText = card ? (card.querySelector('.case-card__highlight strong') || {}).textContent || '' : '';
                var digits = amountText.replace(/[^0-9]/g, '');
                openPayForCase(digits ? parseInt(digits, 10) : '');
            }
        });
    });

    // フローティング CTA
    var ctaBar = document.getElementById('deposit-cta-bar');
    var ctaBtn = document.getElementById('deposit-cta-bar-submit');
    if (ctaBar && ctaBtn) {
        ctaBtn.addEventListener('click', function () {
            var action = ctaBar.getAttribute('data-action');
            var appId = ctaBar.getAttribute('data-application-id');
            if (action === 'approve') {
                openApproveForCase(appId);
            } else if (action === 'pay') {
                openPayForCase('');
            }
        });
    }
});
</script>
@endpush
