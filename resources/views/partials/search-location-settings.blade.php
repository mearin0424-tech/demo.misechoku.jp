{{--
位置情報フィルタ設定カード（cast / shop 共通）
@param array|null $searchLocationSettings  UserLocationService::loadProfileSettings() の戻り値
@param array      $searchLocationDistanceOptions  UserLocationService::DISTANCE_OPTIONS_KM
@param string     $updateRouteName  POST 先のルート名（cast.mypage.search-location.update / shop.mypage.search-location.update）
--}}
@php
    $settings = $searchLocationSettings ?? null;
    $currentMode = (string) ($settings['mode'] ?? '');
    $currentMaxKm = $settings['max_distance_km'] ?? null;
    $currentPassportAddress = $settings['passport_address'] ?? '';
    $currentPassportLabel = $settings['passport_label'] ?? '';
    $hasProfileLocation = !empty($settings['profile_location']);
    $distanceOptions = $searchLocationDistanceOptions ?? [0, 1, 3, 5, 10, 20, 30, 50, 100];
    $updateUrl = route($updateRouteName);
@endphp
<section class="search-location-card glass-panel" aria-labelledby="search-location-card-title">
    <h2 id="search-location-card-title" class="search-location-card__title">
        <i class="fas fa-location-arrow" aria-hidden="true"></i>
        位置情報での絞り込み
    </h2>
    <p class="search-location-card__lead">
        スワイプホーム・検索画面で、ここに設定した拠点から半径以内の相手だけを表示します。
    </p>

    <form id="search-location-form" action="{{ $updateUrl }}" method="POST" class="search-location-form">
        @csrf

        <fieldset class="search-location-modes">
            <legend class="sr-only">基準にする位置</legend>

            <label class="search-location-mode {{ $currentMode === 'profile' ? 'is-selected' : '' }}">
                <input type="radio" name="mode" value="profile" @checked($currentMode === 'profile')>
                <span class="search-location-mode__inner">
                    <span class="search-location-mode__head">
                        <i class="fas fa-home" aria-hidden="true"></i>
                        <span class="search-location-mode__label">登録住所</span>
                    </span>
                    <span class="search-location-mode__sub">
                        @if($hasProfileLocation)
                            プロフィールの住所を基準にします
                        @else
                            プロフィールに住所が登録されていません
                        @endif
                    </span>
                </span>
            </label>

            <label class="search-location-mode {{ $currentMode === 'passport' ? 'is-selected' : '' }}">
                <input type="radio" name="mode" value="passport" @checked($currentMode === 'passport')>
                <span class="search-location-mode__inner">
                    <span class="search-location-mode__head">
                        <i class="fas fa-passport" aria-hidden="true"></i>
                        <span class="search-location-mode__label">指定地（パスポート）</span>
                    </span>
                    <span class="search-location-mode__sub">住所や駅名で任意の場所を指定</span>
                </span>
            </label>

            <label class="search-location-mode {{ $currentMode === 'current' ? 'is-selected' : '' }}">
                <input type="radio" name="mode" value="current" @checked($currentMode === 'current')>
                <span class="search-location-mode__inner">
                    <span class="search-location-mode__head">
                        <i class="fas fa-location-crosshairs" aria-hidden="true"></i>
                        <span class="search-location-mode__label">現在地</span>
                    </span>
                    <span class="search-location-mode__sub">端末の位置情報を毎回取得</span>
                </span>
            </label>
        </fieldset>

        {{-- パスポートモード：住所入力 --}}
        <div class="search-location-passport" data-mode-section="passport" {{ $currentMode === 'passport' ? '' : 'hidden' }}>
            <label class="search-location-input-label" for="search-location-passport-address">住所・駅名</label>
            <input id="search-location-passport-address"
                   type="text"
                   name="passport_address"
                   class="search-location-input"
                   maxlength="255"
                   placeholder="例：東京都新宿区歌舞伎町、渋谷駅"
                   value="{{ $currentPassportAddress }}">
            <input type="hidden" name="passport_lat" id="search-location-passport-lat" value="{{ $settings['passport_latitude'] ?? '' }}">
            <input type="hidden" name="passport_lng" id="search-location-passport-lng" value="{{ $settings['passport_longitude'] ?? '' }}">
            <p class="search-location-hint">保存時に住所をジオコーディングして緯度経度を取得します。</p>
            @if($currentPassportLabel !== '' && $currentMode === 'passport')
                <p class="search-location-current">現在の指定：<strong>{{ $currentPassportLabel }}</strong></p>
            @endif
        </div>

        {{-- 現在地モード：取得ボタン --}}
        <div class="search-location-current-section" data-mode-section="current" {{ $currentMode === 'current' ? '' : 'hidden' }}>
            <button type="button" class="search-location-current-btn" id="search-location-current-btn">
                <i class="fas fa-location-crosshairs" aria-hidden="true"></i>
                端末の現在地を取得
            </button>
            <p class="search-location-hint">ボタンを押すたびに最新の位置を反映します。</p>
            <input type="hidden" name="current_lat" id="search-location-current-lat" value="">
            <input type="hidden" name="current_lng" id="search-location-current-lng" value="">
            <p class="search-location-current" id="search-location-current-label" hidden></p>
        </div>

        {{-- 半径 --}}
        <div class="search-location-distance">
            <label class="search-location-input-label" for="search-location-distance-select">表示する半径</label>
            <select id="search-location-distance-select" name="max_distance_km" class="search-location-select">
                @foreach($distanceOptions as $opt)
                    @if($opt === 0)
                        <option value="0" @selected(($currentMaxKm ?? 0) === 0)>制限なし</option>
                    @else
                        <option value="{{ $opt }}" @selected($currentMaxKm === $opt)>{{ $opt }} km 以内</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="search-location-actions">
            <button type="submit" class="search-location-save-btn" id="search-location-save-btn">
                <i class="fas fa-floppy-disk"></i> 保存する
            </button>
        </div>
        <p class="search-location-feedback" id="search-location-feedback" hidden></p>
    </form>
