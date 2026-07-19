@extends('layouts.app-v2')
@section('title', $shop['name'])
@section('body-class', 'page-shop-profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/shop_profile.css') }}">
@endpush

@section('content')
<div class="profile-view-container animate-fadeIn">
    <div class="profile-hero">
        <img src="{{ $shop['main_img'] }}" class="hero-img js-lightbox-target" alt="{{ $shop['name'] }}">
        <div class="hero-overlay">
            <h1 class="shop-name serif-font">{{ $shop['name'] }}</h1>
            @if($isOwn ?? false)
                <a href="{{ route('shop.profile.edit') }}" class="edit-fab" aria-label="編集"><i class="fas fa-pen"></i></a>
            @endif
        </div>
    </div>

    <div class="shop-header-top">
        <div class="shop-icon-wrapper">
            <img src="{{ $shop['main_img'] }}" alt="" class="js-lightbox-target">
        </div>
        <div class="shop-word-bubble">
            <p>{{ $shop['word'] ?? '' }}</p>
        </div>
        <div class="shop-like-badge">
            <x-ui.view-count :count="(int) ($shop['view_cnt'] ?? 0)" />
        </div>
    </div>

    <div class="profile-actions">
        @if($isOwn ?? false)
            <a href="{{ route('shop.recruits.show') }}" class="btn-gold">求人票を見る</a>
        @endif
        <a href="#" class="btn-outline-gold">お問合せ</a>
    </div>

    <div class="shop-profile-section">
        <h3>Shop Information</h3>
        <p class="concept-text" style="margin-bottom:10px;">
            <strong>業種：</strong>{{ $shop['industry'] ?: '—' }}<br>
            <strong>郵便番号：</strong>{{ $shop['zip'] ?: '—' }}<br>
            <strong>住所：</strong>{{ $shop['address'] ?: '—' }}
            @if(!empty($shop['tel']))<br><strong>電話：</strong>{{ $shop['tel'] }}@endif
            @if(!empty($shop['business_hours_shop']))<br><strong>店舗の営業時間：</strong>{{ $shop['business_hours_shop'] }}@endif
            @if(!empty($shop['nearest_stations']))
                <br><strong>最寄り駅：</strong>{!! nl2br(e(implode("\n", (array) $shop['nearest_stations']))) !!}
            @endif
        </p>
        @foreach(($shop['tag_groups'] ?? []) as $group)
            @if(!empty($group['tags']))
                <p class="concept-text" style="margin-top:8px;">
                    <strong>{{ $group['label'] }}：</strong>{{ implode(' / ', (array) $group['tags']) }}
                </p>
            @endif
        @endforeach
    </div>

    <div class="shop-profile-section">
        <h3>Gallery</h3>
        <div class="shop-gallery-grid">
            @foreach(($shop['sub_images'] ?? []) as $img)
                <img src="{{ $img }}" alt="" loading="lazy" class="js-lightbox-target">
            @endforeach
        </div>
    </div>
</div>

{{-- 画像フルスクリーン用ライトボックス --}}
<div id="lightbox-overlay" class="lightbox-overlay" onclick="closeLightbox(event)">
    <img id="lightbox-image" src="" alt="" class="lightbox-image">
    <button type="button" class="lightbox-close" aria-label="閉じる" onclick="closeLightbox(event)">
        <i class="fas fa-times"></i>
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var targets = document.querySelectorAll('.js-lightbox-target');
    var overlay = document.getElementById('lightbox-overlay');
    var img = document.getElementById('lightbox-image');
    if (!overlay || !img || targets.length === 0) return;

    targets.forEach(function (el) {
        el.style.cursor = 'zoom-in';
        el.addEventListener('click', function () {
            img.src = el.currentSrc || el.src;
            overlay.classList.add('is-open');
        });
    });
});

function closeLightbox(e) {
    if (e) e.stopPropagation();
    var overlay = document.getElementById('lightbox-overlay');
    var img = document.getElementById('lightbox-image');
    if (!overlay) return;
    overlay.classList.remove('is-open');
    if (img) img.src = '';
}
</script>
@endpush
