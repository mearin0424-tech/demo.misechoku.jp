@extends('layouts.app')

@section('title', '求人情報の編集')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/shop_recruit.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/profile_edit.css') }}">
@endpush

@section('content')
<div class="edit-container">
    <div class="edit-header-nav p-4 flex justify-between items-center bg-sub border-b border-border">
        <a href="{{ route('shop.recruits.show') }}" class="text-xs text-gray-500"><i class="fas fa-chevron-left"></i> 戻る</a>
        <h1 class="text-sm font-bold text-white">求人票の編集</h1>
        <div class="w-10"></div> {{-- スペーサー --}}
    </div>

    <form id="recruit-form" action="{{ route('shop.recruits.update') }}" method="POST" class="p-4 space-y-8">
        @csrf
        @method('PUT')

        {{-- アピールセクション --}}
        <section class="form-section">
            <h2 class="text-gold text-xs font-bold mb-4 uppercase tracking-widest border-l-2 border-gold pl-2">Promotion</h2>
            <div class="form-group">
                <label class="edit-label">キャッチコピー</label>
                <input type="text" name="catch_copy" value="{{ $recruit['catch_copy'] }}" class="edit-input" placeholder="例：未経験大歓迎！高時給保証！">
            </div>
            <div class="form-group">
                <label class="edit-label">メッセージ</label>
                <textarea name="message" rows="4" class="edit-input">{{ $recruit['message'] }}</textarea>
            </div>
        </section>

        {{-- 給与セクション --}}
        <section class="form-section">
            <h2 class="text-gold text-xs font-bold mb-4 uppercase tracking-widest border-l-2 border-gold pl-2">Salary</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="edit-label">本入時給 (円)</label>
                    <input type="number" name="wage" value="{{ $recruit['hourly_wage_regular'] }}" class="edit-input">
                </div>
                <div class="form-group">
                    <label class="edit-label">体入時給 (円)</label>
                    <input type="number" name="trial_wage" value="{{ $recruit['trial_hourly_wage'] }}" class="edit-input">
                </div>
            </div>
            <div class="form-group">
                <label class="edit-label">給与詳細・バック詳細</label>
                <textarea name="salary_text" rows="3" class="edit-input">{{ $recruit['salary_text'] }}</textarea>
            </div>
        </section>

        {{-- 勤務条件セクション --}}
        <section class="form-section">
            <h2 class="text-gold text-xs font-bold mb-4 uppercase tracking-widest border-l-2 border-gold pl-2">Conditions</h2>
            <div class="form-group">
                <label class="edit-label">勤務時間</label>
                <input type="text" name="hours" value="{{ $recruit['working_hours'] }}" class="edit-input">
            </div>
            <div class="form-group">
                <label class="edit-label">勤務日数・休日</label>
                <input type="text" name="days" value="{{ $recruit['working_days'] }}" class="edit-input">
            </div>
            <div class="form-group">
                <label class="edit-label">応募資格</label>
                <input type="text" name="qualification" value="{{ $recruit['qualification'] }}" class="edit-input">
            </div>
        </section>

        {{-- 待遇セクション（チェックボックス形式） --}}
        <section class="form-section">
            <h2 class="text-gold text-xs font-bold mb-4 uppercase tracking-widest border-l-2 border-gold pl-2">Benefits</h2>
            <div class="grid grid-cols-2 gap-2">
                @foreach($recruit['benefits'] as $benefit)
                    <label class="flex items-center p-3 bg-white/5 border border-white/10 rounded-lg cursor-pointer">
                        <input type="checkbox" name="benefits[]" value="{{ $benefit }}" 
                            {{ in_array($benefit, $recruit['selected_benefits']) ? 'checked' : '' }}
                            class="mr-2 accent-gold">
                        <span class="text-xs">{{ $benefit }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- 固定保存ボタン --}}
        <div class="fixed-save-area">
            <button type="submit" class="btn-gold-submit w-full py-4 rounded-full font-bold">
                <i class="fas fa-save mr-2"></i> 設定を保存する
            </button>
        </div>
    </form>
</div>
@endsection