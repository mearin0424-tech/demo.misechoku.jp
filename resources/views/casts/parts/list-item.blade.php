@php
    $recruitUrl = Route::has('cast.recruit.show') ? route('cast.recruit.show', $item['id']) : '#';
    $hitokoto = trim((string) ($item['hitokoto'] ?? ''));
    $hitokotoTime = (string) ($item['hitokoto_updated_at'] ?? '');
    $locationLine = trim((string) (($item['pref'] ?? '') . ' ' . ($item['city'] ?? '')));
@endphp
<li class="connection-item connection-item--clickable connection-item--shop-rich">
    <a href="{{ $recruitUrl }}" class="connection-item__link shop-search-card__link">
        <div class="shop-search-card__top">
            <div class="shop-search-card__aside">
                <div class="shop-search-card__thumb-ring">
                    <img src="{{ $item['main_img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="shop-search-card__thumb">
                </div>
                @if(!empty($item['is_excellent']))
                    <div class="shop-search-card__badge" role="img" aria-label="優良店">
                        <i class="fas fa-crown shop-search-card__badge-icon" aria-hidden="true"></i>
                        <span>優良店</span>
                    </div>
                @endif
            </div>
            <div class="shop-search-card__body">
                <h3 class="shop-search-card__name">{{ $item['shop_name'] }}</h3>
                <div class="shop-search-card__meta-row">
                    @if(!empty($item['industry_label']))
                        <span class="shop-search-card__industry">{{ $item['industry_label'] }}</span>
                    @endif
                    @if(!empty($item['rating_display']))
                        <span class="shop-search-card__rating">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            {{ $item['rating_display'] }}
                        </span>
                    @endif
                    @if($locationLine !== '')
                        <span class="shop-search-card__location">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            {{ $locationLine }}
                        </span>
                    @endif
                    @if(!empty($item['distance_label']))
                        <span class="distance-badge distance-badge--inline">
                            <i class="fas fa-route"></i> {{ $item['distance_label'] }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @if($hitokoto !== '')
            <p class="shop-search-card__message">{!! nl2br(e($hitokoto)) !!}</p>
        @endif
        @if($hitokotoTime !== '')
            <div class="shop-search-card__footer">
                <span class="shop-search-card__time">
                    <i class="far fa-clock" aria-hidden="true"></i>
                    {{ $hitokotoTime }}
                </span>
            </div>
        @endif
    </a>
</li>
