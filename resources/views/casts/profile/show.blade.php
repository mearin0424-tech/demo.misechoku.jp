@extends('layouts.app')

@section('title', ($cast['nickname'] ?? $cast['name']) . ' - プロフィール')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cast_profile.css') }}">
@endpush

@section('content')
<div class="cast-profile-wrapper">
    {{-- ヒーロー写真エリア --}}
    <section class="profile-hero" aria-label="プロフィール写真">
        <a href="{{ url()->previous() }}" class="btn-hero-back" aria-label="戻る">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="profile-hero-inner">
            <img src="{{ $cast['img'] }}" alt="{{ $cast['nickname'] ?? $cast['name'] }}" class="profile-hero-img">
            <div class="profile-hero-gradient"></div>
            <div class="profile-hero-badge">
                @if($cast['is_applied'] ?? false)
                    <span class="badge-approved">入金承認済</span>
                @endif
            </div>
        </div>
    </section>

    {{-- メインコンテンツ --}}
    <div class="profile-main-contents">
        <div class="profile-view-inner">
            {{-- 基本情報 --}}
            <header class="cast-header">
                <h1 class="cast-name serif-font">{{ $cast['nickname'] ?? $cast['name'] }}<span class="cast-age">({{ $cast['age'] }})</span></h1>
                <p class="cast-location">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    <span>@if(!empty($cast['pref']) || !empty($cast['city'])){{ implode(' / ', array_filter([$cast['pref'] ?? null, $cast['city'] ?? null])) }} / @endifキャスト</span>
                </p>
            </header>

            {{-- KEEP・LIKE：スペックの上に控えめに（KEEPは件数なし、LIKEは件数表示） --}}
            <div class="profile-detail-actions">
                <button type="button" class="detail-action-btn keep {{ ($cast['is_kept'] ?? false) ? 'active' : '' }}" aria-pressed="{{ ($cast['is_kept'] ?? false) ? 'true' : 'false' }}">
                    <i class="fas fa-bookmark"></i>
                    <span>KEEP</span>
                </button>
                <span class="detail-action-like">
                    <i class="fas fa-heart"></i>
                    <span>LIKE：{{ $cast['like_cnt'] ?? 0 }}件</span>
                </span>
            </div>

            {{-- 生年月日（あれば表示） --}}
            @if(!empty($cast['birth_year']) && !empty($cast['birth_month']) && !empty($cast['birth_day']))
                <div class="detail-row">
                    <span class="detail-label">生年月日</span>
                    <span class="detail-value">{{ $cast['birth_year'] }}年{{ $cast['birth_month'] }}月{{ $cast['birth_day'] }}日</span>
                </div>
            @endif

            {{-- スペック --}}
            <section class="specs-section" aria-labelledby="specs-heading">
                <h2 id="specs-heading" class="section-heading">スペック</h2>
                <div class="specs-grid">
                    <div class="spec-item">
                        <span class="spec-label">Height / Weight</span>
                        <span class="spec-value">{{ $cast['height'] ?? '--' }}cm / {{ $cast['weight'] ?? '--' }}kg</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">B / W / H</span>
                        <span class="spec-value">{{ $cast['bust'] ?? $cast['b'] ?? '--' }} / {{ $cast['waist'] ?? $cast['w'] ?? '--' }} / {{ $cast['hip'] ?? $cast['h'] ?? '--' }}</span>
                    </div>
                </div>
            </section>

            {{-- 自己紹介 / PR --}}
            <section class="intro-section" aria-labelledby="intro-heading">
                <button type="button" class="accordion-trigger" id="intro-heading" aria-expanded="true" aria-controls="intro-body" onclick="toggleAccordion(this)">
                    <span>自己紹介 / PR</span>
                    <i class="fas fa-chevron-down accordion-icon is-open" aria-hidden="true"></i>
                </button>
                <div class="accordion-body" id="intro-body" role="region">
                    <div class="intro-text">
                        {!! nl2br(e($cast['intro'] ?? $cast['pr'] ?? '')) !!}
                    </div>
                </div>
            </section>

            {{-- その他情報 --}}
            <section class="other-info-detail-section" aria-labelledby="other-info-heading">
                <h2 id="other-info-heading" class="section-heading">その他情報</h2>
                <div class="other-info-detail-body">
                    <div class="detail-row">
                        <span class="detail-label">希望職種</span>
                        <span class="detail-value">{{ $cast['desired_job'] ?? '--' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">ご自分の系統</span>
                        <span class="detail-value">{{ $cast['my_field'] ?? '--' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">ご自分の内面・特技</span>
                        <span class="detail-value">{{ $cast['my_inner_skills'] ?? '--' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">シフト希望</span>
                        <span class="detail-value">{{ $cast['shift_hope'] ?? '--' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">勤務時間</span>
                        <span class="detail-value">{{ $cast['work_time_label'] ?? '--' }}</span>
                    </div>
                    <div class="detail-row detail-row-block">
                        <span class="detail-label">現職業</span>
                        <div class="detail-value">@if(!empty($cast['current_job'])){!! nl2br(e($cast['current_job'])) !!}@else--@endif</div>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">ナイトワーク経験</span>
                        <span class="detail-value">{{ $cast['night_work_label'] ?? '--' }}</span>
                    </div>
                </div>
            </section>

            {{-- レビュー --}}
            <section class="reviews-section" aria-labelledby="reviews-heading">
                <h2 id="reviews-heading" class="section-heading">Reviews</h2>
                @if(!empty($cast['reviews']) && count($cast['reviews']) > 0)
                    <ul class="reviews-list">
                        @foreach($cast['reviews'] as $rev)
                            <li class="review-item">
                                <div class="review-stars" aria-label="{{ $rev['score'] }}点">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= ($rev['score'] ?? 0) ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </div>
                                <p class="review-text">{{ $rev['text'] ?? '' }}</p>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="reviews-empty">まだレビューはありません</p>
                @endif
            </section>

            <div class="profile-spacer"></div>
        </div>
    </div>

    {{-- 下部固定アクション --}}
    <div class="profile-actions-sticky">
        @php $talkIndex = request()->is('cast/*') ? route('cast.talk.index') : route('shop.talk.index'); @endphp
        <a href="{{ $talkIndex }}" class="action-btn like">
            <i class="fas fa-heart"></i>
            <span>LIKE</span>
        </a>
        <a href="{{ $talkIndex }}" class="action-btn message">
            <i class="fas fa-comment"></i>
            <span>メッセージ</span>
        </a>
        <button type="button" class="action-btn keep" aria-label="キープ">
            <i class="fas fa-bookmark"></i>
        </button>
    </div>
</div>

<script>
function toggleAccordion(btn) {
    var body = document.getElementById('intro-body');
    var icon = btn.querySelector('.accordion-icon');
    var expanded = btn.getAttribute('aria-expanded') === 'true';
    body.classList.toggle('is-closed', expanded);
    btn.setAttribute('aria-expanded', !expanded);
    icon.classList.toggle('is-open', !expanded);
}
</script>
@endsection
