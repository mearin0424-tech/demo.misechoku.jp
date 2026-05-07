@extends('layouts.admin')

@section('title', '運営アカウント管理')

@section('content')
    <div class="admin-page">
        <div class="u-flex-between u-flex-wrap u-gap-12">
            @include('admin.parts.page-title', [
                'eyebrow' => 'ADMIN ACCOUNTS',
                'title' => '運営アカウント管理',
                'info' => '
                    <p>運営（管理者）アカウントの一覧と、ロールごとの権限設定を行います。</p>
                    <ul>
                        <li><strong>スーパー管理者（admin）</strong>：全権限を保有（変更不可）</li>
                        <li><strong>オペレーター（staff）</strong>：許可する機能をチェックボックスで選択</li>
                    </ul>
                ',
            ])
            <a href="{{ route('admin.admin-accounts.operation-log') }}" class="btn-action btn-action-secondary">
                <i class="fas fa-clipboard-list"></i> 運営操作ログ
            </a>
        </div>

        @if(session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        {{-- ロール権限カード --}}
        <section class="admin-panel">
            <h2 class="admin-panel-title">ロール権限設定</h2>
            <div class="admin-role-grid">
                @foreach($rolesCatalog as $roleKey => $roleMeta)
                    @php
                        $count = $rolePermissionCounts[$roleKey] ?? 0;
                    @endphp
                    <article class="admin-role-card {{ $roleMeta['locked'] ? 'is-locked' : '' }}">
                        <header class="admin-role-card__head">
                            <span class="admin-role-card__badge">
                                <i class="fas {{ $roleMeta['locked'] ? 'fa-shield-halved' : 'fa-user-tie' }}"></i>
                                {{ $roleKey }}
                            </span>
                            <h3 class="admin-role-card__title">{{ $roleMeta['label'] }}</h3>
                        </header>
                        <p class="admin-role-card__desc">{{ $roleMeta['description'] }}</p>
                        <div class="admin-role-card__stats">
                            <span class="admin-role-card__stat">
                                <strong>{{ $count }}</strong> / {{ $allPermissionCount }} 権限
                            </span>
                            @if($roleMeta['locked'])
                                <span class="admin-role-card__pill is-locked">
                                    <i class="fas fa-lock"></i> 編集不可
                                </span>
                            @else
                                <span class="admin-role-card__pill is-editable">
                                    <i class="fas fa-pen"></i> カスタマイズ可
                                </span>
                            @endif
                        </div>
                        <div class="admin-role-card__actions">
                            <a href="{{ route('admin.admin-accounts.roles.edit', $roleKey) }}" class="btn-action {{ $roleMeta['locked'] ? 'btn-action-secondary' : 'manage' }}">
                                <i class="fas {{ $roleMeta['locked'] ? 'fa-eye' : 'fa-sliders' }}"></i>
                                {{ $roleMeta['locked'] ? '権限を確認' : '権限を編集' }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- 運営アカウント一覧 --}}
        <section class="admin-panel">
            <h2 class="admin-panel-title">運営アカウント一覧</h2>
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>氏名</th>
                            <th>メールアドレス</th>
                            <th>ロール</th>
                            <th>有効</th>
                            <th>登録日</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>{{ $admin->id }}</td>
                                <td>{{ $admin->name ?: '—' }}</td>
                                <td>
                                    @if(!empty($admin->email))
                                        <a href="mailto:{{ $admin->email }}">{{ $admin->email }}</a>
                                    @else — @endif
                                </td>
                                <td>
                                    <span class="admin-status-badge {{ $admin->role === 'admin' ? 'is-success' : 'is-warning' }}">
                                        {{ $admin->role === 'admin' ? 'スーパー管理者' : 'オペレーター' }}
                                    </span>
                                </td>
                                <td>
                                    @if((int) $admin->is_active === 1)
                                        <span class="admin-status-badge is-active">有効</span>
                                    @else
                                        <span class="admin-status-badge is-inactive">無効</span>
                                    @endif
                                </td>
                                <td>{{ $admin->created_at ? \Illuminate\Support\Carbon::parse($admin->created_at)->format('Y-m-d') : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">運営アカウントが登録されていません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
