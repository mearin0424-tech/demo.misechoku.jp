{{-- 店舗プロフィール：cast 側からの閲覧専用ビュー（MyPage 同様の Instagram 風） --}}
@php
    $shopName     = $shop['name'] ?? ($recruit['store_name'] ?? '店舗');
    $iconImage    = $shop['main_img'] ?? ($galleryImages[0] ?? asset('assets/images/common/no-image.png'));
    $catchCopy    = trim((string) ($recruit['catch_copy'] ?? ''));
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
    $bonusRemarks    = trim((string) ($recruit['bonus_remarks'] ?? ''));
    $bonusDaysLocal  = trim((string) ($recruit['bonus_total_working_days'] ?? $recruit['bonus_working_days'] ?? ''));
    $bonusHoursLocal = trim((string) ($recruit['bonus_total_working_hours'] ?? $recruit['bonus_working_hours'] ?? ''));
    $bonusExtraLocal = trim((string) ($recruit['bonus_other_conditions'] ?? $recruit['bonus_condition'] ?? ''));
    $storeAtmosphere = trim((string) ($recruit['store_atmosphere'] ?? ''));
    $catchCopyMain   = $catchCopy;

    // ----- ヘッダー用：アピールひとこと（hitokoto を最優先、なければ catch_copy） -----
    $appealLine      = $shopWord !== '' ? $shopWord : $catchCopy;
    $appealTrunc     = $appealLine !== ''
        ? (mb_strlen($appealLine) > 90 ? mb_substr($appealLine, 0, 90) . '…' : $appealLine)
        : '';

    // ----- ヘッダー用：最有力の時給表示（本入り→体入の順で採用） -----
    $primaryWageDisp = $regularWageDisp ?: ($trialWageDisp ?: $helpWageDisp);
    $primaryWageLabel = $regularWageDisp
        ? '本入り時給'
        : ($trialWageDisp ? '体入時給' : ($helpWageDisp ? 'ヘルプ時給' : '時給'));
@endphp

