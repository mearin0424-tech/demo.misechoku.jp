@php
    $profileUrl = Route::has('shop.castprofileview.show') ? route('shop.castprofileview.show', $item['id']) : '#';
    $hitokoto = trim((string) ($item['hitokoto'] ?? ''));
    $hitokotoTime = (string) ($item['hitokoto_updated_at'] ?? '');
    $area = trim(implode(' ', array_filter([(string) ($item['pref'] ?? ''), (string) ($item['city'] ?? '')])));
@endphp
<li class="connection-item connection-item--clickable connection-item--search connection-item--shop-cast-list">
    <a href="{{ $profileUrl }}" class="connection-item__link shop-cast-search__link">
        <img src="{{ $item['img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="shop-cast-search__thumb">
        <div class="shop-cast-search__main">
            <div class="shop-cast-search__line1">
                <span class="shop-cast-search__name">{{ $item['name'] ?? '' }}</span>
                @if(!empty($item['age']))
                    <span class="shop-cast-search__age">({{ $item['age'] }})</span>
                @endif
                @if($area !== '')
                    <span class="shop-cast-search__sep" aria-hidden="true">・</span>
                    <span class="shop-cast-search__area">{{ $area }}</span>
                @endif
                @if(!empty($item['distance_label']))
                    <span class="distance-badge distance-badge--inline" style="margin-left:6px;">
                        <i class="fas fa-route"></i> {{ $item['distance_label'] }}
                    </span>
                @endif
            </div>
            @if($hitokoto !== '' || $hitokotoTime !== '')
                <div class="shop-cast-search__line2 @if($hitokoto === '') shop-cast-search__line2--time-only @endif">
                    @if($hitokoto !== '')
                        <p class="shop-cast-search__msg">{{ e(preg_replace('/\s+/u', ' ', $hitokoto)) }}</p>
                    @endif
                    @if($hitokotoTime !== '')
                        <span class="shop-cast-search__when">
                            <i class="far fa-clock" aria-hidden="true"></i>
                            {{ $hitokotoTime }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
        <span class="connection-item__arrow shop-cast-search__chev" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
    </a>
</li>
