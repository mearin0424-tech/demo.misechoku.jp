@php
    $profileUrl = Route::has('shop.castprofileview.show') ? route('shop.castprofileview.show', $item['id']) : '#';
    $hitokoto = trim((string) ($item['hitokoto'] ?? ''));
    $hitokotoTime = (string) ($item['hitokoto_updated_at'] ?? '');
    $area = trim(implode(' ', array_filter([(string) ($item['pref'] ?? ''), (string) ($item['city'] ?? '')])));
@endphp
<li class="connection-item connection-item--clickable connection-item--search connection-item--shop-cast-list tl-row tl-row--cast">
    <a href="{{ $profileUrl }}" class="connection-item__link tl-row__link">
        <div class="tl-row__thumb-wrap tl-row__thumb-wrap--round">
            <img src="{{ $item['img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="tl-row__thumb tl-row__thumb--round">
        </div>
        <div class="tl-row__body">
            {{-- 1行目：名前 + 年齢 + 位置 + （あれば）距離 --}}
            <div class="tl-row__line1">
                <h3 class="tl-row__name">{{ $item['name'] ?? '' }}</h3>
                @if(!empty($item['age']))
                    <span class="tl-row__age">{{ $item['age'] }}歳</span>
                @endif
                @if($area !== '')
                    <span class="tl-row__loc">
                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>{{ $area }}
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
