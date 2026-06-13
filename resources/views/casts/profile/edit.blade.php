@extends('layouts.app-v2')

@section('title', 'Profile Edit')
@section('header_title', 'Profile Edit')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/profile_edit.css') }}">
<style>
    .input-hint {
        margin-top: 6px;
        font-size: 0.72rem;
        color: #8b8b8b;
        line-height: 1.6;
    }
</style>
@endpush

@push('scripts')
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
@endpush

@section('content')
<div class="cast-edit-page">
    @if(session('message'))
        <p class="profile-edit-flash">{{ session('message') }}</p>
    @endif

    @php
        $selectedIndustryIds = collect(old('industry_ids', $profile['industry_ids'] ?? []))->map(fn ($id) => (int) $id)->all();
        $selectedLookIds = collect(old('look_tag_ids', $profile['look_tag_ids'] ?? []))->map(fn ($id) => (int) $id)->all();
        $selectedPersonalityIds = collect(old('personality_tag_ids', $profile['personality_tag_ids'] ?? []))->map(fn ($id) => (int) $id)->all();
        $selectedNightExp = old('exp', old('night_work_exp', $profile['exp'] ?? ($profile['night_work_exp'] ?? 'none')));
    @endphp

    <form action="{{ route($updateRoute ?? 'cast.profile.update') }}" method="POST" class="cast-edit-form h-adr">
        @csrf
        <span class="p-country-name" style="display:none;">Japan</span>

        <section class="cast-edit-section">
            <h3><i class="far fa-user"></i> 基本情報</h3>
            <div class="field">
                <label>ニックネーム <span class="required">必須</span></label>
                <input type="text" name="nickname" value="{{ old('nickname', $profile['nickname']) }}" class="cast-input">
            </div>
            <div class="field">
                <label>名前</label>
                <input type="text" name="name" value="{{ old('name', $profile['name']) }}" class="cast-input">
            </div>
            <div class="field">
                <label>生年月日 <span class="required">必須</span></label>
                <input type="date" name="birth_date" value="{{ old('birth_date', $profile['birth_date'] ?? '') }}" class="cast-input">
            </div>
            <div class="field">
                <label>郵便番号</label>
                <input type="text" name="zip" value="{{ old('zip', $profile['zip']) }}" class="cast-input p-postal-code" data-postal-code maxlength="8" pattern="[0-9-]*" inputmode="numeric" autocomplete="postal-code">
            </div>
            <div class="two-col">
                <div class="field">
                    <label>都道府県</label>
                    <select name="pref" class="cast-select p-region">
                        <option value="">選択してください</option>
                        @foreach ($prefOptions as $pref)
                            <option value="{{ $pref }}" @selected(old('pref', $profile['pref']) === $pref)>{{ $pref }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>市区町村</label>
                    <input type="text" name="city" value="{{ old('city', $profile['city']) }}" class="cast-input p-locality" autocomplete="address-level2">
                </div>
            </div>
            <div class="field">
                <label>町名・番地</label>
                <input type="text" name="addr1" value="{{ old('addr1', $profile['addr1']) }}" class="cast-input p-street-address" autocomplete="address-line1">
            </div>
        </section>

        <section class="cast-edit-section">
            <h3><i class="far fa-file-alt"></i> 自己PR</h3>
            <div class="field">
                <label>自己PR</label>
                <textarea name="intro" class="cast-input cast-textarea min-h100">{{ old('intro', $profile['intro']) }}</textarea>
            </div>
        </section>

        <section class="cast-edit-section">
            <h3><i class="fas fa-ruler"></i> 体型・ルックス情報</h3>
            <div class="metric-pair">
                <div class="metric-field">
                    <label class="metric-field-label" for="edit-height">身長 <small>cm</small></label>
                    <div class="metric-input-wrap">
                        <input type="number" id="edit-height" name="height" value="{{ old('height', $profile['height']) }}" inputmode="numeric" pattern="[0-9]*" min="130" max="200" placeholder="160">
                        <span class="metric-unit">cm</span>
                    </div>
                </div>
                <div class="metric-field">
                    <label class="metric-field-label" for="edit-weight">体重 <small>kg</small></label>
                    <div class="metric-input-wrap">
                        <input type="number" id="edit-weight" name="weight" value="{{ old('weight', $profile['weight']) }}" inputmode="numeric" pattern="[0-9]*" min="30" max="150" placeholder="48">
                        <span class="metric-unit">kg</span>
                    </div>
                </div>
            </div>
            <div class="bwh-group">
                <span class="metric-field-label">3サイズ <small>cm</small></span>
                <div class="bwh-row">
                    <label class="bwh-field" aria-label="バスト">
                        <span class="bwh-letter">B</span>
                        <input type="number" name="bust" value="{{ old('bust', $profile['bust']) }}" inputmode="numeric" pattern="[0-9]*" min="50" max="120" placeholder="--">
                    </label>
                    <label class="bwh-field" aria-label="ウエスト">
                        <span class="bwh-letter">W</span>
                        <input type="number" name="waist" value="{{ old('waist', $profile['waist']) }}" inputmode="numeric" pattern="[0-9]*" min="40" max="120" placeholder="--">
                    </label>
                    <label class="bwh-field" aria-label="ヒップ">
                        <span class="bwh-letter">H</span>
                        <input type="number" name="hip" value="{{ old('hip', $profile['hip']) }}" inputmode="numeric" pattern="[0-9]*" min="50" max="120" placeholder="--">
                    </label>
                </div>
            </div>

            <div class="field">
                <label>ルックス</label>
                <div class="tag-grid">
                    @foreach(($masters['looks'] ?? []) as $look)
                        <label class="tag-chip tag-looks">
                            <input type="checkbox" name="look_tag_ids[]" value="{{ $look->id }}" @checked(in_array((int)$look->id, $selectedLookIds, true))>
                            <span>{{ $look->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="field">
                <label>性格・内面</label>
                <div class="tag-grid">
                    @foreach(($masters['personalities'] ?? []) as $personality)
                        <label class="tag-chip tag-personality">
                            <input type="checkbox" name="personality_tag_ids[]" value="{{ $personality->id }}" @checked(in_array((int)$personality->id, $selectedPersonalityIds, true))>
                            <span>{{ $personality->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="cast-edit-section">
            <h3><i class="fas fa-briefcase"></i> 希望の働き方</h3>
            <div class="field">
                <label>希望職種</label>
                <div class="tag-grid">
                    @foreach(($masters['industries'] ?? []) as $industry)
                        <label class="tag-chip tag-looks">
                            <input type="checkbox" name="industry_ids[]" value="{{ $industry->id }}" @checked(in_array((int)$industry->id, $selectedIndustryIds, true))>
                            <span>{{ $industry->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="field">
                <label>現職業</label>
                <input type="text" name="profession" value="{{ old('profession', old('current_job', $profile['profession'] ?? ($profile['current_job'] ?? ''))) }}" class="cast-input">
            </div>
            <div class="field">
                <label>ナイトワーク経験</label>
                <div class="radio-like-row">
                    <label class="radio-like">
                        <input type="radio" name="exp" value="none" @checked($selectedNightExp === 'none')>
                        <span class="dot"></span><span>無し</span>
                    </label>
                    <label class="radio-like">
                        <input type="radio" name="exp" value="yes" @checked($selectedNightExp === 'yes')>
                        <span class="dot"></span><span>有り</span>
                    </label>
                </div>
            </div>
        </section>

        <div class="save-bar">
            <button type="submit" class="save-btn">
                <i class="far fa-save"></i><span>保存する</span>
            </button>
        </div>
    </form>
</div>

<style>
.cast-edit-page { padding: 12px 0 0; }
.cast-edit-form { position: relative; padding: 0 0 108px; }
.cast-edit-section { margin: 0 0 14px; padding: 14px; border-radius: 14px; background: rgba(26, 10, 14, .8); border: 1px solid rgba(74, 29, 40, .4); }
.cast-edit-section h3 { margin: 0 0 12px; font-size: 14px; color: var(--accent-text, #f0a6c4); font-weight: 700; display: flex; gap: 8px; align-items: center; }
.field { margin-bottom: 12px; }
.field:last-child { margin-bottom: 0; }
.field > label { display: block; margin-bottom: 6px; font-size: 0.78rem; font-weight: 700; color: rgba(232, 213, 163, 0.78); letter-spacing: 0.04em; }
.required { color: #f87171; margin-left: 4px; }
.cast-input, .cast-select {
    width: 100%;
    background: #050505;
    border: 1px solid #2a2a2a;
    border-radius: 10px;
    padding: 12px 14px;
    color: #fff;
    font-size: 16px; /* iOSズーム回避 */
    line-height: 1.4;
    min-height: 44px;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.cast-input[type="date"] { min-height: 44px; color-scheme: dark; font-variant-numeric: tabular-nums; }
.cast-input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(79%) sepia(29%) saturate(566%) hue-rotate(5deg) brightness(91%) contrast(89%); cursor: pointer; }
.cast-textarea { resize: vertical; line-height: 1.6; }
.min-h100 { min-height: 110px; }
.cast-input:focus, .cast-select:focus { border-color: var(--accent, #d670a2); box-shadow: 0 0 0 3px rgba(var(--accent-rgb, 214, 112, 162), 0.22); }
.cast-input::placeholder { color: rgba(255, 255, 255, 0.28); font-weight: 500; }
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.tag-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
.tag-chip { position: relative; display: inline-flex; }
.tag-chip input { position: absolute; opacity: 0; pointer-events: none; }
.tag-chip span { padding: 7px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid rgba(255, 255, 255, .05); background: rgba(10, 10, 10, 0.8); color: #a0a0a0; }
/* ルックス / 内面の選択チップ：意図的にカラー区分（ルックス=mauve、内面=cool blue）。
   ただし旧 warm wine (#5a1c2c) / 旧 gold (#c8a951) を撤去して新テーマに整合させる。 */
.tag-looks input:checked + span {
    background: rgba(var(--accent-rgb, 214, 112, 162), 0.18);
    border-color: rgba(var(--accent-rgb, 214, 112, 162), 0.45);
    color: var(--accent-text, #f0a6c4);
}
.tag-personality input:checked + span {
    background: rgba(56, 132, 220, 0.16);
    border-color: rgba(56, 132, 220, 0.45);
    color: #93c5fd;
}
.radio-like-row { display: flex; gap: 18px; flex-wrap: wrap; }
.radio-like { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
.radio-like input { position: absolute; opacity: 0; pointer-events: none; }
.radio-like .dot { width: 16px; height: 16px; border-radius: 50%; border: 1px solid #2a2a2a; background: #050505; position: relative; }
.radio-like input:checked + .dot { border-color: var(--accent, #d670a2); }
.radio-like input:checked + .dot::after { content: ""; width: 8px; height: 8px; border-radius: 50%; background: var(--accent, #d670a2); position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); }
.radio-like span:last-child { color: #a0a0a0; font-size: 12px; font-weight: 600; }
.radio-like input:checked + .dot + span { color: #fff; }
.save-bar { position: absolute; left: 0; right: 0; bottom: 0; padding: 24px 16px 16px; background: linear-gradient(to top, #050505 35%, rgba(5, 5, 5, 0.92) 65%, rgba(5, 5, 5, 0)); }
.save-btn { width: 100%; border: 1px solid #a63253; border-radius: 16px; background: linear-gradient(135deg, #8a2542, #5a1628); color: #fff; font-weight: 700; letter-spacing: .08em; padding: 14px; display: inline-flex; justify-content: center; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, .45); }
</style>
@endsection
