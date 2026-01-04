@extends('layouts.app')

@section('title', 'TALK')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">
@endpush

@section('content')
@php
    $isCast = request()->is('cast/*');
    $requestTabText = $isCast ? 'オファー' : 'リクエスト';
    $targetRoute = $isCast ? 'cast.talk.room' : 'shop.talk.room';
    $profileRoute = $isCast ? 'cast.users.show' : 'profile.show';
@endphp

<div class="has-sub-header">
    @include('layouts.parts.sub-header', [
        'tabs' => [
            ['id' => 'pane-ongoing', 'label' => 'やり取り中', 'active' => true],
            ['id' => 'pane-requests', 'label' => $requestTabText, 'active' => false]
        ]
    ])

    <div class="talk-list-container">
        {{-- パネル1：やり取り中 --}}
        <div id="pane-ongoing" class="tab-pane active">
            @forelse($ongoingTalks as $talk)
                <a href="{{ route($targetRoute, $talk['partner_id']) }}" class="talk-item">
                    <img src="{{ asset($talk['avatar']) }}" class="talk-avatar" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($talk['name']) }}&background=4d1a1a&color=fff';">
                    <div class="talk-info">
                        <div class="talk-header">
                            <span class="talk-name">{{ $talk['name'] }}</span>
                            <span class="talk-time">{{ $talk['last_time'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="talk-last-msg">{{ $talk['last_message'] }}</p>
                            @if(isset($talk['unread_count']) && $talk['unread_count'] > 0)
                                <span class="unread-badge">{{ $talk['unread_count'] }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="no-messages text-center py-10 opacity-50">やり取り中のメッセージはありません</div>
            @endforelse
        </div>

        {{-- パネル2：リクエスト / オファー --}}
        <div id="pane-requests" class="tab-pane">
            @forelse($requestTalks as $talk)
                <div class="request-card">
                    @if(Route::has($profileRoute))
                        <a href="{{ route($profileRoute, $talk['partner_id']) }}" class="request-upper-link">
                    @else
                        <div class="request-upper-link">
                    @endif
                        <div class="request-main">
                            <img src="{{ asset($talk['avatar']) }}" class="request-img">
                            <div class="request-content">
                                <div class="name">{{ $talk['name'] }} ({{ $talk['age'] }})</div>
                                <div class="request-msg-preview">{{ $talk['last_message'] }}</div>
                            </div>
                        </div>
                    @if(Route::has($profileRoute))
                        </a>
                    @else
                        </div>
                    @endif
                    <div class="request-actions">
                        <a href="{{ route($targetRoute, $talk['partner_id']) }}" class="btn-action btn-approve">承認</a>
                        <button class="btn-action btn-reject js-reject-request">拒否</button>
                    </div>
                </div>
            @empty
                <div class="no-messages text-center py-10 opacity-50">{{ $requestTabText }}はありません</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
@endpush