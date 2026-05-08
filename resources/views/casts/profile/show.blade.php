@extends('layouts.app')

@section('title', 'Cast Profile')
@section('body-class', 'page-cast-profile')
@section('meta_description', trim((string) (($cast['intro'] ?? $cast['pr'] ?? '') ?: 'ミセチョクのキャストプロフィールです。')))
@section('meta_image', $cast['img'] ?? asset('assets/images/common/no-image.png'))
@section('canonical', $shareUrl ?? url()->current())

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cast_profile.css') }}?v=20260509-cast-carousel">
@endpush

@section('content')
<div class="cast-profile-wrapper animate-fadeIn">
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
</div>

<script>
function toggleAccordion(btn) {
    var body = document.getElementById('intro-body');
    var icon = btn.querySelector('.accordion-icon');
    var expanded = btn.getAttribute('aria-expanded') === 'true';
    if (body) body.classList.toggle('is-closed', expanded);
    btn.setAttribute('aria-expanded', !expanded);
    if (icon) icon.classList.toggle('is-open', !expanded);
}
document.addEventListener('DOMContentLoaded', function() {
    // プロフィール写真カルーセル（左右矢印・ドット・サムネイル・スワイプ）
    var carousel = document.getElementById('profile-hero-carousel');
    if (carousel) {
        var slides = carousel.querySelectorAll('.profile-hero-slide');
        var dots = document.querySelectorAll('.profile-hero-dot[data-hero-goto]');
        var thumbBtns = document.querySelectorAll('.profile-photo-thumb[data-hero-goto]');
        var counterEl = document.getElementById('profile-hero-counter-current');
        var prevBtn = document.getElementById('profile-hero-prev');
        var nextBtn = document.getElementById('profile-hero-next');

        function setHeroIndex(idx) {
            var i = Math.max(0, Math.min(idx, slides.length - 1));
            var slide = slides[i];
            if (slide) carousel.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
            updateActiveState(i);
        }
        function currentHeroIndex() {
            var w = carousel.clientWidth || 1;
            return Math.round(carousel.scrollLeft / w);
        }
        function updateActiveState(i) {
            dots.forEach(function (d) {
                var idx = parseInt(d.getAttribute('data-hero-goto'), 10);
                d.classList.toggle('is-active', idx === i);
                d.setAttribute('aria-selected', idx === i ? 'true' : 'false');
            });
            thumbBtns.forEach(function (b) {
                var idx = parseInt(b.getAttribute('data-hero-goto'), 10);
                b.classList.toggle('active', idx === i);
            });
            if (counterEl) counterEl.textContent = (i + 1).toString();
        }

        document.querySelectorAll('[data-hero-goto]').forEach(function (el) {
            el.addEventListener('click', function () {
                var g = parseInt(el.getAttribute('data-hero-goto'), 10);
                if (!isNaN(g)) setHeroIndex(g);
            });
        });
        if (prevBtn) prevBtn.addEventListener('click', function () { setHeroIndex(currentHeroIndex() - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { setHeroIndex(currentHeroIndex() + 1); });

        // スクロール終了時にドット／サムネ／カウンタを同期
        var scrollEndTimer;
        carousel.addEventListener('scroll', function () {
            clearTimeout(scrollEndTimer);
            scrollEndTimer = setTimeout(function () { updateActiveState(currentHeroIndex()); }, 60);
        }, { passive: true });

        // キーボード操作（左右）
        carousel.setAttribute('tabindex', '0');
        carousel.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { e.preventDefault(); setHeroIndex(currentHeroIndex() - 1); }
            if (e.key === 'ArrowRight') { e.preventDefault(); setHeroIndex(currentHeroIndex() + 1); }
        });
    }

    var btnKeep = document.getElementById('btn-profile-keep');
    if (btnKeep) {
        btnKeep.addEventListener('click', function() {
            var active = this.getAttribute('aria-pressed') === 'true';
            this.classList.toggle('active', !active);
            this.setAttribute('aria-pressed', !active);
        });
    }
    var btnLike = document.getElementById('btn-profile-like');
    if (btnLike) {
        btnLike.addEventListener('click', function() {
            var numEl = this.querySelector('.num');
            if (numEl) {
                var n = parseInt(numEl.textContent, 10) || 0;
                numEl.textContent = n + 1;
            }
        });
    }
});
</script>
@endsection
