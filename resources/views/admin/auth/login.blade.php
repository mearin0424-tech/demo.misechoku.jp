@extends('layouts.admin')

@section('title', '運営ログイン')

@section('content')
    <div class="admin-page">
        <div class="admin-role-switch">
            <a href="{{ route('cast.login') }}" class="admin-role-link">キャスト</a>
            <a href="{{ route('shop.login') }}" class="admin-role-link">店舗</a>
            <a href="{{ route('admin.login') }}" class="admin-role-link is-active">運営</a>
        </div>

        <h1 class="admin-title">運営ログイン</h1>
        <p class="admin-description">運営用ログインです。</p>

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
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}" class="admin-form">
            @csrf
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">ログイン</button>
        </form>
    </div>

    <style>
        .admin-page {
            padding: 32px 0;
            max-width: 420px;
            margin: 0 auto;
        }
        .admin-role-switch {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .admin-role-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            border: 1px solid rgba(148, 163, 184, 0.45);
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.72);
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 700;
        }
        .admin-role-link.is-active {
            border-color: rgba(251, 191, 36, 0.65);
            color: #fef3c7;
        }
        .admin-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #FDF0B2;
            text-align: center;
        }
        .admin-description {
            font-size: 0.9rem;
            color: #e5d4d4;
            margin-bottom: 18px;
            text-align: center;
        }
        .admin-alert {
            background: rgba(56, 189, 248, 0.12);
            border: 1px solid rgba(56, 189, 248, 0.7);
            color: #e0f2fe;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.85rem;
        }
        .admin-alert-error {
            background: rgba(248, 113, 113, 0.12);
            border-color: rgba(248, 113, 113, 0.7);
            color: #fee2e2;
        }
        .admin-alert-error ul {
            padding-left: 1.2rem;
            margin: 0;
        }
        .admin-form {
            background: rgba(15, 23, 42, 0.9);
            border-radius: 16px;
            padding: 20px 18px;
            border: 1px solid rgba(148, 163, 184, 0.5);
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            color: #e5e7eb;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background: rgba(15, 23, 42, 0.9);
            color: #e5e7eb;
            font-size: 0.9rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: rgba(96, 165, 250, 0.9);
            box-shadow: 0 0 0 1px rgba(96, 165, 250, 0.7);
        }
        .btn-primary {
            width: 100%;
            margin-top: 6px;
            padding: 10px 0;
            border-radius: 999px;
            border: none;
            background: linear-gradient(90deg, #fbbf24, #f97316);
            color: #111827;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: transform 0.12s ease, box-shadow 0.16s ease, filter 0.16s ease;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
            filter: brightness(1.05);
        }
        @media (max-width: 640px) {
            .admin-role-switch {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

