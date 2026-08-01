@extends('layouts.app-v2')

@section('title', '本人確認')
@section('body-class', 'page-cast-mypage page-cast-identity')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<style>
/* ============================================================
   本人確認ページ（ライトモード）
   共通デザイントークン（フォント2段階・色3段階に揃える）
   - ヘッダー文字   : #1e1a30 / 800
   - 本文           : #4a4560 / 500
   - 補助（灰紫）   : #8b84a1 / 500
   - アクセント     : #7c3aed
   - 面（カード）   : #ffffff / rgba紫 border / soft shadow
   ============================================================ */
:root {
    --doc-ink:      #1e1a30;
    --doc-body:     #4a4560;
    --doc-muted:    #8b84a1;
    --doc-accent:   #7c3aed;
    --doc-accent-2: #a78bfa;
    --doc-line:     rgba(124, 58, 237, 0.18);
    --doc-line-2:   rgba(124, 58, 237, 0.30);
    --doc-surface:  #ffffff;
    --doc-shadow:   0 4px 14px rgba(76, 29, 149, 0.08);
    --doc-radius:   16px;
}

/* 外側ラッパー：mypage.css のダーク面を打ち消して薄紫背景を通す */
.cast-mypage-sub-page .mypage-detail-box,
.cast-mypage-sub-page .mypage-section {
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    padding: 0 !important;
}
.cast-mypage-sub-page { color: var(--doc-body); }

/* ============================================================
   1. ヒーロー：全体ステータス
   ============================================================ */
