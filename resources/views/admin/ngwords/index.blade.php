@extends('layouts.admin')

@section('title', 'NGワード管理')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', ['eyebrow' => 'NG WORDS', 'title' => 'NGワード管理'])
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
                            <td>
                                <span class="admin-status-badge {{ $word->is_active ? 'is-success' : 'is-inactive' }}">
                                    {{ $word->is_active ? '有効' : '無効' }}
                                </span>
                            </td>
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
@endsection

