@extends('layouts.admin')

@section('title', '運営ログイン')

@section('content')
    <div class="admin-page">
        <div class="admin-role-switch">
            <a href="{{ route('login.demo') }}" class="admin-role-link">キャスト</a>
            <a href="{{ route('login.demo') }}" class="admin-role-link">店舗</a>
            <a href="{{ route('login.demo') }}" class="admin-role-link is-active">運営</a>
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

        <p class="admin-description">ログインは共通画面で行います。</p>
        <a href="{{ route('login.demo') }}" class="btn-primary" style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">共通ログイン画面へ</a>
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
            border: 1px solid rgba(220, 181, 104, 0.24);
            border-radius: 14px;
            background: rgba(74, 18, 42, 0.72);
            color: #f0b4c6;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 700;
        }
        .admin-role-link.is-active {
            border-color: rgba(220, 181, 104, 0.62);
            color: #ffe2a3;
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
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(220, 181, 104, 0.3);
            color: #f9efcf;
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
            background: rgba(74, 18, 42, 0.9);
            border-radius: 16px;
            padding: 20px 18px;
            border: 1px solid rgba(220, 181, 104, 0.3);
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            color: #f0b4c6;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid rgba(220, 181, 104, 0.28);
            background: rgba(56, 13, 31, 0.9);
            color: #fff0f5;
            font-size: 0.9rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: rgba(220, 181, 104, 0.6);
            box-shadow: 0 0 0 1px rgba(220, 181, 104, 0.45);
        }
        .btn-primary {
            width: 100%;
            margin-top: 6px;
            padding: 10px 0;
            border-radius: 999px;
            border: none;
            background: linear-gradient(145deg, #dcb568, #b8860b);
            color: #2a1406;
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

