@extends('layouts.app')

@section('title', $cast['name'])

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/cast_profile.css') }}">
@endpush

@section('content')
<div class="profile-view-container inner pt-10">
    {{-- ヘッダー：アイコンと基本名 --}}
    <div class="cast-header text-center mb-8">
        <div class="relative inline-block">
            <img src="{{ $cast['img'] }}" class="w-32 h-32 rounded-full border-2 border-gold object-cover">
            @if($cast['is_applied'])
                <span class="badge-approved">入金承認済</span>
            @endif
        </div>
        <h1 class="mt-4 text-2xl font-bold serif-font text-white">{{ $cast['name'] }} ({{ $cast['age'] }})</h1>
        <div class="card-location text-xs text-gray-400 mt-1">
            <i class="fas fa-map-marker-alt"></i> 六本木 / キャスト
        </div>
    </div>

    {{-- アクション：キープボタン --}}
    <div class="interaction-bar flex justify-center gap-10 my-6">
        <button class="icon-btn {{ $cast['is_kept'] ? 'active' : '' }}">
            <i class="fas fa-bookmark"></i>
            <span class="label">KEEP ({{ $cast['keep_cnt'] }})</span>
        </button>
    </div>

    <div class="p-4 space-y-6">
        {{-- スペック情報：グリッド配置 --}}
        <div class="specs-grid grid grid-cols-2 gap-4 p-4 bg-white/5 rounded-xl border border-white/10 text-center">
            <div>
                <span class="text-gold text-[10px] block uppercase tracking-tighter">Height / Weight</span>
                <span class="text-sm font-bold">{{ $cast['height'] }}cm / {{ $cast['weight'] ?? '--' }}kg</span>
            </div>
            <div>
                <span class="text-gold text-[10px] block uppercase tracking-tighter">B / W / H</span>
                <span class="text-sm font-bold">{{ $cast['b'] }} / {{ $cast['w'] }} / {{ $cast['h'] }}</span>
            </div>
        </div>

        {{-- 自己紹介：アコーディオン形式 --}}
        <div class="detail-accordion border border-white/10 rounded-xl overflow-hidden bg-white/5">
            <div class="p-4 text-sm font-bold border-b border-white/10 flex justify-between cursor-pointer" onclick="toggleAccordion(this)">
                <span>自己紹介 / PR</span>
                <i class="fas fa-minus"></i>
            </div>
            <div class="p-4 text-xs opacity-80 leading-relaxed accordion-body">
                {!! nl2br(e($cast['pr'])) !!}
            </div>
        </div>

        {{-- レビューエリア --}}
        <div class="reviews-area">
            <h3 class="text-gold text-center text-xs font-bold uppercase mb-4 tracking-widest">Reviews</h3>
            @if(isset($cast['reviews']) && count($cast['reviews']) > 0)
                @foreach($cast['reviews'] as $rev)
                    <div class="rev-bubble-yellow p-3 rounded-2xl mb-3 text-gray-800">
                        <div class="text-xs font-bold mb-1">
                            @for($i=1; $i<=5; $i++)
                                <i class="{{ $i <= $rev['score'] ? 'fas' : 'far' }} fa-star"></i>
                            @endfor
                        </div>
                        <p class="text-xs leading-relaxed">{{ $rev['text'] }}</p>
                    </div>
                @endforeach
            @else
                <p class="text-center text-xs opacity-50">まだレビューはありません</p>
            @endif
        </div>
    </div>
</div>

<script>
function toggleAccordion(el) {
    const body = el.nextElementSibling;
    const icon = el.querySelector('i');
    if (body.style.display === 'none') {
        body.style.display = 'block';
        icon.className = 'fas fa-minus';
    } else {
        body.style.display = 'none';
        icon.className = 'fas fa-plus';
    }
}
</script>
@endsection