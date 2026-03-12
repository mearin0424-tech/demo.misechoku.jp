@extends('layouts.admin')

@section('title', 'キャスト管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">キャスト管理</h1>
        <p class="admin-description">
            登録されているキャストアカウントの一覧です。登録費、公開日、本人確認状況などを確認できます。<br>
            現在はデモデータを表示しており、今後実データに差し替え予定です。
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">キャストアカウントがありません。</td>
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

