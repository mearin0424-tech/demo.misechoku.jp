@extends('layouts.app')

@section('title', '問合せ管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">問合せ管理</h1>
        <p class="admin-description">
            ミセチョク運営への問い合わせ内容を一覧で確認する画面です。<br>
            ひとまずダミーの問い合わせを表示しており、今後問い合わせテーブルと紐付けます。
        </p>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>区分</th>
                        <th>名前</th>
                        <th>件名</th>
                        <th>ステータス</th>
                        <th>受付日時</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inquiry)
                        <tr>
                            <td>{{ $inquiry['id'] }}</td>
                            <td>{{ $inquiry['from_type'] }}</td>
                            <td>{{ $inquiry['from_name'] }}</td>
                            <td>{{ $inquiry['subject'] }}</td>
                            <td>{{ $inquiry['status'] }}</td>
                            <td>{{ $inquiry['created_at']->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">問い合わせはありません。</td>
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

