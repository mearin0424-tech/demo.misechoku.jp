{{--
位置情報フィルタ設定（cast / shop 共通）
MyPage 上はトリガーボタンのみを表示し、押下でダイアログを開く形式。
@param array|null $searchLocationSettings  UserLocationService::loadProfileSettings() の戻り値
@param array      $searchLocationDistanceOptions  UserLocationService::DISTANCE_OPTIONS_KM（保持・互換のため受け取るが UI では4段階スライダーを使用）
@param string     $updateRouteName  POST 先のルート名（cast.mypage.search-location.update / shop.mypage.search-location.update）
--}}
@php
    $settings = $searchLocationSettings ?? null;
    $currentMode = (string) ($settings['mode'] ?? '');
    $currentMaxKm = (int) ($settings['max_distance_km'] ?? 20);
    $currentPassportAddress = $settings['passport_address'] ?? '';
    $currentPassportLabel = $settings['passport_label'] ?? '';
    // プロフィールに住所が登録されているかどうかは pref/city/addr のいずれかが入っているかで判定。
    // 緯度経度はジオコーディング前だと未取得のことがあるが、住所自体は登録済みのため
    // 「住所が登録されていません」と表示しない（保存時に自動でジオコーディングする）。
    $hasProfileAddress = !empty($settings['has_address']);
    $hasProfileLocation = !empty($settings['profile_location']);
    $profileAddressText = (string) ($settings['profile_address'] ?? '');
    $updateUrl = route($updateRouteName);

    // スライダーの目盛り → 実際に submit する km 値
    // 5km以内 / 20km / 30km / 40km以上（=100km）
    $sliderMarks = [
        ['value' => 5,   'label' => '5km以内'],
        ['value' => 20,  'label' => '20km'],
        ['value' => 30,  'label' => '30km'],
        ['value' => 100, 'label' => '40km以上'],
    ];

    // 既存値からスライダー初期位置を決定
    $sliderInitialIndex = 1; // 既定: 20km
    $minDelta = PHP_INT_MAX;
    foreach ($sliderMarks as $i => $m) {
        $d = abs($m['value'] - $currentMaxKm);
        if ($d < $minDelta) { $minDelta = $d; $sliderInitialIndex = $i; }
    }
    if ($currentMaxKm >= 40) {
        $sliderInitialIndex = 3; // 40km以上はスライダー最大に寄せる
    }
@endphp

{{-- MyPage 内のトリガーボタン --}}
<button type="button" class="search-location-trigger" id="search-location-trigger" aria-haspopup="dialog" aria-controls="search-location-dialog">
    <span class="search-location-trigger__icon"><i class="fas fa-location-arrow" aria-hidden="true"></i></span>
    <span class="search-location-trigger__body">
        <span class="search-location-trigger__title">位置情報での絞り込み</span>
        <span class="search-location-trigger__sub">
            @if($currentMode === 'profile')
                登録住所を基準
            @elseif($currentMode === 'passport')
                指定地：{{ $currentPassportLabel ?: ($currentPassportAddress ?: '未設定') }}
            @elseif($currentMode === 'current')
                現在地を基準
            @else
                未設定
            @endif
            @if($currentMaxKm > 0)
                ／ {{ $sliderMarks[$sliderInitialIndex]['label'] }}
            @endif
        </span>
    </span>
    <span class="search-location-trigger__chevron" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
</button>

