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

    .recruit-input-with-unit {
        display: flex;
        align-items: center;
        border: 1px solid #4a4a4a; /* Input border color */
        border-radius: 8px; /* Input border radius */
        overflow: hidden; /* Ensures inner elements respect border-radius */
    }

    .recruit-input-with-unit .recruit-input {
        flex-grow: 1;
        border: none; /* Remove default input border */
        padding-right: 0; /* Adjust padding if needed */
    }

    .recruit-input-with-unit .unit {
        padding: 10px 14px;
        background-color: #333; /* Unit background color */
        color: #fff; /* Unit text color */
        font-size: 0.9rem;
        white-space: nowrap;
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
        <section class="bg-[#141010] border border-neutral-800/60 rounded-2xl overflow-hidden shadow-xl">
          <div class="p-5 border-b border-neutral-800/60 bg-gradient-to-r from-[#1a1410] to-transparent flex items-center gap-3">
            <div class="p-2.5 bg-[#d4a017]/10 rounded-xl text-[#d4a017]">
              <i class="fas fa-bullhorn" size={20}></i>
            </div>
            <h3 class="font-bold text-lg text-white">訴求内容</h3>
          </div>
          <div class="p-6 space-y-6">
            <p class="recruit-section-copy">一覧や詳細の冒頭で目に入る文章です。短くても、お店らしさと魅力が分かる言葉を入れると伝わりやすくなります。</p>
            <div class="space-y-3">
              <label class="flex items-center justify-between">
                <span class="text-sm font-bold text-neutral-300">キャッチコピー <span class="text-[#d4a017] ml-1">必須</span></span>
              </label>
              <input 
                type="text" 
                name="catch_copy"
                placeholder="キャッチコピーを入力してください"
                value="{{ old('catch_copy', $recruit['catch_copy']) }}"
                class="w-full bg-[#0a0505] border border-neutral-800 text-white px-4 py-3.5 rounded-xl focus:border-[#d4a017]/50 focus:ring-2 focus:ring-[#d4a017]/5 outline-none transition-all"
              />
              <div class="flex items-start gap-2 text-xs text-neutral-500">
                <i class="fas fa-info-circle mt-0.5 text-[#d4a017]/60" size={14}></i>
                <p>30〜60文字くらいで、エリア・雰囲気・強みが入ると見やすくなります。</p>
              </div>
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">お店からのメッセージ <span class="text-[#d4a017] ml-1">必須</span></span>
                </label>
                <textarea 
                    name="message" 
                    rows="3" 
                    class="w-full bg-[#0a0505] border border-neutral-800 text-white px-4 py-3.5 rounded-xl focus:border-[#d4a017]/50 focus:ring-2 focus:ring-[#d4a017]/5 outline-none transition-all"
                    placeholder="お店の魅力や募集メッセージ">{{ old('message', $recruit['message']) }}</textarea>
                <div class="flex items-start gap-2 text-xs text-neutral-500">
                    <i class="fas fa-info-circle mt-0.5 text-[#d4a017]/60" size={14}></i>
                    <p>未経験歓迎、客層、サポート体制などを入れると応募前の不安を減らせます。</p>
                </div>
            </div>
          </div>
        </section>

        <section class="bg-[#141010] border border-neutral-800/60 rounded-2xl overflow-hidden shadow-xl">
          <div class="p-5 border-b border-neutral-800/60 bg-gradient-to-r from-[#1a1410] to-transparent flex items-center gap-3">
            <div class="p-2.5 bg-[#d4a017]/10 rounded-xl text-[#d4a017]">
              <i class="fas fa-coins" size={20}></i>
            </div>
            <h3 class="font-bold text-lg text-white">給与</h3>
          </div>
          <div class="p-6 space-y-6">
            <p class="recruit-section-copy">もっとも比較されやすい項目です。通常時給、体験時給、ボーナス条件が一目で分かるようにそろえておくと、プレビューでも見やすくなります。</p>
            {{-- 時給タイプ選択ボタン（サンプル） --}}
            {{-- <div class="grid grid-cols-3 gap-2">
              {salaryTypes.map(type => (
                <button key={type} className={`py-2 rounded-xl text-sm font-bold border transition-all ${type === '時給' ? 'bg-[#d4a017] text-[#0a0505] border-[#d4a017]' : 'bg-transparent border-neutral-800 text-neutral-500'}`}>
                  {type}
                </button>
              ))}
            </div> --}}
            <div class="flex items-center gap-3">
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">
                        @if(($recruitType ?? 'fulltime') === 'trial')
                            体入時給 <span class="text-[#d4a017] ml-1">必須</span>
                        @elseif(($recruitType ?? 'fulltime') === 'help')
                            ヘルプ時給 <span class="text-[#d4a017] ml-1">必須</span>
                        @else
                            通常時給 <span class="text-[#d4a017] ml-1">必須</span>
                        @endif
                    </label>
                    <div class="recruit-input-with-unit">
                        <input type="text" name="hourly_wage_regular" class="recruit-input" value="{{ old('hourly_wage_regular', number_format(floatval($recruit['hourly_wage_regular'] ?? 0))) }}" placeholder="5,000" inputmode="numeric" pattern="[0-9]*" data-type="currency">
                        <span class="unit">円</span>
                    </div>
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">体験時給</label>
                    <div class="recruit-input-with-unit">
                        <input type="text" name="trial_hourly_wage" class="recruit-input" value="{{ old('trial_hourly_wage', number_format(floatval($recruit['trial_hourly_wage'] ?? 0))) }}" placeholder="4,000" inputmode="numeric" pattern="[0-9]*" data-type="currency">
                        <span class="unit">円</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3" style="margin-bottom: 16px;">
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">ヘルプ時給</label>
                    <div class="recruit-input-with-unit">
                        <input type="text" name="help_hourly_wage" class="recruit-input" value="{{ old('help_hourly_wage', number_format(floatval($recruit['help_hourly_wage'] ?? null))) }}" placeholder="3,500" inputmode="numeric" pattern="[0-9]*" data-type="currency">
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
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">給与備考</span>
                </label>
                <textarea 
                    name="salary_text" 
                    rows="2" 
                    class="w-full bg-[#0a0505] border border-neutral-800 text-white px-4 py-3.5 rounded-xl focus:border-[#d4a017]/50 focus:ring-2 focus:ring-[#d4a017]/5 outline-none transition-all"
                    placeholder="指名手当・日払いなど">{{ old('salary_text', $recruit['salary_text']) }}</textarea>
                <div class="flex items-start gap-2 text-xs text-neutral-500">
                    <i class="fas fa-info-circle mt-0.5 text-[#d4a017]/60" size={14}></i>
                    <p>バック率、日払い、送迎、ノルマ有無など補足条件をここにまとめると伝わりやすいです。</p>
                </div>
            </div>
            <div class="flex items-center gap-3" style="margin-bottom: 16px;">
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">ボーナス金額</label>
                    <div class="recruit-input-with-unit">
                        <input type="text" name="noruma_reward" class="recruit-input" value="{{ number_format(floatval(old('noruma_reward', $recruit['noruma_reward'] ?? 0))) }}" placeholder="30,000" inputmode="numeric" pattern="[0-9]*" data-type="currency">
                        <span class="unit">円</span>
                    </div>
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">合計勤務日数</label>
                    <input type="number" name="bonus_total_working_days" class="recruit-input" value="{{ old('bonus_total_working_days', $recruit['bonus_total_working_days'] ?? '') }}" placeholder="例: 10日">
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">合計勤務時間</label>
                    <input type="number" name="bonus_total_working_hours" class="recruit-input" value="{{ old('bonus_total_working_hours', $recruit['bonus_total_working_hours'] ?? '') }}" placeholder="例: 40時間">
                </div>
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">その他条件（フリーテキスト）</span>
                </label>
                <textarea 
                    name="bonus_other_conditions" 
                    rows="2" 
                    class="w-full bg-[#0a0505] border border-neutral-800 text-white px-4 py-3.5 rounded-xl focus:border-[#d4a017]/50 focus:ring-2 focus:ring-[#d4a017]/5 outline-none transition-all"
                    placeholder="例: 無断欠勤なし">{{ old('bonus_other_conditions', $recruit['bonus_other_conditions'] ?? '') }}</textarea>
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">給与・待遇タグ</span>
                </label>
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
        </section>

        <section class="bg-[#141010] border border-neutral-800/60 rounded-2xl overflow-hidden shadow-xl">
          <div class="p-5 border-b border-neutral-800/60 bg-gradient-to-r from-[#1a1410] to-transparent flex items-center gap-3">
            <div class="p-2.5 bg-[#d4a017]/10 rounded-xl text-[#d4a017]">
              <i class="fas fa-calendar-clock" size={20}></i> {{-- Keeping existing icon, applying new style --}}
            </div>
            <h3 class="font-bold text-lg text-white">勤務条件</h3>
          </div>
          <div class="p-6 space-y-6">
            <p class="recruit-section-copy">勤務のしやすさが伝わるように、時間・日数・休みの情報を具体的に入れてください。</p>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">勤務時間 <span class="text-[#d4a017] ml-1">必須</span></span>
                </label>
                <input
                    type="text"
                    name="working_hours"
                    class="w-full bg-[#0a0505] border border-neutral-800 text-white px-4 py-3.5 rounded-xl focus:border-[#d4a017]/50 focus:ring-2 focus:ring-[#d4a017]/5 outline-none transition-all"
                    value="{{ old('working_hours', $recruit['working_hours']) }}"
                    placeholder="20:00〜翌1:00">
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">勤務日数 <span class="text-[#d4a017] ml-1">必須</span></span>
                </label>
                <input
                    type="text"
                    name="working_days"
                    class="w-full bg-[#0a0505] border border-neutral-800 text-white px-4 py-3.5 rounded-xl focus:border-[#d4a017]/50 focus:ring-2 focus:ring-[#d4a017]/5 outline-none transition-all"
                    value="{{ old('working_days', $recruit['working_days']) }}"
                    placeholder="週1日からOK">
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">定休日</span>
                </label>
                <input
                    type="text"
                    name="regular_holiday"
                    class="w-full bg-[#0a0505] border border-neutral-800 text-white px-4 py-3.5 rounded-xl focus:border-[#d4a017]/50 focus:ring-2 focus:ring-[#d4a017]/5 outline-none transition-all"
                    value="{{ old('regular_holiday', $recruit['regular_holiday']) }}"
                    placeholder="不定休">
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">働き方タグ</span>
                </label>
                <div class="flex flex-wrap gap-2"> {{-- Using flex-wrap gap-2 from React example for tags --}}
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
        </section>

        <section class="bg-[#141010] border border-neutral-800/60 rounded-2xl overflow-hidden shadow-xl">
          <div class="p-5 border-b border-neutral-800/60 bg-gradient-to-r from-[#1a1410] to-transparent flex items-center gap-3">
            <div class="p-2.5 bg-[#d4a017]/10 rounded-xl text-[#d4a017]">
              <i class="fas fa-file-lines" size={20}></i> {{-- Keeping existing icon, applying new style --}}
            </div>
            <h3 class="font-bold text-lg text-white">募集要項</h3>
          </div>
          <div class="p-6 space-y-6">
            <p class="recruit-section-copy">仕事内容や雰囲気は、応募するか迷っている人の判断材料になります。働くイメージが湧く説明を意識してください。</p>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">仕事内容 <span class="text-[#d4a017] ml-1">必須</span></span>
                </label>
                <textarea
                    name="job_content"
                    rows="4"
                    class="w-full bg-[#0a0505] border border-neutral-800 text-white px-4 py-3.5 rounded-xl focus:border-[#d4a017]/50 focus:ring-2 focus:ring-[#d4a017]/5 outline-none transition-all"
                    placeholder="仕事内容">{{ old('job_content', $recruit['job_content']) }}</textarea>
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">お店の雰囲気・補足</span>
                </label>
                <textarea
                    name="store_atmosphere"
                    rows="3"
                    class="w-full bg-[#0a0505] border border-neutral-800 text-white px-4 py-3.5 rounded-xl focus:border-[#d4a017]/50 focus:ring-2 focus:ring-[#d4a017]/5 outline-none transition-all"
                    placeholder="お店の雰囲気">{{ old('store_atmosphere', $recruit['store_atmosphere']) }}</textarea>
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">応募資格 <span class="text-[#d4a017] ml-1">必須</span></span>
                </label>
                <input
                    type="text"
                    name="qualification"
                    class="w-full bg-[#0a0505] border border-neutral-800 text-white px-4 py-3.5 rounded-xl focus:border-[#d4a017]/50 focus:ring-2 focus:ring-[#d4a017]/5 outline-none transition-all"
                    value="{{ old('qualification', $recruit['qualification']) }}"
                    placeholder="18歳以上（高校生不可）">
            </div>
          </div>
        </section>

        <section class="bg-[#141010] border border-neutral-800/60 rounded-2xl overflow-hidden shadow-xl">
          <div class="p-5 border-b border-neutral-800/60 bg-gradient-to-r from-[#1a1410] to-transparent flex items-center gap-3">
            <div class="p-2.5 bg-[#d4a017]/10 rounded-xl text-[#d4a017]">
              <i class="fas fa-gift" size={20}></i>
            </div>
            <h3 class="font-bold text-lg text-white">タグ選択</h3>
          </div>
          <div class="p-6 space-y-6">
            <p class="recruit-section-copy">詳細画面で条件をざっと見てもらうための補助情報です。該当するものだけ選ぶと、読みやすい求人票になります。</p>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">メリット・待遇</span>
                </label>
                <div class="flex flex-wrap gap-2">
                    @foreach(($masters['merit'] ?? []) as $tag)
                        <label class="recruit-chip">
                            <input type="checkbox" name="merit_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('merit_tag_ids', $recruit['merit_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                            <span>{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">店舗特徴</span>
                </label>
                <div class="flex flex-wrap gap-2">
                    @foreach(($masters['feature'] ?? []) as $tag)
                        <label class="recruit-chip">
                            <input type="checkbox" name="feature_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('feature_tag_ids', $recruit['feature_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                            <span>{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">設備</span>
                </label>
                <div class="flex flex-wrap gap-2">
                    @foreach(($masters['facility'] ?? []) as $tag)
                        <label class="recruit-chip">
                            <input type="checkbox" name="facility_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('facility_tag_ids', $recruit['facility_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                            <span>{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="space-y-3">
                <label class="flex items-center justify-between">
                    <span class="text-sm font-bold text-neutral-300">お店の雰囲気</span>
                </label>
                <div class="flex flex-wrap gap-2">
                    @foreach(($masters['atmosphere'] ?? []) as $tag)
                        <label class="recruit-chip">
                            <input type="checkbox" name="atmosphere_tag_ids[]" value="{{ $tag->id }}" {{ in_array((int) $tag->id, old('atmosphere_tag_ids', $recruit['atmosphere_tag_ids'] ?? []), true) ? 'checked' : '' }}>
                            <span>{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
          </div>
        </section>

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
    </main>
    <footer class="fixed bottom-0 inset-x-0 z-50 bg-[#0a0505]/95 backdrop-blur-md border-t border-neutral-800/50 py-4 px-4">
        <div class="max-w-2xl mx-auto flex gap-3">
            <a href="{{ route('shop.jobdescription') }}" class="flex-1 bg-white/5 hover:bg-white/10 text-neutral-300 font-bold py-4 rounded-xl text-sm transition-all border border-white/10 flex items-center justify-center gap-2">
                <i class="fas fa-eye"></i> プレビューを見る
            </a>
            <button type="submit" form="recruit-form" class="flex-1 bg-[#d4a017] hover:bg-[#b88a14] text-[#0a0505] font-bold py-4 rounded-xl text-sm transition-all shadow-lg shadow-[#d4a017]/10 flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> 更新内容を保存
            </button>
        </div>
    </footer>
</div>
@endsection
