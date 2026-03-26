@php
    $recruitUrl = Route::has('cast.recruit.show') ? route('cast.recruit.show', $item['id']) : '#';
@endphp
<li class="connection-item connection-item--clickable">
    <a href="{{ $recruitUrl }}" class="connection-item__link">
        <img src="{{ $item['main_img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="conn-thumb">
        <div class="conn-info">
            <div class="conn-name">{{ $item['shop_name'] }}</div>
            <div class="text-xs text-gray-500">{{ $item['pref'] ?? '' }}{{ $item['city'] ?? '' }}</div>
        </div>
        <span class="connection-item__arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
    </a>
</li>
