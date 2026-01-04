<div class="section-label mb-4 text-xs font-bold opacity-50 uppercase tracking-widest">Saved Casts</div>

@forelse($keeps ?? [] as $cast)
    <div class="interaction-card">
        <img src="{{ asset('storage/mock/casts/'.$cast['id'].'-1.png') }}" class="avatar-sm">
        <div class="info-sm">
            <div class="name">{{ $cast['name'] }} ({{ $cast['age'] }})</div>
            <div class="meta">{{ $cast['updated_at'] }} に保存</div>
        </div>
        <a href="{{ route('shop.cast.show', $c['id']) }}" class="cast-list-card glass-card">
    <div class="card-thumb">
        <img src="{{ $c['img'] ?: asset('assets/images/common/user-default.svg') }}" alt="">
    </div>
    <div class="card-info">
        <div class="info-header">
            <span class="name serif-font">{{ $c['name'] }}</span>
            <span class="age numeric-font opacity-70">({{ $c['age'] }})</span>
        </div>
        <div class="info-sub opacity-70">
            <span class="profession">{{ $c['profession'] ?: '未設定' }}</span> / 
            <span class="area">{{ $c['pref'] }}{{ $c['city'] }}</span>
        </div>
        <div class="info-specs numeric-font opacity-70">
            T{{ $c['height'] ?: '--' }} B{{ $c['b'] ?: '--' }} W{{ $c['w'] ?: '--' }} H{{ $c['h'] ?: '--' }}
        </div>
    </div>
    <div class="card-arrow"><i class="fas fa-chevron-right"></i></div>
</a>
    </div>
@empty
    <div class="text-center py-20 text-sm opacity-40">キープしているキャストはいません。</div>
@endforelse