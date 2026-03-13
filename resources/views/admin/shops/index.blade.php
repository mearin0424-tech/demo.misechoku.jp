@extends('layouts.admin')

@section('title', '店舗管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">店舗管理</h1>
        <p class="admin-description">
            登録されている店舗アカウントの一覧です。書類確認状況や求人公開状況を確認し、管理画面から求人公開を切り替えられます。
        </p>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">
                {{ session('status') }}
            </div>
        @endif

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
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shops as $shop)
                        <tr>
                            <td>{{ $shop['id'] }}</td>
                            <td>{{ $shop['name'] }}</td>
                            <td>{{ $shop['plan'] }}</td>
                            <td>{{ number_format($shop['fee']) }} 円</td>
                            <td>{{ $shop['published_at'] ? \Illuminate\Support\Carbon::parse($shop['published_at'])->format('Y-m-d') : '-' }}</td>
                            <td>{{ $shop['document_status'] }}</td>
                            <td>
                                <span class="admin-status-badge {{ $shop['job_status_key'] === 'active' ? 'is-active' : 'is-inactive' }}">
                                    {{ $shop['job_status'] }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('admin.shops.toggle-recruit-status', $shop['id']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="admin-toggle-button">
                                        {{ $shop['job_status_key'] === 'active' ? '非公開にする' : '公開にする' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">店舗アカウントがありません。</td>
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

        .admin-alert-success {
            background: rgba(20, 83, 45, 0.3);
            border: 1px solid rgba(74, 222, 128, 0.32);
            color: #dcfce7;
        }

        .admin-status-badge {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .admin-status-badge.is-active {
            background: rgba(16, 185, 129, 0.16);
            color: #a7f3d0;
            border: 1px solid rgba(16, 185, 129, 0.32);
        }

        .admin-status-badge.is-inactive {
            background: rgba(148, 163, 184, 0.14);
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, 0.28);
        }

        .admin-toggle-button {
            min-height: 36px;
            padding: 0 12px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            cursor: pointer;
            white-space: nowrap;
        }
    </style>
@endsection

