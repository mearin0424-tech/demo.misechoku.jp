@php $profileRoute = $profileRoute ?? 'shop.castprofileview.show'; @endphp
<a href="{{ route($profileRoute, $c['id']) }}" class="cast-list-card">
    <div class="card-thumb">
        <img src="{{ $c['img'] ?? asset('assets/images/common/no-image.png') }}" alt="{{ $c['name'] }}" onerror="this.onerror=null; this.src='{{ asset('assets/images/common/user-default.svg') }}'">
    </div>
    <div class="card-info">
        <div class="info-header">
            <span class="name serif-font">{{ $c['name'] }}</span>
            @if(isset($c['age']))
            <span class="age numeric-font opacity-70">({{ $c['age'] }})</span>
            @endif
        </div>
        <div class="info-sub opacity-70">
            {{ $c['updated_at'] ?? '' }} に保存
        </div>
        <div class="info-specs numeric-font opacity-70">
            {{ $c['pref'] ?? '' }}{{ $c['city'] ?? '' }}
        </div>
    </div>
    <div class="card-arrow"><i class="fas fa-chevron-right"></i></div>
</a>
