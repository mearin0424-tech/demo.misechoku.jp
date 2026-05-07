@extends('layouts.app')

@section('title', 'Cast Profile')
@section('body-class', 'page-cast-profile')
@section('meta_description', trim((string) (($cast['intro'] ?? $cast['pr'] ?? '') ?: 'ミセチョクのキャストプロフィールです。')))
@section('meta_image', $cast['img'] ?? asset('assets/images/common/no-image.png'))
@section('canonical', $shareUrl ?? url()->current())

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cast_profile.css') }}">
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
var profileImages = @json($cast['images'] ?? [$cast['img']]);
function setProfileMainImage(index) {
    var mainImg = document.getElementById('profile-main-img');
    var thumbs = document.querySelectorAll('.profile-photo-thumb');
    if (mainImg && profileImages[index]) {
        mainImg.src = profileImages[index];
    }
    thumbs.forEach(function(t, i) {
        t.classList.toggle('active', i === index);
    });
}
function toggleAccordion(btn) {
    var body = document.getElementById('intro-body');
    var icon = btn.querySelector('.accordion-icon');
    var expanded = btn.getAttribute('aria-expanded') === 'true';
    body.classList.toggle('is-closed', expanded);
    btn.setAttribute('aria-expanded', !expanded);
    icon.classList.toggle('is-open', !expanded);
}
document.addEventListener('DOMContentLoaded', function() {
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
