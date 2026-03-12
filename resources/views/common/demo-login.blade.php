@extends('layouts.app')

@section('title', 'ログイン（デモ）')

@section('content')
    <div class="demo-login-page">
        <div class="demo-login-logo-wrap">
            <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="demo-login-logo">
        </div>

        <h1 class="demo-login-title">デモログイン</h1>
        <p class="demo-login-desc">
            ログインする利用者区分を選んでください。
        </p>

        <form method="POST" action="{{ route('login.demo.post') }}" class="demo-login-form">
            @csrf

            @if ($errors->any())
                <div class="demo-login-alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <button type="submit" name="role" value="admin" class="demo-login-btn demo-login-btn-admin">
                <span class="demo-login-btn-main">管理者としてログイン</span>
                <span class="demo-login-btn-sub">運営用ダッシュボードを確認</span>
            </button>

            <button type="submit" name="role" value="shop" class="demo-login-btn demo-login-btn-shop">
                <span class="demo-login-btn-main">店舗としてログイン</span>
                <span class="demo-login-btn-sub">店舗用ホーム・求人・マイページを確認</span>
            </button>

            <button type="submit" name="role" value="cast" class="demo-login-btn demo-login-btn-cast">
                <span class="demo-login-btn-main">キャストとしてログイン</span>
                <span class="demo-login-btn-sub">キャスト用ホーム・検索・マイページを確認</span>
            </button>
        </form>
    </div>

    <style>
        .demo-login-page {
            padding: 40px 16px 32px;
            max-width: 520px;
            margin: 0 auto;
            text-align: center;
        }
        .demo-login-logo-wrap {
            margin-bottom: 24px;
        }
        .demo-login-logo {
            width: 220px;
            max-width: 70%;
        }
        .demo-login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #FDF0B2;
            margin-bottom: 8px;
        }
        .demo-login-desc {
            font-size: 0.9rem;
            color: #e5d4d4;
            margin-bottom: 20px;
        }
        .demo-login-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .demo-login-alert {
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.7);
            color: #fee2e2;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 0.8rem;
            margin-bottom: 4px;
            text-align: left;
        }
        .demo-login-btn {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.8);
            background: rgba(15, 23, 42, 0.92);
            color: #e5e7eb;
            cursor: pointer;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 2px;
            transition: background 0.15s ease, border-color 0.15s ease, transform 0.08s ease;
        }
        .demo-login-btn:hover {
            background: rgba(30, 64, 175, 0.65);
            border-color: rgba(129, 140, 248, 0.9);
            transform: translateY(-1px);
        }
        .demo-login-btn-main {
            font-size: 0.95rem;
            font-weight: 600;
        }
        .demo-login-btn-sub {
            font-size: 0.8rem;
            color: #cbd5f5;
        }
        .demo-login-btn-admin {
        }
        .demo-login-btn-shop {
        }
        .demo-login-btn-cast {
        }
    </style>
@endsection

