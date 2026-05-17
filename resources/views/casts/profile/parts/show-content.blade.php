{{-- キャストプロフィール本文（shop/castprofileview と cast/mypage で共通） --}}
@php $isOwn = $isOwn ?? false; @endphp
@php $showInteractionActions = $showInteractionActions ?? true; @endphp
@php
    $profileImages = !empty($cast['images']) ? array_values(array_filter((array) $cast['images'])) : [];
    if (empty($profileImages) && !empty($cast['img'])) {
        $profileImages = [$cast['img']];
    }
    $castDisplayName = $cast['nickname'] ?? $cast['name'] ?? 'キャスト';
    $imageCount = count($profileImages);
@endphp
<section class="profile-hero" aria-label="プロフィール写真">
    <div class="profile-hero-inner">
        <div class="profile-hero-carousel" id="profile-hero-carousel" role="list">
            @foreach($profileImages as $hi => $imgUrl)
                <div class="profile-hero-slide"
                     data-hero-slide="{{ $hi }}"
                     role="listitem"
                     aria-roledescription="slide"
                     aria-label="{{ $hi + 1 }} / {{ $imageCount }}">
                    <img src="{{ $imgUrl }}"
                         alt="{{ $castDisplayName }} 写真 {{ $hi + 1 }}"
                         class="profile-hero-img js-lightbox-target"
                         loading="{{ $hi === 0 ? 'eager' : 'lazy' }}">
                </div>
            @endforeach
        </div>
        <div class="profile-hero-gradient"></div>

        @if($imageCount > 1)
            <button type="button" class="profile-hero-arrow profile-hero-arrow--prev" id="profile-hero-prev" aria-label="前の写真を表示">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <button type="button" class="profile-hero-arrow profile-hero-arrow--next" id="profile-hero-next" aria-label="次の写真を表示">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
            <div class="profile-hero-dots" id="profile-hero-dots" role="tablist" aria-label="プロフィール写真の切り替え">
                @foreach($profileImages as $hi => $_)
                    <button type="button"
                            class="profile-hero-dot {{ $hi === 0 ? 'is-active' : '' }}"
                            data-hero-goto="{{ $hi }}"
                            role="tab"
                            aria-selected="{{ $hi === 0 ? 'true' : 'false' }}"
                            aria-label="写真 {{ $hi + 1 }}"></button>
                @endforeach
            </div>
            <span class="profile-hero-counter" id="profile-hero-counter">
                <span id="profile-hero-counter-current">1</span> / {{ $imageCount }}
            </span>
        @endif
    </div>
</section>

@if($imageCount > 1)
    <div class="profile-photo-strip">
        @foreach($profileImages as $index => $imgUrl)
            <button type="button"
                    class="profile-photo-thumb {{ $index === 0 ? 'active' : '' }}"
                    data-hero-goto="{{ $index }}"
                    aria-label="写真{{ $index + 1 }}を表示">
                <img src="{{ $imgUrl }}" alt="">
            </button>
        @endforeach
    </div>
@endif

