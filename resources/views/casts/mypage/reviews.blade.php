@extends('layouts.app')

@section('title', 'レビュー一覧')
@section('body-class', 'page-cast-mypage')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/review.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn">
    <div class="cast-mypage-sub-page">
        @include('casts.mypage.parts.menu', ['current' => 'reviews'])

        <section class="mypage-area">
            <h1 class="mypage-page-title serif-font">レビュー一覧</h1>
            <section class="review-list-area">
        {{-- 総合評価サマリー --}}
        <div class="review-summary-container">
            <div class="summary-label">総合評価</div>
            <div class="summary-main">
                <span class="avg-score-big">{{ number_format((float)($castData['review_avg'] ?? 0), 1) }}</span>
                <div class="summary-right">
                    <div class="stars-gold-big">
                        @php
                            $bigScore = round(($castData['review_avg'] ?? 0) * 2) / 2;
                        @endphp
                        @for($i=1; $i<=5; $i++)
                            @if($i <= $bigScore)
                                <i class="fas fa-star"></i>
                            @elseif($i - 0.5 <= $bigScore)
                                <i class="fas fa-star-half-alt"></i>
                            @else
                                <i class="far fa-star"></i>
                            @endif
                        @endfor
                    </div>
                    <div class="total-count-text">全 <span>{{ $castData['review_count'] ?? 0 }}</span> 件のレビュー</div>
                </div>
            </div>
        </div>

        <div class="review-items">
            @if (empty($reviews))
                <div class="no-data-wrapper text-center py-20 opacity-50">
                    <p>まだレビューはありません。</p>
                </div>
            @else
                @foreach($reviews as $rev)
                <div class="review-card">
                    <div class="rev-main-row">
                        <div class="rev-bubble" style="margin-left: 0;">
                            <div class="rev-bubble-header">
                                <span class="rev-score-small">
                                    <i class="fas fa-star"></i> {{ number_format($rev['score'] ?? 0, 1) }}
                                </span>
                            </div>
                            <div class="rev-comment">
                                {!! nl2br(e($rev['text'] ?? '')) !!}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
            </section>
        </section>
    </div>
</div>
@endsection
