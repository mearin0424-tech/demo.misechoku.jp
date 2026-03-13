@extends('layouts.admin')

@section('title', '店舗管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">店舗管理</h1>
        <p class="admin-description">
            登録されている店舗アカウントの一覧です。登録費、公開日、書類提出状況、求人公開状況、登録プランなどを確認できます。<br>
            現在はデモデータを表示しており、今後実データに差し替え予定です。
        </p>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>店舗名</th>
                        <th>登録プラン</th>
                        <th>登録費</th>
                        <th>公開日</th>
                        <th>書類提出</th>
                        <th>求人公開</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shops as $shop)
                        <tr>
                            <td>{{ $shop['id'] }}</td>
                            <td>{{ $shop['name'] }}</td>
                            <td>{{ $shop['plan'] }}</td>
                            <td>{{ number_format($shop['fee']) }} 円</td>
                            <td>{{ optional($shop['published_at'])->format('Y-m-d') }}</td>
                            <td>{{ $shop['document_status'] }}</td>
                            <td>{{ $shop['job_status'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">店舗アカウントがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

