{{-- 求人プロフィール：cast 側からの閲覧専用ビュー（MyPage 同様の Instagram 風） --}}
@php
    $shopName     = $shop['name'] ?? ($recruit['store_name'] ?? '店舗');
    $iconImage    = $shop['main_img'] ?? ($galleryImages[0] ?? asset('assets/images/common/no-image.png'));
    $catchCopy    = trim((string) ($recruit['catch_copy'] ?? ''));
    $catchTrunc   = $catchCopy !== ''
        ? (mb_strlen($catchCopy) > 80 ? mb_substr($catchCopy, 0, 80) . '…' : $catchCopy)
        : '';
    $totalSlots   = max(9, count($galleryImages));
    $likeCount    = (int) ($recruit['like_cnt'] ?? $shop['like_cnt'] ?? 0);
    $ctaShopId    = $shop['id'] ?? $shop['shop_id'] ?? $recruit['id'] ?? $recruit['shop_id'] ?? null;
    $ctaHasHelp   = $usesJobTypes
        ? !empty($recruit_help['help_hourly_wage']) || !empty($recruit_help['hourly_wage'])
        : !empty($recruit['help_hourly_wage']);
    $ctaHasTrial  = $usesJobTypes
        ? !empty($recruit_trial['trial_hourly_wage']) || !empty($recruit_trial['hourly_wage'])
        : (!empty($recruit['trial_hourly_wage']) || $regularWage > 0);
    $wageFormat = function (?int $wage, ?int $wageMax) {
        if (!$wage && !$wageMax) return null;
        if ($wage && $wageMax && $wageMax > $wage) return '¥' . number_format($wage) . ' 〜 ¥' . number_format($wageMax);
        return '¥' . number_format($wage ?: $wageMax);
    };
    $regularWageDisp = $wageFormat($regularWage ?: null, $regularWageMax);
    $trialWageDisp   = $wageFormat((int) ($recruit['trial_hourly_wage'] ?? 0) ?: null, $trialWageMax);
    $helpWageDisp    = $wageFormat((int) ($recruit['help_hourly_wage'] ?? 0) ?: null, $helpWageMax);
    $businessHours   = trim((string) ($recruit['business_hours'] ?? $shop['business_hours_shop'] ?? ''));
    $nearestStation  = trim((string) ($shop['nearest_station'] ?? ''));
    $workingHours    = trim((string) ($recruit['working_hours'] ?? ''));

    // ----- 店舗プロファイル系の追加情報 -----
    $industryName    = trim((string) ($shop['industry_name'] ?? ''));
    $shopWord        = trim((string) ($shop['word'] ?? ''));
    $shopConcept     = trim((string) ($shop['concept'] ?? ''));
    $openDate        = trim((string) ($recruit['open_date'] ?? ''));
    $shopTagGroups   = isset($tagGroups) && is_array($tagGroups) ? $tagGroups : (array) ($shop['tag_groups'] ?? []);

    // ----- 求人系（勤務条件）の追加情報 -----
    $workingDays     = trim((string) ($recruit['working_days'] ?? ''));
    $regularHoliday  = trim((string) ($recruit['regular_holiday'] ?? ''));
    $qualification   = trim((string) ($recruit['qualification'] ?? ''));
    $jobContent      = trim((string) ($recruit['job_content'] ?? ''));
@endphp

