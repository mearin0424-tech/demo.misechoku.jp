@extends('layouts.app')

@section('title', 'マイページ')
@php
    $showLicenseGuide = collect($documents ?? [])->contains(fn ($doc) => ($doc['status'] ?? null) === 'not_submitted');
@endphp
@section('guide_message')
    @if($showLicenseGuide)
        営業許可証と風営許可証の両方を提出し、運営の承認がおりるまで求人を公開できません。面談日設定などの機能も、書類が整い承認後にご利用いただけます。
    @endif
@endsection

@section('header')
<header id="global-header" class="shop-mypage-custom-header">
    <div class="header-left">
        <a href="javascript:history.back()" class="btn-back" aria-label="戻る">
            <i class="fas fa-chevron-left"></i>
        </a>
    </div>
    <div class="header-center-title">
        <span class="header-title-main header-title-serif">MyPage</span>
    </div>
    <div class="header-right">
        <button id="btn-header-notification" class="header-icon-btn" aria-label="通知">
            <i class="fas fa-bell"></i>
            @if(isset($unreadNewsCount) && $unreadNewsCount > 0)
                <span class="badge-notify">{{ $unreadNewsCount }}</span>
            @else
                <span class="badge-notify">1</span>
            @endif
        </button>
        <button id="btn-header-menu" class="header-icon-btn" aria-label="メニュー">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/shop-license-documents.css') }}?v=20260505">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<style>
    /* 安心バッヂパネル（ボタン化・未付与はグレー） */
    button.mypage-stat-panel-badge {
        cursor: pointer;
        font: inherit;
        text-align: center;
        width: 100%;
    }
    .mypage-stat-panel-badge--inactive {
        border-color: rgba(120, 120, 120, 0.35);
        background: rgba(40, 40, 45, 0.55);
        opacity: 0.92;
    }
    .mypage-stat-panel-badge--inactive .mypage-stat-icon {
        color: #888 !important;
    }
    .mypage-stat-panel-badge--inactive .mypage-stat-value {
        color: #9ca3af;
        font-size: 0.88rem;
    }
    .mypage-stat-panel-badge--active {
        border-color: rgba(34, 197, 94, 0.45);
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.12);
    }
    .mypage-stat-panel-badge--active .mypage-stat-icon {
        color: #86efac !important;
    }
    .mypage-stat-panel-badge--active .mypage-stat-value {
        color: #bbf7d0;
    }
    .good-payer-badge-modal-guide {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        margin-bottom: 14px;
    }
    .good-payer-badge-modal-guide img {
        width: 56px;
        height: auto;
        flex-shrink: 0;
        filter: drop-shadow(0 4px 10px rgba(0,0,0,0.32));
    }
    .good-payer-badge-modal-bubble {
        position: relative;
        flex: 1;
        background: #fffaf0;
        color: #3f3128;
        border-radius: 14px;
        padding: 10px 12px;
        font-size: 0.8rem;
        line-height: 1.55;
        font-weight: 700;
    }
    .good-payer-badge-modal-bubble::after {
        content: '';
        position: absolute;
        right: -8px;
        bottom: 10px;
        border-width: 8px 0 8px 8px;
        border-style: solid;
        border-color: transparent transparent transparent #fffaf0;
    }
    .good-payer-badge-modal-close-top {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 999px;
        background: rgba(255,255,255,0.06);
        color: #f5ead5;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        line-height: 1;
    }
    .good-payer-badge-modal-body {
        margin: 0 0 16px;
        font-size: 0.88rem;
        line-height: 1.75;
        color: #e8e0d8;
        text-align: left;
    }
    .good-payer-badge-modal-body ul {
        margin: 10px 0 0 1.1em;
        padding: 0;
    }
    .good-payer-badge-modal-note {
        margin-top: 10px;
        font-size: 0.76rem;
        line-height: 1.65;
        color: #cabcbc;
    }
    .good-payer-badge-modal-status {
        margin-top: 14px;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .good-payer-badge-modal-status.is-yes {
        background: rgba(34, 197, 94, 0.12);
        border: 1px solid rgba(34, 197, 94, 0.28);
        color: #bbf7d0;
    }
    .good-payer-badge-modal-status.is-no {
        background: rgba(107, 114, 128, 0.15);
        border: 1px solid rgba(156, 163, 175, 0.25);
        color: #d1d5db;
    }
    .status-menu-grid {
        display: grid;
        gap: 10px;
    }
    .jobdescription-button {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 16px 18px;
        border-radius: 16px;
        border: 1px solid rgba(212, 175, 55, 0.4);
        background: radial-gradient(circle at top left, rgba(253, 240, 178, 0.18), rgba(26, 12, 14, 0.96));
        color: #f7e8c2;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 700;
    }
    .mypage-collapsible {
        margin-bottom: 16px;
    }
    .mypage-collapsible-toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid rgba(212, 175, 55, 0.26);
        background: rgba(255,255,255,0.03);
        color: #f5e9cb;
        border-radius: 12px;
        padding: 10px 12px;
        font: inherit;
        cursor: pointer;
    }
    .mypage-collapsible-content {
        margin-top: 10px;
    }
    .mypage-collapsible.is-collapsed .mypage-collapsible-content {
        display: none;
    }
    .shop-info-tag-group {
        margin-top: 12px;
    }
    .shop-info-tag-label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.72rem;
        color: #c8b8a0;
    }
    .shop-info-tag-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .shop-info-tag-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        border: 1px solid rgba(212, 175, 55, 0.35);
        background: rgba(212, 175, 55, 0.1);
        color: #f4e2b3;
        font-size: 0.72rem;
        line-height: 1.4;
        font-weight: 600;
    }

    /* --- マイページ v2：セクション見出しは控えめ・開閉なし --- */
    .shop-mypage-v2 .shop-mypage-section {
        margin-top: 32px;
    }
    .shop-mypage-v2 .shop-mypage-section:first-of-type {
        margin-top: 20px;
    }
    .shop-mypage-section-label {
        margin: 0 0 12px 4px;
        font-size: 0.78rem;
        font-weight: 600;
        font-style: italic;
        font-family: var(--font-serif);
        color: rgba(180, 170, 160, 0.75);
        letter-spacing: 0.12em;
        text-transform: none;
    }
    .shop-mypage-section-head-row {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
        padding: 0 4px;
    }
    .shop-mypage-section-head-row .shop-mypage-section-label {
        margin-bottom: 0;
    }
    .shop-mypage-section-hint {
        font-size: 0.62rem;
        color: rgba(160, 150, 145, 0.85);
        white-space: nowrap;
    }
    .shop-mypage-store-title {
        color: var(--gold);
        font-weight: 800;
    }
    .shop-mypage-stat-sub {
        font-size: 0.68rem;
        font-weight: 600;
        color: rgba(180, 170, 165, 0.9);
        margin-left: 2px;
    }

    .shop-mypage-job-primary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 14px 16px;
        margin-bottom: 10px;
        border-radius: 14px;
        border: 1px solid rgba(212, 175, 55, 0.42);
        background: linear-gradient(90deg, rgba(40, 28, 12, 0.95), rgba(14, 10, 8, 0.98));
        color: #f0d78a;
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        box-shadow: 0 4px 22px rgba(0, 0, 0, 0.35);
    }
    .shop-mypage-job-primary:hover {
        filter: brightness(1.06);
    }
    .shop-mypage-job-primary-inner {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .shop-mypage-link-stack {
        padding: 0 6px;
    }
    .shop-mypage-link-stack a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 4px;
        color: #a09690;
        text-decoration: none;
        font-size: 0.76rem;
        font-weight: 300;
        letter-spacing: 0.18em;
        border-bottom: 1px solid rgba(31, 26, 20, 0.9);
        transition: color 0.15s ease, border-color 0.15s ease;
    }
    .shop-mypage-link-stack a:last-child {
        border-bottom: none;
    }
    .shop-mypage-link-stack a:hover {
        color: #e4d8d0;
        border-bottom-color: rgba(212, 175, 55, 0.28);
    }
    .shop-mypage-link-stack a i.fa-chevron-right {
        color: rgba(90, 80, 75, 0.9);
        font-size: 0.68rem;
        transition: color 0.15s ease, transform 0.15s ease;
        transform: translateX(-4px);
    }
    .shop-mypage-link-stack a:hover i.fa-chevron-right {
        color: rgba(212, 175, 55, 0.85);
        transform: translateX(0);
    }
    .shop-mypage-link-stack-inner {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .shop-mypage-link-stack-inner i {
        width: 16px;
        text-align: center;
        font-size: 0.82rem;
        flex-shrink: 0;
    }

    .shop-mypage-edit-link {
        font-size: 0.62rem;
        font-weight: 700;
        color: var(--gold);
        border: 1px solid rgba(212, 175, 55, 0.45);
        background: transparent;
        padding: 4px 10px;
        border-radius: 999px;
        cursor: pointer;
    }
    .shop-mypage-edit-link:hover {
        background: rgba(212, 175, 55, 0.1);
    }

    .shop-mypage-info-card {
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(14, 11, 10, 0.92);
        overflow: hidden;
    }
    .shop-mypage-info-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }
    .shop-mypage-info-row:last-child {
        border-bottom: none;
    }
    .shop-mypage-info-row .k {
        flex: 0 0 88px;
        font-size: 0.68rem;
        font-weight: 700;
        color: rgba(160, 150, 145, 0.95);
    }
    .shop-mypage-info-row .v {
        flex: 1;
        min-width: 0;
        font-size: 0.82rem;
        font-weight: 600;
        color: #f2e8e4;
        line-height: 1.45;
        word-break: break-word;
    }

    .shop-mypage-tags-block {
        padding: 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }
    .shop-mypage-tag-group-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
    }
    .shop-mypage-tag-group-row:last-child {
        margin-bottom: 0;
    }
    .shop-mypage-tag-group-label {
        flex-shrink: 0;
        width: 76px;
        font-size: 0.62rem;
        font-weight: 800;
        color: rgba(212, 175, 55, 0.9);
        letter-spacing: 0.02em;
        padding-top: 4px;
    }
    .shop-mypage-tag-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        flex: 1;
    }
    .shop-mypage-tag-pills span {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid rgba(60, 56, 52, 0.9);
        background: rgba(8, 8, 10, 0.65);
        font-size: 0.66rem;
        font-weight: 600;
        color: #d0c6c0;
    }

    .shop-mypage-concept {
        padding: 14px;
    }
    .shop-mypage-concept .label {
        margin: 0 0 8px;
        font-size: 0.72rem;
        font-weight: 700;
        color: rgba(170, 160, 152, 0.95);
    }
    .shop-mypage-concept .body {
        margin: 0;
        padding: 12px;
        font-size: 0.82rem;
        line-height: 1.65;
        color: #ddd4cf;
        border-radius: 10px;
        border: 1px solid rgba(55, 48, 42, 0.85);
        background: rgba(6, 6, 8, 0.65);
    }

    .shop-mypage-custom-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .shop-mypage-custom-header .header-title-main,
    .shop-mypage-custom-header .btn-back,
    .shop-mypage-custom-header .header-icon-btn {
        color: #c8a951;
    }
    .shop-mypage-custom-header #btn-header-notification .badge-notify {
        background: #ef4444;
        color: #fff;
    }

    .shop-mypage-v2 .mypage-area {
        padding-top: 8px;
    }
    .shop-mypage-v2 .shop-mypage-store-title {
        color: #c8a951;
        font-size: clamp(1.65rem, 6vw, 2rem);
        letter-spacing: 0.04em;
    }
    .shop-mypage-v2 .mypage-hero {
        align-items: flex-start;
        gap: 14px;
    }
    .shop-mypage-v2 .shop-icon-wrapper {
        width: 84px;
        height: 84px;
        border-radius: 12px;
        background: #080808;
        border: 1px solid rgba(200, 169, 81, 0.28);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }
    .shop-mypage-v2 .shop-icon-main {
        border: 0;
        border-radius: 12px;
        box-shadow: none;
    }
    .shop-mypage-v2 .shop-word-bubble {
        border: 0;
        border-radius: 12px;
        background: #f5ebd6;
        color: #3a2f2b;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    .shop-mypage-v2 .shop-word-bubble::after {
        left: -8px;
        top: 14px;
        margin-top: 0;
        width: 0;
        height: 0;
        border-top: 6px solid transparent;
        border-right: 8px solid #f5ebd6;
        border-bottom: 6px solid transparent;
        border-left: 0;
        transform: none;
        background: transparent;
    }
    .shop-mypage-v2 .shop-word-text {
        color: #3a2f2b;
        font-weight: 700;
        font-size: 0.82rem;
        line-height: 1.55;
    }
    .shop-mypage-v2 .shop-word-bubble-updated {
        color: #8a7c74;
        font-weight: 700;
        font-size: 0.7rem;
    }
    .shop-mypage-v2 .btn-word-edit {
        color: #a89050;
        width: 24px;
        height: 24px;
    }

    .shop-mypage-v2 .mypage-stats-row {
        gap: 10px;
        margin-bottom: 24px;
    }
    .shop-mypage-v2 .mypage-stat-panel {
        border-radius: 10px;
        padding: 10px 8px;
    }
    .shop-mypage-v2 .mypage-stat-label {
        font-size: 0.76rem;
    }
    .shop-mypage-v2 .mypage-stat-value {
        font-size: 1rem;
    }
    .shop-mypage-v2 .mypage-stat-panel-badge--active {
        border-color: rgba(248, 231, 173, 0.6);
        background: linear-gradient(to bottom, #f2d780, #cd9d40);
        box-shadow: 0 2px 4px rgba(0,0,0,0.4), inset 0 1px 1px rgba(255,255,255,0.7);
    }
    .shop-mypage-v2 .mypage-stat-panel-badge--active .mypage-stat-icon,
    .shop-mypage-v2 .mypage-stat-panel-badge--active .mypage-stat-label,
    .shop-mypage-v2 .mypage-stat-panel-badge--active .mypage-stat-value {
        color: #4a1620 !important;
    }
    .shop-mypage-v2 .mypage-stat-panel-link {
        background: rgba(90, 28, 44, 0.8);
        border-color: rgba(200, 169, 81, 0.3);
        color: #c8a951;
    }
    .shop-mypage-v2 .mypage-stat-panel-link:hover {
        background: rgba(90, 28, 44, 1);
    }
    .shop-mypage-v2 .mypage-stat-panel-link .mypage-stat-icon {
        color: #c8a951 !important;
    }
    .shop-mypage-v2 .mypage-stat-panel-link .mypage-stat-label {
        color: #d4c3b3;
    }
    .shop-mypage-v2 .mypage-stat-panel-link .mypage-stat-value {
        color: #c8a951;
        font-size: 0.92rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .shop-mypage-v2 .shop-mypage-stat-sub {
        color: #d4c3b3;
        margin-left: 0;
    }

    .shop-mypage-v2 .shop-mypage-section-label {
        color: #c8a951;
        font-size: 0.98rem;
        font-style: italic;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(200, 169, 81, 0.3);
    }
    .shop-mypage-v2 .shop-mypage-job-button {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(166, 58, 92, 0.8);
        background: linear-gradient(135deg, #8a2542, #5a1628);
        color: #fff;
        text-decoration: none;
        font-size: 1.04rem;
        font-weight: 800;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.28);
        position: relative;
        overflow: hidden;
        transition: transform 0.15s ease, filter 0.15s ease;
    }
    .shop-mypage-v2 .shop-mypage-job-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 42%;
        background: rgba(255, 255, 255, 0.1);
    }
    .shop-mypage-v2 .shop-mypage-job-button:hover {
        filter: brightness(1.08);
    }
    .shop-mypage-v2 .shop-mypage-job-button:active {
        transform: scale(0.98);
    }
    .shop-mypage-v2 .shop-mypage-job-primary-inner {
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        z-index: 1;
    }
    .shop-mypage-v2 .shop-mypage-job-primary-icon {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .shop-mypage-v2 .shop-mypage-job-button i.fa-chevron-right {
        color: #f5ebd6;
        position: relative;
        z-index: 1;
    }

    .shop-mypage-v2 .shop-mypage-info-card {
        border: 1px solid rgba(90, 29, 40, 0.85);
        background: #2c131a;
    }
    .shop-mypage-v2 .shop-mypage-info-row {
        border-bottom: 1px solid rgba(90, 29, 40, 0.55);
    }
    .shop-mypage-v2 .shop-mypage-info-row .k {
        color: #c8a951;
        font-size: 0.78rem;
    }
    .shop-mypage-v2 .shop-mypage-info-row .v {
        color: #fff;
        font-size: 0.88rem;
    }
    .shop-mypage-v2 .shop-mypage-edit-link {
        font-size: 0.72rem;
    }
</style>
@endpush

@section('content')
<div class="mypage-page contents inner animate-fadeIn shop-mypage-v2">
    <section class="mypage-area">
        <h1 class="mypage-shop-name serif-font shop-mypage-store-title">{{ $shopData['shop_name'] }}</h1>

        <div class="mypage-hero">
            <div class="shop-icon-wrapper">
                <img src="{{ (isset($subImages[0]) ? $subImages[0]['url'] : null) ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display" alt="">
            </div>
            <div class="shop-word-bubble glass-panel">
                <p id="display-word" class="shop-word-text {{ empty(trim($shopData['word'] ?? '')) ? 'is-placeholder' : '' }}" data-placeholder="ひとことを入力すると、タイムラインに表示されます。">{{ !empty(trim($shopData['word'] ?? '')) ? $shopData['word'] : 'ひとことを入力すると、タイムラインに表示されます。' }}</p>
                <div class="shop-word-bubble-footer">
                    <span id="display-word-updated" class="shop-word-bubble-updated">最終更新 {{ $shopData['appeal_updated_at'] ?? '未設定' }}</span>
                    <button type="button" class="btn-word-edit" id="open-word-edit-btn" aria-label="ひとことを編集">
                        <i class="fas fa-pen"></i>
                    </button>
                </div>
            </div>
        </div>

        @php $hasGoodPayerBadge = !empty($shopData['badges']['good_payer']); @endphp
        <div class="mypage-stats-row mypage-stats-row--cols-2" aria-label="統計">
            <button type="button"
                class="mypage-stat-panel mypage-stat-panel-badge {{ $hasGoodPayerBadge ? 'mypage-stat-panel-badge--active' : 'mypage-stat-panel-badge--inactive' }}"
                id="open-good-payer-badge-modal"
                aria-haspopup="dialog"
                aria-controls="modal-good-payer-badge"
                aria-label="安心バッヂの説明を開く">
                <span class="mypage-stat-icon" aria-hidden="true"><i class="fas fa-crown"></i></span>
                <span class="mypage-stat-label">優良店</span>
                <span class="mypage-stat-value">{{ $hasGoodPayerBadge ? '優良店' : '未付与' }}</span>
            </button>
            <a href="{{ route('shop.mypage.review.index') }}" class="mypage-stat-panel mypage-stat-panel-link">
                <span class="mypage-stat-icon"><i class="fas fa-star"></i></span>
                <span class="mypage-stat-label">レビュー</span>
                <span class="mypage-stat-value">{{ number_format($shopData['review_avg'], 1) }}<span class="shop-mypage-stat-sub">レビュー</span><i class="fas fa-chevron-right" aria-hidden="true"></i></span>
            </a>
        </div>

        <div class="shop-mypage-section">
            <h3 class="shop-mypage-section-label">Job Management</h3>
            <a href="{{ route('shop.jobdescription') }}" class="shop-mypage-job-button">
                <span class="shop-mypage-job-primary-inner">
                    <span class="shop-mypage-job-primary-icon"><i class="far fa-file-alt"></i></span>
                    <span>求人票の管理</span>
                </span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="{{ route('shop.mypage.management') }}" class="shop-mypage-job-button">
                <span class="shop-mypage-job-primary-inner">
                    <span class="shop-mypage-job-primary-icon"><i class="fas fa-users-cog" aria-hidden="true"></i></span>
                    <span>採用・入金管理</span>
                </span>
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <div class="shop-mypage-section profile-info-section">
            <div class="shop-mypage-section-head-row">
                <h3 class="shop-mypage-section-label">Shop Information</h3>
                <button type="button" class="shop-mypage-edit-link" id="open-profile-edit-btn">編集</button>
            </div>
            <div class="shop-mypage-info-card">
                <div class="shop-mypage-info-row">
                    <span class="k">店舗名</span>
                    <span class="v">{{ $shopInfo['shop_name'] ?: '—' }}</span>
                </div>
                <div class="shop-mypage-info-row">
                    <span class="k">業種</span>
                    <span class="v">{{ $shopInfo['industry'] ?? '未設定' }}</span>
                </div>
                <div class="shop-mypage-info-row">
                    <span class="k">郵便番号</span>
                    <span class="v">{{ $shopInfo['zip'] ?: '—' }}</span>
                </div>
                <div class="shop-mypage-info-row">
                    <span class="k">住所</span>
                    <span class="v">{{ trim(($shopInfo['pref'] ?? '') . ($shopInfo['city'] ?? '') . ($shopInfo['addr1'] ?? '')) ?: '—' }}</span>
                </div>
                @if(!empty($shopInfo['tel'] ?? null))
                    <div class="shop-mypage-info-row">
                        <span class="k">電話</span>
                        <span class="v">{{ $shopInfo['tel'] }}</span>
                    </div>
                @endif
                @if(!empty($shopInfo['business_hours_shop'] ?? null))
                    <div class="shop-mypage-info-row">
                        <span class="k">店舗の営業時間</span>
                        <span class="v">{{ $shopInfo['business_hours_shop'] }}</span>
                    </div>
                @endif
                @if(!empty($shopInfo['nearest_stations'] ?? []))
                    <div class="shop-mypage-info-row">
                        <span class="k">最寄り駅</span>
                        <span class="v">{!! nl2br(e(implode("\n", $shopInfo['nearest_stations']))) !!}</span>
                    </div>
                @elseif(!empty($shopInfo['nearest_station'] ?? null))
                    <div class="shop-mypage-info-row">
                        <span class="k">最寄り</span>
                        <span class="v">{{ $shopInfo['nearest_station'] }}</span>
                    </div>
                @endif
                @if(!empty($shopInfo['working_hours'] ?? null) || !empty($shopInfo['working_days'] ?? null) || !empty($shopInfo['regular_holiday'] ?? null))
                    <div class="shop-mypage-info-row">
                        <span class="k">勤務・休日</span>
                        <span class="v">
                            @if(!empty($shopInfo['working_hours'])){{ $shopInfo['working_hours'] }}@else時間未設定@endif
                            ／
                            @if(!empty($shopInfo['working_days'])){{ $shopInfo['working_days'] }}@else勤務日未設定@endif
                            @if(!empty($shopInfo['regular_holiday']))
                                <br>定休: {{ $shopInfo['regular_holiday'] }}
                            @endif
                        </span>
                    </div>
                @endif

                @php $tagGroups = $shopInfo['tag_groups'] ?? []; @endphp
                @if(!empty($tagGroups))
                    <div class="shop-mypage-tags-block">
                        @foreach($tagGroups as $group)
                            @php
                                $gLabel = (string) ($group['label'] ?? '');
                                if (str_contains($gLabel, 'ご利用プラン')) {
                                    continue;
                                }
                                $gTags = array_values(array_filter(
                                    (array) ($group['tags'] ?? []),
                                    static fn ($t) => ! str_contains((string) $t, 'ご利用プラン')
                                ));
                            @endphp
                            @if($gTags !== [])
                            <div class="shop-mypage-tag-group-row">
                                <span class="shop-mypage-tag-group-label">{{ $gLabel }}</span>
                                <div class="shop-mypage-tag-pills">
                                    @foreach($gTags as $t)
                                        <span>{{ $t }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="shop-mypage-section gallery-edit-section">
            <div class="shop-mypage-section-head-row">
                <h3 class="shop-mypage-section-label">Image Library</h3>
                <span class="shop-mypage-section-hint">ドラッグで並び替え</span>
            </div>
            <ul class="responsive-gallery gallery-grid" id="gallery-list" data-sort-save-url="{{ route('shop.profile.images.order') }}" data-empty-image-url="{{ asset('assets/images/common/no-image.png') }}">
                @for($i = 0; $i < 8; $i++)
                @php $img = $subImages[$i] ?? null; @endphp
                <li class="gallery-grid-item" data-slot-index="{{ $i }}">
                    <div class="photo-slot {{ $img ? 'has-img' : '' }}" data-image-id="{{ $img['id'] ?? '' }}" data-image-url="{{ $img['url'] ?? '' }}">
                        @if($img)
                            <img src="{{ $img['url'] }}" alt="" loading="lazy">
                            @if($i === 0)<span class="photo-slot-badge">MAIN</span>@endif
                        @else
                            <span class="photo-slot-empty"><i class="fas fa-image"></i></span>
                        @endif
                    </div>
                </li>
                @endfor
            </ul>
        </div>

        @include('shops.mypage.partials.shop-license-documents', ['documents' => $documents ?? []])
    </section>
</div>

{{-- 画像大表示モーダル（削除ボタンで削除） --}}
<div id="image-preview-modal" class="mypage-modal-overlay gallery-preview-overlay" role="dialog" aria-label="画像プレビュー">
    <div class="gallery-preview-inner">
        <img id="modal-img" src="" alt="" class="mypage-modal-preview-img">
        <div class="gallery-preview-actions">
            <button type="button" class="btn-action btn-action-secondary gallery-preview-btn-close" id="gallery-preview-close-btn">閉じる</button>
            <button type="button" id="gallery-preview-delete-btn" class="btn-action gallery-preview-btn-delete">削除</button>
        </div>
    </div>
</div>

{{-- 画像編集モーダル（推奨サイズに合わせてトリミング） --}}
<div id="image-edit-modal" class="mypage-modal-overlay gallery-preview-overlay" role="dialog" aria-label="画像編集" style="display:none;">
    <div class="gallery-preview-inner image-edit-inner">
        <div class="image-edit-header">
            <h3 class="mypage-modal-title serif-font">画像を調整してアップロード</h3>
            <p class="image-edit-guide">
                推奨サイズは <strong>16:9（例：1600×900px、横長）</strong> です。<br>
                画面に表示されている範囲で中央を基準に自動トリミングし、求人票のギャラリー表示に最適化してアップロードします。
            </p>
        </div>
        <div class="image-edit-preview-wrapper">
            <div class="image-edit-frame">
                <img id="image-edit-preview" src="" alt="編集プレビュー" class="image-edit-preview-img">
                <div class="image-edit-frame-mask"></div>
            </div>
        </div>
        <div class="gallery-preview-actions image-edit-actions">
            <button type="button" class="btn-action btn-action-secondary" id="image-edit-cancel-btn">別の画像を選ぶ</button>
            <button type="button" class="btn-action btn-action-primary" id="image-edit-confirm-btn">この画像でアップロード</button>
        </div>
    </div>
</div>

{{-- 優良店バッヂの仕様（タップで表示） --}}
<div id="modal-good-payer-badge" class="mypage-modal-overlay modal-word-edit" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="good-payer-badge-modal-title">
    <div class="mypage-modal-panel glass-panel">
        <button type="button" class="good-payer-badge-modal-close-top" id="good-payer-badge-modal-close-top" aria-label="閉じる">×</button>
        <h3 id="good-payer-badge-modal-title" class="mypage-modal-title serif-font">優良店バッヂとは？</h3>
        <div class="good-payer-badge-modal-guide" aria-hidden="true">
            <p class="good-payer-badge-modal-bubble">条件を満たしていないため未付与です。上記を満たすと優良店バッヂが表示されます。</p>
            <img src="{{ asset('assets/images/guide/guide-character.png') }}" alt="">
        </div>
        <div class="good-payer-badge-modal-body">
            <p>優良店バッヂは、直近3ヶ月の請求・入金履歴をもとに、安心して働ける店舗の目安として付与されます。</p>
            <ul>
                <li>すべての案件が「店舗入金確認済み」まで完了している</li>
                <li>請求書発行から店舗入金確認までが10日以内である</li>
            </ul>
            <p class="good-payer-badge-modal-note">※ 条件は毎月見直され、基準を満たさなくなった場合はバッヂ表示が外れることがあります。</p>
        </div>
        <div class="good-payer-badge-modal-status {{ $hasGoodPayerBadge ? 'is-yes' : 'is-no' }}">
            @if($hasGoodPayerBadge)
                現在のお店：優良店バッヂが付与されています。
            @else
                現在のお店：条件を満たしていないため未付与です。上記を満たすと優良店バッヂが表示されます。
            @endif
        </div>
        <div class="mypage-modal-actions">
            <button type="button" class="btn-action btn-action-primary" id="good-payer-badge-modal-close">閉じる</button>
        </div>
    </div>
</div>

{{-- ひとこと編集モーダル --}}
<div id="modal-word" class="mypage-modal-overlay modal-word-edit" style="display:none;">
    <div class="mypage-modal-panel glass-panel">
        <h3 class="mypage-modal-title serif-font">ひとことを編集</h3>
        <textarea id="word-input" rows="3" class="mypage-modal-textarea" placeholder="例：新人大歓迎！働きやすさもお任せください。"></textarea>
        <div class="mypage-modal-actions">
            <button type="button" class="btn-action btn-action-secondary" id="word-edit-cancel-btn">戻る</button>
            <button type="button" class="btn-action btn-action-primary" id="word-edit-save-btn">保存</button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" class="sr-only" accept="image/*">
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="{{ asset('assets/js/gallery-sortable.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
window.MYPAGE_GALLERY_CONFIG = {
    csrfToken: @json(csrf_token()),
    uploadUrl: @json(route('shop.profile.upload.image')),
    deleteUrlTemplate: @json(route('shop.profile.image.delete', ['id' => '__ID__']))
};
</script>
<script src="{{ asset('assets/js/mypage-gallery.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var placeholderText = 'ひとことを入力すると、タイムラインに表示されます。';
    var openWordBtn = document.getElementById('open-word-edit-btn');
    if (openWordBtn) openWordBtn.addEventListener('click', function() {
        document.getElementById('modal-word').style.display = 'flex';
        var displayEl = document.getElementById('display-word');
        var current = displayEl && displayEl.innerText ? displayEl.innerText.trim() : '';
        var wordInput = document.getElementById('word-input');
        if (wordInput) wordInput.value = (current === placeholderText) ? '' : current;
    });
    var cancelWord = document.getElementById('word-edit-cancel-btn');
    if (cancelWord) cancelWord.addEventListener('click', function() { var modalWord = document.getElementById('modal-word'); if (modalWord) modalWord.style.display = 'none'; });
    var saveWordBtn = document.getElementById('word-edit-save-btn');
    if (saveWordBtn) saveWordBtn.addEventListener('click', function() {
        var wordInputEl = document.getElementById('word-input');
        var val = (wordInputEl && wordInputEl.value || '').trim();
        var displayEl = document.getElementById('display-word');
        var updatedEl = document.getElementById('display-word-updated');
        var m = document.getElementById('modal-word');
        var btn = saveWordBtn;
        if (btn.disabled) return;
        btn.disabled = true;
        fetch('{{ route('shop.mypage.word') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ word: val })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                if (displayEl) {
                    displayEl.innerText = val || placeholderText;
                    displayEl.classList.toggle('is-placeholder', !val);
                }
                if (updatedEl && res.appeal_updated_at) {
                    updatedEl.innerText = '最終更新 ' + res.appeal_updated_at;
                }
                if (m) m.style.display = 'none';
            } else {
                alert(res.message || '保存に失敗しました');
            }
        })
        .catch(function() { alert('保存に失敗しました'); })
        .finally(function() { btn.disabled = false; });
    });
    var profileEditBtn = document.getElementById('open-profile-edit-btn');
    if (profileEditBtn) profileEditBtn.addEventListener('click', function() {
        location.href = "{{ route('shop.profile.store.edit') }}";
    });

    var openBadgeModal = document.getElementById('open-good-payer-badge-modal');
    var badgeModal = document.getElementById('modal-good-payer-badge');
    var closeBadgeModal = document.getElementById('good-payer-badge-modal-close');
    var closeBadgeModalTop = document.getElementById('good-payer-badge-modal-close-top');
    function hideBadgeModal() {
        if (badgeModal) badgeModal.style.display = 'none';
        if (openBadgeModal) openBadgeModal.focus();
    }
    if (openBadgeModal && badgeModal) {
        openBadgeModal.addEventListener('click', function() {
            badgeModal.style.display = 'flex';
            if (closeBadgeModal) closeBadgeModal.focus();
        });
        badgeModal.addEventListener('click', function(e) {
            if (e.target === badgeModal) hideBadgeModal();
        });
    }
    if (closeBadgeModal) closeBadgeModal.addEventListener('click', hideBadgeModal);
    if (closeBadgeModalTop) closeBadgeModalTop.addEventListener('click', hideBadgeModal);
});
</script>
@endpush
