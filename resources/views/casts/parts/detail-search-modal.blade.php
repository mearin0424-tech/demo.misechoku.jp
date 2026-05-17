@php
    $options = $detailSearchOptions ?? [];
    $industries = $options['industries'] ?? collect();
    $areas = $options['areas'] ?? collect();
    $hourlyWages = $options['hourly_wages'] ?? collect();
    $rewards = $options['rewards'] ?? collect();
    $workStyleTags = $options['work_style'] ?? collect();
    $welcomeTags = $options['welcome'] ?? collect();
    $benefitTags = $options['benefit'] ?? collect();
    $facilityTags = $options['facility'] ?? collect();
    $atmosphereTags = $options['atmosphere'] ?? collect();

    // 保存済み検索条件（cast_search_preferences）。フォーム未送信時は保存値をデフォルトに使う。
    $savedPrefs = $savedPreferences ?? [];
    $savedIndustryIds = array_map('intval', $savedPrefs['industry_ids'] ?? []);
    $savedShiftFrequency = (string) ($savedPrefs['shift_frequency'] ?? '');
    $savedWorkPeriods = array_values(array_filter((array) ($savedPrefs['work_periods'] ?? []), 'is_string'));
    $savedHourlyWageMin = $savedPrefs['hourly_wage_min'] ?? null;

    // 業種は industry_ids[] と industry[] (name) の二系統。
    // - industry_ids[]: 保存用 / 既存検索パラメータ（POST 後はこちらが優先）
    // - industry[]   : 既存 GET クエリパラメータ（互換維持、業種名で検索）
    $reqIndustryIds = array_map('intval', (array) request('industry_ids', []));
    $selectedIndustryIds = $reqIndustryIds !== [] ? $reqIndustryIds : $savedIndustryIds;
    $selectedIndustries = array_values((array) request('industry', []));
    $selectedAreas = array_values((array) request('area', []));
    $selectedWorkStyleTags = array_map('intval', (array) request('work_style_tag_ids', []));
    $selectedWelcomeTags = array_map('intval', (array) request('welcome_tag_ids', []));
    $selectedBenefitTags = array_map('intval', (array) request('benefit_tag_ids', []));
    $selectedFacilityTags = array_map('intval', (array) request('facility_tag_ids', []));
    $selectedAtmosphereTags = array_map('intval', (array) request('atmosphere_tag_ids', []));

    $reqHourlyWage = (string) request('hourly_wage', '');
    $selectedHourlyWage = $reqHourlyWage !== ''
        ? $reqHourlyWage
        : ($savedHourlyWageMin !== null ? (string) $savedHourlyWageMin : '');
    $selectedReward = (string) request('reward', '');

    $reqShiftFrequency = (string) request('shift_frequency', '');
    $selectedShiftFrequency = $reqShiftFrequency !== '' ? $reqShiftFrequency : $savedShiftFrequency;

    $reqWorkPeriods = array_values(array_filter((array) request('work_periods', []), 'is_string'));
    $selectedWorkPeriods = $reqWorkPeriods !== [] ? $reqWorkPeriods : $savedWorkPeriods;

    $workPeriodLabels = ['morning' => '朝', 'day' => '昼', 'night' => '夜'];
@endphp

