@extends('layouts.admin')

@section('title', '規約管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">規約管理</h1>
        <p class="admin-description">
            「運営協会」「利用規約」「プライバシーポリシー」の3つのページを章タイトル＋本文の組み合わせで管理します。<br>
            既定はロック状態で<strong>閲覧のみ</strong>。編集には明示の解除が必要で、更新者と日時はすべて履歴に記録されます。
        </p>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ページ</th>
                        <th>キー</th>
                        <th>章数</th>
                        <th>状態</th>
                        <th>最終更新者</th>
                        <th>最終更新日時</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                        <tr>
                            <td><strong>{{ $doc->title }}</strong></td>
                            <td><code>{{ $doc->key }}</code></td>
                            <td>{{ number_format($doc->chapters_count ?? 0) }} 章</td>
                            <td>
                                @if($doc->is_locked)
                                    <span class="admin-badge" style="background:rgba(248,113,113,.18);color:#fda4af;">
                                        <i class="fas fa-lock" style="margin-right:4px;"></i>ロック中
                                    </span>
                                @else
                                    <span class="admin-badge" style="background:rgba(52,211,153,.18);color:#86efac;">
                                        <i class="fas fa-lock-open" style="margin-right:4px;"></i>編集可能
                                    </span>
                                @endif
                            </td>
                            <td>{{ $doc->updated_by_name ?: '-' }}</td>
                            <td>
                                @if($doc->content_updated_at)
                                    {{ $doc->content_updated_at->format('Y-m-d H:i') }}
                                @else
                                    <span class="text-gray-400 text-xs">未更新</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.policies.show', ['key' => $doc->key]) }}" style="color:var(--admin-blue);margin-right:8px;">閲覧</a>
                                <a href="{{ route('admin.policies.edit', ['key' => $doc->key]) }}" style="color:var(--admin-gold);">編集</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
