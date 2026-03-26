@extends('layouts.admin')

@section('title', 'お知らせ管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">お知らせ管理</h1>
        <p class="admin-description">
            キャスト・店舗・未ログインユーザー向けのお知らせを作成・公開します。「未ログイン表示」をオンにすると /support/notices にも表示されます。
        </p>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="admin-form-actions" style="margin-bottom: 16px;">
            <a href="{{ route('admin.notices.create') }}" class="btn-action manage">
                <i class="fas fa-plus"></i> 新規作成
            </a>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>公開状態</th>
                        <th>閲覧対象</th>
                        <th>公開日時</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notices as $notice)
                        <tr>
                            <td>{{ $notice->id }}</td>
                            <td>{{ $notice->title }}</td>
                            <td>{{ $notice->status_label }}</td>
                            <td>
                                @php
                                    $targets = array_filter([
                                        $notice->visible_to_cast ? 'キャスト' : null,
                                        $notice->visible_to_shop ? '店舗' : null,
                                        $notice->visible_to_guest ? '未ログイン' : null,
                                    ]);
                                @endphp
                                {{ count($targets) ? implode(' / ', $targets) : '-' }}
                            </td>
                            <td>
                                @if($notice->published_at)
                                    {{ $notice->published_at->format('Y-m-d H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.notices.edit', $notice) }}" style="color:var(--admin-blue);margin-right:8px;">編集</a>
                                <form action="{{ route('admin.notices.destroy', $notice) }}" method="post" onsubmit="return confirm('削除しますか？');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--admin-red);padding:0;font:inherit;">削除</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">お知らせが登録されていません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notices->hasPages())
            <div style="margin-top:16px;">
                {{ $notices->links() }}
            </div>
        @endif
    </div>
@endsection
