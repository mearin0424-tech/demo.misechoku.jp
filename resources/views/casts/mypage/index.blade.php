@extends('layouts.app')

@section('title', 'マイページ - プロフィール確認')
@section('body-class', 'page-cast-mypage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cast_profile.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="mypage-page contents inner animate-fadeIn">
    <section class="mypage-area">
        {{-- ヒーロー：キャスト名（お店マイページと同じ位置） --}}
        <h1 class="mypage-shop-name serif-font gold-gradient">{{ $cast['nickname'] ?? $cast['name'] }}</h1>

        {{-- アイコン＋ひとこと（お店同様） --}}
        <div class="mypage-hero">
            <div class="shop-icon-wrapper">
                <img src="{{ $cast['img'] }}" class="shop-icon-main" alt="{{ $cast['nickname'] ?? $cast['name'] }}">
            </div>
            <div class="shop-word-bubble glass-panel">
                <p class="shop-word-text">{{ Str::limit($cast['intro'] ?? $cast['pr'] ?? 'ひとことを設定しましょう', 50) }}</p>
            </div>
        </div>

        {{-- レビューカード（★評価からレビュー一覧へ・お店同様の位置） --}}
        <a href="{{ route('cast.mypage.reviews') }}" class="mypage-review-card shop-review-link">
            <span class="review-stars"><i class="fas fa-star"></i> {{ $review_avg ?? 0 }}</span>
            <span class="review-count">({{ $review_count ?? 0 }}件)</span>
            <i class="fas fa-chevron-right review-arrow"></i>
        </a>

        <div class="mypage-detail-box">
            {{-- プロフィール情報（お店同様：編集＋本文） --}}
            <div class="mypage-section profile-info-section">
                <div class="section-title-row">
                    <h2 class="section-title">プロフィール情報</h2>
                    <a href="{{ route('cast.profile.edit') }}" class="btn-outline-gold">編集</a>
                </div>
                <p class="shop-access-text">
                    <i class="fas fa-map-marker-alt"></i> @if(!empty($cast['pref']) || !empty($cast['city'])){{ implode(' ', array_filter([$cast['pref'] ?? null, $cast['city'] ?? null])) }} / @endifキャスト
                </p>
                <div class="shop-overview-text">
                    <div class="mypage-cast-specs">
                        <span class="spec-inline">Height {{ $cast['height'] ?? '--' }}cm / Weight {{ $cast['weight'] ?? '--' }}kg</span>
                        <span class="spec-inline">B {{ $cast['bust'] ?? '--' }} / W {{ $cast['waist'] ?? '--' }} / H {{ $cast['hip'] ?? '--' }}</span>
                    </div>
                    <div class="mypage-cast-intro">
                        {!! nl2br(e($cast['intro'] ?? $cast['pr'] ?? '')) !!}
                    </div>
                    <div class="mypage-cast-other other-info-detail-body">
                        @if(!empty($cast['birth_year']) && !empty($cast['birth_month']) && !empty($cast['birth_day']))
                            <div class="detail-row"><span class="detail-label">生年月日</span><span class="detail-value">{{ $cast['birth_year'] }}年{{ $cast['birth_month'] }}月{{ $cast['birth_day'] }}日</span></div>
                        @endif
                        <div class="detail-row"><span class="detail-label">希望職種</span><span class="detail-value">{{ $cast['desired_job'] ?? '--' }}</span></div>
                        <div class="detail-row"><span class="detail-label">シフト希望</span><span class="detail-value">{{ $cast['shift_hope'] ?? '--' }}</span></div>
                        <div class="detail-row"><span class="detail-label">勤務時間</span><span class="detail-value">{{ $cast['work_time_label'] ?? '--' }}</span></div>
                        <div class="detail-row"><span class="detail-label">ナイトワーク経験</span><span class="detail-value">{{ $cast['night_work_label'] ?? '--' }}</span></div>
                    </div>
                </div>
            </div>

            {{-- メニュー（お店同様の位置：採用状況・請求・接客診断のみ） --}}
            @include('casts.mypage.parts.menu', ['current' => 'profile', 'fullWidth' => false])
        </div>
    </section>
</div>
@endsection
