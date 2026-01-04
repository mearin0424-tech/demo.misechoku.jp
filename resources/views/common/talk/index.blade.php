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
                @if(!empty($talk['avatar']) && file_exists(public_path($talk['avatar'])))
    <               img src="{{ asset($talk['avatar']) }}" class="talk-avatar">
                @else
                    <div class="talk-avatar flex items-center justify-center bg-[#4d1a1a]">
                        <i class="fas fa-user text-[#d4af37]"></i>
                    </div>
                @endif
                <div class="talk-info">
                    <div class="talk-header">
                        <span class="talk-name">{{ $talk['name'] }}</span>
                        <span class="talk-time">{{ $talk['last_time'] }}</span>
                    </div>
                    {{-- 前略：ループの中身を以下のように調整 --}}
                    <div class="flex justify-between items-center">
                        <div class="flex items-center min-width-0">
                            <p class="talk-last-msg">{{ $talk['last_message'] }}</p>
                            
                            {{-- 自分が最後に送った場合のみ既読状態を表示 --}}
                            @if($talk['last_message_by_me'])
                                @if($talk['is_read'])
                                    <span class="talk-status">既読</span>
                                @else
                                    <span class="talk-status unread">送付済</span>
                                @endif
                            @endif
                        </div>

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
                @if(!empty($talk['avatar']) && file_exists(public_path($talk['avatar'])))
    <               img src="{{ asset($talk['avatar']) }}" class="talk-avatar">
                @else
                    <div class="talk-avatar flex items-center justify-center bg-[#4d1a1a]">
                        <i class="fas fa-user text-[#d4af37]"></i>
                    </div>
                @endif
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