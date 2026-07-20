@extends('layouts.app-v2')

@section('title', 'TALK')
@section('body-class', 'page-talk page-talk-list')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}?v=20260720-tpl-slide">
<link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">
@endpush

@section('content')
@php
    $isCast = request()->is('cast/*');
    $requestTabText = $isCast ? 'オファー' : '過去のやり取り';
    $targetRoute = $isCast ? 'cast.talk.room' : 'shop.talk.room';
    // $profileRoute はコントローラーから渡される（キャスト→お店詳細、お店→キャスト詳細）
@endphp

<div class="has-sub-header">
    @include('layouts.parts.sub-header', [
        'tabs' => [
            ['id' => 'pane-ongoing', 'label' => 'やり取り中', 'active' => true],
            ['id' => 'pane-requests', 'label' => $requestTabText, 'active' => false]
        ]
    ])
</div>

<div class="talk-list-container tab-page-body">
        {{-- パネル1：やり取り中 --}}
        <div id="pane-ongoing" class="tab-pane active">
            @forelse($ongoingTalks as $index => $talk)
                <div class="talk-item" data-partner-id="{{ $talk['partner_id'] }}" data-original-index="{{ $index }}">
                    <a href="{{ route($targetRoute, $talk['partner_id']) }}" class="talk-item-main">
                        <img loading="lazy" decoding="async" src="{{ $talk['avatar'] }}" class="talk-avatar" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($talk['name']) }}&background=4d1a1a&color=fff';">
                        <div class="talk-info">
                            <div class="talk-header">
                                <span class="talk-name">{{ $talk['name'] }}</span>
                                <span class="talk-time">{{ $talk['last_time'] }}</span>
                            </div>
                            <div class="talk-last-msg-row">
                                <p class="talk-last-msg">{{ $talk['last_message'] }}</p>
                                @if(isset($talk['unread_count']) && $talk['unread_count'] > 0)
                                    <span class="unread-badge">{{ $talk['unread_count'] }}</span>
                                @endif
                            </div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="talk-status">{{ $talk['status_label'] ?? 'やり取り中' }}</span>
                                @if(!empty($talk['has_fulltime_request_badge']))
                                    <span class="unread-badge" style="background:linear-gradient(135deg,#c4b5fd,#a78bfa 45%,#7c3aed);color:#1a0814;">本入店希望</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    <button type="button" class="talk-pin-btn" aria-label="トークをピン留め" aria-pressed="false">
                        <i class="fas fa-thumbtack"></i>
                    </button>
                </div>
            @empty
                <div class="no-messages text-center py-10 opacity-50">やり取り中のメッセージはありません</div>
            @endforelse
        </div>

        {{-- パネル2：オファー / 過去のやり取り --}}
        <div id="pane-requests" class="tab-pane">
            @forelse($requestTalks as $talk)
                @if($isCast)
                    <div class="request-card">
                        @if(Route::has($profileRoute))
                            <a href="{{ route($profileRoute, $talk['profile_id'] ?? $talk['partner_id']) }}" class="request-upper-link">
                        @else
                            <div class="request-upper-link">
                        @endif
                            <div class="request-main">
                                <img loading="lazy" decoding="async" src="{{ $talk['avatar'] }}" class="request-img">
                                <div class="request-content">
                                    <div class="request-top-row">
                                        <div class="name">{{ $talk['name'] }}@if(isset($talk['age']) && $talk['age'] !== null) ({{ $talk['age'] }})@endif</div>
                                        @if(!empty($talk['last_time']))
                                            <span class="request-time">{{ $talk['last_time'] }}</span>
                                        @endif
                                    </div>
                                    <div class="request-meta-row">
                                        <span class="request-status">{{ $talk['status_label'] ?? $requestTabText }}</span>
                                        @if(!empty($talk['has_fulltime_request_badge']))
                                            <span class="unread-badge" style="margin-left:8px; background:linear-gradient(135deg,#c4b5fd,#a78bfa 45%,#7c3aed);color:#1a0814;">本入店希望</span>
                                        @endif
                                    </div>
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
                @else
                    <div class="talk-item" data-partner-id="{{ $talk['partner_id'] }}">
                        <a href="{{ route($targetRoute, $talk['partner_id']) }}" class="talk-item-main">
                            <img loading="lazy" decoding="async" src="{{ $talk['avatar'] }}" class="talk-avatar" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($talk['name']) }}&background=4d1a1a&color=fff';">
                            <div class="talk-info">
                                <div class="talk-header">
                                    <span class="talk-name">{{ $talk['name'] }}</span>
                                    <span class="talk-time">{{ $talk['last_time'] }}</span>
                                </div>
                                <div class="talk-last-msg-row">
                                    <p class="talk-last-msg">{{ $talk['last_message'] }}</p>
                                    @if(isset($talk['unread_count']) && $talk['unread_count'] > 0)
                                        <span class="unread-badge">{{ $talk['unread_count'] }}</span>
                                    @endif
                                </div>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="talk-status">{{ $talk['status_label'] ?? $requestTabText }}</span>
                                    @if(!empty($talk['has_fulltime_request_badge']))
                                        <span class="unread-badge" style="background:linear-gradient(135deg,#c4b5fd,#a78bfa 45%,#7c3aed);color:#1a0814;">本入店希望</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
            @empty
                <div class="no-messages text-center py-10 opacity-50">{{ $requestTabText }}はありません</div>
            @endforelse
        </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== トークピン留め（LINEのように上部固定） =====
    const isCastPortal = {!! $isCast ? 'true' : 'false' !!};
    const pinStorageKey = isCastPortal ? 'talk_pins_cast' : 'talk_pins_shop';
    const ongoingPane = document.getElementById('pane-ongoing');

    function loadPinnedIds() {
        try {
            const raw = localStorage.getItem(pinStorageKey);
            if (!raw) return [];
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function savePinnedIds(ids) {
        try {
            localStorage.setItem(pinStorageKey, JSON.stringify(ids));
        } catch (e) {
            // ignore
        }
    }

    function applyPinState() {
        if (!ongoingPane) return;
        const pinnedIds = loadPinnedIds();
        const items = ongoingPane.querySelectorAll('.talk-item');
        items.forEach(function(item) {
            const id = item.getAttribute('data-partner-id');
            const pinBtn = item.querySelector('.talk-pin-btn');
            const isPinned = pinnedIds.includes(String(id));
            if (isPinned) {
                item.classList.add('is-pinned');
                if (pinBtn) {
                    pinBtn.setAttribute('aria-pressed', 'true');
                }
            } else {
                item.classList.remove('is-pinned');
                if (pinBtn) {
                    pinBtn.setAttribute('aria-pressed', 'false');
                }
            }
        });
    }

    function applyPinnedOrder() {
        if (!ongoingPane) return;
        const items = Array.from(ongoingPane.querySelectorAll('.talk-item'));
        if (items.length === 0) return;

        items.sort(function(a, b) {
            const aPinned = a.classList.contains('is-pinned');
            const bPinned = b.classList.contains('is-pinned');
            if (aPinned && !bPinned) return -1;
            if (!aPinned && bPinned) return 1;
            const aIndex = parseInt(a.getAttribute('data-original-index') || '0', 10);
            const bIndex = parseInt(b.getAttribute('data-original-index') || '0', 10);
            return aIndex - bIndex;
        });

        items.forEach(function(item) {
            ongoingPane.appendChild(item);
        });
    }

    function togglePin(item) {
        const id = item.getAttribute('data-partner-id');
        if (!id) return;
        let pinnedIds = loadPinnedIds();
        const strId = String(id);
        if (pinnedIds.includes(strId)) {
            pinnedIds = pinnedIds.filter(function(v) { return v !== strId; });
        } else {
            pinnedIds.push(strId);
        }
        savePinnedIds(pinnedIds);
        applyPinState();
        applyPinnedOrder();
    }

    if (ongoingPane) {
        ongoingPane.querySelectorAll('.talk-pin-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const item = this.closest('.talk-item');
                if (!item) return;
                togglePin(item);
            });
        });

        // 初期状態の反映
        applyPinState();
        applyPinnedOrder();
    }
});
</script>
@endpush