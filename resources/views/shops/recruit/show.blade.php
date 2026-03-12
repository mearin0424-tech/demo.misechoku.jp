@extends('layouts.app')

@section('title', '求人情報')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
@endpush

@section('content')
<div class="recruit-detail-page animate-fadeIn">
    {{-- ヒーロー（画像 or グラデ＋店名・所在地） --}}
    <div class="recruit-hero-wrap">
        @if(!empty($recruit['hero_image']))
            <img src="{{ $recruit['hero_image'] }}" alt="">
        @else
            <div style="width:100%;height:100%;background:linear-gradient(135deg, #1a0c0e 0%, #2d1518 50%, #120405 100%);"></div>
        @endif
        <div class="recruit-hero-overlay"></div>
        <div class="recruit-hero-body">
            @if(!empty($recruit['open_date']))
                <span class="recruit-hero-badge">オープン {{ $recruit['open_date'] }}</span>
            @else
                <span class="recruit-hero-badge">{{ $recruit['store_genre'] ?? '求人' }}</span>
            @endif
            <h1 class="recruit-hero-title">{{ $recruit['store_name'] ?? '—' }}</h1>
            <p class="recruit-hero-location">
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ $recruit['address'] ?? $recruit['nearest_station'] ?? '—' }}</span>
            </p>
        </div>
    </div>

    <div class="px-0">
        {{-- 給与ハイライト --}}
        <section class="recruit-salary-block">
            @if(!empty($recruit['catch_copy']))
                <p class="recruit-salary-catch">
                    {!! nl2br(e($recruit['catch_copy'])) !!}
                    @if(!empty($recruit['open_date']))
                        <br><span class="highlight">オープン日 {{ $recruit['open_date'] }}</span>
                    @endif
                </p>
            @endif
            <div class="recruit-salary-main">
                <span class="recruit-salary-label">基本時給</span>
                <div class="recruit-salary-amount">
                    <span class="currency">¥</span>
                    <span class="value">{{ number_format($recruit['hourly_wage_regular']) }}</span>
                    <span class="range">~</span>
                </div>
                <span class="recruit-salary-sub">＋ 各種バック</span>
                @if(!empty($recruit['trial_hourly_wage']))
                    <p class="text-sm mt-2" style="color:#A89090;">体験時給 ¥{{ number_format($recruit['trial_hourly_wage']) }}〜</p>
                @endif
            </div>
            @if(!empty($recruit['selected_benefits']) && is_array($recruit['selected_benefits']))
                <div class="recruit-feature-tags">
                    @foreach(array_slice($recruit['selected_benefits'], 0, 6) as $benefit)
                        <span class="recruit-feature-tag-new"><i class="fas fa-check-circle"></i> {{ $benefit }}</span>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- お店からのメッセージ --}}
        @if(!empty($recruit['message']) || !empty($recruit['job_content']))
            <section class="recruit-message-block-new">
                <h3 class="recruit-block-title"><i class="fas fa-sparkles"></i> お店からのメッセージ</h3>
                <div class="recruit-message-block-new">
                    @if(!empty($recruit['message']))
                        <p>{!! nl2br(e($recruit['message'])) !!}</p>
                    @endif
                    @if(!empty($recruit['job_content']))
                        <p>{{ $recruit['job_content'] }}</p>
                    @endif
                </div>
            </section>
        @endif

        {{-- 募集要項 --}}
        <section>
            <h3 class="recruit-block-title"><i class="fas fa-file-alt"></i> 募集要項</h3>
            <div class="recruit-table-wrap">
                <table class="recruit-table">
                    <tbody>
                        <tr>
                            <th>職種</th>
                            <td>フロアレディ <span class="sub">（キャバクラ・クラブ）</span></td>
                        </tr>
                        <tr>
                            <th>資格</th>
                            <td>{{ $recruit['qualification'] ?? '18歳以上（高校生不可）' }}<span class="sub">※未経験者大歓迎</span></td>
                        </tr>
                        <tr>
                            <th>営業時間</th>
                            <td>{{ $recruit['working_hours'] ?? '—' }}<span class="sub">（週1日・1日3h〜OK）</span></td>
                        </tr>
                        <tr>
                            <th>待遇</th>
                            <td>
                                @if(!empty($recruit['selected_benefits']) && is_array($recruit['selected_benefits']))
                                    @foreach($recruit['selected_benefits'] as $b)
                                       ・{{ $b }}<br>
                                    @endforeach
                                @else
                                    {{ $recruit['salary_text'] ?? '—' }}
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- 勤務時間・勤務日 --}}
        <div class="recruit-grid-2">
            <div class="recruit-mini-card">
                <div class="label"><i class="fas fa-clock"></i> 勤務時間</div>
                <div class="value">{{ $recruit['working_hours'] ?? '—' }}</div>
            </div>
            <div class="recruit-mini-card">
                <div class="label"><i class="fas fa-calendar-day"></i> 勤務日</div>
                <div class="value">{{ $recruit['working_days'] ?? '—' }}<br><span class="text-sm" style="color:#A89090;">{{ $recruit['regular_holiday'] ?? '' }}</span></div>
            </div>
        </div>

        {{-- 勤務地 --}}
        <section class="recruit-location-block">
            <div class="label"><i class="fas fa-map-marker-alt"></i> 勤務地</div>
            <p class="recruit-location-address">{{ $recruit['address'] ?? '—' }}</p>
            @if(!empty($recruit['nearest_station']))
                <p class="recruit-location-station"><i class="fas fa-train-subway"></i> {{ $recruit['nearest_station'] }}</p>
            @endif
            @if(!empty($recruit['map_embed_src']))
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($recruit['address'] ?? '') }}" target="_blank" rel="noopener noreferrer" class="recruit-map-link" style="display:inline-flex;align-items:center;gap:6px;font-size:0.8rem;color:var(--gold);margin-bottom:10px;">
                    <i class="fas fa-external-link-alt"></i> マップで開く
                </a>
                <div class="recruit-map-placeholder">
                    <iframe src="{{ $recruit['map_embed_src'] }}" style="position:absolute;inset:0;width:100%;height:100%;border:0;" allowfullscreen="" loading="lazy" title="勤務地の地図"></iframe>
                    <span class="pin"><i class="fas fa-map-marker-alt"></i></span>
                </div>
            @else
                <div class="recruit-map-placeholder">
                    <span class="pin"><i class="fas fa-map-marker-alt"></i></span>
                </div>
            @endif
        </section>

        @if(empty($forCast))
            <div class="mt-8 text-center">
                <a href="{{ route('shop.recruits.status') }}" class="recruit-cta-btn" style="max-width:320px;margin:0 auto;">
                    <i class="fas fa-list"></i> ステータス管理に戻る
                </a>
            </div>
        @endif
    </div>

    @if(!empty($forCast))
        {{-- キャスト向け：固定フッターで応募 --}}
        <div class="recruit-footer-cta">
            <button type="button" class="recruit-cta-heart" aria-label="キープ"><i class="far fa-heart"></i></button>
            <a href="#" class="recruit-cta-btn"><i class="fas fa-paper-plane"></i> 応募する</a>
        </div>
    @endif
</div>
@endsection
