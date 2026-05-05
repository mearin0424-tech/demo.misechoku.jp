@extends('layouts.app')

@section('title', 'プロフィール編集')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<style>
    /* Shop Information / 求人票系と揃えた店舗プロフィール編集 */
    .shop-profile-edit {
        --spe-bg: #050505;
        --spe-panel: #0a0a0a;
        --spe-field: #110f0d;
        --spe-border: #2a2015;
        --spe-border-focus: rgba(212, 175, 55, 0.5);
        --spe-gold: #d4af37;
        --spe-muted: #71717a;
        --spe-hint: #52525b;
        background: var(--spe-bg);
        min-height: 100%;
        margin: 0 calc(-1 * var(--content-padding-x, 16px));
        padding-bottom: calc(var(--footer-height, 75px) + 88px + env(safe-area-inset-bottom, 0px));
    }

    .shop-profile-edit__shell {
        max-width: 28rem;
        margin: 0 auto;
        min-height: 100%;
        background: var(--spe-panel);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .shop-profile-edit__top {
        position: sticky;
        top: 0;
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        background: rgba(10, 10, 10, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid #1f1a14;
    }

    .shop-profile-edit__back {
        color: #a1a1aa;
        padding: 4px;
        margin-left: -4px;
        text-decoration: none;
        font-size: 1.25rem;
        line-height: 1;
        transition: color 0.15s ease;
    }
    .shop-profile-edit__back:hover {
        color: var(--spe-gold);
    }

    .shop-profile-edit__title-block {
        text-align: center;
        flex: 1;
        min-width: 0;
    }
    .shop-profile-edit__title-en {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: 0.2em;
        font-family: var(--font-serif, "Shippori Mincho", serif);
    }
    .shop-profile-edit__title-sub {
        margin: 2px 0 0;
        font-size: 9px;
        font-weight: 700;
        color: var(--spe-gold);
        letter-spacing: 0.06em;
    }

    .shop-profile-edit__spacer {
        width: 2rem;
        flex-shrink: 0;
    }

    .shop-profile-edit__flash {
        margin: 0 16px 12px;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #bbf7d0;
        background: rgba(34, 197, 94, 0.12);
        border: 1px solid rgba(34, 197, 94, 0.25);
    }

    .shop-profile-edit__errors {
        margin: 0 16px 12px;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 0.75rem;
        color: #fecaca;
        background: rgba(127, 29, 29, 0.25);
        border: 1px solid rgba(248, 113, 113, 0.35);
    }
    .shop-profile-edit__errors ul {
        margin: 6px 0 0 1.1em;
        padding: 0;
    }

    .shop-profile-edit__form {
        padding: 20px 20px 8px;
        display: flex;
        flex-direction: column;
        gap: 40px;
    }

    .shop-profile-edit__section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 20px;
        padding-bottom: 8px;
        border-bottom: 1px solid #1f1a14;
        font-size: 0.875rem;
        font-style: italic;
        font-family: var(--font-serif, "Shippori Mincho", serif);
        font-weight: 600;
        color: rgba(161, 161, 170, 0.95);
        letter-spacing: 0.08em;
    }
    .shop-profile-edit__section-title i {
        font-size: 0.9rem;
        color: #52525b;
        font-style: normal;
    }

    .shop-profile-edit__field {
        margin-bottom: 22px;
    }
    .shop-profile-edit__field:last-child {
        margin-bottom: 0;
    }

    .shop-profile-edit__label {
        display: block;
        font-size: 10px;
        font-weight: 800;
        color: var(--spe-gold);
        margin: 0 0 6px 4px;
        letter-spacing: 0.02em;
    }

    .shop-profile-edit__input,
    .shop-profile-edit__textarea,
    .shop-profile-edit__select {
        width: 100%;
        box-sizing: border-box;
        background: var(--spe-field);
        border: 1px solid var(--spe-border);
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 0.875rem;
        color: #fafafa;
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .shop-profile-edit__input::placeholder,
    .shop-profile-edit__textarea::placeholder {
        color: #52525b;
    }
    .shop-profile-edit__input:focus,
    .shop-profile-edit__textarea:focus,
    .shop-profile-edit__select:focus {
        outline: none;
        border-color: var(--spe-border-focus);
        background: #161311;
    }
    .shop-profile-edit__textarea {
        resize: vertical;
        min-height: 140px;
        line-height: 1.6;
    }

    .shop-profile-edit__input--mono {
        font-family: ui-monospace, monospace;
    }

    .shop-profile-edit__hint {
        margin: 6px 0 0 4px;
        font-size: 10px;
        line-height: 1.5;
        color: var(--spe-hint);
    }

    .shop-profile-edit__grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .shop-profile-edit__ref-table-wrap {
        margin: 0 0 16px;
        padding: 12px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid #1f1a14;
        overflow-x: auto;
    }
    .shop-profile-edit__ref-title {
        margin: 0 0 8px;
        font-size: 10px;
        font-weight: 800;
        color: var(--spe-hint);
        letter-spacing: 0.04em;
    }
    .shop-profile-edit__ref-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
        color: #a1a1aa;
    }
    .shop-profile-edit__ref-table th,
    .shop-profile-edit__ref-table td {
        border: 1px solid #2a2015;
        padding: 8px 6px;
        text-align: left;
        line-height: 1.4;
    }
    .shop-profile-edit__ref-table th {
        background: #141210;
        color: var(--spe-gold);
        font-weight: 800;
    }
    .shop-profile-edit__shift-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        align-items: end;
    }
    @media (max-width: 360px) {
        .shop-profile-edit__shift-grid { grid-template-columns: 1fr; }
    }
    .shop-profile-edit__check-line {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        font-size: 0.75rem;
        color: #a1a1aa;
        cursor: pointer;
    }
    .shop-profile-edit__check-line input { width: auto; accent-color: var(--spe-gold); }
    .shop-profile-edit__station-list { display: flex; flex-direction: column; gap: 10px; }
    .shop-profile-edit__station-add {
        align-self: flex-start;
        margin-top: 4px;
        padding: 8px 12px;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--spe-gold);
        background: transparent;
        border: 1px dashed rgba(212, 175, 55, 0.35);
        border-radius: 8px;
        cursor: pointer;
    }

    .shop-profile-edit__select-wrap {
        position: relative;
    }
    .shop-profile-edit__select-wrap select {
        appearance: none;
        -webkit-appearance: none;
        padding-right: 2.25rem;
    }
    .shop-profile-edit__select-wrap::after {
        content: '▼';
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        font-size: 10px;
        color: var(--spe-muted);
    }

    .shop-profile-edit__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .shop-profile-edit__chip {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .shop-profile-edit__chip input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .shop-profile-edit__chip span {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 6px 12px;
        border-radius: 999px;
        border: 1px solid var(--spe-border);
        background: #141210;
        color: #a1a1aa;
        font-size: 0.75rem;
        font-weight: 600;
        transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }
    .shop-profile-edit__chip:hover span {
        border-color: #3a2a18;
    }
    .shop-profile-edit__chip input:checked + span {
        border-color: var(--spe-gold);
        background: #2a2210;
        color: var(--spe-gold);
        box-shadow: 0 0 10px rgba(212, 175, 55, 0.1);
    }
    .shop-profile-edit__chip input:focus-visible + span {
        outline: 2px solid rgba(212, 175, 55, 0.45);
        outline-offset: 2px;
    }

    .shop-profile-edit__actions {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--footer-height, 75px);
        z-index: 999;
        display: flex;
        justify-content: center;
        padding: 16px;
        padding-left: max(16px, env(safe-area-inset-left, 0px));
        padding-right: max(16px, env(safe-area-inset-right, 0px));
        padding-bottom: calc(16px + env(safe-area-inset-bottom, 0px));
        background: rgba(10, 10, 10, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-top: 1px solid #1f1a14;
        box-sizing: border-box;
    }
    .shop-profile-edit__actions-inner {
        display: flex;
        align-items: stretch;
        gap: 12px;
        width: 100%;
        max-width: 28rem;
    }

    .shop-profile-edit__cancel {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 700;
        color: #a1a1aa;
        text-decoration: none;
        border: none;
        background: transparent;
        cursor: pointer;
        transition: color 0.15s ease, background 0.15s ease;
    }
    .shop-profile-edit__cancel:hover {
        color: #fff;
        background: #18181b;
    }

    .shop-profile-edit__submit {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 800;
        cursor: pointer;
        color: #141210;
        background: linear-gradient(to right, #d4af37, #b8942b);
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.15);
        transition: opacity 0.15s ease, transform 0.15s ease;
    }
    .shop-profile-edit__submit:hover {
        opacity: 0.92;
    }
    .shop-profile-edit__submit:active {
        transform: scale(0.99);
    }
</style>
@endpush

@push('scripts')
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var lastCb = document.querySelector('.js-biz-close-last');
    var closeInp = document.querySelector('.js-biz-close-time');
    if (lastCb && closeInp) {
        function syncBizClose() {
            closeInp.disabled = lastCb.checked;
            if (lastCb.checked) closeInp.value = '';
        }
        lastCb.addEventListener('change', syncBizClose);
        syncBizClose();
    }
    var form = document.getElementById('shop-profile-edit-form');
    if (form && closeInp) {
        form.addEventListener('submit', function () {
            if (closeInp.disabled) {
                closeInp.disabled = false;
                closeInp.value = '';
            }
        });
    }
    var stAdd = document.getElementById('shop-station-add');
    var stList = document.getElementById('shop-stations-list');
    if (stAdd && stList) {
        stAdd.addEventListener('click', function () {
            var n = stList.querySelectorAll('input[name="stations[]"]').length + 1;
            var wrap = document.createElement('div');
            wrap.className = 'shop-profile-edit__field';
            wrap.style.marginBottom = '10px';
            wrap.innerHTML =
                '<label class="shop-profile-edit__label" for="station-new-' + n + '">最寄り ' + n + '</label>' +
                '<input id="station-new-' + n + '" type="text" name="stations[]" class="shop-profile-edit__input" placeholder="例：六本木駅 徒歩3分">';
            stList.appendChild(wrap);
        });
    }
});
</script>
@endpush

