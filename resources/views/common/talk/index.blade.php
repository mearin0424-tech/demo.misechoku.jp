@extends('layouts.app')

@section('title', 'TALK')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
@endpush

@section('content')
@php
    $isCast = request()->is('cast/*');
    $requestTabText = $isCast ? 'オファー' : 'リクエスト';
    $targetRoute = $isCast ? 'cast.talk.room' : 'shop.talk.room';
    $profileRoute = $isCast ? 'cast.users.show' : 'profile.show';
@endphp

{{-- タブメニュー --}}
<div class="talk-tabs-wrapper"> 
    <div class="talk-tabs">
        <div class="tab-item active" data-target="ongoing">やり取り中</div>
        <div class="tab-item" data-target="requests">{{ $requestTabText }}</div>
    </div>
</div>

<div class="talk-list-container">
    {{-- 1. やり取り中パネル --}}
    <div id="pane-ongoing" class="talk-content-pane active">
        @forelse($ongoingTalks as $talk)
            <a href="{{ route($targetRoute, $talk['partner_id']) }}" class="talk-item">
                @if(!empty($talk['avatar']))
                    <img src="{{ asset($talk['avatar']) }}" class="talk-avatar" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($talk['name']) }}&background=4d1a1a&color=fff';">
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
                        <div class="flex items-center min-width-0">
                            <p class="talk-last-msg">{{ $talk['last_message'] }}</p>
                            
                            @if(isset($talk['last_message_by_me']) && $talk['last_message_by_me'])
                                @if(isset($talk['is_read']) && $talk['is_read'])
                                    <span class="talk-status">既読</span>
                                @else
                                    <span class="talk-status unread">送付済</span>
                                @endif
                            @endif
                        </div>

                        @if(isset($talk['unread_count']) && $talk['unread_count'] > 0)
                            <span class="unread-badge">{{ $talk['unread_count'] }}</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="no-messages">
                <i class="fas fa-comments text-3xl opacity-20 mb-2 block"></i>
                <p>やり取り中のメッセージはありません</p>
            </div>
        @endforelse
    </div>

    {{-- 2. リクエスト / オファー パネル --}}
    <div id="pane-requests" class="talk-content-pane">
        @forelse($requestTalks as $talk)
            <div class="request-card" data-id="{{ $talk['partner_id'] }}">
                
                {{-- プロフィール詳細へのリンク（画像と情報を包む） --}}
                @if(Route::has($profileRoute))
                    <a href="{{ route($profileRoute, $talk['partner_id']) }}" class="request-upper-link no-underline">
                @else
                    {{-- ルートが存在しない場合はリンクにせず、コンソールに警告を出す用 --}}
                    <div class="request-upper-link">
                @endif
                    <div class="request-main">
                        <img src="{{ asset($talk['avatar']) }}" 
                             class="request-img" 
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($talk['name']) }}&background=4d1a1a&color=fff';">
                        
                        <div class="request-content">
                            <div class="request-top-row">
                                <div class="request-user-info">
                                    <div class="name">{{ $talk['name'] }} ({{ $talk['age'] }})</div>
                                    <div class="location"><i class="fas fa-map-marker-alt"></i>{{ $talk['location'] }}</div>
                                </div>
                                <span class="talk-time">{{ $talk['last_time'] }}</span>
                            </div>
                            
                            <div class="request-msg-preview">
                                {{ $talk['last_message'] }}
                            </div>
                        </div>
                    </div>
                @if(Route::has($profileRoute))
                    </a>
                @else
                    </div>
                @endif

                {{-- ボタンエリア --}}
                <div class="request-actions">
                    <a href="{{ route($targetRoute, $talk['partner_id']) }}" class="btn-action btn-approve">
                        <i class="fas fa-check"></i> 承認
                    </a>
                    <button type="button" class="btn-action btn-reject js-reject-request">
                        <i class="fas fa-times"></i> 拒否
                    </button>
                </div>
            </div>
        @empty
            <div class="no-messages">
                <i class="fas fa-paper-plane text-3xl opacity-20 mb-2 block"></i>
                <p>{{ $requestTabText }}はありません</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/talk-list.js') }}"></script>
@endpush