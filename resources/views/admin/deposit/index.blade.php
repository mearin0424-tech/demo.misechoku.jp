@extends('layouts.app')

@section('title', '入金・振込管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">入金・振込管理</h1>
        <p class="admin-description">
            店舗からの入金状況と、キャストへの振込ステータスを管理する画面です。<br>
            現在はデモデータを表示しており、後続で実データ連携（`Deposit` 周り）を差し込みます。
        </p>

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
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #FDF0B2;
        }
        .admin-description {
            font-size: 0.9rem;
            color: #e5d4d4;
            margin-bottom: 18px;
            line-height: 1.6;
        }
        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: rgba(15, 23, 42, 0.7);
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .admin-table thead {
            background: rgba(30, 64, 175, 0.7);
        }
        .admin-table th,
        .admin-table td {
            padding: 10px 12px;
            white-space: nowrap;
        }
        .admin-table th {
            text-align: left;
            font-weight: 600;
            color: #e5e7eb;
        }
        .admin-table tbody tr:nth-child(even) {
            background: rgba(15, 23, 42, 0.9);
        }
        .admin-table tbody tr:nth-child(odd) {
            background: rgba(30, 41, 59, 0.9);
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