@section('content')
<div class="shop-profile-edit animate-fadeIn">
    <div class="shop-profile-edit__shell">
        <header class="shop-profile-edit__top">
            <a href="{{ route('shop.mypage.index') }}" class="shop-profile-edit__back" aria-label="戻る">
                <i class="fas fa-chevron-left"></i>
            </a>
            <div class="shop-profile-edit__title-block">
                <h1 class="shop-profile-edit__title-en">EDIT PROFILE</h1>
                <p class="shop-profile-edit__title-sub">Shop Information</p>
            </div>
            <div class="shop-profile-edit__spacer" aria-hidden="true"></div>
        </header>

        @if(session('message'))
            <p class="shop-profile-edit__flash" role="status">{{ session('message') }}</p>
        @endif

        @if($errors->any())
            <div class="shop-profile-edit__errors" role="alert">
                入力内容を確認してください。
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="shop-profile-edit-form" action="{{ route('shop.profile.store.update') }}" method="POST" class="h-adr shop-profile-edit__form">
            @csrf
            <span class="p-country-name" style="display:none;">Japan</span>

            <section aria-labelledby="spe-sec-basic">
                <h2 id="spe-sec-basic" class="shop-profile-edit__section-title">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    Basic Information
                </h2>

                <div class="shop-profile-edit__field">
                    <label class="shop-profile-edit__label" for="shop_name">店舗名</label>
                    <input
                        id="shop_name"
                        type="text"
                        name="shop_name"
                        class="shop-profile-edit__input"
                        value="{{ old('shop_name', $shopData['shop_name']) }}"
                        placeholder="店舗名を入力"
                        autocomplete="organization"
                        required
                    >
                </div>

                <p class="shop-profile-edit__hint" style="margin:0 0 16px;">店舗の「ひとこと」はマイページから編集できます。</p>

                <div class="shop-profile-edit__field">
                    <span class="shop-profile-edit__label">業種</span>
                    <div class="shop-profile-edit__chips" role="radiogroup" aria-label="業種（1つのみ）">
                        @foreach(($masters['industries'] ?? []) as $industry)
                            <label class="shop-profile-edit__chip">
                                <input
                                    type="radio"
                                    name="industry_id"
                                    value="{{ $industry->id }}"
                                    {{ (int) old('industry_id', $shopData['industry_id'] ?? 0) === (int) $industry->id ? 'checked' : '' }}
                                >
                                <span>{{ $industry->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="shop-profile-edit__hint">※1つだけ選択できます。未選択でも保存できます。</p>
                </div>
            </section>

            <section aria-labelledby="spe-sec-loc">
                <h2 id="spe-sec-loc" class="shop-profile-edit__section-title">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    Location
                </h2>

                <div class="shop-profile-edit__field">
                    <label class="shop-profile-edit__label" for="zip">郵便番号</label>
                    <input
                        id="zip"
                        type="text"
                        name="zip"
                        class="shop-profile-edit__input shop-profile-edit__input--mono p-postal-code"
                        data-postal-code
                        maxlength="8"
                        pattern="[0-9-]*"
                        value="{{ old('zip', $shopData['zip']) }}"
                        inputmode="numeric"
                        autocomplete="postal-code"
                        placeholder="例：106-0032"
                    >
                    <p class="shop-profile-edit__hint">※ハイフン有り・無しどちらでも入力でき、住所が自動補完されます。</p>
                </div>

                <div class="shop-profile-edit__grid-2">
                    <div class="shop-profile-edit__field">
                        <label class="shop-profile-edit__label" for="pref">都道府県</label>
                        <div class="shop-profile-edit__select-wrap">
                            <select id="pref" name="pref" class="shop-profile-edit__select p-region" required>
                                <option value="">選択してください</option>
                                @foreach ($prefOptions as $pref)
                                    <option value="{{ $pref }}" {{ old('pref', $shopData['pref']) === $pref ? 'selected' : '' }}>{{ $pref }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="shop-profile-edit__field">
                        <label class="shop-profile-edit__label" for="city">市区町村</label>
                        <input
                            id="city"
                            type="text"
                            name="city"
                            class="shop-profile-edit__input p-locality"
                            value="{{ old('city', $shopData['city']) }}"
                            autocomplete="address-level2"
                            placeholder="例：港区六本木"
                        >
                    </div>
                </div>

                @if(!empty($hasProfileAddr))
                    <div class="shop-profile-edit__field">
                        <label class="shop-profile-edit__label" for="addr">番地・丁目</label>
                        <input
                            id="addr"
                            type="text"
                            name="addr"
                            class="shop-profile-edit__input p-street-address"
                            value="{{ old('addr', $shopData['addr'] ?? '') }}"
                            autocomplete="street-address"
                            placeholder="例：7-12-34"
                        >
                    </div>
                    <div class="shop-profile-edit__field">
                        <label class="shop-profile-edit__label" for="building">建物名・部屋番号</label>
                        <input
                            id="building"
                            type="text"
                            name="building"
                            class="shop-profile-edit__input"
                            value="{{ old('building', $shopData['building'] ?? '') }}"
                            placeholder="例：〇〇ビル 2F"
                        >
                    </div>
                @else
                    <div class="shop-profile-edit__field">
                        <label class="shop-profile-edit__label" for="addr1">以降の住所・ビル名</label>
                        <input
                            id="addr1"
                            type="text"
                            name="addr1"
                            class="shop-profile-edit__input p-street-address"
                            value="{{ old('addr1', $shopData['addr1']) }}"
                            autocomplete="address-line1"
                            placeholder="例：7-12-34 〇〇ビル 2F"
                        >
                    </div>
                @endif

                @if(!empty($hasProfileTel))
                    <div class="shop-profile-edit__field">
                        <label class="shop-profile-edit__label" for="tel">電話番号</label>
                        <input
                            id="tel"
                            type="tel"
                            name="tel"
                            class="shop-profile-edit__input shop-profile-edit__input--mono"
                            value="{{ old('tel', $shopData['tel'] ?? '') }}"
                            autocomplete="tel"
                            placeholder="例：03-1234-5678"
                        >
                    </div>
                @endif
            </section>

            @if(!empty($hasProfileBusinessHours))
            <section aria-labelledby="spe-sec-hours">
                <h2 id="spe-sec-hours" class="shop-profile-edit__section-title">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    Business Hours（店舗の営業時間）
                </h2>
                <p class="shop-profile-edit__hint" style="margin-top:0;">求人のシフト時間とは別に、店舗の営業時間を登録できます（未設定の場合は表示されません）。</p>

                <div class="shop-profile-edit__ref-table-wrap">
                    <p class="shop-profile-edit__ref-title">登録イメージ（DBの保存値）</p>
                    <table class="shop-profile-edit__ref-table">
                        <thead>
                            <tr>
                                <th>パターン</th>
                                <th>open_time</th>
                                <th>close_is_last</th>
                                <th>close_time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>20:00～翌4:00</td>
                                <td>20:00:00</td>
                                <td>0</td>
                                <td>04:00:00</td>
                            </tr>
                            <tr>
                                <td>20:00～LAST</td>
                                <td>20:00:00</td>
                                <td>1</td>
                                <td>NULL</td>
                            </tr>
                            <tr>
                                <td>未設定</td>
                                <td>NULL</td>
                                <td>0</td>
                                <td>NULL</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @php
                    $bizOpen = old('business_open', $shopData['business_open'] ?? '');
                    $bizClose = old('business_close', $shopData['business_close'] ?? '');
                    $bizLastRaw = old('business_close_last', !empty($shopData['business_close_last']) ? '1' : '0');
                    $bizLast = $bizLastRaw === '1' || $bizLastRaw === 1 || $bizLastRaw === true;
                @endphp
                <div class="shop-profile-edit__shift-grid">
                    <div class="shop-profile-edit__field">
                        <label class="shop-profile-edit__label" for="business_open">開店（open_time）</label>
                        <input type="time" step="60" name="business_open" id="business_open" class="shop-profile-edit__input js-biz-open"
                               value="{{ $bizOpen }}">
                    </div>
                    <div class="shop-profile-edit__field">
                        <label class="shop-profile-edit__label" for="business_close">閉店（close_time）</label>
                        <input type="time" step="60" name="business_close" id="business_close" class="shop-profile-edit__input js-biz-close-time"
                               value="{{ $bizClose }}" @if($bizLast) disabled @endif>
                        <label class="shop-profile-edit__check-line">
                            <input type="checkbox" name="business_close_last" value="1" class="js-biz-close-last" id="business_close_last" {{ $bizLast ? 'checked' : '' }}>
                            <span>LAST（終電まで）</span>
                        </label>
                    </div>
                </div>
                <p class="shop-profile-edit__hint">※閉店が翌日にまたがる場合は、翌日の時刻で入力してください（例：開店 20:00／閉店 04:00）。「LAST」のときは閉店時刻は保存されません。</p>
            </section>
            @endif

            @if(!empty($hasShopStationsTable))
            <section aria-labelledby="spe-sec-st">
                <h2 id="spe-sec-st" class="shop-profile-edit__section-title">
                    <i class="fas fa-train" aria-hidden="true"></i>
                    最寄り駅
                </h2>
                <p class="shop-profile-edit__hint" style="margin-top:0;">複数行で登録できます（例：六本木駅 徒歩3分）。</p>
                <div class="shop-profile-edit__station-list" id="shop-stations-list">
                    @foreach(old('stations', $shopData['stations'] ?? ['']) as $i => $stLine)
                        <div class="shop-profile-edit__field" style="margin-bottom:10px;">
                            <label class="shop-profile-edit__label" for="station-{{ $i }}">最寄り {{ $i + 1 }}</label>
                            <input id="station-{{ $i }}" type="text" name="stations[]" class="shop-profile-edit__input"
                                   value="{{ $stLine }}" placeholder="例：六本木駅 徒歩3分">
                        </div>
                    @endforeach
                </div>
                <button type="button" class="shop-profile-edit__station-add" id="shop-station-add" aria-label="最寄り駅の行を追加">＋ 行を追加</button>
            </section>
            @endif


            <section aria-labelledby="spe-sec-tags">
                <h2 id="spe-sec-tags" class="shop-profile-edit__section-title">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    Shop Tags
                </h2>

                <div class="shop-profile-edit__field">
                    <span class="shop-profile-edit__label">店内の雰囲気・客層</span>
                    <div class="shop-profile-edit__chips">
                        @foreach(($masters['atmosphere'] ?? []) as $tag)
                            <label class="shop-profile-edit__chip">
                                <input type="checkbox" name="atmosphere_tag_ids[]" value="{{ $tag->id }}"
                                    {{ in_array((int) $tag->id, old('atmosphere_tag_ids', $shopData['atmosphere_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="shop-profile-edit__hint">来店客やお店のムードを表すタグを選択してください。</p>
                </div>

                <div class="shop-profile-edit__field">
                    <span class="shop-profile-edit__label">設備・アクセス</span>
                    <div class="shop-profile-edit__chips">
                        @foreach(($masters['facility'] ?? []) as $tag)
                            <label class="shop-profile-edit__chip">
                                <input type="checkbox" name="facility_tag_ids[]" value="{{ $tag->id }}"
                                    {{ in_array((int) $tag->id, old('facility_tag_ids', $shopData['facility_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="shop-profile-edit__hint">店舗設備や駅からのアクセス等を表すタグを選択してください。</p>
                </div>
            </section>
        </form>

        <div class="shop-profile-edit__actions">
            <div class="shop-profile-edit__actions-inner">
                <a href="{{ route('shop.mypage.index') }}" class="shop-profile-edit__cancel">キャンセル</a>
                <button type="submit" form="shop-profile-edit-form" class="shop-profile-edit__submit">
                    <i class="fas fa-check" aria-hidden="true"></i>
                    プロフィールを更新する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
