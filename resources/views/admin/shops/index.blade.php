@extends('layouts.admin')

@section('title', '店舗管理')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', [
            'eyebrow' => 'SHOPS',
            'title' => '店舗管理',
            'info' => '
                <ul>
                    <li>登録店舗アカウントの一覧を表示します</li>
                    <li><strong>行をタップ</strong>で詳細画面に移動</li>
                    <li>書類確認状況・最終ログイン・状態（有効／停止中）を確認</li>
                    <li>停止操作・運用実績・非公開情報の確認は<strong>詳細画面</strong>から</li>
                </ul>
            ',
        ])

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="table-wrapper">
            <table class="admin-table admin-table-clickable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>店舗名</th>
                        <th>登録日</th>
                        <th>最終ログイン</th>
                        <th>書類提出</th>
                        <th>状態</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shops as $shop)
                        @php
                            $isSuspended = (int) ($shop['account_status'] ?? 0) === 2;
                            $detailUrl = route('admin.shops.show', $shop['id']);
                        @endphp
                        <tr class="admin-row-clickable {{ $isSuspended ? 'is-suspended' : '' }}"
                            data-href="{{ $detailUrl }}"
                            tabindex="0"
                            role="link"
                            aria-label="店舗詳細：{{ $shop['name'] }}">
                            <td><code>{{ $shop['id'] }}</code></td>
                            <td>
                                <a href="{{ $detailUrl }}" class="admin-row-clickable__link">{{ $shop['name'] }}</a>
                            </td>
                            <td>{{ $shop['registered_at'] ? \Illuminate\Support\Carbon::parse($shop['registered_at'])->format('Y-m-d') : '—' }}</td>
                            <td>{{ $shop['last_login_at'] ? \Illuminate\Support\Carbon::parse($shop['last_login_at'])->format('Y-m-d H:i') : '—' }}</td>
                            <td>{{ $shop['document_status'] }}</td>
                            <td>
                                @if($isSuspended)
                                    <span class="admin-status-badge is-danger"><i class="fas fa-ban"></i> 停止中</span>
                                @elseif((int) ($shop['account_status'] ?? 0) === 1)
                                    <span class="admin-status-badge is-success">有効</span>
                                @else
                                    <span class="admin-status-badge is-inactive">仮登録</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">店舗アカウントがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
