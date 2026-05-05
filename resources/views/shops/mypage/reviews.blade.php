@extends('layouts.app')

@section('title', 'Reviews')
@section('body-class', 'page-shop-mypage-reviews')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/review.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn shop-mypage-reviews-page">
    <section class="mypage-area">
        <h1 class="sr-only">Reviews</h1>

        <section class="review-list-area">
            <div class="shop-review-summary">
                <p class="shop-review-summary__label">総合評価</p>
                <div class="shop-review-summary__main">
                    <span class="shop-review-summary__score">{{ number_format((float) $shopData['review_avg'], 1) }}</span>
                    <div class="shop-review-summary__right">
                        @include('partials.review_stars', ['score' => (float) $shopData['review_avg'], 'size' => 'lg'])
                        <p class="shop-review-summary__count">全 <span>{{ $shopData['review_count'] }}</span> 件のレビュー</p>
                    </div>
                </div>
            </div>

            <div class="shop-review-items">
                @if (empty($reviews))
                    <div class="no-data-wrapper text-center py-20 opacity-50 shop-review-items__empty">
                        <p>まだレビューはありません。</p>
                    </div>
                @else
                    @foreach($reviews as $rev)
                        @php
                            $hasDetails = !empty($rev['details']);
                            $displayName = $rev['anonymous'] == 1 ? '匿名' : $rev['user_name'];
                        @endphp
                        <article
                            id="review-{{ $rev['id'] }}"
                            class="shop-review-item {{ $rev['release'] == 0 ? 'is-hidden' : '' }}"
                            @if($hasDetails) data-shop-review-accordion @endif
                        >
                            <div
                                class="shop-review-item__surface @if($hasDetails) shop-review-item__surface--interactive @endif"
                                @if($hasDetails)
                                    role="button"
                                    tabindex="0"
                                    aria-expanded="false"
                                    aria-controls="shop-review-breakdown-{{ $rev['id'] }}"
                                @endif
                            >
                                <div class="shop-review-item__head">
                                    <div class="shop-review-item__user">
                                        <div class="shop-review-item__avatar-wrap">
                                            <img
                                                src="{{ $rev['user_img'] ?: asset('assets/images/common/user-default.svg') }}"
                                                alt=""
                                                class="shop-review-item__avatar"
                                                width="48"
                                                height="48"
                                                loading="lazy"
                                            >
                                        </div>
                                        <div class="shop-review-item__user-text">
                                            <span class="shop-review-item__name">{{ $displayName }}</span>
                                            @if(!empty($rev['created_at_label']))
                                                <p class="shop-review-item__date">{{ $rev['created_at_label'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="shop-review-item__overall">
                                        @include('partials.review_stars', ['score' => (float) $rev['avg_score'], 'size' => 'sm'])
                                        <span class="shop-review-item__overall-num">{{ number_format($rev['avg_score'], 1) }}</span>
                                    </div>
                                </div>

                                @if ($rev['release'] == 0)
                                    <span class="badge-hidden shop-review-item__badge-hidden">非表示設定中</span>
                                @endif

                                <div class="shop-review-item__comment">
                                    {!! nl2br(e($rev['text'])) !!}
                                </div>

                                @if($hasDetails)
                                    <div class="shop-review-item__chevron" aria-hidden="true">
                                        <i class="fas fa-chevron-down shop-review-item__chevron-icon shop-review-item__chevron-icon--down"></i>
                                        <i class="fas fa-chevron-up shop-review-item__chevron-icon shop-review-item__chevron-icon--up"></i>
                                    </div>

                                    <div
                                        id="shop-review-breakdown-{{ $rev['id'] }}"
                                        class="shop-review-item__breakdown"
                                        aria-hidden="true"
                                    >
                                        <div class="shop-review-item__breakdown-inner">
                                            <h3 class="shop-review-item__breakdown-title">詳細評価</h3>
                                            <ul class="shop-review-item__detail-list">
                                                @foreach($rev['details'] as $det)
                                                    @php
                                                        $qLabel = $det['content'] ?? $det['name'] ?? '';
                                                        $qScore = (float) ($det['score'] ?? 0);
                                                    @endphp
                                                    <li class="shop-review-item__detail-row">
                                                        <span class="shop-review-item__detail-label">{{ $qLabel !== '' ? $qLabel : '（設問）' }}</span>
                                                        <span class="shop-review-item__detail-score">
                                                            @include('partials.review_stars', ['score' => $qScore, 'size' => 'sm'])
                                                            <span class="shop-review-item__detail-val">{{ number_format($qScore, 1) }}</span>
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if ($isPaidPlan)
                                <div class="shop-review-item__admin">
                                    @if ($rev['release'] == 1)
                                        <button type="button" class="btn-hide" onclick="toggleReviewStatus(this, {{ $rev['id'] }}, 1)">
                                            このレビューを非表示にする<span class="plan-label">※プレミアムプラン</span>
                                        </button>
                                    @else
                                        <button type="button" class="btn-show-again" onclick="toggleReviewStatus(this, {{ $rev['id'] }}, 0)">
                                            このレビューを表示する<span class="plan-label">※プレミアムプラン</span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @endforeach
                @endif
            </div>
        </section>
    </section>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        document.querySelectorAll('[data-shop-review-accordion]').forEach(function (article) {
            var surface = article.querySelector('.shop-review-item__surface--interactive');
            var panel = article.querySelector('.shop-review-item__breakdown');
            if (!surface || !panel) return;

            function toggle() {
                var open = article.classList.toggle('is-open');
                surface.setAttribute('aria-expanded', open ? 'true' : 'false');
                panel.setAttribute('aria-hidden', open ? 'false' : 'true');
            }

            surface.addEventListener('click', toggle);
            surface.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggle();
                }
            });
        });
    })();

    function toggleReviewStatus(btn, id, currentRelease) {
        const nextRelease = currentRelease === 1 ? 0 : 1;
        const actionText = nextRelease === 0 ? '非表示に' : '表示に';

        if (!confirm(`このレビューを${actionText}しますか？`)) return;

        fetch('{{ route("shop.review.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: id, release: nextRelease })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('更新に失敗しました');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('通信エラーが発生しました');
        });
    }
</script>
@endpush
