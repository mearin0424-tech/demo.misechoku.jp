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
        left: -8px;
        bottom: 10px;
        border-width: 8px 8px 8px 0;
        border-style: solid;
        border-color: transparent #fffaf0 transparent transparent;
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
    .dashboard-task-list {
        margin: 10px 0 0;
        display: grid;
        gap: 8px;
    }
    .dashboard-task-item {
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid rgba(212, 175, 55, 0.2);
        background: rgba(255,255,255,0.03);
        font-size: 0.82rem;
        color: #f0e5d2;
        line-height: 1.6;
    }
    .dashboard-task-empty {
        margin-top: 10px;
        font-size: 0.8rem;
        color: #aaa;
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
</style>
@endpush

@section('content')
<div class="mypage-page contents inner animate-fadeIn">
    <section class="mypage-area">
        {{-- ヒーロー：店舗名 --}}
        <h1 class="mypage-shop-name serif-font gold-gradient">{{ $shopData['shop_name'] }}</h1>

        {{-- アイコン＋ひとこと（モーダルで編集） --}}
        <div class="mypage-hero">
            <div class="shop-icon-wrapper">
                <img src="{{ (isset($subImages[0]) ? $subImages[0]['url'] : null) ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display" alt="">
                <button type="button" class="btn-add-icon" onclick="document.getElementById('gallery-upload').click()" aria-label="写真を追加">
                    <i class="fas fa-plus"></i>
                </button>
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

        <h2 class="section-title section-title-gold">Dash Board</h2>
        {{-- 優良店バッヂ・評価（2列アイコン） --}}
        @php $hasGoodPayerBadge = !empty($shopData['badges']['good_payer']); @endphp
        <div class="mypage-stats-row mypage-stats-row--cols-2" aria-label="統計">
            <button type="button"
                class="mypage-stat-panel mypage-stat-panel-badge {{ $hasGoodPayerBadge ? 'mypage-stat-panel-badge--active' : 'mypage-stat-panel-badge--inactive' }}"
                id="open-good-payer-badge-modal"
                aria-haspopup="dialog"
                aria-controls="modal-good-payer-badge"
                aria-label="安心バッヂの説明を開く">
                <span class="mypage-stat-icon" aria-hidden="true"><i class="fas fa-shield-heart"></i></span>
                <span class="mypage-stat-label">バッヂ</span>
                <span class="mypage-stat-value">{{ $hasGoodPayerBadge ? '優良店' : '未付与' }}</span>
            </button>
            <a href="{{ route('shop.mypage.review.index') }}" class="mypage-stat-panel mypage-stat-panel-link">
                <span class="mypage-stat-icon"><i class="fas fa-star"></i></span>
                <span class="mypage-stat-label">評価</span>
                <span class="mypage-stat-value">{{ $shopData['review_avg'] }}</span>
            </a>
        </div>
        @if(isset($todoList) && count($todoList) > 0)
            <div class="dashboard-task-list">
                @foreach($todoList as $todo)
                    <div class="dashboard-task-item">
                        <i class="fas fa-exclamation-circle"></i> {{ $todo['text'] }}
                    </div>
                @endforeach
            </div>
        @else
            <p class="dashboard-task-empty">未済タスクはありません。</p>
        @endif

        <div class="mypage-detail-box">
            <div class="mypage-section mypage-quick-actions cast-mypage-menu-buttons">
                <h2 class="section-title section-title-gold">Status Menu</h2>
                <div class="status-menu-grid">
                    <a href="{{ route('shop.recruits.status') }}" class="menu-btn job">
                        <div class="menu-btn-content">
                            <div class="menu-btn-icon"><i class="far fa-folder-open"></i></div>
                            <div class="menu-btn-text">
                                <p class="menu-btn-title">採用管理へ移動</p>
                            </div>
                        </div>
                        <div class="menu-btn-arrow"><i class="fas fa-chevron-right"></i></div>
                    </a>
                    <a href="{{ route('shop.mypage.payment.index') }}" class="menu-btn manage">
                        <div class="menu-btn-content">
                            <div class="menu-btn-icon"><i class="far fa-credit-card"></i></div>
                            <div class="menu-btn-text">
                                <p class="menu-btn-title">入金管理へ移動</p>
                            </div>
                        </div>
                        <div class="menu-btn-arrow"><i class="fas fa-chevron-right"></i></div>
                    </a>
                </div>
            </div>

            <div class="mypage-section">
                <h2 class="section-title section-title-gold">jobdescription</h2>
                <a href="{{ route('shop.jobdescription') }}" class="jobdescription-button">
                    <span>求人票へ移動</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>

            {{-- Image Library（ドラッグで並び替え・タップで大表示・削除・空きタップで登録） --}}
            <div class="mypage-section gallery-edit-section">
                <div class="gallery-section-header">
                    <h2 class="section-title section-title-gold">Image Library</h2>
                    <p class="gallery-section-hint">ドラッグで並び替え（スマホは長押し）</p>
                </div>
                <ul class="responsive-gallery gallery-grid" id="gallery-list" data-sort-save-url="{{ route('shop.profile.images.order') }}" data-empty-image-url="{{ asset('assets/images/common/no-image.png') }}">
                    @for($i = 0; $i < 8; $i++)
                    @php $img = $subImages[$i] ?? null; @endphp
                    <li class="gallery-grid-item" data-slot-index="{{ $i }}">
                        <div class="photo-slot {{ $img ? 'has-img' : '' }}"
                             data-image-id="{{ $img['id'] ?? '' }}"
                             data-image-url="{{ $img['url'] ?? '' }}">
                            @if($img)
                                <img src="{{ $img['url'] }}" alt="" loading="lazy">
                                @if($i === 0)
                                    <span class="photo-slot-badge">MAIN</span>
                                @endif
                            @else
                                <span class="photo-slot-empty"><i class="fas fa-image"></i></span>
                            @endif
                        </div>
                    </li>
                    @endfor
                </ul>
            </div>

            {{-- Shop Information（プロフィール） --}}
            <div class="mypage-section profile-info-section">
                <div class="section-title-row">
                    <h2 class="section-title">Shop Information</h2>
                    <button type="button" class="btn-outline-gold" id="open-profile-edit-btn">編集</button>
                </div>
                <p class="shop-access-text">
                    <i class="fas fa-map-marker-alt"></i> {{ $shopData['pref'] }}{{ $shopData['city'] }}{{ $shopData['addr1'] }}
                </p>
                @if(!empty($shopInfo['nearest_station'] ?? null))
                    <p class="shop-access-text"><i class="fas fa-train-subway"></i> {{ $shopInfo['nearest_station'] }}</p>
                @endif
                @if(!empty($shopInfo['working_hours'] ?? null) || !empty($shopInfo['working_days'] ?? null))
                    <p class="shop-access-text"><i class="fas fa-clock"></i> 営業時間 {{ $shopInfo['working_hours'] ?: '未設定' }} / 勤務日 {{ $shopInfo['working_days'] ?: '未設定' }}</p>
                @endif
                @if(!empty($shopInfo['concept'] ?? null))
                    <p class="shop-overview-text">{!! nl2br(e($shopInfo['concept'])) !!}</p>
                @endif
            </div>
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
                推奨サイズは <strong>3:4（例：1200×1600px、縦長）</strong> です。<br>
                画面に表示されている範囲で中央を基準に自動トリミングし、スマホ表示でも粗くなりすぎないよう約2MPまでサイズ調整してアップロードします。
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
        <h3 id="good-payer-badge-modal-title" class="mypage-modal-title serif-font">優良店バッヂとは？</h3>
        <div class="good-payer-badge-modal-guide" aria-hidden="true">
            <img src="{{ asset('assets/images/guide/guide-character.png') }}" alt="">
            <p class="good-payer-badge-modal-bubble">オコジョガイドだよ。ここでは、バッヂが付与される条件と現在の状態をわかりやすく案内するね。</p>
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
});
</script>
@endpush