.identity-hero {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 20px;
    margin-bottom: 20px;
    border-radius: var(--doc-radius);
    background: linear-gradient(180deg, rgba(168, 85, 247, 0.09), rgba(168, 85, 247, 0.02));
    border: 1px solid var(--doc-line-2);
    box-shadow: var(--doc-shadow);
}
.identity-hero__icon {
    flex: 0 0 auto;
    width: 44px; height: 44px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(124, 58, 237, 0.12);
    color: var(--doc-accent);
    font-size: 1.35rem;
}
.identity-hero__body { flex: 1; min-width: 0; }
.identity-hero__title {
    margin: 0 0 4px;
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--doc-ink);
    letter-spacing: -0.005em;
    line-height: 1.3;
}
.identity-hero__desc {
    margin: 0;
    font-size: 0.82rem;
    color: var(--doc-body);
    line-height: 1.65;
}
.identity-hero.is-verified {
    background: linear-gradient(180deg, rgba(16, 185, 129, 0.10), rgba(16, 185, 129, 0.02));
    border-color: rgba(16, 185, 129, 0.35);
}
.identity-hero.is-verified .identity-hero__icon { background: rgba(16, 185, 129, 0.12); color: #059669; }
.identity-hero.is-pending {
    background: linear-gradient(180deg, rgba(217, 119, 6, 0.09), rgba(217, 119, 6, 0.02));
    border-color: rgba(217, 119, 6, 0.35);
}
.identity-hero.is-pending .identity-hero__icon { background: rgba(217, 119, 6, 0.12); color: #b45309; }

/* フラッシュ */
.identity-flash {
    margin: 0 0 16px;
    padding: 12px 14px;
    border-radius: 12px;
    background: rgba(124, 58, 237, 0.08);
    border: 1px solid var(--doc-line-2);
    color: var(--doc-ink);
    font-size: 0.86rem;
    line-height: 1.6;
}

/* ============================================================
   2. 提出状況サマリー
   ============================================================ */
.doc-summary {
    background: var(--doc-surface);
    border: 1px solid var(--doc-line);
    border-radius: var(--doc-radius);
    padding: 16px;
    margin-bottom: 20px;
    box-shadow: var(--doc-shadow);
}
.doc-summary__title {
    margin: 0 0 12px;
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--doc-muted);
    letter-spacing: 0.12em;
    text-transform: uppercase;
}
.doc-summary__counts {
    display: flex; flex-wrap: wrap; gap: 6px;
    margin-bottom: 14px;
}
.doc-summary__count {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.74rem; font-weight: 700;
    background: #f4f0fb;
    color: var(--doc-body);
    border: 1px solid var(--doc-line);
}
.doc-summary__count i { font-size: 0.7rem; }
.doc-summary__count.is-approved { background: rgba(5, 150, 105, 0.08); color: #059669; border-color: rgba(5, 150, 105, 0.28); }
.doc-summary__count.is-pending  { background: rgba(180, 83, 9, 0.08); color: #b45309; border-color: rgba(180, 83, 9, 0.28); }
.doc-summary__count.is-rejected { background: rgba(220, 38, 38, 0.06); color: #dc2626; border-color: rgba(220, 38, 38, 0.30); }

.doc-summary__list {
    list-style: none; margin: 0; padding: 0;
    display: flex; flex-direction: column;
}
.doc-summary__row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 0;
}
.doc-summary__row + .doc-summary__row { border-top: 1px solid var(--doc-line); }
.doc-summary__row-icon {
    flex: 0 0 auto;
    width: 28px; height: 28px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.82rem;
    background: #f4f0fb; color: var(--doc-muted);
}
.doc-summary__row.is-approved .doc-summary__row-icon { background: rgba(5, 150, 105, 0.10); color: #059669; }
.doc-summary__row.is-pending  .doc-summary__row-icon { background: rgba(180, 83, 9, 0.10); color: #b45309; }
.doc-summary__row.is-rejected .doc-summary__row-icon { background: rgba(220, 38, 38, 0.08); color: #dc2626; }
.doc-summary__row-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.doc-summary__row-name {
    font-size: 0.90rem; font-weight: 700; color: var(--doc-ink);
    letter-spacing: -0.005em;
}
.doc-summary__row-name .doc-summary__pattern {
    margin-left: 6px;
    font-size: 0.66rem; font-weight: 700;
    color: var(--doc-muted);
    letter-spacing: 0.06em;
}
.doc-summary__row-detail {
    font-size: 0.76rem;
    color: var(--doc-body);
    line-height: 1.5;
    overflow-wrap: anywhere;
}
.doc-summary__row-detail strong { color: var(--doc-ink); font-weight: 700; }
.doc-summary__row-status {
    flex: 0 0 auto;
    font-size: 0.74rem; font-weight: 700;
    color: var(--doc-muted);
    white-space: nowrap;
}
.doc-summary__row.is-approved .doc-summary__row-status { color: #059669; }
.doc-summary__row.is-pending  .doc-summary__row-status { color: #b45309; }
.doc-summary__row.is-rejected .doc-summary__row-status { color: #dc2626; }

/* ============================================================
   3. 承認催促
   ============================================================ */
.identity-remind {
    margin: 0 0 20px;
    display: flex; flex-direction: column; gap: 8px;
}
.identity-remind__btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%;
    min-height: 48px;
    padding: 12px 16px;
    border-radius: 12px;
    border: 1px solid var(--doc-line-2);
    background: rgba(124, 58, 237, 0.06);
    color: var(--doc-accent);
    font-size: 0.90rem; font-weight: 800;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.12s ease;
}
.identity-remind__btn:active { transform: scale(0.98); }
.identity-remind__note {
    margin: 0; text-align: center;
    font-size: 0.74rem; color: var(--doc-muted);
}
.identity-remind__done {
    margin: 0; padding: 12px 14px; text-align: center;
    border-radius: 12px;
    border: 1px dashed var(--doc-line-2);
    color: var(--doc-body);
    font-size: 0.82rem;
}
.identity-remind__done i { color: var(--doc-accent); margin-right: 4px; }

/* ============================================================
   4. パターン切替タブ
   ============================================================ */
.identity-pattern-tabs {
    display: flex;
    gap: 4px;
    padding: 4px;
    background: #ece7f7;
    border-radius: 12px;
    margin-bottom: 12px;
}
.identity-pattern-tab {
    flex: 1;
    padding: 12px 10px;
    border-radius: 9px;
    background: transparent; border: 0;
    color: var(--doc-body);
    font-size: 0.82rem; font-weight: 700;
    line-height: 1.35;
    cursor: pointer;
    text-align: center;
    transition: background 0.15s ease, color 0.15s ease;
}
.identity-pattern-tab.is-active {
    background: var(--doc-accent);
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(124, 58, 237, 0.30);
}
.identity-pattern-tab:not(.is-active):hover { background: rgba(124, 58, 237, 0.08); color: var(--doc-ink); }

.identity-pattern-help {
    margin: 0 0 16px;
    padding: 12px 14px;
    font-size: 0.82rem;
    line-height: 1.7;
    color: var(--doc-body);
    background: rgba(124, 58, 237, 0.05);
    border-left: 3px solid var(--doc-accent-2);
    border-radius: 0 10px 10px 0;
}
.identity-pattern-help strong { color: var(--doc-ink); font-weight: 700; }

/* ============================================================
   5. 書類カード（doc-card / doc-form）— 各書類の入力領域
   ============================================================ */
.doc-card {
    background: var(--doc-surface);
    border: 1px solid var(--doc-line);
    border-radius: var(--doc-radius);
    padding: 18px;
    margin-bottom: 14px;
    box-shadow: var(--doc-shadow);
}
.doc-card__head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    margin-bottom: 14px;
}
.doc-card__title {
    margin: 0;
    font-size: 1rem; font-weight: 800;
    color: var(--doc-ink);
    letter-spacing: -0.005em;
}
.doc-card__pill {
    flex: 0 0 auto;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.7rem; font-weight: 700; letter-spacing: 0.02em;
    background: #f4f0fb;
    color: var(--doc-muted);
    border: 1px solid var(--doc-line);
}
.doc-card__pill.is-approved { background: rgba(5, 150, 105, 0.10); color: #059669; border-color: rgba(5, 150, 105, 0.30); }
.doc-card__pill.is-pending  { background: rgba(180, 83, 9, 0.10); color: #b45309; border-color: rgba(180, 83, 9, 0.30); }
.doc-card__pill.is-rejected { background: rgba(220, 38, 38, 0.08); color: #dc2626; border-color: rgba(220, 38, 38, 0.32); }

.doc-card__ng {
    margin: 0 0 12px;
    padding: 10px 12px;
    border-radius: 10px;
    background: rgba(220, 38, 38, 0.06);
    border: 1px solid rgba(220, 38, 38, 0.30);
    color: #b91c1c;
    font-size: 0.82rem;
    line-height: 1.55;
    display: flex; align-items: flex-start; gap: 6px;
}
.doc-card__ng i { margin-top: 2px; }

.doc-card__current {
    margin: 0 0 14px;
    padding: 10px 12px;
    border-radius: 10px;
    background: #faf7ff;
    border: 1px dashed var(--doc-line-2);
    font-size: 0.82rem;
    color: var(--doc-body);
    display: flex; flex-wrap: wrap; align-items: center; gap: 4px 8px;
}
.doc-card__current-label {
    font-size: 0.68rem; font-weight: 800; letter-spacing: 0.06em;
    color: var(--doc-muted);
    text-transform: uppercase;
    margin-right: 2px;
}
.doc-card__current strong { color: var(--doc-ink); font-weight: 700; }
.doc-card__current-meta {
    margin-left: auto;
    font-size: 0.72rem; color: var(--doc-muted);
}

/* ---------- フォーム内部（縦積み・統一） ---------- */
.doc-form { display: flex; flex-direction: column; gap: 14px; }
.doc-form__field { display: flex; flex-direction: column; gap: 6px; }
.doc-form__label {
    font-size: 0.80rem; font-weight: 700; color: var(--doc-ink);
    letter-spacing: 0.02em;
    display: inline-flex; align-items: center; gap: 6px;
}
.doc-form__req {
    display: inline-flex; align-items: center;
    padding: 1px 7px;
    border-radius: 999px;
    background: rgba(220, 38, 38, 0.10);
    color: #dc2626;
    font-size: 0.64rem; font-weight: 800;
    letter-spacing: 0.04em;
}
.doc-form__req.is-optional {
    background: #f4f0fb;
    color: var(--doc-muted);
}

.doc-form__select,
.doc-form__input {
    width: 100%;
    min-height: 44px;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid var(--doc-line-2);
    background: #ffffff;
    color: var(--doc-ink);
    font-size: 0.90rem;
    font-family: inherit;
    box-sizing: border-box;
    color-scheme: light;
}
.doc-form__select:focus,
.doc-form__input:focus {
    outline: none;
    border-color: var(--doc-accent);
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
}

/* ドロップボタン：全幅・大きく・選択時は色替え */
.doc-form__drop {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 14px 16px;
    border: 1.5px dashed var(--doc-line-2);
    border-radius: 12px;
    background: #faf7ff;
    color: var(--doc-body);
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.doc-form__drop:hover {
    background: rgba(124, 58, 237, 0.06);
    border-color: var(--doc-accent-2);
}
.doc-form__drop-icon {
    flex: 0 0 auto;
    width: 40px; height: 40px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 50%;
    background: rgba(124, 58, 237, 0.10);
    color: var(--doc-accent);
    font-size: 1.05rem;
}
.doc-form__drop-text {
    flex: 1; min-width: 0;
    display: flex; flex-direction: column; gap: 2px;
    text-align: left;
    line-height: 1.4;
    overflow: hidden;
}
.doc-form__drop-name {
    font-size: 0.88rem; font-weight: 700;
    color: var(--doc-ink);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.doc-form__drop-text small {
    font-size: 0.70rem; font-weight: 500; color: var(--doc-muted);
}
/* 選択済み状態 */
.doc-form__drop.is-selected {
    border-style: solid;
    border-color: rgba(16, 185, 129, 0.45);
    background: rgba(16, 185, 129, 0.06);
}
.doc-form__drop.is-selected .doc-form__drop-icon {
    background: rgba(16, 185, 129, 0.14);
    color: #059669;
}
.doc-form__drop.is-selected .doc-form__drop-name { color: #065f46; }

.doc-form__preview {
    display: block;
    max-width: 100%;
    max-height: 200px;
    margin-top: 6px;
    border-radius: 10px;
    border: 1px solid var(--doc-line);
    background: #ffffff;
    object-fit: contain;
    box-shadow: 0 2px 8px rgba(76, 29, 149, 0.08);
}
.doc-form__preview[hidden] { display: none; }
.doc-form__pdf-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 10px;
    margin-top: 4px;
    border-radius: 8px;
    background: rgba(220, 38, 38, 0.06);
    border: 1px solid rgba(220, 38, 38, 0.30);
    color: #b91c1c;
    font-size: 0.76rem; font-weight: 700;
    max-width: 100%; overflow: hidden;
}
.doc-form__pdf-chip[hidden] { display: none; }
.doc-form__pdf-chip span {
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* 送信ボタン（本ページ内の Primary CTA レシピ） */
.doc-form__submit {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%;
    min-height: 50px;
    margin-top: 4px;
    padding: 12px 16px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--doc-accent-2), var(--doc-accent));
    color: #ffffff;
    font-size: 0.95rem; font-weight: 800;
    letter-spacing: 0.02em;
    cursor: pointer;
    box-shadow:
        0 6px 14px rgba(124, 58, 237, 0.30),
        inset 0 1px 0 rgba(255, 255, 255, 0.25);
    transition: transform 0.12s ease;
}
.doc-form__submit:active { transform: scale(0.98); }
.doc-form__submit:disabled { opacity: 0.65; cursor: progress; }

/* インラインメッセージ */
.cast-identity-error {
    margin: 0;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid rgba(220, 38, 38, 0.35);
    background: rgba(220, 38, 38, 0.05);
    color: #b91c1c;
    font-size: 0.82rem;
    line-height: 1.5;
    display: flex; align-items: flex-start; gap: 6px;
}
.cast-identity-error::before { content: "⚠"; flex-shrink: 0; }
.cast-identity-error[hidden] { display: none; }
.cast-identity-success {
    margin: 0;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid rgba(16, 185, 129, 0.35);
    background: rgba(16, 185, 129, 0.06);
    color: #047857;
    font-size: 0.82rem;
    line-height: 1.5;
}
.cast-identity-success[hidden] { display: none; }
</style>
@endpush

@section('content')
@php
    $allowedTypes = $allowedTypes ?? [
        'photo_id' => ['driver_license', 'passport', 'mynumber_card', 'residence_card'],
        'non_photo_id' => ['health_insurance', 'pension_book'],
        'address_proof' => ['residence_certificate', 'utility_bill'],
    ];
    $typeLabels = $typeLabels ?? [];
    $categoryDocs = $categoryDocuments ?? ['photo_id' => null, 'non_photo_id' => null, 'address_proof' => null];
    $detectedPattern = $detectedPattern ?? 'photo';
    $isVerified = $isVerified ?? false;
    $isPendingReview = !$isVerified && ($identityStatus ?? '') === 'pending';

    $summaryRows = [
        ['label' => '顔写真付き身分証', 'pattern' => 'A', 'doc' => $categoryDocs['photo_id'] ?? null],
        ['label' => '顔写真なし身分証', 'pattern' => 'B', 'doc' => $categoryDocs['non_photo_id'] ?? null],
        ['label' => '住所確認書類',       'pattern' => 'B', 'doc' => $categoryDocs['address_proof'] ?? null],
    ];
    $idApproved = 0; $idPending = 0; $idRejected = 0; $idNone = 0;
    foreach ($summaryRows as $r) {
        $sk = $r['doc']['status_key'] ?? null;
        if (!$r['doc']) { $idNone++; }
        elseif ($sk === 'approved') { $idApproved++; }
        elseif ($sk === 'rejected') { $idRejected++; }
        else { $idPending++; }
    }
@endphp
<div class="content-wrapper animate-fadeIn">
    <div class="cast-mypage-sub-page">
        <section class="mypage-area">
            <div class="mypage-detail-box">
                <div class="mypage-section">

                    @if(session('status'))
                        <p class="identity-flash" role="status">{{ session('status') }}</p>
                    @endif

                    {{-- 1. ヒーロー：全体状況 --}}
                    <div class="identity-hero {{ $isVerified ? 'is-verified' : ($isPendingReview ? 'is-pending' : '') }}">
                        <span class="identity-hero__icon" aria-hidden="true">
                            <i class="fas {{ $isVerified ? 'fa-circle-check' : ($isPendingReview ? 'fa-hourglass-half' : 'fa-clock') }}"></i>
                        </span>
                        <div class="identity-hero__body">
                            @if($isVerified)
                                <p class="identity-hero__title">本人確認 完了</p>
                                <p class="identity-hero__desc">すべての書類が承認されています。</p>
                            @elseif($isPendingReview)
                                <p class="identity-hero__title">審査中です</p>
                                <p class="identity-hero__desc">運営が内容を確認しています（通常 1〜2 営業日）。承認まで一部機能が制限されます。</p>
                            @else
                                <p class="identity-hero__title">本人確認 未完了</p>
                                <p class="identity-hero__desc">下記のいずれかのパターンで書類を提出してください。</p>
                            @endif
                        </div>
                    </div>

                    {{-- 2. 提出状況サマリー --}}
                    <section class="doc-summary" aria-label="提出状況サマリー">
                        <p class="doc-summary__title">提出状況</p>
                        <div class="doc-summary__counts">
                            <span class="doc-summary__count is-approved"><i class="fas fa-circle-check"></i>承認 {{ $idApproved }}</span>
                            <span class="doc-summary__count is-pending"><i class="fas fa-hourglass-half"></i>審査中 {{ $idPending }}</span>
                            @if($idRejected > 0)
                                <span class="doc-summary__count is-rejected"><i class="fas fa-circle-exclamation"></i>差戻し {{ $idRejected }}</span>
                            @endif
                            <span class="doc-summary__count"><i class="fas fa-minus"></i>未提出 {{ $idNone }}</span>
                        </div>
                        <ul class="doc-summary__list">
                            @foreach($summaryRows as $r)
                                @php
                                    $d = $r['doc'];
                                    $sk = $d['status_key'] ?? null;
                                    $rowState = $d ? ($sk ?? 'pending') : 'not-submitted';
                                @endphp
                                <li class="doc-summary__row is-{{ $rowState }}">
                                    <span class="doc-summary__row-icon" aria-hidden="true">
                                        <i class="fas {{ $d ? ($sk === 'approved' ? 'fa-circle-check' : ($sk === 'rejected' ? 'fa-circle-exclamation' : 'fa-hourglass-half')) : 'fa-minus' }}"></i>
                                    </span>
                                    <span class="doc-summary__row-body">
                                        <span class="doc-summary__row-name">
                                            {{ $r['label'] }}<span class="doc-summary__pattern">パターン{{ $r['pattern'] }}</span>
                                        </span>
                                        <span class="doc-summary__row-detail">
                                            @if($d)
                                                <strong>{{ $d['type_label'] ?? '書類' }}</strong>
                                                @if(!empty($d['updated_at_label']))・{{ $d['updated_at_label'] }}提出@endif
                                            @else
                                                まだ提出されていません
                                            @endif
                                        </span>
                                    </span>
                                    <span class="doc-summary__row-status">{{ $d ? ($d['status_label'] ?? '審査中') : '未提出' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    {{-- 3. 審査催促 --}}
                    @if($isPendingReview)
                        <div class="identity-remind">
                            @if(!empty($identityRemindSentRecently))
                                <p class="identity-remind__done">
                                    <i class="fas fa-paper-plane"></i> 承認の催促を送信済みです（24時間に1回まで）
                                </p>
                            @else
                                <form method="POST" action="{{ route('cast.mypage.identity.remind') }}"
                                      onsubmit="return confirm('運営へ本人確認の承認催促を送信します。よろしいですか？');">
                                    @csrf
                                    <button type="submit" class="identity-remind__btn">
                                        <i class="fas fa-paper-plane"></i> 運営に承認を催促する
                                    </button>
                                </form>
                                <p class="identity-remind__note">審査が長引いている場合、運営へ確認の連絡を送れます。</p>
                            @endif
                        </div>
                    @endif

                    {{-- 4. パターン切替タブ --}}
                    <div class="identity-pattern-tabs" role="tablist">
                        <button type="button" class="identity-pattern-tab {{ $detectedPattern === 'photo' ? 'is-active' : '' }}" data-pattern="photo">
                            パターンA<br>顔写真付き身分証 1枚
                        </button>
                        <button type="button" class="identity-pattern-tab {{ $detectedPattern === 'non_photo' ? 'is-active' : '' }}" data-pattern="non_photo">
                            パターンB<br>顔写真なし＋住所確認
                        </button>
                    </div>

                    {{-- 5. パターンA --}}
                    <div class="identity-pattern-pane" data-pattern-pane="photo" @if($detectedPattern !== 'photo') hidden @endif>
                        <p class="identity-pattern-help">
                            <strong>運転免許証 / パスポート / マイナンバーカード / 在留カード</strong> のいずれか 1 点をアップロードしてください。両面ある書類は表・裏の両方を提出してください。
                        </p>

                        @include('casts.mypage._identity_form', [
                            'category'    => 'photo_id',
                            'sectionTitle'=> '顔写真付き身分証',
                            'currentDoc'  => $categoryDocs['photo_id'] ?? null,
                            'allowedTypes'=> $allowedTypes['photo_id'],
                            'typeLabels'  => $typeLabels,
                            'showExpiry'  => true,
                            'requireBack' => false,
                        ])
                    </div>

                    {{-- 6. パターンB --}}
                    <div class="identity-pattern-pane" data-pattern-pane="non_photo" @if($detectedPattern !== 'non_photo') hidden @endif>
                        <p class="identity-pattern-help">
                            <strong>顔写真なし身分証（健康保険証 など）</strong>と<strong>住所確認書類（住民票・公共料金領収書 など）</strong>の <strong>両方</strong> をアップロードしてください。両方が承認されて本人確認完了となります。
                        </p>

                        @include('casts.mypage._identity_form', [
                            'category'    => 'non_photo_id',
                            'sectionTitle'=> '① 顔写真なし身分証',
                            'currentDoc'  => $categoryDocs['non_photo_id'] ?? null,
                            'allowedTypes'=> $allowedTypes['non_photo_id'],
                            'typeLabels'  => $typeLabels,
                            'showExpiry'  => false,
                            'requireBack' => false,
                        ])

                        @include('casts.mypage._identity_form', [
                            'category'    => 'address_proof',
                            'sectionTitle'=> '② 住所確認書類',
                            'currentDoc'  => $categoryDocs['address_proof'] ?? null,
                            'allowedTypes'=> $allowedTypes['address_proof'],
                            'typeLabels'  => $typeLabels,
                            'showExpiry'  => false,
                            'requireBack' => false,
                        ])
                    </div>

                    <p id="cast-identity-message" style="display:none; margin-top:8px;"></p>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // パターン切替
    var tabs = document.querySelectorAll('.identity-pattern-tab');
    var panes = document.querySelectorAll('[data-pattern-pane]');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-pattern');
            tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            panes.forEach(function (p) {
                p.hidden = p.getAttribute('data-pattern-pane') !== key;
            });
        });
    });

    // 各カテゴリのフォーム送信（インラインエラー + 成功表示）
    document.querySelectorAll('form.cast-identity-form').forEach(function (form) {
        var errorEl = form.querySelector('.cast-identity-error');
        var successEl = form.querySelector('.cast-identity-success');
        var showError = function (text) {
            if (!errorEl) return;
            errorEl.textContent = text;
            errorEl.hidden = false;
            if (successEl) successEl.hidden = true;
        };
        var showSuccess = function (text) {
            if (!successEl) return;
            successEl.textContent = text;
            successEl.hidden = false;
            if (errorEl) errorEl.hidden = true;
        };
        var clearMessages = function () {
            if (errorEl) errorEl.hidden = true;
            if (successEl) successEl.hidden = true;
        };
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearMessages();
            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            var formData = new FormData(form);
            fetch('{{ route("cast.mypage.identity.upload") }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            }).then(function (r) {
                return r.json().then(function (json) {
                    if (!r.ok) throw json;
                    return json;
                });
            })
            .then(function (res) {
                showSuccess(res && res.message ? res.message : 'アップロードしました。');
                setTimeout(function () { window.location.reload(); }, 600);
            }).catch(function (error) {
                if (submitBtn) submitBtn.disabled = false;
                var messages = error && error.errors ? Object.values(error.errors).flat() : [];
                showError(messages[0] || (error && error.message) || 'アップロードに失敗しました。時間をおいて再度お試しください。');
            });
        });
    });

    /* ファイル選択：ドロップボタンにファイル名を反映 + プレビュー / PDF チップ */
    document.querySelectorAll('input[type="file"].bank-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var nameEl  = document.getElementById(input.id + '_name');
            var preview = document.getElementById(input.id + '_preview');
            var pdfChip = document.getElementById(input.id + '_pdf');
            // ドロップボタン（input の直前の label）
            var drop = document.querySelector('label[for="' + input.id + '"]');
            var file = input.files && input.files[0];

            // プレビュー
            if (preview) {
                if (preview.dataset.blobUrl) {
                    URL.revokeObjectURL(preview.dataset.blobUrl);
                    delete preview.dataset.blobUrl;
                }
                if (file && /^image\//.test(file.type)) {
                    var blobUrl = URL.createObjectURL(file);
                    preview.src = blobUrl;
                    preview.dataset.blobUrl = blobUrl;
                    preview.hidden = false;
                } else {
                    preview.hidden = true;
                    preview.removeAttribute('src');
                }
            }
            if (pdfChip) {
                var isPdf = file && (file.type === 'application/pdf' || /\.pdf$/i.test(file.name));
                pdfChip.hidden = !isPdf;
                var nameSpan = pdfChip.querySelector('span');
                if (nameSpan) nameSpan.textContent = isPdf ? file.name : '';
            }

            if (file) {
                if (nameEl) nameEl.textContent = file.name;
                if (drop) drop.classList.add('is-selected');
            } else {
                if (nameEl) nameEl.textContent = 'タップしてファイルを選択';
                if (drop) drop.classList.remove('is-selected');
            }
        });
    });
});
</script>
@endpush
