@extends('layouts.admin')

@section('title', '運営ログイン')

@section('content')
    <div class="admin-page admin-auth-shell">
        <div class="admin-role-switch">
            <a href="{{ route('cast.login') }}" class="admin-role-link">キャスト</a>
            <a href="{{ route('shop.login') }}" class="admin-role-link">店舗</a>
            <a href="{{ route('admin.login') }}" class="admin-role-link is-active">運営</a>
        </div>

        <div class="admin-panel">
            <div style="text-align:center;">
                @include('admin.parts.page-title', ['eyebrow' => 'ADMIN LOGIN', 'title' => '運営ログイン'])
            </div>
            <p class="admin-description" style="text-align:center;">運営用ログインです。メールアドレスとパスワードを入力してください。</p>

            @if ($errors->any())
                <div class="admin-alert admin-alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="admin-alert admin-alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('message'))
                <div class="admin-alert admin-alert-info">
                    {{ session('message') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="admin-auth-form">
                @csrf

                <label class="admin-auth-field">
                    <span>メールアドレス</span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@misechoku.jp" autocomplete="email" required>
                </label>

                <label class="admin-auth-field">
                    <span>パスワード</span>
                    <input type="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                </label>

                <button type="submit" class="btn-action manage" style="width:100%;">
                    <i class="fas fa-right-to-bracket"></i> ログイン
                </button>
            </form>

            <p class="admin-description" style="text-align:center; margin-top: 12px; font-size: 0.82rem;">
                <a href="{{ route('login.demo') }}" style="color: var(--admin-sub); text-decoration: underline;">デモログイン画面へ</a>
            </p>
        </div>
    </div>

    <style>
        .admin-auth-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 8px;
        }
        .admin-auth-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .admin-auth-field > span {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--admin-sub);
            letter-spacing: 0.06em;
        }
        .admin-auth-field input {
            width: 100%;
            min-height: 44px;
            padding: 0 14px;
            border: 1px solid var(--admin-line);
            border-radius: 10px;
            background: var(--admin-surface);
            color: var(--admin-ink);
            font-size: 0.92rem;
            box-sizing: border-box;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .admin-auth-field input:focus {
            outline: none;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px var(--admin-primary-soft);
        }
    </style>
@endsection
