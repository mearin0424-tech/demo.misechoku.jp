@php
    $isCast = request()->is('cast/*');
    $targetRoute = $isCast ? 'cast.talk.room' : 'shop.talk.room';
@endphp

<a href="{{ route($targetRoute, $talk['partner_id']) }}" class="talk-item">
    <img src="{{ asset($talk['avatar']) }}" class="talk-avatar" onerror="this.src='{{ asset('assets/images/common/placeholder.png') }}'">
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