@extends('layouts.app')

@section('title', '求人情報')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<style>
    .view-section-label {
        font-size: 0.65rem;
        color: var(--gold);
        text-transform: uppercase;
        letter-spacing: 0.15em;
        margin-bottom: 4px;
        display: block;
        font-weight: bold;
    }
    .view-data-text { color: #fff; line-height: 1.6; }
    .recruit-detail-row {
        padding: 14px 0;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .recruit-detail-row:last-child { border-bottom: none; }
    .recruit-detail-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--color-text);
        opacity: 0.85;
        margin-bottom: 6px;
    }
    .recruit-detail-value { font-size: 0.9rem; color: #fff; }
    .recruit-map-wrap {
        margin-top: 10px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .recruit-map-link {
        display: inline-block;
        font-size: 0.75rem;
        color: var(--gold);
        margin-bottom: 8px;
        text-decoration: none;
    }
    .recruit-map-link:hover { text-decoration: underline; }
    .recruit-map-wrap iframe {
        width: 100%;
        height: 200px;
        border: 0;
        display: block;
    }
    .recruit-feature-block { margin-bottom: 20px; }
    .recruit-feature-block:last-child { margin-bottom: 0; }
    .recruit-feature-subtitle {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--gold);
        margin-bottom: 8px;
    }
    .recruit-feature-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .recruit-feature-list li {
        position: relative;
        padding-left: 18px;
        margin-bottom: 6px;
        font-size: 0.85rem;
        color: var(--color-text);
    }
    .recruit-feature-list li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.5em;
        width: 6px;
        height: 6px;
        background: #d4af37;
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
<div class="contents inner animate-fadeIn p-4 pb-24">
    <div class="flex justify-between items-center mb-8">
        <div class="title-area">
            <h2 class="serif-font text-2xl gold-gradient tracking-tight">Job Preview</h2>
            <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1">求人情報のプレビュー</p>
        </div>
        <a href="{{ route('shop.recruits.edit') }}" class="text-gold text-xs font-bold no-underline border border-gold/30 px-3 py-1 rounded-full">編集する</a>
    </div>

    <div class="mb-8 px-2">
        <h3 class="serif-font text-xl text-white leading-tight italic">"{{ $recruit['catch_copy'] }}"</h3>
    </div>

    <div class="space-y-0 glass-panel rounded-2xl overflow-hidden">
        {{-- 店舗名・オープン日 --}}
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">店舗名</p>
            <p class="recruit-detail-value">{{ $recruit['store_name'] ?? '—' }}</p>
        </div>
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">オープン日</p>
            <p class="recruit-detail-value">{{ $recruit['open_date'] ?? '—' }}</p>
        </div>

        {{-- 勤務地・地図 --}}
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">勤務地</p>
            <p class="recruit-detail-value">{{ $recruit['address'] ?? '—' }}</p>
            @if(!empty($recruit['map_embed_src']))
            <div class="recruit-map-wrap">
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($recruit['address'] ?? '') }}" target="_blank" rel="noopener noreferrer" class="recruit-map-link">
                    マップで開く
                </a>
                <iframe src="{{ $recruit['map_embed_src'] }}" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="勤務地の地図"></iframe>
            </div>
            @endif
        </div>

        {{-- 最寄り駅 --}}
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">最寄り駅</p>
            <p class="recruit-detail-value">{{ $recruit['nearest_station'] ?? '—' }}</p>
        </div>

        {{-- 給与 --}}
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">給与</p>
            <p class="recruit-detail-value">時給 ¥{{ number_format($recruit['hourly_wage_regular']) }}〜（体験時給: ¥{{ number_format($recruit['trial_hourly_wage']) }}）</p>
            <p class="text-xs text-gray-400 mt-1">{{ $recruit['salary_text'] }}</p>
        </div>

        {{-- お仕事内容 --}}
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">お仕事内容</p>
            <p class="recruit-detail-value">{{ $recruit['job_content'] ?? $recruit['salary_text'] }}</p>
        </div>

        {{-- お店の雰囲気 --}}
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">お店の雰囲気</p>
            <p class="recruit-detail-value">{{ $recruit['store_atmosphere'] ?? '—' }}</p>
        </div>

        {{-- 勤務日 --}}
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">勤務日</p>
            <p class="recruit-detail-value">{{ $recruit['working_days'] ?? '—' }}</p>
        </div>

        {{-- 定休日 --}}
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">定休日</p>
            <p class="recruit-detail-value">{{ $recruit['regular_holiday'] ?? '—' }}</p>
        </div>

        {{-- 勤務時間 --}}
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">勤務時間</p>
            <p class="recruit-detail-value">{{ $recruit['working_hours'] ?? '—' }}</p>
        </div>

        {{-- 採用条件 --}}
        <div class="recruit-detail-row px-4">
            <p class="recruit-detail-label">採用条件</p>
            <p class="recruit-detail-value">{{ $recruit['qualification'] ?? '—' }}</p>
        </div>

        {{-- お店の特徴（報酬・働き方・メリット・特徴・設備） --}}
        @if(!empty($recruit['store_features']) && is_array($recruit['store_features']))
        <div class="recruit-detail-row px-4 pt-4 pb-4">
            <p class="recruit-detail-label mb-4">お店の特徴</p>
            <div class="space-y-4">
                @foreach($recruit['store_features'] as $subTitle => $items)
                    @if(is_array($items) && count($items) > 0)
                    <div class="recruit-feature-block">
                        <p class="recruit-feature-subtitle">{{ $subTitle }}</p>
                        <ul class="recruit-feature-list">
                            @foreach($items as $item)
                            <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- 待遇タグ --}}
    <div class="glass-panel p-6 rounded-2xl mt-6">
        <label class="view-section-label mb-4">Benefits</label>
        <div class="flex flex-wrap gap-2">
            @foreach($recruit['selected_benefits'] as $benefit)
                <span class="status-badge status-active" style="font-size: 0.6rem;">{{ $benefit }}</span>
            @endforeach
        </div>
    </div>

    {{-- メッセージ --}}
    <div class="glass-panel p-6 rounded-2xl mt-6">
        <label class="view-section-label mb-2">Message</label>
        <p class="text-sm view-data-text font-light">{!! nl2br(e($recruit['message'])) !!}</p>
    </div>

    <div class="mt-10 flex flex-col gap-4">
        <a href="{{ route('shop.recruits.status') }}" class="btn-gold w-full py-4 text-center no-underline">ステータス管理に戻る</a>
        <p class="text-center text-[10px] text-gray-600 uppercase tracking-widest">Luxe Lounge Premium Recruit</p>
    </div>
</div>
@endsection
