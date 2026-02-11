@extends('layouts.app')

@section('title', 'DISCOVERY')
@section('body-class', 'no-scroll')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/character-guide.css') }}">
@endpush

@section('guide_message', '上下でキャストを変更、左右で写真をチェックできるよ！')

@section('content')
<div id="home-screen">
    {{-- メインスワイパー（上下） --}}
    <div class="main-swiper swiper">
        <div class="swiper-wrapper">
            @foreach($casts as $cast)
            <div class="swiper-slide cast-card glass-card">
                
                {{-- 写真スワイパー（左右・入れ子） --}}
                <div class="photo-swiper swiper">
                    <div class="swiper-wrapper">
                        @for($i=1; $i<=3; $i++)
                        <div class="swiper-slide photo-item">
                            <img
                                src="{{ asset("storage/mock/casts/{$cast['id']}-{$i}.png") }}"
                                alt="{{ $cast['name'] }}の写真 {{ $i }}"
                                class="photo-img"
                                loading="lazy"
                            >
                            <a href="{{ route('cast.profile.show', $cast['id']) }}" class="card-detail-link"></a>
                        </div>
                        @endfor
                    </div>
                    <div class="swiper-pagination photo-pagination"></div>
                </div>

                {{-- アクションボタン --}}
                <div class="card-actions-overlay stop-propagation">
                    <button class="action-circle-btn like"><i class="fas fa-heart"></i></button>
                    <button class="action-circle-btn keep"><i class="fas fa-bookmark"></i></button>
                    <a href="{{ route('shop.talk.room', $cast['id']) }}" class="action-circle-btn message">
                        <i class="fas fa-paper-plane"></i>
                    </a>
                </div>

                {{-- プロフィール情報 --}}
                <div class="card-bottom-info">
                    <h2 class="cast-name serif-font">{{ $cast['name'] }} <span class="age">{{ $cast['age'] }}</span></h2>
                    <div class="card-location"><i class="fas fa-map-marker-alt"></i> 六本木</div>
                    <div class="card-tags-row">
                        @foreach($cast['tags'] as $tag)
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