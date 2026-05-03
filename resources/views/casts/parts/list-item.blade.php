@php
    $recruitUrl = Route::has('cast.recruit.show') ? route('cast.recruit.show', $item['id']) : '#';
    $hitokoto = trim((string) ($item['hitokoto'] ?? ''));
    $hitokotoTime = (string) ($item['hitokoto_updated_at'] ?? '');
@endphp
<li class="connection-item connection-item--clickable connection-item--search">
    <a href="{{ $recruitUrl }}" class="connection-item__link">
        <img src="{{ $item['main_img'] ?? asset('assets/images/common/no-image.png') }}" alt="" class="conn-thumb">
        <div class="conn-info">
            <div class="conn-info__head">
                <div class="conn-name">{{ $item['shop_name'] }}</div>
                @if($hitokotoTime !== '')
                    <span class="conn-info__time">{{ $hitokotoTime }}</span>
                @endif
            </div>
            <div class="conn-info__meta">{{ $item['pref'] ?? '' }}{{ $item['city'] ?? '' }}</div>
            @if($hitokoto !== '')
                <div class="conn-info__hitokoto">{!! nl2br(e($hitokoto)) !!}</div>
            @endif
        </div>
        <span class="connection-item__arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
    </a>
</li>
