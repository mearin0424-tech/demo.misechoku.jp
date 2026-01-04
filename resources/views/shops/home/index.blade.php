@extends('layouts.app')

@section('title', 'Discovery')
{{-- app.blade.php の body class に適用して縦スクロールを禁止する --}}
@section('body-class', 'no-scroll')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">
@endpush

@section('content')
<div id="home-screen">

    {{-- スワイプガイド --}}
    <div class="swipe-guide-overlay">
        <div class="swipe-arrow-anim">
            <i class="fas fa-chevron-up"></i>
            <span>SWIPE</span>
        </div>
    </div>

    {{-- メインスワイパー（上下） --}}
    <div class="main-swiper-container swiper main-swiper">
        <div class="swiper-wrapper">
            @foreach($casts as $cast)
            <div class="swiper-slide cast-card glass-card">
                
                {{-- 写真スワイパー（左右） --}}
                <div class="swiper photo-swiper stop-propagation">
                    <div class="swiper-wrapper">
                        @for($i=1; $i<=3; $i++)
                        <div class="swiper-slide photo-item" 
                             style="background-image: url('{{ asset("storage/mock/casts/{$cast['id']}-{$i}.png") }}');">
                             <a href="{{ route('shop.cast.show', $cast['id']) }}" class="card-detail-link"></a>
                        </div>
                        @endfor
                    </div>
                    <div class="swiper-pagination photo-pagination"></div>
                </div>

                {{-- アクションボタン（CSSで位置を上に調整済み） --}}
                <div class="card-actions-overlay stop-propagation">
                    <button class="action-circle-btn like" title="Like"><i class="fas fa-heart"></i></button>
                    <button class="action-circle-btn keep" title="Keep"><i class="fas fa-bookmark"></i></button>
                    <a href="{{ route('shop.talk.room', $cast['id']) }}" class="action-circle-btn message" title="Send Message">
                        <i class="fas fa-paper-plane"></i>
                    </a>
                </div>

                {{-- プロフィール情報） --}}
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



    {{-- ガイドキャラクター（左右の順序を入れ替え） --}}
    <div id="discovery-guide">

        <div id="guide-speech-bubble">
            <p>上下でキャストを変更、左右で写真をチェック！</p>
        </div>
        <div id="guide-character-wrap">
            <img src="{{ asset('assets/images/guide/okojyo.png') }}" id="guide-character" alt="ガイド">
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/home.js') }}"></script>
@endpush