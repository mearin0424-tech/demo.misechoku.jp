@extends('layouts.app')

@section('title', '求人ステータス管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
@endpush

@section('content')
<div class="contents inner animate-fadeIn p-4 pb-24">
    <div class="flex justify-between items-center mb-8">
        <div class="title-area">
            <h2 class="serif-font text-2xl gold-gradient tracking-tight">Recruit Status</h2>
            <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1">Management</p>
        </div>
        <a href="{{ route('shop.mypage.index') }}" class="text-gray-400 text-xs no-underline">戻る</a>
    </div>

    {{-- サマリー表示 --}}
    <div class="glass-panel p-6 rounded-2xl mb-8 border-gold/10">
        <p class="text-[10px] text-gold uppercase tracking-widest mb-1">Current Active</p>
        <p class="text-2xl font-serif text-white">1 <span class="text-xs opacity-50">Postings</span></p>
    </div>

    {{-- 求人リスト（モックデータに合わせて表示） --}}
    <div class="space-y-4">
        <div class="glass-panel p-5 rounded-2xl border-white/5 relative">
            <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="status-badge status-active">ON AIR</span>
                    </div>
                    <h4 class="text-white font-bold text-lg mb-1">レギュラーキャスト募集</h4>
                    <p class="text-[10px] text-gray-500">時給: ¥5,000〜</p>
                </div>
                <div class="toggle-btn active" onclick="toggleStatus(this)">
                    <div class="toggle-circle"></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-4 border-t border-white/5 pt-4">
                <a href="{{ route('shop.recruits.edit') }}" class="text-center py-3 rounded-xl bg-white/5 text-gray-300 text-xs font-bold no-underline">
                    編集する
                </a>
                <a href="{{ route('shop.recruits.show', ['id' => 1]) }}" class="text-center py-3 rounded-xl border border-white/10 text-gold text-xs font-bold no-underline">
                    プレビュー
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleStatus(el) {
        el.classList.toggle('active');
        const badge = el.closest('.glass-panel').querySelector('.status-badge');
        if (el.classList.contains('active')) {
            badge.innerText = 'ON AIR';
            badge.className = 'status-badge status-active';
        } else {
            badge.innerText = 'PAUSED';
            badge.className = 'status-badge status-inactive';
        }
    }
</script>
@endpush