<div class="pb-6">

    <div class="px-5 pt-4 pb-6">

        {{-- アイコン + 紹介文（catch_copy） --}}
        <div class="flex items-start gap-3 mb-5">
            <div class="w-[84px] h-[84px] rounded-2xl overflow-hidden border-2 border-line-accent/40 shadow-card-3d bg-surface-from shrink-0">
                <img src="{{ $iconImage }}" alt="" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0">
                <div class="relative bg-gradient-to-br from-surface-from to-base border border-line-accent/40 rounded-2xl shadow-card-3d p-3">
                    <span class="absolute top-5 -left-[8px] w-0 h-0 border-y-[8px] border-y-transparent border-r-[10px] border-r-line-accent/40"></span>
                    <span class="absolute top-5 -left-[6px] w-0 h-0 border-y-[7px] border-y-transparent border-r-[9px] border-r-surface-from"></span>
                    <p class="text-[13px] leading-relaxed {{ $catchTrunc === '' ? 'text-text-sub' : 'text-text-main' }}">
                        {{ $catchTrunc !== '' ? $catchTrunc : 'キャッチコピー未設定' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- 名前（店舗名）+ ライク --}}
        <div class="flex items-center justify-between gap-3 mb-3">
            <h1 class="app-title text-[24px] text-text-main leading-tight truncate min-w-0">{{ $shopName }}</h1>
            <div class="flex items-center gap-1.5 shrink-0">
                <i class="fas fa-heart text-[18px] text-discovery-pink"></i>
                <span class="font-bold text-[14px] text-text-main">{{ number_format($likeCount) }}</span>
            </div>
        </div>

        {{-- エリア + 業種 --}}
        @if($areaChip !== '' || $industryName !== '')
            <div class="flex items-center gap-2 mb-4 text-text-sub text-[12px] flex-wrap">
                @if($areaChip !== '')
                    <span class="inline-flex items-center gap-1.5"><i class="fas fa-map-marker-alt"></i>{{ $areaChip }}</span>
                @endif
                @if($industryName !== '')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-accent/10 border border-line-accent/40 text-accent-text font-bold tracking-wide">
                        <i class="fas fa-tag text-[10px]"></i>{{ $industryName }}
                    </span>
                @endif
            </div>
        @endif

        {{-- ボーナス金バッジ --}}
        @if($showBonusMain)
            <div class="mb-5">
                <x-ui.badge variant="gold">
                    <span class="text-[10px] tracking-wider opacity-90">ボーナス金</span>
                    <span class="text-[18px] tracking-wider font-extrabold">¥{{ number_format($noruma) }}</span>
                </x-ui.badge>
                @if($bonusConditionsText !== '')
                    <p class="text-[11px] text-text-sub mt-2">達成条件：{{ $bonusConditionsText }}</p>
                @endif
            </div>
        @endif

        {{-- アクション CTA --}}
        @if(!empty($ctaShopId))
            <div class="flex flex-col gap-2 mb-3">
                @if($ctaHasTrial)
                    <a href="{{ route('cast.talk.room', ['id' => $ctaShopId, 'job_kind' => 'trial', 'talk_topic' => 'new_hire', 'initiate' => 1]) }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-bold bg-gradient-to-r from-accent-grad-from to-accent-grad-to text-on-accent-strong shadow-btn-3d active:translate-y-1.5 active:shadow-btn-3d-active transition-all duration-300">
                        <i class="fas fa-paper-plane"></i> 新規採用に応募する
                    </a>
                @endif
                @if($ctaHasHelp)
                    <a href="{{ route('cast.talk.room', ['id' => $ctaShopId, 'job_kind' => 'help', 'talk_topic' => 'help', 'initiate' => 1]) }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-bold border border-line-accent/40 bg-accent/10 text-accent-text transition-all duration-300">
                        <i class="fas fa-hand-holding-heart"></i> ヘルプ求人に応募する
                    </a>
                @endif
                <div class="flex gap-2">
                    <button type="button"
                            class="recruit-cta-heart flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-full font-bold border border-line-accent/40 bg-base/60 text-text-main transition-all duration-300 {{ !empty($recruit['is_kept']) ? 'bg-accent/15 text-accent-text border-accent' : '' }}"
                            aria-label="キープ"
                            data-item-id="{{ $shop['id'] ?? '' }}"
                            data-item-type="shop"
                            data-action="keep">
                        <i class="fas fa-bookmark"></i> KEEP
                    </button>
                    <a href="{{ route('cast.talk.room', ['id' => $ctaShopId, 'talk_topic' => 'other', 'initiate' => 1]) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-full font-bold border border-line-accent/40 bg-base/60 text-text-main transition-all duration-300">
                        <i class="fas fa-comment-dots"></i> 話を聞く
                    </a>
                </div>
            </div>
        @endif

        {{-- 共有 --}}
        @if(!empty($shareUrlResolved))
            <div class="flex justify-end mb-3">
                @include('partials.share-menu', [
                    'shareUrl' => $shareUrlResolved,
                    'shareTitle' => $shareTitleResolved,
                    'shareText' => $shareTextResolved,
                    'menuId' => 'recruit-share-menu',
                ])
            </div>
        @endif
    </div>

    {{-- Tabs: GALLERY / JOB / SHOP --}}
    <div data-tabs-scope>
        <div data-tabs class="border-t border-b border-line-accent/40 bg-base/90 backdrop-blur-md sticky top-0 z-10">
            <div class="flex">
                <button type="button" data-tab="gallery"
                        class="flex-1 py-3 flex flex-col items-center justify-center gap-0.5 transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
                    <i class="fas fa-images text-[14px]"></i>
                    <span class="app-title text-[10px] tracking-widest">GALLERY</span>
                </button>
                <button type="button" data-tab="job"
                        class="is-active flex-1 py-3 flex flex-col items-center justify-center gap-0.5 transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
                    <i class="fas fa-briefcase text-[14px]"></i>
                    <span class="app-title text-[10px] tracking-widest">JOB</span>
                </button>
                <button type="button" data-tab="shop"
                        class="flex-1 py-3 flex flex-col items-center justify-center gap-0.5 transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
                    <i class="fas fa-store text-[14px]"></i>
                    <span class="app-title text-[10px] tracking-widest">SHOP</span>
                </button>
            </div>
        </div>

        {{-- ========== GALLERY ========== --}}
        <div data-tab-panel="gallery">
            @if(count($galleryImages) > 0)
                <ul id="profile-gallery-list">
                    @foreach($galleryImages as $i => $img)
                        <li class="profile-gallery-item">
                            <button type="button" class="profile-gallery-slot has-img js-lightbox-target" data-image-url="{{ $img }}" aria-label="写真 {{ $i + 1 }} を拡大">
                                <img src="{{ $img }}" alt="" loading="lazy">
                                @if($i === 0)<span class="profile-gallery-badge">MAIN</span>@endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-8 text-center text-text-sub text-[13px]">
                    <i class="far fa-image text-2xl mb-2 block opacity-50"></i>
                    写真はまだ登録されていません
                </div>
            @endif
        </div>

        {{-- ========== JOB（求人情報） ========== --}}
        <div data-tab-panel="job" class="is-active">
            <div class="p-4 flex flex-col gap-4">

                {{-- BONUS card --}}
                @if($showBonusMain || $bonusConditionsText !== '')
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                            <i class="fas fa-gift"></i> BONUS
                        </h3>
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between items-center border-b border-line pb-2">
                                <span class="text-[12px] text-text-sub font-medium">ボーナス金</span>
                                <span class="text-[14px] font-extrabold text-amber-400">¥{{ number_format($noruma) }}</span>
                            </div>
                            @if($bonusConditionsText !== '')
                                <div class="flex flex-col gap-1">
                                    <span class="text-[12px] text-text-sub font-medium">達成条件</span>
                                    <span class="text-[13px] text-text-main leading-relaxed">{{ $bonusConditionsText }}</span>
                                </div>
                            @endif
                        </div>
                    </x-ui.card>
                @endif

                {{-- WAGES card --}}
                @if($regularWageDisp || $trialWageDisp || $helpWageDisp)
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                            <i class="fas fa-yen-sign"></i> WAGE
                        </h3>
                        <div class="flex flex-col gap-3">
                            @if($regularWageDisp)
                                <div class="flex justify-between items-center border-b border-line pb-2">
                                    <span class="text-[12px] text-text-sub font-medium">本入り時給</span>
                                    <span class="text-[13px] font-bold text-text-main">{{ $regularWageDisp }}</span>
                                </div>
                            @endif
                            @if($trialWageDisp)
                                <div class="flex justify-between items-center border-b border-line pb-2">
                                    <span class="text-[12px] text-text-sub font-medium">体入時給</span>
                                    <span class="text-[13px] font-bold text-text-main">{{ $trialWageDisp }}</span>
                                </div>
                            @endif
                            @if($helpWageDisp)
                                <div class="flex justify-between items-center">
                                    <span class="text-[12px] text-text-sub font-medium">ヘルプ時給</span>
                                    <span class="text-[13px] font-bold text-text-main">{{ $helpWageDisp }}</span>
                                </div>
                            @endif
                        </div>
                        @if($salaryNotesMain !== '')
                            <p class="text-[11px] text-text-sub mt-3">{{ $salaryNotesMain }}</p>
                        @endif
                    </x-ui.card>
                @endif

                {{-- ABOUT / Message card --}}
                @if($messageBody !== '' || $jobSupplementMain !== '' || $jobContent !== '')
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                            <i class="fas fa-comment-dots"></i> ABOUT
                        </h3>
                        @if($messageBody !== '')
                            <div class="text-[13px] text-text-main leading-relaxed whitespace-pre-line">{{ $messageBody }}</div>
                        @endif
                        @php $jobBody = $jobSupplementMain !== '' ? $jobSupplementMain : $jobContent; @endphp
                        @if($jobBody !== '')
                            <div class="mt-3 pt-3 border-t border-line">
                                <span class="text-[12px] text-text-sub font-medium block mb-1">仕事内容</span>
                                <div class="text-[13px] text-text-main leading-relaxed whitespace-pre-line">{{ $jobBody }}</div>
                            </div>
                        @endif
                    </x-ui.card>
                @endif

                {{-- SHIFT / 勤務条件 card --}}
                @if($workingHours !== '' || $workingDays !== '' || $regularHoliday !== '' || $qualification !== '')
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                            <i class="fas fa-calendar-check"></i> SHIFT
                        </h3>
                        <div class="flex flex-col gap-3">
                            @if($workingHours !== '')
                                <div class="flex justify-between items-center border-b border-line pb-2">
                                    <span class="text-[12px] text-text-sub font-medium">勤務時間</span>
                                    <span class="text-[13px] font-bold text-text-main text-right">{{ $workingHours }}</span>
                                </div>
                            @endif
                            @if($workingDays !== '')
                                <div class="flex justify-between items-center border-b border-line pb-2">
                                    <span class="text-[12px] text-text-sub font-medium">勤務日数</span>
                                    <span class="text-[13px] font-bold text-text-main text-right">{{ $workingDays }}</span>
                                </div>
                            @endif
                            @if($regularHoliday !== '')
                                <div class="flex justify-between items-center border-b border-line pb-2">
                                    <span class="text-[12px] text-text-sub font-medium">定休日</span>
                                    <span class="text-[13px] font-bold text-text-main text-right">{{ $regularHoliday }}</span>
                                </div>
                            @endif
                            @if($qualification !== '')
                                <div class="flex justify-between items-center">
                                    <span class="text-[12px] text-text-sub font-medium">応募資格</span>
                                    <span class="text-[13px] font-bold text-text-main text-right">{{ $qualification }}</span>
                                </div>
                            @endif
                        </div>
                    </x-ui.card>
                @endif

                {{-- FEATURES card（求人タグ：働き方・歓迎条件・待遇） --}}
                @if($hasFeatureMatrix)
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                            <i class="fas fa-tags"></i> FEATURES
                        </h3>
                        <div class="flex flex-col gap-3">
                            @foreach($matrixLabels as $key => $label)
                                @if(!empty($storeFeatures[$key]) && count((array) $storeFeatures[$key]) > 0)
                                    <div>
                                        <div class="text-[10px] text-text-sub uppercase tracking-wider mb-1 flex items-center gap-1">
                                            <i class="fas {{ $matrixIcons[$key] ?? 'fa-tag' }}"></i> {{ $label }}
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach((array) $storeFeatures[$key] as $tag)
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-accent/10 border border-line-accent/30 text-[11px] text-text-main">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </x-ui.card>
                @endif

            </div>
        </div>

        {{-- ========== SHOP（店舗プロフィール） ========== --}}
        <div data-tab-panel="shop">
            <div class="p-4 flex flex-col gap-4">

                {{-- SHOP INFO card --}}
                <x-ui.card class="p-5">
                    <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                        <i class="fas fa-store"></i> SHOP INFO
                    </h3>
                    <div class="flex flex-col gap-3">
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">店名</span>
                            <span class="text-[13px] font-bold text-text-main text-right">{{ $shopName }}</span>
                        </div>
                        @if($industryName !== '')
                            <div class="flex justify-between items-center border-b border-line pb-2">
                                <span class="text-[12px] text-text-sub font-medium">業種</span>
                                <span class="text-[13px] font-bold text-text-main text-right">{{ $industryName }}</span>
                            </div>
                        @endif
                        @if($openDate !== '')
                            <div class="flex justify-between items-center">
                                <span class="text-[12px] text-text-sub font-medium">開店日</span>
                                <span class="text-[13px] font-bold text-text-main text-right">{{ $openDate }}</span>
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                {{-- WORD（店長のひとこと） --}}
                @if($shopWord !== '' || $shopConcept !== '')
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                            <i class="fas fa-quote-left"></i> WORD
                        </h3>
                        @if($shopWord !== '')
                            <div class="text-[13px] text-text-main leading-relaxed whitespace-pre-line">{{ $shopWord }}</div>
                        @endif
                        @if($shopConcept !== '')
                            <div class="@if($shopWord !== '') mt-3 pt-3 border-t border-line @endif">
                                <span class="text-[11px] text-text-sub uppercase tracking-wider block mb-1">CONCEPT</span>
                                <div class="text-[13px] text-text-main leading-relaxed whitespace-pre-line">{{ $shopConcept }}</div>
                            </div>
                        @endif
                    </x-ui.card>
                @endif

                {{-- AMBIENCE（店内雰囲気・設備タグ） --}}
                @if(!empty($shopTagGroups))
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                            <i class="fas fa-sparkles"></i> AMBIENCE
                        </h3>
                        <div class="flex flex-col gap-3">
                            @foreach($shopTagGroups as $group)
                                @if(!empty($group['tags']))
                                    <div>
                                        <div class="text-[10px] text-text-sub uppercase tracking-wider mb-1">
                                            {{ $group['label'] ?? '' }}
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($group['tags'] as $tag)
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-accent/10 border border-line-accent/30 text-[11px] text-text-main">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </x-ui.card>
                @endif

                {{-- ACCESS card --}}
                <x-ui.card class="p-5">
                    <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                        <i class="fas fa-location-dot"></i> ACCESS
                    </h3>
                    <div class="flex flex-col gap-3">
                        @if($addressLine !== '')
                            <div class="flex flex-col gap-1">
                                <span class="text-[12px] text-text-sub font-medium">住所</span>
                                <span class="text-[13px] font-bold text-text-main">{{ $addressLine }}</span>
                            </div>
                        @endif
                        @if($nearestStation !== '')
                            <div class="flex justify-between items-center border-t border-line pt-2">
                                <span class="text-[12px] text-text-sub font-medium">最寄り駅</span>
                                <span class="text-[13px] font-bold text-text-main text-right">{{ $nearestStation }}</span>
                            </div>
                        @endif
                        @if($businessHours !== '')
                            <div class="flex justify-between items-center border-t border-line pt-2">
                                <span class="text-[12px] text-text-sub font-medium">営業時間</span>
                                <span class="text-[13px] font-bold text-text-main text-right">{{ $businessHours }}</span>
                            </div>
                        @endif
                    </div>
                </x-ui.card>

            </div>
        </div>
    </div>
</div>

{{-- ライトボックス（共通） --}}
<div id="lightbox-overlay" class="lightbox-overlay" onclick="closeLightbox(event)">
    <img id="lightbox-image" src="" alt="" class="lightbox-image">
    <button type="button" class="lightbox-close" aria-label="閉じる" onclick="closeLightbox(event)">
        <i class="fas fa-times"></i>
    </button>
</div>
