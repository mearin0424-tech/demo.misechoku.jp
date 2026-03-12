@extends('layouts.app')

@section('title', 'ログイン（デモ）')

@section('content')
    <div class="demo-login-page">
        <div class="demo-login-logo-wrap">
            <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="demo-login-logo">
        </div>

        <h1 class="demo-login-title">デモログイン</h1>
        <p class="demo-login-desc">
            デモ用のため、メールアドレス・パスワードの登録や変更を行わずに<br>
            ロール（管理者／キャスト／店舗）を選択して画面を体験できます。
        </p>

        <form method="POST" action="{{ route('login.demo.post') }}" class="demo-login-form">
            @csrf

            @if ($errors->any())
                <div class="demo-login-alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="demo-login-radios">
                <label class="demo-radio">
                    <input type="radio" name="role" value="admin" {{ old('role') === 'admin' ? 'checked' : '' }}>
                    <span class="demo-radio-label">
                        <span class="demo-radio-title">管理者</span>
                        <span class="demo-radio-sub">バックオフィス機能（入金・売上・マスタ設定・コラム・問合せ）を確認</span>
                    </span>
                </label>

                <label class="demo-radio">
                    <input type="radio" name="role" value="cast" {{ old('role') === 'cast' ? 'checked' : '' }}>
                    <span class="demo-radio-label">
                        <span class="demo-radio-title">キャスト</span>
                        <span class="demo-radio-sub">キャスト用のホーム・検索・マイページを確認</span>
                    </span>
                </label>

                <label class="demo-radio">
                    <input type="radio" name="role" value="shop" {{ old('role') === 'shop' ? 'checked' : '' }}>
                    <span class="demo-radio-label">
                        <span class="demo-radio-title">店舗（マネージャ）</span>
                        <span class="demo-radio-sub">店舗用のホーム・求人・マイページを確認</span>
                    </span>
                </label>
            </div>

            <button type="submit" class="demo-login-btn">選択したロールでログインする</button>
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
            line-height: 1.6;
        }
        .demo-login-form {
            background: rgba(15, 23, 42, 0.9);
            border-radius: 18px;
            padding: 18px 16px 20px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            text-align: left;
        }
        .demo-login-alert {
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.7);
            color: #fee2e2;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }
        .demo-login-alert ul {
            padding-left: 1.2rem;
            margin: 0;
        }
        .demo-login-radios {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 14px;
        }
        .demo-radio {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 10px;
            border-radius: 12px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.6);
            cursor: pointer;
        }
        .demo-radio input[type="radio"] {
            margin-top: 4px;
        }
        .demo-radio-label {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .demo-radio-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #e5e7eb;
        }
        .demo-radio-sub {
            font-size: 0.8rem;
            color: #9ca3af;
        }
        .demo-login-btn {
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
        .demo-login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
            filter: brightness(1.05);
        }

        @media (min-width: 640px) {
            .demo-login-form {
                padding: 20px 22px 22px;
            }
        }
    </style>
@endsection

