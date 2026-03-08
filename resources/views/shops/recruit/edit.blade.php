@extends('layouts.app')

@section('title', '求人情報の編集')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
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

    <form id="recruit-form">
        {{-- 給与 --}}
        <div class="recruit-section">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-yen-sign"></i></div>
                <h3 class="recruit-section-title">給与</h3>
            </div>
            <div class="recruit-info-grid" style="margin-bottom: 16px;">
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">通常時給</label>
                    <input type="number" name="hourly_wage_regular" class="recruit-input" value="{{ $recruit['hourly_wage_regular'] }}" placeholder="5000">
                </div>
                <div class="recruit-form-group" style="margin-bottom: 0;">
                    <label class="recruit-label">体験時給</label>
                    <input type="number" name="trial_hourly_wage" class="recruit-input" value="{{ $recruit['trial_hourly_wage'] }}" placeholder="4000">
                </div>
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">給与備考</label>
                <textarea name="salary_text" rows="2" class="recruit-textarea" placeholder="指名手当・日払いなど">{{ $recruit['salary_text'] }}</textarea>
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
                <input type="text" name="working_hours" class="recruit-input" value="{{ $recruit['working_hours'] }}" placeholder="20:00〜翌1:00">
            </div>
            <div class="recruit-form-group">
                <label class="recruit-label">勤務日数</label>
                <input type="text" name="working_days" class="recruit-input" value="{{ $recruit['working_days'] }}" placeholder="週1日からOK">
            </div>
        </div>

        {{-- 待遇 --}}
        <div class="recruit-section">
            <div class="recruit-section-head">
                <div class="recruit-section-icon"><i class="fas fa-gift"></i></div>
                <h3 class="recruit-section-title">Benefits（タップで選択）</h3>
            </div>
            <div class="recruit-tag-wrap">
                @foreach($recruit['benefits'] as $benefit)
                <span class="benefit-tag {{ in_array($benefit, $recruit['selected_benefits']) ? 'selected' : '' }}" onclick="this.classList.toggle('selected')" role="button" tabindex="0">{{ $benefit }}</span>
                @endforeach
            </div>
        </div>

        <button type="button" onclick="saveRecruit()" class="btn-gold w-full py-4 shadow-2xl mt-6">
            求人情報を保存する
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function saveRecruit() {
        alert('求人情報を保存しました');
        location.href = "{{ route('shop.recruits.status') }}";
    }
</script>
@endpush
