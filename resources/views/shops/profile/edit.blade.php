@extends('layouts.app-v2')

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
        max-width: 100%;
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
        font-family: var(--font-sans);
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
        font-family: var(--font-sans);
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
    .shop-profile-edit__station-row {
        display: flex;
        align-items: stretch;
        gap: 8px;
        padding: 8px;
        border: 1px solid var(--color-border, rgba(197,160,89,0.22));
        border-radius: 10px;
        background: rgba(255,255,255,0.02);
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .shop-profile-edit__station-row:first-child {
        border-color: var(--gold, #c5a059);
        background: rgba(197,160,89,0.06);
    }
    .shop-profile-edit__station-row.is-dragging { opacity: 0.55; }
    .shop-profile-edit__station-row.is-ghost { background: rgba(197,160,89,0.18); }
    .shop-profile-edit__station-drag {
        flex: 0 0 auto;
        width: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 0;
        color: var(--color-text-muted, rgba(216,201,168,0.65));
        cursor: grab;
        touch-action: none;
        border-radius: 6px;
    }
    .shop-profile-edit__station-drag:hover { color: var(--gold, #c5a059); background: rgba(197,160,89,0.10); }
    .shop-profile-edit__station-drag:active { cursor: grabbing; }
    .shop-profile-edit__station-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
    .shop-profile-edit__station-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.68rem;
        font-weight: 700;
        color: var(--color-text-muted, rgba(216,201,168,0.65));
        letter-spacing: 0.04em;
    }
    .shop-profile-edit__station-row:first-child .shop-profile-edit__station-badge { color: var(--gold, #c5a059); }
    .shop-profile-edit__station-badge .is-main-pill {
        display: inline-flex;
        align-items: center;
        padding: 1px 8px;
        border-radius: 999px;
        font-size: 0.62rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--gold-light, #e8cd8a), var(--gold, #c5a059));
        color: #1a1206;
    }
    .shop-profile-edit__station-row:not(:first-child) .is-main-pill { display: none; }
    .shop-profile-edit__station-set-main {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        padding: 5px 10px;
        font-size: 0.7rem;
        font-weight: 700;
        background: transparent;
        border: 1px solid var(--color-border, rgba(197,160,89,0.22));
        border-radius: 999px;
        color: var(--color-text-muted, rgba(216,201,168,0.65));
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }
    .shop-profile-edit__station-set-main:hover {
        background: rgba(197,160,89,0.10);
        color: var(--gold-light, #e8cd8a);
        border-color: var(--color-border-strong, rgba(197,160,89,0.4));
    }
    .shop-profile-edit__station-set-main i { font-size: 0.66rem; }
    /* メイン（先頭）の行は「設定済み」状態にして無効化 */
    .shop-profile-edit__station-row:first-child .shop-profile-edit__station-set-main {
        background: linear-gradient(135deg, var(--gold-light, #e8cd8a), var(--gold, #c5a059));
        border-color: var(--gold, #c5a059);
        color: #1a1206;
        cursor: default;
        pointer-events: none;
    }
    .shop-profile-edit__station-row:first-child .shop-profile-edit__station-set-main-label::before {
        content: '✓ ';
    }
    .shop-profile-edit__station-row:first-child .shop-profile-edit__station-set-main-label {
        font-weight: 800;
    }
    .shop-profile-edit__station-remove {
        flex: 0 0 auto;
        width: 32px;
        background: transparent;
        border: 0;
        color: var(--color-text-muted, rgba(216,201,168,0.65));
        cursor: pointer;
        border-radius: 6px;
    }
    .shop-profile-edit__station-remove:hover { color: var(--color-danger, #fca5a5); background: rgba(248,113,113,0.10); }
    .shop-profile-edit__station-info {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin: 6px 0 10px;
        padding: 8px 10px;
        border-radius: 8px;
        background: rgba(197,160,89,0.06);
        border: 1px solid rgba(197,160,89,0.22);
        font-size: 0.72rem;
        line-height: 1.55;
        color: var(--color-text, #d8c9a8);
    }
    .shop-profile-edit__station-info i { color: var(--gold, #c5a059); margin-top: 2px; }
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
    .shop-profile-edit__station-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .shop-profile-edit__section-title-actions {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
    }
    .shop-profile-edit__section-title-actions .shop-profile-edit__station-add {
        margin-top: 0;
        padding: 6px 10px;
        font-size: 0.66rem;
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
        max-width: var(--max-content-width);
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
    var suggestUrl = @json(route('shop.profile.suggest-stations'));
    var prefInput = document.getElementById('pref');
    var cityInput = document.getElementById('city');
    var addrInput = document.getElementById('addr');
    var buildingInput = document.getElementById('building');
    var addr1Input = document.getElementById('addr1');
    var stationFetchBtn = document.getElementById('shop-station-fetch');
    var suggestSeq = 0;
    function collectAddressPayload() {
        return {
            pref: prefInput ? prefInput.value : '',
            city: cityInput ? cityInput.value : '',
            addr: addrInput ? addrInput.value : '',
            building: buildingInput ? buildingInput.value : '',
            addr1: addr1Input ? addr1Input.value : ''
        };
    }
    function buildStationRow(value, idx) {
        var row = document.createElement('div');
        row.className = 'shop-profile-edit__station-row';
        row.setAttribute('data-station-row', '');
        row.innerHTML =
            '<button type="button" class="shop-profile-edit__station-drag" aria-label="並び替え"><i class="fas fa-grip-vertical" aria-hidden="true"></i></button>' +
            '<div class="shop-profile-edit__station-body">' +
                '<span class="shop-profile-edit__station-badge">最寄り <span class="js-station-index">' + (idx + 1) + '</span> <span class="is-main-pill">MAIN</span></span>' +
                '<input type="text" name="stations[]" class="shop-profile-edit__input" placeholder="例：六本木駅 徒歩3分">' +
                '<button type="button" class="shop-profile-edit__station-set-main" data-station-set-main aria-label="この駅をメインに設定">' +
                    '<i class="fas fa-star" aria-hidden="true"></i>' +
                    '<span class="shop-profile-edit__station-set-main-label">メインに設定</span>' +
                '</button>' +
            '</div>' +
            '<button type="button" class="shop-profile-edit__station-remove" aria-label="削除"><i class="fas fa-times" aria-hidden="true"></i></button>';
        var input = row.querySelector('input[name="stations[]"]');
        if (input) input.value = value || '';
        return row;
    }
    function refreshStationIndexes() {
        if (!stList) return;
        var rows = stList.querySelectorAll('[data-station-row]');
        rows.forEach(function (r, i) {
            var idx = r.querySelector('.js-station-index');
            if (idx) idx.textContent = String(i + 1);
        });
    }
    function renderStationRows(lines) {
        if (!stList) return;
        var rows = Array.isArray(lines) ? lines.filter(function (v) { return (v || '').trim() !== ''; }) : [];
        if (rows.length === 0) return;
        stList.innerHTML = '';
        rows.forEach(function (line, i) {
            stList.appendChild(buildStationRow(line, i));
        });
        refreshStationIndexes();
    }
    function suggestStations() {
        if (!stList || !suggestUrl) return Promise.resolve();
        var payload = collectAddressPayload();
        var fullAddress = [payload.pref, payload.city, payload.addr, payload.building, payload.addr1].join('').trim();
        if (fullAddress === '') return Promise.resolve();
        var seq = ++suggestSeq;
        return fetch(suggestUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token())
            },
            body: JSON.stringify(payload)
        })
        .then(function (res) { return res.ok ? res.json() : null; })
        .then(function (json) {
            if (!json || seq !== suggestSeq) return;
            renderStationRows(json.stations || []);
        })
        .catch(function () {});
    }
    if (stationFetchBtn) {
        stationFetchBtn.addEventListener('click', function () {
            if (stationFetchBtn.disabled) return;
            stationFetchBtn.disabled = true;
            stationFetchBtn.textContent = '取得中...';
            Promise.resolve()
                .then(suggestStations)
                .finally(function () {
                    stationFetchBtn.disabled = false;
                    stationFetchBtn.textContent = '住所から取得';
                });
        });
    }

    if (stAdd && stList) {
        stAdd.addEventListener('click', function () {
            var n = stList.querySelectorAll('input[name="stations[]"]').length;
            stList.appendChild(buildStationRow('', n));
            refreshStationIndexes();
        });
    }

    // 削除ボタン＋「メインに設定」ボタン（イベント委譲）
    if (stList) {
        stList.addEventListener('click', function (e) {
            // メインに設定：押下した行を最上段へ
            var mainBtn = e.target.closest && e.target.closest('[data-station-set-main]');
            if (mainBtn) {
                var row = mainBtn.closest('[data-station-row]');
                if (row && row.parentNode === stList && row !== stList.firstElementChild) {
                    stList.insertBefore(row, stList.firstElementChild);
                    refreshStationIndexes();
                }
                return;
            }

            // 削除
            var rmBtn = e.target.closest && e.target.closest('.shop-profile-edit__station-remove');
            if (!rmBtn) return;
            var rmRow = rmBtn.closest('[data-station-row]');
            if (!rmRow) return;
            // 最後の1行は中身を消すだけにする（フォーム送信時に行が消えるとレイアウトが崩れるため）
            var rows = stList.querySelectorAll('[data-station-row]');
            if (rows.length <= 1) {
                var input = rmRow.querySelector('input[name="stations[]"]');
                if (input) input.value = '';
            } else {
                rmRow.remove();
            }
            refreshStationIndexes();
        });
    }

    // SortableJS で並び替えを有効化（一番上が「メイン最寄り駅」として一覧に採用される）
    if (stList && window.Sortable) {
        new Sortable(stList, {
            handle: '.shop-profile-edit__station-drag',
            animation: 150,
            ghostClass: 'is-ghost',
            dragClass: 'is-dragging',
            onEnd: refreshStationIndexes,
        });
    }
    refreshStationIndexes();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>
@endpush

@section('content')
<div class="shop-profile-edit animate-fadeIn">
    <div class="shop-profile-edit__shell">
        <header class="shop-profile-edit__top">
            <div class="shop-profile-edit__title-block">
                <h1 class="shop-profile-edit__title-en">EDIT PROFILE</h1>
                <p class="shop-profile-edit__title-sub">Shop Information</p>
            </div>
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

        <form id="shop-profile-edit-form" action="{{ route('shop.profile.update') }}" method="POST" class="h-adr shop-profile-edit__form">
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
                    <div class="shop-profile-edit__chips">
                        @php
                            $selectedIndustryIds = collect(old('industry_ids', $shopData['industry_ids'] ?? []))
                                ->map(fn ($id) => (int) $id)
                                ->all();
                            $selectedIndustryId = $selectedIndustryIds[0] ?? null;
                        @endphp
                        @foreach(($masters['industries'] ?? []) as $industry)
                            <label class="shop-profile-edit__chip">
                                <input type="radio" name="industry_ids[]" value="{{ $industry->id }}"
                                    {{ (int) $industry->id === (int) $selectedIndustryId ? 'checked' : '' }}>
                                <span>{{ $industry->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="shop-profile-edit__hint">※1 つだけ選択してください。朝キャバとキャバクラ等、両方の営業形態を持つ店舗はメインの業種を 1 つ選んでください。</p>
                </div>

                <div class="shop-profile-edit__field">
                    <label class="shop-profile-edit__label" for="industry_label">業種名（表示用・自由入力）</label>
                    <input
                        id="industry_label"
                        type="text"
                        name="industry_label"
                        class="shop-profile-edit__input"
                        value="{{ old('industry_label', $shopData['industry_label'] ?? '') }}"
                        maxlength="60"
                        placeholder="例：高級ラウンジ／会員制クラブ／カジュアルキャバ等"
                    >
                    <p class="shop-profile-edit__hint">店舗ページや一覧で表示される業種名です。空欄の場合は上で選んだ業種名（例：キャバクラ）が表示されます。検索の絞り込みには上の業種カテゴリが使われます。</p>
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
                    <span class="shop-profile-edit__section-title-actions">
                        <button type="button" class="shop-profile-edit__station-add" id="shop-station-fetch" aria-label="住所から最寄り駅を取得">住所から取得</button>
                    </span>
                </h2>
                <p class="shop-profile-edit__hint" style="margin-top:0;">複数行で登録できます（例：六本木駅 徒歩3分）。</p>
                <p class="shop-profile-edit__station-info">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>各行の「★ メインに設定」を押すか、ハンドル <i class="fas fa-grip-vertical" aria-hidden="true"></i> をドラッグして一番上に持ってきた最寄り駅が、ホーム／検索の一覧に表示されます。</span>
                </p>
                <div class="shop-profile-edit__station-list" id="shop-stations-list">
                    @foreach(old('stations', $shopData['stations'] ?? ['']) as $i => $stLine)
                        <div class="shop-profile-edit__station-row" data-station-row>
                            <button type="button" class="shop-profile-edit__station-drag" aria-label="並び替え"><i class="fas fa-grip-vertical" aria-hidden="true"></i></button>
                            <div class="shop-profile-edit__station-body">
                                <span class="shop-profile-edit__station-badge">
                                    最寄り <span class="js-station-index">{{ $i + 1 }}</span>
                                    <span class="is-main-pill">MAIN</span>
                                </span>
                                <input id="station-{{ $i }}" type="text" name="stations[]" class="shop-profile-edit__input"
                                       value="{{ $stLine }}" placeholder="例：六本木駅 徒歩3分">
                                <button type="button" class="shop-profile-edit__station-set-main" data-station-set-main aria-label="この駅をメインに設定">
                                    <i class="fas fa-star" aria-hidden="true"></i>
                                    <span class="shop-profile-edit__station-set-main-label">メインに設定</span>
                                </button>
                            </div>
                            <button type="button" class="shop-profile-edit__station-remove" aria-label="削除"><i class="fas fa-times" aria-hidden="true"></i></button>
                        </div>
                    @endforeach
                </div>
                <div class="shop-profile-edit__station-actions">
                    <button type="button" class="shop-profile-edit__station-add" id="shop-station-add" aria-label="最寄り駅の行を追加">＋ 行を追加</button>
                </div>
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
