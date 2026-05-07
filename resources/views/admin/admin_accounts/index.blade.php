@extends('layouts.admin')

@section('title', '運営アカウント管理')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', ['eyebrow' => 'ADMIN ACCOUNTS', 'title' => '運営アカウント管理'])
        <p class="admin-description">
            運営（管理者）アカウントの一覧です。氏名、メールアドレス、権限ロール、最終ログイン日時を確認できます。<br>
            現在はデモデータのみ表示しています。
        </p>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>氏名</th>
                        <th>メールアドレス</th>
                        <th>ロール</th>
                        <th>最終ログイン</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <td>{{ $admin['id'] }}</td>
                            <td>{{ $admin['name'] }}</td>
                            <td>{{ $admin['email'] }}</td>
                            <td>{{ $admin['role'] }}</td>
                            <td>{{ $admin['last_login_at']->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">運営アカウントが登録されていません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

