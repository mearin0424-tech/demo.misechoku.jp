@extends('layouts.app-v2')

@section('title', '採用ステータス管理')

@push('styles')
<style>
    .recruit-container {
        max-width: var(--max-content-width);
        margin: 0 auto;
        padding: 12px var(--content-padding-x) 24px;
        box-sizing: border-box;
    }
    .status-card {
        background: var(--color-card);
        border: 1px solid var(--color-border);
        border-radius: 15px;
        margin-bottom: 10px;
        overflow: hidden;
    }
    .status-item {
        display: flex;
        padding: 12px 14px;
        align-items: center;
        text-decoration: none;
        color: inherit;
    }
    .cast-thumb-sm {
        width: 50px; height: 50px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 15px;
        border: 1px solid var(--color-gold);
    }
    .status-info { flex: 1; min-width: 0; }
    .name-row { display: flex; align-items: baseline; gap: 8px; margin-bottom: 4px; }
    .cast-name { font-weight: bold; color: #fff; font-size: 1rem; }
    .cast-age { font-size: 0.75rem; color: #888; }
    .last-msg { font-size: 0.8rem; color: #aaa; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    
    .status-badge-area { text-align: right; margin-left: 10px; }
    .badge-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: bold;
        background: #333;
        color: #ccc;
    }
    .badge-active { background: #1e3a1e; color: #4caf50; } /* 契約中 */
    .badge-attention { background: #2a2a2a; color: var(--color-accent); } /* 面談予定など */

    .date-text { font-size: 0.65rem; color: #666; margin-top: 5px; display: block; }
</style>
@endpush

@section('content')
<div class="recruit-container">
    <h2 class="serif-font" style="color:var(--color-gold); margin-bottom:20px; font-size:1.2rem;">
        <i class="fas fa-user-check"></i> 採用ステータス一覧
    </h2>

    @forelse($matchingList as $item)
    <div class="status-card">
        <a href="{{ route('shop.talk.room', ['id' => $item['id'], 'talk_topic' => 'other', 'initiate' => 1]) }}" class="status-item">
            <img loading="lazy" decoding="async" src="{{ $item['img'] ?? asset('assets/images/common/no-image.png') }}" class="cast-thumb-sm" onerror="this.src='{{ asset('assets/images/common/user-default.svg') }}'">
            <div class="status-info">
                <div class="name-row">
                    <span class="cast-name">{{ $item['cast_name'] }}</span>
                    <span class="cast-age">({{ $item['age'] }})</span>
                </div>
                <p class="last-msg">{{ $item['last_msg'] }}</p>
            </div>
            <div class="status-badge-area">
                <span class="badge-status {{ $item['status'] === '契約中' ? 'badge-active' : ($item['status'] === '面談予定' ? 'badge-attention' : '') }}">
                    {{ $item['status'] }}
                </span>
                <span class="date-text numeric-font">{{ $item['date'] }}</span>
            </div>
        </a>
    </div>
    @empty
        <div class="text-center py-20 opacity-40">
            現在、選考中のキャストはいません。
        </div>
    @endforelse
</div>
@endsection