@extends('layouts.app')

@section('title', '求人票の編集')

@section('guide_message')
    求人票は応募者向けのオファーです。店舗名・住所・写真・設備・店内の雰囲気などは「Shop Information（プロフィール編集）」で登録し、この画面では募集の訴求・給与・シフトの書き方・求人タグを編集してください。
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<style>
    .job-edit-v2 {
        --je-bg: #050505;
        --je-panel: #0a0a0a;
        --je-field: #110f0d;
        --je-border: #2a2015;
        --je-gold: #d4af37;
        background: var(--je-bg);
        margin: 0 calc(-1 * var(--content-padding-x, 16px));
        padding-bottom: calc(var(--footer-height, 75px) + 96px + env(safe-area-inset-bottom, 0px));
    }
    .job-edit-v2__shell {
        max-width: 28rem;
        margin: 0 auto;
        min-height: 100%;
        background: var(--je-panel);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
    }
    .job-edit-v2__top {
        position: sticky;
        top: 0;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        background: rgba(10, 10, 10, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid #1f1a14;
    }
    .job-edit-v2__back {
        color: #a1a1aa;
        padding: 4px;
        margin-left: -4px;
        font-size: 1.25rem;
        text-decoration: none;
        line-height: 1;
    }
    .job-edit-v2__back:hover { color: var(--je-gold); }
    .job-edit-v2__title-wrap { text-align: center; flex: 1; min-width: 0; }
    .job-edit-v2__title-en {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: 0.18em;
        font-family: var(--font-serif, "Shippori Mincho", serif);
    }
    .job-edit-v2__title-sub { margin: 2px 0 0; font-size: 9px; font-weight: 700; color: var(--je-gold); letter-spacing: 0.06em; }
    .job-edit-v2__spacer { width: 2rem; flex-shrink: 0; }

    .job-edit-v2__notice {
        margin: 12px 16px 0;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid rgba(212, 175, 55, 0.28);
        background: rgba(42, 34, 16, 0.35);
        font-size: 0.72rem;
        line-height: 1.65;
        color: #c4b8ae;
    }
    .job-edit-v2__notice a {
        color: var(--je-gold);
        font-weight: 700;
        white-space: nowrap;
    }

    .job-edit-v2__status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 20px;
        background: #110f0d;
        border-bottom: 1px solid var(--je-border);
    }
    .job-edit-v2__status-label { margin: 0 0 2px; font-size: 0.75rem; font-weight: 800; color: #fafafa; }
    .job-edit-v2__status-hint { margin: 0; font-size: 9px; color: #71717a; }
    .job-edit-v2__status-right { display: flex; align-items: center; gap: 10px; }
    .job-edit-v2__pub-label { font-size: 10px; font-weight: 800; color: #71717a; }
    .job-edit-v2__pub-label.is-on { color: var(--je-gold); }

    .job-edit-v2__switch {
        position: relative;
        width: 48px;
        height: 26px;
        flex-shrink: 0;
        cursor: pointer;
    }
    .job-edit-v2__switch input { opacity: 0; width: 0; height: 0; position: absolute; }
    .job-edit-v2__switch-track {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: #52525b;
        transition: background 0.25s ease;
    }
    .job-edit-v2__switch input:checked + .job-edit-v2__switch-track { background: var(--je-gold); }
    .job-edit-v2__switch-knob {
        position: absolute;
        top: 3px;
        left: 4px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
        transition: transform 0.25s ease;
    }
    .job-edit-v2__switch input:checked + .job-edit-v2__switch-track .job-edit-v2__switch-knob { transform: translateX(22px); }

    .job-edit-v2__form { padding: 20px; display: flex; flex-direction: column; gap: 40px; }

    .job-edit-v2__sec-title {
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
        letter-spacing: 0.06em;
    }
    .job-edit-v2__sec-title i { font-size: 0.9rem; color: #52525b; font-style: normal; }

    .job-edit-v2__field { margin-bottom: 22px; }
    .job-edit-v2__field:last-child { margin-bottom: 0; }
    .job-edit-v2__label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 10px;
        font-weight: 800;
        color: var(--je-gold);
        margin: 0 0 6px 4px;
    }
    .job-edit-v2__req {
        font-size: 8px;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 4px;
        background: rgba(212, 175, 55, 0.15);
        border: 1px solid rgba(212, 175, 55, 0.35);
        color: var(--je-gold);
        line-height: 1.2;
    }
    .job-edit-v2__input,
    .job-edit-v2__textarea {
        width: 100%;
        box-sizing: border-box;
        background: var(--je-field);
        border: 1px solid var(--je-border);
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 0.875rem;
        color: #fafafa;
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .job-edit-v2__textarea { resize: vertical; min-height: 100px; line-height: 1.6; }
    .job-edit-v2__input:focus,
    .job-edit-v2__textarea:focus {
        outline: none;
        border-color: rgba(212, 175, 55, 0.5);
        background: #161311;
    }
    .job-edit-v2__hint { margin: 6px 0 0 4px; font-size: 9px; line-height: 1.55; color: #52525b; }

    .job-edit-v2__grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    .job-edit-v2__unit-wrap { position: relative; }
    .job-edit-v2__unit-wrap .job-edit-v2__input { padding-right: 2.25rem; }
    .job-edit-v2__unit-suffix {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.75rem;
        color: #71717a;
        pointer-events: none;
    }

    .job-edit-v2__card {
        background: #110f0d;
        border: 1px solid var(--je-border);
        border-radius: 12px;
        padding: 16px;
    }
    .job-edit-v2__card--accent {
        border-color: #1f1a14;
        position: relative;
        overflow: hidden;
    }
    .job-edit-v2__card--accent::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: rgba(212, 175, 55, 0.45);
    }
    .job-edit-v2__card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .job-edit-v2__card-head label { margin: 0; font-size: 0.75rem; font-weight: 800; color: var(--je-gold); }

    .job-edit-v2__help-switch { width: 40px; height: 22px; }
    .job-edit-v2__help-switch .job-edit-v2__switch-knob { width: 16px; height: 16px; top: 3px; }
    .job-edit-v2__help-switch input:checked + .job-edit-v2__switch-track .job-edit-v2__switch-knob { transform: translateX(18px); }

    .job-edit-v2__chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .job-edit-v2__chip {
        position: relative;
        display: inline-flex;
        cursor: pointer;
    }
    .job-edit-v2__chip input { position: absolute; opacity: 0; pointer-events: none; }
    .job-edit-v2__chip span {
        display: inline-flex;
        align-items: center;
        min-height: 32px;
        padding: 6px 12px;
        border-radius: 999px;
        border: 1px solid var(--je-border);
        background: #141210;
        color: #a1a1aa;
        font-size: 10px;
        font-weight: 600;
        transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }
    .job-edit-v2__chip input:checked + span {
        border-color: var(--je-gold);
        background: #2a2210;
        color: var(--je-gold);
        box-shadow: 0 0 10px rgba(212, 175, 55, 0.1);
    }
    .job-edit-v2__tag-cat { margin: 0 0 8px; font-size: 0.75rem; font-weight: 800; color: var(--je-gold); }

    .job-edit-v2__shop-note {
        margin-top: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px dashed rgba(255, 255, 255, 0.12);
        font-size: 0.68rem;
        line-height: 1.6;
        color: #9ca3af;
    }

    .job-edit-v2__footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--footer-height, 75px);
        z-index: 35;
        display: flex;
        justify-content: center;
        padding: 16px;
        padding-left: max(16px, env(safe-area-inset-left, 0px));
        padding-right: max(16px, env(safe-area-inset-right, 0px));
        padding-bottom: calc(16px + env(safe-area-inset-bottom, 0px));
        background: rgba(10, 10, 10, 0.95);
        backdrop-filter: blur(10px);
        border-top: 1px solid #1f1a14;
        box-sizing: border-box;
    }
    .job-edit-v2__footer-inner {
        display: flex;
        gap: 12px;
        width: 100%;
        max-width: 28rem;
    }
    .job-edit-v2__btn-cancel {
        flex: 0 0 auto;
        padding: 12px 18px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 700;
        color: #a1a1aa;
        text-decoration: none;
        border: none;
        background: transparent;
        cursor: pointer;
        font-family: inherit;
    }
    .job-edit-v2__btn-cancel:hover { color: #fff; background: #18181b; }
    .job-edit-v2__btn-save {
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
        font-family: inherit;
    }
    .job-edit-v2__btn-save:hover { opacity: 0.92; }

    .profile-edit-flash,
    .recruit-error-summary { margin: 12px 16px 0; }

    .job-edit-v2__kind-tabs {
        display: flex;
        gap: 8px;
        padding: 12px 16px 0;
        background: var(--je-panel);
    }
    .job-edit-v2__kind-tabs a {
        flex: 1;
        text-align: center;
        padding: 10px 6px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        color: #71717a;
        border: 1px solid var(--je-border);
        background: var(--je-field);
        letter-spacing: 0.02em;
    }
    .job-edit-v2__kind-tabs a.is-active {
        color: var(--je-gold);
        border-color: rgba(212, 175, 55, 0.55);
        background: rgba(42, 34, 16, 0.65);
    }
</style>
@endpush

@section('content')
@php
    $isActive = ($recruit['status'] ?? 'active') === 'active';
    $rt = $recruitType ?? (!empty($usesJobTypes) && $usesJobTypes ? 'trial' : 'fulltime');
    if (!empty($usesJobTypes) && $usesJobTypes && $rt === 'fulltime') {
        $rt = 'trial';
    }
@endphp

<div class="job-edit-v2 animate-fadeIn">
    <div class="job-edit-v2__shell">
        <header class="job-edit-v2__top">
            <a href="{{ route('shop.jobdescription') }}" class="job-edit-v2__back" aria-label="戻る"><i class="fas fa-chevron-left"></i></a>
            <div class="job-edit-v2__title-wrap">
                <h1 class="job-edit-v2__title-en">EDIT JOB</h1>
                <p class="job-edit-v2__title-sub">求人票の編集</p>
            </div>
            <div class="job-edit-v2__spacer" aria-hidden="true"></div>
        </header>

        <p class="job-edit-v2__notice">
            <strong style="color:#e8dcc8;">Shop Information</strong> で登録：店舗名・業種・住所・最寄り・営業時間・定休・写真・<strong>設備／店内の雰囲気タグ</strong> など。<br>
            <strong style="color:#e8dcc8;">この画面（求人票）</strong> で登録：キャッチ・店長からのメッセージ・時給・ボーナス・勤務の書き方・<strong>働き方／歓迎条件／待遇タグ</strong>・応募資格。
            @if(!empty($usesJobTypes) && $usesJobTypes)
                <br><span style="color:#b4a99a;">求人票の編集は<strong>体験入店</strong>と<strong>ヘルプ</strong>の2種類です。本入の正式条件は体験後の面談で調整し、体験入店側では本入の時給・待遇を参考程度に書いてください。</span>
            @endif
            <a href="{{ route('shop.profile.store.edit') }}">プロフィールを編集</a>
        </p>

        @if(session('message'))
            <p class="profile-edit-flash" style="margin-top:12px;">{{ session('message') }}</p>
        @endif

        @if($errors->any())
            <div class="recruit-error-summary" style="margin-top:12px;">
                <p class="recruit-error-summary-title">入力内容を確認してください</p>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="recruit-form" action="{{ route('shop.recruits.update') }}" method="POST">
            @csrf
            @method('PUT')
            @if(!empty($usesJobTypes) && $usesJobTypes)
                <input type="hidden" name="recruit_job_kind" value="{{ $rt === 'help' ? 'help' : 'trial' }}">
            @endif

            <div class="job-edit-v2__status">
                <div>
                    <p class="job-edit-v2__status-label">ステータス</p>
                    <p class="job-edit-v2__status-hint">オフにすると求人票が非公開になります</p>
                </div>
                <div class="job-edit-v2__status-right">
                    <span class="job-edit-v2__pub-label {{ $isActive ? 'is-on' : '' }}" id="published-label">{{ $isActive ? '公開中' : '非公開' }}</span>
                    <label class="job-edit-v2__switch">
                        <input type="checkbox" name="published" value="1" {{ $isActive ? 'checked' : '' }} id="published-toggle" aria-labelledby="published-label">
                        <span class="job-edit-v2__switch-track"><span class="job-edit-v2__switch-knob"></span></span>
                    </label>
                </div>
            </div>

            @if(!empty($usesJobTypes) && $usesJobTypes)
                <nav class="job-edit-v2__kind-tabs" aria-label="求人の種類">
                    <a href="{{ route('shop.recruits.edit') }}" class="{{ $rt === 'trial' ? 'is-active' : '' }}">体験入店</a>
                    <a href="{{ route('shop.recruits.edit') }}?type=help" class="{{ $rt === 'help' ? 'is-active' : '' }}">ヘルプ</a>
                </nav>
            @endif

            <div class="job-edit-v2__form">

                <section aria-labelledby="job-sec-basic">
                    <h2 id="job-sec-basic" class="job-edit-v2__sec-title"><i class="fas fa-file-alt"></i> Basic Information</h2>
                    <div class="job-edit-v2__field">
                        <label class="job-edit-v2__label" for="catch_copy">キャッチコピー <span class="job-edit-v2__req">必須</span></label>
                        <input type="text" id="catch_copy" name="catch_copy" class="job-edit-v2__input recruit-input" value="{{ old('catch_copy', $recruit['catch_copy']) }}" placeholder="例: 未経験でも時給5000円スタート！" maxlength="100">
                        <p class="job-edit-v2__hint">一覧・求人票の冒頭で目立つ短い一文です（店舗名・住所はプロフィール側）。</p>
                    </div>
                    <div class="job-edit-v2__field">
                        <label class="job-edit-v2__label" for="message">店長からのメッセージ <span class="job-edit-v2__req">必須</span></label>
                        <textarea id="message" name="message" rows="5" class="job-edit-v2__textarea recruit-textarea" placeholder="未経験歓迎、サポート体制など">{{ old('message', $recruit['message']) }}</textarea>
                        <p class="job-edit-v2__hint">採用向けの紹介文です。客層やサポートはこちら／店舗の固定スペックはプロフィールとあわせて書くと伝わりやすいです。</p>
                    </div>
                </section>

                <section aria-labelledby="job-sec-salary">
                    <h2 id="job-sec-salary" class="job-edit-v2__sec-title"><i class="fas fa-wallet"></i> Salary &amp; Bonus</h2>
                    @if(!empty($usesJobTypes) && $usesJobTypes && $rt === 'help')
                        <div class="job-edit-v2__field">
                            <label class="job-edit-v2__label" for="help_hourly_wage">ヘルプ時給 <span class="job-edit-v2__req">必須</span></label>
                            <div class="job-edit-v2__unit-wrap">
                                <input type="text" id="help_hourly_wage" name="help_hourly_wage" class="job-edit-v2__input recruit-input" value="{{ old('help_hourly_wage', !empty($recruit['help_hourly_wage']) ? number_format((float) $recruit['help_hourly_wage']) : '') }}" placeholder="4,000" inputmode="numeric" data-type="currency">
                                <span class="job-edit-v2__unit-suffix">円</span>
                            </div>
                        </div>
                        <input type="hidden" name="has_help" value="1">
                    @elseif(!empty($usesJobTypes) && $usesJobTypes)
                        <div class="job-edit-v2__field">
                            <label class="job-edit-v2__label" for="trial_hourly_wage">体験入店時給 <span class="job-edit-v2__req">必須</span></label>
                            <div class="job-edit-v2__unit-wrap">
                                <input type="text" id="trial_hourly_wage" name="trial_hourly_wage" class="job-edit-v2__input recruit-input" value="{{ old('trial_hourly_wage', $recruit['trial_hourly_wage'] ? number_format((float) $recruit['trial_hourly_wage']) : '') }}" placeholder="5,000" inputmode="numeric" data-type="currency">
                                <span class="job-edit-v2__unit-suffix">円</span>
                            </div>
                        </div>
                        <div class="job-edit-v2__field">
                            <label class="job-edit-v2__label" for="hourly_wage_regular">本入時給（参考・任意）</label>
                            <div class="job-edit-v2__unit-wrap">
                                <input type="text" id="hourly_wage_regular" name="hourly_wage_regular" class="job-edit-v2__input recruit-input" value="{{ old('hourly_wage_regular', number_format((float) ($recruit['hourly_wage_regular'] ?? 0))) }}" placeholder="5,000" inputmode="numeric" data-type="currency">
                                <span class="job-edit-v2__unit-suffix">円</span>
                            </div>
                            <p class="job-edit-v2__hint">本入の正式条件は体験後に面談で調整します。求人票では参考として載せる程度にしてください（未入力・0 の場合はプレビューに本入参考は出ません）。</p>
                        </div>
                    @else
                        <div class="job-edit-v2__grid2">
                            <div class="job-edit-v2__field">
                                <label class="job-edit-v2__label" for="trial_hourly_wage">体入時給 <span class="job-edit-v2__req">必須</span></label>
                                <div class="job-edit-v2__unit-wrap">
                                    <input type="text" id="trial_hourly_wage" name="trial_hourly_wage" class="job-edit-v2__input recruit-input" value="{{ old('trial_hourly_wage', $recruit['trial_hourly_wage'] ? number_format((float) $recruit['trial_hourly_wage']) : '') }}" placeholder="5,000" inputmode="numeric" data-type="currency">
                                    <span class="job-edit-v2__unit-suffix">円</span>
                                </div>
                            </div>
                            <div class="job-edit-v2__field">
                                <label class="job-edit-v2__label" for="hourly_wage_regular">本入時給 <span class="job-edit-v2__req">必須</span></label>
                                <div class="job-edit-v2__unit-wrap">
                                    <input type="text" id="hourly_wage_regular" name="hourly_wage_regular" class="job-edit-v2__input recruit-input" value="{{ old('hourly_wage_regular', number_format((float) ($recruit['hourly_wage_regular'] ?? 0))) }}" placeholder="5,000" inputmode="numeric" data-type="currency">
                                    <span class="job-edit-v2__unit-suffix">円</span>
                                </div>
                            </div>
                        </div>

                        <div class="job-edit-v2__card" style="margin-top:8px;">
                            <div class="job-edit-v2__card-head">
                                <label for="has-help-toggle">ヘルプ求人を公開する</label>
                                <label class="job-edit-v2__switch job-edit-v2__help-switch">
                                    <input type="checkbox" name="has_help" value="1" id="has-help-toggle" {{ old('has_help', !empty($recruit['help_hourly_wage'])) ? 'checked' : '' }}>
                                    <span class="job-edit-v2__switch-track"><span class="job-edit-v2__switch-knob"></span></span>
                                </label>
                            </div>
                            <div class="job-edit-v2__field" id="help-wage-field">
                                <label class="job-edit-v2__label" for="help_hourly_wage">ヘルプ時給</label>
                                <div class="job-edit-v2__unit-wrap">
                                    <input type="text" id="help_hourly_wage" name="help_hourly_wage" class="job-edit-v2__input recruit-input" value="{{ old('help_hourly_wage', !empty($recruit['help_hourly_wage']) ? number_format((float) $recruit['help_hourly_wage']) : '') }}" placeholder="4,000" inputmode="numeric" data-type="currency">
                                    <span class="job-edit-v2__unit-suffix">円</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="job-edit-v2__field">
                        <label class="job-edit-v2__label" for="salary_text">給与備考</label>
                        <textarea id="salary_text" name="salary_text" rows="3" class="job-edit-v2__textarea recruit-textarea" placeholder="バック詳細・日払い・昇給など">{{ old('salary_text', $recruit['salary_text']) }}</textarea>
                        <p class="job-edit-v2__hint">バック率・各種手当・ノルマの有無など、応募者が比較しやすい条件をまとめます。</p>
                    </div>

                    <div class="job-edit-v2__card job-edit-v2__card--accent" style="padding-left:18px;">
                        <p style="margin:0 0 14px;font-size:0.75rem;font-weight:800;color:#e4e4e7;">入店ボーナス設定</p>
                        <div class="job-edit-v2__field">
                            <label class="job-edit-v2__label" for="noruma_reward">ボーナス金額</label>
                            <div class="job-edit-v2__unit-wrap">
                                <input type="text" id="noruma_reward" name="noruma_reward" class="job-edit-v2__input recruit-input" value="{{ old('noruma_reward', number_format((float) ($recruit['noruma_reward'] ?? 0))) }}" placeholder="50,000" inputmode="numeric" data-type="currency">
                                <span class="job-edit-v2__unit-suffix">円</span>
                            </div>
                        </div>
                        <div class="job-edit-v2__grid2">
                            <div class="job-edit-v2__field">
                                <label class="job-edit-v2__label" for="bonus_total_working_days">合計勤務回数</label>
                                <div class="job-edit-v2__unit-wrap">
                                    <input type="number" id="bonus_total_working_days" name="bonus_total_working_days" class="job-edit-v2__input" value="{{ old('bonus_total_working_days', $recruit['bonus_total_working_days'] ?? '') }}" placeholder="10" min="0">
                                    <span class="job-edit-v2__unit-suffix">回</span>
                                </div>
                            </div>
                            <div class="job-edit-v2__field">
                                <label class="job-edit-v2__label" for="bonus_total_working_hours">合計勤務時間</label>
                                <div class="job-edit-v2__unit-wrap">
                                    <input type="number" id="bonus_total_working_hours" name="bonus_total_working_hours" class="job-edit-v2__input" value="{{ old('bonus_total_working_hours', $recruit['bonus_total_working_hours'] ?? '') }}" placeholder="40" min="0">
                                    <span class="job-edit-v2__unit-suffix">h</span>
                                </div>
                            </div>
                        </div>
                        <div class="job-edit-v2__field">
                            <label class="job-edit-v2__label" for="bonus_other_conditions">その他条件（フリーテキスト）</label>
                            <input type="text" id="bonus_other_conditions" name="bonus_other_conditions" class="job-edit-v2__input" value="{{ old('bonus_other_conditions', $recruit['bonus_other_conditions'] ?? '') }}" placeholder="例: 無遅刻無欠勤">
                        </div>
                    </div>
                </section>

                <section aria-labelledby="job-sec-work">
                    <h2 id="job-sec-work" class="job-edit-v2__sec-title"><i class="fas fa-clock"></i> Working Conditions</h2>
                    <div class="job-edit-v2__field">
                        <label class="job-edit-v2__label" for="working_hours">勤務時間 <span class="job-edit-v2__req">必須</span></label>
                        <input type="text" id="working_hours" name="working_hours" class="job-edit-v2__input" value="{{ old('working_hours', $recruit['working_hours']) }}" placeholder="20:00 〜 翌1:00">
                    </div>
                    <div class="job-edit-v2__field">
                        <label class="job-edit-v2__label" for="working_days">勤務日数・シフト <span class="job-edit-v2__req">必須</span></label>
                        <input type="text" id="working_days" name="working_days" class="job-edit-v2__input" value="{{ old('working_days', $recruit['working_days']) }}" placeholder="週1日からOK">
                    </div>
                    <div class="job-edit-v2__field">
                        <label class="job-edit-v2__label" for="regular_holiday">定休日・休みの書き方</label>
                        <input type="text" id="regular_holiday" name="regular_holiday" class="job-edit-v2__input" value="{{ old('regular_holiday', $recruit['regular_holiday']) }}" placeholder="不定休 / 店舗の定休と異なる場合は記載">
                    </div>
                    <p class="job-edit-v2__shop-note">
                        店舗としての<strong>営業時間・定休日</strong>は Shop Information（プロフィール）で登録してください。この欄は<strong>この求人のシフト・休みの伝え方</strong>用です。
                    </p>
                </section>

                <section aria-labelledby="job-sec-detail">
                    <h2 id="job-sec-detail" class="job-edit-v2__sec-title"><i class="fas fa-briefcase"></i> Recruitment</h2>
                    <div class="job-edit-v2__field">
                        <label class="job-edit-v2__label" for="qualification">応募資格 <span class="job-edit-v2__req">必須</span></label>
                        <input type="text" id="qualification" name="qualification" class="job-edit-v2__input" value="{{ old('qualification', $recruit['qualification']) }}" placeholder="18歳以上（高校生不可）">
                    </div>
                </section>

                <section aria-labelledby="job-sec-tags">
                    <h2 id="job-sec-tags" class="job-edit-v2__sec-title"><i class="fas fa-check-circle"></i> Tags &amp; Appeals</h2>
                    <p class="job-edit-v2__hint" style="margin:-8px 0 16px 4px;">求人票向けのタグです。<strong>設備・店内の雰囲気</strong>のタグはプロフィール（Shop Information）で選んでください。</p>

                    <div style="margin-bottom:22px;">
                        <p class="job-edit-v2__tag-cat">働き方・給与</p>
                        <div class="job-edit-v2__chips">
                            @foreach(($masters['work_style'] ?? []) as $tag)
                                <label class="job-edit-v2__chip">
                                    <input type="checkbox" name="work_style_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('work_style_tag_ids', $recruit['work_style_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div style="margin-bottom:22px;">
                        <p class="job-edit-v2__tag-cat">歓迎条件</p>
                        <div class="job-edit-v2__chips">
                            @foreach(($masters['welcome'] ?? []) as $tag)
                                <label class="job-edit-v2__chip">
                                    <input type="checkbox" name="welcome_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('welcome_tag_ids', $recruit['welcome_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="job-edit-v2__tag-cat">待遇・サポート</p>
                        <div class="job-edit-v2__chips">
                            @foreach(($masters['benefit'] ?? []) as $tag)
                                <label class="job-edit-v2__chip">
                                    <input type="checkbox" name="benefit_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('benefit_tag_ids', $recruit['benefit_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                                    <span>{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>
        </form>

        <div class="job-edit-v2__footer">
            <div class="job-edit-v2__footer-inner">
                <a href="{{ route('shop.jobdescription') }}" class="job-edit-v2__btn-cancel">キャンセル</a>
                <button type="submit" form="recruit-form" class="job-edit-v2__btn-save">
                    <i class="fas fa-check"></i> 保存する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('recruit-form');
    var pubToggle = document.getElementById('published-toggle');
    var pubLabel = document.getElementById('published-label');
    if (pubToggle && pubLabel) {
        pubToggle.addEventListener('change', function () {
            pubLabel.textContent = pubToggle.checked ? '公開中' : '非公開';
            pubLabel.classList.toggle('is-on', pubToggle.checked);
        });
    }

    var helpToggle = document.getElementById('has-help-toggle');
    var helpField = document.getElementById('help-wage-field');
    function syncHelpField() {
        if (!helpField) return;
        helpField.style.opacity = helpToggle && helpToggle.checked ? '1' : '0.45';
        helpField.querySelectorAll('input').forEach(function (inp) {
            inp.disabled = !(helpToggle && helpToggle.checked);
        });
    }
    if (helpToggle) {
        helpToggle.addEventListener('change', syncHelpField);
        syncHelpField();
    }

    if (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('[data-type="currency"]').forEach(function (el) {
                el.value = String(el.value).replace(/[^\d]/g, '');
            });
        });
    }
});
</script>
@endpush
