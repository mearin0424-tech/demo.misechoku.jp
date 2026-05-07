@extends('layouts.admin')

@section('title', 'キャスト管理')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', ['eyebrow' => 'CASTS', 'title' => 'キャスト管理'])
        <p class="admin-description">
            登録されているキャストアカウントの一覧です。本人確認、最終ログイン、運用実績（請求／振込）をキャスト単位で確認できます。本名・連絡先などの非公開情報は詳細画面で個別に解除して閲覧します。
        </p>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>キャスト名</th>
                        <th>登録日</th>
                        <th>最終ログイン</th>
                        <th>本人確認</th>
                        <th>運用実績（キャスト単位）</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($casts as $cast)
                        <tr>
                            <td><code>{{ $cast['id'] }}</code></td>
                            <td>
                                <a href="{{ route('admin.casts.show', $cast['id']) }}">{{ $cast['name'] }}</a>
                            </td>
                            <td>{{ $cast['registered_at'] ? \Illuminate\Support\Carbon::parse($cast['registered_at'])->format('Y-m-d') : '—' }}</td>
                            <td>{{ $cast['last_login_at'] ? \Illuminate\Support\Carbon::parse($cast['last_login_at'])->format('Y-m-d H:i') : '—' }}</td>
                            <td>{{ $cast['identity_status'] }}</td>
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
                                <a href="{{ route('admin.casts.show', $cast['id']) }}" class="btn-action btn-action-secondary">
                                    <i class="fas fa-circle-info"></i> 詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">キャストアカウントがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