<div class="profile-main-contents">
    <div class="profile-view-inner">
        <header class="cast-header">
            <div class="recruit-ref-title-row">
                <h1 class="cast-name serif-font">{{ $cast['nickname'] ?? $cast['name'] }}<span class="cast-age">({{ $cast['age'] }})</span></h1>
                @if(!empty($shareUrl) && !$isOwn)
                    @include('partials.share-menu', [
                        'shareUrl' => $shareUrl,
                        'shareTitle' => $shareTitle ?? (($cast['nickname'] ?? $cast['name']) . 'のプロフィール'),
                        'shareText' => $shareText ?? ($cast['intro'] ?? $cast['pr'] ?? ''),
                        'menuId' => 'cast-share-menu',
                    ])
                @endif
            </div>
            <p class="cast-location">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                <span>@if(!empty($cast['pref']) || !empty($cast['city'])){{ implode(' / ', array_filter([$cast['pref'] ?? null, $cast['city'] ?? null])) }} / @endifキャスト</span>
                @if(!empty($distanceLabel ?? null))
                    <span class="distance-badge" style="margin-left:6px;">
                        <i class="fas fa-route"></i> 現在位置から {{ $distanceLabel }}
                    </span>
                @endif
            </p>
        </header>

        @if($isOwn)
            <div class="profile-detail-actions">
                <a href="{{ route('cast.profile.edit') }}" class="detail-action-btn edit-btn">
                    <i class="fas fa-pen"></i>
                    <span>プロフィール編集</span>
                </a>
            </div>
            @if(!empty($mypageMenu))
                @include('casts.mypage.parts.menu', ['current' => $mypageMenu, 'fullWidth' => false])
            @endif
        @else
            <div class="profile-detail-actions">
                @if($showInteractionActions)
                    <button type="button" id="btn-profile-keep" class="detail-action-btn keep {{ ($cast['is_kept'] ?? false) ? 'active' : '' }}" aria-pressed="{{ ($cast['is_kept'] ?? false) ? 'true' : 'false' }}">
                        <i class="fas fa-bookmark"></i>
                        <span>KEEP</span>
                    </button>
                    <button type="button" id="btn-profile-like" class="detail-action-btn like" data-count="{{ $cast['like_cnt'] ?? 0 }}">
                        <i class="fas fa-heart"></i>
                        <span class="like-count-text">LIKE：<span class="num">{{ $cast['like_cnt'] ?? 0 }}</span>件</span>
                    </button>
                @endif
            </div>
            {{-- 共有はヘッダー横の share-menu に集約済み --}}
        @endif

        <section class="specs-section" aria-labelledby="specs-heading">
            <h2 id="specs-heading" class="section-heading">スペック</h2>
            @php
                $heightVal = $cast['height'] ?? null;
                $weightVal = $cast['weight'] ?? null;
                $bustVal = $cast['bust'] ?? $cast['b'] ?? null;
                $waistVal = $cast['waist'] ?? $cast['w'] ?? null;
                $hipVal = $cast['hip'] ?? $cast['h'] ?? null;
            @endphp
            <div class="metric-display">
                <div class="metric-display-row">
                    <div class="metric-display-item">
                        <span class="metric-display-label">Height</span>
                        <span class="metric-display-value {{ empty($heightVal) ? 'is-empty' : '' }}">
                            {{ $heightVal ?: '--' }}<small>cm</small>
                        </span>
                    </div>
                    <div class="metric-display-item">
                        <span class="metric-display-label">Weight</span>
                        <span class="metric-display-value {{ empty($weightVal) ? 'is-empty' : '' }}">
                            {{ $weightVal ?: '--' }}<small>kg</small>
                        </span>
                    </div>
                </div>
                <div class="bwh-display" aria-label="3サイズ">
                    <div class="bwh-display-pill {{ empty($bustVal) ? 'is-empty' : '' }}">
                        <b>B</b><span>{{ $bustVal ?: '--' }}</span>
                    </div>
                    <div class="bwh-display-pill {{ empty($waistVal) ? 'is-empty' : '' }}">
                        <b>W</b><span>{{ $waistVal ?: '--' }}</span>
                    </div>
                    <div class="bwh-display-pill {{ empty($hipVal) ? 'is-empty' : '' }}">
                        <b>H</b><span>{{ $hipVal ?: '--' }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="intro-section" aria-labelledby="intro-heading">
            <button type="button" class="accordion-trigger" id="intro-heading" aria-expanded="true" aria-controls="intro-body" onclick="toggleAccordion(this)">
                <span>自己PR</span>
                <i class="fas fa-chevron-down accordion-icon is-open" aria-hidden="true"></i>
            </button>
            <div class="accordion-body" id="intro-body" role="region">
                <div class="intro-text">
                    {!! nl2br(e($cast['intro'] ?? $cast['pr'] ?? '')) !!}
                </div>
            </div>
        </section>

        <section class="other-info-detail-section" aria-labelledby="other-info-heading">
            <h2 id="other-info-heading" class="section-heading">その他情報</h2>
            <div class="other-info-detail-body">
                @if(!empty($cast['birth_year']) && !empty($cast['birth_month']) && !empty($cast['birth_day']))
                    <div class="detail-row">
                        <span class="detail-label">生年月日</span>
                        <span class="detail-value">{{ $cast['birth_year'] }}年{{ $cast['birth_month'] }}月{{ $cast['birth_day'] }}日</span>
                    </div>
                @endif
                @if(!empty($cast['personality_type']))
                    <div class="detail-row">
                        <span class="detail-label">接客タイプ診断結果</span>
                        <span class="detail-value">{{ $cast['personality_type'] }}</span>
                    </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">希望職種</span>
                    <span class="detail-value">{{ $cast['industry_names'] ?? ($cast['desired_job'] ?? '--') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">ルックス</span>
                    <span class="detail-value">{{ $cast['my_field'] ?? '--' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">性格・内面</span>
                    <span class="detail-value">{{ $cast['my_inner_skills'] ?? '--' }}</span>
                </div>
                {{-- 自己PR は上のアコーディオンに集約済み --}}
                <div class="detail-row detail-row-block">
                    <span class="detail-label">現職業</span>
                    <div class="detail-value">@if(!empty($cast['profession'])){!! nl2br(e($cast['profession'])) !!}@elseif(!empty($cast['current_job'])){!! nl2br(e($cast['current_job'])) !!}@else--@endif</div>
                </div>
                <div class="detail-row">
                    <span class="detail-label">ナイトワーク経験</span>
                    <span class="detail-value">{{ $cast['night_work_label'] ?? '--' }}</span>
                </div>
            </div>
        </section>

        @if($isOwn)
            <section class="reviews-section" aria-labelledby="reviews-heading">
                <h2 id="reviews-heading" class="section-heading">Reviews</h2>
                <p class="reviews-empty">レビューはマイページの「レビュー一覧」で確認できます。</p>
                <a href="{{ route('cast.mypage.reviews') }}" class="detail-action-btn edit-btn" style="margin-top: 0.5rem;">
                    <i class="fas fa-star"></i>
                    <span>レビュー一覧を見る</span>
                </a>
            </section>
        @endif
        {{-- 店舗側の表示では Reviews セクションは表示しない --}}
    </div>
</div>

{{-- 画像フルスクリーン用ライトボックス（共通） --}}
<div id="lightbox-overlay" class="lightbox-overlay" onclick="closeLightbox(event)">
    <img id="lightbox-image" src="" alt="" class="lightbox-image">
    <button type="button" class="lightbox-close" aria-label="閉じる" onclick="closeLightbox(event)">
        <i class="fas fa-times"></i>
    </button>
</div>
