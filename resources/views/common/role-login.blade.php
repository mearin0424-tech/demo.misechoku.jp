@extends('layouts.app-v2')

@section('title', $title)
@section('body-class', $bodyClass)

@section('content')
    <div class="role-login-shell">
        <div class="role-login-bg">
            <div class="role-login-bg-photo"></div>
            <div class="role-login-bg-overlay"></div>
            <div class="role-login-bg-glow role-login-bg-glow-left"></div>
            <div class="role-login-bg-glow role-login-bg-glow-right"></div>
        </div>

        <div class="role-login-page">
            <div class="role-login-brand">
                <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="role-login-logo">
            </div>

            @if (session('message'))
                <div class="role-login-alert role-login-alert-info">
                    {{ session('message') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="role-login-alert role-login-alert-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="role-login-card">
                <div class="role-login-tabs">
                    <span class="role-login-tab is-active">ログイン</span>
                    <a href="{{ $registerUrl }}" class="role-login-tab">新規登録</a>
                </div>

                <div class="role-login-card-body">
                    <div class="role-login-role-switch">
                        <a href="{{ route('cast.login') }}" class="role-login-role {{ $role === 'cast' ? 'is-active' : '' }}">
                            <i class="fas fa-user role-login-role__icon" aria-hidden="true"></i>
                            <span class="role-login-role__label">キャスト</span>
                            <span class="role-login-role__sub">お仕事を探す</span>
                        </a>
                        <a href="{{ route('shop.login') }}" class="role-login-role {{ $role === 'shop' ? 'is-active' : '' }}">
                            <i class="fas fa-store role-login-role__icon" aria-hidden="true"></i>
                            <span class="role-login-role__label">店舗</span>
                            <span class="role-login-role__sub">キャストを探す</span>
                        </a>
                    </div>

                    <div class="role-login-copy">
                        <h2 class="role-login-title">{{ $heroTitle }}</h2>
                    </div>

                    <form method="POST" action="{{ $formAction }}" class="role-login-form">
                        @csrf

                        <label class="role-login-field">
                            <span>メールアドレス</span>
                            <div class="role-login-input-wrap">
                                <i class="fas fa-envelope role-login-input-icon" aria-hidden="true"></i>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="example@misechoku.jp" autocomplete="email">
                            </div>
                        </label>

                        <label class="role-login-field">
                            <span>パスワード</span>
                            <div class="role-login-input-wrap">
                                <i class="fas fa-lock role-login-input-icon" aria-hidden="true"></i>
                                <input type="password" name="password" placeholder="••••••••" autocomplete="current-password">
                            </div>
                        </label>

                        <button type="submit" class="role-login-submit">
                            <i class="fas fa-right-to-bracket" aria-hidden="true"></i>
                            <span>ログイン</span>
                        </button>

                        {{-- パスワードリセット導線（キャスト・店舗共通） --}}
                        <div style="margin-top: 12px; text-align: center;">
                            <a href="{{ route('password.forgot.show') }}"
                               style="font-size: 0.82rem; color: rgba(255,255,255,0.7); text-decoration: underline;">
                                パスワードをお忘れの方はこちら
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="role-login-links">
                <a href="{{ $registerUrl }}" class="role-login-secondary">新規登録</a>
                <a href="{{ route('login.demo') }}" class="role-login-back">デモログイン一覧へ戻る</a>
            </div>
        </div>
    </div>

    <style>
        body.page-auth-login #global-header,
        body.page-auth-login #bottom-nav,
        body.page-auth-login #side-menu,
        body.page-auth-login #character-guide,
        body.page-auth-login .header-right {
            display: none !important;
        }

        body.page-auth-login,
        body.page-auth-login #app,
        body.page-auth-login .main-layout-container,
        body.page-auth-login main {
            min-height: 100vh;
        }

        body.page-auth-login #bg-layer {
            background:
                radial-gradient(circle at 20% 20%, rgba(168, 85, 247, 0.14), transparent 24%),
                radial-gradient(circle at 80% 74%, rgba(168, 85, 247, 0.18), transparent 28%),
                linear-gradient(180deg, #0a0a0a 0%, #050505 100%);
        }

        body.page-auth-login {
            background:
                radial-gradient(circle at top, rgba(var(--accent-rgb, 214, 112, 162), 0.10), transparent 36%),
                linear-gradient(180deg, #050505 0%, #0a0a0a 100%);
            color: #f5f5f5;
            font-family: "Noto Sans JP", "Hiragino Sans", "Meiryo", sans-serif;
        }

        body.page-auth-login main {
            padding: 0;
        }

        body.page-auth-login .content-wrapper {
            width: 100%;
            max-width: 100%;
            padding: 0;
            background: transparent;
            box-shadow: none;
            overflow-x: hidden;
        }

        body.page-auth-login #app {
            max-width: 100%;
        }

        .role-login-shell {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            width: 100%;
            max-width: 100vw;
            box-sizing: border-box;
        }

        .role-login-bg,
        .role-login-bg-photo,
        .role-login-bg-overlay {
            position: absolute;
            inset: 0;
        }

        .role-login-bg-photo {
            background:
                linear-gradient(135deg, rgba(10, 2, 3, 0.12), rgba(10, 2, 3, 0.5)),
                url('https://images.unsplash.com/photo-1514933651103-005eec06c04b?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            opacity: 0.28;
            transform: scale(1.06);
            filter: blur(4px);
        }

        .role-login-bg-overlay {
            background: linear-gradient(180deg, rgba(10, 10, 10, 0.62) 0%, rgba(5, 5, 5, 0.84) 45%, rgba(5, 5, 5, 0.98) 100%);
        }

        .role-login-bg-glow {
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
        }

        .role-login-bg-glow-left {
            top: 10%;
            left: 8%;
            background: rgba(168, 85, 247, 0.1);
        }

        .role-login-bg-glow-right {
            right: 8%;
            bottom: 10%;
            background: rgba(124, 58, 237, 0.1);
        }

        .role-login-page {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: min(460px, calc(100vw - 2 * var(--content-padding-x, 16px)));
            margin: 0 auto;
            padding: 40px max(16px, var(--content-padding-x)) 28px;
            box-sizing: border-box;
        }

        .role-login-brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .role-login-logo {
            width: 200px;
            max-width: 72%;
            margin-bottom: 0;
            filter: drop-shadow(0 10px 28px rgba(0, 0, 0, 0.4));
        }

        .role-login-title {
            margin: 0;
            font-size: clamp(1.4rem, 4vw, 1.7rem);
            line-height: 1.4;
            color: #ffffff;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .role-login-alert {
            margin-bottom: 14px;
            padding: 13px 14px;
            border-radius: 18px;
            font-size: 0.82rem;
            line-height: 1.7;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .role-login-alert-info {
            border: 1px solid rgba(168, 85, 247, 0.40);
            background: rgba(168, 85, 247, 0.10);
            color: var(--accent-text, #f0a6c4);
        }

        .role-login-alert-error {
            border: 1px solid rgba(248, 113, 113, 0.45);
            background: rgba(220, 38, 38, 0.15);
            color: #fecaca;
        }

        .role-login-card {
            overflow: hidden;
            border: 1px solid rgba(168, 85, 247, 0.30);
            border-radius: 30px;
            background: linear-gradient(155deg, rgba(255, 255, 255, 0.04), rgba(10, 10, 10, 0.86));
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            max-width: 100%;
            box-sizing: border-box;
        }

        .role-login-tabs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            border-bottom: 1px solid rgba(168, 85, 247, 0.18);
        }

        .role-login-tab {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 56px;
            padding: 16px 12px;
            color: rgba(255, 255, 255, 0.55);
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            transition: color 0.2s ease;
        }
        .role-login-tab:hover { color: rgba(255, 255, 255, 0.85); }

        .role-login-tab.is-active {
            color: var(--accent-text, #f0a6c4);
        }

        .role-login-tab.is-active::after {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 0;
            width: 50%;
            height: 3px;
            background: var(--accent, #d670a2);
            border-radius: 3px 3px 0 0;
            box-shadow: 0 0 12px rgba(var(--accent-rgb, 214, 112, 162), 0.45);
        }

        .role-login-card-body {
            padding: 24px;
        }

        .role-login-role-switch {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .role-login-role {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            min-height: 84px;
            padding: 12px 8px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.04);
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            font-size: 0.86rem;
            font-weight: 700;
            transition: background 0.2s, border-color 0.2s, color 0.2s, transform 0.15s, box-shadow 0.2s;
        }
        .role-login-role:hover {
            background: rgba(255, 255, 255, 0.07);
            color: #ffffff;
            transform: translateY(-1px);
        }
        .role-login-role:focus-visible {
            outline: 2px solid var(--accent-text, #f0a6c4);
            outline-offset: 2px;
        }

        .role-login-role__icon {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.45);
            margin-bottom: 2px;
            transition: color 0.2s, transform 0.2s;
        }
        .role-login-role__label {
            font-size: 0.9rem;
            letter-spacing: 0.06em;
        }
        .role-login-role__sub {
            font-size: 0.64rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.40);
            letter-spacing: 0.04em;
        }

        .role-login-role.is-active {
            border-color: rgba(var(--accent-rgb, 214, 112, 162), 0.60);
            color: #fff;
            background:
                radial-gradient(100% 100% at 50% 0%, rgba(var(--accent-rgb, 214, 112, 162), 0.22), transparent 75%),
                rgba(var(--accent-rgb, 214, 112, 162), 0.08);
            box-shadow: 0 8px 20px rgba(var(--accent-rgb, 214, 112, 162), 0.18);
        }
        .role-login-role.is-active .role-login-role__icon {
            color: var(--accent-text, #f0a6c4);
            transform: scale(1.1);
        }
        .role-login-role.is-active .role-login-role__sub {
            color: rgba(var(--accent-rgb, 214, 112, 162), 0.85);
        }

        .role-login-copy {
            margin-bottom: 18px;
            text-align: center;
        }

        .role-login-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .role-login-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .role-login-field > span,
        .role-login-label-row span {
            margin-left: 2px;
            color: var(--accent-text, #f0a6c4);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.14em;
        }

        .role-login-input-wrap {
            position: relative;
        }

        .role-login-input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.35);
            pointer-events: none;
            transition: color 0.2s;
        }
        .role-login-input-wrap:focus-within .role-login-input-icon {
            color: var(--accent-text, #f0a6c4);
        }

        .role-login-input-wrap input {
            width: 100%;
            min-height: 54px;
            padding: 0 16px 0 44px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(0, 0, 0, 0.35);
            color: #ffffff;
            font-size: 0.94rem;
            box-sizing: border-box;
            max-width: 100%;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .role-login-input-wrap input::placeholder {
            color: rgba(255, 255, 255, 0.40);
        }

        .role-login-input-wrap input:focus,
        .role-login-input-wrap input:focus-visible {
            outline: none;
            border-color: rgba(var(--accent-rgb, 214, 112, 162), 0.60);
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb, 214, 112, 162), 0.22);
        }

        .role-login-submit,
        .role-login-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 54px;
            padding: 14px 18px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 700;
            transition: filter 0.15s ease, transform 0.12s ease, box-shadow 0.15s ease;
        }

        .role-login-submit {
            border: 0;
            background: linear-gradient(135deg, var(--accent-grad-from, #e88bb2), var(--accent-grad-to, #a83d70));
            color: var(--on-accent-strong, #ffffff);
            cursor: pointer;
            box-shadow:
                0 6px 14px rgba(0, 0, 0, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.20),
                inset 0 -1px 0 rgba(0, 0, 0, 0.18);
        }
        .role-login-submit:hover { filter: none; }
        .role-login-submit:active {
            transform: scale(0.97);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.45), inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .role-login-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 18px;
        }

        .role-login-secondary {
            border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.40);
            background: rgba(255, 255, 255, 0.04);
            color: var(--accent-text, #f0a6c4);
        }
        .role-login-secondary:hover {
            background: rgba(var(--accent-rgb, 214, 112, 162), 0.10);
            border-color: rgba(var(--accent-rgb, 214, 112, 162), 0.60);
        }

        .role-login-back {
            text-align: center;
            color: rgba(255, 255, 255, 0.55);
            text-decoration: none;
            font-size: 0.84rem;
            transition: color 0.2s;
        }
        .role-login-back:hover { color: var(--accent-text, #f0a6c4); }

        @media (max-width: 640px) {
            .role-login-page {
                padding-top: 24px;
                padding-left: var(--content-padding-x, 12px);
                padding-right: var(--content-padding-x, 12px);
            }

            .role-login-card-body {
                padding: 20px 18px;
            }

            /* 2 択なのでモバイルでも横並びを維持（縦積みにしない） */
            .role-login-role { min-height: 76px; }
        }

        @media (max-width: 360px) {
            .role-login-page {
                padding-left: 10px;
                padding-right: 10px;
            }

            .role-login-card-body {
                padding: 16px 14px;
            }
        }
    </style>
@endsection
