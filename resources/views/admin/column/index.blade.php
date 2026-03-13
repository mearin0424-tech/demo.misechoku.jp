@extends('layouts.admin')

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
@endsection

