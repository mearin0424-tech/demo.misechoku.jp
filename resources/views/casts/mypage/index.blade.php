@extends('layouts.app')

@section('title', 'マイページ - プロフィール確認')
@section('body-class', 'page-cast-mypage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cast_profile.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
@php $subImages = $subImages ?? []; @endphp
<div class="mypage-page contents inner animate-fadeIn">
    <section class="mypage-area">
        {{-- ヒーロー：キャスト名（お店マイページと同じ位置） --}}
        <h1 class="mypage-shop-name serif-font gold-gradient">{{ $cast['nickname'] ?? $cast['name'] }}</h1>

        {{-- アイコン＋ひとこと（お店同様・編集可能） --}}
        <div class="mypage-hero">
            <div class="shop-icon-wrapper">
                <img src="{{ (isset($subImages[0]) ? $subImages[0]['url'] : null) ?? $cast['img'] ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display" alt="">
                <button type="button" class="btn-add-icon" onclick="document.getElementById('gallery-upload').click()" aria-label="写真を追加">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="shop-word-bubble glass-panel" onclick="openWordEdit()" role="button" tabindex="0">
                <p id="display-word" class="shop-word-text">{{ $cast['word'] ?? Str::limit($cast['intro'] ?? $cast['pr'] ?? 'ひとことを設定しましょう', 50) }}</p>
                <button type="button" class="btn-word-edit" aria-label="ひとことを編集">
                    <i class="fas fa-pen"></i>
                </button>
            </div>
        </div>

        {{-- レビュー表示（枠なし・星＋数値で画像のようなイメージ） --}}
        @php
            $avg = (float)($review_avg ?? 0);
            $avgRounded = round($avg * 2) / 2;
        @endphp
        <a href="{{ route('cast.mypage.reviews') }}" class="mypage-review-link-frameless">
            <span class="mypage-review-stars-inline" aria-label="{{ $avg }}点">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $avgRounded)
                        <i class="fas fa-star"></i>
                    @elseif($i - 0.5 <= $avgRounded)
                        <i class="fas fa-star-half-alt"></i>
                    @else
                        <i class="far fa-star"></i>
                    @endif
                @endfor
            </span>
            <span class="mypage-review-num">{{ number_format($avg, 1) }}</span>
            <span class="mypage-review-count-text">({{ $review_count ?? 0 }}件)</span>
        </a>

        <div class="mypage-detail-box">
            {{-- メニュー（プロフィール情報より上） --}}
            @include('casts.mypage.parts.menu', ['current' => 'profile', 'fullWidth' => false])

            {{-- プロフィール情報：編集ボタン＋5カテゴリ --}}
            <div class="mypage-section profile-info-section">
                <div class="section-title-row">
                    <h2 class="section-title">プロフィール情報</h2>
                    <a href="{{ route('cast.profile.edit') }}" class="btn-outline-gold">編集</a>
                </div>
                <p class="shop-access-text">
                    <i class="fas fa-map-marker-alt"></i> @if(!empty($cast['pref']) || !empty($cast['city'])){{ implode(' ', array_filter([$cast['pref'] ?? null, $cast['city'] ?? null])) }} / @endifキャスト
                </p>

                {{-- 自己PR --}}
                <div class="mypage-profile-block">
                    <h3 class="section-title section-title-gold">自己PR</h3>
                    <div class="shop-overview-text mypage-cast-intro">
                        {!! nl2br(e($cast['intro'] ?? $cast['pr'] ?? '—')) !!}
                    </div>
                </div>

                {{-- 基本情報（生年月日、身長体重、サイズ） --}}
                <div class="mypage-profile-block">
                    <h3 class="section-title section-title-gold">基本情報</h3>
                    <div class="mypage-cast-other other-info-detail-body">
                        @if(!empty($cast['birth_year']) && !empty($cast['birth_month']) && !empty($cast['birth_day']))
                            <div class="detail-row"><span class="detail-label">生年月日</span><span class="detail-value">{{ $cast['birth_year'] }}年{{ $cast['birth_month'] }}月{{ $cast['birth_day'] }}日</span></div>
                        @endif
                        <div class="detail-row"><span class="detail-label">身長</span><span class="detail-value">{{ $cast['height'] ?? '--' }}cm</span></div>
                        <div class="detail-row"><span class="detail-label">体重</span><span class="detail-value">{{ $cast['weight'] ?? '--' }}kg</span></div>
                        <div class="detail-row"><span class="detail-label">B / W / H</span><span class="detail-value">{{ $cast['bust'] ?? '--' }} / {{ $cast['waist'] ?? '--' }} / {{ $cast['hip'] ?? '--' }}</span></div>
                    </div>
                </div>

                {{-- 接客タイプ・系統 --}}
                <div class="mypage-profile-block">
                    <h3 class="section-title section-title-gold">接客タイプ・系統</h3>
                    <div class="mypage-cast-other other-info-detail-body">
                        @if(!empty($cast['personality_type']))
                            <div class="detail-row"><span class="detail-label">接客タイプ診断結果</span><span class="detail-value">{{ $cast['personality_type'] }}</span></div>
                        @endif
                        <div class="detail-row"><span class="detail-label">ご自分の系統</span><span class="detail-value">{{ $cast['my_field'] ?? '--' }}</span></div>
                        <div class="detail-row"><span class="detail-label">ご自分の内面・特技</span><span class="detail-value">{{ $cast['my_inner_skills'] ?? '--' }}</span></div>
                    </div>
                    <a href="{{ url('personality-test/personality-test.html') }}" class="btn-personality-test" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-clipboard-list"></i>
                        <span>接客タイプ診断</span>
                    </a>
                </div>

                {{-- 経歴・スキル --}}
                <div class="mypage-profile-block">
                    <h3 class="section-title section-title-gold">経歴・スキル</h3>
                    <div class="mypage-cast-other other-info-detail-body">
                        <div class="detail-row"><span class="detail-label">ナイトワーク経験</span><span class="detail-value">{{ $cast['night_work_label'] ?? '--' }}</span></div>
                        <div class="detail-row detail-row-block"><span class="detail-label">現職業</span><div class="detail-value">@if(!empty($cast['current_job'])){!! nl2br(e($cast['current_job'])) !!}@else—@endif</div></div>
                    </div>
                </div>

                {{-- 希望の職種・働き方 --}}
                <div class="mypage-profile-block">
                    <h3 class="section-title section-title-gold">希望の職種・働き方</h3>
                    <div class="mypage-cast-other other-info-detail-body">
                        <div class="detail-row"><span class="detail-label">希望職種</span><span class="detail-value">{{ $cast['desired_job'] ?? '--' }}</span></div>
                        <div class="detail-row"><span class="detail-label">シフト希望</span><span class="detail-value">{{ $cast['shift_hope'] ?? '--' }}</span></div>
                        <div class="detail-row"><span class="detail-label">勤務時間</span><span class="detail-value">{{ $cast['work_time_label'] ?? '--' }}</span></div>
                    </div>
                </div>
            </div>

            {{-- Image Library（ドラッグで並び替え・お店同様） --}}
            <div class="mypage-section gallery-edit-section">
                <div class="gallery-section-header">
                    <h2 class="section-title section-title-gold">Image Library</h2>
                    <p class="gallery-section-hint">ドラッグで並び替え（スマホは長押し）</p>
                </div>
                <ul class="responsive-gallery gallery-grid" id="gallery-list">
                    @for($i = 0; $i < 8; $i++)
                    @php $img = $subImages[$i] ?? null; @endphp
                    <li class="gallery-grid-item" data-slot-index="{{ $i }}">
                        <div class="photo-slot {{ $img ? 'has-img' : '' }}"
                             data-image-id="{{ $img['id'] ?? '' }}"
                             data-image-url="{{ $img['url'] ?? '' }}"
                             onclick="handleGallerySlotClick(event, this, {{ $i }})">
                            @if($img && !empty($img['url']))
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
        </div>
    </section>
