@extends('layouts.app')

@section('title', '求人情報')

@push('styles')
{{-- 共通化したCSSを読み込み --}}
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<style>
    .view-section-label {
        font-size: 0.65rem;
        color: var(--gold);
        text-transform: uppercase;
        letter-spacing: 0.15em;
        margin-bottom: 4px;
        display: block;
        font-weight: bold;
    }
    .view-data-text {
        color: #fff;
        line-height: 1.6;
    }
</style>
@endpush

@section('content')
<div class="contents inner animate-fadeIn p-4 pb-24">
    {{-- ヘッダーエリア --}}
    <div class="flex justify-between items-center mb-8">
        <div class="title-area">
            <h2 class="serif-font text-2xl gold-gradient tracking-tight">Job Preview</h2>
            <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1">求人情報のプレビュー</p>
        </div>
        <a href="{{ route('shop.recruits.edit') }}" class="text-gold text-xs font-bold no-underline border border-gold/30 px-3 py-1 rounded-full">編集する</a>
    </div>

    {{-- キャッチコピー --}}
    <div class="mb-8 px-2">
        <h3 class="serif-font text-xl text-white leading-tight italic">
            "{{ $recruit['catch_copy'] }}"
        </h3>
    </div>

    <div class="space-y-6">
        {{-- メイン条件カード --}}
        <div class="glass-panel p-6 rounded-2xl border-gold/10">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="view-section-label">Wage</label>
                    <p class="text-2xl font-serif gold-gradient">¥{{ number_format($recruit['hourly_wage_regular']) }}~</p>
                    <p class="text-[10px] text-gray-500">体験時給: ¥{{ number_format($recruit['trial_hourly_wage']) }}</p>
                </div>
                <div>
                    <label class="view-section-label">Hours</label>
                    <p class="text-sm view-data-text">{{ $recruit['working_hours'] }}</p>
                    <p class="text-[10px] text-gray-500">{{ $recruit['working_days'] }}</p>
                </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-white/5">
                <label class="view-section-label">Salary Details</label>
                <p class="text-xs view-data-text">{{ $recruit['salary_text'] }}</p>
            </div>
        </div>

        {{-- 待遇・メリット --}}
        <div class="glass-panel p-6 rounded-2xl">
            <label class="view-section-label mb-4">Benefits</label>
            <div class="flex flex-wrap gap-2">
                @foreach($recruit['selected_benefits'] as $benefit)
                    <span class="status-badge status-active" style="font-size: 0.6rem;">{{ $benefit }}</span>
                @endforeach
            </div>
        </div>

        {{-- メッセージ --}}
        <div class="glass-panel p-6 rounded-2xl">
            <label class="view-section-label mb-2">Message</label>
            <p class="text-sm view-data-text font-light">
                {!! nl2br(e($recruit['message'])) !!}
            </p>
        </div>

        {{-- 応募資格 --}}
        <div class="px-6 py-4 rounded-xl border border-white/5 bg-white/5">
            <label class="view-section-label">Qualification</label>
            <p class="text-xs text-gray-400">{{ $recruit['qualification'] }}</p>
        </div>
    </div>

    {{-- フッターアクション --}}
    <div class="mt-10 flex flex-col gap-4">
        <a href="{{ route('shop.recruits.status') }}" class="btn-gold w-full py-4 text-center no-underline">
            ステータス管理に戻る
        </a>
        <p class="text-center text-[10px] text-gray-600 uppercase tracking-widest">Luxe Lounge Premium Recruit</p>
    </div>
</div>
@endsection