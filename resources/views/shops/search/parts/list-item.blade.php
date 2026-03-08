@php
    $profileUrl = Route::has('shop.castprofileview.show') ? route('shop.castprofileview.show', $item['id']) : '#';
@endphp
<li class="connection-item connection-item--clickable">
    <a href="{{ $profileUrl }}" class="connection-item__link">
        <img src="{{ $item['img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="conn-thumb">
        <div class="conn-info">
            <div class="conn-name">{{ $item['name'] ?? '' }}@if(!empty($item['age'])) ({{ $item['age'] }})@endif</div>
        </div>
        <span class="connection-item__arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
    </a>
</li>
