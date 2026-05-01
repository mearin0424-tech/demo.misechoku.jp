@extends('layouts.app')

@section('title', '求人情報の編集')
@section('header_title', '求人情報の編集')
@section('guide_message', '求人票は、最初に目に入る条件とお店らしさが伝わるほど、応募につながりやすくなります。上から順にご入力いただければ、そのまま見やすい求人票になるよう整えております。')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<style>
    .recruit-chip-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .recruit-chip {
        position: relative;
        display: inline-flex;
        align-items: center;
    }
    .recruit-chip input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .recruit-chip span {
        display: inline-flex;
        align-items: center;
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.12);
        background: rgba(255,255,255,0.04);
        color: #fff;
        font-size: 0.85rem;
    }
    .recruit-chip input:checked + span {
        border-color: var(--gold);
        background: rgba(212, 175, 55, 0.18);
        color: #f8e7b0;
    }

    .recruit-input-with-unit {
        display: flex;
        align-items: center;
        border: 1px solid rgba(255,255,255,0.14);
        border-radius: 12px;
        overflow: hidden;
        background: rgba(0,0,0,0.25);
    }

    .recruit-input-with-unit .recruit-input {
        flex-grow: 1;
        border: none;
        border-radius: 0;
        background: transparent;
    }

    .recruit-input-with-unit .unit {
        padding: 10px 14px;
        background: rgba(255,255,255,0.06);
        color: #e9dede;
        font-size: 0.85rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .recruit-publish-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 8px 2px 20px;
        margin-bottom: 4px;
    }
    .recruit-publish-label {
        font-size: 0.62rem;
        font-weight: 700;
        color: rgba(255,255,255,0.45);
        letter-spacing: 0.14em;
        text-transform: uppercase;
        margin: 0 0 6px;
    }
    .recruit-publish-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .recruit-publish-title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        color: #fff;
    }
    .recruit-publish-title.is-off {
        color: rgba(255,255,255,0.35);
    }
    .recruit-publish-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--gold);
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.25);
    }
    .recruit-publish-switch-wrap {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
    }
    .recruit-publish-switch {
        position: relative;
        display: inline-block;
        width: 64px;
        height: 36px;
        cursor: pointer;
    }
    .recruit-publish-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }
    .recruit-publish-switch-track {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: rgba(255,255,255,0.12);
        transition: background 0.25s ease;
    }
    .recruit-publish-switch input:checked + .recruit-publish-switch-track {
        background: rgba(212, 175, 55, 0.95);
    }
    .recruit-publish-switch-knob {
        position: absolute;
        top: 3px;
        left: 4px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.35);
        transition: transform 0.25s ease;
    }
    .recruit-publish-switch input:checked + .recruit-publish-switch-track .recruit-publish-switch-knob {
        transform: translateX(26px);
    }
    .recruit-publish-switch-hint {
        font-size: 0.58rem;
        font-weight: 700;
        color: rgba(255,255,255,0.38);
        letter-spacing: 0.04em;
    }

    .recruit-edit-footer-fixed {
        position: fixed;
        left: 0;
        right: 0;
        bottom: var(--footer-height);
        z-index: 30;
        display: flex;
        gap: 10px;
        padding: 12px 16px;
        background: linear-gradient(180deg, rgba(18, 6, 8, 0.92), rgba(12, 4, 6, 0.98));
        border-top: 1px solid rgba(230, 208, 128, 0.18);
        box-sizing: border-box;
    }
    .recruit-edit-footer-fixed .recruit-edit-footer-btn {
        flex: 1;
        justify-content: center;
        min-height: 48px;
        margin: 0;
    }
    .recruit-edit-footer-primary {
        flex: 1;
        min-height: 48px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #d4af37, #b8922a);
        color: #1a0a0c;
        font-size: 0.85rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 8px 24px rgba(212, 175, 55, 0.2);
    }
    .recruit-edit-footer-primary:hover {
        filter: brightness(1.05);
    }

    .recruit-edit-page {
        padding-bottom: calc(var(--footer-height) + 88px);
    }
</style>
@endpush

