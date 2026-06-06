{{-- キャストプロフィール本文：MyPage と同じ Instagram 風デザイン（GALLERY / DETAILS タブ） --}}
@php
    $isOwn = $isOwn ?? false;
    $showInteractionActions = $showInteractionActions ?? true;
    $profileImages = !empty($cast['images']) ? array_values(array_filter((array) $cast['images'])) : [];
    if (empty($profileImages) && !empty($cast['img'])) {
        $profileImages = [$cast['img']];
    }
    $iconImage = $profileImages[0] ?? asset('assets/images/common/no-image.png');
    $castDisplayName = $cast['nickname'] ?? $cast['name'] ?? 'キャスト';
    $age = $cast['age'] ?? null;
    $intro = trim((string) ($cast['intro'] ?? $cast['pr'] ?? ''));
    $introTrunc = mb_substr($intro, 0, 80) . (mb_strlen($intro) > 80 ? '…' : '');
    $likeCount = (int) ($cast['like_cnt'] ?? 0);
    $location = trim(implode(' / ', array_filter([$cast['pref'] ?? null, $cast['city'] ?? null])));
    $bwh = trim(implode(' / ', [
        ($cast['bust'] ?? $cast['b'] ?? '') ?: '--',
        ($cast['waist'] ?? $cast['w'] ?? '') ?: '--',
        ($cast['hip'] ?? $cast['h'] ?? '') ?: '--',
    ]));
    $totalSlots = max(9, count($profileImages));
@endphp