</section>

@push('styles')
<style>
.search-location-card {
    margin: 16px 0 20px;
    padding: 18px 18px 16px;
    border-radius: 18px;
    border: 1px solid rgba(220, 181, 104, 0.22);
    background: linear-gradient(180deg, rgba(220, 181, 104, 0.06), rgba(255, 255, 255, 0.02));
}
.search-location-card__title {
    margin: 0 0 6px;
    font-size: 0.95rem;
    font-weight: 800;
    color: #f8e9c8;
    display: flex;
    align-items: center;
    gap: 8px;
}
.search-location-card__title i { color: #dcb568; font-size: 0.85rem; }
.search-location-card__lead {
    margin: 0 0 14px;
    font-size: 0.78rem;
    line-height: 1.55;
    color: rgba(248, 233, 200, 0.66);
}

.search-location-modes {
    display: grid;
    gap: 8px;
    border: 0;
    padding: 0;
    margin: 0 0 14px;
}
.search-location-mode {
    cursor: pointer;
    border: 1px solid rgba(220, 181, 104, 0.18);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.03);
    padding: 10px 12px;
    transition: border-color 0.15s ease, background 0.15s ease;
    display: block;
}
.search-location-mode input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}
.search-location-mode.is-selected,
.search-location-mode:has(input[type="radio"]:checked) {
    border-color: rgba(220, 181, 104, 0.6);
    background: rgba(220, 181, 104, 0.10);
}
.search-location-mode__inner { display: flex; flex-direction: column; gap: 2px; }
.search-location-mode__head {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.86rem;
    font-weight: 800;
    color: #f8e9c8;
}
.search-location-mode__head i { color: #dcb568; font-size: 0.78rem; }
.search-location-mode__sub {
    font-size: 0.7rem;
    color: rgba(248, 233, 200, 0.6);
    line-height: 1.5;
}

.search-location-passport,
.search-location-current-section,
.search-location-distance {
    margin-bottom: 12px;
}
.search-location-input-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(220, 181, 104, 0.85);
    letter-spacing: 0.04em;
    margin: 0 0 6px;
}
.search-location-input,
.search-location-select {
    width: 100%;
    box-sizing: border-box;
    padding: 11px 12px;
    border-radius: 10px;
    border: 1px solid rgba(220, 181, 104, 0.22);
    background: rgba(255, 255, 255, 0.05);
    color: #fafafa;
    font-size: 0.9rem;
}
.search-location-input::placeholder { color: rgba(248, 233, 200, 0.32); }
.search-location-input:focus,
.search-location-select:focus {
    outline: none;
    border-color: rgba(220, 181, 104, 0.55);
    box-shadow: 0 0 0 3px rgba(220, 181, 104, 0.15);
}
.search-location-hint {
    margin: 6px 0 0;
    font-size: 0.7rem;
    color: rgba(248, 233, 200, 0.5);
    line-height: 1.5;
}
.search-location-current {
    margin: 8px 0 0;
    font-size: 0.78rem;
    color: rgba(220, 181, 104, 0.85);
}
.search-location-current strong { color: #ffe2a3; font-weight: 800; }

.search-location-current-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid rgba(220, 181, 104, 0.4);
    background: rgba(220, 181, 104, 0.08);
    color: #ffe2a3;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease;
}
.search-location-current-btn:hover { background: rgba(220, 181, 104, 0.16); }
.search-location-current-btn:disabled { opacity: 0.6; cursor: wait; }

