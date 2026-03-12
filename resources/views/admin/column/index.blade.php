@extends('layouts.app')

@section('title', 'コラム管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">コラム管理</h1>
        <p class="admin-description">
            お役立ちコラムの作成・編集・公開設定を行う画面です。<br>
            まずは一覧 UI を用意し、後続で実際の `columns` テーブルと連携します。
        </p>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>カテゴリ</th>
                        <th>公開状態</th>
                        <th>公開日時</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($columns as $column)
                        <tr>
                            <td>{{ $column['id'] }}</td>
                            <td>{{ $column['title'] }}</td>
                            <td>{{ $column['category'] }}</td>
                            <td>{{ $column['status'] }}</td>
                            <td>
                                @if(!empty($column['posted_at']))
                                    {{ $column['posted_at']->format('Y-m-d H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">コラム記事がありません。</td>
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

