@extends('layouts.app')

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
    background: linear-gradient(135deg, #dcb568, #b8860b);
    color: #2a1406;
    box-shadow: 0 4px 14px rgba(220,181,104,.25);
}
.identity-pattern-tab:not(.is-active):hover { background: rgba(255,255,255,.06); color: #f8e9c8; }
.identity-pattern-help {
    font-size: 0.78rem;
    color: #C9B8B8;
    line-height: 1.7;
    margin: 0 0 12px;
    padding: 10px 12px;
    border-left: 2px solid rgba(220,181,104,.55);
    background: rgba(220,181,104,.04);
    border-radius: 0 6px 6px 0;
}
.identity-form-section {
    border: 1px solid rgba(220,181,104,.18);
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
    color: #ffe2a3;
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
.identity-form-section__pill.is-pending  { background: rgba(234,179,8,.16);  color: #fde68a; }
.identity-form-section__pill.is-rejected { background: rgba(220,38,38,.16);  color: #fca5a5; }
.identity-status-overall {
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid rgba(220,181,104,.35);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(220,181,104,.06);
}
.identity-status-overall.is-verified {
    background: linear-gradient(135deg, rgba(16,185,129,.12), rgba(16,185,129,.04));
    border-color: rgba(16,185,129,.45);
}
.identity-status-overall i { font-size: 1.4rem; color: #ffe2a3; }
.identity-status-overall.is-verified i { color: #6ee7b7; }
.identity-status-overall__text { color: #f8e9c8; font-weight: 700; }
.identity-status-overall__text small { display: block; color: #C9B8B8; font-weight: 500; font-size: 0.78rem; margin-top: 2px; }
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
            <a href="{{ route('cast.mypage.index') }}" class="cast-mypage-back-link">
                <i class="fas fa-chevron-left"></i> マイページへ戻る
            </a>
            <h1 class="mypage-page-title serif-font">本人確認</h1>

            <div class="mypage-detail-box">
                <div class="mypage-section">
                    <h2 class="section-title section-title-gold">本人確認の状況</h2>

                    {{-- 全体ステータス --}}
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

    // 各カテゴリのフォーム送信
    document.querySelectorAll('form.cast-identity-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
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
                var msgEl = document.getElementById('cast-identity-message');
                if (msgEl) {
                    msgEl.style.display = 'block';
                    msgEl.textContent = res && res.message ? res.message : 'アップロードしました。';
                }
                window.location.reload();
            }).catch(function (error) {
                var messages = error && error.errors ? Object.values(error.errors).flat() : [];
                alert(messages[0] || (error && error.message) || 'アップロードに失敗しました。時間をおいて再度お試しください。');
            });
        });
    });
});
</script>
@endpush
