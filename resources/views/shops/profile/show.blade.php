@extends('layouts.app')
@section('title', $shop['name'])
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/shop_profile.css') }}">
@endpush

@section('content')
<div class="profile-view-container">
    <div class="profile-hero">
        <img src="{{ $shop['main_img'] }}" class="hero-img">
        <div class="hero-overlay">
            <h1 class="shop-name serif-font">{{ $shop['name'] }}</h1>
            @if($isOwn)
                <a href="{{ route('shop.profile.store.edit') }}" class="edit-fab"><i class="fas fa-pen"></i></a>
            @endif
        </div>
    </div>
    
    <div class="shop-header-top flex items-center p-4">
        <div class="shop-icon-wrapper w-20 h-20 mr-4 shrink-0">
            <img src="{{ $shop['main_img'] }}" class="w-full h-full rounded-full border-2 border-gold object-cover">
        </div>
        <div class="shop-word-bubble relative bg-sub border border-border text-white p-3 rounded-xl text-sm flex-1 min-h-[60px] flex items-center">
            <p>{{ $shop['word'] }}</p>
        </div>
    </div>

    <div class="profile-actions p-4 flex gap-3">
        <a href="{{ route('shop.recruits.show') }}" class="btn-gold flex-1 justify-center py-3 rounded-lg font-bold text-center decoration-none">求人票を見る</a>
        <a href="#" class="btn-outline-gold flex-1 justify-center py-3 rounded-lg border border-gold text-gold text-center decoration-none">お問合せ</a>
    </div>

    <div class="p-4">
        <h3 class="section-title-gold text-gold text-xs font-bold border-b border-border pb-2 mb-3 uppercase">Concept</h3>
        <p class="text-sm opacity-80 leading-relaxed">{!! nl2br(e($shop['concept'])) !!}</p>
    </div>

    <div class="p-4">
        <h3 class="section-title-gold text-gold text-xs font-bold border-b border-border pb-2 mb-3 uppercase">Gallery</h3>
        <div class="grid grid-cols-3 gap-2">
            @foreach($shop['sub_images'] as $img)
                <img src="{{ $img }}" class="rounded-lg aspect-square object-cover border border-white/10">
            @endforeach
        </div>
    </div>
</div>
@endsection