@php
    $options = $detailSearchOptions ?? [];
    $industries = $options['industries'] ?? collect();
    $areas = $options['areas'] ?? collect();
    $hourlyWages = $options['hourly_wages'] ?? collect();
    $rewards = $options['rewards'] ?? collect();
    $salaryTags = $options['salary'] ?? collect();
    $howtoTags = $options['howto'] ?? collect();
    $meritTags = $options['merit'] ?? collect();
    $featureTags = $options['feature'] ?? collect();
    $facilityTags = $options['facility'] ?? collect();
    $atmosphereTags = $options['atmosphere'] ?? collect();

    $selectedIndustries = array_values((array) request('industry', []));
    $selectedAreas = array_values((array) request('area', []));
    $selectedSalaryTags = array_map('intval', (array) request('salary_tag_ids', []));
    $selectedHowtoTags = array_map('intval', (array) request('howto_tag_ids', []));
    $selectedMeritTags = array_map('intval', (array) request('merit_tag_ids', []));
    $selectedFeatureTags = array_map('intval', (array) request('feature_tag_ids', []));
    $selectedFacilityTags = array_map('intval', (array) request('facility_tag_ids', []));
    $selectedAtmosphereTags = array_map('intval', (array) request('atmosphere_tag_ids', []));
    $selectedHourlyWage = (string) request('hourly_wage', '');
    $selectedReward = (string) request('reward', '');
@endphp

<div id="detail-search-modal" class="detail-search-modal" aria-hidden="true">
    <div class="detail-search-modal__overlay" data-close-modal></div>
    <div class="detail-search-modal__window detail-search-modal__window--search">
        <div class="detail-search-modal__header detail-search-modal__header--search">
            <div class="detail-search-modal__header-line" aria-hidden="true"></div>
            <h2 class="detail-search-modal__title">詳細検索</h2>
            <button type="button" class="detail-search-modal__close" data-close-modal aria-label="閉じる">&times;</button>
        </div>

        <div class="detail-search-modal__body detail-search-modal__body--search">
            <form id="detail-search-form" class="detail-search-form detail-search-form--search">
                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="業種" data-open="true">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="true">
                        <span>業種</span>
                        <span class="detail-search-accordion__icon">−</span>
                    </button>
                    <div class="detail-search-accordion__body">
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($industries as $industry)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="industry[]" value="{{ $industry->name }}" {{ in_array($industry->name, $selectedIndustries, true) ? 'checked' : '' }}>
                                    <span>{{ $industry->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="detail-search-section detail-search-section--panel detail-search-section--area" data-summary-group="エリア">
                    <div class="detail-search-section__head">
                        <span class="detail-search-section__title">エリア</span>
                        <span class="detail-search-selection-badge" data-selection-count data-empty-label="" {{ count($selectedAreas) > 0 ? '' : 'hidden' }}>
                            {{ count($selectedAreas) }}件選択中
                        </span>
                    </div>
                    <div class="detail-search-chips detail-search-chips--search">
                        @foreach($areas as $area)
                            <label class="detail-search-chip detail-search-chip--search">
                                <input type="checkbox" name="area[]" value="{{ $area->name }}" {{ in_array($area->name, $selectedAreas, true) ? 'checked' : '' }}>
                                <span>{{ $area->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="detail-search-section detail-search-section--panel">
                    <div class="detail-search-section__head">
                        <span class="detail-search-section__title">現在地・位置情報から探す</span>
                    </div>

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

                    <div class="detail-search-distance detail-search-distance--search">
                        <div class="detail-search-distance__marks">
                            <span>5km以内</span>
                            <span>20km</span>
                            <span>30km</span>
                            <span>40km以上</span>
                        </div>
                        <input type="range" id="search-distance-km" name="distance_km" class="detail-search-distance-slider" min="5" max="40" step="5" value="{{ request('distance_km', 20) }}" aria-label="距離">
                        <output for="search-distance-km" class="detail-search-distance__value" id="search-distance-value">{{ request('distance_km', 20) == 40 ? '40km以上' : request('distance_km', 20) . 'km' }}</output>
                    </div>
                </div>

                <div class="detail-search-section detail-search-section--panel" data-summary-group="給与(時給)">
                    <label class="detail-search-label detail-search-label--panel" for="detail-search-hourly-wage">給与(時給)</label>
                    <div class="detail-search-select-wrap">
                        <select id="detail-search-hourly-wage" name="hourly_wage" class="detail-search-select">
                            <option value="">選択する</option>
                            @foreach($hourlyWages as $value)
                                <option value="{{ $value }}" {{ $selectedHourlyWage === (string) $value ? 'selected' : '' }}>{{ number_format((int) $value) }}円以上</option>
                            @endforeach
                        </select>
                        <span class="detail-search-select-wrap__icon" aria-hidden="true"><i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>

                <div class="detail-search-section detail-search-section--panel" data-summary-group="採用報酬">
                    <label class="detail-search-label detail-search-label--panel" for="detail-search-reward">採用報酬</label>
                    <div class="detail-search-select-wrap">
                        <select id="detail-search-reward" name="reward" class="detail-search-select">
                            <option value="">選択する</option>
                            @foreach($rewards as $value)
                                <option value="{{ $value }}" {{ $selectedReward === (string) $value ? 'selected' : '' }}>{{ number_format((int) $value) }}円以上</option>
                            @endforeach
                        </select>
                        <span class="detail-search-select-wrap__icon" aria-hidden="true"><i class="fas fa-chevron-down"></i></span>
                    </div>
                </div>

                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="給与・待遇">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>給与・待遇</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($salaryTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="salary_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedSalaryTags, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="働き方">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>働き方</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($howtoTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="howto_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedHowtoTags, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="メリット">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>メリット</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($meritTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="merit_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedMeritTags, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="こだわり条件" data-open="true">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="true">
                        <span>こだわり条件</span>
                        <span class="detail-search-accordion__icon">−</span>
                    </button>
                    <div class="detail-search-accordion__body">
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($featureTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="feature_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedFeatureTags, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                            @foreach($facilityTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="facility_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedFacilityTags, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                            @foreach($atmosphereTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="atmosphere_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedAtmosphereTags, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="detail-search-modal__footer detail-search-modal__footer--search">
            <button type="button" class="detail-search-modal__btn detail-search-modal__btn--reset" data-detail-search-reset>条件をクリア</button>
            <button type="button" class="detail-search-modal__btn detail-search-modal__btn--submit" data-detail-search-submit>この条件で検索</button>
        </div>
    </div>
</div>