{{-- ダイアログ --}}
<div class="search-location-dialog-overlay" id="search-location-dialog-overlay" aria-hidden="true">
    <div class="search-location-dialog" id="search-location-dialog" role="dialog" aria-modal="true" aria-labelledby="search-location-dialog-title">
        <button type="button" class="search-location-dialog__close js-search-location-close" aria-label="閉じる">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>

        <header class="search-location-dialog__head">
            <h2 id="search-location-dialog-title" class="search-location-dialog__title">
                <i class="fas fa-location-arrow" aria-hidden="true"></i>
                位置情報での絞り込み
            </h2>
            <p class="search-location-dialog__lead">
                スワイプ・検索画面で、ここに設定した拠点から指定半径以内の相手だけを表示します。
            </p>
        </header>

        <form id="search-location-form" action="{{ $updateUrl }}" method="POST" class="search-location-form">
            @csrf

            {{-- 拠点選択 --}}
            <fieldset class="search-location-modes">
                <legend class="search-location-section-title">基準となる拠点</legend>

                {{-- 登録住所 --}}
                <label class="search-location-card {{ $currentMode === 'profile' ? 'is-selected' : '' }} {{ $hasProfileAddress ? '' : 'is-disabled-soft' }}" data-mode-card="profile">
                    <input type="radio" name="mode" value="profile" @checked($currentMode === 'profile')>
                    <span class="search-location-card__row">
                        <i class="fas fa-home search-location-card__icon"></i>
                        <span class="search-location-card__main">
                            <span class="search-location-card__title">登録住所</span>
                            <span class="search-location-card__sub {{ $hasProfileAddress ? '' : 'is-warn' }}">
                                @if($hasProfileLocation)
                                    {{ $profileAddressText !== '' ? $profileAddressText : 'プロフィールの住所を基準にします' }}
                                @elseif($hasProfileAddress)
                                    {{ $profileAddressText }}（保存時に緯度経度を自動取得します）
                                @else
                                    プロフィールに住所が登録されていません
                                @endif
                            </span>
                        </span>
                    </span>
                    @unless($hasProfileAddress)
                        <span class="search-location-card__expand" data-mode-section="profile">
                            <a href="{{ url('/setting/account') }}" class="search-location-inline-action">
                                住所を登録する <i class="fas fa-chevron-right"></i>
                            </a>
                        </span>
                    @endunless
                </label>

                {{-- 指定地 --}}
                <label class="search-location-card {{ $currentMode === 'passport' ? 'is-selected' : '' }}" data-mode-card="passport">
                    <input type="radio" name="mode" value="passport" @checked($currentMode === 'passport')>
                    <span class="search-location-card__row">
                        <i class="fas fa-map search-location-card__icon"></i>
                        <span class="search-location-card__main">
                            <span class="search-location-card__title">
                                指定地
                                <span class="search-location-card__badge">PASSPORT</span>
                            </span>
                            <span class="search-location-card__sub">住所や駅名で任意の場所を指定できます</span>
                        </span>
                    </span>
                    <span class="search-location-card__expand" data-mode-section="passport">
                        <span class="search-location-passport-row">
                            <span class="search-location-input-wrap search-location-suggest-wrap" id="search-location-suggest-wrap">
                                <i class="fas fa-search"></i>
                                <input id="search-location-passport-address"
                                       type="text"
                                       name="passport_address"
                                       class="search-location-input"
                                       maxlength="255"
                                       placeholder="例: 渋谷駅, 新宿区..."
                                       value="{{ $currentPassportAddress }}"
                                       autocomplete="off"
                                       role="combobox"
                                       aria-autocomplete="list"
                                       aria-controls="search-location-suggest-list"
                                       aria-expanded="false">
                                <ul class="search-location-suggest-list" id="search-location-suggest-list" role="listbox" hidden></ul>
                            </span>
                            <button type="button" class="search-location-lookup-btn" id="search-location-lookup-btn">
                                <i class="fas fa-magnifying-glass-location" data-lookup-icon></i>
                                <span data-lookup-label>検索</span>
                            </button>
                        </span>
                        <input type="hidden" name="passport_lat" id="search-location-passport-lat" value="{{ $settings['passport_latitude'] ?? '' }}">
                        <input type="hidden" name="passport_lng" id="search-location-passport-lng" value="{{ $settings['passport_longitude'] ?? '' }}">
                        <p class="search-location-passport-status" id="search-location-passport-status"
                           data-default-message="住所・駅名を入れて『検索』を押してください"
                           data-state="{{ ($currentMode === 'passport' && $currentPassportLabel !== '' && !empty($settings['passport_latitude'])) ? 'resolved' : 'idle' }}">
                            @if($currentMode === 'passport' && $currentPassportLabel !== '' && !empty($settings['passport_latitude']))
                                <i class="fas fa-circle-check"></i>
                                <span>解決済み: <strong>{{ $currentPassportLabel }}</strong>（{{ number_format((float) $settings['passport_latitude'], 4) }}, {{ number_format((float) $settings['passport_longitude'], 4) }}）</span>
                            @else
                                <i class="fas fa-info-circle"></i>
                                <span>住所・駅名を入れて『検索』を押してください</span>
                            @endif
                        </p>
                    </span>
                </label>

                {{-- 現在地 --}}
                <label class="search-location-card {{ $currentMode === 'current' ? 'is-selected' : '' }}" data-mode-card="current">
                    <input type="radio" name="mode" value="current" @checked($currentMode === 'current')>
                    <span class="search-location-card__row">
                        <i class="fas fa-location-crosshairs search-location-card__icon"></i>
                        <span class="search-location-card__main">
                            <span class="search-location-card__title">現在地</span>
                            <span class="search-location-card__sub">端末の位置情報を使用します</span>
                        </span>
                        <i class="fas fa-circle-check search-location-card__check" data-current-check hidden></i>
                    </span>
                    <span class="search-location-card__expand" data-mode-section="current">
                        <button type="button" class="search-location-current-btn" id="search-location-current-btn">
                            <i class="fas fa-location-crosshairs" aria-hidden="true"></i>
                            <span data-current-btn-label>最新の現在地を取得する</span>
                        </button>
                        <p class="search-location-hint" id="search-location-current-label" hidden>
                            <i class="fas fa-info-circle"></i>
                            <span>最新の位置情報が反映されています</span>
                        </p>
                        <input type="hidden" name="current_lat" id="search-location-current-lat" value="">
                        <input type="hidden" name="current_lng" id="search-location-current-lng" value="">
                    </span>
                </label>
            </fieldset>

            {{-- 半径スライダー --}}
            <div class="search-location-radius">
                <h3 class="search-location-section-title">表示する半径</h3>
                <div class="search-location-slider">
                    <div class="search-location-slider__marks" id="search-location-slider-marks">
                        @foreach($sliderMarks as $i => $m)
                            <span class="search-location-slider__mark {{ $i === $sliderInitialIndex ? 'is-active' : '' }}" data-slider-mark="{{ $i }}">
                                {{ $m['label'] }}
                            </span>
                        @endforeach
                    </div>
                    <input type="range"
                           id="search-location-slider"
                           class="search-location-range"
                           min="0" max="{{ count($sliderMarks) - 1 }}" step="1"
                           value="{{ $sliderInitialIndex }}">
                    <div class="search-location-slider__value" id="search-location-slider-value">
                        {{ $sliderMarks[$sliderInitialIndex]['label'] }}
                    </div>
                </div>
                {{-- 実際に submit される km 値（スライダー操作で更新） --}}
                <input type="hidden" name="max_distance_km" id="search-location-max-km" value="{{ $sliderMarks[$sliderInitialIndex]['value'] }}">
            </div>

            <div class="search-location-actions">
                <button type="submit" class="search-location-save-btn" id="search-location-save-btn">
                    この条件で保存する
                </button>
            </div>
            <p class="search-location-feedback" id="search-location-feedback" hidden></p>
        </form>
    </div>
