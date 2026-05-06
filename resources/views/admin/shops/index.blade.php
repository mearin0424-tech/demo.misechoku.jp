@extends('layouts.admin')

@section('title', '店舗管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">店舗管理</h1>
        <p class="admin-description">
            登録されている店舗アカウントの一覧です。書類確認状況や求人公開状況に加えて、請求書送付・入金確認・振込完了までの運用実績を店舗単位で確認できます。
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
                        <th>登録プラン</th>
                        <th>登録費</th>
                        <th>公開日</th>
                        <th>書類提出</th>
                        <th>求人公開</th>
                        <th>運用実績（店舗単位）</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shops as $shop)
                        <tr>
                            <td>{{ $shop['id'] }}</td>
                            <td>{{ $shop['name'] }}</td>
                            <td>{{ $shop['plan'] }}</td>
                            <td>{{ number_format($shop['fee']) }} 円</td>
                            <td>{{ $shop['published_at'] ? \Illuminate\Support\Carbon::parse($shop['published_at'])->format('Y-m-d') : '-' }}</td>
                            <td>{{ $shop['document_status'] }}</td>
                            <td>
                                <span class="admin-status-badge {{ $shop['job_status_key'] === 'active' ? 'is-active' : 'is-inactive' }}">
                                    {{ $shop['job_status'] }}
                                </span>
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
                                @if(!empty($shop['recruit_schema_horizontal']))
                                    <div class="u-flex u-flex-wrap u-gap-6">
                                        @foreach($shop['admin_recruit_toggles'] as $t)
                                            <form action="{{ route('admin.shops.toggle-recruit-status', $shop['id']) }}" method="POST" style="margin:0;">
                                                @csrf
                                                <input type="hidden" name="job_type" value="{{ $t['job_type'] }}">
                                                <button type="submit" class="admin-toggle-button">
                                                    {{ $t['label'] }} {{ $t['is_on'] ? '→非公開' : '→公開' }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                @else
                                <form action="{{ route('admin.shops.toggle-recruit-status', $shop['id']) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="job_type" value="1">
                                    <button type="submit" class="admin-toggle-button">
                                        {{ $shop['job_status_key'] === 'active' ? '非公開にする' : '公開にする' }}
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">店舗アカウントがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .admin-alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 14px;
        }

        .admin-alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 0;
            box-shadow: var(--admin-shadow);
            color: #14532d;
        }

        .admin-status-badge {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .admin-status-badge.is-active {
            background: rgba(16, 185, 129, 0.14);
            color: #047857;
            border: 0;
        }

        .admin-status-badge.is-inactive {
            background: rgba(62, 44, 38, 0.06);
            color: var(--admin-muted);
            border: 0;
        }

        .admin-toggle-button {
            min-height: 36px;
            padding: 0 12px;
            border: 0;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.85);
            box-shadow: var(--admin-shadow);
            color: var(--admin-text);
            cursor: pointer;
            white-space: nowrap;
        }
    </style>
@endsection

