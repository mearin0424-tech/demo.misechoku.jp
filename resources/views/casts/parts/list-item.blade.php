@php
    $recruitUrl = Route::has('cast.shopprofile.show') ? route('cast.shopprofile.show', $item['id']) : '#';
    $hitokoto = trim((string) ($item['hitokoto'] ?? ''));
    $hitokotoTime = (string) ($item['hitokoto_updated_at'] ?? '');
    $nearestStation = trim((string) ($item['nearest_station'] ?? ''));
    $locationLine = $nearestStation !== ''
        ? $nearestStation
        : trim((string) (($item['pref'] ?? '') . ' ' . ($item['city'] ?? '')));
    $locationIcon = $nearestStation !== '' ? 'fa-train' : 'fa-map-marker-alt';
@endphp
<li class="connection-item connection-item--clickable connection-item--shop-rich tl-row tl-row--shop tl-row--tall">
    <a href="{{ $recruitUrl }}" class="connection-item__link tl-row__link">
        {{-- 四角サムネイル：行の高さいっぱいまで拡大 --}}
        <div class="tl-row__thumb-wrap tl-row__thumb-wrap--square">
            <img loading="lazy" decoding="async" src="{{ $item['main_img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="tl-row__thumb">
        </div>

        <div class="tl-row__body">
            {{-- 1行目：名前 + 優良店バッヂ --}}
            <h3 class="tl-row__name">{{ $item['shop_name'] }}@if(!empty($item['is_excellent'])) <x-ui.premium-badge size="sm" />@endif</h3>

            {{-- 2行目：業種・位置・評価・距離 --}}
            <div class="tl-row__meta">
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

            {{-- 3行目：ボーナス金 + 時給（給与情報チップ） --}}
            @php
                $hourly = (int) ($item['hourly_wage'] ?? 0);
                $reward = (int) ($item['reward'] ?? 0);
            @endphp
            @if($hourly > 0 || $reward > 0)
                <div class="tl-row__pay">
                    @if($reward > 0)
                        <span class="tl-pay-chip tl-pay-chip--bonus">
                            <i class="fas fa-gift" aria-hidden="true"></i>
                            <span class="tl-pay-chip__label">ボーナス</span>
                            <strong>¥{{ number_format($reward) }}</strong>
                        </span>
                    @endif
                    @if($hourly > 0)
                        <span class="tl-pay-chip tl-pay-chip--wage">
                            <i class="fas fa-yen-sign" aria-hidden="true"></i>
                            <span class="tl-pay-chip__label">時給</span>
                            <strong>¥{{ number_format($hourly) }}〜</strong>
                        </span>
                    @endif
                </div>
            @endif

            {{-- 4行目以降：ひとこと --}}
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
