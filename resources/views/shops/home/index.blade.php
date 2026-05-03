@extends('layouts.app')

@section('title', 'DISCOVERY')
@section('body-class', 'no-scroll page-home')
@section('guide_message', '') {{-- ホームのスワイプ画面ではオコジョを表示しない --}}

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}?v=20260503-rc-redesign">
@endpush

@php
    $itemType = $itemType ?? 'cast';
    $isShop = ($itemType === 'shop');
    $isRecruit = ($itemType === 'recruit');
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

                @if($isRecruit)
                {{-- ============================================================ --}}
                {{-- 求人カード（新デザイン：上部60%=画像 / 下部=黒背景+求人情報） --}}
                {{-- ============================================================ --}}

                {{-- 1. 画像エリア（上部 60%） --}}
                <div class="rc-img-wrap home-photo-wrap" data-detail-url="{{ route($detailRoute, $item['id']) }}">
                    <div class="photo-swiper swiper {{ $imageCount <= 1 ? 'photo-swiper--single' : '' }}">
                        <div class="swiper-wrapper">
                            @foreach($images as $index => $imgPath)
                            <div class="swiper-slide">
                                <img
                                    src="{{ $imgPath }}"
                                    alt="{{ $item['name'] }}"
                                    class="home-photo"
                                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                >
                            </div>
                            @endforeach
                        </div>
                        @if($imageCount > 1)
                        <div class="photo-pagination swiper-pagination"></div>
                        @endif
                    </div>
                    {{-- 画像下端を黒に溶け込ませるグラデーション --}}
                    <div class="rc-img-gradient" aria-hidden="true"></div>
                </div>

                {{-- 2. アクションボタン（右側） --}}
                <div class="card-actions-overlay rc-actions">
                    {{-- いいね --}}
                    <div class="rc-action-item">
                        <button
                            type="button"
                            class="action-circle-btn like stop-propagation"
                            data-item-id="{{ $item['id'] }}"
                            data-item-type="shop"
                            data-action="like"
                        >
                            <i class="fas fa-heart"></i>
                            <span class="action-btn-count">{{ $item['like_count'] ?? 0 }}</span>
                        </button>
                    </div>
                    {{-- キープ --}}
                    <div class="rc-action-item">
                        <button
                            type="button"
                            class="action-circle-btn keep stop-propagation"
                            data-item-id="{{ $item['id'] }}"
                            data-item-type="shop"
                            data-action="keep"
                        >
                            <i class="fas fa-bookmark"></i>
                        </button>
                        <span class="rc-action-label">キープ</span>
                    </div>
                    {{-- メッセージ --}}
                    <div class="rc-action-item">
                        <a
                            href="{{ route($talkRoute, $item['id']) }}"
                            class="action-circle-btn stop-propagation"
                            aria-label="メッセージを送る"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </a>
                        <span class="rc-action-label">メッセージ</span>
                    </div>
                    {{-- 求人詳細 --}}
                    <div class="rc-action-item">
                        <a href="{{ route('cast.recruit.show', $item['id']) }}" class="card-recruit-btn stop-propagation">求人詳細</a>
                    </div>
                </div>

                {{-- 3. 店舗・求人情報エリア（下部黒背景に重ねて表示） --}}
                <div class="rc-info" aria-label="店舗情報">

                    {{-- 評価 + 優良認定店バッヂ --}}
                    <div class="rc-badges">
                        @if(!empty($item['rating']) && $item['rating'] > 0)
                        <div class="rc-rating-chip">
                            <span class="rc-star">★</span>
                            <span class="numeric-font">{{ number_format((float)$item['rating'], 1) }}</span>
                        </div>
                        @endif
                        @if(!empty($item['is_premium']))
                        <span class="rc-badge-premium">優良認定店</span>
                        @endif
                    </div>

                    {{-- 店舗名 --}}
                    <h2 class="rc-shop-name serif-font">{{ $item['name'] }}</h2>

                    {{-- 業種 + 場所 --}}
                    <div class="rc-meta">
                        @if(!empty($item['industry_name']))
                        <span class="rc-genre">{{ $item['industry_name'] }}</span>
                        <span class="rc-meta-sep">·</span>
                        @endif
                        <i class="fas fa-map-marker-alt rc-mappin"></i>
                        <span>{{ trim(($item['pref'] ?? '') . ' ' . ($item['city'] ?? '')) ?: '六本木' }}</span>
                    </div>

                    {{-- 時給ハイライト --}}
                    @php
                        $trialW = $item['trial_hourly_wage'] ?? null;
                        $mainW  = (int)($item['hourly_wage_regular'] ?? 0);
                        $helpW  = $item['help_hourly_wage'] ?? null;
                        $primaryW = $trialW ?? $mainW;
                        $bonusAmt = ($item['recruit_bonus_lines'][0]['offered'] ?? false)
                            ? ($item['recruit_bonus_lines'][0]['amount'] ?? 0) : 0;
                    @endphp
                    <div class="rc-wages-block">
                        <span class="rc-wages-label">{{ $trialW ? '体入時給' : '時給' }}</span>
                        <div class="rc-wages-primary">
                            <span class="rc-yen">¥</span>
                            <span class="rc-wages-num numeric-font">{{ number_format($primaryW) }}</span>
                            <span class="rc-wages-tilde">〜</span>
                        </div>
                        <div class="rc-wages-secondary">
                            @if($trialW && $mainW > 0)
                            <span>本入 <span class="numeric-font">¥{{ number_format($mainW) }}〜</span></span>
                            @endif
                            @if($helpW)
                            <span>ヘルプ <span class="numeric-font">¥{{ number_format($helpW) }}〜</span></span>
                            @endif
                            @if($bonusAmt > 0)
                            <span class="rc-bonus-pill">ボーナス ¥{{ number_format($bonusAmt) }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- 特徴タグ（最大6個） --}}
                    @if(!empty($item['tags']))
                    <div class="rc-tags">
                        @foreach(array_slice($item['tags'], 0, 6) as $tag)
                        <span class="rc-tag">#{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif

                </div>

                {{-- 4. スワイプアフォーダンス --}}
                <div class="rc-swipe-hint" aria-hidden="true">
                    <i class="fas fa-chevron-up rc-chevron-anim"></i>
                    <span class="rc-swipe-text">SWIPE NEXT</span>
                </div>

                @else
                {{-- ============================================================ --}}
                {{-- キャスト・店舗カード（既存レイアウト）                         --}}
                {{-- ============================================================ --}}

                {{-- メイン写真 --}}
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
                    @if($isShop)
                    <a href="{{ route('cast.recruit.show', $item['id']) }}" class="card-recruit-btn stop-propagation">求人</a>
                    @endif
                </div>

                {{-- プロフィール情報 --}}
                <div class="card-bottom-info">
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
                </div>

                @endif
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
<script src="{{ asset('assets/js/home.js') }}?v=20260503-rc-redesign"></script>
@endpush