.search-location-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 14px;
}
.search-location-save-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    border-radius: 999px;
    background: linear-gradient(135deg, #ffe2a3, #dcb568 50%, #b8860b);
    color: #2a1406;
    font-weight: 800;
    font-size: 0.86rem;
    border: 0;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(220, 181, 104, 0.4);
}
.search-location-save-btn:hover { transform: translateY(-1px); }
.search-location-save-btn:disabled { opacity: 0.6; cursor: wait; transform: none; }

.search-location-feedback {
    margin: 10px 0 0;
    font-size: 0.78rem;
    line-height: 1.5;
}
.search-location-feedback.is-success { color: #6ee7b7; }
.search-location-feedback.is-error { color: #fca5a5; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var form = document.getElementById('search-location-form');
    if (!form) return;
    var modeInputs = form.querySelectorAll('input[name="mode"]');
    var sections = form.querySelectorAll('[data-mode-section]');
    var saveBtn = document.getElementById('search-location-save-btn');
    var feedback = document.getElementById('search-location-feedback');
    var currentBtn = document.getElementById('search-location-current-btn');
    var currentLatEl = document.getElementById('search-location-current-lat');
    var currentLngEl = document.getElementById('search-location-current-lng');
    var currentLabelEl = document.getElementById('search-location-current-label');

    function syncMode() {
        var current = (form.querySelector('input[name="mode"]:checked') || {}).value || '';
        sections.forEach(function (sec) {
            sec.hidden = sec.getAttribute('data-mode-section') !== current;
        });
        // 視覚的選択状態の同期
        form.querySelectorAll('.search-location-mode').forEach(function (el) {
            var radio = el.querySelector('input[type="radio"]');
            el.classList.toggle('is-selected', !!(radio && radio.checked));
        });
    }
    modeInputs.forEach(function (i) { i.addEventListener('change', syncMode); });
    syncMode();

    if (currentBtn) {
        currentBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                feedback.hidden = false;
                feedback.className = 'search-location-feedback is-error';
                feedback.textContent = 'この端末では位置情報を取得できません。';
                return;
            }
            currentBtn.disabled = true;
            navigator.geolocation.getCurrentPosition(function (pos) {
                currentBtn.disabled = false;
                if (currentLatEl) currentLatEl.value = pos.coords.latitude.toFixed(7);
                if (currentLngEl) currentLngEl.value = pos.coords.longitude.toFixed(7);
                if (currentLabelEl) {
                    currentLabelEl.hidden = false;
                    currentLabelEl.innerHTML = '取得済み：<strong>' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4) + '</strong>';
                }
            }, function () {
                currentBtn.disabled = false;
                feedback.hidden = false;
                feedback.className = 'search-location-feedback is-error';
                feedback.textContent = '位置情報の取得に失敗しました。ブラウザの権限を確認してください。';
            }, { enableHighAccuracy: true, maximumAge: 60000, timeout: 8000 });
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (saveBtn) saveBtn.disabled = true;
        feedback.hidden = true;
        feedback.className = 'search-location-feedback';

        var fd = new FormData(form);
        var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: fd
        })
        .then(function (r) { return r.json().then(function (b) { return { ok: r.ok, body: b }; }); })
        .then(function (res) {
            if (saveBtn) saveBtn.disabled = false;
            if (res.ok && res.body && res.body.success) {
                feedback.hidden = false;
                feedback.className = 'search-location-feedback is-success';
                feedback.textContent = '位置情報の絞り込み設定を保存しました。';
            } else {
                feedback.hidden = false;
                feedback.className = 'search-location-feedback is-error';
                feedback.textContent = (res.body && res.body.message) ? res.body.message : '保存に失敗しました。';
            }
        })
        .catch(function () {
            if (saveBtn) saveBtn.disabled = false;
            feedback.hidden = false;
            feedback.className = 'search-location-feedback is-error';
            feedback.textContent = '通信に失敗しました。';
        });
    });
})();
</script>
@endpush
