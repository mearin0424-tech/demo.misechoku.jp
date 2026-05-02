@extends('layouts.app')

@section('title', 'レビュー一覧')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/review.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn">
    <section class="mypage-area">
        <h1 class="mypage-page-title serif-font">レビュー一覧</h1>

        <section class="review-list-area">
            {{-- 総合評価サマリー --}}
            <div class="review-summary-container">
                <div class="summary-label">総合評価</div>
                <div class="summary-main">
                    <span class="avg-score-big">{{ number_format((float)$shopData['review_avg'], 1) }}</span>
                    <div class="summary-right">
                        <div class="stars-gold-big">
                            @php
                                $bigScore = round($shopData['review_avg'] * 2) / 2;
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
                        <div class="total-count-text">全 <span>{{ $shopData['review_count'] }}</span> 件のレビュー</div>
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
                    <div id="review-{{ $rev['id'] }}" class="review-card {{ $rev['release'] == 0 ? 'is-hidden' : '' }}">
                        <div class="rev-main-row">
                            <div class="rev-user-aside">
                                <img src="{{ $rev['user_img'] ?: asset('assets/images/common/user-default.svg') }}" class="rev-user-img">
                            </div>

                            <div class="rev-bubble">
                                @if ($rev['release'] == 0)
                                    <span class="badge-hidden">非表示設定中</span>
                                @endif

                                <div class="rev-bubble-header">
                                    <span class="rev-name">{{ $rev['anonymous'] == 1 ? '匿名' : $rev['user_name'] }}</span>
                                    <span class="rev-score-small">
                                        <i class="fas fa-star"></i> {{ number_format($rev['avg_score'], 1) }}
                                    </span>
                                </div>
                                @if(!empty($rev['created_at_label']))
                                    <div class="text-xs" style="margin-bottom:8px; color:#9ca3af;">{{ $rev['created_at_label'] }}</div>
                                @endif

                                <div class="rev-comment">
                                    {!! nl2br(e($rev['text'])) !!}
                                </div>

                                @if(!empty($rev['details']))
                                    <div class="rev-details-block">
                                        <div class="rev-details-heading">設問別の評価</div>
                                        <ul class="rev-details-list rev-details-list--open">
                                            @foreach($rev['details'] as $det)
                                                @php
                                                    $qLabel = $det['content'] ?? $det['name'] ?? '';
                                                    $qScore = (float) ($det['score'] ?? 0);
                                                    $qStars = round($qScore * 2) / 2;
                                                @endphp
                                                <li class="detail-row">
                                                    <span class="detail-label">{{ $qLabel !== '' ? $qLabel : '（設問）' }}</span>
                                                    <span class="detail-score-cell">
                                                        <span class="detail-stars" aria-hidden="true">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                @if($i <= $qStars)
                                                                    <i class="fas fa-star"></i>
                                                                @elseif($i - 0.5 <= $qStars)
                                                                    <i class="fas fa-star-half-alt"></i>
                                                                @else
                                                                    <i class="far fa-star"></i>
                                                                @endif
                                                            @endfor
                                                        </span>
                                                        <span class="detail-val">{{ number_format($qScore, 1) }}</span>
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- 管理アクション（旧reviews.jsのtoggleReviewStatusに対応） --}}
                        @if ($isPaidPlan)
                            <div class="rev-admin-actions">
                                @if ($rev['release'] == 1)
                                    <button class="btn-hide" onclick="toggleReviewStatus(this, {{ $rev['id'] }}, 1)">
                                        このレビューを非表示にする<span class="plan-label">※プレミアムプラン</span>
                                    </button>
                                @else
                                    <button class="btn-show-again" onclick="toggleReviewStatus(this, {{ $rev['id'] }}, 0)">
                                        このレビューを表示する<span class="plan-label">※プレミアムプラン</span>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                    @endforeach
                @endif
            </div>
        </section>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // レビュー公開・非公開の切り替え処理
    function toggleReviewStatus(btn, id, currentRelease) {
        const nextRelease = currentRelease === 1 ? 0 : 1;
        const actionText = nextRelease === 0 ? '非表示に' : '表示に';

        if (!confirm(`このレビューを${actionText}しますか？`)) return;

        // Laravel API ルートへFetch
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