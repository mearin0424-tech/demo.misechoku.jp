@php $profileRoute = $profileRoute ?? 'shop.castprofileview.show'; @endphp
<a href="{{ route($profileRoute, $c['id']) }}" class="cast-list-card">
    <div class="card-thumb">
        <img src="{{ $c['img'] ?? asset('storage/mock/casts/'.$c['id'].'-1.png') }}" alt="{{ $c['name'] }}">
    </div>
    <div class="card-info">
        <div class="info-header">
            <span class="name serif-font">{{ $c['name'] }}</span>
            @if(isset($c['age']))
            <span class="age numeric-font opacity-70">({{ $c['age'] }})</span>
            @endif
        </div>
        <div class="info-sub opacity-70">
            @if(!empty($c['is_match']))
                <span class="text-gold font-bold"><i class="fas fa-heart"></i> マッチング中</span>
            @else
                {{ $c['created_at'] ?? '' }} にライク
            @endif
        </div>
        <div class="info-specs numeric-font opacity-70">
            {{ $c['pref'] ?? '' }}{{ $c['city'] ?? '' }}
        </div>
    </div>
    <div class="card-arrow"><i class="fas fa-chevron-right"></i></div>
</a>