<div id="detail-search-modal" class="detail-search-modal" aria-hidden="true">
    <div class="detail-search-modal__overlay" aria-hidden="true"></div>
    <div class="detail-search-modal__window detail-search-modal__window--search">
        <div class="detail-search-modal__header detail-search-modal__header--search">
            <div class="detail-search-modal__header-line" aria-hidden="true"></div>
            <h2 class="detail-search-modal__title">詳細検索</h2>
            <button type="button" class="detail-search-modal__close" data-close-modal aria-label="詳細検索を閉じる">&times;</button>
        </div>

        <div class="detail-search-modal__body detail-search-modal__body--search">
            <form id="detail-search-form" class="detail-search-form detail-search-form--search">
                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="業種" data-open="true">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="true">
                        <span>希望業種</span>
                        <span class="detail-search-accordion__icon">−</span>
                    </button>
                    <div class="detail-search-accordion__body">
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($industries as $industry)
                                @php
                                    $iid = (int) $industry->id;
                                    $checked = in_array($iid, $selectedIndustryIds, true)
                                        || in_array($industry->name, $selectedIndustries, true);
                                @endphp
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="industry_ids[]" value="{{ $iid }}" data-industry-name="{{ $industry->name }}" {{ $checked ? 'checked' : '' }}>
                                    <span>{{ $industry->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="出勤頻度・時間帯">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>希望の出勤頻度・時間帯</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-subsection">
                            <span class="detail-search-subsection__label">出勤頻度</span>
                            <div class="detail-search-chips detail-search-chips--search">
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="radio" name="shift_frequency" value="" {{ $selectedShiftFrequency === '' ? 'checked' : '' }}>
                                    <span>指定なし</span>
                                </label>
                                @foreach(['週1回出勤', '週2回出勤', '週3回以上'] as $freq)
                                    <label class="detail-search-chip detail-search-chip--search">
                                        <input type="radio" name="shift_frequency" value="{{ $freq }}" {{ $selectedShiftFrequency === $freq ? 'checked' : '' }}>
                                        <span>{{ $freq }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="detail-search-subsection">
                            <span class="detail-search-subsection__label">時間帯（複数選択可）</span>
                            <div class="detail-search-chips detail-search-chips--search">
                                @foreach($workPeriodLabels as $key => $label)
                                    <label class="detail-search-chip detail-search-chip--search">
                                        <input type="checkbox" name="work_periods[]" value="{{ $key }}" {{ in_array($key, $selectedWorkPeriods, true) ? 'checked' : '' }}>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
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

                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="働き方・給与">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>働き方・給与</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($workStyleTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="work_style_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedWorkStyleTags, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="歓迎条件">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>歓迎条件</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($welcomeTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="welcome_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedWelcomeTags, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="待遇・サポート">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>待遇・サポート</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($benefitTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="benefit_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedBenefitTags, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="店舗の雰囲気・設備" data-open="true">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="true">
                        <span>店舗の雰囲気・設備</span>
                        <span class="detail-search-accordion__icon">−</span>
                    </button>
                    <div class="detail-search-accordion__body">
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($atmosphereTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="atmosphere_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedAtmosphereTags, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                            @foreach($facilityTags as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="facility_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedFacilityTags, true) ? 'checked' : '' }}>
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
            <button type="button" class="detail-search-modal__btn detail-search-modal__btn--save" data-detail-search-save
                    data-save-url="{{ route('cast.search-preferences.save') }}">
                条件を保存
            </button>
            <button type="button" class="detail-search-modal__btn detail-search-modal__btn--submit" data-detail-search-submit>この条件で検索</button>
        </div>
        <p class="detail-search-modal__save-feedback" data-detail-search-save-feedback hidden></p>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('detail-search-form');
    if (!form) return;
    var saveBtn = form.closest('.detail-search-modal__window')?.querySelector('[data-detail-search-save]');
    var feedback = form.closest('.detail-search-modal__window')?.querySelector('[data-detail-search-save-feedback]');
    if (!saveBtn) return;

    function readCheckedValues(name) {
        return Array.from(form.querySelectorAll('input[name="' + name + '"]:checked'))
            .map(function (el) { return el.value; })
            .filter(function (v) { return v !== ''; });
    }
    function readRadioValue(name) {
        var el = form.querySelector('input[name="' + name + '"]:checked');
        return el ? el.value : '';
    }

    saveBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (saveBtn.dataset.busy === '1') return;
        saveBtn.dataset.busy = '1';
        if (feedback) { feedback.hidden = true; feedback.className = 'detail-search-modal__save-feedback'; }

        var payload = new FormData();
        payload.append('_token', (document.querySelector('meta[name="csrf-token"]') || {}).content || '');
        readCheckedValues('industry_ids[]').forEach(function (v) { payload.append('industry_ids[]', v); });
        readCheckedValues('work_periods[]').forEach(function (v) { payload.append('work_periods[]', v); });
        var freq = readRadioValue('shift_frequency');
        if (freq !== '') payload.append('shift_frequency', freq);
        var wage = (form.querySelector('[name="hourly_wage"]') || {}).value || '';
        if (wage !== '') payload.append('hourly_wage_min', wage);

        fetch(saveBtn.dataset.saveUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: payload,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
        .then(function () {
            if (feedback) {
                feedback.hidden = false;
                feedback.className = 'detail-search-modal__save-feedback is-success';
                feedback.textContent = '検索条件を保存しました。次回もこの条件で開きます。';
            }
        })
        .catch(function () {
            if (feedback) {
                feedback.hidden = false;
                feedback.className = 'detail-search-modal__save-feedback is-error';
                feedback.textContent = '保存に失敗しました。時間をおいて再度お試しください。';
            }
        })
        .finally(function () { saveBtn.dataset.busy = '0'; });
    });
})();
</script>
<style>
.detail-search-subsection { margin-bottom: 12px; }
.detail-search-subsection__label {
    display: block;
    font-size: 0.74rem;
    font-weight: 700;
    color: var(--gold);
    letter-spacing: 0.04em;
    margin: 0 0 6px;
}
.detail-search-modal__footer--search {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.detail-search-modal__btn--save {
    flex: 1 1 auto;
    background: rgba(197, 160, 89, 0.10);
    border: 1px solid var(--color-border-strong);
    color: var(--gold-light);
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.84rem;
    cursor: pointer;
    transition: background 0.15s ease;
}
.detail-search-modal__btn--save:hover { background: rgba(197, 160, 89, 0.18); }
.detail-search-modal__save-feedback {
    padding: 8px 16px 0;
    margin: 0;
    font-size: 0.76rem;
    text-align: center;
}
.detail-search-modal__save-feedback.is-success { color: var(--color-success); }
.detail-search-modal__save-feedback.is-error { color: var(--color-danger); }
</style>
@endpush
