@extends('layouts.admin')

@section('title', 'キャスト管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">キャスト管理</h1>
        <p class="admin-description">
            登録されているキャストアカウントの一覧です。登録費、公開日、本人確認状況などを確認できます。<br>
            現在はデモデータを表示しており、今後実データに差し替え予定です。
        </p>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>キャスト名</th>
                        <th>登録費</th>
                        <th>公開日</th>
                        <th>本人確認</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($casts as $cast)
                        <tr>
                            <td>{{ $cast['id'] }}</td>
                            <td>{{ $cast['name'] }}</td>
                            <td>{{ number_format($cast['fee']) }} 円</td>
                            <td>{{ optional($cast['published_at'])->format('Y-m-d') }}</td>
                            <td>{{ $cast['identity_status'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">キャストアカウントがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

