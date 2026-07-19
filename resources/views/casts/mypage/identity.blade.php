@extends('layouts.app-v2')

@section('title', '本人確認')
@section('body-class', 'page-cast-mypage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<style>
.identity-pattern-tabs {
    display: flex;
    gap: 6px;
    padding: 6px;
    background: #ece7f7;
    border-radius: 12px;
    margin: 12px 0 16px;
}
.identity-pattern-tab {
    flex: 1;
    padding: 10px 12px;
    border-radius: 8px;
    background: transparent;
    border: 0;
    color: #6d6685;
    font-weight: 700;
    font-size: 0.86rem;
    cursor: pointer;
    text-align: center;
}
.identity-pattern-tab.is-active {
    background: var(--accent, #d670a2);
    color: var(--on-accent, #1a0814);
    box-shadow: 0 4px 12px rgba(0, 0, 0, .45), inset 0 1px 0 rgba(255, 255, 255, .20), inset 0 -1px 0 rgba(0, 0, 0, .18);
}
.identity-pattern-tab:not(.is-active):hover { background: rgba(124,58,237,.08); color: #241f33; }
.identity-pattern-help {
    font-size: 0.78rem;
    color: #5f5876;
    line-height: 1.7;
    margin: 0 0 12px;
    padding: 10px 12px;
    border-left: 2px solid rgba(168, 85, 247, .55);
    background: rgba(168, 85, 247, .04);
    border-radius: 0 6px 6px 0;
}
.identity-form-section {
    border: 1px solid rgba(168, 85, 247, .18);
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 14px;
    background: #ffffff;
}
.identity-form-section__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 10px;
}
.identity-form-section__title {
    font-size: 0.92rem;
    font-weight: 800;
    color: #6d28d9;
}
.identity-form-section__pill {
    font-size: 0.7rem;
    padding: 3px 8px;
    border-radius: 999px;
    background: #f5f2fb;
    color: #6d6685;
    font-weight: 700;
}
.identity-form-section__pill.is-approved { background: rgba(5,150,105,.10); color: #047857; }
.identity-form-section__pill.is-pending  { background: rgba(180,83,9,.10);  color: #b45309; }
.identity-form-section__pill.is-rejected { background: rgba(220,38,38,.08);  color: #dc2626; }
/* フラッシュメッセージ */
.identity-flash {
    margin: 0 0 14px;
    padding: 11px 14px;
    border-radius: 12px;
    background: rgba(var(--accent-rgb, 139, 92, 246), 0.12);
    border: 1px solid rgba(var(--accent-rgb, 139, 92, 246), 0.4);
    color: var(--color-text-main, #f5f5f5);
    font-size: 0.84rem;
    line-height: 1.6;
}

/* 審査中バリアント（warn トーン） */
.identity-status-overall.is-pending-review {
    background: linear-gradient(180deg, rgba(252, 211, 77, 0.10), rgba(252, 211, 77, 0.02));
    border-color: rgba(252, 211, 77, 0.45);
}
.identity-status-overall.is-pending-review i {
    color: var(--color-warn, #fcd34d);
    filter: drop-shadow(0 2px 8px rgba(252, 211, 77, 0.3));
}

/* 承認催促 */
.identity-remind {
    margin: -8px 0 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.identity-remind__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 16px;
    border-radius: 999px;
    border: 1px solid rgba(var(--accent-rgb, 139, 92, 246), 0.5);
    background: rgba(var(--accent-rgb, 139, 92, 246), 0.12);
    color: var(--accent-text, #a78bfa);
    font-size: 0.88rem;
    font-weight: 800;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, transform 0.12s ease;
}
.identity-remind__btn:active { transform: scale(0.98); }
.identity-remind__note {
    margin: 0;
    font-size: 0.72rem;
    color: var(--color-text-sub, #b8b8b8);
    text-align: center;
}
.identity-remind__done {
    margin: 0;
    padding: 11px 14px;
    border-radius: 12px;
    border: 1px dashed rgba(var(--accent-rgb, 139, 92, 246), 0.4);
    color: var(--color-text-sub, #b8b8b8);
    font-size: 0.8rem;
    text-align: center;
}
.identity-remind__done i { color: var(--accent-text, #a78bfa); margin-right: 4px; }

/* === 本人確認ステータス：ページのヒーローカード === */
.identity-status-overall {
    padding: 18px 20px 20px;
    border-radius: 18px;
    border: 1px solid rgba(168, 85, 247, .35);
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 14px;
    background:
        linear-gradient(180deg, rgba(168, 85, 247, 0.12), rgba(168, 85, 247, 0.03));
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    box-shadow:
        0 4px 16px rgba(0, 0, 0, 0.25),
        inset 0 1px 0 rgba(255, 255, 255, 0.06);
}
.identity-status-overall.is-verified {
    background:
        linear-gradient(180deg, rgba(16, 185, 129, 0.14), rgba(16, 185, 129, 0.03));
    border-color: rgba(16, 185, 129, .50);
    box-shadow:
        0 4px 16px rgba(16, 185, 129, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.06);
}
/* アイコン：丸枠なしのフラット、サイズ大きく */
.identity-status-overall i {
    flex-shrink: 0;
    font-size: 2.1rem;
    color: #7c3aed;
    filter: drop-shadow(0 2px 8px rgba(168, 85, 247, 0.35));
}
.identity-status-overall.is-verified i {
    color: #059669;
    filter: drop-shadow(0 2px 8px rgba(16, 185, 129, 0.45));
}
/* メインテキスト：大きく */
.identity-status-overall__text {
    color: #241f33;
    font-weight: 800;
    font-size: 1.05rem;
    line-height: 1.25;
    letter-spacing: -0.01em;
}
.identity-status-overall__text small {
    display: block;
    color: #5f5876;
    font-weight: 500;
    font-size: 0.76rem;
    margin-top: 4px;
    letter-spacing: 0;
    line-height: 1.5;
}

/* === C-1: 本人確認 UX polish === */
/* ファイル選択ボタン：ファイル名が長くても崩れない flex row */
.identity-form-section .bank-form-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 10px;
    margin-bottom: 12px;
}
.identity-form-section .bank-form-row .bank-label {
    flex-basis: 100%;
    font-size: 0.78rem;
    color: #5f5876;
    margin-bottom: 2px;
}
/* ファイル選択ボタン：mauve outline、選択済みは緑 */
.identity-form-section .file-upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    font-size: 0.82rem;
    font-weight: 600;
    border-radius: 999px;
    border: 1px solid rgba(124, 58, 237, 0.40);
    color: #2d2742;
    background: rgba(124, 58, 237, 0.05);
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.identity-form-section .file-upload-btn:hover {
    background: rgba(168, 85, 247, 0.14);
    border-color: rgba(168, 85, 247, 0.65);
}
.identity-form-section .file-upload-btn.is-selected {
    color: #047857;
    border-color: rgba(110, 231, 183, 0.55);
    background: rgba(16, 185, 129, 0.10);
}
.identity-form-section .file-upload-btn.is-selected i::before {
    content: "\f00c";  /* fa-check */
}
.identity-form-section .file-name-display {
    flex: 1 1 auto;
    min-width: 0;
    font-size: 0.78rem;
    color: #8b84a1;
    word-break: break-all;
}
.identity-form-section .file-name-display.is-set {
    color: #2d2742;
}
/* インラインエラー（alert の置き換え）*/
.cast-identity-error {
    margin: 10px 0 0;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid rgba(248, 113, 113, 0.45);
    background: rgba(220, 38, 38, 0.06);
    color: #b91c1c;
    font-size: 0.82rem;
    line-height: 1.5;
    display: flex;
    align-items: flex-start;
    gap: 6px;
}
.cast-identity-error::before {
    content: "⚠";
    flex-shrink: 0;
    color: #fca5a5;
}
.cast-identity-error[hidden] { display: none; }
/* 成功メッセージ */
.cast-identity-success {
    margin: 10px 0 0;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid rgba(110, 231, 183, 0.45);
    background: rgba(5, 150, 105, 0.08);
    color: #047857;
    font-size: 0.82rem;
    line-height: 1.5;
}
.cast-identity-success[hidden] { display: none; }

/* === 提出状況サマリー（何を提出したかを一覧で明示） === */
.identity-doc-summary {
    background: #ffffff;
    border: 1px solid rgba(124, 58, 237, 0.20);
    border-radius: 14px;
    padding: 12px 14px;
    margin: 0 0 18px;
    box-shadow: 0 6px 18px rgba(76, 29, 149, 0.08);
}
.identity-doc-summary__title {
    margin: 0 0 8px;
    font-size: 0.82rem;
    font-weight: 800;
    color: #6d28d9;
}
.identity-doc-summary__title i { margin-right: 4px; }
.identity-doc-summary__list { list-style: none; margin: 0; padding: 0; }
.identity-doc-summary__row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 2px;
}
.identity-doc-summary__row + .identity-doc-summary__row { border-top: 1px solid rgba(124, 58, 237, 0.10); }
.identity-doc-summary__icon {
    flex: 0 0 auto;
    width: 28px; height: 28px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    background: #f5f2fb; color: #8b84a1;
}
.identity-doc-summary__row.is-approved .identity-doc-summary__icon { background: rgba(5,150,105,0.10); color: #059669; }
.identity-doc-summary__row.is-pending  .identity-doc-summary__icon { background: rgba(180,83,9,0.10); color: #b45309; }
.identity-doc-summary__row.is-rejected .identity-doc-summary__icon { background: rgba(220,38,38,0.08); color: #dc2626; }
.identity-doc-summary__body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 1px; }
.identity-doc-summary__label { font-size: 0.84rem; font-weight: 800; color: #241f33; }
.identity-doc-summary__label small {
    margin-left: 6px;
    font-size: 0.66rem;
    font-weight: 700;
    color: #8b84a1;
}
.identity-doc-summary__detail { font-size: 0.72rem; color: #6d6685; overflow-wrap: anywhere; }
.identity-doc-summary__detail strong { color: #2d2742; }
.identity-doc-summary__status { flex: 0 0 auto; font-size: 0.72rem; font-weight: 800; color: #8b84a1; white-space: nowrap; }
.identity-doc-summary__row.is-approved .identity-doc-summary__status { color: #059669; }
.identity-doc-summary__row.is-pending  .identity-doc-summary__status { color: #b45309; }
.identity-doc-summary__row.is-rejected .identity-doc-summary__status { color: #dc2626; }

/* === ライトモード：mypage.css のダーク面を上書き === */
.cast-mypage-sub-page .mypage-detail-box {
    background: #ffffff !important;
    border: 1px solid rgba(124, 58, 237, 0.18) !important;
    box-shadow: 0 6px 18px rgba(76, 29, 149, 0.08) !important;
}
.cast-mypage-sub-page .mypage-page-head__title { color: #241f33 !important; }
.cast-mypage-sub-page .mypage-page-head__title i { color: #7c3aed !important; }
.cast-mypage-sub-page .mypage-page-head__desc { color: #5f5876 !important; }
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
@endphp
<div class="content-wrapper animate-fadeIn">
    <div class="cast-mypage-sub-page">
        <section class="mypage-area">
            <header class="mypage-page-head">
                <h1 class="mypage-page-head__title"><i class="fas fa-id-card"></i>本人確認（書類の提出）</h1>
                <p class="mypage-page-head__desc">安心してご利用いただくため、本人確認書類の提出をお願いしています。運営の承認が完了すると、すべての機能が利用できます。</p>
            </header>

            <div class="mypage-detail-box">
                <div class="mypage-section">
                    {{-- h2 "本人確認の状況" は eyebrow と status hero と triple-redundant なので撤去 --}}

                    @if(session('status'))
                        <p class="identity-flash" role="status">{{ session('status') }}</p>
                    @endif

                    {{-- 全体ステータス（このカードがページの "hero" として機能する）
                         完了 / 審査中 / 未提出・差戻し の3状態を明確に分ける --}}
                    @php $isPendingReview = !$isVerified && ($identityStatus ?? '') === 'pending'; @endphp
                    <div class="identity-status-overall {{ $isVerified ? 'is-verified' : '' }} {{ $isPendingReview ? 'is-pending-review' : '' }}">
                        <i class="fas {{ $isVerified ? 'fa-circle-check' : ($isPendingReview ? 'fa-hourglass-half' : 'fa-clock') }}"></i>
                        <div class="identity-status-overall__text">
                            @if($isVerified)
                                本人確認 完了
                                <small>すべての書類が承認されています。</small>
                            @elseif($isPendingReview)
                                審査中です
                                <small>書類は提出済みです。運営が内容を確認しています（通常1〜2営業日）。承認されるまで一部機能が制限されます。</small>
                            @else
                                本人確認 未完了
                                <small>下記のいずれかのパターンで書類を提出してください。</small>
                            @endif
                        </div>
                    </div>

                    {{-- 提出状況サマリー：どの書類を提出済みか（種類・提出日・状態）を一覧で明示 --}}
                    @php
                        $summaryRows = [
                            ['label' => '顔写真付き身分証', 'pattern' => 'A', 'doc' => $categoryDocs['photo_id'] ?? null],
                            ['label' => '顔写真なし身分証', 'pattern' => 'B', 'doc' => $categoryDocs['non_photo_id'] ?? null],
                            ['label' => '住所確認書類', 'pattern' => 'B', 'doc' => $categoryDocs['address_proof'] ?? null],
                        ];
                    @endphp
                    <div class="identity-doc-summary" aria-label="提出状況">
                        <p class="identity-doc-summary__title"><i class="fas fa-list-check" aria-hidden="true"></i>提出状況</p>
                        <ul class="identity-doc-summary__list">
                            @foreach($summaryRows as $r)
                                @php
                                    $d = $r['doc'];
                                    $sk = $d['status_key'] ?? null;
                                    $rowState = $d ? ($sk ?? 'pending') : 'none';
                                @endphp
                                <li class="identity-doc-summary__row is-{{ $rowState }}">
                                    <span class="identity-doc-summary__icon" aria-hidden="true">
                                        <i class="fas {{ $d ? ($sk === 'approved' ? 'fa-circle-check' : ($sk === 'rejected' ? 'fa-circle-exclamation' : 'fa-hourglass-half')) : 'fa-minus' }}"></i>
                                    </span>
                                    <span class="identity-doc-summary__body">
                                        <span class="identity-doc-summary__label">{{ $r['label'] }}<small>パターン{{ $r['pattern'] }}</small></span>
                                        <span class="identity-doc-summary__detail">
                                            @if($d)
                                                提出書類: <strong>{{ $d['type_label'] ?? '書類' }}</strong>
                                                @if(!empty($d['updated_at_label']))（{{ $d['updated_at_label'] }} 提出）@endif
                                            @else
                                                まだ提出されていません
                                            @endif
                                        </span>
                                    </span>
                                    <span class="identity-doc-summary__status">{{ $d ? ($d['status_label'] ?? '審査中') : '未提出' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- 審査中：運営へ承認を催促できる（24時間に1回まで） --}}
                    @if($isPendingReview)
                        <div class="identity-remind">
                            @if(!empty($identityRemindSentRecently))
                                <p class="identity-remind__done">
                                    <i class="fas fa-paper-plane"></i> 承認の催促を送信済みです（24時間に1回まで送信できます）
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

                    {{-- パターン切替タブ --}}
                    <div class="identity-pattern-tabs" role="tablist">
                        <button type="button" class="identity-pattern-tab {{ $detectedPattern === 'photo' ? 'is-active' : '' }}" data-pattern="photo">
                            パターンA：顔写真付き身分証 1枚
                        </button>
                        <button type="button" class="identity-pattern-tab {{ $detectedPattern === 'non_photo' ? 'is-active' : '' }}" data-pattern="non_photo">
                            パターンB：顔写真なし身分証 ＋ 住所確認書類
                        </button>
                    </div>

                    {{-- パターンA --}}
                    <div class="identity-pattern-pane" data-pattern-pane="photo" @if($detectedPattern !== 'photo') hidden @endif>
                        <p class="identity-pattern-help">
                            <strong>運転免許証 / パスポート / マイナンバーカード / 在留カード</strong> のいずれか1点をアップロードしてください。両面記載のあるものは表面・裏面の両方を提出してください。
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

                    {{-- パターンB --}}
                    <div class="identity-pattern-pane" data-pattern-pane="non_photo" @if($detectedPattern !== 'non_photo') hidden @endif>
                        <p class="identity-pattern-help">
                            <strong>顔写真なし身分証（健康保険証 など）</strong>と<strong>住所確認書類（住民票・公共料金領収書 など）</strong>の<strong>両方</strong>をアップロードしてください。両方が承認されてはじめて本人確認が完了します。
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

                    <p id="cast-identity-message" class="management-summary-note" style="display:none; margin-top:8px;"></p>
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

    /* ファイル選択：ファイル名 + 選択済みチェック + 画像サムネ */
    document.querySelectorAll('input[type="file"].bank-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var nameDisplay = document.getElementById(input.id + '_name');
            var uploadBtn = input.previousElementSibling;
            while (uploadBtn && !uploadBtn.classList.contains('file-upload-btn')) {
                uploadBtn = uploadBtn.previousElementSibling;
            }
            var file = input.files && input.files[0];
            if (file) {
                if (nameDisplay) {
                    nameDisplay.textContent = file.name;
                    nameDisplay.classList.add('is-set');
                }
                if (uploadBtn) uploadBtn.classList.add('is-selected');
            } else {
                if (nameDisplay) {
                    nameDisplay.textContent = '選択されていません';
                    nameDisplay.classList.remove('is-set');
                }
                if (uploadBtn) uploadBtn.classList.remove('is-selected');
            }
        });
    });
});
</script>
@endpush
