@extends('layouts.app-v2')

@section('title', 'マイページ')
@section('body-class', 'page-cast-mypage')

@php
    $displayName = $cast['nickname'] ?? $cast['name'] ?? '--';
    $birthdayText = (!empty($cast['birth_year']) && !empty($cast['birth_month']) && !empty($cast['birth_day']))
        ? $cast['birth_year'] . '年' . $cast['birth_month'] . '月' . $cast['birth_day'] . '日'
        : '--';
    $heightText  = !empty($cast['height']) ? ((string) $cast['height'] . ' cm') : '--';
    $weightText  = !empty($cast['weight']) ? ((string) $cast['weight'] . ' kg') : '--';
    $bwhParts    = [($cast['bust'] ?? '') ?: '--', ($cast['waist'] ?? '') ?: '--', ($cast['hip'] ?? '') ?: '--'];
    $bwhText     = implode(' / ', $bwhParts);
    $addressText = trim((string) ($cast['pref'] ?? '') . (string) ($cast['city'] ?? '') . (string) ($cast['addr1'] ?? ''));
    $addressText = $addressText !== '' ? $addressText : '--';
    $zipText     = !empty($cast['zip']) ? ('〒' . $cast['zip']) : '';
    $heroImage   = ($subImages[0]['url'] ?? null) ?: ($cast['img'] ?? asset('assets/images/common/no-image.png'));
    $iconImage   = ($cast['img'] ?? null) ?: ($subImages[0]['url'] ?? asset('assets/images/common/no-image.png'));
    $bonusTotal  = number_format((int) ($cast['bonus_total'] ?? 0));
    $likeCount   = number_format((int) ($cast['like_cnt'] ?? 0));
    $photoCount  = count($subImages);
    $word        = trim((string) ($cast['word'] ?? ''));
@endphp

