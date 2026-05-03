@extends('layouts.app')

@section('title', 'マイページ')
@php
    $showLicenseGuide = collect($documents ?? [])->contains(fn ($doc) => ($doc['status'] ?? null) === 'not_submitted');
@endphp
@section('guide_message')
    @if($showLicenseGuide)
        営業許可証または風営許可証が、まだそろっていないようです。両方がそろいますと、面談日設定などの機能もご利用いただけますので、先にこちらをご準備ください。
    @endif
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
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
    .document-upload-group {
        margin-bottom: 14px;
    }
    .document-upload-group-title {
        margin: 0 0 8px;
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #bdaaaa;
    }
    .document-upload-list {
        display: grid;
        gap: 10px;
    }
    .document-upload-card {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid rgba(212, 175, 55, 0.2);
        background: rgba(255,255,255,0.02);
        cursor: pointer;
        text-align: left;
        font: inherit;
    }
    .document-upload-card.is-missing {
        border-style: dashed;
        border-color: rgba(224, 108, 108, 0.45);
        background: rgba(64, 22, 24, 0.28);
    }
    .document-upload-name {
        margin: 0 0 6px;
        font-size: 0.9rem;
        font-weight: 700;
        color: #fff8ea;
    }
    .document-status-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
    }
    .document-status-chip.is-approved { color: #dcfce7; background: rgba(34, 197, 94, 0.14); border: 1px solid rgba(34, 197, 94, 0.24); }
    .document-status-chip.is-pending { color: #f6e7af; background: rgba(212, 175, 55, 0.12); border: 1px solid rgba(212, 175, 55, 0.22); }
    .document-status-chip.is-rejected { color: #fee2e2; background: rgba(248, 113, 113, 0.12); border: 1px solid rgba(248, 113, 113, 0.24); }
    .document-status-chip.is-not-submitted { color: #ffd4d4; background: rgba(128, 30, 35, 0.28); border: 1px solid rgba(224, 108, 108, 0.35); }
    .document-upload-meta {
        margin-top: 6px;
        font-size: 0.73rem;
        color: #bdaaaa;
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

    .shop-mypage-license-card {
        width: 100%;
        display: block;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid rgba(212, 175, 55, 0.18);
        background: rgba(12, 10, 9, 0.88);
        cursor: pointer;
        text-align: left;
        font: inherit;
        margin-bottom: 10px;
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    .shop-mypage-license-card:hover {
        border-color: rgba(212, 175, 55, 0.35);
    }
    .shop-mypage-license-card.is-missing {
        border-style: dashed;
        border-color: rgba(180, 70, 70, 0.45);
        background: rgba(40, 18, 20, 0.35);
    }
    .shop-mypage-license-card-body {
        width: 100%;
        min-width: 0;
    }
    .license-upload-modal {
        position: relative;
        max-width: 420px;
        width: 100%;
        padding: 0 0 20px;
    }
    .license-upload-modal__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px 12px;
        border-bottom: 1px solid rgba(90, 78, 68, 0.45);
    }
    .license-upload-modal__head .mypage-modal-title {
        margin: 0;
        font-size: 1rem;
    }
    .license-upload-modal__close {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        margin: -6px -8px 0 0;
        border: none;
        border-radius: 10px;
        background: rgba(255,255,255,0.06);
        color: #c4b5a8;
        font-size: 1.35rem;
        line-height: 1;
        cursor: pointer;
    }
    .license-upload-modal__close:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
    }
    .license-upload-modal__body {
        padding: 16px 20px 0;
    }
    .license-upload-modal__doc-name {
        margin: 0 0 10px;
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff8ea;
    }
    .license-upload-modal__status-row {
        margin-bottom: 8px;
    }
    .license-upload-modal__ng {
        margin: 0 0 10px;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 0.78rem;
        line-height: 1.55;
        color: #fecaca;
        background: rgba(127, 29, 29, 0.28);
        border: 1px solid rgba(248, 113, 113, 0.25);
    }
    .license-upload-modal__meta {
        margin: 0 0 14px;
        font-size: 0.74rem;
        color: #bdaaaa;
    }
    .license-upload-dropzone {
        margin-top: 4px;
        padding: 16px 14px;
        border-radius: 12px;
        border: 1px dashed rgba(212, 175, 55, 0.28);
        background: rgba(8, 8, 10, 0.55);
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .license-upload-dropzone.is-dragover {
        border-color: rgba(212, 175, 55, 0.65);
        background: rgba(212, 175, 55, 0.06);
    }
    .license-upload-dropzone__hint {
        margin: 0 0 14px;
        font-size: 0.76rem;
        line-height: 1.55;
        color: #a8988c;
    }
    .license-upload-modal__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .license-upload-modal__filename {
        margin: 12px 20px 0;
        font-size: 0.72rem;
        color: #8a7d72;
        min-height: 1.2em;
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
                <span class="mypage-stat-icon" aria-hidden="true"><i class="fas fa-award"></i></span>
                <span class="mypage-stat-label">優良店バッヂ</span>
                <span class="mypage-stat-value">{{ $hasGoodPayerBadge ? '取得済み' : '未付与' }}</span>
            </button>
            <a href="{{ route('shop.mypage.review.index') }}" class="mypage-stat-panel mypage-stat-panel-link">
                <span class="mypage-stat-icon"><i class="fas fa-star"></i></span>
                <span class="mypage-stat-label">評価</span>
                <span class="mypage-stat-value">{{ number_format($shopData['review_avg'], 1) }}<span class="shop-mypage-stat-sub">({{ $shopData['review_count'] }}件)</span></span>
            </a>
        </div>

        <div class="shop-mypage-section">
            <h3 class="shop-mypage-section-label">Job Management</h3>
            <a href="{{ route('shop.jobdescription') }}" class="shop-mypage-job-primary">
                <span class="shop-mypage-job-primary-inner">
                    <i class="far fa-file-alt"></i>
                    <span>求人票の管理</span>
                </span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <div class="shop-mypage-link-stack">
                <a href="{{ route('shop.recruits.status') }}">
                    <span class="shop-mypage-link-stack-inner">
                        <i class="fas fa-users" aria-hidden="true"></i>
                        採用管理
                    </span>
                    <i class="fas fa-chevron-right"></i>
                </a>
                <a href="{{ route('shop.mypage.payment.index') }}">
                    <span class="shop-mypage-link-stack-inner">
                        <i class="far fa-credit-card" aria-hidden="true"></i>
                        入金管理
                    </span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
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
                @if(!empty($shopInfo['nearest_station'] ?? null))
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

        <div class="shop-mypage-section document-section">
            <h3 class="shop-mypage-section-label">Licenses</h3>
            @foreach(($documents ?? []) as $doc)
                @php
                    $s = $doc['status'] ?? 'not_submitted';
                    $record = $doc['record'] ?? null;
                    $label = $doc['status_label'] ?? ($s === 'approved' ? '承認済み' : ($s === 'rejected' ? '差し戻し' : ($s === 'pending' ? '審査中' : '未提出')));
                    $isMissing = $s === 'not_submitted';
                @endphp
                <button type="button"
                    class="shop-mypage-license-card js-license-card {{ $isMissing ? 'is-missing' : '' }}"
                    data-doc-key="{{ $doc['key'] }}"
                    data-doc-name="{{ $doc['name'] }}"
                    data-doc-status="{{ $s }}"
                    data-doc-status-label="{{ $label }}"
                    data-doc-url="{{ $record['file_url'] ?? '' }}"
                    data-doc-updated="{{ $record['updated_at_label'] ?? '' }}"
                    data-ng-reason="{{ $record['ng_reason'] ?? '' }}">
                    <div class="shop-mypage-license-card-body">
                        <p class="document-upload-name">{{ $doc['name'] }}</p>
                        <span class="document-status-chip is-{{ str_replace('_', '-', $s) }}">{{ $label }}</span>
                        <p class="document-upload-meta">
                            @if($isMissing)
                                タップしてファイルを提出してください
                            @else
                                最終更新: {{ $record['updated_at_label'] ?? '—' }}
                            @endif
                        </p>
                    </div>
                </button>
            @endforeach
        </div>
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

<div id="modal-license-detail" class="mypage-modal-overlay modal-word-edit" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="license-detail-title">
    <div class="mypage-modal-panel glass-panel license-upload-modal">
        <div class="license-upload-modal__head">
            <h3 id="license-detail-title" class="mypage-modal-title serif-font">許可証の提出</h3>
            <button type="button" class="license-upload-modal__close" id="license-detail-close-btn" aria-label="閉じる">×</button>
        </div>
        <div class="license-upload-modal__body">
            <p id="license-detail-name" class="license-upload-modal__doc-name"></p>
            <div class="license-upload-modal__status-row">
                <span id="license-detail-status-chip" class="document-status-chip is-not-submitted" role="status"></span>
            </div>
            <p id="license-detail-ng" class="license-upload-modal__ng" style="display:none;"></p>
            <p id="license-detail-updated" class="license-upload-modal__meta"></p>
            <div id="license-detail-dropzone" class="license-upload-dropzone">
                <p class="license-upload-dropzone__hint">PDF、JPEG、PNG（最大 8MB）をドラッグ＆ドロップするか、「ファイルを選択」からアップロードしてください。</p>
                <input type="hidden" id="license-detail-type" value="">
                <input type="file" id="license-detail-file" class="sr-only" accept=".pdf,.png,.jpg,.jpeg,image/*,application/pdf">
                <div class="license-upload-modal__actions">
                    <button type="button" class="btn-action btn-action-primary" id="license-detail-pick-btn">ファイルを選択</button>
                    <a id="license-detail-view-link" href="#" target="_blank" rel="noopener" class="btn-action btn-action-secondary" style="display:none;">書類を表示</a>
                </div>
            </div>
        </div>
        <p id="license-detail-file-hint" class="license-upload-modal__filename" aria-live="polite"></p>
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
<script>
(function() {
    var cards = document.querySelectorAll('.js-license-card');
    var detailModal = document.getElementById('modal-license-detail');
    if (!cards.length || !detailModal) return;

    var detailName = document.getElementById('license-detail-name');
    var detailChip = document.getElementById('license-detail-status-chip');
    var detailNg = document.getElementById('license-detail-ng');
    var detailUpdated = document.getElementById('license-detail-updated');
    var detailType = document.getElementById('license-detail-type');
    var detailFile = document.getElementById('license-detail-file');
    var detailPickBtn = document.getElementById('license-detail-pick-btn');
    var detailViewLink = document.getElementById('license-detail-view-link');
    var detailCloseBtn = document.getElementById('license-detail-close-btn');
    var detailDropzone = document.getElementById('license-detail-dropzone');
    var detailFileHint = document.getElementById('license-detail-file-hint');

    function setChip(chipEl, statusKey, labelText) {
        if (!chipEl) return;
        var sk = (statusKey || 'not_submitted').replace(/_/g, '-');
        chipEl.className = 'document-status-chip is-' + sk;
        chipEl.textContent = labelText || '';
    }

    function closeModal() {
        detailModal.style.display = 'none';
        if (detailFile) detailFile.value = '';
        if (detailFileHint) detailFileHint.textContent = '';
        if (detailDropzone) detailDropzone.classList.remove('is-dragover');
    }

    function uploadFile(file) {
        if (!file || !detailType) return;
        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('type', detailType.value || '');
        formData.append('file', file);

        if (detailPickBtn) detailPickBtn.disabled = true;
        if (detailFileHint) detailFileHint.textContent = 'アップロード中…';

        fetch('{{ route("shop.mypage.documents.upload") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        }).then(function(r) {
            return r.json().then(function(json) {
                if (!r.ok) throw json;
                return json;
            });
        }).then(function(res) {
            alert(res.message || '書類をアップロードしました。');
            window.location.reload();
        }).catch(function(error) {
            var messages = error && error.errors ? Object.values(error.errors).flat() : [];
            alert(messages[0] || (error && error.message) || 'アップロードに失敗しました。');
            if (detailFileHint) detailFileHint.textContent = '';
        }).finally(function() {
            if (detailPickBtn) detailPickBtn.disabled = false;
            if (detailFile) detailFile.value = '';
        });
    }

    cards.forEach(function(card) {
        card.addEventListener('click', function() {
            var docStatus = card.getAttribute('data-doc-status') || 'not_submitted';
            var docUrl = card.getAttribute('data-doc-url') || '';
            var statusLabel = card.getAttribute('data-doc-status-label') || '';
            var ngReason = card.getAttribute('data-ng-reason') || '';

            if (detailName) detailName.textContent = card.getAttribute('data-doc-name') || '書類';
            if (detailType) detailType.value = card.getAttribute('data-doc-key') || '';

            setChip(detailChip, docStatus, statusLabel);

            if (detailNg) {
                if (docStatus === 'rejected' && ngReason) {
                    detailNg.style.display = 'block';
                    detailNg.textContent = '差し戻し理由: ' + ngReason;
                } else {
                    detailNg.style.display = 'none';
                    detailNg.textContent = '';
                }
            }

            if (detailUpdated) {
                if (docStatus === 'not_submitted') {
                    detailUpdated.textContent = 'まだファイルが提出されていません。';
                } else {
                    detailUpdated.textContent = '最終更新: ' + (card.getAttribute('data-doc-updated') || '—');
                }
            }

            if (detailViewLink) {
                if (docUrl && docStatus !== 'not_submitted') {
                    detailViewLink.href = docUrl;
                    detailViewLink.style.display = 'inline-flex';
                } else {
                    detailViewLink.style.display = 'none';
                }
            }

            if (detailFileHint) detailFileHint.textContent = '';

            detailModal.style.display = 'flex';
        });
    });

    detailModal.addEventListener('click', function(e) { if (e.target === detailModal) closeModal(); });
    if (detailCloseBtn) detailCloseBtn.addEventListener('click', closeModal);
    if (detailPickBtn && detailFile) {
        detailPickBtn.addEventListener('click', function() { detailFile.click(); });
    }

    if (detailFile) {
        detailFile.addEventListener('change', function() {
            if (!detailFile.files || !detailFile.files.length) return;
            uploadFile(detailFile.files[0]);
        });
    }

    if (detailDropzone && detailFile) {
        detailDropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            detailDropzone.classList.add('is-dragover');
        });
        detailDropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            if (e.target === detailDropzone) detailDropzone.classList.remove('is-dragover');
        });
        detailDropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            detailDropzone.classList.remove('is-dragover');
            var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (f) uploadFile(f);
        });
    }
})();
</script>
@endpush
