@extends('layouts.app')

@section('title', 'DISCOVERY')
@section('body-class', 'no-scroll page-home')
@section('guide_message', '') {{-- ホームのスワイプ画面ではオコジョを表示しない --}}

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}?v=20260326-recruit-bonus-lines">
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
<div id="home-screen" data-discovery-mode="{{ $itemType }}">
    {{-- メインスワイパー（上下） --}}
    <div class="main-swiper swiper">
        <div class="swiper-wrapper">
            @foreach($items as $item)
            <div class="swiper-slide cast-card glass-card {{ $isRecruit ? 'cast-card--recruit' : '' }}">
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

                {{-- アクションボタン（stop-propagation は各操作子のみ。オーバーレイ全面だと縦スワイプの touch が親に届かない） --}}
                <div class="card-actions-overlay">
                    <button
                        type="button"
                        class="action-circle-btn like stop-propagation"
                        data-item-id="{{ $item['id'] }}"
                        data-item-type="{{ $itemType === 'recruit' ? 'shop' : 'cast' }}"
                        data-action="like"
                    >
                        <i class="fas fa-heart"></i>
                        <span class="action-btn-count">{{ $item['like_count'] ?? 0 }}</span>
                    </button>
                    <button
                        type="button"
                        class="action-circle-btn keep stop-propagation"
                        data-item-id="{{ $item['id'] }}"
                        data-item-type="{{ $itemType === 'recruit' ? 'shop' : 'cast' }}"
                        data-action="keep"
                    >
                        <i class="fas fa-bookmark"></i>
                    </button>
                    <a href="{{ route($talkRoute, $item['id']) }}" class="action-btn-message stop-propagation" aria-label="メッセージを送る">
                        <i class="fas fa-paper-plane"></i>
                    </a>
                    @if($isRecruit)
                    <a href="{{ route('cast.recruit.show', $item['id']) }}" class="card-recruit-btn stop-propagation">求人詳細</a>
                    @elseif($isShop)
                    <a href="{{ route('cast.recruit.show', $item['id']) }}" class="card-recruit-btn stop-propagation">求人</a>
                    @endif
                </div>

                {{-- プロフィール情報（キャスト） / 求人票情報（キャスト側ホーム） --}}
                <div class="card-bottom-info">
                    @if($isRecruit)
                    {{-- 優良店バッヂ + 評価 --}}
                    <div class="card-recruit-badges">
                        @if(!empty($item['is_premium']))
                        <span class="badge-premium"><i class="fas fa-shield-alt"></i> 優良店</span>
                        @endif
                        @if(!empty($item['rating']) && $item['rating'] > 0)
                        <div class="card-rating-inline">
                            <span class="card-rating-star">★</span>
                            <span class="card-rating-val numeric-font">{{ number_format((float)$item['rating'], 1) }}</span>
                            @if(!empty($item['review_count']))
                            <span class="card-rating-cnt">({{ $item['review_count'] }})</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    {{-- 店舗名 --}}
                    <p class="card-shop-name-main serif-font">{{ $item['name'] }}</p>
                    {{-- 業種 + 場所 --}}
                    <div class="card-shop-meta">
                        @if(!empty($item['industry_name']))
                        <span class="card-industry-label">{{ $item['industry_name'] }}</span>
                        <span class="card-meta-dot">·</span>
                        @endif
                        <span class="card-location-label"><i class="fas fa-map-marker-alt"></i> {{ trim(($item['pref'] ?? '') . ' ' . ($item['city'] ?? '')) ?: '六本木' }}</span>
                    </div>
                    {{-- 公開求人情報：体入・ヘルプ --}}
                    <div class="card-recruit-wages">
                        <div class="card-wage-row">
                            <span class="card-wage-type">体入</span>
                            @if(!empty($item['trial_hourly_wage']))
                            <span class="card-wage-hourly numeric-font">¥{{ number_format($item['trial_hourly_wage']) }}〜</span>
                            @else
                            <span class="card-wage-none">—</span>
                            @endif
                            @if(!empty($item['recruit_bonus_lines'][0]['offered']) && !empty($item['recruit_bonus_lines'][0]['amount']))
                            <span class="card-wage-bonus numeric-font">ボーナス ¥{{ number_format((int)$item['recruit_bonus_lines'][0]['amount']) }}</span>
                            @endif
                        </div>
                        <div class="card-wage-row">
                            <span class="card-wage-type">ヘルプ</span>
                            @if(!empty($item['help_hourly_wage']))
                            <span class="card-wage-hourly numeric-font">¥{{ number_format($item['help_hourly_wage']) }}〜</span>
                            @else
                            <span class="card-wage-none">—</span>
                            @endif
                        </div>
                    </div>
                    @else
                    {{-- キャスト・店舗カード（既存レイアウト） --}}
                    <h2 class="cast-name serif-font">{{ $item['name'] }}@if(!$isShop && isset($item['age'])) <span class="age">{{ $item['age'] }}</span>@endif</h2>
                    <div class="card-location"><i class="fas fa-map-marker-alt"></i> 六本木</div>
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
                        @foreach($item['tags'] ?? [] as $tag)
                            <span class="tag-pill">#{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
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
<script src="{{ asset('assets/js/home.js') }}?v=20260320-swipe-nested"></script>
@endpush