@section('content')
<div class="pb-24">

    {{-- Hero image --}}
    <div class="relative w-full h-[220px] overflow-hidden">
        <img src="{{ $heroImage }}" alt="" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-base opacity-90"></div>
    </div>

    {{-- Profile head --}}
    <div class="relative px-5 pt-2 pb-6">
        <div class="flex items-end justify-between -mt-12 mb-4 relative z-10">
            <div class="w-[88px] h-[88px] rounded-full border-[3px] border-base overflow-hidden shadow-card-3d bg-surface-from">
                <img src="{{ $iconImage }}" alt="" class="w-full h-full object-cover">
            </div>
            <div class="flex gap-5 px-2 pb-1">
                <div class="flex flex-col items-center">
                    <span class="font-bold text-[18px] text-text-main tracking-tight">{{ $likeCount }}</span>
                    <span class="text-[10px] text-text-sub">ライク</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="font-bold text-[18px] text-accent-text tracking-tight">¥{{ $bonusTotal }}</span>
                    <span class="text-[10px] text-text-sub">ボーナス</span>
                </div>
                <div class="flex flex-col items-center">
                    <span class="font-bold text-[18px] text-text-main tracking-tight">{{ $photoCount }}</span>
                    <span class="text-[10px] text-text-sub">写真</span>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <h1 class="app-title text-[26px] text-text-main mb-2 leading-tight">{{ $displayName }}</h1>
            @if($word !== '')
                <p class="text-[13px] text-text-main font-medium leading-relaxed">{{ $word }}</p>
            @else
                <p class="text-[13px] text-text-sub leading-relaxed">プロフィールを編集してアピール文を設定しましょう</p>
            @endif
        </div>

        <div class="mb-5">
            <x-ui.badge variant="gold">
                <span class="text-[10px] tracking-wider opacity-90 mr-2">獲得ボーナス金合計</span>
                <span class="text-[16px] tracking-wider font-extrabold">¥{{ $bonusTotal }}</span>
            </x-ui.badge>
        </div>

        {{-- Sub-page menu --}}
        <div class="flex flex-col gap-3">
            <x-ui.menu-card icon="settings"
                            sub="EMPLOYMENT & PAYMENT"
                            title="採用・入金管理"
                            href="{{ route('cast.mypage.management') }}" />
            <x-ui.menu-card icon="super"
                            sub="REVIEWS"
                            title="レビュー一覧"
                            href="{{ route('cast.mypage.reviews') }}" />
            <x-ui.menu-card icon="check"
                            sub="IDENTITY"
                            title="本人確認"
                            href="{{ route('cast.mypage.identity') }}" />
            <x-ui.menu-card icon="mypage"
                            sub="PROFILE"
                            title="プロフィールを編集"
                            href="{{ route('cast.profile.edit') }}" />
        </div>
    </div>

    {{-- Tabs --}}
    <div data-tabs-scope>
        <div data-tabs class="border-t border-b border-line-accent/40 bg-base/90 backdrop-blur-md">
            <div class="flex">
                <button type="button" data-tab="gallery"
                        class="is-active flex-1 py-3 flex justify-center items-center transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
                    <span class="app-title text-[12px] tracking-widest">GALLERY</span>
                </button>
                <button type="button" data-tab="details"
                        class="flex-1 py-3 flex justify-center items-center transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
                    <span class="app-title text-[12px] tracking-widest">DETAILS</span>
                </button>
            </div>
        </div>

        {{-- Gallery panel --}}
        <div data-tab-panel="gallery" class="is-active">
            <div class="grid grid-cols-3 gap-[2px]">
                @for($i = 0; $i < 9; $i++)
                    @php $img = $subImages[$i] ?? null; @endphp
                    <div class="aspect-[4/5] relative bg-surface-from overflow-hidden">
                        @if($img && !empty($img['url']))
                            <img src="{{ $img['url'] }}" alt="" class="w-full h-full object-cover">
                            @if($i === 0)
                                <span class="absolute top-1 left-1 text-[9px] font-bold text-on-accent-strong bg-gradient-to-r from-accent-grad-from to-accent-grad-to px-1.5 py-0.5 rounded">MAIN</span>
                            @endif
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-text-sub/60">
                                <x-ui.icon name="plus" class="text-2xl" />
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
            <div class="px-5 pt-5 flex justify-center">
                <x-ui.button variant="grad" as="a" href="{{ route('cast.profile.edit') }}" icon="plus">
                    ギャラリーを編集
                </x-ui.button>
            </div>
        </div>

        {{-- Details panel --}}
        <div data-tab-panel="details" class="p-4 flex flex-col gap-4">

        <x-ui.card class="p-5">
            <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                <x-ui.icon name="mypage" class="text-lg" /> BASIC
            </h3>
            <div class="flex flex-col gap-3">
                <div class="flex justify-between items-center border-b border-line pb-2">
                    <span class="text-[12px] text-text-sub font-medium">生年月日</span>
                    <span class="text-[13px] font-bold text-text-main">{{ $birthdayText }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-line pb-2">
                    <span class="text-[12px] text-text-sub font-medium">身長 / 体重</span>
                    <span class="text-[13px] font-bold text-text-main">{{ $heightText }} / {{ $weightText }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-line pb-2">
                    <span class="text-[12px] text-text-sub font-medium">B / W / H</span>
                    <span class="text-[13px] font-bold text-text-main">{{ $bwhText }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-[12px] text-text-sub font-medium">住所</span>
                    <span class="text-[13px] font-bold text-text-main">{{ $addressText }}</span>
                    @if($zipText !== '')
                        <span class="text-[11px] text-text-sub">{{ $zipText }}</span>
                    @endif
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-[12px] text-text-sub font-medium">自己PR</span>
                    <span class="text-[13px] font-medium text-text-main leading-relaxed">
                        {!! !empty($cast['pr'] ?? '') ? nl2br(e($cast['pr'])) : '<span class="text-text-sub">--</span>' !!}
                    </span>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-5">
            <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                <x-ui.icon name="super" class="text-lg" /> STYLE & TAGS
            </h3>
            <div class="flex flex-col gap-3">
                <div class="flex justify-between items-center border-b border-line pb-2">
                    <span class="text-[12px] text-text-sub font-medium">接客タイプ</span>
                    <span class="text-[13px] font-bold text-text-main">{{ $cast['personality_type'] ?: '--' }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-line pb-2">
                    <span class="text-[12px] text-text-sub font-medium">ルックス</span>
                    <span class="text-[13px] font-bold text-text-main">{{ $cast['my_field'] ?? '--' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[12px] text-text-sub font-medium">性格・内面</span>
                    <span class="text-[13px] font-bold text-text-main">{{ $cast['my_inner_skills'] ?? '--' }}</span>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-5">
            <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                <x-ui.icon name="settings" class="text-lg" /> CAREER
            </h3>
            <div class="flex flex-col gap-3">
                <div class="flex justify-between items-center border-b border-line pb-2">
                    <span class="text-[12px] text-text-sub font-medium">ナイトワーク経験</span>
                    <span class="text-[13px] font-bold text-text-main">{{ $cast['night_work_label'] ?: '--' }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-line pb-2">
                    <span class="text-[12px] text-text-sub font-medium">現職業</span>
                    <span class="text-[13px] font-bold text-text-main">{{ $cast['profession'] ?? ($cast['current_job'] ?? '--') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[12px] text-text-sub font-medium">希望職種</span>
                    <span class="text-[13px] font-bold text-text-main">{{ $cast['industry_names'] ?? ($cast['desired_job'] ?? '--') }}</span>
                </div>
            </div>
        </x-ui.card>

        <div class="flex justify-center pt-1">
            <x-ui.button variant="grad" as="a" href="{{ route('cast.profile.edit') }}">
                プロフィールを編集
            </x-ui.button>
        </div>
        </div>
    </div>

</div>
@endsection
