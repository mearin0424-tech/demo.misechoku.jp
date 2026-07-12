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

        {{-- ===== ヘッダー：アイコン + ひとこと吹き出し（MyPage / 店舗プロフィールと同じ形式） ===== --}}
        @php
            $word = trim((string) ($cast['word'] ?? ''));
            $bubbleText = $word !== '' ? $word : $intro;
            $bubbleTrunc = $bubbleText !== ''
                ? (mb_strlen($bubbleText) > 90 ? mb_substr($bubbleText, 0, 90) . '…' : $bubbleText)
                : '';
        @endphp
        <div class="flex items-start gap-3 mb-3">
            <div class="w-[84px] h-[84px] rounded-full overflow-hidden border-2 border-line-accent/40 shadow-card-3d bg-surface-from shrink-0">
                <img src="{{ $iconImage }}" alt="" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0">
                <div class="relative min-h-[84px] flex flex-col justify-center bg-gradient-to-br from-surface-from to-base border border-line-accent/40 rounded-2xl shadow-card-3d px-3 py-2.5">
                    {{-- 吹き出しのしっぽ（アイコン側に向く） --}}
                    <span class="absolute top-5 -left-[8px] w-0 h-0 border-y-[8px] border-y-transparent border-r-[10px] border-r-line-accent/40"></span>
                    <span class="absolute top-5 -left-[6px] w-0 h-0 border-y-[7px] border-y-transparent border-r-[9px] border-r-surface-from"></span>
                    <p class="text-[13px] leading-relaxed {{ $bubbleTrunc !== '' ? 'text-text-main' : 'text-text-sub' }}">
                        {{ $bubbleTrunc !== '' ? $bubbleTrunc : 'ひとことはまだ登録されていません' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ===== 名前/年齢 + ライク数 + 場所 ===== --}}
        <div class="mb-4 flex flex-col gap-1.5">
            <div class="flex items-center justify-between gap-2">
                <h1 class="app-title text-[22px] text-text-main leading-tight truncate min-w-0">
                    {{ $castDisplayName }}@if($age)<span class="text-[16px] text-text-sub ml-1">({{ $age }})</span>@endif
                </h1>
                <div class="flex items-center gap-1 shrink-0" title="受け取ったいいね">
                    <i class="fas fa-heart text-[15px] text-discovery-pink"></i>
                    <span class="font-bold text-[13px] text-text-main" data-fav-count-target="cast:{{ $favCastId ?? ($cast['id'] ?? '') }}">{{ number_format($likeCount) }}</span>
                </div>
            </div>
            @if($location !== '' || !empty($distanceLabel ?? null))
                <div class="flex flex-wrap items-center gap-1.5 text-[12px] text-text-sub">
                    <span class="inline-flex items-center gap-1">
                        <i class="fas fa-map-marker-alt text-[10px]"></i>{{ $location !== '' ? $location : '位置情報未設定' }}
                    </span>
                    @if(!empty($distanceLabel ?? null))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-accent/10 border border-line-accent/30 text-accent-text text-[10.5px] font-bold">
                            <i class="fas fa-route text-[9px]"></i> {{ $distanceLabel }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- ===== アクション（店舗プロフィールと同じ構成：シンプルな単色 Primary + 中央寄せアイコン列） ===== --}}
        @if($isOwn)
            <a href="{{ route('cast.profile.edit') }}"
               class="flex w-full items-center justify-center gap-2 px-6 py-3 rounded-full font-bold bg-accent text-on-accent shadow-[0_4px_12px_rgba(0,0,0,0.4)] active:scale-[0.98] transition-transform duration-150 mb-5">
                <i class="fas fa-pen"></i> プロフィール編集
            </a>
        @elseif($showInteractionActions)
            @php $favCastId = (string) ($cast['id'] ?? $cast['cast_id'] ?? ''); @endphp
            {{-- トーク / LIKE / KEEP / 共有 の横一列。トークが flex-1 で最も目立つ Primary --}}
            <div class="fav-actions-row">
                @if($favCastId !== '' && Route::has('shop.talk.room'))
                    <a href="{{ route('shop.talk.room', ['id' => $favCastId, 'talk_topic' => 'other', 'initiate' => 1]) }}"
                       class="fav-actions-row__primary inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-full font-bold bg-accent text-on-accent shadow-[0_4px_12px_rgba(0,0,0,0.4)] active:scale-[0.98] transition-transform duration-150">
                        <i class="fas fa-comment-dots"></i> トークする
                    </a>
                @endif
                <button type="button" id="btn-profile-like"
                        class="fav-circle fav-circle--like"
                        data-fav-toggle data-action="like" data-item-type="cast" data-item-id="{{ $favCastId }}"
                        aria-label="いいね" aria-pressed="{{ ($cast['is_liked'] ?? false) ? 'true' : 'false' }}">
                    <i class="fas fa-heart" aria-hidden="true"></i>
                    <span class="fav-circle__cap">LIKE</span>
                </button>
                <button type="button" id="btn-profile-keep"
                        class="fav-circle fav-circle--keep"
                        data-fav-toggle data-action="keep" data-item-type="cast" data-item-id="{{ $favCastId }}"
                        aria-label="キープ" aria-pressed="{{ ($cast['is_kept'] ?? false) ? 'true' : 'false' }}">
                    <i class="fas fa-bookmark" aria-hidden="true"></i>
                    <span class="fav-circle__cap">KEEP</span>
                </button>
                @if(!empty($shareUrl))
                    <div class="shrink-0 flex flex-col items-center">
                        @include('partials.share-menu', [
                            'shareUrl' => $shareUrl,
                            'shareTitle' => $shareTitle ?? ($castDisplayName . 'のプロフィール'),
                            'shareText' => $shareText ?? $intro,
                            'menuId' => 'cast-share-menu',
                        ])
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ===== Tabs ===== --}}
    <div data-tabs-scope>
        {{-- タブ：MyPage と同じデザイン・名称（アイコン + 英字ラベル）に統一 --}}
        <div data-tabs class="border-t border-b border-line-accent/40 bg-base/90 backdrop-blur-md sticky top-0 z-10">
            <div class="flex">
                <button type="button" data-tab="gallery"
                        class="is-active flex-1 py-3 flex flex-col items-center justify-center gap-0.5 transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
                    <i class="fas fa-images text-[14px]"></i>
                    <span class="app-title text-[10px] tracking-widest">GALLERY</span>
                </button>
                <button type="button" data-tab="details"
                        class="flex-1 py-3 flex flex-col items-center justify-center gap-0.5 transition-colors border-b-2 border-transparent text-text-sub [&.is-active]:text-accent-text [&.is-active]:border-accent">
                    <i class="fas fa-address-card text-[14px]"></i>
                    <span class="app-title text-[10px] tracking-widest">PROFILE</span>
                </button>
            </div>
        </div>

        {{-- GALLERY：登録済み写真のみ。空スロットは出さない --}}
        <div data-tab-panel="gallery" class="is-active">
            @if(count($profileImages) > 0)
                <ul id="profile-gallery-list">
                    @foreach($profileImages as $i => $img)
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
                            {{-- inline @else は直前に単語文字があると Blade にコンパイルされず
                                 「cm@else--」がそのまま表示されるバグがあったため三項演算子に変更 --}}
                            <span class="text-[13px] font-bold text-text-main text-right">
                                {{ !empty($cast['height']) ? $cast['height'] . 'cm' : '--' }} / {{ !empty($cast['weight']) ? $cast['weight'] . 'kg' : '--' }}
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
                        {{-- 接客タイプ：目立つカード（タップで解説。他者表示なので再診断導線なし） --}}
                        @include('casts.profile.parts.personality-type', [
                            'typeCode'  => $cast['personality_type'] ?? '',
                            'canRetest' => false,
                        ])
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
                        <div class="flex justify-between items-start gap-3 border-b border-line pb-2">
                            <span class="text-[12px] text-text-sub font-medium shrink-0">現職業</span>
                            <span class="text-[13px] font-bold text-text-main text-right">
                                {!! nl2br(e(!empty($cast['profession']) ? $cast['profession'] : (!empty($cast['current_job']) ? $cast['current_job'] : '--'))) !!}
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