@section('content')
<div class="contents inner recruit-edit-page animate-fadeIn p-4">
    @php
        $isActive = ($recruit['status'] ?? 'active') === 'active';
    @endphp

    @if(session('message'))
        <p class="profile-edit-flash" style="margin-bottom:16px;">{{ session('message') }}</p>
    @endif

    @if($errors->any())
        <div class="recruit-error-summary">
            <p class="recruit-error-summary-title">入力内容を確認してください</p>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <nav class="recruit-anchor-nav" aria-label="編集セクション">
        <a href="#recruit-appeal" class="recruit-anchor-link">訴求内容</a>
        <a href="#recruit-salary" class="recruit-anchor-link">給与</a>
        <a href="#recruit-work" class="recruit-anchor-link">勤務条件</a>
        <a href="#recruit-detail" class="recruit-anchor-link">募集要項</a>
        <a href="#recruit-tags" class="recruit-anchor-link">タグ選択</a>
        <a href="#recruit-save" class="recruit-anchor-link">保存</a>
    </nav>

    <form id="recruit-form" action="{{ route('shop.recruits.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="recruit-publish-bar">
            <div>
                <p class="recruit-publish-label">ステータス</p>
                <div class="recruit-publish-title-row">
                    <h2 class="recruit-publish-title {{ $isActive ? '' : 'is-off' }}">{{ $isActive ? '公開中' : '公開停止中' }}</h2>
                    @if($isActive)
                        <span class="recruit-publish-dot" aria-hidden="true"></span>
                    @endif
                </div>
            </div>
            <div class="recruit-publish-switch-wrap">
                <label class="recruit-publish-switch" for="publish-toggle">
                    <input type="checkbox" id="publish-toggle" name="status" value="active" {{ $isActive ? 'checked' : '' }}>
                    <span class="recruit-publish-switch-track"><span class="recruit-publish-switch-knob"></span></span>
                </label>
                <span class="recruit-publish-switch-hint">{{ $isActive ? 'オフにすると非公開' : 'オンにすると公開' }}</span>
            </div>
        </div>

        <div class="recruit-section" id="recruit-appeal">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-bullhorn"></i></div>
                <h3 class="recruit-section-title">訴求内容</h3>
            </div>
            <p class="recruit-section-copy">一覧や詳細の冒頭で目に入る文章です。短くても、お店らしさと魅力が分かる言葉を入れると伝わりやすくなります。</p>
            <div class="recruit-form-group">
                <label class="recruit-label">キャッチコピー <span class="recruit-field-required">必須</span></label>
                <input type="text" name="catch_copy" class="recruit-input" value="{{ old('catch_copy', $recruit['catch_copy']) }}" placeholder="例: 六本木で一番稼げるお店です！">
                <p class="recruit-helper-text">30〜60文字くらいで、エリア・雰囲気・強みが入ると見やすくなります。</p>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">お店からのメッセージ <span class="recruit-field-required">必須</span></label>
                <textarea name="message" rows="3" class="recruit-textarea" placeholder="お店の魅力や募集メッセージ">{{ old('message', $recruit['message']) }}</textarea>
                <p class="recruit-helper-text">未経験歓迎、客層、サポート体制などを入れると応募前の不安を減らせます。</p>
            </div>
        </div>

        <div class="recruit-section" id="recruit-salary">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-yen-sign"></i></div>
                <h3 class="recruit-section-title">給与</h3>
            </div>
            <p class="recruit-section-copy">もっとも比較されやすい項目です。通常時給、体験時給、ボーナス条件が一目で分かるようにそろえておくと、プレビューでも見やすくなります。</p>
            <div class="recruit-info-grid" style="margin-bottom: 16px;">
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">
                        @if(($recruitType ?? 'fulltime') === 'trial')
                            体入時給 <span class="recruit-field-required">必須</span>
                        @elseif(($recruitType ?? 'fulltime') === 'help')
                            ヘルプ時給 <span class="recruit-field-required">必須</span>
                        @else
                            通常時給 <span class="recruit-field-required">必須</span>
                        @endif
                    </label>
                    <div class="recruit-input-with-unit">
                        <input type="text" name="hourly_wage_regular" class="recruit-input" value="{{ old('hourly_wage_regular', number_format((float) ($recruit['hourly_wage_regular'] ?? 0))) }}" placeholder="5,000" inputmode="numeric" pattern="[0-9]*" data-type="currency">
                        <span class="unit">円</span>
                    </div>
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">体験時給</label>
                    <div class="recruit-input-with-unit">
                        <input type="text" name="trial_hourly_wage" class="recruit-input" value="{{ old('trial_hourly_wage', number_format((float) ($recruit['trial_hourly_wage'] ?? 0))) }}" placeholder="4,000" inputmode="numeric" pattern="[0-9]*" data-type="currency">
                        <span class="unit">円</span>
                    </div>
                </div>
            </div>
            <div class="recruit-info-grid" style="margin-bottom: 16px;">
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">ヘルプ時給</label>
                    <div class="recruit-input-with-unit">
                        <input type="text" name="help_hourly_wage" class="recruit-input" value="{{ old('help_hourly_wage', number_format((float) ($recruit['help_hourly_wage'] ?? 0))) }}" placeholder="3,500" inputmode="numeric" pattern="[0-9]*" data-type="currency">
                        <span class="unit">円</span>
                    </div>
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0; align-self: flex-end;">
                    <label class="recruit-label">
                        <input type="checkbox" name="has_help" value="1" {{ old('has_help', !empty($recruit['help_hourly_wage'])) ? 'checked' : '' }}>
                        ヘルプ求人を公開する
                    </label>
                </div>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">給与備考</label>
                <textarea name="salary_text" rows="2" class="recruit-textarea" placeholder="指名手当・日払いなど">{{ old('salary_text', $recruit['salary_text']) }}</textarea>
                <p class="recruit-helper-text">バック率、日払い、送迎、ノルマ有無など補足条件をここにまとめると伝わりやすいです。</p>
            </div>
            <div class="recruit-info-grid" style="margin-bottom: 16px;">
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">ボーナス金額</label>
                    <div class="recruit-input-with-unit">
                        <input type="text" name="noruma_reward" class="recruit-input" value="{{ old('noruma_reward', number_format((float) ($recruit['noruma_reward'] ?? 0))) }}" placeholder="30,000" inputmode="numeric" pattern="[0-9]*" data-type="currency">
                        <span class="unit">円</span>
                    </div>
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">合計勤務日数</label>
                    <input type="number" name="bonus_total_working_days" class="recruit-input" value="{{ old('bonus_total_working_days', $recruit['bonus_total_working_days'] ?? '') }}" placeholder="例: 10">
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">合計勤務時間</label>
                    <input type="number" name="bonus_total_working_hours" class="recruit-input" value="{{ old('bonus_total_working_hours', $recruit['bonus_total_working_hours'] ?? '') }}" placeholder="例: 40">
                </div>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">その他条件（フリーテキスト）</label>
                <textarea name="bonus_other_conditions" rows="2" class="recruit-textarea" placeholder="例: 無断欠勤なし">{{ old('bonus_other_conditions', $recruit['bonus_other_conditions'] ?? '') }}</textarea>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">給与・待遇タグ</label>
                <div class="recruit-chip-grid">
                    @foreach(($masters['salary'] ?? []) as $tag)
                        <label class="recruit-chip">
                            <input
                                type="checkbox"
                                name="salary_tag_ids[]"
                                value="{{ $tag->id }}"
                                {{ in_array((int) $tag->id, old('salary_tag_ids', $recruit['salary_tag_ids'] ?? []), true) ? 'checked' : '' }}
                            >
                            <span>{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="recruit-section" id="recruit-work">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-calendar-clock"></i></div>
                <h3 class="recruit-section-title">勤務条件</h3>
            </div>
            <p class="recruit-section-copy">勤務のしやすさが伝わるように、時間・日数・休みの情報を具体的に入れてください。</p>
            <div class="recruit-form-group">
                <label class="recruit-label">勤務時間 <span class="recruit-field-required">必須</span></label>
                <input type="text" name="working_hours" class="recruit-input" value="{{ old('working_hours', $recruit['working_hours']) }}" placeholder="20:00〜翌1:00">
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">勤務日数 <span class="recruit-field-required">必須</span></label>
                <input type="text" name="working_days" class="recruit-input" value="{{ old('working_days', $recruit['working_days']) }}" placeholder="週1日からOK">
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">定休日</label>
                <input type="text" name="regular_holiday" class="recruit-input" value="{{ old('regular_holiday', $recruit['regular_holiday']) }}" placeholder="不定休">
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">働き方タグ</label>
                <div class="recruit-chip-grid">
                    @foreach(($masters['howto'] ?? []) as $tag)
                        <label class="recruit-chip">
                            <input
                                type="checkbox"
                                name="howto_tag_ids[]"
                                value="{{ $tag->id }}"
                                {{ in_array((int) $tag->id, old('howto_tag_ids', $recruit['howto_tag_ids'] ?? []), true) ? 'checked' : '' }}
                            >
                            <span>{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="recruit-section" id="recruit-detail">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-file-lines"></i></div>
                <h3 class="recruit-section-title">募集要項</h3>
            </div>
            <p class="recruit-section-copy">仕事内容や雰囲気は、応募するか迷っている人の判断材料になります。働くイメージが湧く説明を意識してください。</p>
            <div class="recruit-form-group">
                <label class="recruit-label">仕事内容 <span class="recruit-field-required">必須</span></label>
                <textarea name="job_content" rows="4" class="recruit-textarea" placeholder="仕事内容">{{ old('job_content', $recruit['job_content']) }}</textarea>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">お店の雰囲気・補足</label>
                <textarea name="store_atmosphere" rows="3" class="recruit-textarea" placeholder="お店の雰囲気">{{ old('store_atmosphere', $recruit['store_atmosphere']) }}</textarea>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">応募資格 <span class="recruit-field-required">必須</span></label>
                <input type="text" name="qualification" class="recruit-input" value="{{ old('qualification', $recruit['qualification']) }}" placeholder="18歳以上（高校生不可）">
            </div>
        </div>

        <div class="recruit-section" id="recruit-tags">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-gift"></i></div>
                <h3 class="recruit-section-title">タグ選択</h3>
            </div>
            <p class="recruit-section-copy">詳細画面で条件をざっと見てもらうための補助情報です。該当するものだけ選ぶと、読みやすい求人票になります。</p>
            <div class="recruit-form-group">
                <label class="recruit-label">メリット・待遇</label>
                <div class="recruit-chip-grid">
                    @foreach(($masters['merit'] ?? []) as $tag)
                        <label class="recruit-chip">
                            <input type="checkbox" name="merit_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('merit_tag_ids', $recruit['merit_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                            <span>{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">店舗特徴</label>
                <div class="recruit-chip-grid">
                    @foreach(($masters['feature'] ?? []) as $tag)
                        <label class="recruit-chip">
                            <input type="checkbox" name="feature_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('feature_tag_ids', $recruit['feature_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                            <span>{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">設備</label>
                <div class="recruit-chip-grid">
                    @foreach(($masters['facility'] ?? []) as $tag)
                        <label class="recruit-chip">
                            <input type="checkbox" name="facility_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('facility_tag_ids', $recruit['facility_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                            <span>{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">お店の雰囲気</label>
                <div class="recruit-chip-grid">
                    @foreach(($masters['atmosphere'] ?? []) as $tag)
                        <label class="recruit-chip">
                            <input type="checkbox" name="atmosphere_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('atmosphere_tag_ids', $recruit['atmosphere_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                            <span>{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="recruit-bottom-actions" id="recruit-save">
            <div class="recruit-bottom-actions-copy">
                <p class="recruit-bottom-actions-title">このあと保存できます</p>
                <p class="recruit-bottom-actions-text">画面下のボタンからプレビュー確認・保存ができます。</p>
            </div>
        </div>
    </form>

    <footer class="recruit-edit-footer-fixed" aria-label="求人編集の固定操作">
        <a href="{{ route('shop.jobdescription') }}" class="recruit-ghost-btn recruit-edit-footer-btn">
            <i class="fas fa-eye"></i> プレビューを見る
        </a>
        <button type="submit" form="recruit-form" class="recruit-edit-footer-primary">
            <i class="fas fa-save"></i> 更新内容を保存
        </button>
    </footer>
</div>
@endsection
