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

        {{-- エリア表示 --}}
        @if($areaChip !== '')
            <div class="flex items-center gap-2 mb-4 text-text-sub text-[12px]">
                <i class="fas fa-map-marker-alt"></i>{{ $areaChip }}
            </div>
        @endif

        {{-- ボーナス金バッジ --}}
        @if($showBonusMain)
            <div class="mb-5">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-[#D4AF37] to-[#B8860B] text-[#111] font-bold border border-amber-400/40 shadow-[inset_0_4px_6px_rgba(255,255,255,0.4),inset_0_-6px_6px_rgba(0,0,0,0.4),0_8px_16px_rgba(0,0,0,0.7)]">
                    <span class="text-[10px] tracking-wider opacity-90">ボーナス金</span>
                    <span class="text-[18px] tracking-wider font-extrabold">¥{{ number_format($noruma) }}</span>
                </div>
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

        {{-- GALLERY：登録済み画像のみ表示（空スロットは出さない） --}}
        <div data-tab-panel="gallery" class="is-active">
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

        {{-- DETAILS --}}
        <div data-tab-panel="details">
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
                @if($messageBody !== '' || $jobSupplementMain !== '')
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                            <i class="fas fa-comment-dots"></i> ABOUT
                        </h3>
                        @if($messageBody !== '')
                            <div class="text-[13px] text-text-main leading-relaxed whitespace-pre-line">{{ $messageBody }}</div>
                        @endif
                        @if($jobSupplementMain !== '')
                            <div class="mt-3 pt-3 border-t border-line">
                                <span class="text-[12px] text-text-sub font-medium block mb-1">仕事内容</span>
                                <div class="text-[13px] text-text-main leading-relaxed whitespace-pre-line">{{ $jobSupplementMain }}</div>
                            </div>
                        @endif
                    </x-ui.card>
                @endif

                {{-- FEATURES card --}}
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
                                <span class="text-[13px] font-bold text-text-main">{{ $nearestStation }}</span>
                            </div>
                        @endif
                        @if($businessHours !== '')
                            <div class="flex justify-between items-center border-t border-line pt-2">
                                <span class="text-[12px] text-text-sub font-medium">営業時間</span>
                                <span class="text-[13px] font-bold text-text-main">{{ $businessHours }}</span>
                            </div>
                        @endif
                        @if($workingHours !== '')
                            <div class="flex justify-between items-center border-t border-line pt-2">
                                <span class="text-[12px] text-text-sub font-medium">勤務時間</span>
                                <span class="text-[13px] font-bold text-text-main">{{ $workingHours }}</span>
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
