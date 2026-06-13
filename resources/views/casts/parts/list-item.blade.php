@php
    $recruitUrl = Route::has('cast.recruit.show') ? route('cast.recruit.show', $item['id']) : '#';
    $hitokoto = trim((string) ($item['hitokoto'] ?? ''));
    $hitokotoTime = (string) ($item['hitokoto_updated_at'] ?? '');
    $nearestStation = trim((string) ($item['nearest_station'] ?? ''));
    $locationLine = $nearestStation !== ''
        ? $nearestStation
        : trim((string) (($item['pref'] ?? '') . ' ' . ($item['city'] ?? '')));
    $locationIcon = $nearestStation !== '' ? 'fa-train' : 'fa-map-marker-alt';
@endphp
<li class="connection-item connection-item--clickable connection-item--shop-rich tl-row tl-row--shop">
    <a href="{{ $recruitUrl }}" class="connection-item__link tl-row__link">
        <div class="tl-row__thumb-wrap">
            <img src="{{ $item['main_img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="tl-row__thumb">
            @if(!empty($item['is_excellent']))
                <span class="tl-row__crown" role="img" aria-label="優良店" title="優良店">
                    <i class="fas fa-crown" aria-hidden="true"></i>
                </span>
            @endif
        </div>
        <div class="tl-row__body">
            {{-- 1行目：名前 + 業種 + 位置 + （あれば）評価／距離 --}}
            <div class="tl-row__line1">
                <h3 class="tl-row__name">{{ $item['shop_name'] }}</h3>
                @if(!empty($item['industry_label']))
                    <span class="tl-row__industry">{{ $item['industry_label'] }}</span>
                @endif
                @if($locationLine !== '')
                    <span class="tl-row__loc">
                        <i class="fas {{ $locationIcon }}" aria-hidden="true"></i>{{ $locationLine }}
                    </span>
                @endif
                @if(!empty($item['rating_display']))
                    <span class="tl-row__rating">
                        <i class="fas fa-star" aria-hidden="true"></i>{{ $item['rating_display'] }}
                    </span>
                @endif
                @if(!empty($item['distance_label']))
                    <span class="tl-row__dist">
                        <i class="fas fa-route"></i>{{ $item['distance_label'] }}
                    </span>
                @endif
            </div>

            {{-- 2行目以降：ひとこと --}}
            @if($hitokoto !== '')
                <p class="tl-row__msg">{{ preg_replace('/\s+/u', ' ', $hitokoto) }}</p>
            @endif

            {{-- 最下部：控えめな最終更新（あればマッチ度も） --}}
            @if($hitokotoTime !== '' || !empty($item['match_summary']))
                <div class="tl-row__foot">
                    @if(!empty($item['match_summary']) && ($sort ?? '') === 'relevance')
                        <span class="tl-row__match" title="{{ implode(' / ', (array) ($item['match_reasons'] ?? [])) }}">
                            <i class="fas fa-bullseye" aria-hidden="true"></i>{{ $item['match_summary'] }}
                        </span>
                    @endif
                    @if($hitokotoTime !== '')
                        <time class="tl-row__time">{{ $hitokotoTime }}</time>
                    @endif
                </div>
            @endif
        </div>
    </a>
</li>