<div class="pb-6">

    <div class="px-5 pt-4 pb-6">

        {{-- ===== アイコン + 紹介文（読み取り専用の吹き出し） ===== --}}
        <div class="flex items-start gap-3 mb-5">
            <div class="w-[84px] h-[84px] rounded-full overflow-hidden border-2 border-line-accent/40 shadow-card-3d bg-surface-from shrink-0">
                <img src="{{ $iconImage }}" alt="" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0">
                <div class="relative bg-gradient-to-br from-surface-from to-base border border-line-accent/40 rounded-2xl shadow-card-3d p-3">
                    <span class="absolute top-5 -left-[8px] w-0 h-0 border-y-[8px] border-y-transparent border-r-[10px] border-r-line-accent/40"></span>
                    <span class="absolute top-5 -left-[6px] w-0 h-0 border-y-[7px] border-y-transparent border-r-[9px] border-r-surface-from"></span>
                    <p class="text-[13px] leading-relaxed {{ $intro === '' ? 'text-text-sub' : 'text-text-main' }}">
                        {{ $intro !== '' ? $introTrunc : 'プロフィール紹介文は未設定です' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ===== 名前 + ライク（横並び） ===== --}}
        <div class="flex items-center justify-between gap-3 mb-3">
            <h1 class="app-title text-[24px] text-text-main leading-tight truncate min-w-0">
                {{ $castDisplayName }}@if($age)<span class="text-[18px] text-text-sub ml-1">({{ $age }})</span>@endif
            </h1>
            <div class="flex items-center gap-1.5 shrink-0">
                <i class="fas fa-heart text-[18px] text-discovery-pink"></i>
                <span class="font-bold text-[14px] text-text-main">{{ number_format($likeCount) }}</span>
            </div>
        </div>

        {{-- ===== 場所 / 距離 ===== --}}
        @if($location !== '' || !empty($distanceLabel ?? null))
            <div class="flex flex-wrap items-center gap-2 mb-5 text-[12px] text-text-sub">
                <span class="inline-flex items-center gap-1">
                    <i class="fas fa-map-marker-alt"></i>{{ $location !== '' ? $location : '位置情報未設定' }}
                </span>
                @if(!empty($distanceLabel ?? null))
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-accent/10 border border-line-accent/30 text-accent-text">
                        <i class="fas fa-route"></i> {{ $distanceLabel }}
                    </span>
                @endif
            </div>
        @endif

        {{-- ===== アクション ===== --}}
        @if($isOwn)
            <a href="{{ route('cast.profile.edit') }}"
               class="flex w-full items-center justify-center gap-2 px-6 py-3 rounded-full font-bold bg-gradient-to-r from-accent-grad-from to-accent-grad-to text-on-accent-strong shadow-btn-3d transition-all duration-300 mb-5">
                <i class="fas fa-pen"></i> プロフィール編集
            </a>
        @elseif($showInteractionActions)
            <div class="flex gap-3 mb-3">
                <button type="button" id="btn-profile-keep"
                        aria-pressed="{{ ($cast['is_kept'] ?? false) ? 'true' : 'false' }}"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full font-bold border border-line-accent/40 bg-base/60 text-text-main transition-all duration-300 {{ ($cast['is_kept'] ?? false) ? 'bg-accent/15 text-accent-text border-accent' : '' }}">
                    <i class="fas fa-bookmark"></i> <span>KEEP</span>
                </button>
                <button type="button" id="btn-profile-like" data-count="{{ $likeCount }}"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full font-bold bg-gradient-to-r from-accent-grad-from to-accent-grad-to text-on-accent-strong shadow-btn-3d active:translate-y-1.5 active:shadow-btn-3d-active transition-all duration-300">
                    <i class="fas fa-heart"></i> <span>LIKE</span>
                </button>
            </div>
        @endif

        {{-- 共有メニュー --}}
        @if(!empty($shareUrl) && !$isOwn)
            <div class="flex justify-end mb-3">
                @include('partials.share-menu', [
                    'shareUrl' => $shareUrl,
                    'shareTitle' => $shareTitle ?? ($castDisplayName . 'のプロフィール'),
                    'shareText' => $shareText ?? $intro,
                    'menuId' => 'cast-share-menu',
                ])
            </div>
        @endif
    </div>

    {{-- ===== Tabs ===== --}}
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

        {{-- GALLERY：Instagram 風 3 列・読み取り専用・タップでライトボックス --}}
        <div data-tab-panel="gallery" class="is-active">
            <ul id="profile-gallery-list">
                @for($i = 0; $i < $totalSlots; $i++)
                    @php $img = $profileImages[$i] ?? null; @endphp
                    <li class="profile-gallery-item">
                        @if($img)
                            <button type="button" class="profile-gallery-slot has-img js-lightbox-target" data-image-url="{{ $img }}" aria-label="写真 {{ $i + 1 }} を拡大">
                                <img src="{{ $img }}" alt="" loading="lazy">
                                @if($i === 0)<span class="profile-gallery-badge">MAIN</span>@endif
                            </button>
                        @else
                            <div class="profile-gallery-slot">
                                <span class="profile-gallery-empty"><i class="far fa-image"></i></span>
                            </div>
                        @endif
                    </li>
                @endfor
            </ul>
        </div>

        {{-- DETAILS：スペック・自己PR・接客タイプ・キャリア --}}
        <div data-tab-panel="details">
            <div class="p-4 flex flex-col gap-4">

                <x-ui.card class="p-5">
                    <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                        <i class="fas fa-user-circle"></i> BASIC
                    </h3>
                    <div class="flex flex-col gap-3">
                        @if(!empty($cast['birth_year']) && !empty($cast['birth_month']) && !empty($cast['birth_day']))
                            <div class="flex justify-between items-center border-b border-line pb-2">
                                <span class="text-[12px] text-text-sub font-medium">生年月日</span>
                                <span class="text-[13px] font-bold text-text-main">{{ $cast['birth_year'] }}年{{ $cast['birth_month'] }}月{{ $cast['birth_day'] }}日</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">身長 / 体重</span>
                            <span class="text-[13px] font-bold text-text-main">
                                @if(!empty($cast['height'])){{ $cast['height'] }}cm@else--@endif /
                                @if(!empty($cast['weight'])){{ $cast['weight'] }}kg@else--@endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">B / W / H</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $bwh }}</span>
                        </div>
                        @if($intro !== '')
                            <div class="flex flex-col gap-1">
                                <span class="text-[12px] text-text-sub font-medium">自己PR</span>
                                <span class="text-[13px] font-medium text-text-main leading-relaxed">{!! nl2br(e($intro)) !!}</span>
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <x-ui.card class="p-5">
                    <h3 class="app-title text-[13px] tracking-widest text-accent-text mb-4 flex items-center gap-2">
                        <i class="fas fa-tags"></i> STYLE &amp; TAGS
                    </h3>
                    <div class="flex flex-col gap-3">
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">接客タイプ</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $cast['personality_type'] ?? '--' }}</span>
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
                        <i class="fas fa-briefcase"></i> CAREER
                    </h3>
                    <div class="flex flex-col gap-3">
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">ナイトワーク経験</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $cast['night_work_label'] ?? '--' }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium">現職業</span>
                            <span class="text-[13px] font-bold text-text-main">
                                @if(!empty($cast['profession'])){!! nl2br(e($cast['profession'])) !!}@elseif(!empty($cast['current_job'])){!! nl2br(e($cast['current_job'])) !!}@else--@endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[12px] text-text-sub font-medium">希望職種</span>
                            <span class="text-[13px] font-bold text-text-main">{{ $cast['industry_names'] ?? ($cast['desired_job'] ?? '--') }}</span>
                        </div>
                    </div>
                </x-ui.card>

                @if($isOwn)
                    <a href="{{ route('cast.mypage.reviews') }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-bold border border-line-accent/40 bg-base/60 text-text-main hover:bg-accent/10 transition-all duration-300">
                        <i class="fas fa-star"></i> レビュー一覧を見る
                    </a>
                @endif

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
