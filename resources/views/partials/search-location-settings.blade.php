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
    $hasProfileLocation = !empty($settings['profile_location']);
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
                <label class="search-location-card {{ $currentMode === 'profile' ? 'is-selected' : '' }} {{ $hasProfileLocation ? '' : 'is-disabled-soft' }}" data-mode-card="profile">
                    <input type="radio" name="mode" value="profile" @checked($currentMode === 'profile')>
                    <span class="search-location-card__row">
                        <i class="fas fa-home search-location-card__icon"></i>
                        <span class="search-location-card__main">
                            <span class="search-location-card__title">登録住所</span>
                            <span class="search-location-card__sub {{ $hasProfileLocation ? '' : 'is-warn' }}">
                                @if($hasProfileLocation)
                                    プロフィールの住所を基準にします
                                @else
                                    プロフィールに住所が登録されていません
                                @endif
                            </span>
                        </span>
                    </span>
                    @unless($hasProfileLocation)
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
                        <span class="search-location-input-wrap">
                            <i class="fas fa-search"></i>
                            <input id="search-location-passport-address"
                                   type="text"
                                   name="passport_address"
                                   class="search-location-input"
                                   maxlength="255"
                                   placeholder="例: 渋谷駅, 新宿区..."
                                   value="{{ $currentPassportAddress }}">
                        </span>
                        <input type="hidden" name="passport_lat" id="search-location-passport-lat" value="{{ $settings['passport_latitude'] ?? '' }}">
                        <input type="hidden" name="passport_lng" id="search-location-passport-lng" value="{{ $settings['passport_longitude'] ?? '' }}">
                        @if($currentPassportLabel !== '' && $currentMode === 'passport')
                            <p class="search-location-current">現在の指定：<strong>{{ $currentPassportLabel }}</strong></p>
                        @endif
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
    border: 1px solid rgba(220, 181, 104, 0.22);
    background: linear-gradient(180deg, rgba(220, 181, 104, 0.06), rgba(255, 255, 255, 0.02));
    color: #f8e9c8;
    cursor: pointer;
    text-align: left;
    transition: border-color 0.15s ease, background 0.15s ease;
}
.search-location-trigger:hover {
    border-color: rgba(220, 181, 104, 0.45);
    background: rgba(220, 181, 104, 0.10);
}
.search-location-trigger__icon {
    width: 36px; height: 36px; flex-shrink: 0;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(220, 181, 104, 0.15);
    color: #dcb568;
}
.search-location-trigger__body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.search-location-trigger__title { font-size: 0.92rem; font-weight: 800; color: #f8e9c8; }
.search-location-trigger__sub {
    font-size: 0.74rem; color: rgba(248, 233, 200, 0.62);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.search-location-trigger__chevron { color: rgba(220, 181, 104, 0.7); }

/* ===== ダイアログ ===== */
.search-location-dialog-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(15, 5, 10, 0.72);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    display: none;
    align-items: flex-end;
    justify-content: center;
    padding: 0;
}
.search-location-dialog-overlay[aria-hidden="false"] { display: flex; }
.search-location-dialog {
    position: relative;
    width: 100%; max-width: 480px;
    max-height: 92vh;
    overflow-y: auto;
    background: #2D1B24;
    color: #fff;
    border: 1px solid #4a2f3e;
    border-radius: 24px 24px 0 0;
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.5);
    padding: 22px 22px calc(22px + env(safe-area-inset-bottom));
    animation: search-location-slide-up 0.22s ease-out;
}
@keyframes search-location-slide-up {
    from { transform: translateY(24px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
@media (min-width: 600px) {
    .search-location-dialog-overlay { align-items: center; padding: 24px; }
    .search-location-dialog { border-radius: 24px; }
}

.search-location-dialog__close {
    position: absolute; top: 14px; right: 14px;
    width: 36px; height: 36px;
    border-radius: 50%;
    border: 1px solid #4a2f3e;
    background: #3a232f;
    color: #f8e9c8;
    cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    z-index: 2;
}
.search-location-dialog__close:hover { background: #4a2f3e; }

.search-location-dialog__head { padding-bottom: 16px; border-bottom: 1px solid rgba(74, 47, 62, 0.6); margin-bottom: 20px; }
.search-location-dialog__title {
    margin: 0 0 8px;
    font-size: 1.05rem; font-weight: 800;
    color: #fff;
    display: flex; align-items: center; gap: 8px;
}
.search-location-dialog__title i { color: #E8C372; font-size: 0.95rem; }
.search-location-dialog__lead {
    margin: 0;
    font-size: 0.82rem; line-height: 1.65;
    color: rgba(255, 255, 255, 0.72);
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
    border: 0; padding: 0; margin: 0 0 24px;
    display: flex; flex-direction: column; gap: 10px;
}
.search-location-card {
    position: relative;
    display: block;
    padding: 14px 16px;
    border-radius: 16px;
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
    background: linear-gradient(135deg, #E8C372, #d4af37);
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
    margin: 14px 0 0 30px;
}
.search-location-card.is-selected .search-location-card__expand { display: block; }

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
.search-location-current strong { color: #ffe2a3; font-weight: 800; }

/* ===== 半径スライダー ===== */
.search-location-radius {
    margin-top: 8px;
    padding-top: 20px;
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
    margin-top: 18px;
    font-size: 1.2rem; font-weight: 800;
    color: #E8C372;
    letter-spacing: 0.04em;
}

/* ===== 保存ボタン / フィードバック ===== */
.search-location-actions { margin-top: 24px; }
.search-location-save-btn {
    width: 100%;
    padding: 14px 20px;
    border-radius: 14px;
    border: 0;
    background: linear-gradient(135deg, #E8C372, #d4af37);
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

    // 保存
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