</div>

{{-- 画像大表示モーダル（削除ボタンで削除） --}}
<div id="image-preview-modal" class="mypage-modal-overlay gallery-preview-overlay" onclick="closeGalleryPreview(event)" role="dialog" aria-label="画像プレビュー">
    <div class="gallery-preview-inner" onclick="event.stopPropagation()">
        <img id="modal-img" src="" alt="" class="mypage-modal-preview-img">
        <div class="gallery-preview-actions">
            <button type="button" class="btn-action btn-action-secondary gallery-preview-btn-close" onclick="closeGalleryPreview()">閉じる</button>
            <button type="button" class="btn-action gallery-preview-btn-delete" onclick="deleteGalleryImageFromModal(event)">削除</button>
        </div>
    </div>
</div>

{{-- ひとこと編集モーダル --}}
<div id="modal-word" class="mypage-modal-overlay modal-word-edit" style="display:none;">
    <div class="mypage-modal-panel glass-panel">
        <h3 class="mypage-modal-title serif-font">ひとこと編集</h3>
        <textarea id="word-input" rows="3" class="mypage-modal-textarea" placeholder="ひとことを入力"></textarea>
        <div class="mypage-modal-actions">
            <button type="button" class="btn-action btn-action-secondary" onclick="closeWordEdit()">戻る</button>
            <button type="button" class="btn-action btn-action-primary" onclick="saveWord()">保存</button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" class="sr-only" accept="image/*">
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="{{ asset('assets/js/gallery-sortable.js') }}"></script>
<script>
var _galleryPreviewImageId = null;
var _galleryPreviewLi = null;
var _galleryUploadSlotIndex = null;

function handleGallerySlotClick(ev, slotEl, slotIndex) {
    var li = slotEl.closest('li');
    var hasImg = slotEl.classList.contains('has-img');
    var imageId = slotEl.getAttribute('data-image-id');
    var imageUrl = slotEl.getAttribute('data-image-url');
    if (hasImg && imageUrl) {
        ev.preventDefault();
        ev.stopPropagation();
        _galleryPreviewImageId = imageId;
        _galleryPreviewLi = li;
        document.getElementById('modal-img').src = imageUrl;
        document.getElementById('image-preview-modal').style.display = 'flex';
    } else {
        _galleryUploadSlotIndex = slotIndex;
        document.getElementById('gallery-upload').click();
    }
}

function closeGalleryPreview(ev) {
    if (ev && ev.target !== ev.currentTarget) return;
    document.getElementById('image-preview-modal').style.display = 'none';
    _galleryPreviewImageId = null;
    _galleryPreviewLi = null;
}

function deleteGalleryImageFromModal(ev) {
    ev.preventDefault();
    ev.stopPropagation();
    if (!_galleryPreviewImageId || !_galleryPreviewLi) return;
    if (!confirm('この画像を削除しますか？')) return;
    var li = _galleryPreviewLi;
    var slot = li.querySelector('.photo-slot');
    slot.classList.remove('has-img');
    slot.removeAttribute('data-image-id');
    slot.removeAttribute('data-image-url');
    slot.innerHTML = '<span class="photo-slot-empty"><i class="fas fa-image"></i></span>';
    var mainIcon = document.getElementById('main-icon-display');
    if (mainIcon && li.getAttribute('data-slot-index') === '0') {
        var firstWithImg = document.querySelector('#gallery-list .photo-slot.has-img');
        mainIcon.src = firstWithImg ? firstWithImg.getAttribute('data-image-url') : '';
    }
    closeGalleryPreview();
}

document.getElementById('gallery-upload').addEventListener('change', function() {
    var file = this.files && this.files[0];
    if (!file) return;
    var slotIndex = _galleryUploadSlotIndex;
    if (slotIndex == null) {
        var firstEmpty = document.querySelector('#gallery-list .gallery-grid-item .photo-slot:not(.has-img)');
        slotIndex = firstEmpty ? Array.prototype.indexOf.call(document.querySelectorAll('#gallery-list .gallery-grid-item'), firstEmpty.closest('.gallery-grid-item')) : 0;
    }
    var list = document.getElementById('gallery-list');
    var items = list.querySelectorAll('.gallery-grid-item');
    var li = items[slotIndex];
    if (li) {
        var slot = li.querySelector('.photo-slot');
        var url = URL.createObjectURL(file);
        slot.classList.add('has-img');
        slot.setAttribute('data-image-id', 'local-' + Date.now());
        slot.setAttribute('data-image-url', url);
        slot.innerHTML = '<img src="' + url + '" alt="" loading="lazy">' + (slotIndex === 0 ? '<span class="photo-slot-badge">MAIN</span>' : '');
        var mainIcon = document.getElementById('main-icon-display');
        if (mainIcon && slotIndex === 0) mainIcon.src = url;
    }
    this.value = '';
    _galleryUploadSlotIndex = null;
});

function openWordEdit() {
    document.getElementById('modal-word').style.display = 'flex';
    document.getElementById('word-input').value = document.getElementById('display-word').innerText;
}
function closeWordEdit() {
    document.getElementById('modal-word').style.display = 'none';
}
function saveWord() {
    var val = document.getElementById('word-input').value.trim();
    document.getElementById('display-word').innerText = val || 'ひとことを設定しましょう';
    closeWordEdit();
}
</script>
@endpush
