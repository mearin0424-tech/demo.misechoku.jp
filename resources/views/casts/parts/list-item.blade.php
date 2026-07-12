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
<li class="connection-item connection-item--clickable connection-item--shop-rich tl-row tl-row--shop tl-row--has-actions">
    <a href="{{ $recruitUrl }}" class="connection-item__link tl-row__link">
        {{-- 丸型アイコン --}}
        <div class="tl-row__thumb-wrap">
            <img loading="lazy" decoding="async" src="{{ $item['main_img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="tl-row__thumb">
            @if(!empty($item['is_excellent']))
                <span class="tl-row__crown" role="img" aria-label="優良店" title="優良店">
                    <i class="fas fa-crown" aria-hidden="true"></i>
                </span>
            @endif
        </div>

        <div class="tl-row__body">
            {{-- 1行目：アイコンの隣に来る「名前」のみで強くフォーカス --}}
            <h3 class="tl-row__name">{{ $item['shop_name'] }}</h3>

            {{-- 2行目：業種・位置・評価・距離（メタ情報を分離） --}}
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

            {{-- 3行目以降：ひとこと --}}
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
    @php
        $isLiked = (bool) ($item['is_liked'] ?? false);
        $likeCnt = $item['like_count'] ?? null;
    @endphp
    {{-- 一覧の LIKE は状態表示のみ（操作はスワイプ / プロフィール詳細に集約） --}}
    <div class="tl-row__actions">
        <span class="tl-row__like-indicator {{ $isLiked ? 'is-on' : '' }}"
              aria-label="いいね{{ $isLiked ? '済み' : '' }}{{ $likeCnt !== null ? '・' . number_format((int) $likeCnt) . '件' : '' }}">
            <i class="fas fa-heart" aria-hidden="true"></i>
            @if($likeCnt !== null)<span class="tl-row__like-count">{{ number_format((int) $likeCnt) }}</span>@endif
        </span>
    </div>
</li>
