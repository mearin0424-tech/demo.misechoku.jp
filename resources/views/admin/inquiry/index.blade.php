@extends('layouts.admin')

@section('title', '問合せ管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">問合せ管理</h1>
        <p class="admin-description">
            ミセチョク運営への問い合わせ内容を一覧で確認する画面です。<br>
            問い合わせテーブルに登録されたデータを表示します。
        </p>

        @include('admin.parts.operation-achievement', ['operationAchievementRoute' => 'admin.inquiries.index'])

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>区分</th>
                        <th>名前</th>
                        <th>件名</th>
                        <th>ステータス</th>
                        <th>受付日時</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inquiries as $inquiry)
                        <tr>
                            <td>{{ $inquiry['id'] }}</td>
                            <td>{{ $inquiry['from_type'] }}</td>
                            <td>{{ $inquiry['from_name'] }}</td>
                            <td>{{ $inquiry['subject'] }}</td>
                            <td>{{ $inquiry['status'] }}</td>
                            <td>{{ $inquiry['created_at']->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">問い合わせはありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

