@extends('layouts.app')

@section('title', '求人情報')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<style>
    .recruit-preview-hero {
        background: linear-gradient(145deg, rgba(212,175,55,0.12) 0%, rgba(212,175,55,0.02) 100%);
        border: 1px solid rgba(212,175,55,0.2);
        border-radius: 20px;
        padding: 24px 20px;
        margin-bottom: 20px;
        text-align: center;
    }
    .recruit-preview-hero .catch { font-size: 1.05rem; color: var(--color-text-header); font-style: italic; margin: 0 0 8px 0; line-height: 1.5; }
    .recruit-preview-hero .store { font-size: 0.75rem; color: var(--gold); letter-spacing: 0.1em; }
    .recruit-salary-hero {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--gold);
        font-family: var(--font-serif);
        margin: 0 0 4px 0;
    }
    .recruit-salary-hero sub { font-size: 0.6rem; font-weight: 400; opacity: 0.9; }
    .recruit-map-wrap {
        margin-top: 12px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .recruit-map-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        color: var(--gold);
        margin-bottom: 8px;
        text-decoration: none;
    }
    .recruit-map-link:hover { text-decoration: underline; }
    .recruit-map-wrap iframe { width: 100%; height: 180px; border: 0; display: block; }
    .recruit-feature-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .recruit-feature-tag {
        padding: 6px 12px;
        background: rgba(255,255,255,0.05);
        border-radius: 20px;
        font-size: 0.75rem;
        color: var(--color-text);
        border: 1px solid rgba(255,255,255,0.08);
    }
    .recruit-message-block {
        background: rgba(212,175,55,0.06);
        border-left: 3px solid var(--gold);
        border-radius: 0 12px 12px 0;
        padding: 14px 16px;
        margin: 0;
        font-size: 0.9rem;
        color: var(--color-text);
        line-height: 1.6;
    }
    .recruit-pr-catch {
        font-size: 1.05rem;
        color: var(--color-text-header);
        font-style: italic;
        margin: 0 0 12px 0;
        line-height: 1.5;
    }
    .recruit-pr-message { margin: 0; }
</style>
@endpush

