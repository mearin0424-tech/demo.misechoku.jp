{{-- 店舗用：キャスト詳細検索モーダル --}}
<div id="detail-search-modal" class="detail-search-modal" aria-hidden="true">
    <div class="detail-search-modal__overlay" data-close-modal></div>
    <div class="detail-search-modal__window">
        <div class="detail-search-modal__header">
            <h2 class="detail-search-modal__title">詳細検索</h2>
            <button type="button" class="detail-search-modal__close" data-close-modal aria-label="閉じる">&times;</button>
        </div>
        <div class="detail-search-modal__body">
            <form id="detail-search-form" class="detail-search-form">
                {{-- 経験業種 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>経験業種</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['キャバクラ', 'ラウンジ', 'バー', 'スナック', 'その他'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="industry[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- エリア（賃貸検索風） --}}
                <div class="detail-search-row">
                    <label class="detail-search-label">エリア</label>
                    <button type="button" class="detail-search-select-btn" data-area-trigger>
                        <span class="detail-search-select-btn__text">選択する</span>
                        <span class="detail-search-select-btn__arrow">&gt;</span>
                    </button>
                </div>
                <div class="detail-search-row detail-search-row--location">
                    <span class="detail-search-label">現在地・位置情報から探す</span>
                    <div class="detail-search-location-segment" role="group" aria-label="検索方法">
                        <label class="detail-search-location-option {{ request('location_type', 'current') === 'current' ? 'is-selected' : '' }}">
                            <input type="radio" name="location_type" value="current" {{ request('location_type', 'current') === 'current' ? 'checked' : '' }} class="sr-only">
                            <span class="detail-search-location-option__icon" aria-hidden="true"><i class="fas fa-check"></i></span>
                            <span class="detail-search-location-option__text">現在地から探す</span>
                        </label>
                        <label class="detail-search-location-option {{ request('location_type') === 'geo' ? 'is-selected' : '' }}">
                            <input type="radio" name="location_type" value="geo" {{ request('location_type') === 'geo' ? 'checked' : '' }} class="sr-only">
                            <span class="detail-search-location-option__icon" aria-hidden="true"><i class="fas fa-check"></i></span>
                            <span class="detail-search-location-option__text">位置情報から探す</span>
                        </label>
                    </div>
                </div>
                <div class="detail-search-row detail-search-row--distance">
                    <div class="detail-search-distance">
                        <div class="detail-search-distance__marks">
                            <span>5km以内</span>
                            <span>20km</span>
                            <span>30km</span>
                            <span>40km以上</span>
                        </div>
                        <input type="range" id="search-distance-km" name="distance_km" class="detail-search-distance-slider" min="5" max="40" step="5" value="{{ request('distance_km', 20) }}" aria-label="距離">
                        <output for="search-distance-km" class="detail-search-distance__value" id="search-distance-value">{{ request('distance_km', 20) }}km</output>
                    </div>
                </div>

                {{-- 年齢 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>年齢</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['18〜22歳', '23〜27歳', '28〜32歳', '33歳〜'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="age_range[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 経験 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>経験</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['未経験', '経験者', 'ベテラン'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="experience[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 身長 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>身長</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['150cm未満', '150〜160cm', '160〜170cm', '170cm〜'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="height[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 特徴 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>特徴</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['即戦力', 'Wワーク可', '週1からOK', '送りあり希望'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="feature[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 希望勤務 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>希望勤務</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['平日', '土日', '夜間', '短期・単発'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="work_preference[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="detail-search-modal__footer">
            <button type="button" class="detail-search-modal__btn detail-search-modal__btn--reset" data-detail-search-reset>条件をクリア</button>
            <button type="button" class="detail-search-modal__btn detail-search-modal__btn--submit" data-detail-search-submit>この条件で検索</button>
        </div>
    </div>
</div>
