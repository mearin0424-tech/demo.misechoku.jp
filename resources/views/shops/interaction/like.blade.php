<div class="section-label mb-4 text-xs font-bold opacity-50 uppercase tracking-widest">Like Interactions</div>

@forelse($likes ?? [] as $like)
    <div class="interaction-card">
        <img src="{{ asset('storage/mock/casts/'.$like['id'].'-1.png') }}" class="avatar-sm">
        <div class="info-sm">
            <div class="name">{{ $like['name'] }} ({{ $like['age'] }})</div>
            <div class="meta">
                @if($like['is_match'])
                    <span class="text-gold font-bold"><i class="fas fa-heart"></i> マッチング中</span>
                @else
                    {{ $like['created_at'] }} にライク
                @endif
            </div>
        </div>
        <div class="btn-action-sm">
            <i class="fas fa-heart {{ $like['is_match'] ? 'text-red-500' : '' }}"></i>
        </div>
    </div>
@empty
    <div class="text-center py-20 text-sm opacity-40">ライクの履歴はありません。</div>
@endforelse