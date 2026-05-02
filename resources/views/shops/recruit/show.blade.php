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
    .recruit-ref-wrap { padding-bottom: calc(var(--footer-height, 75px) + 16px); background: #050505; }

    /* プレビューバー（sticky） */
    .recruit-ref-preview-sticky {
        position: sticky;
        top: 0;
        z-index: 50;
        background: #110f0d;
        border-bottom: 1px solid #2a2015;
        padding: 12px 16px;
    }
    .recruit-ref-preview-sticky > p {
        margin: 0 0 12px;
        font-size: 11px;
        color: #d4af37;
        font-weight: 800;
    }
    .recruit-ref-preview-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .recruit-ref-publish-form { margin: 0; }
    .recruit-ref-switch {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        background: transparent;
        padding: 0;
        cursor: pointer;
        font: inherit;
    }
    .recruit-ref-switch-track {
        width: 44px;
        height: 24px;
        border-radius: 999px;
        padding: 4px;
        box-sizing: border-box;
        background: #52525b;
        transition: background 0.25s ease;
        flex-shrink: 0;
    }
    .recruit-ref-switch-track.is-on { background: #d4af37; }
    .recruit-ref-switch-knob {
        display: block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.35);
        transition: transform 0.25s ease;
        transform: translateX(0);
    }
    .recruit-ref-switch-track.is-on .recruit-ref-switch-knob { transform: translateX(20px); }
    .recruit-ref-switch-label {
        font-size: 12px;
        font-weight: 800;
        color: #71717a;
    }
    .recruit-ref-switch-label.is-on { color: #d4af37; }
    .recruit-ref-preview-edit {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border: 1px solid #d4af37;
        border-radius: 999px;
        color: #d4af37;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        transition: background 0.15s ease;
    }
    .recruit-ref-preview-edit:hover { background: rgba(212,175,55,.1); }
    .recruit-ref-flash { font-size: 11px; color: #86efac; margin-top: 10px; margin-bottom: 0; }

    .recruit-ref-hero-wrap { position: relative; }
    .recruit-ref-hero { position: relative; margin: 0; height: 16rem; overflow: hidden; background: #18181b; }
    .recruit-ref-hero img { width: 100%; height: 100%; object-fit: cover; opacity: 0.8; }
    .recruit-ref-hero-overlay { position: absolute; inset: 0; background: linear-gradient(to top, #0a0a0a 0%, rgba(10,10,10,.4) 50%, transparent 100%); pointer-events: none; z-index: 2; }
    .recruit-ref-hero-carousel {
        display: flex;
        flex-flow: row nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        height: 100%;
        touch-action: pan-y pinch-zoom;
    }
    .recruit-ref-hero-carousel::-webkit-scrollbar { display: none; }
    .recruit-ref-hero-slide {
        flex: 0 0 100%;
        width: 100%;
        height: 100%;
        scroll-snap-align: start;
        scroll-snap-stop: always;
        position: relative;
    }
    .recruit-ref-hero-slide img { display: block; }
    .recruit-ref-dots {
        position: absolute;
        left: 0; right: 0; bottom: 10px;
        display: flex;
        justify-content: center;
        gap: 6px;
        z-index: 14;
        pointer-events: none;
    }
    .recruit-ref-dot {
        pointer-events: auto;
        width: 6px; height: 6px;
        border-radius: 50%;
        border: none;
        padding: 0;
        background: rgba(255,255,255,.35);
        cursor: pointer;
        transition: background 0.2s, transform 0.2s;
    }
    .recruit-ref-dot.is-active { background: #d4af37; transform: scale(1.15); }
    .recruit-ref-thumbs--carousel {
        position: absolute;
        right: 12px;
        bottom: 12px;
        left: 12px;
        justify-content: flex-end;
        flex-wrap: wrap;
        max-width: none;
    }
    .recruit-ref-thumbs--carousel button {
        border: none;
        padding: 0;
        background: transparent;
        cursor: pointer;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }
    .recruit-ref-thumbs--carousel button.is-active img { border-color: #d4af37; box-shadow: 0 0 0 1px #d4af37; }
    .recruit-ref-thumbs--carousel img { width: 2.5rem; height: 2.5rem; border-radius: 8px; border: 1px solid rgba(212,175,55,.5); object-fit: cover; display: block; }

    .recruit-ref-thumbs { position: absolute; right: 12px; bottom: 12px; display: flex; gap: 6px; z-index: 12; align-items: center; }
    .recruit-ref-thumbs img { width: 2.5rem; height: 2.5rem; border-radius: 8px; border: 1px solid rgba(212,175,55,.5); object-fit: cover; }
    .recruit-ref-thumb-more { width: 2.5rem; height: 2.5rem; border-radius: 8px; border: 1px solid #2a2015; background: rgba(0,0,0,.55); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; color: #fff; }

    .recruit-ref-head { padding: 16px 20px 20px; border-bottom: 1px solid #1f1a14; }
    .recruit-ref-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
    .recruit-ref-chip { font-size: 10px; padding: 2px 8px; border-radius: 2px; font-weight: 700; background: #27272a; color: #d4d4d8; border: 1px solid #3f3f46; }
    .recruit-ref-chip.gold-outline { background: transparent; color: #d4af37; border: 1px solid #d4af37; }

    .recruit-ref-title { margin: 0 0 8px; font-size: 1.5rem; line-height: 1.25; font-weight: 800; color: #fff; letter-spacing: .02em; font-family: var(--font-serif, "Shippori Mincho", serif); }
    .recruit-ref-catch { margin: 0 0 20px; font-size: 0.875rem; font-weight: 800; color: #d4af37; letter-spacing: 0.02em; }

    .recruit-job-toggle { background: #110f0d; padding: 4px; border-radius: 8px; display: flex; border: 1px solid #2a2015; margin-bottom: 16px; }
    .recruit-job-toggle button { flex: 1; border: none; background: transparent; color: #71717a; padding: 10px 4px; font-size: 12px; font-weight: 800; border-radius: 6px; cursor: pointer; transition: color .15s, background .15s; }
    .recruit-job-toggle button.is-active { background: #2a2210; color: #d4af37; box-shadow: 0 1px 2px rgba(0,0,0,.2); }

    /* ヒーロー直下の単一時給カード */
    .recruit-ref-pay-highlight {
        background: #110f0d;
        border-radius: 8px;
        padding: 16px;
        border: 1px solid rgba(212,175,55,.5);
        margin-bottom: 24px;
    }
    .recruit-ref-pay-highlight .label { font-size: 10px; font-weight: 800; color: #d4af37; margin-bottom: 4px; display: block; }
    .recruit-ref-pay-highlight .line { display: flex; align-items: baseline; gap: 4px; flex-wrap: wrap; }
    .recruit-ref-pay-highlight .yen { color: #d4af37; font-weight: 800; font-size: 0.875rem; }
    .recruit-ref-pay-highlight .num { font-size: 1.875rem; font-weight: 800; color: #fff; letter-spacing: -0.02em; }
    .recruit-ref-pay-highlight .tilde { font-size: 0.875rem; color: #a1a1aa; }

    .recruit-ref-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .recruit-ref-tags span { font-size: 10px; padding: 4px 10px; border-radius: 999px; font-weight: 700; }
    .recruit-ref-tags span.gold { background: rgba(212,175,55,.1); border: 1px solid rgba(212,175,55,.3); color: #d4af37; }
    .recruit-ref-tags span.dim { background: #1a1714; border: 1px solid #3a2a18; color: #d4d4d8; font-weight: 600; }

    .recruit-ref-body { padding: 20px; display: flex; flex-direction: column; gap: 40px; padding-bottom: 32px; }

    .recruit-ref-h2 { margin: 0 0 12px; font-size: 0.875rem; font-weight: 800; color: #d4af37; display: flex; align-items: center; gap: 8px; }
    .recruit-ref-h2-lg { margin: 0 0 16px; font-size: 1.125rem; font-weight: 800; color: #fff; display: flex; align-items: flex-start; gap: 8px; flex-wrap: wrap; }
    .recruit-ref-h2-lg .bar { width: 4px; height: 1.25rem; background: #d4af37; border-radius: 1px; flex-shrink: 0; margin-top: 2px; }
    .recruit-ref-subtle { font-size: 11px; font-weight: 600; color: #71717a; }

    .recruit-ref-msg { font-size: 0.875rem; color: #d4d4d8; line-height: 1.75; font-weight: 500; background: #110f0d; padding: 20px; border-radius: 12px; border: 1px solid #1f1a14; white-space: pre-wrap; margin-bottom: 16px; }

    .recruit-ref-share-row { display: flex; gap: 10px; }
    .recruit-ref-share-row .recruit-ref-share-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        transition: background 0.15s ease;
        cursor: pointer;
        border: 1px solid #71717a;
        background: transparent;
        color: #d4d4d8;
        font-family: inherit;
    }
    .recruit-ref-share-row .recruit-ref-share-btn--gold { border-color: #d4af37; color: #d4af37; }
    .recruit-ref-share-row .recruit-ref-share-btn--gold:hover { background: rgba(212,175,55,.1); }
    .recruit-ref-share-row .recruit-ref-share-btn--line { border-color: rgba(6,199,85,.5); color: #06C755; }
    .recruit-ref-share-row .recruit-ref-share-btn--line:hover { background: rgba(6,199,85,.1); }
    .recruit-ref-share-row .recruit-ref-share-btn--muted:hover { background: #27272a; }

    /* 入店ボーナス（募集要項内） */
    .recruit-ref-bonus-card {
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
        background: linear-gradient(to bottom right, #2a2210, #141210);
        border: 1px solid rgba(212,175,55,.4);
        box-shadow: 0 8px 24px rgba(0,0,0,.35);
    }
    .recruit-ref-bonus-card__head { display: flex; align-items: center; gap: 8px; color: #d4af37; margin-bottom: 8px; font-size: 12px; font-weight: 800; letter-spacing: 0.06em; }
    .recruit-ref-bonus-card__amount { display: flex; align-items: baseline; gap: 4px; margin-bottom: 10px; }
    .recruit-ref-bonus-card__amount .num { font-size: 1.25rem; font-weight: 800; color: #fff; letter-spacing: -0.02em; }
    .recruit-ref-bonus-card__amount .suffix { font-size: 0.875rem; font-weight: 800; color: #d4d4d8; }
    .recruit-ref-bonus-card__cond {
        font-size: 10px;
        color: #a1a1aa;
        background: rgba(0,0,0,.3);
        padding: 8px 10px;
        border-radius: 6px;
        border: 1px solid #3a2a18;
        line-height: 1.55;
    }
    .recruit-ref-bonus-card__cond strong { color: #d4af37; font-weight: 800; }

    .recruit-ref-inforow { display: flex; padding: 14px 0; border-bottom: 1px solid #1f1a14; font-size: 0.875rem; }
    .recruit-ref-inforow:last-child { border-bottom: none; }
    .recruit-ref-inforow .k { width: 6rem; flex-shrink: 0; font-size: 11px; font-weight: 800; color: #71717a; padding-top: 2px; }
    .recruit-ref-inforow .v { flex: 1; color: #e4e4e7; font-weight: 600; line-height: 1.6; }

    .recruit-ref-tag-matrix { margin-top: 24px; background: #110f0d; border-radius: 12px; border: 1px solid #1f1a14; padding: 16px; }
    .recruit-ref-tag-matrix > p { margin: 0 0 12px; font-size: 12px; font-weight: 800; color: #d4af37; }
    .recruit-ref-tag-matrix-row { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
    @media (min-width: 480px) {
        .recruit-ref-tag-matrix-row { flex-direction: row; align-items: flex-start; gap: 12px; }
        .recruit-ref-tag-matrix-row .cat { width: 6rem; flex-shrink: 0; padding-top: 4px; }
    }
    .recruit-ref-tag-matrix-row:last-child { margin-bottom: 0; }
    .recruit-ref-tag-matrix-row .cat { font-size: 10px; font-weight: 800; color: #71717a; }
    .recruit-ref-tag-matrix-pills { display: flex; flex-wrap: wrap; gap: 6px; flex: 1; }
    .recruit-ref-tag-matrix-pills span {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        color: #d4d4d8;
        background: #1a1714;
        border: 1px solid #3a2a18;
    }

    .recruit-ref-card { background: #110f0d; border-radius: 12px; border: 1px solid #1f1a14; padding: 16px; margin-bottom: 16px; }

    .recruit-ref-concept .label { font-size: 11px; font-weight: 800; color: #a1a1aa; margin-bottom: 8px; }
    .recruit-ref-concept .body { font-size: 0.875rem; color: #d4d4d8; line-height: 1.75; }

    .recruit-ref-map-placeholder {
        width: 100%; height: 10rem; border-radius: 8px; background: #18181b; border: 1px solid #2a2015;
        display: flex; align-items: center; justify-content: center; margin-bottom: 12px;
    }
    .recruit-ref-map-placeholder i { font-size: 2rem; color: #d4af37; }

    .recruit-ref-map-link {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px;
        border: 1px solid #d4af37;
        border-radius: 8px;
        color: #d4af37;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        transition: background 0.15s ease;
    }
    .recruit-ref-map-link:hover { background: rgba(212,175,55,.1); }
</style>
@endpush

@section('content')
@php
    $usesJobTypes = $usesJobTypes ?? false;
    $recruit_trial = $recruit_trial ?? $recruit;
    $recruit_help = $recruit_help ?? $recruit;

    $hasHelp = !empty($recruit['help_hourly_wage']);
    $hasTrial = !empty($recruit['trial_hourly_wage']);
    $regularWage = (int) ($recruit['hourly_wage_regular'] ?? 0);
    $noruma = (int) ($recruit['noruma_reward'] ?? 0);
    $bonusDays = trim((string) ($recruit['bonus_total_working_days'] ?? $recruit['bonus_working_days'] ?? ''));
    $bonusHours = trim((string) ($recruit['bonus_total_working_hours'] ?? $recruit['bonus_working_hours'] ?? ''));
    $bonusExtra = trim((string) ($recruit['bonus_other_conditions'] ?? $recruit['bonus_condition'] ?? ''));
    $bonusCondParts = array_filter([
        $bonusDays !== '' ? '累計勤務日数: ' . $bonusDays . '日以上' : null,
        $bonusHours !== '' ? '累計勤務時間: ' . $bonusHours . '時間以上' : null,
        $bonusExtra !== '' ? $bonusExtra : null,
    ]);
    $bonusConditionsText = implode('、', $bonusCondParts);
    $showBonusMain = $noruma > 0 || $bonusConditionsText !== '';

    $salaryTags = collect($recruit['store_features']['報酬'] ?? [])->values();
    $otherTags = collect($recruit['store_features'] ?? [])->except('報酬')->flatten()->filter()->unique()->values();
    $pillTags = $salaryTags->merge($otherTags)->unique()->values();

    $subImages = $shop['sub_images'] ?? [];
    $thumbMore = max(0, count($subImages) - 2);
    $galleryImages = array_values(array_filter($shop['gallery_images'] ?? []));
    if (empty($galleryImages)) {
        $galleryImages = array_values(array_filter(array_merge(
            !empty($shop['main_img']) ? [$shop['main_img']] : [],
            is_array($subImages) ? $subImages : []
        )));
    }
    $addressLine = trim((string) ($recruit['address'] ?? ''));
    if ($addressLine === '') {
        $addressLine = trim(($shop['pref'] ?? '') . ($shop['city'] ?? '') . ($shop['addr1'] ?? ''));
    }
    $pref = trim((string) ($shop['pref'] ?? ''));
    $city = trim((string) ($shop['city'] ?? ''));
    $areaChip = ($pref !== '' && $city !== '') ? $pref . '・' . $city : ($pref !== '' ? $pref : $city);

    $isPublishActive = (($recruit['status'] ?? 'active') === 'active');
    $tagGroups = $shop['tag_groups'] ?? [];

    $shareUrlResolved = $shareUrl ?? url()->current();
    $shareTitleResolved = ($shareTitle ?? (($recruit['store_name'] ?? ($shop['name'] ?? '店舗')) . 'の求人情報'));
    $shareTextResolved = $shareText ?? ($recruit['message'] ?? '');
    $xShareUrl = 'https://twitter.com/intent/tweet?url=' . rawurlencode($shareUrlResolved) . '&text=' . rawurlencode(trim($shareTitleResolved . ' ' . $shareTextResolved));
    $lineShareUrl = 'https://social-plugins.line.me/lineit/share?url=' . rawurlencode($shareUrlResolved);

    $storeFeatures = $recruit['store_features'] ?? [];
    $matrixLabels = [
        '報酬' => '給与・支払い',
        '働き方' => '働き方',
        'メリット' => '待遇・サポート',
        '特徴' => '店舗特徴・条件',
        '設備' => '設備・空間',
        'お店の雰囲気' => 'お店の雰囲気・客層',
    ];
    $messageBody = trim((string) ($recruit['message'] ?? ''));
    if ($messageBody === '') {
        $messageBody = trim((string) ($recruit['catch_copy'] ?? ''));
    }

    $salaryNotesMain = trim((string) ($recruit['salary_text'] ?? ''));
    $jobNotesHelp = trim((string) ($recruit['help_job_content'] ?? ''));

    $hasFeatureMatrix = false;
    foreach ($matrixLabels as $key => $_lbl) {
        if (!empty($storeFeatures[$key]) && count((array) $storeFeatures[$key]) > 0) {
            $hasFeatureMatrix = true;
            break;
        }
    }
@endphp

<div class="recruit-detail-page animate-fadeIn recruit-ref-wrap">
    <div class="recruit-ref-shell">

        @if(empty($forCast))
            <div class="recruit-ref-preview-sticky">
                <p>求人票プレビュー（求職者からの見え方）</p>
                <div class="recruit-ref-preview-row">
                    <form method="post" action="{{ route('shop.recruits.toggle-status') }}" class="recruit-ref-publish-form">
                        @csrf
                        <button type="submit" class="recruit-ref-switch" title="タップで公開／非公開を切り替えます" aria-label="{{ $isPublishActive ? '公開中。クリックで非公開にします' : '非公開。クリックで公開します' }}">
                            <span class="recruit-ref-switch-track {{ $isPublishActive ? 'is-on' : '' }}">
                                <span class="recruit-ref-switch-knob"></span>
                            </span>
                            <span class="recruit-ref-switch-label {{ $isPublishActive ? 'is-on' : '' }}">{{ $isPublishActive ? '公開中' : '非公開' }}</span>
                        </button>
                    </form>
                    <a href="{{ route('shop.recruits.edit') }}" class="recruit-ref-preview-edit"><i class="fas fa-pen"></i> 編集</a>
                </div>
                @if(session('message'))
                    <p class="recruit-ref-flash" role="status">{{ session('message') }}</p>
                @endif
            </div>
        @endif

        <div class="recruit-ref-hero-wrap">
            <div class="recruit-ref-hero" id="top">
                @if(count($galleryImages) > 0)
                    <div class="recruit-ref-hero-carousel" id="recruit-hero-carousel">
                        @foreach($galleryImages as $hi => $imgUrl)
                            <div class="recruit-ref-hero-slide" data-hero-slide="{{ $hi }}">
                                <img src="{{ $imgUrl }}" alt="{{ ($recruit['store_name'] ?? $shop['name'] ?? '店舗') }}の写真 {{ $hi + 1 }}" class="js-lightbox-target">
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="recruit-ref-hero-carousel" id="recruit-hero-carousel">
                        <div class="recruit-ref-hero-slide">
                            @if(!empty($recruit['hero_image']))
                                <img src="{{ $recruit['hero_image'] }}" alt="{{ $recruit['store_name'] ?? '' }}" class="js-lightbox-target">
                            @else
                                <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a0c0e 0%,#2d1518 50%,#120405 100%);"></div>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="recruit-ref-hero-overlay"></div>
                @if(count($galleryImages) > 1)
                    <div class="recruit-ref-dots" id="recruit-hero-dots" role="tablist" aria-label="店舗写真の切り替え">
                        @foreach($galleryImages as $hi => $_)
                            <button type="button" class="recruit-ref-dot {{ $hi === 0 ? 'is-active' : '' }}" data-hero-goto="{{ $hi }}" aria-label="写真 {{ $hi + 1 }}" role="tab"></button>
                        @endforeach
                    </div>
                    <div class="recruit-ref-thumbs recruit-ref-thumbs--carousel" id="recruit-hero-thumbs">
                        @foreach($galleryImages as $hi => $imgUrl)
                            <button type="button" data-hero-goto="{{ $hi }}" class="{{ $hi === 0 ? 'is-active' : '' }}" aria-label="サムネイル {{ $hi + 1 }}">
                                <img src="{{ $imgUrl }}" alt="" width="40" height="40" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="recruit-ref-head">
            <div class="recruit-ref-chips">
                @if($areaChip !== '')
                    <span class="recruit-ref-chip">{{ $areaChip }}</span>
                @endif
                @if(!empty($shop['industry_name'] ?? null))
                    <span class="recruit-ref-chip gold-outline">{{ $shop['industry_name'] }}</span>
                @endif
            </div>

            <h1 class="recruit-ref-title">{{ $recruit['store_name'] ?? ($shop['name'] ?? '—') }}</h1>

            @if($usesJobTypes)
                <div class="recruit-job-toggle" id="recruit-job-toggle" role="tablist" aria-label="求人の種類">
                    <button type="button" class="is-active" data-job-type="trial">体験入店</button>
                    <button type="button" data-job-type="help">ヘルプ</button>
                </div>
                @foreach(['trial' => $recruit_trial, 'help' => $recruit_help] as $vk => $rv)
                    <div class="recruit-variant-head" data-variant-head="{{ $vk }}" @if($vk !== 'trial') hidden @endif>
                        @include('shops.recruit.preview-variant-head', ['rv' => $rv, 'vk' => $vk])
                    </div>
                @endforeach
            @else
                @if(!empty($recruit['catch_copy']))
                    <p class="recruit-ref-catch">{{ $recruit['catch_copy'] }}</p>
                @endif

                @if($hasHelp)
                    <div class="recruit-job-toggle" id="recruit-job-toggle" role="tablist" aria-label="募集枠">
                        <button type="button" class="is-active" data-job-type="main">体験入店・本入店</button>
                        <button type="button" data-job-type="help">ヘルプ</button>
                    </div>
                @endif

                <div id="recruit-panel-main" data-job-panel="main">
                    <div class="recruit-ref-pay-highlight">
                        <span class="label">{{ $regularWage > 0 ? '本入時給' : ($hasTrial ? '体験時給' : '本入時給') }}</span>
                        <div class="line">
                            @if($regularWage > 0)
                                <span class="yen">¥</span><span class="num">{{ number_format($regularWage) }}</span><span class="tilde">〜</span>
                            @elseif($hasTrial)
                                <span class="yen">¥</span><span class="num">{{ number_format((int) $recruit['trial_hourly_wage']) }}</span><span class="tilde">〜</span>
                            @else
                                <span style="font-size:0.9rem;color:#71717a;font-weight:700;">求人編集で入力してください</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($hasHelp)
                    <div id="recruit-panel-help" data-job-panel="help" hidden>
                        <div class="recruit-ref-pay-highlight">
                            <span class="label">ヘルプ時給</span>
                            <div class="line">
                                <span class="yen">¥</span><span class="num">{{ number_format((int) $recruit['help_hourly_wage']) }}</span><span class="tilde">〜</span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="recruit-ref-tags" aria-label="特徴タグ">
                    @foreach($pillTags as $i => $tag)
                        @php $ts = (string) $tag; $t = strpos($ts, '#') === 0 ? $ts : '#' . $ts; @endphp
                        <span class="{{ $i < 2 ? 'gold' : 'dim' }}">{{ $t }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="recruit-ref-body">

            @if($usesJobTypes)
                @foreach(['trial' => $recruit_trial, 'help' => $recruit_help] as $vk => $rv)
                    <div class="recruit-variant-body" data-variant-body="{{ $vk }}" @if($vk !== 'trial') hidden @endif>
                        @include('shops.recruit.preview-variant-body', ['rv' => $rv, 'vk' => $vk, 'matrixLabels' => $matrixLabels])
                    </div>
                @endforeach
            @else
                <section id="section-message">
                    <h2 class="recruit-ref-h2"><i class="fas fa-comment-dots"></i> お店からのメッセージ</h2>
                    <div class="recruit-ref-msg">{{ $messageBody !== '' ? $messageBody : 'メッセージは求人編集から入力できます。' }}</div>

                    @if(!empty($shareUrlResolved ?? null))
                        <div class="recruit-ref-share-row">
                            <button type="button" class="recruit-ref-share-btn recruit-ref-share-btn--gold js-recruit-native-share">
                                <i class="fas fa-share-alt"></i> 共有
                            </button>
                            <a href="{{ $xShareUrl }}" target="_blank" rel="noopener noreferrer" class="recruit-ref-share-btn recruit-ref-share-btn--muted">
                                <span style="font-weight:900;">𝕏</span>
                            </a>
                            <a href="{{ $lineShareUrl }}" target="_blank" rel="noopener noreferrer" class="recruit-ref-share-btn recruit-ref-share-btn--line">
                                LINE
                            </a>
                        </div>
                    @endif
                </section>

                <section id="requirements">
                    <h2 class="recruit-ref-h2-lg">
                        <span class="bar" aria-hidden="true"></span>
                        募集要項
                        <span id="recruit-req-sub" class="recruit-ref-subtle">（体験・本入店）</span>
                    </h2>

                    <div id="recruit-req-main">
                        @if($showBonusMain)
                            <div class="recruit-ref-bonus-card" aria-labelledby="recruit-bonus-title">
                                <div id="recruit-bonus-title" class="recruit-ref-bonus-card__head">
                                    <i class="fas fa-gift" aria-hidden="true"></i>
                                    <span>入店ボーナス</span>
                                </div>
                                <div class="recruit-ref-bonus-card__amount">
                                    @if($noruma > 0)
                                        <span class="num">{{ number_format($noruma) }}</span>
                                        <span class="suffix">円支給</span>
                                    @else
                                        <span class="num" style="font-size:1rem;">条件のみ設定されています</span>
                                    @endif
                                </div>
                                @if($bonusConditionsText !== '')
                                    <div class="recruit-ref-bonus-card__cond"><strong>条件:</strong> {{ $bonusConditionsText }}</div>
                                @endif
                            </div>
                        @endif

                        <div class="recruit-ref-inforow"><span class="k">給与</span><span class="v">
                            @if($regularWage > 0 && $hasTrial)
                                <span style="color:#d4af37;font-weight:800;">体入: {{ number_format((int) $recruit['trial_hourly_wage']) }}円〜</span><br>
                                <span style="color:#e4e4e7;">本入: {{ number_format($regularWage) }}円〜</span>
                            @elseif($regularWage > 0)
                                <span style="color:#d4af37;font-weight:800;">本入: {{ number_format($regularWage) }}円〜</span>
                            @elseif($hasTrial)
                                <span style="color:#d4af37;font-weight:800;">体入: {{ number_format((int) $recruit['trial_hourly_wage']) }}円〜</span>
                            @else
                                —
                            @endif
                        </span></div>
                        <div class="recruit-ref-inforow"><span class="k">給与備考</span><span class="v" style="white-space:pre-wrap;color:#d4d4d8;">{{ $salaryNotesMain !== '' ? $salaryNotesMain : '—' }}</span></div>
                        <div class="recruit-ref-inforow"><span class="k">勤務時間</span><span class="v">{{ $recruit['working_hours'] ?: '—' }}</span></div>
                        <div class="recruit-ref-inforow"><span class="k">勤務日・シフト</span><span class="v">{{ $recruit['working_days'] ?: '—' }}</span></div>
                        <div class="recruit-ref-inforow"><span class="k">応募資格</span><span class="v">{{ $recruit['qualification'] ?? '—' }}</span></div>
                        <div class="recruit-ref-inforow"><span class="k">控除</span><span class="v">10.21%（源泉所得税）</span></div>

                        @if($hasFeatureMatrix)
                            <div class="recruit-ref-tag-matrix">
                                <p>特徴・アピールタグ</p>
                                @foreach($matrixLabels as $key => $label)
                                    @php $tags = $storeFeatures[$key] ?? []; @endphp
                                    @if(!empty($tags))
                                        <div class="recruit-ref-tag-matrix-row">
                                            <span class="cat">{{ $label }}</span>
                                            <div class="recruit-ref-tag-matrix-pills">
                                                @foreach((array) $tags as $t)
                                                    <span>{{ $t }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($hasHelp)
                        <div id="recruit-req-help" hidden>
                            @if($noruma > 0)
                                <div class="recruit-ref-bonus-card">
                                    <div class="recruit-ref-bonus-card__head"><i class="fas fa-gift"></i><span>入店ボーナス</span></div>
                                    <div class="recruit-ref-bonus-card__amount">
                                        <span class="num">{{ number_format($noruma) }}</span><span class="suffix">円支給</span>
                                    </div>
                                    @if($bonusConditionsText !== '')
                                        <div class="recruit-ref-bonus-card__cond"><strong>条件:</strong> {{ $bonusConditionsText }}</div>
                                    @endif
                                </div>
                            @endif

                            <div class="recruit-ref-inforow"><span class="k">給与</span><span class="v"><span style="color:#d4af37;font-weight:800;">{{ number_format((int) $recruit['help_hourly_wage']) }}円〜</span></span></div>
                            <div class="recruit-ref-inforow"><span class="k">給与備考</span><span class="v" style="white-space:pre-wrap;">{{ ($jobNotesHelp !== '' ? $jobNotesHelp : $salaryNotesMain) !== '' ? ($jobNotesHelp !== '' ? $jobNotesHelp : $salaryNotesMain) : '—' }}</span></div>
                            <div class="recruit-ref-inforow"><span class="k">勤務時間</span><span class="v">{{ $recruit['working_hours'] ?: '—' }}</span></div>
                            <div class="recruit-ref-inforow"><span class="k">勤務日・シフト</span><span class="v">{{ $recruit['working_days'] ?: '—' }}</span></div>
                            <div class="recruit-ref-inforow"><span class="k">応募資格</span><span class="v">{{ $recruit['qualification'] ?? '—' }}</span></div>
                            <div class="recruit-ref-inforow"><span class="k">控除</span><span class="v">10.21%（源泉所得税）</span></div>

                            @if($hasFeatureMatrix)
                                <div class="recruit-ref-tag-matrix">
                                    <p>特徴・アピールタグ</p>
                                    @foreach($matrixLabels as $key => $label)
                                        @php $tags = $storeFeatures[$key] ?? []; @endphp
                                        @if(!empty($tags))
                                            <div class="recruit-ref-tag-matrix-row">
                                                <span class="cat">{{ $label }}</span>
                                                <div class="recruit-ref-tag-matrix-pills">
                                                    @foreach((array) $tags as $t)
                                                        <span>{{ $t }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </section>
            @endif

            <section id="info">
                <h2 class="recruit-ref-h2-lg"><span class="bar" aria-hidden="true"></span> 店舗情報</h2>

                <div class="recruit-ref-inforow"><span class="k">店名</span><span class="v">{{ $shop['name'] ?? ($recruit['store_name'] ?? '—') }}</span></div>
                <div class="recruit-ref-inforow"><span class="k">業種</span><span class="v">{{ $shop['industry_name'] ?? '未設定' }}</span></div>
                <div class="recruit-ref-inforow"><span class="k">営業時間</span><span class="v">{{ $recruit['working_hours'] ?: '—' }}</span></div>
                <div class="recruit-ref-inforow"><span class="k">定休日</span><span class="v">{{ $recruit['regular_holiday'] ?: '—' }}</span></div>
                @if(!empty($recruit['store_atmosphere']))
                    <div class="recruit-ref-inforow"><span class="k">店舗の雰囲気</span><span class="v" style="white-space:pre-wrap;">{{ $recruit['store_atmosphere'] }}</span></div>
                @endif

                @if(!empty($tagGroups))
                    @foreach($tagGroups as $group)
                        <div style="margin-top:14px;padding-top:14px;border-top:1px solid #1f1a14;">
                            <p style="margin:0 0 8px;font-size:11px;font-weight:800;color:#d4af37;">{{ $group['label'] }}</p>
                            <div class="recruit-ref-tag-matrix-pills">
                                @foreach($group['tags'] as $t)
                                    <span>{{ $t }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
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
                        <p style="font-size:0.875rem;font-weight:800;color:#fafafa;margin:0 0 6px;">{{ $addressLine }}</p>
                    @endif
                    @if(!empty($recruit['nearest_station'] ?? $shop['nearest_station'] ?? null))
                        <p style="font-size:12px;color:#d4af37;margin:0 0 14px;"><i class="fas fa-train-subway"></i> {{ $recruit['nearest_station'] ?? $shop['nearest_station'] }}</p>
                    @endif
                    <div class="recruit-ref-map-placeholder" aria-hidden="true"><i class="fas fa-map-marker-alt"></i></div>
                    @if($addressLine !== '')
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($addressLine) }}" target="_blank" rel="noopener noreferrer" class="recruit-ref-map-link">
                            <i class="fas fa-map"></i> マップアプリで開く
                        </a>
                    @endif
                </div>
            </section>

            @if(!empty($forCast))
                <div class="recruit-footer-cta">
                    <button type="button" class="recruit-cta-heart" aria-label="キープ"><i class="far fa-heart"></i></button>
                    <a href="#" class="recruit-cta-btn"><i class="fas fa-paper-plane"></i> 応募する</a>
                </div>
            @endif
        </div>
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
    var usesJobTypes = @json($usesJobTypes ?? false);
    var heroCarousel = document.getElementById('recruit-hero-carousel');
    if (heroCarousel && heroCarousel.children.length > 1) {
        var heroSlides = heroCarousel.querySelectorAll('.recruit-ref-hero-slide');
        var dots = document.querySelectorAll('.recruit-ref-dot[data-hero-goto]');
        var thumbBtns = document.querySelectorAll('#recruit-hero-thumbs button[data-hero-goto]');
        function setHeroIndex(idx) {
            var i = Math.max(0, Math.min(idx, heroSlides.length - 1));
            var slide = heroSlides[i];
            if (slide) heroCarousel.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
            dots.forEach(function(d) { d.classList.toggle('is-active', parseInt(d.getAttribute('data-hero-goto'), 10) === i); });
            thumbBtns.forEach(function(b) { b.classList.toggle('is-active', parseInt(b.getAttribute('data-hero-goto'), 10) === i); });
        }
        function currentHeroIndex() {
            var w = heroCarousel.clientWidth || 1;
            return Math.round(heroCarousel.scrollLeft / w);
        }
        document.querySelectorAll('[data-hero-goto]').forEach(function(el) {
            el.addEventListener('click', function() {
                var g = parseInt(el.getAttribute('data-hero-goto'), 10);
                if (!isNaN(g)) setHeroIndex(g);
            });
        });
        var scrollEndTimer;
        heroCarousel.addEventListener('scroll', function() {
            clearTimeout(scrollEndTimer);
            scrollEndTimer = setTimeout(function() {
                var ci = currentHeroIndex();
                dots.forEach(function(d) { d.classList.toggle('is-active', parseInt(d.getAttribute('data-hero-goto'), 10) === ci); });
                thumbBtns.forEach(function(b) { b.classList.toggle('is-active', parseInt(b.getAttribute('data-hero-goto'), 10) === ci); });
            }, 60);
        }, { passive: true });
    }

    var jobToggle = document.getElementById('recruit-job-toggle');
    if (jobToggle) {
        var reqSub = document.getElementById('recruit-req-sub');
        jobToggle.querySelectorAll('button[data-job-type]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var t = btn.getAttribute('data-job-type');
                jobToggle.querySelectorAll('button').forEach(function(b) { b.classList.toggle('is-active', b === btn); });
                if (usesJobTypes) {
                    document.querySelectorAll('[data-variant-head]').forEach(function(el) {
                        el.hidden = el.getAttribute('data-variant-head') !== t;
                    });
                    document.querySelectorAll('[data-variant-body]').forEach(function(el) {
                        el.hidden = el.getAttribute('data-variant-body') !== t;
                    });
                } else {
                    document.querySelectorAll('[data-job-panel]').forEach(function(panel) {
                        panel.hidden = panel.getAttribute('data-job-panel') !== t;
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
                }
            });
        });
    }

    var shareUrl = @json($shareUrlResolved ?? '');
    var shareTitle = @json($shareTitleResolved ?? '');
    var shareText = @json($shareTextResolved ?? '');
    document.querySelectorAll('.js-recruit-native-share').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({ title: shareTitle, text: shareText, url: shareUrl }).catch(function() {});
            } else if (shareUrl) {
                window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(shareUrl) + '&text=' + encodeURIComponent(shareTitle), '_blank', 'noopener,noreferrer');
            }
        });
    });

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
