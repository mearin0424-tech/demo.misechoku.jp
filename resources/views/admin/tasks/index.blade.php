@extends('layouts.admin')

@section('title', '請求・振込タスク管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">請求・振込タスク管理</h1>
        <p class="admin-description">
            ステータスに応じて、運営が今対応すべき請求・振込タスクだけを一覧化しています。<br>
            実作業は各行の詳細導線から `入金・振込管理` 画面で完了できます。
        </p>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="admin-alert" style="background: rgba(248, 113, 113, 0.12); border-color: rgba(248, 113, 113, 0.3); color: #fee2e2;">
                {{ session('error') }}
            </div>
        @endif

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タスク</th>
                        <th>店舗 / キャスト</th>
                        <th>金額</th>
                        <th>期限 / 発生日</th>
                        <th>ステータス</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>{{ $task['id'] }}</td>
                            <td>{{ $task['task_title'] }}</td>
                            <td>{{ $task['shop_name'] }} / {{ $task['cast_name'] }}</td>
                            <td>¥{{ number_format($task['invoice_amount']) }}</td>
                            <td>{{ $task['task_due_date'] ?: '—' }}</td>
                            <td>{{ $task['status_label'] }}</td>
                            <td>
                                <a href="{{ route('admin.deposits.index') }}#deposit-{{ $task['id'] }}" class="btn-action manage">
                                    詳細へ
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">現在対応が必要なタスクはありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

