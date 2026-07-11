@extends('layouts.app-v2')

@section('title', 'Cast Profile')
@section('body-class', 'page-cast-profile')
@section('meta_description', trim((string) (($cast['intro'] ?? $cast['pr'] ?? '') ?: 'ミセチョクのキャストプロフィールです。')))
@section('meta_image', $cast['img'] ?? asset('assets/images/common/no-image.png'))
@section('canonical', $shareUrl ?? url()->current())

@push('head-styles')
<link rel="stylesheet" href="{{ asset('assets/css/fav-actions.css') }}">
<style>
    /* ===== プロフィールギャラリー：Instagram 風 3 列（cast/mypage と同パターン） ===== */
    #profile-gallery-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2px;
        padding: 0;
        margin: 0;
        list-style: none;
    }
    .profile-gallery-item {
        aspect-ratio: 1 / 1;
        padding: 0;
        margin: 0;
        overflow: hidden;
        position: relative;
    }
    .profile-gallery-slot {
        position: relative;
        width: 100%;
        height: 100%;
        padding: 0;
        border: 0;
        border-radius: 0;
        overflow: hidden;
        cursor: pointer;
        box-sizing: border-box;
        background: transparent;
    }
    .profile-gallery-slot:not(.has-img) {
        border: 2px dashed rgba(255, 255, 255, 0.22);
    }
    .profile-gallery-slot > img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .profile-gallery-empty {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        opacity: 0.45;
    }
    .profile-gallery-badge {
        position: absolute;
        top: 4px;
        left: 4px;
        font-size: 9px;
        font-weight: 700;
        color: #111;
        background: linear-gradient(to right, #fbcfe8, #f472b6);
        padding: 2px 6px;
        border-radius: 4px;
        line-height: 1;
    }

    /* ライトボックス：共通の最低限スタイル（v2 でも動くように） */
    .lightbox-overlay {
        position: fixed;
        inset: 0;
        z-index: 2000;
        background: rgba(0, 0, 0, 0.92);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .lightbox-overlay.is-open {
        display: flex;
    }
    .lightbox-image {
        max-width: 100%;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 12px;
    }
    .lightbox-close {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        border: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
    }
</style>
@endpush

@section('content')
@include('casts.profile.parts.show-content', [
    'cast' => $cast,
    'isOwn' => false,
    'showInteractionActions' => $showInteractionActions ?? true,
    'shareUrl' => $shareUrl ?? null,
    'shareTitle' => $shareTitle ?? (($cast['nickname'] ?? $cast['name']) . 'のプロフィール'),
    'shareText' => $shareText ?? ($cast['intro'] ?? $cast['pr'] ?? ''),
    'distanceLabel' => $distanceLabel ?? null,
    'distanceKm' => $distanceKm ?? null,
])
@endsection

@push('scripts')
{{-- KEEP / LIKE トグル（data-fav-toggle）+ トースト。従来の #btn-profile-keep/like は
     ハンドラ未実装の死にボタンだったため、共通機構に載せ替え --}}
<script src="{{ asset('assets/js/favorite-quick.js') }}"></script>
<script>
(function () {
    'use strict';
    // ===== ライトボックス：GALLERY スロット（.js-lightbox-target）クリックで拡大 =====
    var overlay = document.getElementById('lightbox-overlay');
    var img = document.getElementById('lightbox-image');
    function openLightbox(src) {
        if (!overlay || !img || !src) return;
        img.src = src;
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    window.closeLightbox = function (e) {
        if (e && e.target && !e.target.classList.contains('lightbox-overlay') && !e.target.closest('.lightbox-close')) return;
        if (!overlay || !img) return;
        overlay.classList.remove('is-open');
        img.src = '';
        document.body.style.overflow = '';
    };
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#profile-gallery-list .js-lightbox-target').forEach(function (el) {
            el.addEventListener('click', function (ev) {
                ev.preventDefault();
                var src = el.getAttribute('data-image-url') || (el.querySelector('img') && el.querySelector('img').currentSrc) || '';
                openLightbox(src);
            });
        });
    });
})();
</script>
@endpush
