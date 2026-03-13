@extends('layouts.admin')

@section('title', '入金・振込管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">入金・振込管理</h1>
        <p class="admin-description">
            店舗からの入金状況と、キャストへの振込ステータスを管理する画面です。<br>
            現在はデモデータとステータスフローを表示しており、後続で実データ連携（`Deposit` 周り）を差し込みます。
        </p>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        <section class="admin-panel" style="margin-bottom:16px;">
            <h2 class="admin-panel-title">現在の入金ステータス</h2>
            @php $flow = $depositFlow ?? ['cast' => '未申請','shop' => '未稼働','admin' => '未稼働']; @endphp
            <table class="admin-table" style="margin-bottom:8px;">
                <thead>
                    <tr>
                        <th>アクター</th>
                        <th>ステータス</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>キャスト</td><td>{{ $flow['cast'] }}</td></tr>
                    <tr><td>店舗</td><td>{{ $flow['shop'] }}</td></tr>
                    <tr><td>運営</td><td>{{ $flow['admin'] }}</td></tr>
                </tbody>
            </table>
            @php $step = session('deposit_flow_step', 0); @endphp
            <div class="management-actions" style="text-align:right;">
                @if($step == 2)
                    <form method="POST" action="{{ route('admin.deposits.approve') }}">
                        @csrf
                        <button type="submit" class="btn-action manage">
                            入金額を承認する
                        </button>
                    </form>
                @elseif($step == 4)
                    <form method="POST" action="{{ route('admin.deposits.paycast') }}">
                        @csrf
                        <button type="submit" class="btn-action manage">
                            キャストへの振込を実行する
                        </button>
                    </form>
                @endif
            </div>
        </section>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>店舗</th>
                        <th>キャスト</th>
                        <th>ステータス</th>
                        <th>金額</th>
                        <th>依頼日</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposits as $deposit)
                        <tr>
                            <td>{{ $deposit['id'] }}</td>
                            <td>{{ $deposit['shop_name'] }}</td>
                            <td>{{ $deposit['cast_name'] }}</td>
                            <td>{{ $deposit['status'] }}</td>
                            <td>{{ number_format($deposit['amount']) }} 円</td>
                            <td>{{ optional($deposit['requested_at'])->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">入金データがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .admin-page {
            padding: 24px 0;
        }
        .admin-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #e5e7eb;
        }
        .admin-description {
            font-size: 0.9rem;
            color: #cbd5f5;
            margin-bottom: 16px;
            line-height: 1.6;
        }
        .table-wrapper {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid rgba(55, 65, 81, 0.9);
            background: rgba(15, 23, 42, 0.95);
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .admin-table thead {
            background: rgba(31, 41, 55, 1);
        }
        .admin-table th,
        .admin-table td {
            padding: 8px 10px;
            white-space: nowrap;
        }
        .admin-table th {
            text-align: left;
            font-weight: 600;
            color: #e5e7eb;
        }
        .admin-table tbody tr:nth-child(even) {
            background: rgba(17, 24, 39, 1);
        }
        .admin-table tbody tr:nth-child(odd) {
            background: rgba(15, 23, 42, 1);
        }
        .admin-table tbody td {
            color: #e5e7eb;
        }
        .admin-table tbody tr:hover {
            background: rgba(55, 65, 81, 0.95);
        }
        .text-center {
            text-align: center;
        }
    </style>
@endsection

