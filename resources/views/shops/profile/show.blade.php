@extends('layouts.app')
@section('title', $shop['name'])
@section('body-class', 'page-shop-profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/shop_profile.css') }}">
@endpush

@section('content')
<div class="profile-view-container animate-fadeIn">
    <div class="profile-hero">
        <img src="{{ $shop['main_img'] }}" class="hero-img" alt="{{ $shop['name'] }}">
        <div class="hero-overlay">
            <h1 class="shop-name serif-font">{{ $shop['name'] }}</h1>
            @if($isOwn ?? false)
                <a href="{{ route('shop.profile.store.edit') }}" class="edit-fab" aria-label="編集"><i class="fas fa-pen"></i></a>
            @endif
        </div>
    </div>

    <div class="shop-header-top">
        <div class="shop-icon-wrapper">
            <img src="{{ $shop['main_img'] }}" alt="">
        </div>
        <div class="shop-word-bubble">
            <p>{{ $shop['word'] ?? '' }}</p>
        </div>
    </div>

    <div class="profile-actions">
        @if($isOwn ?? false)
            <a href="{{ route('shop.recruits.show') }}" class="btn-gold">求人票を見る</a>
        @endif
        <a href="#" class="btn-outline-gold">お問合せ</a>
    </div>

    <div class="shop-profile-section">
        <h3>Concept</h3>
        <p class="concept-text">{!! nl2br(e($shop['concept'] ?? '')) !!}</p>
    </div>

    <div class="shop-profile-section">
        <h3>Gallery</h3>
        <div class="shop-gallery-grid">
            @foreach(($shop['sub_images'] ?? []) as $img)
                <img src="{{ $img }}" alt="" loading="lazy">
            @endforeach
        </div>
    </div>
</div>
@endsection
