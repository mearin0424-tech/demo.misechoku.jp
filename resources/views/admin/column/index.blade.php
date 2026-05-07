@extends('layouts.admin')

@section('title', 'コラム管理')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', [
            'eyebrow' => 'COLUMNS',
            'title' => 'コラム管理',
            'info' => '
                <p>キャスト・店舗向けの<strong>お役立ちコラム</strong>を作成・公開します。</p>
                <p>未ログイン向けに表示する場合は「<strong>未ログイン表示</strong>」をオンにしてください。</p>
            ',
        ])

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="admin-form-actions" style="margin-bottom: 16px;">
            <a href="{{ route('admin.columns.create') }}" class="btn-action manage">
                <i class="fas fa-plus"></i> 新規作成
            </a>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>カテゴリ</th>
                        <th>公開状態</th>
                        <th>閲覧対象</th>
                        <th>公開日時</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($columns as $column)
                        <tr>
                            <td>{{ $column->id }}</td>
                            <td>{{ $column->title }}</td>
                            <td>{{ $column->columnCategory?->name ?? '-' }}</td>
                            <td>{{ $column->status_label }}</td>
                            <td>
                                @php
                                    $targets = array_filter([
                                        $column->visible_to_cast ? 'キャスト' : null,
                                        $column->visible_to_shop ? '店舗' : null,
                                        $column->visible_to_guest ? '未ログイン' : null,
                                    ]);
                                @endphp
                                {{ count($targets) ? implode(' / ', $targets) : '-' }}
                            </td>
                            <td>
                                @if($column->published_at)
                                    {{ $column->published_at->format('Y-m-d H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.columns.edit', $column) }}" class="row-action-link">編集</a>
                                <form action="{{ route('admin.columns.destroy', $column) }}" method="post" onsubmit="return confirm('削除しますか？');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="row-action-link is-danger">削除</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">コラム記事がありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($columns->hasPages())
            <div style="margin-top:16px;">
                {{ $columns->links() }}
            </div>
        @endif
    </div>
@endsection
