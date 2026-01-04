@extends('layouts.app')

@section('title', 'メッセージ一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
@endpush

@section('content')
@php
    $isCast = request()->is('cast/*');
    $requestTabText = $isCast ? 'オファー' : 'リクエスト';
    $targetRoute = $isCast ? 'cast.talk.room' : 'shop.talk.room';
@endphp

{{-- タブメニュー --}}
<div class="talk-tabs">
    <div class="tab-item active" data-target="ongoing">やり取り中</div>
    <div class="tab-item" data-target="requests">{{ $requestTabText }}</div>
</div>

<div class="talk-list-container">
    {{-- 1. やり取り中パネル --}}
    <div id="pane-ongoing" class="talk-content-pane active">
        @forelse($ongoingTalks as $talk)
            <a href="{{ route($targetRoute, $talk['partner_id']) }}" class="talk-item">
                <img src="{{ asset($talk['avatar']) }}" class="talk-avatar" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=No+Image&background=4d1a1a&color=fff';">
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
            <div class="no-messages">
                <i class="fas fa-comments"></i>
                <p>やり取り中のメッセージはありません</p>
            </div>
        @endforelse
    </div>

    {{-- 2. リクエスト / オファー パネル --}}
    <div id="pane-requests" class="talk-content-pane">
        @forelse($requestTalks as $talk)
            <a href="{{ route($targetRoute, $talk['partner_id']) }}" class="talk-item">
                <img src="{{ asset($talk['avatar']) }}" class="talk-avatar" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=No+Image&background=4d1a1a&color=fff';">
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
            <div class="no-messages">
                <i class="fas fa-paper-plane"></i>
                <p>{{ $requestTabText }}はありません</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/talk-list.js') }}"></script>
@endpush