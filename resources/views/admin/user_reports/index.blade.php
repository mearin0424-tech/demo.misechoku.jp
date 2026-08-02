@extends('layouts.admin')

@section('title', 'ユーザー通報管理')

@section('content')
<div class="content-wrap">
    <div class="content-header">
        <h1 class="content-title"><i class="fas fa-flag"></i> ユーザー通報管理</h1>
        <p class="content-subtitle">キャスト・店舗から寄せられた通報を確認・対応します。</p>
    </div>

    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    {{-- ステータスフィルタ --}}
    @php
        $tabs = [
            ['key' => 'all',       'label' => 'すべて',   'count' => array_sum($counts)],
            ['key' => 'pending',   'label' => '未対応',   'count' => $counts['pending']],
            ['key' => 'in_review', 'label' => '対応中',   'count' => $counts['in_review']],
            ['key' => 'resolved',  'label' => '完了',     'count' => $counts['resolved']],
            ['key' => 'dismissed', 'label' => '却下',     'count' => $counts['dismissed']],
        ];
    @endphp
    <div class="filter-chips" style="display:flex; gap:8px; flex-wrap:wrap; margin: 12px 0 18px;">
        @foreach($tabs as $t)
            <a href="{{ route('admin.user_reports.index', ['status' => $t['key']]) }}"
               class="chip {{ $currentTab === $t['key'] ? 'is-active' : '' }}"
               style="padding: 6px 14px; border-radius: 999px; border: 1px solid rgba(124,58,237,0.3); background: {{ $currentTab === $t['key'] ? '#7c3aed' : 'transparent' }}; color: {{ $currentTab === $t['key'] ? '#fff' : '#4a4560' }}; font-size: 0.85rem; text-decoration: none; font-weight: 600;">
                {{ $t['label'] }} <span style="opacity: 0.75;">({{ $t['count'] }})</span>
            </a>
        @endforeach
    </div>

    @if($reports->isEmpty())
        <div style="padding: 40px; text-align: center; color: #8b84a1; background: #faf7ff; border-radius: 12px;">
            該当する通報はありません。
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($reports as $r)
                @php
                    $reporterName = $names[$r->reporter_type . ':' . $r->reporter_id] ?? $r->reporter_id;
                    $targetName   = $names[$r->target_type . ':' . $r->target_id] ?? $r->target_id;
                    $statusColor  = match ((int) $r->status) {
                        0 => ['#dc2626', 'rgba(220,38,38,0.10)'],
                        1 => ['#b45309', 'rgba(180,83,9,0.10)'],
                        2 => ['#059669', 'rgba(5,150,105,0.10)'],
                        3 => ['#6b7280', 'rgba(107,114,128,0.10)'],
                    };
                @endphp
                <article style="border: 1px solid rgba(124,58,237,0.18); border-radius: 12px; padding: 16px; background: #fff;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:6px;">
                                <span style="padding:3px 10px; border-radius:999px; background:{{ $statusColor[1] }}; color:{{ $statusColor[0] }}; font-size:0.72rem; font-weight:800;">
                                    {{ $r->statusLabel() }}
                                </span>
                                <span style="padding:3px 10px; border-radius:999px; background:rgba(124,58,237,0.10); color:#6d28d9; font-size:0.72rem; font-weight:700;">
                                    {{ $reasonLabels[$r->reason] ?? $r->reason }}
                                </span>
                                <span style="font-size:0.72rem; color:#8b84a1;">
                                    #{{ $r->id }} · {{ $r->created_at?->format('Y/m/d H:i') }}
                                </span>
                            </div>
                            <p style="margin:0 0 4px; font-size:0.92rem; font-weight:700; color:#1e1a30;">
                                <span style="color:#8b84a1; font-weight:500;">通報者:</span> {{ $reporterName }}
                                （{{ $r->reporter_type === 'cast' ? 'キャスト' : '店舗' }}: {{ $r->reporter_id }}）
                            </p>
                            <p style="margin:0 0 8px; font-size:0.92rem; font-weight:700; color:#1e1a30;">
                                <span style="color:#8b84a1; font-weight:500;">対象:</span> {{ $targetName }}
                                （{{ $r->target_type === 'cast' ? 'キャスト' : '店舗' }}: {{ $r->target_id }}）
                            </p>
                            @if($r->detail)
                                <div style="padding:10px 12px; background:#faf7ff; border-left:3px solid #7c3aed; border-radius:6px; margin:8px 0;">
                                    <p style="margin:0; font-size:0.84rem; color:#4a4560; line-height:1.6; white-space:pre-wrap;">{{ $r->detail }}</p>
                                </div>
                            @endif
                            @if($r->context_type === 'talk' && $r->context_message_id)
                                <p style="margin:6px 0 0; font-size:0.74rem; color:#8b84a1;">
                                    <i class="fas fa-comment"></i> トーク由来（message #{{ $r->context_message_id }}）
                                </p>
                            @endif
                            @if($r->admin_note)
                                <div style="padding:10px 12px; background:#fef3c7; border:1px dashed #b45309; border-radius:6px; margin:8px 0 0;">
                                    <p style="margin:0; font-size:0.82rem; color:#78350f;">
                                        <strong>運営メモ:</strong><br>{{ $r->admin_note }}
                                    </p>
                                    @if($r->handled_at)
                                        <p style="margin:6px 0 0; font-size:0.7rem; color:#a16207;">
                                            {{ $r->handled_at->format('Y/m/d H:i') }} 更新
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 対応フォーム --}}
                    <form method="POST" action="{{ route('admin.user_reports.status', ['id' => $r->id]) }}"
                          style="margin-top:12px; padding-top:12px; border-top:1px solid rgba(124,58,237,0.10); display:flex; gap:8px; flex-wrap:wrap; align-items:end;">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ $currentTab }}">
                        <div style="flex:1; min-width:200px;">
                            <label style="display:block; font-size:0.72rem; color:#8b84a1; margin-bottom:4px;">運営メモ（任意）</label>
                            <textarea name="admin_note" rows="2" placeholder="対応履歴のメモ..."
                                      style="width:100%; padding:8px 10px; border-radius:8px; border:1px solid rgba(124,58,237,0.24); font-size:0.84rem; resize:vertical;">{{ old('admin_note', $r->admin_note) }}</textarea>
                        </div>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            @foreach ([
                                ['status' => 1, 'label' => '対応中に変更', 'color' => '#b45309', 'bg' => 'rgba(180,83,9,0.10)'],
                                ['status' => 2, 'label' => '完了', 'color' => '#059669', 'bg' => 'rgba(5,150,105,0.10)'],
                                ['status' => 3, 'label' => '却下', 'color' => '#6b7280', 'bg' => 'rgba(107,114,128,0.10)'],
                            ] as $act)
                                @if((int) $r->status !== $act['status'])
                                    <button type="submit" name="status" value="{{ $act['status'] }}"
                                            style="padding:8px 14px; border-radius:8px; border:1px solid {{ $act['color'] }}44; background:{{ $act['bg'] }}; color:{{ $act['color'] }}; font-size:0.82rem; font-weight:700; cursor:pointer;">
                                        {{ $act['label'] }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </form>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
