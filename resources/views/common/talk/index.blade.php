@extends('layouts.app')

@section('title', 'メッセージ一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
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