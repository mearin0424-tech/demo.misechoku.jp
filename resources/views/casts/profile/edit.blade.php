@extends('layouts.app')

@section('title', 'プロフィール確認・編集')
@section('header_title', 'プロフィール確認・編集')
@section('guide_message', 'プロフィールを充実させていただくと、よりマッチしやすくなります。公開したくない内容は、無理にご記入いただかなくても問題ございません。')

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
<div class="edit-container cast-profile-edit">
    @if(session('message'))
        <p class="profile-edit-flash">{{ session('message') }}</p>
    @endif

    <form action="{{ route($updateRoute ?? 'cast.profile.update') }}" method="POST" class="profile-edit-form h-adr">
        @csrf
        <span class="p-country-name" style="display:none;">Japan</span>

        {{-- ニックネーム --}}
        <div class="form-section">
            <label class="edit-label" for="nickname">ニックネーム <span class="required">必須</span></label>
            <input type="text" id="nickname" name="nickname" class="edit-input" value="{{ old('nickname', $profile['nickname']) }}" placeholder="ニックネーム">
        </div>

        {{-- 名前 --}}
        <div class="form-section">
            <label class="edit-label" for="name">名前</label>
            <input type="text" id="name" name="name" class="edit-input" value="{{ old('name', $profile['name']) }}" placeholder="名前">
        </div>

        {{-- 生年月日 --}}
        <div class="form-section">
            <label class="edit-label">生年月日 <span class="required">必須</span></label>
            <div class="birth-row">
                <select name="birth_year" class="edit-select" aria-label="年">
                    @foreach(range(date('Y'), 1950) as $y)
                        <option value="{{ $y }}" {{ old('birth_year', $profile['birth_year']) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <span class="birth-sep">年</span>
                <select name="birth_month" class="edit-select" aria-label="月">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ old('birth_month', $profile['birth_month']) == (string)$m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
                <span class="birth-sep">月</span>
                <select name="birth_day" class="edit-select" aria-label="日">
                    @foreach(range(1, 31) as $d)
                        <option value="{{ $d }}" {{ old('birth_day', $profile['birth_day']) == (string)$d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
                <span class="birth-sep">日</span>
            </div>
        </div>

        {{-- 居住地 --}}
        <div class="form-section">
            <label class="edit-label">居住地</label>
            <div class="address-item" style="margin-bottom: 12px;">
                <label class="edit-label sub" for="zip">郵便番号</label>
                <input
                    type="text"
                    id="zip"
                    name="zip"
                    class="edit-input p-postal-code"
                    data-postal-code
                    maxlength="8"
                    pattern="[0-9-]*"
                    value="{{ old('zip', $profile['zip']) }}"
                    inputmode="numeric"
                    autocomplete="postal-code"
                    placeholder="例：160-0021"
                >
                <p class="input-hint">半角数字で入力してください。ハイフン有無どちらでも入力でき、住所が自動補完されます。</p>
            </div>
            <div class="address-row">
                <div class="address-item">
                    <label class="edit-label sub" for="pref">都道府県</label>
                    <select id="pref" name="pref" class="edit-select p-region">
                        <option value="">選択してください</option>
                        @foreach ($prefOptions as $pref)
                            <option value="{{ $pref }}" {{ old('pref', $profile['pref']) === $pref ? 'selected' : '' }}>{{ $pref }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="address-item">
                    <label class="edit-label sub" for="city">市区町村</label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        class="edit-input p-locality"
                        value="{{ old('city', $profile['city']) }}"
                        autocomplete="address-level2"
                        placeholder="例：新宿区"
                    >
                </div>
            </div>
            <div class="address-item" style="margin-top: 12px;">
                <label class="edit-label sub" for="addr1">町名・番地</label>
                <input
                    type="text"
                    id="addr1"
                    name="addr1"
                    class="edit-input p-street-address"
                    value="{{ old('addr1', $profile['addr1']) }}"
                    autocomplete="address-line1"
                    placeholder="例：1-2-3"
                >
            </div>
        </div>

        <div class="form-section">
            <label class="edit-label">希望業種</label>
            <div class="checkbox-chip-grid">
                @foreach(($masters['industries'] ?? []) as $industry)
                    <label class="checkbox-chip">
                        <input
                            type="checkbox"
                            name="industry_ids[]"
                            value="{{ $industry->id }}"
                            {{ in_array((int) $industry->id, old('industry_ids', $profile['industry_ids'] ?? []), true) ? 'checked' : '' }}
                        >
                        <span>{{ $industry->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 自己紹介 --}}
        <div class="form-section">
            <label class="edit-label" for="intro">自己紹介</label>
            <textarea id="intro" name="intro" class="edit-textarea" rows="4" placeholder="自己紹介">{{ old('intro', $profile['intro']) }}</textarea>
        </div>

        {{-- 身長 --}}
        <div class="form-section">
            <label class="edit-label" for="height">身長</label>
            <div class="number-unit-row">
                <input type="number" id="height" name="height" class="edit-input with-unit" value="{{ old('height', $profile['height']) }}" placeholder="156" min="100" max="250">
                <span class="unit">cm</span>
            </div>
        </div>

        {{-- 体重 --}}
        <div class="form-section">
            <label class="edit-label" for="weight">体重</label>
            <div class="number-unit-row">
                <input type="number" id="weight" name="weight" class="edit-input with-unit" value="{{ old('weight', $profile['weight']) }}" placeholder="44" min="30" max="150">
                <span class="unit">kg</span>
            </div>
        </div>

        {{-- 3サイズ --}}
        <div class="form-section">
            <label class="edit-label">3サイズ</label>
            <div class="three-size-row">
                <div class="size-item">
                    <input type="number" name="bust" class="edit-input" value="{{ old('bust', $profile['bust']) }}" placeholder="B" min="50" max="120">
                </div>
                <div class="size-item">
                    <input type="number" name="waist" class="edit-input" value="{{ old('waist', $profile['waist']) }}" placeholder="W" min="40" max="120">
                </div>
                <div class="size-item">
                    <input type="number" name="hip" class="edit-input" value="{{ old('hip', $profile['hip']) }}" placeholder="H" min="50" max="120">
                </div>
            </div>
        </div>

        {{-- その他情報（アコーディオン） --}}
        <div class="form-section other-info-section">
            <button type="button" class="other-info-header" id="other-info-toggle" aria-expanded="true" aria-controls="other-info-body" onclick="toggleOtherInfo(this)">
                <span class="other-info-icon" aria-hidden="true"></span>
                <span class="other-info-title">その他情報</span>
                <i class="fas fa-minus other-info-chevron" aria-hidden="true"></i>
            </button>
            <div class="other-info-body" id="other-info-body" role="region">
                {{-- 希望職種（展開可能） --}}
                <div class="accordion-item">
                    <button type="button" class="accordion-trigger" aria-expanded="false" aria-controls="desired-job-body" onclick="toggleAccordionItem(this)">
                        <span>希望職種</span>
                        <i class="fas fa-plus accordion-icon" aria-hidden="true"></i>
                    </button>
                    <div class="accordion-content" id="desired-job-body">
                        <input type="text" name="desired_job" class="edit-input" value="{{ old('desired_job', $profile['desired_job']) }}" placeholder="希望職種を入力">
                    </div>
                </div>
                {{-- ご自分の系統 --}}
                <div class="accordion-item">
                    <button type="button" class="accordion-trigger" aria-expanded="false" aria-controls="my-field-body" onclick="toggleAccordionItem(this)">
                        <span>ご自分の系統</span>
                        <i class="fas fa-plus accordion-icon" aria-hidden="true"></i>
                    </button>
                    <div class="accordion-content" id="my-field-body">
                        <input type="text" name="my_field" class="edit-input" value="{{ old('my_field', $profile['my_field']) }}" placeholder="ご自分の系統を入力">
                        <div class="checkbox-chip-grid mt-12">
                            @foreach(($masters['looks'] ?? []) as $look)
                                <label class="checkbox-chip">
                                    <input
                                        type="checkbox"
                                        name="look_tag_ids[]"
                                        value="{{ $look->id }}"
                                        {{ in_array((int) $look->id, old('look_tag_ids', $profile['look_tag_ids'] ?? []), true) ? 'checked' : '' }}
                                    >
                                    <span>{{ $look->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                {{-- ご自分の内面・特技 --}}
                <div class="accordion-item">
                    <button type="button" class="accordion-trigger" aria-expanded="false" aria-controls="my-inner-body" onclick="toggleAccordionItem(this)">
                        <span>ご自分の内面・特技</span>
                        <i class="fas fa-plus accordion-icon" aria-hidden="true"></i>
                    </button>
                    <div class="accordion-content" id="my-inner-body">
                        <input type="text" name="my_inner_skills" class="edit-input" value="{{ old('my_inner_skills', $profile['my_inner_skills']) }}" placeholder="内面・特技を入力">
                        <div class="checkbox-chip-grid mt-12">
                            @foreach(($masters['personalities'] ?? []) as $personality)
                                <label class="checkbox-chip">
                                    <input
                                        type="checkbox"
                                        name="personality_tag_ids[]"
                                        value="{{ $personality->id }}"
                                        {{ in_array((int) $personality->id, old('personality_tag_ids', $profile['personality_tag_ids'] ?? []), true) ? 'checked' : '' }}
                                    >
                                    <span>{{ $personality->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- シフト希望 --}}
                <div class="form-row">
                    <label class="edit-label" for="shift_hope">シフト希望</label>
                    <select id="shift_hope" name="shift_hope" class="edit-select">
                        <option value="週1回出勤" {{ old('shift_hope', $profile['shift_hope']) === '週1回出勤' ? 'selected' : '' }}>週1回出勤</option>
                        <option value="週2回出勤" {{ old('shift_hope', $profile['shift_hope']) === '週2回出勤' ? 'selected' : '' }}>週2回出勤</option>
                        <option value="週3回以上" {{ old('shift_hope', $profile['shift_hope']) === '週3回以上' ? 'selected' : '' }}>週3回以上</option>
                    </select>
                </div>

                {{-- 勤務時間 --}}
                <div class="form-row">
                    <span class="edit-label">勤務時間</span>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="work_time" value="morning" class="accent-gold" {{ old('work_time', $profile['work_time']) === 'morning' ? 'checked' : '' }}>
                            <span>朝</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="work_time" value="day_night" class="accent-gold" {{ old('work_time', $profile['work_time']) === 'day_night' ? 'checked' : '' }}>
                            <span>昼or夜</span>
                        </label>
                    </div>
                </div>

                {{-- 現職業 --}}
                <div class="form-row">
                    <label class="edit-label" for="current_job">現職業</label>
                    <textarea id="current_job" name="current_job" class="edit-textarea" rows="3" placeholder="現職業を入力">{{ old('current_job', $profile['current_job']) }}</textarea>
                </div>

                {{-- ナイトワーク経験 --}}
                <div class="form-row">
                    <span class="edit-label">ナイトワーク経験</span>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="night_work_exp" value="none" class="accent-gold" {{ old('night_work_exp', $profile['night_work_exp']) === 'none' ? 'checked' : '' }}>
                            <span>無し</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="night_work_exp" value="yes" class="accent-gold" {{ old('night_work_exp', $profile['night_work_exp']) === 'yes' ? 'checked' : '' }}>
                            <span>有り</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-submit-section">
            <button type="submit" class="btn-gold-submit">保存する</button>
        </div>
    </form>
</div>

<script>
function toggleOtherInfo(btn) {
    var body = document.getElementById('other-info-body');
    var chevron = btn.querySelector('.other-info-chevron');
    var expanded = btn.getAttribute('aria-expanded') === 'true';
    body.classList.toggle('is-closed', expanded);
    btn.setAttribute('aria-expanded', !expanded);
    chevron.classList.toggle('fa-minus', !expanded);
    chevron.classList.toggle('fa-plus', expanded);
}
function toggleAccordionItem(btn) {
    var id = btn.getAttribute('aria-controls');
    var body = id ? document.getElementById(id) : btn.nextElementSibling;
    if (!body) return;
    var icon = btn.querySelector('.accordion-icon');
    var expanded = btn.getAttribute('aria-expanded') === 'true';
    body.classList.toggle('is-open', !expanded);
    btn.setAttribute('aria-expanded', !expanded);
    icon.classList.toggle('fa-plus', !expanded);
    icon.classList.toggle('fa-minus', expanded);
}
</script>
<style>
.checkbox-chip-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.checkbox-chip {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.checkbox-chip input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.checkbox-chip span {
    display: inline-flex;
    align-items: center;
    min-height: 38px;
    padding: 8px 14px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.04);
    color: #fff;
    font-size: 0.9rem;
}
.checkbox-chip input:checked + span {
    border-color: #d4af37;
    background: rgba(212, 175, 55, 0.18);
    color: #f8e7b0;
}
.mt-12 {
    margin-top: 12px;
}
</style>
@endsection
