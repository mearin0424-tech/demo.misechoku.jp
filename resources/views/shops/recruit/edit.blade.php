@extends('layouts.app')

@section('title', '求人情報の編集')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
@endpush

@section('content')
<div class="contents inner animate-fadeIn p-4 pb-24">
    <div class="flex justify-between items-center mb-8">
        <div class="title-area">
            <h2 class="serif-font text-2xl gold-gradient tracking-tight">Edit Recruit</h2>
            <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1">Job Details</p>
        </div>
        <a href="{{ route('shop.recruits.status') }}" class="text-gray-400 text-xs no-underline">キャンセル</a>
    </div>

    <form id="recruit-form">
        {{-- 給与セクション --}}
        <div class="glass-panel p-6 rounded-2xl mb-6">
            <h3 class="text-xs text-gray-500 uppercase tracking-widest mb-6 border-b border-white/5 pb-2">Salary Information</h3>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="recruit-form-group">
                    <label class="recruit-label">通常時給</label>
                    <input type="number" name="hourly_wage_regular" class="recruit-input" value="{{ $recruit['hourly_wage_regular'] }}">
                </div>
                <div class="recruit-form-group">
                    <label class="recruit-label">体験時給</label>
                    <input type="number" name="trial_hourly_wage" class="recruit-input" value="{{ $recruit['trial_hourly_wage'] }}">
                </div>
            </div>

            <div class="recruit-form-group">
                <label class="recruit-label">給与備考</label>
                <textarea name="salary_text" rows="2" class="recruit-textarea">{{ $recruit['salary_text'] }}</textarea>
            </div>
        </div>

        {{-- 勤務条件セクション --}}
        <div class="glass-panel p-6 rounded-2xl mb-6">
            <h3 class="text-xs text-gray-500 uppercase tracking-widest mb-6 border-b border-white/5 pb-2">Conditions</h3>
            
            <div class="recruit-form-group">
                <label class="recruit-label">勤務時間</label>
                <input type="text" name="working_hours" class="recruit-input" value="{{ $recruit['working_hours'] }}">
            </div>

            <div class="recruit-form-group">
                <label class="recruit-label">勤務日数</label>
                <input type="text" name="working_days" class="recruit-input" value="{{ $recruit['working_days'] }}">
            </div>
        </div>

        {{-- 待遇セクション --}}
        <div class="glass-panel p-6 rounded-2xl mb-6">
            <h3 class="text-xs text-gray-500 uppercase tracking-widest mb-6 border-b border-white/5 pb-2">Benefits</h3>
            <div class="flex flex-wrap">
                @foreach($recruit['benefits'] as $benefit)
                    <div class="benefit-tag {{ in_array($benefit, $recruit['selected_benefits']) ? 'selected' : '' }}" onclick="this.classList.toggle('selected')">
                        {{ $benefit }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 送信ボタン --}}
        <div class="mt-8">
            <button type="button" onclick="saveRecruit()" class="btn-gold w-full py-4 shadow-2xl">
                求人情報を保存する
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function saveRecruit() {
        // ここで非同期通信（axios等）を行い、updateメソッドへ送信
        alert('求人情報を保存しました');
        location.href = "{{ route('shop.recruits.status') }}";
    }
</script>
@endpush