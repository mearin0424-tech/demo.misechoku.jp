@extends('layouts.admin')

@section('title', '店舗管理')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', ['eyebrow' => 'SHOPS', 'title' => '店舗管理'])
        <p class="admin-description">
            登録されている店舗アカウントの一覧です。書類確認状況、最終ログイン、運用実績（請求／入金／振込）を店舗単位で確認できます。詳細画面で非公開情報（口座・連絡先など）を解除できます。
        </p>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>店舗名</th>
                        <th>登録日</th>
                        <th>最終ログイン</th>
                        <th>書類提出</th>
                        <th>状態</th>
                        <th>運用実績（店舗単位）</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shops as $shop)
                        @php $isSuspended = (int) ($shop['account_status'] ?? 0) === 2; @endphp
                        <tr class="{{ $isSuspended ? 'is-suspended' : '' }}">
                            <td><code>{{ $shop['id'] }}</code></td>
                            <td><a href="{{ route('admin.shops.show', $shop['id']) }}">{{ $shop['name'] }}</a></td>
                            <td>{{ $shop['registered_at'] ? \Illuminate\Support\Carbon::parse($shop['registered_at'])->format('Y-m-d') : '—' }}</td>
                            <td>{{ $shop['last_login_at'] ? \Illuminate\Support\Carbon::parse($shop['last_login_at'])->format('Y-m-d H:i') : '—' }}</td>
                            <td>{{ $shop['document_status'] }}</td>
                            <td>
                                @if($isSuspended)
                                    <span class="admin-status-badge is-danger"><i class="fas fa-ban"></i> 停止中</span>
                                @elseif((int) ($shop['account_status'] ?? 0) === 1)
                                    <span class="admin-status-badge is-success">有効</span>
                                @else
                                    <span class="admin-status-badge is-inactive">仮登録</span>
                                @endif
                            </td>
                            <td class="u-min-w-360">
                                @php($summary = $shop['operation_summary'] ?? null)
                                @if($summary)
                                    <div class="u-text-pre">
                                        <div>請求書送付: <strong>{{ number_format($summary['invoice_issued']) }}</strong> 件 / 入金確認: <strong>{{ number_format($summary['payment_confirmed']) }}</strong> 件 / 振込実行: <strong>{{ number_format($summary['cast_transferred']) }}</strong> 件 / 完了: <strong>{{ number_format($summary['completed']) }}</strong> 件</div>
                                        <div class="text-muted text-xs u-mt-4">最新: {{ $summary['latest_status_label'] }}{{ !empty($summary['latest_updated_at']) ? '（' . $summary['latest_updated_at'] . '）' : '' }}</div>
                                    </div>
                                @else
                                    <span class="text-muted">請求・振込フロー実績なし</span>
                                @endif
                            </td>
                            <td>
                                <div class="u-flex u-flex-wrap u-gap-6">
                                    <a href="{{ route('admin.shops.show', $shop['id']) }}" class="btn-action btn-action-secondary">
                                        <i class="fas fa-circle-info"></i> 詳細
                                    </a>
                                    @if($isSuspended)
                                        <form method="POST" action="{{ route('admin.shops.unsuspend', $shop['id']) }}" style="display:inline;" onsubmit="return confirm('この店舗アカウントの停止を解除しますか？');">
                                            @csrf
                                            <button type="submit" class="btn-action btn-action-secondary">
                                                <i class="fas fa-rotate-left"></i> 停止解除
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.shops.suspend', $shop['id']) }}" style="display:inline;" onsubmit="return confirm('この店舗アカウントを停止します。停止中はログイン後に「停止中」表示と問合せ送信のみ可能になります。よろしいですか？');">
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
                            <td colspan="8" class="text-center">店舗アカウントがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
