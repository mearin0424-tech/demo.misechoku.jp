@extends('layouts.admin')

@section('title', 'キャスト管理')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', ['eyebrow' => 'CASTS', 'title' => 'キャスト管理'])
        <p class="admin-description">
            登録されているキャストアカウントの一覧です。本人確認、最終ログイン、運用実績（請求／振込）をキャスト単位で確認できます。本名・連絡先などの非公開情報は詳細画面で個別に解除して閲覧します。
        </p>

        @if(session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>キャスト名</th>
                        <th>登録日</th>
                        <th>最終ログイン</th>
                        <th>本人確認</th>
                        <th>状態</th>
                        <th>運用実績（キャスト単位）</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($casts as $cast)
                        @php $isSuspended = (int) ($cast['account_status'] ?? 0) === 2; @endphp
                        <tr class="{{ $isSuspended ? 'is-suspended' : '' }}">
                            <td><code>{{ $cast['id'] }}</code></td>
                            <td>
                                <a href="{{ route('admin.casts.show', $cast['id']) }}">{{ $cast['name'] }}</a>
                            </td>
                            <td>{{ $cast['registered_at'] ? \Illuminate\Support\Carbon::parse($cast['registered_at'])->format('Y-m-d') : '—' }}</td>
                            <td>{{ $cast['last_login_at'] ? \Illuminate\Support\Carbon::parse($cast['last_login_at'])->format('Y-m-d H:i') : '—' }}</td>
                            <td>{{ $cast['identity_status'] }}</td>
                            <td>
                                @if($isSuspended)
                                    <span class="admin-status-badge is-danger"><i class="fas fa-ban"></i> 停止中</span>
                                @elseif((int) ($cast['account_status'] ?? 0) === 1)
                                    <span class="admin-status-badge is-success">有効</span>
                                @else
                                    <span class="admin-status-badge is-inactive">仮登録</span>
                                @endif
                            </td>
                            <td class="u-min-w-340">
                                @php($summary = $cast['operation_summary'] ?? null)
                                @if($summary)
                                    <div class="u-text-pre">
                                        <div>請求書送付: <strong>{{ number_format($summary['invoice_issued']) }}</strong> 件 / 振込実行: <strong>{{ number_format($summary['cast_transferred']) }}</strong> 件 / 完了: <strong>{{ number_format($summary['completed']) }}</strong> 件</div>
                                        <div class="text-muted text-xs u-mt-4">最新: {{ $summary['latest_status_label'] }}{{ !empty($summary['latest_updated_at']) ? '（' . $summary['latest_updated_at'] . '）' : '' }}</div>
                                    </div>
                                @else
                                    <span class="text-muted">請求・振込フロー実績なし</span>
                                @endif
                            </td>
                            <td>
                                <div class="u-flex u-flex-wrap u-gap-6">
                                    <a href="{{ route('admin.casts.show', $cast['id']) }}" class="btn-action btn-action-secondary">
                                        <i class="fas fa-circle-info"></i> 詳細
                                    </a>
                                    @if($isSuspended)
                                        <form method="POST" action="{{ route('admin.casts.unsuspend', $cast['id']) }}" style="display:inline;" onsubmit="return confirm('このキャストアカウントの停止を解除しますか？');">
                                            @csrf
                                            <button type="submit" class="btn-action btn-action-secondary">
                                                <i class="fas fa-rotate-left"></i> 停止解除
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.casts.suspend', $cast['id']) }}" style="display:inline;" onsubmit="return confirm('このキャストアカウントを停止します。停止中はログイン後に「停止中」表示と問合せ送信のみ可能になります。よろしいですか？');">
                                            @csrf
                                            <button type="submit" class="btn-action btn-action-danger">
                                                <i class="fas fa-ban"></i> 停止
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">キャストアカウントがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
