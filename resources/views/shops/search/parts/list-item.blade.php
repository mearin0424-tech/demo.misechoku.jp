@php
    $profileUrl = Route::has('shop.castprofileview.show') ? route('shop.castprofileview.show', $item['id']) : '#';
    $hitokoto = trim((string) ($item['hitokoto'] ?? ''));
    $hitokotoTime = (string) ($item['hitokoto_updated_at'] ?? '');
@endphp
<li class="connection-item connection-item--clickable connection-item--search">
    <a href="{{ $profileUrl }}" class="connection-item__link">
        <img src="{{ $item['img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="conn-thumb">
        <div class="conn-info">
            <div class="conn-info__head">
                <div class="conn-name">{{ $item['name'] ?? '' }}@if(!empty($item['age'])) ({{ $item['age'] }})@endif</div>
                @if($hitokotoTime !== '')
                    <span class="conn-info__time">{{ $hitokotoTime }}</span>
                @endif
            </div>
            @if(!empty($item['pref']) || !empty($item['city']))
                <div class="conn-info__meta">{{ $item['pref'] ?? '' }}{{ $item['city'] ?? '' }}</div>
            @endif
            @if($hitokoto !== '')
                <div class="conn-info__hitokoto">{!! nl2br(e($hitokoto)) !!}</div>
            @endif
        </div>
        <span class="connection-item__arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
    </a>
</li>
