@php
    $profileUrl = Route::has('shop.castprofileview.show') ? route('shop.castprofileview.show', $item['id']) : '#';
    $hitokoto = trim((string) ($item['hitokoto'] ?? ''));
    $hitokotoTime = (string) ($item['hitokoto_updated_at'] ?? '');
    $area = trim(implode(' ', array_filter([(string) ($item['pref'] ?? ''), (string) ($item['city'] ?? '')])));
@endphp
<li class="connection-item connection-item--clickable connection-item--search connection-item--shop-cast-list tl-row tl-row--cast tl-row--has-actions">
    <a href="{{ $profileUrl }}" class="connection-item__link tl-row__link">
        {{-- 丸型アイコン --}}
        <div class="tl-row__thumb-wrap">
            <img loading="lazy" decoding="async" src="{{ $item['img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="tl-row__thumb">
        </div>

        <div class="tl-row__body">
            {{-- 1行目：アイコンの隣に来る「名前」のみで強くフォーカス --}}
            <h3 class="tl-row__name">{{ $item['name'] ?? '' }}</h3>

            {{-- 2行目：年齢・位置・距離（メタ情報を分離） --}}
            <div class="tl-row__meta">
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
        $isKeeping = (bool) ($item['is_keeping'] ?? false);
        $isLiked = (bool) ($item['is_liked'] ?? false);
        $likeCount = (int) ($item['like_count'] ?? 0);
        $keepCount = (int) ($item['keep_count'] ?? 0);
    @endphp
    <div class="tl-row__actions" aria-label="クイックアクション">
        <button type="button" class="tl-row__action-btn tl-row__action-btn--keep"
                data-fav-toggle data-action="keep" data-item-type="cast" data-item-id="{{ $item['id'] }}"
                aria-label="キープ" aria-pressed="{{ $isKeeping ? 'true' : 'false' }}">
            <i class="fas fa-bookmark" aria-hidden="true"></i>
            @if($keepCount > 0)<span class="tl-row__action-count">{{ $keepCount > 99 ? '99+' : $keepCount }}</span>@endif
        </button>
        <button type="button" class="tl-row__action-btn tl-row__action-btn--like"
                data-fav-toggle data-action="like" data-item-type="cast" data-item-id="{{ $item['id'] }}"
                aria-label="いいね" aria-pressed="{{ $isLiked ? 'true' : 'false' }}">
            <i class="fas fa-heart" aria-hidden="true"></i>
            @if($likeCount > 0)<span class="tl-row__action-count">{{ $likeCount > 99 ? '99+' : $likeCount }}</span>@endif
        </button>
    </div>
</li>
