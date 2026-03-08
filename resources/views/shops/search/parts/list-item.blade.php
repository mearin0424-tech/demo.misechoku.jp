<li class="connection-item">
    <img src="{{ $item['img'] }}" class="conn-thumb">
    <div class="conn-info">
        <div class="conn-name">{{ $item['name'] }} ({{ $item['age'] }})</div>
    </div>
    {{-- カード全体をリンクにする対応済み --}}
    @if(Route::has('shop.profile.cast.show'))
        <a href="{{ route('shop.profile.cast.show', $item['id']) }}" class="conn-action-btn">詳細</a>
    @else
        <span class="text-xs opacity-30">詳細準備中</span>
    @endif
</li>