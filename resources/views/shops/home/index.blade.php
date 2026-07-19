@extends('layouts.app-v2')

@section('title', 'DISCOVERY')
@section('body-class', 'no-scroll page-home')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/home.css') }}?v=20260719-card-lines">
@endpush

@php
    $itemType = $itemType ?? 'cast';
    $isShop = ($itemType === 'shop');
    $isRecruit = ($itemType === 'recruit');
    $detailRoute = $isRecruit ? 'cast.shopprofile.show' : ($isShop ? 'cast.shopprofile.show' : 'shop.castprofileview.show');
    $talkRoute = ($isRecruit || $isShop) ? 'cast.talk.room' : 'shop.talk.room';
@endphp
@section('content')
<div id="home-screen" data-discovery-mode="{{ $itemType }}">
    {{-- メインスワイパー（上下） --}}
    <div class="main-swiper swiper">
        <div class="swiper-wrapper">
            @foreach($items as $item)
            <div
                class="swiper-slide cast-card glass-card {{ $isRecruit ? 'cast-card--recruit' : '' }}"
                @if($isRecruit)
                data-detail-url="{{ route('cast.shopprofile.show', $item['id']) }}"
                @endif
            >
                @php
                    $images = $item['images'] ?? [];
                    if (empty($images)) {
                        $images = [asset('assets/images/common/no-image.png')];
                    }
                    $imageCount = count($images);
                @endphp

                @if($isRecruit)
                {{-- ============================================================ --}}
                {{-- 求人カード（上部65%画像 / コンパクト情報 / タグなし） --}}
                {{-- ============================================================ --}}

                {{-- 1. 画像エリア（上部 65%） --}}
                <div class="rc-img-wrap home-photo-wrap">
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
                        <div class="photo-pagination swiper-pagination stop-propagation"></div>
                        @endif
                    </div>
                    @php $mo = $item['manager_overlay'] ?? ['show' => false]; @endphp
                    @if(!empty($mo['show']))
                    <div class="rc-manager-msg" aria-label="キャッチコピー">
                        <div class="rc-manager-msg__backdrop">
                            <div class="rc-manager-msg__inner">
                                @if(!empty($mo['line1_html']))
                                <p class="rc-manager-msg__line1">{!! $mo['line1_html'] !!}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    {{-- 画像下端を黒に溶け込ませるグラデーション --}}
                    <div class="rc-img-gradient" aria-hidden="true"></div>
                </div>

                {{-- 優良店バッヂ：カード左上（ヘッダー直下）に固定表示 --}}
                @if(!empty($item['is_premium']))
                <div class="rc-premium-topleft">
                    <x-ui.premium-badge />
                </div>
                @endif

                {{-- 2. アクションボタン（右側）。KEEP は favorite-quick.js（data-fav-toggle）に一本化 --}}
                <div class="card-actions-overlay rc-actions stop-propagation">
                    <div class="rc-action-item stop-propagation">
                        <button
                            type="button"
                            class="action-circle-btn keep stop-propagation {{ !empty($item['is_kept']) ? 'is-active' : '' }}"
                            data-fav-toggle
                            data-item-id="{{ $item['id'] }}"
                            data-item-type="shop"
                            data-action="keep"
                            aria-label="キープ"
                            aria-pressed="{{ !empty($item['is_kept']) ? 'true' : 'false' }}"
                        >
                            <svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 4a2 2 0 0 0-2 2v15.382a.6.6 0 0 0 .89.527L12 17.5l7.11 4.41A.6.6 0 0 0 20 21.382V6a2 2 0 0 0-2-2H6Z"/>
                            </svg>
                        </button>
                        <span class="rc-action-label">キープ</span>
                    </div>
                    <div class="rc-action-item stop-propagation">
                        <a
                            href="{{ route($talkRoute, ['id' => $item['id'], 'talk_topic' => 'other', 'initiate' => 1]) }}"
                            class="action-circle-btn message stop-propagation"
                            aria-label="トークを開始する"
                        >
                            <svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/>
                            </svg>
                        </a>
                        <span class="rc-action-label">トーク</span>
                    </div>
                </div>

                {{-- 3. 下部スタック（4行構成）
                     1行目: 店名 / 2行目: 業種・レビュー・閲覧数 /
                     3行目: 最寄り駅・距離 / 4行目: 左=ボーナス金（大） 右=時給 --}}
                <div class="rc-bottom-bar" aria-label="店舗情報">
                    <div class="rc-bottom-bar__stack">
                        @php
                            $trialR = $item['trial_hourly_range'] ?? null;
                            $helpR  = $item['help_hourly_range'] ?? null;
                            $bonusRg = $item['signup_bonus_range'] ?? null;
                            $hasRating = !empty($item['rating']) && $item['rating'] > 0;
                            $stationLine = trim((string) ($item['nearest_station'] ?? ''));
                            $areaLine = trim(($item['pref'] ?? '') . ' ' . ($item['city'] ?? ''));
                        @endphp

                        {{-- 1行目：店名（大きめ） --}}
                        <h2 class="rc-shop-name serif-font">{{ $item['name'] }}</h2>

                        {{-- 2行目：業種・レビュー（★値 + 件数はかっこ）・閲覧数 --}}
                        <div class="rc-line rc-line--meta">
                            @if(!empty($item['industry_name']))
                                <span class="rc-genre">{{ $item['industry_name'] }}</span>
                            @endif
                            @if($hasRating)
                                <span class="rc-rating-inline">
                                    <span class="rc-star" aria-hidden="true">★</span>{{ number_format((float)$item['rating'], 1) }}@if((int)($item['review_count'] ?? 0) > 0)<span class="rc-review-cnt">({{ (int)$item['review_count'] }}件)</span>@endif
                                </span>
                            @endif
                            <x-ui.view-count :count="(int) ($item['view_count'] ?? 0)" class="rc-views" />
                        </div>

                        {{-- 3行目：最寄り駅・自分からの距離 --}}
                        <div class="rc-line rc-line--loc">
                            <span class="rc-loc">
                                <i class="fas {{ $stationLine !== '' ? 'fa-train' : 'fa-map-marker-alt' }}" aria-hidden="true"></i>{{ $stationLine !== '' ? $stationLine : ($areaLine !== '' ? $areaLine : 'エリア未設定') }}
                            </span>
                            @if(!empty($item['distance_label']))
                                <span class="rc-dist"><i class="fas fa-route" aria-hidden="true"></i>自分から {{ $item['distance_label'] }}</span>
                            @endif
                        </div>

                        {{-- 4行目：左=入店祝い金（大きめ） / 右=時給 --}}
                        <div class="rc-line rc-line--money">
                            <div class="rc-bonus-big">
                                <span class="rc-bonus-big__label">入店祝い金</span>
                                <span class="rc-bonus-big__amount numeric-font">
                                    @if(!empty($bonusRg))
                                        ¥{{ number_format((int)$bonusRg['lo']) }}@if((int)$bonusRg['hi'] > (int)$bonusRg['lo'])〜@endif
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                            <div class="rc-wage-compact">
                                <span class="rc-wage-compact__row">
                                    <span>体入時給</span>
                                    <strong class="numeric-font">@if(!empty($trialR))¥{{ number_format((int)$trialR['lo']) }}〜@else —@endif</strong>
                                </span>
                                <span class="rc-wage-compact__row">
                                    <span>ヘルプ時給</span>
                                    <strong class="numeric-font">@if(!empty($helpR))¥{{ number_format((int)$helpR['lo']) }}〜@else —@endif</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. スワイプ誘導ガイド（上向きキャレット・常時バウンス） --}}
                <div class="discovery-swipe-guide" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"/>
                    </svg>
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

                {{-- アクションボタン（求人カードと同じラベル付き構造。favorite-quick.js に一本化） --}}
                <div class="card-actions-overlay rc-actions stop-propagation">
                    <div class="rc-action-item stop-propagation">
                        <button
                            type="button"
                            class="action-circle-btn keep stop-propagation {{ !empty($item['is_kept']) ? 'is-active' : '' }}"
                            data-fav-toggle
                            data-item-id="{{ $item['id'] }}"
                            data-item-type="{{ $itemType === 'recruit' ? 'shop' : 'cast' }}"
                            data-action="keep"
                            aria-label="キープ"
                            aria-pressed="{{ !empty($item['is_kept']) ? 'true' : 'false' }}"
                        >
                            <svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 4a2 2 0 0 0-2 2v15.382a.6.6 0 0 0 .89.527L12 17.5l7.11 4.41A.6.6 0 0 0 20 21.382V6a2 2 0 0 0-2-2H6Z"/>
                            </svg>
                        </button>
                        <span class="rc-action-label">キープ</span>
                    </div>
                    <div class="rc-action-item stop-propagation">
                        <a href="{{ route($talkRoute, ['id' => $item['id'], 'talk_topic' => 'other', 'initiate' => 1]) }}"
                           class="action-circle-btn message stop-propagation" aria-label="トークを開始する">
                            <svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/>
                            </svg>
                        </a>
                        <span class="rc-action-label">トーク</span>
                    </div>
                    @if($isShop)
                    <a href="{{ route('cast.shopprofile.show', $item['id']) }}" class="card-recruit-btn stop-propagation">求人</a>
                    @endif
                </div>

                {{-- プロフィール情報（4行構成）
                     1行目: 名前(年齢) / 2行目: 希望業種・経験有無 /
                     3行目: エリア・距離 / 4行目: タグ --}}
                <div class="card-bottom-info">
                    {{-- 1行目：名前（年齢はかっこ） --}}
                    <h2 class="cast-name serif-font">{{ $item['name'] }}@if(!$isShop && isset($item['age']))<span class="age">({{ $item['age'] }})</span>@endif</h2>

                    {{-- 2行目：希望業種・経験有無 --}}
                    <div class="cc-line cc-line--job">
                        @if(!empty($item['industry_name']))
                            <span class="cc-genre">{{ $item['industry_name'] }}</span>
                        @endif
                        @if(!$isShop && !empty($item['night_work_label']))
                            <span class="cc-exp {{ $item['night_work_label'] === '経験あり' ? 'is-exp' : '' }}">{{ $item['night_work_label'] }}</span>
                        @endif
                    </div>

                    {{-- 3行目：エリア・自分からの距離 --}}
                    @php
                        $bottomStation = trim((string) ($item['nearest_station'] ?? ''));
                        $bottomArea = trim(($item['pref'] ?? '') . ' ' . ($item['city'] ?? ''));
                    @endphp
                    <div class="cc-line cc-line--loc">
                        <span class="rc-loc">
                            <i class="fas {{ $bottomStation !== '' ? 'fa-train' : 'fa-map-marker-alt' }}" aria-hidden="true"></i>{{ $bottomStation !== '' ? $bottomStation : ($bottomArea !== '' ? $bottomArea : 'エリア未設定') }}
                        </span>
                        @if(!empty($item['distance_label']))
                            <span class="rc-dist"><i class="fas fa-route" aria-hidden="true"></i>自分から {{ $item['distance_label'] }}</span>
                        @endif
                    </div>

                    {{-- 4行目：タグ --}}
                    <div class="card-tags-row">
                        @foreach($item['tags'] ?? [] as $tag)
                            <span class="tag-pill">#{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="discovery-swipe-guide" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"/>
                    </svg>
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
<script src="{{ asset('assets/js/home.js') }}?v=20260712-motion"></script>
{{-- LIKE / KEEP の共通トグル（全画面この1本に統一） --}}
<script src="{{ asset('assets/js/favorite-quick.js') }}?v=20260719-keep-only"></script>
@endpush
