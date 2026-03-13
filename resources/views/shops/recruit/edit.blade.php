@extends('layouts.app')

@section('title', '求人情報の編集')

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
<div class="contents inner animate-fadeIn p-4 pb-24">
    <header class="recruit-status-header" style="margin-bottom: 24px;">
        <a href="{{ route('shop.recruits.status') }}" class="recruit-status-back"><i class="fas fa-chevron-left"></i> キャンセル</a>
        <div class="recruit-status-title-block">
            <h1 class="recruit-status-title serif-font" style="font-size: 1.4rem;">Edit Recruit</h1>
            <p class="recruit-status-sub">求人情報の編集</p>
        </div>
    </header>

    @if(session('message'))
        <p class="profile-edit-flash" style="margin-bottom:16px;">{{ session('message') }}</p>
    @endif

    <form id="recruit-form" action="{{ route('shop.recruits.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="recruit-section">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-bullhorn"></i></div>
                <h3 class="recruit-section-title">訴求内容</h3>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">キャッチコピー</label>
                <input type="text" name="catch_copy" class="recruit-input" value="{{ old('catch_copy', $recruit['catch_copy']) }}" placeholder="例: 六本木で一番稼げるお店です！">
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">お店からのメッセージ</label>
                <textarea name="message" rows="3" class="recruit-textarea" placeholder="お店の魅力や募集メッセージ">{{ old('message', $recruit['message']) }}</textarea>
            </div>
        </div>

        {{-- 給与 --}}
        <div class="recruit-section">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-yen-sign"></i></div>
                <h3 class="recruit-section-title">給与</h3>
            </div>
            <div class="recruit-info-grid" style="margin-bottom: 16px;">
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">通常時給</label>
                    <input type="number" name="hourly_wage_regular" class="recruit-input" value="{{ old('hourly_wage_regular', $recruit['hourly_wage_regular']) }}" placeholder="5000">
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">体験時給</label>
                    <input type="number" name="trial_hourly_wage" class="recruit-input" value="{{ old('trial_hourly_wage', $recruit['trial_hourly_wage']) }}" placeholder="4000">
                </div>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">給与備考</label>
                <textarea name="salary_text" rows="2" class="recruit-textarea" placeholder="指名手当・日払いなど">{{ old('salary_text', $recruit['salary_text']) }}</textarea>
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
        <div class="recruit-section">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-calendar-clock"></i></div>
                <h3 class="recruit-section-title">勤務条件</h3>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">勤務時間</label>
                <input type="text" name="working_hours" class="recruit-input" value="{{ old('working_hours', $recruit['working_hours']) }}" placeholder="20:00〜翌1:00">
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">勤務日数</label>
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

        <div class="recruit-section">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-file-lines"></i></div>
                <h3 class="recruit-section-title">募集要項</h3>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">仕事内容</label>
                <textarea name="job_content" rows="4" class="recruit-textarea" placeholder="仕事内容">{{ old('job_content', $recruit['job_content']) }}</textarea>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">お店の雰囲気・補足</label>
                <textarea name="store_atmosphere" rows="3" class="recruit-textarea" placeholder="お店の雰囲気">{{ old('store_atmosphere', $recruit['store_atmosphere']) }}</textarea>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">応募資格</label>
                <input type="text" name="qualification" class="recruit-input" value="{{ old('qualification', $recruit['qualification']) }}" placeholder="18歳以上（高校生不可）">
            </div>
        </div>

        {{-- 各種タグ --}}
        <div class="recruit-section">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-gift"></i></div>
                <h3 class="recruit-section-title">タグ選択</h3>
            </div>
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

        <button type="submit" class="btn-gold w-full py-4 shadow-2xl mt-6">
            求人情報を保存する
        </button>
    </form>
</div>
@endsection
