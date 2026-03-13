@extends('layouts.admin')

@section('title', 'NGワード管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">NGワード管理</h1>
        <p class="admin-description">
            メッセージなどで使用できないキーワードを登録・管理します。<br>
            表示内容はデータベースの `ng_words` テーブルから読み込んでいます。
        </p>

        @if (!empty($error))
            <div class="admin-alert admin-alert-error">
                {{ $error }}
            </div>
        @endif

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NGワード</th>
                        <th>状態</th>
                        <th>登録日</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($words as $word)
                        <tr>
                            <td>{{ $word->id }}</td>
                            <td>{{ $word->word }}</td>
                            <td>{{ $word->is_active ? '有効' : '無効' }}</td>
                            <td>{{ $word->created_at ? \Illuminate\Support\Carbon::parse($word->created_at)->format('Y-m-d') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">登録されているNGワードはありません。</td>
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

        .admin-alert-error {
            background: rgba(127, 29, 29, 0.3);
            border: 1px solid rgba(248, 113, 113, 0.4);
            color: #fee2e2;
        }
    </style>
@endsection

