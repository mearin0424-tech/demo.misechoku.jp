@extends('layouts.admin')

@section('title', 'NGワード管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">NGワード管理</h1>
        <p class="admin-description">
            メッセージなどで使用できないキーワードを登録・管理します。<br>
            現在はデモデータのみ表示しています。
        </p>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NGワード</th>
                        <th>登録日</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($words as $word)
                        <tr>
                            <td>{{ $word['id'] }}</td>
                            <td>{{ $word['word'] }}</td>
                            <td>{{ $word['created_at']->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">登録されているNGワードはありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

