@extends('layouts.app')

@section('title', 'マイページ - プロフィール確認')
@section('body-class', 'page-cast-mypage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cast_profile.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="cast-profile-wrapper animate-fadeIn">
    @include('casts.profile.parts.show-content', ['cast' => $cast, 'isOwn' => true, 'mypageMenu' => 'profile'])
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
</script>
@endsection
