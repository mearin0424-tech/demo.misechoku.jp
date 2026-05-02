@extends('layouts.app')

@section('title', ($recruit['store_name'] ?? ($shop['name'] ?? '店舗')) . 'の求人情報')
@section('meta_description', trim((string) (($recruit['catch_copy'] ?? '') ?: ($recruit['message'] ?? 'ミセチョクの求人情報です。'))))
@section('meta_image', $shop['main_img'] ?? ($recruit['hero_image'] ?? asset('assets/images/common/no-image.png')))
@section('canonical', $shareUrl ?? url()->current())
@section('guide_message', empty($forCast) ? '表示の見え方をご確認いただきながら、時給・勤務条件・メッセージが適切に伝わっているかご確認ください。気になる点がございましたら、そのまま編集画面へお戻りいただけます。' : '')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<style>
    .recruit-ref-shell { max-width: 28rem; margin: 0 auto; min-height: 100vh; background: #0a0a0a; box-shadow: 0 25px 50px -12px rgba(0,0,0,.5); position: relative; }
    .recruit-ref-wrap { padding-bottom: calc(var(--footer-height, 0px) + 96px); background: #050505; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) {
        .recruit-ref-wrap { padding-bottom: calc(var(--footer-height, 0px) + 96px + env(safe-area-inset-bottom)); }
    }

    .recruit-ref-preview-bar { background: #1a1510; padding: 8px 12px; border-bottom: 1px solid #2a2015; }
    .recruit-ref-preview-bar > p { margin: 0 0 8px; font-size: 11px; color: #d4af37; font-weight: 700; }
    .recruit-ref-preview-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; justify-content: space-between; }
    .recruit-ref-preview-links { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .recruit-ref-preview-links a { color: #d4af37; border: 1px solid rgba(212,175,55,.45); border-radius: 999px; padding: 6px 12px; font-size: 11px; font-weight: 700; text-decoration: none; }
    .recruit-ref-preview-links a:hover { background: rgba(212,175,55,.08); }

    .recruit-ref-publish-form { margin: 0; }
    .recruit-ref-publish-btn {
        display: inline-flex; align-items: center; gap: 8px; border-radius: 999px; padding: 8px 14px; font-size: 11px; font-weight: 800;
        cursor: pointer; border: 1px solid rgba(212,175,55,.5); background: #141210; color: #e8e0d8;
    }
    .recruit-ref-publish-btn.is-active { border-color: rgba(34,197,94,.45); color: #bbf7d0; }
    .recruit-ref-publish-btn.is-inactive { opacity: .95; }

    .recruit-ref-hero-wrap { position: relative; }
    .recruit-ref-hero { position: relative; margin: 0; height: 18rem; overflow: hidden; background: #18181b; }
    .recruit-ref-hero img { width: 100%; height: 100%; object-fit: cover; opacity: .88; }
    .recruit-ref-hero-overlay { position: absolute; inset: 0; background: linear-gradient(to top, #0a0a0a 0%, rgba(10,10,10,.45) 50%, transparent 100%); pointer-events: none; }
    .recruit-ref-float-actions { position: absolute; top: 10px; left: 12px; right: 12px; display: flex; justify-content: space-between; z-index: 12; pointer-events: none; }
    .recruit-ref-float-btn { pointer-events: auto; width: 2rem; height: 2rem; border-radius: 999px; border: 1px solid rgba(255,255,255,.1); background: rgba(0,0,0,.5); color: #fff; display: flex; align-items: center; justify-content: center; text-decoration: none; backdrop-filter: blur(6px); }
    .recruit-ref-thumbs { position: absolute; right: 12px; bottom: 12px; display: flex; gap: 6px; z-index: 12; align-items: center; }
    .recruit-ref-thumbs img { width: 2.5rem; height: 2.5rem; border-radius: 8px; border: 1px solid rgba(212,175,55,.5); object-fit: cover; }
    .recruit-ref-thumb-more { width: 2.5rem; height: 2.5rem; border-radius: 8px; border: 1px solid #2a2015; background: rgba(0,0,0,.55); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; color: #fff; }

    .recruit-ref-head { padding: 8px 1.25rem 1.25rem; }
    .recruit-ref-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
    .recruit-ref-chip { font-size: 10px; padding: 2px 8px; border-radius: 2px; font-weight: 700; background: #27272a; color: #d4d4d8; }
    .recruit-ref-chip.gold { background: rgba(212,175,55,.1); color: #d4af37; border: 1px solid rgba(212,175,55,.3); }
    .recruit-ref-title { margin: 0 0 16px; font-size: 1.5rem; line-height: 1.25; font-weight: 800; color: #fff; letter-spacing: .02em; }

    .recruit-job-toggle { background: #110f0d; padding: 4px; border-radius: 8px; display: flex; border: 1px solid #2a2015; margin-bottom: 16px; }
    .recruit-job-toggle button { flex: 1; border: none; background: transparent; color: #71717a; padding: 8px 4px; font-size: 12px; font-weight: 800; border-radius: 6px; cursor: pointer; transition: color .15s, background .15s; }
    .recruit-job-toggle button.is-active { background: #2a2210; color: #d4af37; box-shadow: 0 1px 2px rgba(0,0,0,.2); }
    .recruit-job-toggle button:disabled { opacity: .45; cursor: not-allowed; }

    .recruit-ref-pay-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }
    .recruit-ref-pay-card { border-radius: 8px; padding: 12px; border: 1px solid #2a2015; background: #141210; }
    .recruit-ref-pay-card.gold { background: linear-gradient(to bottom right, #1a140a, #0a0804); border-color: rgba(212,175,55,.4); }
    .recruit-ref-pay-card .label { font-size: 10px; font-weight: 800; color: #a1a1aa; margin-bottom: 4px; display: block; }
    .recruit-ref-pay-card.gold .label { color: #d4af37; }
    .recruit-ref-pay-yen { color: #d4af37; font-weight: 800; font-size: .875rem; vertical-align: baseline; }
    .recruit-ref-pay-num { font-size: 1.5rem; font-weight: 800; color: #fff; letter-spacing: -0.02em; }
    .recruit-ref-pay-card.sm .recruit-ref-pay-num { font-size: 1.25rem; }
    .recruit-ref-pay-suffix { font-size: 10px; color: #a1a1aa; }

    .recruit-ref-bonus-hero {
        margin-bottom: 16px; padding: 16px; border-radius: 12px;
        background: linear-gradient(135deg, rgba(212,175,55,.12) 0%, rgba(10,8,4,.95) 50%, #0a0a0a 100%);
        border: 1px solid rgba(212,175,55,.45);
    }
    .recruit-ref-bonus-hero .label { font-size: 10px; font-weight: 800; color: #d4af37; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .06em; }
    .recruit-ref-bonus-amount { font-size: 2rem; font-weight: 800; color: #fff; letter-spacing: -0.03em; line-height: 1.1; }
    .recruit-ref-bonus-amount span.yen { color: #d4af37; font-size: 1.25rem; margin-right: 2px; }
    .recruit-ref-bonus-list { margin: 12px 0 0; padding: 0; list-style: none; font-size: 12px; color: #d4d4d8; line-height: 1.55; }
    .recruit-ref-bonus-list li { padding-left: 1em; text-indent: -0.6em; margin-bottom: 4px; }
    .recruit-ref-bonus-list li::before { content: '✓'; color: #d4af37; margin-right: 6px; font-weight: 800; }

    .recruit-ref-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .recruit-ref-tags span { font-size: 10px; padding: 4px 10px; border-radius: 999px; font-weight: 700; border: 1px solid #3a2a18; background: #1a1714; color: #d4d4d8; }
    .recruit-ref-tags span.gold { border-color: rgba(212,175,55,.5); background: rgba(212,175,55,.2); color: #d4af37; }

    .recruit-ref-tab { position: sticky; top: 0; z-index: 40; background: rgba(10,10,10,.95); backdrop-filter: blur(10px); border-top: 1px solid #1f1a14; border-bottom: 1px solid #1f1a14; display: flex; padding: 0 8px; }
    .recruit-ref-tab button { flex: 1; border: none; background: transparent; color: #71717a; padding: 12px 4px; font-size: 11px; font-weight: 800; cursor: pointer; transition: color .15s; }
    .recruit-ref-tab button.is-active { color: #d4af37; border-bottom: 2px solid #d4af37; margin-bottom: -1px; }

    .recruit-ref-body { padding: 1.25rem; display: flex; flex-direction: column; gap: 2.5rem; padding-bottom: 2rem; }

    .recruit-ref-h2 { margin: 0 0 12px; font-size: 0.875rem; font-weight: 800; color: #d4af37; display: flex; align-items: center; gap: 8px; }
    .recruit-ref-h2-lg { margin: 0 0 16px; font-size: 1.125rem; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 8px; }
    .recruit-ref-h2-lg .bar { width: 4px; height: 1.25rem; background: #d4af37; border-radius: 1px; display: block; }

    .recruit-ref-msg { font-size: 0.875rem; color: #d4d4d8; line-height: 1.75; font-weight: 500; background: #110f0d; padding: 16px; border-radius: 12px; border: 1px solid #1f1a14; }
    .recruit-ref-msg p:last-child { margin-bottom: 0; }

    .recruit-ref-card { background: #110f0d; border-radius: 12px; border: 1px solid #1f1a14; padding: 16px; margin-bottom: 16px; }
    .recruit-ref-card p:last-child { margin-bottom: 0; }

    .recruit-ref-inforow { display: flex; padding: 14px 0; border-bottom: 1px solid #1f1a14; font-size: 0.875rem; }
    .recruit-ref-inforow:last-child { border-bottom: none; }
    .recruit-ref-inforow .k { width: 6.5rem; flex-shrink: 0; font-size: 11px; font-weight: 800; color: #71717a; padding-top: 2px; }
    .recruit-ref-inforow .v { flex: 1; color: #e4e4e7; font-weight: 600; line-height: 1.6; }

    .recruit-ref-map-placeholder {
        width: 100%; height: 10rem; border-radius: 8px; background: #18181b; border: 1px solid #2a2015;
        display: flex; align-items: center; justify-content: center; color: #52525b; margin-bottom: 12px; position: relative; overflow: hidden;
    }
    .recruit-ref-map-placeholder i { font-size: 2rem; color: #d4af37; z-index: 1; filter: drop-shadow(0 2px 8px rgba(0,0,0,.5)); }

    .recruit-ref-shop-tags-title { font-size: 11px; font-weight: 800; color: #a1a1aa; margin: 0 0 10px; }
    .recruit-ref-shop-tag-group { margin-bottom: 14px; }
    .recruit-ref-shop-tag-group:last-child { margin-bottom: 0; }
    .recruit-ref-shop-tag-label { font-size: 10px; font-weight: 800; color: #d4af37; margin-bottom: 6px; display: block; }
    .recruit-ref-shop-tag-pills { display: flex; flex-wrap: wrap; gap: 6px; }
    .recruit-ref-shop-tag-pills span { font-size: 11px; padding: 4px 10px; border-radius: 999px; background: #1a1714; border: 1px solid #3a2a18; color: #e4e4e7; }

    .recruit-ref-concept .label { font-size: 11px; font-weight: 800; color: #a1a1aa; margin-bottom: 8px; }
    .recruit-ref-concept .body { font-size: 0.875rem; color: #d4d4d8; line-height: 1.75; }

    .recruit-ref-fixed-footer {
        position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 28rem;
        z-index: 50; background: rgba(10,10,10,.95); backdrop-filter: blur(10px); border-top: 1px solid #1f1a14;
        padding: 12px 16px;
        padding-bottom: calc(12px + env(safe-area-inset-bottom, 0px));
        display: flex; justify-content: center;
    }
    .recruit-ref-fixed-footer a {
        width: 100%; max-width: 240px; border: 1px solid #d4af37; color: #d4af37; border-radius: 999px; padding: 12px 16px; font-size: 0.875rem; font-weight: 800;
        text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: background .15s;
    }
    .recruit-ref-fixed-footer a:hover { background: rgba(212,175,55,.1); }

    .recruit-ref-flash { font-size: 11px; color: #86efac; margin-top: 6px; }
</style>
@endpush

@section('content')
@php
    $hasHelp = !empty($recruit['help_hourly_wage']);
    $hasTrial = !empty($recruit['trial_hourly_wage']);
    $regularWage = (int) ($recruit['hourly_wage_regular'] ?? 0);
    $noruma = (int) ($recruit['noruma_reward'] ?? 0);
    $bonusDays = trim((string) ($recruit['bonus_total_working_days'] ?? $recruit['bonus_working_days'] ?? ''));
    $bonusHours = trim((string) ($recruit['bonus_total_working_hours'] ?? $recruit['bonus_working_hours'] ?? ''));
    $bonusExtra = trim((string) ($recruit['bonus_other_conditions'] ?? $recruit['bonus_condition'] ?? ''));
    $showBonusBlock = $noruma > 0 || $bonusDays !== '' || $bonusHours !== '' || $bonusExtra !== '';
    $salaryTags = collect($recruit['store_features']['報酬'] ?? [])->values();
    $otherTags = collect($recruit['store_features'] ?? [])->except('報酬')->flatten()->filter()->unique()->values();
    $subImages = $shop['sub_images'] ?? [];
    $thumbMore = max(0, count($subImages) - 2);
    $addressLine = trim((string) ($recruit['address'] ?? ''));
    if ($addressLine === '') {
        $addressLine = trim(($shop['pref'] ?? '') . ($shop['city'] ?? '') . ($shop['addr1'] ?? ''));
    }
    $isPublishActive = (($recruit['status'] ?? 'active') === 'active');
    $tagGroups = $shop['tag_groups'] ?? [];
    $showMainWage = $regularWage > 0 || $hasTrial;
@endphp

<div class="recruit-detail-page animate-fadeIn recruit-ref-wrap">
    <div class="recruit-ref-shell">

        @if(empty($forCast))
            <div class="recruit-ref-preview-bar">
                <p>求人票プレビュー（求職者からの見え方）</p>
                <div class="recruit-ref-preview-actions">
                    <form method="post" action="{{ route('shop.recruits.toggle-status') }}" class="recruit-ref-publish-form">
                        @csrf
                        <button type="submit" class="recruit-ref-publish-btn {{ $isPublishActive ? 'is-active' : 'is-inactive' }}" title="タップで公開／非公開を切り替えます">
                            <span aria-hidden="true">{{ $isPublishActive ? '●' : '○' }}</span>
                            {{ $isPublishActive ? '求人は公開中' : '求人は非公開' }}
                        </button>
                    </form>
                    <div class="recruit-ref-preview-links">
                        <a href="{{ route('shop.recruits.edit') }}"><i class="fas fa-pen"></i> 編集</a>
                        <a href="#top"><i class="fas fa-arrow-up"></i> ページトップ</a>
                    </div>
                </div>
                @if(session('message'))
                    <p class="recruit-ref-flash" role="status">{{ session('message') }}</p>
                @endif
            </div>
        @endif

        <div class="recruit-ref-hero-wrap">
            <div class="recruit-ref-float-actions">
                <a href="javascript:history.back()" class="recruit-ref-float-btn" aria-label="戻る"><i class="fas fa-chevron-left"></i></a>
                @if(!empty($shareUrl))
                    <button type="button" class="recruit-ref-float-btn" id="recruit-share-top-btn" aria-label="共有"><i class="fas fa-share-nodes"></i></button>
                @else
                    <span></span>
                @endif
            </div>

            <div class="recruit-ref-hero" id="top">
                @if(!empty($shop['main_img'] ?? null))
                    <img src="{{ $shop['main_img'] }}" alt="{{ $recruit['store_name'] ?? ($shop['name'] ?? '') }}" class="js-lightbox-target">
                @elseif(!empty($recruit['hero_image']))
                    <img src="{{ $recruit['hero_image'] }}" alt="{{ $recruit['store_name'] ?? '' }}" class="js-lightbox-target">
                @else
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a0c0e 0%,#2d1518 50%,#120405 100%);"></div>
                @endif
                <div class="recruit-ref-hero-overlay"></div>
                @if(!empty($subImages))
                    <div class="recruit-ref-thumbs">
                        @foreach(collect($subImages)->take(2) as $img)
                            <img src="{{ $img }}" alt="" class="js-lightbox-target">
                        @endforeach
                        @if($thumbMore > 0)
                            <span class="recruit-ref-thumb-more">+{{ $thumbMore }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="recruit-ref-head">
            <div class="recruit-ref-chips">
                @if(!empty($shop['area'] ?? null))
                    <span class="recruit-ref-chip">{{ $shop['area'] }}</span>
                @endif
                @if(!empty($shop['industry_name'] ?? null))
                    <span class="recruit-ref-chip gold">{{ $shop['industry_name'] }}</span>
                @endif
            </div>

            <h1 class="recruit-ref-title">{{ $recruit['store_name'] ?? ($shop['name'] ?? '—') }}</h1>

            @if($hasHelp)
                <div class="recruit-job-toggle" id="recruit-job-toggle" role="tablist" aria-label="募集枠">
                    <button type="button" class="is-active" data-job-type="main">体験入店・本入店</button>
                    <button type="button" data-job-type="help">ヘルプ</button>
                </div>
            @endif

            {{-- メイン枠: 時給ハイライト --}}
            <div id="recruit-panel-main" data-job-panel="main">
                @if($hasHelp)
                    @if($hasTrial && $regularWage > 0)
                        <div class="recruit-ref-pay-grid">
                            <div class="recruit-ref-pay-card gold">
                                <span class="label">体験時給</span>
                                <div>
                                    <span class="recruit-ref-pay-yen">¥</span><span class="recruit-ref-pay-num">{{ number_format((int) $recruit['trial_hourly_wage']) }}</span>
                                    <span class="recruit-ref-pay-suffix">〜</span>
                                </div>
                            </div>
                            <div class="recruit-ref-pay-card sm">
                                <span class="label">本入時給</span>
                                <div>
                                    <span class="recruit-ref-pay-yen">¥</span><span class="recruit-ref-pay-num">{{ number_format($regularWage) }}</span>
                                    <span class="recruit-ref-pay-suffix">〜</span>
                                </div>
                            </div>
                        </div>
                    @elseif($regularWage > 0)
                        <div class="recruit-ref-pay-grid" style="grid-template-columns:1fr;">
                            <div class="recruit-ref-pay-card gold">
                                <span class="label">本入時給</span>
                                <div>
                                    <span class="recruit-ref-pay-yen">¥</span><span class="recruit-ref-pay-num">{{ number_format($regularWage) }}</span>
                                    <span class="recruit-ref-pay-suffix">〜</span>
                                </div>
                            </div>
                        </div>
                    @elseif($hasTrial)
                        <div class="recruit-ref-pay-grid" style="grid-template-columns:1fr;">
                            <div class="recruit-ref-pay-card gold">
                                <span class="label">体験時給</span>
                                <div>
                                    <span class="recruit-ref-pay-yen">¥</span><span class="recruit-ref-pay-num">{{ number_format((int) $recruit['trial_hourly_wage']) }}</span>
                                    <span class="recruit-ref-pay-suffix">〜</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    @if($hasTrial && $regularWage > 0)
                        <div class="recruit-ref-pay-grid">
                            <div class="recruit-ref-pay-card gold">
                                <span class="label">体験時給</span>
                                <div>
                                    <span class="recruit-ref-pay-yen">¥</span><span class="recruit-ref-pay-num">{{ number_format((int) $recruit['trial_hourly_wage']) }}</span>
                                    <span class="recruit-ref-pay-suffix">〜</span>
                                </div>
                            </div>
                            <div class="recruit-ref-pay-card sm">
                                <span class="label">本入時給</span>
                                <div>
                                    <span class="recruit-ref-pay-yen">¥</span><span class="recruit-ref-pay-num">{{ number_format($regularWage) }}</span>
                                    <span class="recruit-ref-pay-suffix">〜</span>
                                </div>
                            </div>
                        </div>
                    @elseif($regularWage > 0)
                        <div class="recruit-ref-pay-grid" style="grid-template-columns:1fr;">
                            <div class="recruit-ref-pay-card gold">
                                <span class="label">本入時給</span>
                                <div>
                                    <span class="recruit-ref-pay-yen">¥</span><span class="recruit-ref-pay-num">{{ number_format($regularWage) }}</span>
                                    <span class="recruit-ref-pay-suffix">〜</span>
                                </div>
                            </div>
                        </div>
                    @elseif($hasTrial)
                        <div class="recruit-ref-pay-grid" style="grid-template-columns:1fr;">
                            <div class="recruit-ref-pay-card gold">
                                <span class="label">体験時給</span>
                                <div>
                                    <span class="recruit-ref-pay-yen">¥</span><span class="recruit-ref-pay-num">{{ number_format((int) $recruit['trial_hourly_wage']) }}</span>
                                    <span class="recruit-ref-pay-suffix">〜</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                @if(!$showMainWage)
                    <p class="recruit-ref-msg" style="margin-bottom:12px;padding:10px 14px;font-size:13px;">本入・体験の時給は求人編集から入力してください。</p>
                @endif

                @if($showBonusBlock)
                    <div class="recruit-ref-bonus-hero" aria-labelledby="recruit-bonus-label">
                        <div class="label" id="recruit-bonus-label">ボーナス金（達成時に支給）</div>
                        <div class="recruit-ref-bonus-amount">
                            @if($noruma > 0)
                                <span class="yen">¥</span>{{ number_format($noruma) }}
                            @else
                                <span style="font-size:1rem;font-weight:700;color:#a1a1aa;">金額はお店にお問い合わせください</span>
                            @endif
                        </div>
                        @if($bonusDays !== '' || $bonusHours !== '' || $bonusExtra !== '')
                            <ul class="recruit-ref-bonus-list">
                                @if($bonusDays !== '')
                                    <li>累計勤務日数: {{ $bonusDays }}日以上</li>
                                @endif
                                @if($bonusHours !== '')
                                    <li>累計勤務時間: {{ $bonusHours }}時間以上</li>
                                @endif
                                @if($bonusExtra !== '')
                                    <li>{!! nl2br(e($bonusExtra)) !!}</li>
                                @endif
                            </ul>
                        @endif
                    </div>
                @endif
            </div>

            {{-- ヘルプ枠 --}}
            @if($hasHelp)
                <div id="recruit-panel-help" data-job-panel="help" hidden>
                    <div class="recruit-ref-pay-grid" style="grid-template-columns:1fr; margin-bottom: 12px;">
                        <div class="recruit-ref-pay-card gold" style="text-align:center;">
                            <span class="label">ヘルプ時給</span>
                            <div style="justify-content:center;display:flex;align-items:baseline;gap:2px;flex-wrap:wrap;">
                                <span class="recruit-ref-pay-yen">¥</span>
                                <span class="recruit-ref-pay-num" style="font-size:1.875rem;">{{ number_format((int) $recruit['help_hourly_wage']) }}</span>
                                <span class="recruit-ref-pay-suffix">〜</span>
                            </div>
                        </div>
                    </div>
                    @if($showBonusBlock)
                        <div class="recruit-ref-bonus-hero">
                            <div class="label">ボーナス金（達成時に支給）</div>
                            <div class="recruit-ref-bonus-amount">
                                @if($noruma > 0)
                                    <span class="yen">¥</span>{{ number_format($noruma) }}
                                @else
                                    <span style="font-size:1rem;font-weight:700;color:#a1a1aa;">金額はお店にお問い合わせください</span>
                                @endif
                            </div>
                            @if($bonusDays !== '' || $bonusHours !== '' || $bonusExtra !== '')
                                <ul class="recruit-ref-bonus-list">
                                    @if($bonusDays !== '')
                                        <li>累計勤務日数: {{ $bonusDays }}日以上</li>
                                    @endif
                                    @if($bonusHours !== '')
                                        <li>累計勤務時間: {{ $bonusHours }}時間以上</li>
                                    @endif
                                    @if($bonusExtra !== '')
                                        <li>{!! nl2br(e($bonusExtra)) !!}</li>
                                    @endif
                                </ul>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <div class="recruit-ref-tags" aria-label="特徴タグ">
                @foreach($salaryTags->take(8) as $tag)
                    <span class="gold">#{{ $tag }}</span>
                @endforeach
                @foreach($otherTags->take(12) as $tag)
                    <span>#{{ $tag }}</span>
                @endforeach
            </div>
        </div>

        <div class="recruit-ref-tab" id="recruit-ref-tab">
            <button type="button" data-tab-target="top" class="is-active">トップ</button>
            <button type="button" data-tab-target="requirements">募集要項</button>
            <button type="button" data-tab-target="info">店舗情報</button>
        </div>

        <div class="recruit-ref-body">

            <section id="section-message">
                <h2 class="recruit-ref-h2"><i class="fas fa-comment-dots"></i> お店からのメッセージ</h2>
                <div class="recruit-ref-msg">
                    @if(!empty($recruit['message']))
                        <p>{!! nl2br(e($recruit['message'])) !!}</p>
                    @elseif(!empty($recruit['catch_copy']))
                        <p>{!! nl2br(e($recruit['catch_copy'])) !!}</p>
                    @else
                        <p style="opacity:.65;">メッセージは求人編集から入力できます。</p>
                    @endif
                </div>
            </section>

            @if(!empty($shareUrl))
                @include('common.share-actions', [
                    'shareUrl' => $shareUrl,
                    'shareTitle' => $shareTitle ?? (($recruit['store_name'] ?? ($shop['name'] ?? '店舗')) . 'の求人情報'),
                    'shareText' => $shareText ?? ($recruit['message'] ?? ''),
                    'shareLabel' => 'この求人票をSNSで共有'
                ])
            @endif

            <section id="requirements">
                <h2 class="recruit-ref-h2-lg"><span class="bar" aria-hidden="true"></span> 募集要項 <span id="recruit-req-sub" style="font-size:11px;font-weight:600;color:#71717a;margin-left:8px;">（体験・本入店）</span></h2>

                <div id="recruit-req-main">
                    <div class="recruit-ref-card">
                        <p style="font-size:0.875rem;font-weight:800;color:#fafafa;margin:0 0 8px;">キャスト募集</p>
                        @if(!empty($recruit['job_content']))
                            <p style="font-size:0.75rem;color:#a1a1aa;line-height:1.65;margin:0;">{!! nl2br(e($recruit['job_content'])) !!}</p>
                        @else
                            <p style="font-size:0.75rem;color:#71717a;margin:0;">仕事内容は求人編集から入力できます。</p>
                        @endif
                    </div>

                    <div class="recruit-ref-inforow"><span class="k">給与</span><span class="v">
                        @if($regularWage > 0)
                            <span style="color:#d4af37;font-weight:800;">本入: ¥{{ number_format($regularWage) }}〜</span>
                        @endif
                        @if($hasTrial)
                            @if($regularWage > 0)<br>@endif
                            <span style="color:#d4af37;font-weight:800;">体入: ¥{{ number_format((int) $recruit['trial_hourly_wage']) }}〜</span>
                        @endif
                        @if(!$regularWage && !$hasTrial)
                            要相談（求人編集で入力）
                        @endif
                    </span></div>
                    <div class="recruit-ref-inforow"><span class="k">給与システム</span><span class="v">@if(!empty($recruit['salary_text'])){!! nl2br(e($recruit['salary_text'])) !!}@else — @endif</span></div>
                    <div class="recruit-ref-inforow"><span class="k">勤務時間</span><span class="v">{{ $recruit['working_hours'] ?: '—' }}</span></div>
                    <div class="recruit-ref-inforow"><span class="k">勤務日・シフト</span><span class="v">{{ $recruit['working_days'] ?: '—' }}</span></div>
                    <div class="recruit-ref-inforow"><span class="k">応募資格</span><span class="v">{{ $recruit['qualification'] ?? '—' }}</span></div>
                    <div class="recruit-ref-inforow"><span class="k">ボーナス金</span><span class="v">
                        @if($noruma > 0)
                            <strong style="color:#d4af37;">¥{{ number_format($noruma) }}</strong>
                        @else
                            —
                        @endif
                        @if($bonusDays !== '' || $bonusHours !== '' || $bonusExtra !== '')
                            <br><span style="font-size:12px;color:#a1a1aa;font-weight:600;">
                                @if($bonusDays !== '')勤務日数: {{ $bonusDays }}日以上 @endif
                                @if($bonusHours !== '')／ 勤務時間: {{ $bonusHours }}h以上 @endif
                                @if($bonusExtra !== '')<br>{!! nl2br(e($bonusExtra)) !!}@endif
                            </span>
                        @endif
                    </span></div>
                    <div class="recruit-ref-inforow"><span class="k">控除</span><span class="v">源泉所得税 10.21%（法令に基づく控除）</span></div>
                </div>

                @if($hasHelp)
                    <div id="recruit-req-help" hidden>
                        <div class="recruit-ref-card">
                            <p style="font-size:0.875rem;font-weight:800;color:#fafafa;margin:0 0 8px;">ヘルプ募集</p>
                            @if(!empty($recruit['help_job_content']))
                                <p style="font-size:0.75rem;color:#a1a1aa;line-height:1.65;margin:0;">{!! nl2br(e($recruit['help_job_content'])) !!}</p>
                            @else
                                <p style="font-size:0.75rem;color:#71717a;margin:0;">ヘルプの仕事内容は求人編集から入力できます。</p>
                            @endif
                        </div>
                        <div class="recruit-ref-inforow"><span class="k">給与</span><span class="v"><span style="color:#d4af37;font-weight:800;">¥{{ number_format((int) $recruit['help_hourly_wage']) }}〜</span></span></div>
                        <div class="recruit-ref-inforow"><span class="k">給与システム</span><span class="v">@if(!empty($recruit['salary_text'])){!! nl2br(e($recruit['salary_text'])) !!}@else 時給制 @endif</span></div>
                        <div class="recruit-ref-inforow"><span class="k">勤務時間</span><span class="v">{{ $recruit['working_hours'] ?: '—' }}</span></div>
                        <div class="recruit-ref-inforow"><span class="k">勤務日・シフト</span><span class="v">{{ $recruit['working_days'] ?: '—' }}</span></div>
                        <div class="recruit-ref-inforow"><span class="k">応募資格</span><span class="v">{{ $recruit['qualification'] ?? '—' }}</span></div>
                        <div class="recruit-ref-inforow"><span class="k">ボーナス金</span><span class="v">
                            @if($noruma > 0)<strong style="color:#d4af37;">¥{{ number_format($noruma) }}</strong>@else — @endif
                        </span></div>
                        <div class="recruit-ref-inforow"><span class="k">控除</span><span class="v">源泉所得税 10.21%（法令に基づく控除）</span></div>
                    </div>
                @endif
            </section>

            <section id="info">
                <h2 class="recruit-ref-h2-lg"><span class="bar" aria-hidden="true"></span> 店舗情報</h2>

                <div class="recruit-ref-inforow"><span class="k">店名</span><span class="v">{{ $shop['name'] ?? ($recruit['store_name'] ?? '—') }}</span></div>
                <div class="recruit-ref-inforow"><span class="k">業種</span><span class="v">{{ $shop['industry_name'] ?? '未設定' }}</span></div>
                <div class="recruit-ref-inforow"><span class="k">郵便番号</span><span class="v">{{ ($shop['zip'] ?? '') !== '' ? $shop['zip'] : '—' }}</span></div>
                <div class="recruit-ref-inforow"><span class="k">住所</span><span class="v">{{ $addressLine !== '' ? $addressLine : '—' }}</span></div>
                @if(!empty($shop['nearest_station'] ?? null))
                    <div class="recruit-ref-inforow"><span class="k">最寄り</span><span class="v">{{ $shop['nearest_station'] }}</span></div>
                @endif
                @if(!empty($recruit['working_hours']) || !empty($recruit['working_days']) || !empty($recruit['regular_holiday']))
                    <div class="recruit-ref-inforow"><span class="k">勤務・休日</span><span class="v">
                        @if(!empty($recruit['working_hours'])){{ $recruit['working_hours'] }}@else時間未設定@endif
                        ／
                        @if(!empty($recruit['working_days'])){{ $recruit['working_days'] }}@else勤務日未設定@endif
                        @if(!empty($recruit['regular_holiday']))
                            <br>定休: {{ $recruit['regular_holiday'] }}
                        @endif
                    </span></div>
                @endif

                @if(!empty($tagGroups))
                    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #1f1a14;">
                        <p class="recruit-ref-shop-tags-title">特徴・アピールタグ（マスタ）</p>
                        @foreach($tagGroups as $group)
                            <div class="recruit-ref-shop-tag-group">
                                <span class="recruit-ref-shop-tag-label">{{ $group['label'] }}</span>
                                <div class="recruit-ref-shop-tag-pills">
                                    @foreach($group['tags'] as $t)
                                        <span>{{ $t }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="recruit-ref-concept" style="margin-top:16px;">
                    <p class="label">お店の紹介文</p>
                    <div class="body">
                        @if(!empty(trim($shop['concept'] ?? '')))
                            {!! nl2br(e($shop['concept'])) !!}
                        @else
                            <span style="opacity:.65;">プロフィール編集から入力すると、求人票などに反映されます。</span>
                        @endif
                    </div>
                </div>

                <h2 class="recruit-ref-h2-lg" style="margin-top:28px;"><span class="bar" aria-hidden="true"></span> 交通アクセス</h2>
                <div class="recruit-ref-card">
                    @if($addressLine !== '')
                        <p style="font-size:0.875rem;font-weight:800;color:#fafafa;margin:0 0 8px;">{{ $addressLine }}</p>
                    @endif
                    @if(!empty($recruit['nearest_station'] ?? $shop['nearest_station'] ?? null))
                        <p style="font-size:12px;color:#d4af37;margin:0 0 12px;"><i class="fas fa-train-subway"></i> {{ $recruit['nearest_station'] ?? $shop['nearest_station'] }}</p>
                    @endif
                    <div class="recruit-ref-map-placeholder" aria-hidden="true">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    @if($addressLine !== '')
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($addressLine) }}" target="_blank" rel="noopener noreferrer" class="recruit-btn recruit-btn-preview" style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;margin:0;">
                            <i class="fas fa-map"></i> マップアプリで開く
                        </a>
                    @endif
                </div>
            </section>

            @if(!empty($recruit['store_atmosphere']))
                <section>
                    <h2 class="recruit-ref-h2-lg"><span class="bar" aria-hidden="true"></span> お店の雰囲気</h2>
                    <div class="recruit-ref-msg">{!! nl2br(e($recruit['store_atmosphere'])) !!}</div>
                </section>
            @endif
        </div>

        @if(empty($forCast))
            <div class="recruit-ref-fixed-footer">
                <a href="{{ route('shop.recruits.edit') }}"><i class="fas fa-pen"></i> この内容を編集</a>
            </div>
        @endif

        @if(!empty($forCast))
            <div class="recruit-footer-cta">
                <button type="button" class="recruit-cta-heart" aria-label="キープ"><i class="far fa-heart"></i></button>
                <a href="#" class="recruit-cta-btn"><i class="fas fa-paper-plane"></i> 応募する</a>
            </div>
        @endif
    </div>
</div>

<div id="lightbox-overlay" class="lightbox-overlay" onclick="closeLightbox(event)">
    <img id="lightbox-image" src="" alt="" class="lightbox-image">
    <button type="button" class="lightbox-close" aria-label="閉じる" onclick="closeLightbox(event)">
        <i class="fas fa-times"></i>
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tabButtons = document.querySelectorAll('#recruit-ref-tab [data-tab-target]');
    tabButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = btn.getAttribute('data-tab-target');
            var target = targetId === 'top' ? document.getElementById('top') : document.getElementById(targetId);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            tabButtons.forEach(function(b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
        });
    });

    var jobToggle = document.getElementById('recruit-job-toggle');
    if (jobToggle) {
        var reqSub = document.getElementById('recruit-req-sub');
        jobToggle.querySelectorAll('button[data-job-type]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var t = btn.getAttribute('data-job-type');
                jobToggle.querySelectorAll('button').forEach(function(b) { b.classList.toggle('is-active', b === btn); });
                document.querySelectorAll('[data-job-panel]').forEach(function(panel) {
                    var show = panel.getAttribute('data-job-panel') === t;
                    panel.hidden = !show;
                });
                var reqMain = document.getElementById('recruit-req-main');
                var reqHelp = document.getElementById('recruit-req-help');
                if (reqMain && reqHelp) {
                    reqMain.hidden = (t === 'help');
                    reqHelp.hidden = (t !== 'help');
                }
                if (reqSub) {
                    reqSub.textContent = t === 'help' ? '（ヘルプ）' : '（体験・本入店）';
                }
            });
        });
    }

    var topShareBtn = document.getElementById('recruit-share-top-btn');
    if (topShareBtn) {
        topShareBtn.addEventListener('click', function() {
            var trigger = document.querySelector('[data-share-trigger], .share-actions button, .share-actions a');
            if (trigger) trigger.click();
            else if (navigator.share && @json(!empty($shareUrl))) navigator.share({ url: @json($shareUrl) });
        });
    }

    var overlay = document.getElementById('lightbox-overlay');
    var img = document.getElementById('lightbox-image');
    document.querySelectorAll('.js-lightbox-target').forEach(function (el) {
        el.style.cursor = 'zoom-in';
        el.addEventListener('click', function () {
            if (!overlay || !img) return;
            img.src = el.currentSrc || el.src;
            overlay.classList.add('is-open');
        });
    });
});

function closeLightbox(e) {
    if (e) {
        if (e.target && !e.target.classList.contains('lightbox-overlay') && !e.target.closest('.lightbox-close')) {
            return;
        }
        e.stopPropagation();
    }
    var overlay = document.getElementById('lightbox-overlay');
    var lbImg = document.getElementById('lightbox-image');
    if (!overlay) return;
    overlay.classList.remove('is-open');
    if (lbImg) lbImg.src = '';
}
</script>
@endpush
