@extends('layouts.app')

@section('title', 'メッセージ一覧')

@push('styles')
<style>
    .talk-list-container { padding: 10px; }
    .talk-item {
        display: flex; align-items: center; padding: 15px;
        background: #220a0a; border: 1px solid #4d1a1a;
        border-radius: 15px; margin-bottom: 10px;
        text-decoration: none; transition: 0.3s;
    }
    .talk-item:active { transform: scale(0.98); background: #3d1414; }
    .talk-avatar {
        width: 60px; height: 60px; border-radius: 50%;
        object-fit: cover; border: 1px solid #d4af37; margin-right: 15px;
    }
    .talk-info { flex: 1; min-width: 0; }
    .talk-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 5px; }
    .talk-name { font-weight: bold; color: #f5ecec; font-size: 1.1rem; }
    .talk-time { font-size: 0.75rem; color: #888; }
    .talk-last-msg {
        font-size: 0.85rem; color: #d1c1c1;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .unread-badge {
        background: #b91c1c; color: #fff; font-size: 0.7rem;
        padding: 2px 8px; border-radius: 10px; margin-left: 10px;
    }
</style>
@endpush

@section('content')
<div class="talk-list-container">
    @forelse($talks as $talk)
        @php
            // ルート名を動的に判定（アクセスしているURLに/cast/が含まれるかどうか）
            $isCast = request()->is('cast/*');
            $targetRoute = $isCast ? 'cast.talk.room' : 'shop.talk.room';
        @endphp
        <a href="{{ route($targetRoute, $talk['partner_id']) }}" class="talk-item">
            <img src="{{ asset($talk['avatar']) }}" class="talk-avatar" onerror="this.src='/assets/images/common/placeholder.png'">
            <div class="talk-info">
                <div class="talk-header">
                    <span class="talk-name">{{ $talk['name'] }}</span>
                    <span class="talk-time">{{ $talk['last_time'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <p class="talk-last-msg">{{ $talk['last_message'] }}</p>
                    @if($talk['unread_count'] > 0)
                        <span class="unread-badge">{{ $talk['unread_count'] }}</span>
                    @endif
                </div>
            </div>
        </a>
    @empty
        <div class="text-center py-20 text-gray-500">
            メッセージはありません
        </div>
    @endforelse
</div>
@endsection