<div class="pb-6">

    <div class="px-5 pt-4 pb-5">

        {{-- 1. アバター + ひとこと吹き出し（キャスト MyPage と同じ吹き出し式） --}}
        <div class="flex items-start gap-3 mb-3">
            <div class="w-[84px] h-[84px] rounded-2xl overflow-hidden border-2 border-line-accent/40 shadow-card-3d bg-surface-from shrink-0">
                <img src="{{ $iconImage }}" alt="" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0">
                <div class="relative min-h-[84px] flex flex-col justify-center bg-gradient-to-br from-surface-from to-base border border-line-accent/40 rounded-2xl shadow-card-3d px-3 py-2.5">
                    {{-- 吹き出しのしっぽ（アイコン側に向く） --}}
                    <span class="absolute top-5 -left-[8px] w-0 h-0 border-y-[8px] border-y-transparent border-r-[10px] border-r-line-accent/40"></span>
                    <span class="absolute top-5 -left-[6px] w-0 h-0 border-y-[7px] border-y-transparent border-r-[9px] border-r-surface-from"></span>
                    <p class="text-[13px] leading-relaxed {{ $appealTrunc !== '' ? 'text-text-main' : 'text-text-sub' }}">
                        {{ $appealTrunc !== '' ? $appealTrunc : 'ひとことはまだ登録されていません' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- 2. 店名 + ライク + エリア/業種 --}}
        <div class="mb-4 flex flex-col gap-1.5">
            <div class="flex items-center justify-between gap-2">
                <h1 class="app-title text-[22px] text-text-main leading-tight truncate min-w-0">{{ $shopName }}</h1>
                <div class="flex items-center gap-1 shrink-0">
                    <i class="fas fa-heart text-[15px] text-discovery-pink"></i>
                    <span class="font-bold text-[13px] text-text-main" data-fav-count-target="shop:{{ $ctaShopId }}">{{ number_format($likeCount) }}</span>
                </div>
            </div>
            @if($areaChip !== '' || $industryName !== '')
                <div class="flex items-center gap-1.5 text-text-sub text-[12px] flex-wrap">
                    @if($areaChip !== '')
                        <span class="inline-flex items-center gap-1"><i class="fas fa-map-marker-alt text-[10px]"></i>{{ $areaChip }}</span>
                    @endif
                    @if($industryName !== '')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-accent/10 border border-line-accent/40 text-accent-text font-bold tracking-wide text-[10.5px]">
                            <i class="fas fa-tag text-[9px]"></i>{{ $industryName }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- 3. ボーナス金 / 時給：枠なしのタイポグラフィ主体（採用・入金管理の合計表示と同系統） --}}
        @if($showBonusMain || $primaryWageDisp)
            <div class="mb-4 pb-3 flex items-stretch gap-4"
                 style="border-bottom: 1px solid transparent; border-image: linear-gradient(to right, rgba(246, 211, 106, 0.4), rgba(246, 211, 106, 0.06) 70%, transparent) 1;">
                @if($showBonusMain)
                    <div class="flex-1 min-w-0 flex flex-col gap-1">
                        <span class="text-[10px] font-extrabold tracking-[0.16em] text-amber-300/90 uppercase">入店ボーナス</span>
                        <span class="text-[26px] font-black leading-none tracking-tight bg-gradient-to-br from-amber-200 via-amber-300 to-amber-500 bg-clip-text text-transparent"
                              style="font-variant-numeric: tabular-nums;">¥{{ number_format($noruma) }}</span>
                        @if($bonusConditionsText !== '')
                            <span class="text-[10px] text-text-sub leading-snug line-clamp-1">{{ $bonusConditionsText }}</span>
                        @endif
                    </div>
                @endif
                @if($showBonusMain && $primaryWageDisp)
                    <div class="w-px bg-line self-stretch"></div>
                @endif
                @if($primaryWageDisp)
                    <div class="flex-1 min-w-0 flex flex-col gap-1">
                        <span class="text-[10px] font-extrabold tracking-[0.16em] text-accent-text uppercase">{{ $primaryWageLabel }}</span>
                        <span class="text-[22px] font-extrabold leading-none tracking-tight text-text-main" style="font-variant-numeric: tabular-nums;">{{ $primaryWageDisp }}</span>
                    </div>
                @endif
            </div>
        @endif

        {{-- 4. アクション CTA：TALK を最上位に、応募系・KEEP・共有を横に --}}
        @php
            $isShopPreview = $isShopPreview ?? empty($forCast);
            // 店舗プレビュー時は cast.talk.room が member 認証必須のため、リンク先を '#' にして
            // クリックしても遷移しないようにし、プレビュー用の注釈を出す
            $mkTalkHref = function (array $params) use ($isShopPreview) {
                return $isShopPreview ? '#' : route('cast.talk.room', $params);
            };
        @endphp
        @if(!empty($ctaShopId))
            <div class="flex flex-col gap-2 mb-2">
                {{-- 最重要：TALK 遷移（求人未登録でも常に表示） --}}
                {{-- シンプルなソリッド1色のプライマリ（グラデ・重い立体は使わない） --}}
                <a href="{{ $mkTalkHref(['id' => $ctaShopId, 'talk_topic' => 'other', 'initiate' => 1]) }}"
                   @if($isShopPreview) aria-disabled="true" title="プレビュー：求職者はここからトークに遷移します" onclick="return false;" @endif
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-bold bg-accent text-on-accent shadow-[0_4px_12px_rgba(0,0,0,0.4)] active:scale-[0.98] transition-transform duration-150 {{ $isShopPreview ? 'cursor-default' : '' }}">
                    <i class="fas fa-comment-dots"></i> トークで話を聞いてみる
                </a>

                {{-- 応募系（求人が登録されている場合のみ） --}}
                @if($ctaHasTrial || $ctaHasHelp)
                    <div class="flex gap-2">
                        @if($ctaHasTrial)
                            <a href="{{ $mkTalkHref(['id' => $ctaShopId, 'job_kind' => 'trial', 'talk_topic' => 'new_hire', 'initiate' => 1]) }}"
                               @if($isShopPreview) aria-disabled="true" onclick="return false;" @endif
                               class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-full font-bold text-[12.5px] border border-line-accent/40 bg-accent/10 text-accent-text transition-all duration-300 {{ $isShopPreview ? 'cursor-default' : '' }}">
                                <i class="fas fa-paper-plane text-[11px]"></i> 新規採用に応募
                            </a>
                        @endif
                        @if($ctaHasHelp)
                            <a href="{{ $mkTalkHref(['id' => $ctaShopId, 'job_kind' => 'help', 'talk_topic' => 'help', 'initiate' => 1]) }}"
                               @if($isShopPreview) aria-disabled="true" onclick="return false;" @endif
                               class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-full font-bold text-[12.5px] border border-line-accent/40 bg-accent/10 text-accent-text transition-all duration-300 {{ $isShopPreview ? 'cursor-default' : '' }}">
                                <i class="fas fa-hand-holding-heart text-[11px]"></i> ヘルプ応募
                            </a>
                        @endif
                    </div>
                @endif

                {{-- LIKE / KEEP / 共有：CTA 直下に中央寄せの均等3アイコン列 --}}
                <div class="flex items-start justify-center gap-8 pt-2" style="margin-bottom: 18px;">
                    <button type="button"
                            @if($isShopPreview) disabled title="プレビュー：求職者はここでいいねできます" @endif
                            class="fav-circle fav-circle--like {{ $isShopPreview ? 'opacity-70' : '' }}"
                            aria-label="いいね"
                            aria-pressed="{{ !empty($shop['is_liked']) ? 'true' : 'false' }}"
                            @unless($isShopPreview) data-fav-toggle @endunless
                            data-item-id="{{ $shop['id'] ?? '' }}"
                            data-item-type="shop"
                            data-action="like">
                        <i class="fas fa-heart" aria-hidden="true"></i>
                        <span class="fav-circle__cap">LIKE</span>
                    </button>
                    <button type="button"
                            @if($isShopPreview) disabled title="プレビュー：求職者はここでキープできます" @endif
                            class="fav-circle fav-circle--keep {{ $isShopPreview ? 'opacity-70' : '' }}"
                            aria-label="キープ"
                            aria-pressed="{{ !empty($recruit['is_kept']) ? 'true' : 'false' }}"
                            @unless($isShopPreview) data-fav-toggle @endunless
                            data-item-id="{{ $shop['id'] ?? '' }}"
                            data-item-type="shop"
                            data-action="keep">
                        <i class="fas fa-bookmark" aria-hidden="true"></i>
                        <span class="fav-circle__cap">KEEP</span>
                    </button>
                    @if(!empty($shareUrlResolved))
                        <div class="flex flex-col items-center">
                            @include('partials.share-menu', [
                                'shareUrl' => $shareUrlResolved,
                                'shareTitle' => $shareTitleResolved,
                                'shareText' => $shareTextResolved,
                                'menuId' => 'recruit-share-menu',
                            ])
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Tabs: GALLERY / JOB / SHOP --}}
    <div data-tabs-scope>
        <div data-tabs class="border-t border-b border-line-accent/40 bg-base/90 backdrop-blur-md sticky top-0 z-10">
            <div class="flex">
                <button type="button" data-tab="gallery"
                        class="is-active flex-1 py-3 flex flex-col items-center justify-center gap-0.5 transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
                    <i class="fas fa-images text-[14px]"></i>
                    <span class="app-title text-[10px] tracking-widest">GALLERY</span>
                </button>
                <button type="button" data-tab="job"
                        class="flex-1 py-3 flex flex-col items-center justify-center gap-0.5 transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
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

        {{-- ========== JOB（求人情報） ========== --}}
        <div data-tab-panel="job">
            <div class="p-4 flex flex-col gap-4">

                {{-- CATCH（キャッチコピー）：ヘッダーの吹き出しに表示済みの文言と重複しない場合のみ掲載 --}}
                @if($catchCopyMain !== '' && $catchCopyMain !== $appealLine)
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-3 flex items-center gap-2">
                            <i class="fas fa-bullhorn"></i> CATCH
                        </h3>
                        <p class="text-[13.5px] leading-relaxed text-text-main whitespace-pre-line">{{ $catchCopyMain }}</p>
                    </x-ui.card>
                @endif

                {{-- BONUS card --}}
                @if($showBonusMain || $bonusConditionsText !== '' || $bonusRemarks !== '')
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                            <i class="fas fa-gift"></i> BONUS
                        </h3>
                        <div class="flex flex-col gap-3">
                            <div class="flex justify-between items-center border-b border-line pb-2">
                                <span class="text-[12px] text-text-sub font-medium">ボーナス金</span>
                                <span class="text-[14px] font-extrabold text-amber-400">¥{{ number_format($noruma) }}</span>
                            </div>
                            @if($bonusDaysLocal !== '')
                                <div class="flex justify-between items-center border-b border-line pb-2">
                                    <span class="text-[12px] text-text-sub font-medium">達成勤務日数</span>
                                    <span class="text-[13px] font-bold text-text-main text-right">{{ $bonusDaysLocal }}日以上</span>
                                </div>
                            @endif
                            @if($bonusHoursLocal !== '')
                                <div class="flex justify-between items-center border-b border-line pb-2">
                                    <span class="text-[12px] text-text-sub font-medium">達成勤務時間</span>
                                    <span class="text-[13px] font-bold text-text-main text-right">{{ $bonusHoursLocal }}時間以上</span>
                                </div>
                            @endif
                            @if($bonusExtraLocal !== '')
                                <div class="flex flex-col gap-1 border-b border-line pb-2">
                                    <span class="text-[12px] text-text-sub font-medium">その他の条件</span>
                                    <span class="text-[13px] text-text-main leading-relaxed whitespace-pre-line">{{ $bonusExtraLocal }}</span>
                                </div>
                            @endif
                            @if($bonusRemarks !== '')
                                <div class="flex flex-col gap-1">
                                    <span class="text-[12px] text-text-sub font-medium">備考</span>
                                    <span class="text-[13px] text-text-main leading-relaxed whitespace-pre-line">{{ $bonusRemarks }}</span>
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

                {{-- WORD カードは廃止（ひとことはヘッダーの吹き出しに集約）。雰囲気のみ掲載 --}}
                @if($storeAtmosphere !== '')
                    <x-ui.card class="p-5">
                        <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-3 flex items-center gap-2">
                            <i class="fas fa-champagne-glasses"></i> ATMOSPHERE
                        </h3>
                        <div class="text-[13px] text-text-main leading-relaxed whitespace-pre-line">{{ $storeAtmosphere }}</div>
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
