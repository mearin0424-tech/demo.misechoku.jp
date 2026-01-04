@extends('layouts.app')
@section('title', '求人情報')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/shop_recruit.css') }}">
@endpush

@section('content')
<div class="recruit-view-container p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-gold serif-font text-xl uppercase">Recruit Info</h1>
        <a href="{{ route('shop.recruits.edit') }}" class="text-xs text-gray-500 underline">編集する</a>
    </div>

    <div class="recruit-card bg-white/5 border border-white/10 p-6 rounded-xl space-y-6">
        <div>
            <label class="text-gold text-[10px] block mb-1 uppercase">Wage</label>
            <p class="text-xl font-bold font-serif">時給 5,000円 〜</p>
        </div>
        <div>
            <label class="text-gold text-[10px] block mb-1 uppercase">Hours</label>
            <p class="text-sm">20:00 〜 翌1:00</p>
        </div>
    </div>
</div>
@endsection