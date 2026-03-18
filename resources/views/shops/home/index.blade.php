@extends('layouts.app')

@section('title', 'DISCOVERY')
@section('body-class', 'no-scroll page-home')
@section('guide_message', '') {{-- ホームのスワイプ画面ではオコジョを表示しない --}}

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}">
@endpush

@php
    $itemType = $itemType ?? 'cast';
    $isShop = ($itemType === 'shop');
    $isRecruit = ($itemType === 'recruit');
    // キャスト側：求人票ベース → 求人詳細 cast.recruit.show / 店舗プロファイルは廃止。お店側でキャスト一覧 → キャスト詳細は shop/castprofileview/{id}
    $detailRoute = $isRecruit ? 'cast.recruit.show' : ($isShop ? 'cast.shopprofileview.show' : 'shop.castprofileview.show');
    $talkRoute = ($isRecruit || $isShop) ? 'cast.talk.room' : 'shop.talk.room';
@endphp
@section('content')
<div id="home-screen">
    {{-- メインスワイパー（上下） --}}
    <div class="main-swiper swiper">
        <div class="swiper-wrapper">
            @foreach($items as $item)
            <div class="swiper-slide cast-card glass-card">
                @php
                    $images = $item['images'] ?? [];
                    if (empty($images)) {
                        $images = [asset('assets/images/common/no-image.png')];
                    }
                    $imageCount = count($images);
                @endphp
                {{-- メイン写真（左右スワイプで同一アカウントの別写真を表示） --}}
                <div class="home-photo-wrap" data-detail-url="{{ route($detailRoute, $item['id']) }}">
                    <div class="photo-swiper swiper">
                        <div class="swiper-wrapper">
                            @foreach($images as $index => $imgPath)
                                <div class="swiper-slide">
                                    <img
                                        src="{{ $imgPath }}"
                                        alt="{{ $item['name'] }}の写真{{ $imageCount > 1 ? '（' . ($index + 1) . '枚目）' : '' }}"
                                        class="home-photo"
                                        loading="lazy"
                                    >
                                </div>
                            @endforeach
                        </div>
                        <div class="photo-pagination swiper-pagination"></div>
                    </div>
                </div>

                {{-- アクションボタン --}}
                <div class="card-actions-overlay stop-propagation">
                    <button
                        type="button"
                        class="action-circle-btn like"
                        data-item-id="{{ $item['id'] }}"
                        data-item-type="{{ $itemType === 'recruit' ? 'shop' : 'cast' }}"
                        data-action="like"
                    >
                        <i class="fas fa-heart"></i>
                        <span class="action-btn-count">{{ $item['like_count'] ?? 0 }}</span>
                    </button>
                    <button
                        type="button"
                        class="action-circle-btn keep"
                        data-item-id="{{ $item['id'] }}"
                        data-item-type="{{ $itemType === 'recruit' ? 'shop' : 'cast' }}"
                        data-action="keep"
                    >
                        <i class="fas fa-bookmark"></i>
                    </button>
                    <a href="{{ route($talkRoute, $item['id']) }}" class="action-btn-message" aria-label="メッセージを送る">
                        <i class="fas fa-paper-plane"></i>
                    </a>
                    @if($isRecruit)
                    <a href="{{ route('cast.recruit.show', $item['id']) }}" class="card-recruit-btn">求人詳細</a>
                    @elseif($isShop)
                    <a href="{{ route('cast.recruit.show', $item['id']) }}" class="card-recruit-btn">求人</a>
                    @endif
                </div>

                {{-- プロフィール情報（キャスト） / 求人票情報（キャスト側ホーム） --}}
                <div class="card-bottom-info">
                    <h2 class="cast-name serif-font">{{ $item['name'] }}@if(!$isShop && !$isRecruit && isset($item['age'])) <span class="age">{{ $item['age'] }}</span>@endif</h2>
                    <div class="card-location"><i class="fas fa-map-marker-alt"></i> {{ $isRecruit ? (trim(($item['pref'] ?? '') . ' ' . ($item['city'] ?? '')) ?: '六本木') : '六本木' }}</div>
                    @if($isRecruit)
                    <div class="card-recruit-summary">
                        <span class="card-recruit-wage">時給 ¥{{ number_format($item['hourly_wage_regular'] ?? 0) }}〜</span>
                        @if(!empty($item['trial_hourly_wage']))
                        <span class="card-recruit-trial">体験 ¥{{ number_format($item['trial_hourly_wage']) }}〜</span>
                        @endif
                        @if(!empty($item['noruma_reward']))
                        <span class="card-recruit-bonus">ボーナス ¥{{ number_format($item['noruma_reward']) }}</span>
                        @endif
                    </div>
                    @elseif($isShop && isset($item['rating']))
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
                        @foreach($item['tags'] ?? [] as $tag)
                            <span class="tag-pill">#{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- スワイプ操作オンボーディング（初回・久しぶりのみ表示） --}}
    <div class="home-onboarding-overlay" id="home-swipe-onboarding" aria-hidden="true">
        <div class="home-onboarding-inner">
            <div class="home-onboarding-header">
                <span class="home-onboarding-kicker">DISCOVERY GUIDE</span>
                <h2 class="home-onboarding-title">スワイプで直感的にチェック</h2>
                <p class="home-onboarding-lead">まずはホームの操作だけ覚えればOKです。</p>
            </div>
            <div class="home-onboarding-body">
                <div class="home-onboarding-row">
                    <span class="home-onboarding-icon">↑↓</span>
                    <span class="home-onboarding-desc">上下で次 / 前のアカウントへ</span>
                </div>
                <div class="home-onboarding-row">
                    <span class="home-onboarding-icon">←→</span>
                    <span class="home-onboarding-desc">左右で同じアカウントの別写真へ</span>
                </div>
                <div class="home-onboarding-row">
                    <span class="home-onboarding-icon">TAP</span>
                    <span class="home-onboarding-desc">{{ $isRecruit ? 'タップで求人詳細を表示' : 'タップでプロフィール詳細を表示' }}</span>
                </div>
            </div>
            <div class="home-onboarding-footer">
                画面をタップしてガイドを閉じる
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/home.js') }}?v=20260313-guide-2"></script>
@endpush