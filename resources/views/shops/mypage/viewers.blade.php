@extends('layouts.app-v2')

@section('title', '閲覧キャスト一覧')

@push('styles')
<style>
    .viewers-shell { padding: 0 16px 32px; }

    .viewers-premium-chip {
        display: inline-flex; align-items: center; gap: 6px;
        margin: 14px 0 0; padding: 4px 12px; border-radius: 999px;
        font-size: 0.68rem; font-weight: 800; letter-spacing: 0.06em;
        background: rgba(212, 160, 23, 0.14); color: #92650a; border: 1px solid rgba(212, 160, 23, 0.45);
    }

    .viewer-list { list-style: none; margin: 14px 0 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
    .viewer-row {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 12px; border-radius: 14px;
        background: #ffffff; border: 1px solid rgba(124, 58, 237, 0.18);
        text-decoration: none; color: inherit;
        box-shadow: 0 2px 8px rgba(76, 29, 149, 0.06);
    }
    .viewer-row:hover { border-color: rgba(124, 58, 237, 0.40); }
    .viewer-row__avatar { width: 52px; height: 52px; border-radius: 12px; object-fit: cover; flex: 0 0 auto; background: #ece7f6; }
    .viewer-row__main { flex: 1; min-width: 0; }
    .viewer-row__name { font-size: 0.92rem; font-weight: 800; color: #241f33; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .viewer-row__name small { font-weight: 600; color: #6d6685; margin-left: 4px; }
    .viewer-row__meta { font-size: 0.7rem; color: #6d6685; margin-top: 2px; display: flex; flex-wrap: wrap; gap: 4px 10px; }
    .viewer-row__meta i { color: #7c3aed; margin-right: 3px; font-size: 0.64rem; }
    .viewer-row__count {
        flex: 0 0 auto; text-align: center;
        font-size: 0.64rem; color: #6d6685; font-weight: 700;
    }
    .viewer-row__count strong { display: block; font-size: 1.05rem; color: #6d28d9; font-weight: 900; font-variant-numeric: tabular-nums; }
    .viewer-row__talk {
        flex: 0 0 auto; display: inline-flex; align-items: center; gap: 5px;
        font-size: 0.72rem; font-weight: 800; padding: 8px 12px; border-radius: 999px;
        background: linear-gradient(135deg, #a78bfa, #7c3aed); color: #ffffff; text-decoration: none;
        box-shadow: 0 4px 10px rgba(124, 58, 237, 0.25);
    }

    .viewers-empty {
        margin-top: 16px; padding: 36px 16px; text-align: center;
        font-size: 0.84rem; color: #6d6685;
        background: #ffffff; border: 1px dashed rgba(124, 58, 237, 0.3); border-radius: 14px;
    }

    /* 非加入ティーザー */
    .viewers-teaser {
        margin-top: 16px; padding: 26px 18px; text-align: center;
        background: linear-gradient(180deg, rgba(212, 160, 23, 0.08), #ffffff 55%);
        border: 1px solid rgba(212, 160, 23, 0.5); border-radius: 16px;
    }
    .viewers-teaser__icon { font-size: 1.6rem; color: #b8860b; margin-bottom: 8px; }
    .viewers-teaser__count { font-size: 1.05rem; font-weight: 800; color: #241f33; margin: 0 0 6px; }
    .viewers-teaser__count strong { color: #b45309; font-size: 1.5rem; }
    .viewers-teaser__desc { font-size: 0.78rem; color: #5f5876; line-height: 1.8; margin: 0 0 16px; }
    .viewers-teaser__cta {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 26px; border-radius: 999px; text-decoration: none;
        background: linear-gradient(135deg, #e3b94a, #b8860b); color: #ffffff;
        font-weight: 800; font-size: 0.9rem;
        box-shadow: 0 6px 16px rgba(184, 134, 11, 0.30);
    }
</style>
@endpush

@section('content')
<div class="viewers-shell animate-fadeIn">
    <span class="viewers-premium-chip"><i class="fas fa-crown"></i> PREMIUM機能</span>
    {{-- タイトルはヘッダー中央、説明はオコジョガイド（character_guide_settings）に集約 --}}

    @if($isPremium)
        @if(empty($viewers))
            <div class="viewers-empty">
                まだ閲覧したキャストがいません。<br>
                求人票やひとことを充実させると閲覧が増えやすくなります。
            </div>
        @else
            <ul class="viewer-list">
                @foreach($viewers as $v)
                    <li>
                        <a href="{{ route('shop.castprofileview.show', $v['cast_id']) }}" class="viewer-row">
                            <img src="{{ $v['avatar_url'] }}" alt="" class="viewer-row__avatar" loading="lazy" decoding="async">
                            <span class="viewer-row__main">
                                <span class="viewer-row__name">{{ $v['name'] }}@if($v['age'])<small>({{ $v['age'] }})</small>@endif</span>
                                <span class="viewer-row__meta">
                                    @if($v['area'] !== '')<span><i class="fas fa-location-dot"></i>{{ $v['area'] }}</span>@endif
                                    @if($v['last_viewed_at'] !== '')<span><i class="fas fa-clock"></i>{{ $v['last_viewed_at'] }}</span>@endif
                                </span>
                            </span>
                            <span class="viewer-row__count"><strong>{{ number_format($v['view_count']) }}</strong>回閲覧</span>
                            <span class="viewer-row__talk" onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('shop.talk.room', $v['cast_id']) }}';">
                                <i class="fas fa-comment-dots"></i> トーク
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    @else
        <div class="viewers-teaser">
            <div class="viewers-teaser__icon"><i class="fas fa-crown"></i></div>
            <p class="viewers-teaser__count">
                @if($totalViewers > 0)
                    これまでに <strong>{{ number_format($totalViewers) }}</strong> 名のキャストが閲覧しています
                @else
                    閲覧したキャストをここで確認できます
                @endif
            </p>
            <p class="viewers-teaser__desc">
                「誰が」閲覧したかは Premiumプラン限定の機能です。<br>
                Premiumなら閲覧キャストの一覧表示に加えて、AIレコメンドの優先表示・スカウト上限の緩和（1日{{ \App\Services\PlanSubscriptionService::SCOUT_LIMIT_PREMIUM }}件）もご利用いただけます。
            </p>
            <a href="{{ route('subscription') }}" class="viewers-teaser__cta">
                <i class="fas fa-crown"></i> Premiumプランを見る
            </a>
        </div>
    @endif
</div>
@endsection