@section('content')
<div class="contents inner animate-fadeIn p-4 pb-24">
    <div class="flex justify-between items-center mb-6">
        <div class="title-area">
            <h2 class="serif-font text-2xl gold-gradient tracking-tight">{{ $recruit['store_name'] ?? '—' }}</h2>
            <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1">求人情報</p>
        </div>
        @if(empty($forCast))
        <a href="{{ route('shop.recruits.edit') }}" class="recruit-btn recruit-btn-preview" style="padding: 8px 14px; font-size: 0.75rem;">
            <i class="fas fa-pen"></i> 編集
        </a>
        @endif
    </div>

    {{-- ヒーロー：オープン日など --}}
    @if(!empty($recruit['open_date']))
    <div class="recruit-preview-hero">
        <p class="text-[10px] text-gray-500">オープン {{ $recruit['open_date'] }}</p>
    </div>
    @endif

    {{-- 給与（目立たせる） --}}
    <div class="recruit-section">
        <div class="recruit-section-head">
            <div class="recruit-section-icon"><i class="fas fa-yen-sign"></i></div>
            <h3 class="recruit-section-title">給与</h3>
        </div>
        <p class="recruit-salary-hero">¥{{ number_format($recruit['hourly_wage_regular']) }}<sub>〜/h</sub></p>
        <p class="text-xs text-gray-400 mt-1">体験時給 ¥{{ number_format($recruit['trial_hourly_wage']) }}〜</p>
        @if(!empty($recruit['salary_text']))
        <p class="text-xs text-gray-400 mt-2">{{ $recruit['salary_text'] }}</p>
        @endif
    </div>

    {{-- 場所：勤務地・最寄り・地図 --}}
    <div class="recruit-section">
        <div class="recruit-section-head">
            <div class="recruit-section-icon"><i class="fas fa-map-marker-alt"></i></div>
            <h3 class="recruit-section-title">勤務地</h3>
        </div>
        <p class="text-sm text-white mb-1">{{ $recruit['address'] ?? '—' }}</p>
        <p class="text-xs text-gray-400 mb-2"><i class="fas fa-train-subway" style="margin-right:6px;"></i>{{ $recruit['nearest_station'] ?? '—' }}</p>
        @if(!empty($recruit['map_embed_src']))
        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($recruit['address'] ?? '') }}" target="_blank" rel="noopener noreferrer" class="recruit-map-link">
            <i class="fas fa-external-link-alt"></i> マップで開く
        </a>
        <div class="recruit-map-wrap">
            <iframe src="{{ $recruit['map_embed_src'] }}" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="勤務地の地図"></iframe>
        </div>
        @endif
    </div>

    {{-- 働き方：勤務日・時間・定休 --}}
    <div class="recruit-section">
        <div class="recruit-section-head">
            <div class="recruit-section-icon"><i class="fas fa-calendar-clock"></i></div>
            <h3 class="recruit-section-title">働き方</h3>
        </div>
        <div class="recruit-info-grid">
            <div class="recruit-info-item">
                <span class="recruit-info-item-icon"><i class="fas fa-clock"></i></span>
                <div>
                    <span class="recruit-info-item-label">勤務時間</span>
                    <p class="recruit-info-item-value">{{ $recruit['working_hours'] ?? '—' }}</p>
                </div>
            </div>
            <div class="recruit-info-item">
                <span class="recruit-info-item-icon"><i class="fas fa-calendar-day"></i></span>
                <div>
                    <span class="recruit-info-item-label">勤務日</span>
                    <p class="recruit-info-item-value">{{ $recruit['working_days'] ?? '—' }}</p>
                </div>
            </div>
            <div class="recruit-info-item">
                <span class="recruit-info-item-icon"><i class="fas fa-umbrella-beach"></i></span>
                <div>
                    <span class="recruit-info-item-label">定休日</span>
                    <p class="recruit-info-item-value">{{ $recruit['regular_holiday'] ?? '—' }}</p>
                </div>
            </div>
            <div class="recruit-info-item">
                <span class="recruit-info-item-icon"><i class="fas fa-user-check"></i></span>
                <div>
                    <span class="recruit-info-item-label">採用条件</span>
                    <p class="recruit-info-item-value">{{ $recruit['qualification'] ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 仕事内容・雰囲気（短く） --}}
    <div class="recruit-section">
        <div class="recruit-section-head">
            <div class="recruit-section-icon"><i class="fas fa-handshake"></i></div>
            <h3 class="recruit-section-title">仕事・雰囲気</h3>
        </div>
        @if(!empty($recruit['job_content']))
        <p class="text-sm text-white mb-3">{{ $recruit['job_content'] }}</p>
        @endif
        @if(!empty($recruit['store_atmosphere']))
        <p class="text-xs text-gray-400">{{ $recruit['store_atmosphere'] }}</p>
        @endif
    </div>

    {{-- お店の特徴（タグで） --}}
    @if(!empty($recruit['store_features']) && is_array($recruit['store_features']))
    <div class="recruit-section">
        <div class="recruit-section-head">
            <div class="recruit-section-icon"><i class="fas fa-star"></i></div>
            <h3 class="recruit-section-title">お店の特徴</h3>
        </div>
        <div class="recruit-feature-grid">
            @foreach($recruit['store_features'] as $subTitle => $items)
                @if(is_array($items))
                    @foreach($items as $item)
                    <span class="recruit-feature-tag">{{ $item }}</span>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Benefits --}}
    <div class="recruit-section">
        <div class="recruit-section-head">
            <div class="recruit-section-icon"><i class="fas fa-gift"></i></div>
            <h3 class="recruit-section-title">Benefits</h3>
        </div>
        <div class="recruit-tag-wrap">
            @foreach($recruit['selected_benefits'] as $benefit)
            <span class="status-badge status-active">{{ $benefit }}</span>
            @endforeach
        </div>
    </div>

    {{-- PRコメント（キャッチコピー＋メッセージ） --}}
    @if(!empty($recruit['catch_copy']) || !empty($recruit['message']))
    <div class="recruit-section">
        <div class="recruit-section-head">
            <div class="recruit-section-icon"><i class="fas fa-quote-left"></i></div>
            <h3 class="recruit-section-title">PRコメント</h3>
        </div>
        <div class="recruit-message-block">
            @if(!empty($recruit['catch_copy']))
            <p class="recruit-pr-catch">"{{ $recruit['catch_copy'] }}"</p>
            @endif
            @if(!empty($recruit['message']))
            <div class="recruit-pr-message">{!! nl2br(e($recruit['message'])) !!}</div>
            @endif
        </div>
    </div>
    @endif

    @if(empty($forCast))
    <div class="mt-10 flex flex-col gap-4">
        <a href="{{ route('shop.recruits.status') }}" class="btn-gold w-full py-4 text-center no-underline">ステータス管理に戻る</a>
        <p class="text-center text-[10px] text-gray-600 uppercase tracking-widest">Luxe Lounge Premium Recruit</p>
    </div>
    @endif
</div>
@endsection
