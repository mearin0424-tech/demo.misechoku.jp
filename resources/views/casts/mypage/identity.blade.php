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
    background: rgba(255,255,255,.05);
    border-radius: 12px;
    margin: 12px 0 16px;
}
.identity-pattern-tab {
    flex: 1;
    padding: 10px 12px;
    border-radius: 8px;
    background: transparent;
    border: 0;
    color: #C9B8B8;
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
.identity-pattern-tab:not(.is-active):hover { background: rgba(255,255,255,.06); color: #e6dffc; }
.identity-pattern-help {
    font-size: 0.78rem;
    color: #C9B8B8;
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
    background: rgba(255,255,255,.02);
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
    color: #c4b5fd;
}
.identity-form-section__pill {
    font-size: 0.7rem;
    padding: 3px 8px;
    border-radius: 999px;
    background: rgba(255,255,255,.06);
    color: #C9B8B8;
    font-weight: 700;
}
.identity-form-section__pill.is-approved { background: rgba(16,185,129,.18); color: #6ee7b7; }
.identity-form-section__pill.is-pending  { background: rgba(234,179,8,.16);  color: #fde047; }
.identity-form-section__pill.is-rejected { background: rgba(220,38,38,.16);  color: #fca5a5; }
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
    color: #c4b5fd;
    filter: drop-shadow(0 2px 8px rgba(168, 85, 247, 0.35));
}
.identity-status-overall.is-verified i {
    color: #6ee7b7;
    filter: drop-shadow(0 2px 8px rgba(16, 185, 129, 0.45));
}
/* メインテキスト：大きく */
.identity-status-overall__text {
    color: #ffffff;
    font-weight: 800;
    font-size: 1.05rem;
    line-height: 1.25;
    letter-spacing: -0.01em;
}
.identity-status-overall__text small {
    display: block;
    color: rgba(255, 255, 255, 0.62);
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
    color: rgba(255, 255, 255, 0.78);
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
    border: 1px solid rgba(168, 85, 247, 0.40);
    color: rgba(255, 255, 255, 0.88);
    background: rgba(168, 85, 247, 0.06);
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.identity-form-section .file-upload-btn:hover {
    background: rgba(168, 85, 247, 0.14);
    border-color: rgba(168, 85, 247, 0.65);
}
.identity-form-section .file-upload-btn.is-selected {
    color: #6ee7b7;
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
    color: rgba(255, 255, 255, 0.55);
    word-break: break-all;
}
.identity-form-section .file-name-display.is-set {
    color: #d4d4d4;
}
/* インラインエラー（alert の置き換え）*/
.cast-identity-error {
    margin: 10px 0 0;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid rgba(248, 113, 113, 0.45);
    background: rgba(220, 38, 38, 0.08);
    color: #fecaca;
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
    background: rgba(16, 185, 129, 0.08);
    color: #a7f3d0;
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

                    {{-- 全体ステータス（このカードがページの "hero" として機能する） --}}
                    <div class="identity-status-overall {{ $isVerified ? 'is-verified' : '' }}">
                        <i class="fas {{ $isVerified ? 'fa-circle-check' : 'fa-clock' }}"></i>
                        <div class="identity-status-overall__text">
                            {{ $isVerified ? '本人確認 完了' : '本人確認 未完了' }}
                            <small>
                                @if($isVerified)
                                    すべての書類が承認されています。
                                @else
                                    下記のいずれかのパターンで書類を提出してください。
                                @endif
                            </small>
                        </div>
                    </div>

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
