@extends('layouts.admin')

@section('title', 'キャスト管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">キャスト管理</h1>
        <p class="admin-description">
            登録されているキャストアカウントの一覧です。本人確認に加え、請求書送付・振込・完了までの運用実績をキャスト単位で確認できます。
        </p>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>キャスト名</th>
                        <th>登録費</th>
                        <th>公開日</th>
                        <th>本人確認</th>
                        <th>運用実績（キャスト単位）</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($casts as $cast)
                        <tr>
                            <td>{{ $cast['id'] }}</td>
                            <td>{{ $cast['name'] }}</td>
                            <td>{{ number_format($cast['fee']) }} 円</td>
                            <td>{{ optional($cast['published_at'])->format('Y-m-d') }}</td>
                            <td>{{ $cast['identity_status'] }}</td>
                            <td style="min-width: 340px;">
                                @php($summary = $cast['operation_summary'] ?? null)
                                @if($summary)
                                    <div style="font-size: .8rem; line-height: 1.7;">
                                        <div>請求書送付: <strong>{{ number_format($summary['invoice_issued']) }}</strong> 件 / 振込実行: <strong>{{ number_format($summary['cast_transferred']) }}</strong> 件 / 完了: <strong>{{ number_format($summary['completed']) }}</strong> 件</div>
                                        <div style="color: var(--admin-muted);">最新: {{ $summary['latest_status_label'] }}{{ !empty($summary['latest_updated_at']) ? '（' . $summary['latest_updated_at'] . '）' : '' }}</div>
                                    </div>
                                @else
                                    <span class="text-muted">請求・振込フロー実績なし</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">キャストアカウントがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

