@extends('layouts.app')

@section('title', '求人情報の編集')
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
</style>
@endpush

@section('content')
<div class="contents inner recruit-edit-page animate-fadeIn p-4">
    <header class="recruit-status-header" style="margin-bottom: 18px;">
        <a href="{{ route('shop.recruits.status') }}" class="recruit-status-back"><i class="fas fa-chevron-left"></i> キャンセル</a>
        <div class="recruit-status-title-block">
            <h1 class="recruit-status-title serif-font" style="font-size: 1.4rem;">Edit Recruit</h1>
            <p class="recruit-status-sub">求人情報の編集</p>
        </div>
    </header>

    <section class="recruit-edit-hero">
        <div class="recruit-edit-headline">
            <div>
                <span class="status-badge {{ ($recruit['status'] ?? 'active') === 'active' ? 'status-active' : 'status-inactive' }}">
                    {{ ($recruit['status'] ?? 'active') === 'active' ? '現在は公開中' : '現在は非公開' }}
                </span>
                <h2 class="recruit-edit-title serif-font">{{ $recruit['catch_copy'] ?: '求人票を整えて応募につながる内容にしましょう' }}</h2>
                <p class="recruit-edit-sub">
                    入力した内容は `recruits/show` のプレビューにそのまま反映されます。まずはキャッチコピー、時給、勤務条件の3点を優先して整えるのがおすすめです。
                </p>
            </div>
        </div>
        <div class="recruit-edit-toolbar">
            <a href="{{ route('shop.jobdescription') }}" class="recruit-ghost-btn">
                <i class="fas fa-eye"></i> プレビューを見る
            </a>
            <a href="{{ route('shop.recruits.status') }}" class="recruit-ghost-btn">
                <i class="fas fa-list-check"></i> ステータス管理へ
            </a>
        </div>
    </section>

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

        {{-- 給与 --}}
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
                    <input type="number" name="hourly_wage_regular" class="recruit-input" value="{{ old('hourly_wage_regular', $recruit['hourly_wage_regular']) }}" placeholder="5000">
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">体験時給</label>
                    <input type="number" name="trial_hourly_wage" class="recruit-input" value="{{ old('trial_hourly_wage', $recruit['trial_hourly_wage']) }}" placeholder="4000">
                </div>
            </div>
            <div class="recruit-info-grid" style="margin-bottom: 16px;">
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">ヘルプ時給</label>
                    <input type="number" name="help_hourly_wage" class="recruit-input" value="{{ old('help_hourly_wage', $recruit['help_hourly_wage'] ?? null) }}" placeholder="3500">
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
                    <input type="number" name="noruma_reward" class="recruit-input" value="{{ old('noruma_reward', $recruit['noruma_reward'] ?? 0) }}" placeholder="30000">
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">ボーナス達成条件</label>
                    <textarea name="bonus_condition" rows="2" class="recruit-textarea" placeholder="例: 10日勤務達成 / 規定シフト消化 / 無断欠勤なし">{{ old('bonus_condition', $recruit['bonus_condition'] ?? '') }}</textarea>
                </div>
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

        {{-- 勤務条件 --}}
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

        {{-- 各種タグ --}}
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
                <p class="recruit-bottom-actions-title">入力後はそのまま保存できます</p>
                <p class="recruit-bottom-actions-text">保存後にプレビューを確認して、見え方や文言の最終調整をしてください。</p>
            </div>
            <div class="recruit-bottom-actions-buttons">
                <a href="{{ route('shop.jobdescription') }}" class="recruit-ghost-btn">
                    <i class="fas fa-eye"></i> プレビュー
                </a>
                <button type="submit" class="btn-gold shadow-2xl" style="min-width: 190px;">
                    求人情報を保存する
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
