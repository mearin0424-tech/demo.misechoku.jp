<li class="connection-item">
    <img src="{{ $item['main_img'] ?? asset('assets/images/common/no-image.png') }}" class="conn-thumb">
    <div class="conn-info">
        <div class="conn-name">{{ $item['shop_name'] }}</div>
        <div class="text-xs text-gray-500">{{ $item['pref'] }}{{ $item['city'] }}</div>
    </div>
    @if(Route::has('cast.profile.show'))
        <a href="{{ route('cast.profile.show', $item['id']) }}" class="conn-action-btn">詳細</a>
    @else
        <span class="text-xs opacity-30">詳細準備中</span>
    @endif
</li>