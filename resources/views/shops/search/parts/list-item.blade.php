<li class="connection-item">
    <img src="{{ $item['img'] }}" class="conn-thumb">
    <div class="conn-info">
        <div class="conn-name">{{ $item['name'] }} ({{ $item['age'] }})</div>
    </div>
    @if(Route::has('cast.profile.show'))
        <a href="{{ route('cast.profile.show', ['id' => $item['id']]) }}" class="conn-action-btn">詳細</a>
    @else
        <span class="text-xs opacity-30">詳細準備中</span>
    @endif
</li>