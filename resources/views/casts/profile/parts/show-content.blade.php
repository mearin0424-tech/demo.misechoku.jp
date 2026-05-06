{{-- キャストプロフィール本文（shop/castprofileview と cast/mypage で共通） --}}
@php $isOwn = $isOwn ?? false; @endphp
@php $showInteractionActions = $showInteractionActions ?? true; @endphp
<section class="profile-hero" aria-label="プロフィール写真">
    <div class="profile-hero-inner">
        <img id="profile-main-img" src="{{ $cast['img'] }}" alt="{{ $cast['nickname'] ?? $cast['name'] }}" class="profile-hero-img js-lightbox-target">
        <div class="profile-hero-gradient"></div>
        <div class="profile-hero-badge">
            @if($cast['is_applied'] ?? false)
                <span class="badge-approved">入金承認済</span>
            @endif
        </div>
    </div>
</section>

@if(!empty($cast['images']) && count($cast['images']) > 0)
    <div class="profile-photo-strip">
        @foreach($cast['images'] as $index => $imgUrl)
            <button type="button" class="profile-photo-thumb {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}" onclick="setProfileMainImage({{ $index }})" aria-label="写真{{ $index + 1 }}を表示">
                <img src="{{ $imgUrl }}" alt="" class="js-lightbox-target">
            </button>
        @endforeach
    </div>
@endif

<div class="profile-main-contents">
    <div class="profile-view-inner">
        <header class="cast-header">
            <h1 class="cast-name serif-font">{{ $cast['nickname'] ?? $cast['name'] }}<span class="cast-age">({{ $cast['age'] }})</span></h1>
            <p class="cast-location">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                <span>@if(!empty($cast['pref']) || !empty($cast['city'])){{ implode(' / ', array_filter([$cast['pref'] ?? null, $cast['city'] ?? null])) }} / @endifキャスト</span>
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
            @if(!empty($shareUrl))
                @include('common.share-actions', [
                    'shareUrl' => $shareUrl,
                    'shareTitle' => $shareTitle ?? (($cast['nickname'] ?? $cast['name']) . 'のプロフィール'),
                    'shareText' => $shareText ?? ($cast['intro'] ?? $cast['pr'] ?? ''),
                    'shareLabel' => 'このキャストプロフィールをSNSで共有'
                ])
            @endif
        @endif

        <section class="specs-section" aria-labelledby="specs-heading">
            <h2 id="specs-heading" class="section-heading">スペック</h2>
            <div class="specs-grid">
                <div class="spec-item">
                    <span class="spec-label">Height / Weight</span>
                    <span class="spec-value">{{ $cast['height'] ?? '--' }}cm / {{ $cast['weight'] ?? '--' }}kg</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">B / W / H</span>
                    <span class="spec-value">{{ $cast['bust'] ?? $cast['b'] ?? '--' }} / {{ $cast['waist'] ?? $cast['w'] ?? '--' }} / {{ $cast['hip'] ?? $cast['h'] ?? '--' }}</span>
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
                <div class="detail-row">
                    <span class="detail-label">シフト希望</span>
                    <span class="detail-value">{{ $cast['work_where'] ?? ($cast['shift_hope'] ?? '--') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">勤務時間帯</span>
                    <span class="detail-value">{{ $cast['work_time_label'] ?? '--' }}</span>
                </div>
                <div class="detail-row detail-row-block">
                    <span class="detail-label">自己PR</span>
                    <div class="detail-value">@if(!empty($cast['pr'])){!! nl2br(e($cast['pr'])) !!}@else--@endif</div>
                </div>
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

        <section class="reviews-section" aria-labelledby="reviews-heading">
            <h2 id="reviews-heading" class="section-heading">Reviews</h2>
            @if($isOwn)
                <p class="reviews-empty">レビューはマイページの「レビュー一覧」で確認できます。</p>
                <a href="{{ route('cast.mypage.reviews') }}" class="detail-action-btn edit-btn" style="margin-top: 0.5rem;">
                    <i class="fas fa-star"></i>
                    <span>レビュー一覧を見る</span>
                </a>
            @elseif(!empty($cast['reviews']) && count($cast['reviews']) > 0)
                <ul class="reviews-list">
                    @foreach($cast['reviews'] as $rev)
                        <li class="review-item">
                            <div class="review-stars" aria-label="{{ $rev['score'] }}点">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= ($rev['score'] ?? 0) ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </div>
                            <p class="review-text">{{ $rev['text'] ?? '' }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="reviews-empty">まだレビューはありません</p>
            @endif
        </section>
    </div>
</div>

{{-- 画像フルスクリーン用ライトボックス（共通） --}}
<div id="lightbox-overlay" class="lightbox-overlay" onclick="closeLightbox(event)">
    <img id="lightbox-image" src="" alt="" class="lightbox-image">
    <button type="button" class="lightbox-close" aria-label="閉じる" onclick="closeLightbox(event)">
        <i class="fas fa-times"></i>
    </button>
</div>
