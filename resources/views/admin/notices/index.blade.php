@extends('layouts.admin')

@section('title', 'お知らせ管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">お知らせ管理</h1>
        <p class="admin-description">
            ユーザーに配信するお知らせの作成・公開状態を管理します。<br>
            現在はデモデータのみ表示しています。
        </p>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>タイトル</th>
                        <th>配信対象</th>
                        <th>状態</th>
                        <th>公開日時</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notices as $notice)
                        <tr>
                            <td>{{ $notice['id'] }}</td>
                            <td>{{ $notice['title'] }}</td>
                            <td>{{ $notice['target'] }}</td>
                            <td>{{ $notice['status'] }}</td>
                            <td>
                                @if(!empty($notice['published_at']))
                                    {{ $notice['published_at']->format('Y-m-d H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">お知らせが登録されていません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

