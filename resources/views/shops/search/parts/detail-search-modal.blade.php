{{-- 店舗用：キャスト詳細検索モーダル --}}
@php
    $savedPrefs = $savedPreferences ?? [];
    $tagsByCat = $castTagsByCategory ?? ['looks' => [], 'personality' => []];

    $maxDistanceKm = (int) (request('distance_km', $savedPrefs['max_distance_km'] ?? 20));
    $ageMin = (string) (request('age_min', $savedPrefs['age_min'] ?? ''));
    $ageMax = (string) (request('age_max', $savedPrefs['age_max'] ?? ''));

    $reqShiftFrequency = (string) request('shift_frequency', '');
    $shiftFrequency = $reqShiftFrequency !== '' ? $reqShiftFrequency : (string) ($savedPrefs['shift_frequency'] ?? '');

    $reqWorkPeriods = array_values(array_filter((array) request('work_periods', []), 'is_string'));
    $workPeriods = $reqWorkPeriods !== [] ? $reqWorkPeriods : array_values(array_filter((array) ($savedPrefs['work_periods'] ?? []), 'is_string'));

    $reqLooks = array_map('intval', (array) request('looks_tag_ids', []));
    $selectedLooks = $reqLooks !== [] ? $reqLooks : array_map('intval', $savedPrefs['looks_tag_ids'] ?? []);

    $reqPers = array_map('intval', (array) request('personality_tag_ids', []));
    $selectedPersonality = $reqPers !== [] ? $reqPers : array_map('intval', $savedPrefs['personality_tag_ids'] ?? []);

    $reqExp = (string) request('night_work_exp', '');
    $nightWorkExp = $reqExp !== '' ? $reqExp : (string) ($savedPrefs['night_work_exp'] ?? '');

    $workPeriodLabels = ['morning' => '朝', 'day' => '昼', 'night' => '夜'];
    $nightWorkExpLabels = ['any' => '指定なし', 'yes' => '経験あり', 'none' => '未経験'];
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
                {{-- 距離 --}}
                <div class="detail-search-section detail-search-section--panel">
                    <div class="detail-search-section__head">
                        <span class="detail-search-section__title">店舗からの距離</span>
                    </div>
                    <div class="detail-search-distance detail-search-distance--search">
                        <div class="detail-search-distance__marks">
                            <span>5km以内</span>
                            <span>20km</span>
                            <span>30km</span>
                            <span>40km以上</span>
                        </div>
                        <input type="range" id="search-distance-km" name="distance_km" class="detail-search-distance-slider"
                               min="5" max="40" step="5"
                               value="{{ max(5, min(40, $maxDistanceKm ?: 20)) }}" aria-label="距離">
                        <output for="search-distance-km" class="detail-search-distance__value" id="search-distance-value">
                            {{ ($maxDistanceKm >= 40) ? '40km以上' : ($maxDistanceKm . 'km') }}
                        </output>
                    </div>
                </div>

                {{-- 年齢範囲 --}}
                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="年齢" data-open="true">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="true">
                        <span>年齢</span>
                        <span class="detail-search-accordion__icon">−</span>
                    </button>
                    <div class="detail-search-accordion__body">
                        <div class="detail-search-age-range">
                            <label class="detail-search-age-range__field">
                                <span>下限</span>
                                <input type="number" name="age_min" min="18" max="99" value="{{ $ageMin }}" placeholder="18">
                                <span class="detail-search-age-range__unit">歳</span>
                            </label>
                            <span class="detail-search-age-range__separator">〜</span>
                            <label class="detail-search-age-range__field">
                                <span>上限</span>
                                <input type="number" name="age_max" min="18" max="99" value="{{ $ageMax }}" placeholder="30">
                                <span class="detail-search-age-range__unit">歳</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- 出勤頻度・時間帯 --}}
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
                                    <input type="radio" name="shift_frequency" value="" {{ $shiftFrequency === '' ? 'checked' : '' }}>
                                    <span>指定なし</span>
                                </label>
                                @foreach(['週1回出勤', '週2回出勤', '週3回以上'] as $freq)
                                    <label class="detail-search-chip detail-search-chip--search">
                                        <input type="radio" name="shift_frequency" value="{{ $freq }}" {{ $shiftFrequency === $freq ? 'checked' : '' }}>
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
                                        <input type="checkbox" name="work_periods[]" value="{{ $key }}" {{ in_array($key, $workPeriods, true) ? 'checked' : '' }}>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 経験 --}}
                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="ナイトワーク経験">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>ナイトワーク経験</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($nightWorkExpLabels as $key => $label)
                                @php $checked = ($nightWorkExp === '' && $key === 'any') || $nightWorkExp === $key; @endphp
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="radio" name="night_work_exp" value="{{ $key }}" {{ $checked ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ルックス --}}
                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="ルックス">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>ルックス</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($tagsByCat['looks'] ?? [] as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="looks_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedLooks, true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 性格・内面 --}}
                <div class="detail-search-accordion detail-search-accordion--panel" data-accordion data-summary-group="性格・内面">
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>性格・内面</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips detail-search-chips--search">
                            @foreach($tagsByCat['personality'] ?? [] as $tag)
                                <label class="detail-search-chip detail-search-chip--search">
                                    <input type="checkbox" name="personality_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, $selectedPersonality, true) ? 'checked' : '' }}>
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
                    data-save-url="{{ route('shop.search-preferences.save') }}">
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
    function readInputValue(name) {
        var el = form.querySelector('[name="' + name + '"]');
        return el ? String(el.value || '').trim() : '';
    }

    saveBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (saveBtn.dataset.busy === '1') return;
        saveBtn.dataset.busy = '1';
        if (feedback) { feedback.hidden = true; feedback.className = 'detail-search-modal__save-feedback'; }

        var payload = new FormData();
        payload.append('_token', (document.querySelector('meta[name="csrf-token"]') || {}).content || '');

        var distance = readInputValue('distance_km');
        if (distance !== '') payload.append('max_distance_km', distance === '40' ? '100' : distance);

        var ageMin = readInputValue('age_min');
        if (ageMin !== '') payload.append('age_min', ageMin);
        var ageMax = readInputValue('age_max');
        if (ageMax !== '') payload.append('age_max', ageMax);

        var freq = readRadioValue('shift_frequency');
        if (freq !== '') payload.append('shift_frequency', freq);
        readCheckedValues('work_periods[]').forEach(function (v) { payload.append('work_periods[]', v); });
        readCheckedValues('looks_tag_ids[]').forEach(function (v) { payload.append('looks_tag_ids[]', v); });
        readCheckedValues('personality_tag_ids[]').forEach(function (v) { payload.append('personality_tag_ids[]', v); });
        var exp = readRadioValue('night_work_exp');
        if (exp !== '') payload.append('night_work_exp', exp);

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
.detail-search-age-range { display: flex; align-items: center; gap: 10px; padding: 4px 2px; }
.detail-search-age-range__field {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex: 1;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    padding: 8px 10px;
}
.detail-search-age-range__field > span:first-child {
    font-size: 0.7rem;
    color: var(--color-text-muted);
    font-weight: 700;
}
.detail-search-age-range__field input {
    flex: 1;
    width: 100%;
    background: transparent;
    border: 0;
    color: var(--color-text-header);
    font-size: 0.95rem;
    font-weight: 700;
    text-align: right;
    outline: none;
    min-height: 28px;
}
.detail-search-age-range__unit {
    font-size: 0.75rem;
    color: var(--gold);
    font-weight: 700;
}
.detail-search-age-range__separator {
    color: var(--color-text-muted);
    font-weight: 700;
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
