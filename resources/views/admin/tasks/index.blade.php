@extends('layouts.admin')

@section('title', '請求・振込タスク管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">請求・振込タスク管理</h1>
        <p class="admin-description">
            店舗への請求およびキャストへの振込に関するタスクを一覧で管理します。<br>
            現在はデモデータのみ表示しています。
        </p>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>種別</th>
                        <th>対象</th>
                        <th>金額</th>
                        <th>期限</th>
                        <th>ステータス</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>{{ $task['id'] }}</td>
                            <td>{{ $task['type'] }}</td>
                            <td>{{ $task['target'] }}</td>
                            <td>{{ number_format($task['amount']) }} 円</td>
                            <td>{{ $task['due_date']->format('Y-m-d') }}</td>
                            <td>{{ $task['status'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">タスクはありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

