@extends('layouts.app')

@section('title', 'DISCOVERY')
@section('body-class', 'no-scroll page-home')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/character-guide.css') }}">
@endpush

@php
    $itemType = $itemType ?? 'cast';
    $isShop = ($itemType === 'shop');
    // キャスト側でお店一覧 → お店詳細は cast/shopprofileview/{id}、お店側でキャスト一覧 → キャスト詳細は shop/castprofileview/{id}
    $detailRoute = $isShop ? 'cast.shopprofileview.show' : 'shop.castprofileview.show';
    $talkRoute = $isShop ? 'cast.talk.room' : 'shop.talk.room';
@endphp
@section('content')
<div id="home-screen">
    {{-- メインスワイパー（上下） --}}
    <div class="main-swiper swiper">
        <div class="swiper-wrapper">
            @foreach($items as $item)
            <div class="swiper-slide cast-card glass-card">
                @php
                    $imgPath = $isShop ? "storage/mock/shops/out-{$item['id']}.png" : "storage/mock/casts/{$item['id']}-1.png";
                @endphp
                {{-- メイン写真（カード型にせず画面いっぱいに表示） --}}
                <div class="home-photo-wrap">
                    <img
                        src="{{ asset($imgPath) }}"
                        alt="{{ $item['name'] }}の写真"
                        class="home-photo"
                        loading="lazy"
                    >
                    <a href="{{ route($detailRoute, $item['id']) }}" class="card-detail-link"></a>
                </div>

                {{-- アクションボタン --}}
                <div class="card-actions-overlay stop-propagation">
                    <button type="button" class="action-circle-btn like">
                        <i class="fas fa-heart"></i>
                        <span class="action-btn-count">{{ $item['like_count'] ?? 0 }}</span>
                    </button>
                    <button type="button" class="action-circle-btn keep"><i class="fas fa-bookmark"></i></button>
                    <a href="{{ route($talkRoute, $item['id']) }}" class="action-btn-message" aria-label="メッセージを送る">
                        <i class="fas fa-paper-plane"></i>
                    </a>
                    @if($isShop)
                    <a href="{{ route('cast.recruit.show', $item['id']) }}" class="card-recruit-btn">求人情報へ</a>
                    @endif
                </div>

                {{-- プロフィール情報 --}}
                <div class="card-bottom-info">
                    <h2 class="cast-name serif-font">{{ $item['name'] }}@if(!$isShop && isset($item['age'])) <span class="age">{{ $item['age'] }}</span>@endif</h2>
                    <div class="card-location"><i class="fas fa-map-marker-alt"></i> {{ $isShop ? '六本木' : '六本木' }}</div>
                    @if($isShop && isset($item['rating']))
                    <div class="card-rating">
                        <span class="card-rating-stars" aria-label="評価 {{ $item['rating'] }}">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star {{ $i <= floor($item['rating']) ? 'filled' : 'empty' }}">{{ $i <= floor($item['rating']) ? '★' : '☆' }}</span>
                            @endfor
                        </span>
                        <span class="card-rating-num">{{ number_format($item['rating'], 1) }}</span>
                    </div>
                    @endif
                    <div class="card-tags-row">
                        @foreach($item['tags'] as $tag)
                            <span class="tag-pill">#{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/character-guide.js') }}"></script>
<script src="{{ asset('assets/js/home.js') }}"></script>
@endpush