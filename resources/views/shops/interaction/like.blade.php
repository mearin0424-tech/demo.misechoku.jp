@php $profileRoute = $profileRoute ?? 'shop.castprofileview.show'; @endphp
<a href="{{ route($profileRoute, $c['id']) }}" class="cast-list-card">
    <div class="card-thumb">
        <img src="{{ $c['img'] ?? asset('assets/images/common/no-image.png') }}" alt="{{ $c['name'] }}" onerror="this.onerror=null; this.src='{{ asset('assets/images/common/user-default.svg') }}'">
    </div>
    <div class="card-info">
        <div class="info-header info-header-one-line">
            <span class="name serif-font">{{ $c['name'] }}</span>
            @if(isset($c['age']))
            <span class="age numeric-font opacity-70">({{ $c['age'] }})</span>
            @endif
            @if(!empty($c['is_match']))
                <span class="match-badge text-gold font-bold"><i class="fas fa-heart"></i> マッチング中</span>
            @endif
        </div>
        @if(empty($c['is_match']))
        <div class="info-sub opacity-70">
            {{ $c['created_at'] ?? '' }} にライク
        </div>
        @endif
    </div>
    <div class="card-arrow"><i class="fas fa-chevron-right"></i></div>
</a>
