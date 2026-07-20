@extends('layouts.admin')

@section('title', '運営操作ログ')

@section('content')
<div class="admin-page">
    <div class="u-flex-between u-flex-wrap u-gap-12">
        @include('admin.parts.page-title', [
            'eyebrow' => 'OPERATION LOG',
            'title' => '運営操作ログ',
            'info' => '
                <p>運営アカウントが実行した重要操作の監査ログです。</p>
                <ul>
                    <li>書類審査の承認／差戻し</li>
                    <li>アカウントの停止／停止解除</li>
                    <li>ロール権限の変更</li>
                    <li>非公開情報の解除</li>
                </ul>
                <p>改ざん防止のため <strong>追記専用</strong> として運用してください。</p>
            ',
        ])
        @include('admin.parts.back-link', ['url' => route('admin.admin-accounts.index'), 'label' => '運営アカウント管理へ戻る'])
    </div>

    <form method="GET" action="{{ route('admin.admin-accounts.operation-log') }}" class="admin-card admin-card-wide" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        <label style="display:flex; flex-direction:column; gap:4px;">
            <span style="font-size:.74rem; color:var(--admin-muted); font-weight:700;">アクション</span>
            <select name="action" style="height:40px; padding:0 12px; border:1px solid rgba(74,18,42,.18); border-radius:10px;">
                @foreach($actionOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['action'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label style="display:flex; flex-direction:column; gap:4px;">
            <span style="font-size:.74rem; color:var(--admin-muted); font-weight:700;">対象種別</span>
            <input type="text" name="target_type" value="{{ $filters['target_type'] }}" placeholder="cast / shop / role など" style="height:40px; padding:0 12px; border:1px solid rgba(74,18,42,.18); border-radius:10px; min-width:220px;">
        </label>
        <button type="submit" class="btn-action manage">
            <i class="fas fa-magnifying-glass"></i> 絞り込み
        </button>
        <a href="{{ route('admin.admin-accounts.operation-log') }}" class="btn-action btn-action-secondary">クリア</a>
    </form>

    <section class="admin-panel">
        <h2 class="admin-panel-title">直近のログ（{{ count($logs) }}件・最大500件）</h2>
        @if(empty($logs))
            <p class="admin-note u-mb-0">該当するログはありません。</p>
        @else
            <div class="table-wrapper">
                <table class="admin-table admin-table--stack">
                    <thead>
                        <tr>
                            <th class="u-w-140">日時</th>
                            <th>運営者</th>
                            <th>アクション</th>
                            <th>対象</th>
                            <th>概要</th>
                            <th class="u-w-110">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->created_at ? \Illuminate\Support\Carbon::parse($log->created_at)->format('Y-m-d H:i') : '—' }}</td>
                                <td data-label="運営者">
                                    <div>{{ $log->operator_email ?: '—' }}</div>
                                    @if(!empty($log->operator_role))
                                        <div class="u-fs-xs u-text-muted">{{ $log->operator_role }}</div>
                                    @endif
                                </td>
                                <td data-label="アクション">
                                    <span class="admin-status-badge {{ str_contains((string) $log->action, 'reject') || str_contains((string) $log->action, 'suspend') ? 'is-danger' : (str_contains((string) $log->action, 'approve') || str_contains((string) $log->action, 'unsuspend') ? 'is-success' : 'is-warning') }}">
                                        {{ $actionLabel($log->action) }}
                                    </span>
                                    <div class="u-fs-xs u-text-muted u-mt-4"><code>{{ $log->action }}</code></div>
                                </td>
                                <td data-label="対象">
                                    <div>{{ $log->target_type ?: '—' }}</div>
                                    @if(!empty($log->target_id))
                                        <code class="u-fs-xs">{{ $log->target_id }}</code>
                                    @endif
                                </td>
                                <td data-label="概要" class="u-text-pre">{{ $log->summary ?: '—' }}</td>
                                <td data-label="IP" class="u-fs-xs u-text-muted">{{ $log->ip_address ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
