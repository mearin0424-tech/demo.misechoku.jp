<div class="section-label mb-4 text-xs font-bold opacity-50 uppercase tracking-widest">Visitor History</div>

@forelse($footprints ?? [] as $fp)
    <div class="interaction-card">
        <img src="{{ asset('storage/mock/casts/'.$fp['id'].'-1.png') }}" class="avatar-sm">
        <div class="info-sm">
            <div class="name">{{ $fp['name'] }} ({{ $fp['age'] }})</div>
            <div class="meta">{{ $fp['viewed_at'] }} に閲覧</div>
        </div>
        <a href="{{ route('shop.cast.show', $fp['id']) }}" class="btn-action-sm">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
@empty
    <div class="text-center py-20 text-sm opacity-40">足あとはありません。</div>
@endforelse