</div>

@push('styles')
<style>
/* ===== トリガーボタン ===== */
.search-location-trigger {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    margin: 16px 0 16px;
    padding: 14px 16px;
    border-radius: 14px;
    border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.22);
    background: linear-gradient(180deg, rgba(var(--accent-rgb, 214, 112, 162), 0.06), rgba(255, 255, 255, 0.02));
    color: #e6dffc;
    cursor: pointer;
    text-align: left;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.search-location-trigger:hover {
    border-color: rgba(var(--accent-rgb, 214, 112, 162), 0.45);
    background: rgba(var(--accent-rgb, 214, 112, 162), 0.10);
}
.search-location-trigger__icon {
    width: 36px; height: 36px; flex-shrink: 0;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(var(--accent-rgb, 214, 112, 162), 0.15);
    color: #eba8c8;
}
.search-location-trigger__body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.search-location-trigger__title { font-size: 0.92rem; font-weight: 800; color: #e6dffc; }
.search-location-trigger__sub {
    font-size: 0.74rem; color: rgba(196, 181, 253, 0.62);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.search-location-trigger__chevron { color: rgba(var(--accent-rgb, 214, 112, 162), 0.7); }

/* ===== ダイアログ ===== */
.search-location-dialog-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(8, 4, 6, 0.78);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
}
.search-location-dialog-overlay[aria-hidden="false"] { display: flex; }
.search-location-dialog {
    position: relative;
    width: 100%; max-width: 380px;
    max-height: min(78vh, 640px);
    overflow-y: auto;
    background: #2D1B24;
    color: #fff;
    border: 1px solid rgba(232, 195, 114, 0.55);
    border-radius: 20px;
    box-shadow:
        0 0 0 1px rgba(232, 195, 114, 0.18),
        0 24px 48px rgba(0, 0, 0, 0.6),
        0 0 60px rgba(232, 195, 114, 0.18);
    padding: 20px 18px;
    animation: search-location-pop 0.2s ease-out;
}
@keyframes search-location-pop {
    from { transform: scale(0.96) translateY(8px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}

.search-location-dialog__close {
    position: absolute; top: 12px; right: 12px;
    width: 32px; height: 32px;
    border-radius: 50%;
    border: 1px solid #4a2f3e;
    background: #3a232f;
    color: #e6dffc;
    cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.78rem;
    z-index: 2;
}
.search-location-dialog__close:hover { background: #4a2f3e; }

.search-location-dialog__head { padding: 0 28px 12px 0; border-bottom: 1px solid rgba(74, 47, 62, 0.6); margin-bottom: 16px; }
.search-location-dialog__title {
    margin: 0 0 6px;
    font-size: 0.98rem; font-weight: 800;
    color: #fff;
    display: flex; align-items: center; gap: 8px;
}
.search-location-dialog__title i { color: #E8C372; font-size: 0.9rem; }
.search-location-dialog__lead {
    margin: 0;
    font-size: 0.75rem; line-height: 1.6;
    color: rgba(255, 255, 255, 0.68);
}

/* ===== セクションタイトル ===== */
.search-location-section-title {
    font-size: 0.78rem; font-weight: 700;
    color: rgba(255, 255, 255, 0.55);
    margin: 0 0 12px;
    padding: 0;
    letter-spacing: 0.04em;
}

/* ===== 拠点カード ===== */
.search-location-modes {
    border: 0; padding: 0; margin: 0 0 18px;
    display: flex; flex-direction: column; gap: 8px;
}
.search-location-card {
    position: relative;
    display: block;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #4a2f3e;
    background: #3a232f;
    cursor: pointer;
    transition: border-color 0.18s ease, background 0.18s ease, opacity 0.18s ease;
}
.search-location-card:hover { border-color: rgba(232, 195, 114, 0.5); }
.search-location-card.is-selected {
    border-color: #E8C372;
    background: rgba(232, 195, 114, 0.10);
}
.search-location-card.is-disabled-soft:not(.is-selected) { opacity: 0.7; }
.search-location-card input[type="radio"] {
    position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none;
}
.search-location-card__row {
    display: flex; align-items: flex-start; gap: 12px;
}
.search-location-card__icon {
    color: rgba(255, 255, 255, 0.45);
    font-size: 1.05rem;
    margin-top: 2px;
    transition: color 0.18s ease;
}
.search-location-card.is-selected .search-location-card__icon { color: #E8C372; }
.search-location-card__main { flex: 1; min-width: 0; }
.search-location-card__title {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    font-size: 0.95rem; font-weight: 700;
    color: rgba(255, 255, 255, 0.85);
}
.search-location-card.is-selected .search-location-card__title { color: #fff; }
.search-location-card__badge {
    font-size: 0.62rem; letter-spacing: 0.14em; font-weight: 800;
    padding: 2px 8px; border-radius: 4px;
    background: linear-gradient(135deg, #E8C372, #eba8c8);
    color: #1a1015;
}
.search-location-card__sub {
    display: block;
    margin-top: 4px;
    font-size: 0.74rem; line-height: 1.5;
    color: rgba(255, 255, 255, 0.55);
}
.search-location-card__sub.is-warn { color: #fca5a5; }
.search-location-card__check {
    color: #34d399;
    font-size: 1.15rem;
    margin-top: 2px;
}

/* 選択時にだけ展開する内側コンテンツ */
.search-location-card__expand {
    display: none;
    margin: 12px 0 0 28px;
}
.search-location-card.is-selected .search-location-card__expand { display: block; }

/* 指定地：検索ボタン付きの入力行 */
.search-location-passport-row {
    display: flex; gap: 8px; align-items: stretch;
}
.search-location-passport-row .search-location-input-wrap { flex: 1; min-width: 0; }
.search-location-lookup-btn {
    flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 6px;
    padding: 0 14px;
    border: 0;
    border-radius: 10px;
    background: linear-gradient(135deg, #E8C372, #eba8c8);
    color: #1a1015;
    font-size: 0.82rem; font-weight: 800;
    cursor: pointer;
    transition: filter 0.15s ease;
}
.search-location-lookup-btn:hover { filter: brightness(1.05); }
.search-location-lookup-btn:disabled { opacity: 0.6; cursor: wait; }
.search-location-lookup-btn.is-loading [data-lookup-icon] {
    animation: search-location-spin 0.9s linear infinite;
}

.search-location-passport-status {
    display: flex; align-items: flex-start; gap: 6px;
    margin: 10px 0 0;
    font-size: 0.74rem; line-height: 1.55;
    color: rgba(255, 255, 255, 0.6);
}
.search-location-passport-status i { color: rgba(232, 195, 114, 0.6); margin-top: 2px; }
.search-location-passport-status[data-state="resolved"] { color: rgba(110, 231, 183, 0.95); }
.search-location-passport-status[data-state="resolved"] i { color: #34d399; }
.search-location-passport-status[data-state="resolved"] strong { color: #fff; font-weight: 800; }
.search-location-passport-status[data-state="error"] { color: #fca5a5; }
.search-location-passport-status[data-state="error"] i { color: #fca5a5; }
.search-location-passport-status[data-state="loading"] { color: rgba(232, 195, 114, 0.85); }
.search-location-passport-status[data-state="loading"] i { color: #E8C372; animation: search-location-spin 0.9s linear infinite; }

.search-location-inline-action {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 12px;
    border-radius: 999px;
    background: rgba(244, 63, 94, 0.18);
    color: #fda4af;
    font-size: 0.74rem; font-weight: 700;
    text-decoration: none;
}
.search-location-inline-action:hover { background: rgba(244, 63, 94, 0.28); color: #fecaca; }
.search-location-inline-action i { font-size: 0.65rem; }

.search-location-input-wrap {
    position: relative;
    display: block;
}
.search-location-input-wrap > i {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.45);
    font-size: 0.85rem;
}
.search-location-input {
    width: 100%; box-sizing: border-box;
    padding: 11px 14px 11px 36px;
    border-radius: 12px;
    border: 1px solid #4a2f3e;
    background: #2D1B24;
    color: #fff;
    font-size: 0.88rem;
}
.search-location-input::placeholder { color: rgba(255, 255, 255, 0.35); }

/* ===== 住所サジェスト ===== */
.search-location-suggest-wrap { position: relative; }
.search-location-suggest-list {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 20;
    margin: 0;
    padding: 4px;
    list-style: none;
    max-height: 220px;
    overflow-y: auto;
    background: var(--color-card-strong, #1a1a1a);
    border: 1px solid var(--color-border-strong, rgba(var(--accent-rgb, 214, 112, 162), 0.4));
    border-radius: 10px;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.55);
}
.search-location-suggest-list[hidden] { display: none; }
.search-location-suggest-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 0.82rem;
    color: var(--color-text, #d8c9a8);
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease;
}
.search-location-suggest-item:hover,
.search-location-suggest-item.is-active {
    background: rgba(var(--accent-rgb, 214, 112, 162), 0.12);
    color: var(--color-text-header, #f2cadf);
}
.search-location-suggest-item i { color: var(--gold, #eba8c8); font-size: 0.78rem; }
.search-location-suggest-empty {
    padding: 10px;
    font-size: 0.78rem;
    color: var(--color-text-muted, rgba(216,201,168,0.65));
    text-align: center;
}
.search-location-input:focus {
    outline: none;
    border-color: #E8C372;
    box-shadow: 0 0 0 3px rgba(232, 195, 114, 0.15);
}

.search-location-current-btn {
    width: 100%;
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 11px 14px;
    border-radius: 12px;
    border: 0;
    background: #4a2f3e;
    color: #fff;
    font-size: 0.86rem; font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
}
.search-location-current-btn:hover { background: #5c3e4c; }
.search-location-current-btn:disabled { opacity: 0.6; cursor: wait; }
.search-location-current-btn i { color: #E8C372; }
.search-location-current-btn.is-loading i { animation: search-location-spin 0.9s linear infinite; }
@keyframes search-location-spin { to { transform: rotate(360deg); } }

.search-location-hint {
    display: flex; align-items: center; gap: 6px;
    margin: 10px 0 0;
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.5);
}
.search-location-hint i { color: rgba(232, 195, 114, 0.6); }
.search-location-current {
    margin: 8px 0 0;
    font-size: 0.78rem;
    color: rgba(232, 195, 114, 0.85);
}
.search-location-current strong { color: #f2cadf; font-weight: 800; }

/* ===== 半径スライダー ===== */
.search-location-radius {
    margin-top: 4px;
    padding-top: 16px;
    border-top: 1px solid rgba(74, 47, 62, 0.6);
}
.search-location-slider { padding: 6px 4px 0; }
.search-location-slider__marks {
    display: flex; justify-content: space-between;
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.4);
    padding: 0 2px;
    margin-bottom: 12px;
}
.search-location-slider__mark { transition: color 0.15s ease; }
.search-location-slider__mark.is-active { color: #E8C372; font-weight: 700; }

.search-location-range {
    -webkit-appearance: none; appearance: none;
    width: 100%; background: transparent;
    margin: 6px 0;
    cursor: pointer;
}
.search-location-range:focus { outline: none; }
.search-location-range::-webkit-slider-runnable-track {
    width: 100%; height: 18px;
    background: #2D1B24;
    border-radius: 999px;
    border: 1px solid #6b4c5a;
}
.search-location-range::-webkit-slider-thumb {
    -webkit-appearance: none; appearance: none;
    height: 24px; width: 24px;
    border-radius: 50%;
    background: #E8C372;
    border: 2px solid #1a1015;
    box-shadow: 0 0 16px rgba(232, 195, 114, 0.5);
    margin-top: -4px;
    cursor: pointer;
}
.search-location-range::-moz-range-track {
    width: 100%; height: 18px;
    background: #2D1B24;
    border-radius: 999px;
    border: 1px solid #6b4c5a;
}
.search-location-range::-moz-range-thumb {
    height: 24px; width: 24px;
    border-radius: 50%;
    background: #E8C372;
    border: 2px solid #1a1015;
    box-shadow: 0 0 16px rgba(232, 195, 114, 0.5);
    cursor: pointer;
}
.search-location-slider__value {
    text-align: center;
    margin-top: 14px;
    font-size: 1.1rem; font-weight: 800;
    color: #E8C372;
    letter-spacing: 0.04em;
}

/* ===== 保存ボタン / フィードバック ===== */
.search-location-actions { margin-top: 20px; }
.search-location-save-btn {
    width: 100%;
    padding: 12px 18px;
    border-radius: 12px;
    border: 0;
    background: linear-gradient(135deg, #E8C372, #eba8c8);
    color: #1a1015;
    font-size: 0.98rem; font-weight: 800;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(232, 195, 114, 0.35);
    transition: transform 0.12s ease, box-shadow 0.12s ease;
}
.search-location-save-btn:hover { box-shadow: 0 8px 22px rgba(232, 195, 114, 0.45); }
.search-location-save-btn:active { transform: scale(0.98); }
.search-location-save-btn:disabled { opacity: 0.6; cursor: wait; transform: none; }

.search-location-feedback {
    margin: 12px 0 0;
    font-size: 0.78rem; line-height: 1.5;
    text-align: center;
}
.search-location-feedback.is-success { color: #6ee7b7; }
.search-location-feedback.is-error { color: #fca5a5; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var trigger = document.getElementById('search-location-trigger');
    var overlay = document.getElementById('search-location-dialog-overlay');
    if (!trigger || !overlay) return;

    var dialog = document.getElementById('search-location-dialog');
    var form = document.getElementById('search-location-form');
    var closeBtns = overlay.querySelectorAll('.js-search-location-close');
    var saveBtn = document.getElementById('search-location-save-btn');
    var feedback = document.getElementById('search-location-feedback');

    var sliderMarks = @json($sliderMarks);
    var range = document.getElementById('search-location-slider');
    var rangeMarks = document.querySelectorAll('#search-location-slider-marks .search-location-slider__mark');
    var rangeValueLabel = document.getElementById('search-location-slider-value');
    var maxKmInput = document.getElementById('search-location-max-km');

    var modeCards = form.querySelectorAll('[data-mode-card]');
    var modeInputs = form.querySelectorAll('input[name="mode"]');
    var passportInput = document.getElementById('search-location-passport-address');
    var passportLatEl = document.getElementById('search-location-passport-lat');
    var passportLngEl = document.getElementById('search-location-passport-lng');
    var lookupBtn = document.getElementById('search-location-lookup-btn');
    var lookupLabelEl = lookupBtn ? lookupBtn.querySelector('[data-lookup-label]') : null;
    var passportStatus = document.getElementById('search-location-passport-status');
    var lookupUrl = @json(route('api.geocoding.lookup'));
    var currentBtn = document.getElementById('search-location-current-btn');
    var currentBtnLabel = currentBtn ? currentBtn.querySelector('[data-current-btn-label]') : null;
    var currentLatEl = document.getElementById('search-location-current-lat');
    var currentLngEl = document.getElementById('search-location-current-lng');
    var currentLabelEl = document.getElementById('search-location-current-label');
    var currentCheck = form.querySelector('[data-current-check]');

    function openDialog() {
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }
    function closeDialog() {
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    trigger.addEventListener('click', openDialog);
    closeBtns.forEach(function (b) { b.addEventListener('click', closeDialog); });
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeDialog();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.getAttribute('aria-hidden') === 'false') closeDialog();
    });

    // 拠点カードの選択状態を input[type=radio] と同期
    function syncMode() {
        var checked = form.querySelector('input[name="mode"]:checked');
        var current = checked ? checked.value : '';
        modeCards.forEach(function (card) {
            card.classList.toggle('is-selected', card.getAttribute('data-mode-card') === current);
        });
    }
    modeInputs.forEach(function (i) { i.addEventListener('change', syncMode); });
    syncMode();

    // ラジオがクリックされたときのみ submit ガード（input 内クリックでは閉じない）
    modeCards.forEach(function (card) {
        card.addEventListener('click', function (e) {
            // フォーム内コントロールでの操作は伝播停止
            var tag = (e.target && e.target.tagName) || '';
            if (tag === 'INPUT' || tag === 'BUTTON' || tag === 'A') return;
        });
    });

    // スライダー
    function syncSlider() {
        var idx = parseInt(range.value, 10);
        if (isNaN(idx) || idx < 0 || idx >= sliderMarks.length) idx = 0;
        var mark = sliderMarks[idx];
        if (rangeValueLabel) rangeValueLabel.textContent = mark.label;
        if (maxKmInput) maxKmInput.value = String(mark.value);
        rangeMarks.forEach(function (el) {
            el.classList.toggle('is-active', parseInt(el.getAttribute('data-slider-mark'), 10) === idx);
        });
    }
    if (range) {
        range.addEventListener('input', syncSlider);
        range.addEventListener('change', syncSlider);
    }
    rangeMarks.forEach(function (el) {
        el.addEventListener('click', function () {
            var i = parseInt(el.getAttribute('data-slider-mark'), 10);
            if (!isNaN(i) && range) {
                range.value = String(i);
                syncSlider();
            }
        });
    });

    // 指定地：住所→緯度経度ルックアップ
    function setPassportStatus(state, html) {
        if (!passportStatus) return;
        passportStatus.setAttribute('data-state', state);
        passportStatus.innerHTML = html;
    }
    function clearPassportCoords() {
        if (passportLatEl) passportLatEl.value = '';
        if (passportLngEl) passportLngEl.value = '';
    }
    function setLookupLoading(isLoading) {
        if (!lookupBtn) return;
        lookupBtn.disabled = !!isLoading;
        lookupBtn.classList.toggle('is-loading', !!isLoading);
        if (lookupLabelEl) lookupLabelEl.textContent = isLoading ? '検索中...' : '検索';
    }
    function performLookup() {
        if (!passportInput) return;
        var q = (passportInput.value || '').trim();
        if (q === '') {
            setPassportStatus('error', '<i class="fas fa-circle-exclamation"></i><span>住所または駅名を入力してください</span>');
            return;
        }
        setLookupLoading(true);
        setPassportStatus('loading', '<i class="fas fa-spinner"></i><span>位置情報を検索中...</span>');
        var url = lookupUrl + (lookupUrl.indexOf('?') >= 0 ? '&' : '?') + 'q=' + encodeURIComponent(q);
        fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json().then(function (b) { return { ok: r.ok, body: b }; }); })
        .then(function (res) {
            setLookupLoading(false);
            if (res.ok && res.body && res.body.success) {
                if (passportLatEl) passportLatEl.value = String(res.body.latitude);
                if (passportLngEl) passportLngEl.value = String(res.body.longitude);
                var label = res.body.label || q;
                var lat = parseFloat(res.body.latitude).toFixed(4);
                var lng = parseFloat(res.body.longitude).toFixed(4);
                setPassportStatus('resolved',
                    '<i class="fas fa-circle-check"></i><span>解決済み: <strong>' +
                    label.replace(/[<>&"]/g, function (c) { return ({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[c]); }) +
                    '</strong>（' + lat + ', ' + lng + '）</span>'
                );
            } else {
                clearPassportCoords();
                var msg = (res.body && res.body.message) ? res.body.message : '位置情報を取得できませんでした。';
                setPassportStatus('error', '<i class="fas fa-circle-xmark"></i><span>' + msg + '</span>');
            }
        })
        .catch(function () {
            setLookupLoading(false);
            clearPassportCoords();
            setPassportStatus('error', '<i class="fas fa-circle-xmark"></i><span>通信に失敗しました</span>');
        });
    }
    if (lookupBtn) lookupBtn.addEventListener('click', performLookup);
    if (passportInput) {
        passportInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); performLookup(); }
        });
        // 入力が変わったら過去に解決した座標は破棄（次回再検索を促す）
        passportInput.addEventListener('input', function () {
            if (passportStatus && passportStatus.getAttribute('data-state') === 'resolved') {
                clearPassportCoords();
                var defaultMsg = passportStatus.getAttribute('data-default-message') || '住所・駅名を入れて『検索』を押してください';
                setPassportStatus('idle', '<i class="fas fa-info-circle"></i><span>' + defaultMsg + '</span>');
            }
        });
    }

    // ----- パスポート: 入力に応じて候補をサジェスト -----
    var suggestListEl = document.getElementById('search-location-suggest-list');
    var suggestUrl = @json(route('api.geocoding.suggest'));
    var suggestSeq = 0;
    var suggestDebounceTimer = null;

    function escapeHtml(s) {
        return String(s).replace(/[<>&"]/g, function (c) {
            return ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' })[c];
        });
    }
    function closeSuggest() {
        if (!suggestListEl) return;
        suggestListEl.hidden = true;
        suggestListEl.innerHTML = '';
        if (passportInput) passportInput.setAttribute('aria-expanded', 'false');
    }
    function renderSuggest(candidates) {
        if (!suggestListEl) return;
        suggestListEl.innerHTML = '';
        if (!candidates || candidates.length === 0) {
            var empty = document.createElement('li');
            empty.className = 'search-location-suggest-empty';
            empty.textContent = '候補が見つかりません';
            suggestListEl.appendChild(empty);
        } else {
            candidates.forEach(function (c) {
                var li = document.createElement('li');
                li.className = 'search-location-suggest-item';
                li.setAttribute('role', 'option');
                li.setAttribute('data-lat', String(c.latitude));
                li.setAttribute('data-lng', String(c.longitude));
                li.setAttribute('data-label', c.label);
                li.innerHTML = '<i class="fas fa-map-marker-alt"></i><span>' + escapeHtml(c.label) + '</span>';
                suggestListEl.appendChild(li);
            });
        }
        suggestListEl.hidden = false;
        if (passportInput) passportInput.setAttribute('aria-expanded', 'true');
    }
    function fetchSuggest(q) {
        if (!suggestUrl) return;
        var seq = ++suggestSeq;
        var url = suggestUrl + (suggestUrl.indexOf('?') >= 0 ? '&' : '?') + 'q=' + encodeURIComponent(q);
        fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (json) {
            if (seq !== suggestSeq) return; // 最後の入力結果のみ採用
            if (!json) { closeSuggest(); return; }
            renderSuggest(json.candidates || []);
        })
        .catch(function () { closeSuggest(); });
    }
    if (passportInput && suggestListEl) {
        passportInput.addEventListener('input', function () {
            var q = (passportInput.value || '').trim();
            clearTimeout(suggestDebounceTimer);
            if (q.length < 2) { closeSuggest(); return; }
            suggestDebounceTimer = setTimeout(function () { fetchSuggest(q); }, 220);
        });
        passportInput.addEventListener('focus', function () {
            var q = (passportInput.value || '').trim();
            if (q.length >= 2) fetchSuggest(q);
        });
        passportInput.addEventListener('blur', function () {
            // クリックを拾うため少し遅延
            setTimeout(closeSuggest, 150);
        });
        // 候補クリックで入力欄＋緯度経度を確定
        suggestListEl.addEventListener('mousedown', function (e) {
            var li = e.target.closest && e.target.closest('.search-location-suggest-item');
            if (!li) return;
            e.preventDefault();
            var label = li.getAttribute('data-label') || '';
            var lat = li.getAttribute('data-lat') || '';
            var lng = li.getAttribute('data-lng') || '';
            passportInput.value = label;
            if (passportLatEl) passportLatEl.value = lat;
            if (passportLngEl) passportLngEl.value = lng;
            setPassportStatus('resolved',
                '<i class="fas fa-circle-check"></i><span>解決済み: <strong>' + escapeHtml(label) +
                '</strong>（' + parseFloat(lat).toFixed(4) + ', ' + parseFloat(lng).toFixed(4) + '）</span>'
            );
            closeSuggest();
        });
    }

    // 現在地取得
    function setCurrentLoading(isLoading) {
        if (!currentBtn) return;
        currentBtn.disabled = !!isLoading;
        currentBtn.classList.toggle('is-loading', !!isLoading);
        if (currentBtnLabel) currentBtnLabel.textContent = isLoading ? '取得中...' : '最新の現在地を取得する';
    }
    if (currentBtn) {
        currentBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                feedback.hidden = false;
                feedback.className = 'search-location-feedback is-error';
                feedback.textContent = 'この端末では位置情報を取得できません。';
                return;
            }
            setCurrentLoading(true);
            navigator.geolocation.getCurrentPosition(function (pos) {
                setCurrentLoading(false);
                if (currentLatEl) currentLatEl.value = pos.coords.latitude.toFixed(7);
                if (currentLngEl) currentLngEl.value = pos.coords.longitude.toFixed(7);
                if (currentLabelEl) currentLabelEl.hidden = false;
                if (currentCheck) currentCheck.hidden = false;
            }, function () {
                setCurrentLoading(false);
                feedback.hidden = false;
                feedback.className = 'search-location-feedback is-error';
                feedback.textContent = '位置情報の取得に失敗しました。ブラウザの権限を確認してください。';
            }, { enableHighAccuracy: true, maximumAge: 60000, timeout: 8000 });
        });
    }

    // 保存後にトリガーボタンの文言（mode／指定地名／半径）を更新するヘルパー
    var triggerSubEl = trigger.querySelector('.search-location-trigger__sub');
    function formatTriggerSub(settings) {
        if (!settings) return '未設定';
        var parts = [];
        var mode = settings.mode || '';
        if (mode === 'profile') parts.push('登録住所を基準');
        else if (mode === 'passport') parts.push('指定地：' + (settings.passport_label || settings.passport_address || '未設定'));
        else if (mode === 'current') parts.push('現在地を基準');
        else parts.push('未設定');
        var km = parseInt(settings.max_distance_km || 0, 10);
        if (km > 0) {
            var label = '';
            sliderMarks.forEach(function (m) { if (m.value === km) label = m.label; });
            if (!label) {
                // 直近の目盛にスナップ
                var best = sliderMarks[0]; var bestDelta = Math.abs(best.value - km);
                sliderMarks.forEach(function (m) {
                    var d = Math.abs(m.value - km);
                    if (d < bestDelta) { best = m; bestDelta = d; }
                });
                if (km >= 40) best = sliderMarks[sliderMarks.length - 1];
                label = best.label;
            }
            parts.push(label);
        }
        return parts.join(' ／ ');
    }

    // 保存
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // 指定地モード：座標未解決なら自動で検索を促す
        var checkedMode = (form.querySelector('input[name="mode"]:checked') || {}).value || '';
        if (checkedMode === 'passport') {
            var lat = (passportLatEl && passportLatEl.value) || '';
            var lng = (passportLngEl && passportLngEl.value) || '';
            var addr = (passportInput && passportInput.value || '').trim();
            if (addr === '') {
                setPassportStatus('error', '<i class="fas fa-circle-exclamation"></i><span>住所または駅名を入力してください</span>');
                return;
            }
            if (!lat || !lng) {
                // 自動的に検索 → 成功したらサーバ送信は次回（明示再保存）に任せる：UX 一貫のためここは検索のみ実行
                performLookup();
                return;
            }
        }

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
                // トリガーボタン表示を即時反映（ページリロード不要）
                if (triggerSubEl) {
                    triggerSubEl.textContent = formatTriggerSub(res.body.settings || null);
                }
                setTimeout(closeDialog, 800);
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

    syncSlider();
})();
</script>
@endpush
