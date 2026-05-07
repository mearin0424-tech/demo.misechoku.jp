@extends('layouts.admin')

@section('title', 'キャスト管理')

@section('content')
    <div class="admin-page">
        @include('admin.parts.page-title', [
            'eyebrow' => 'CASTS',
            'title' => 'キャスト管理',
            'info' => '
                <ul>
                    <li>登録キャストアカウントの一覧を表示します</li>
                    <li><strong>行をタップ</strong>で詳細画面に移動</li>
                    <li>本人確認状況・最終ログイン・状態（有効／停止中）を確認</li>
                    <li>停止操作・運用実績・非公開情報の確認は<strong>詳細画面</strong>から</li>
                </ul>
            ',
        ])

        @if(session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="table-wrapper">
            <table class="admin-table admin-table-clickable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>キャスト名</th>
                        <th>登録日</th>
                        <th>最終ログイン</th>
                        <th>本人確認</th>
                        <th>状態</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($casts as $cast)
                        @php
                            $isSuspended = (int) ($cast['account_status'] ?? 0) === 2;
                            $detailUrl = route('admin.casts.show', $cast['id']);
                        @endphp
                        <tr class="admin-row-clickable {{ $isSuspended ? 'is-suspended' : '' }}"
                            data-href="{{ $detailUrl }}"
                            tabindex="0"
                            role="link"
                            aria-label="キャスト詳細：{{ $cast['name'] }}">
                            <td><code>{{ $cast['id'] }}</code></td>
                            <td>
                                <a href="{{ $detailUrl }}" class="admin-row-clickable__link">{{ $cast['name'] }}</a>
                            </td>
                            <td>{{ $cast['registered_at'] ? \Illuminate\Support\Carbon::parse($cast['registered_at'])->format('Y-m-d') : '—' }}</td>
                            <td>{{ $cast['last_login_at'] ? \Illuminate\Support\Carbon::parse($cast['last_login_at'])->format('Y-m-d H:i') : '—' }}</td>
                            <td>{{ $cast['identity_status'] }}</td>
                            <td>
                                @if($isSuspended)
                                    <span class="admin-status-badge is-danger"><i class="fas fa-ban"></i> 停止中</span>
                                @elseif((int) ($cast['account_status'] ?? 0) === 1)
                                    <span class="admin-status-badge is-success">有効</span>
                                @else
                                    <span class="admin-status-badge is-inactive">仮登録</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">キャストアカウントがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
