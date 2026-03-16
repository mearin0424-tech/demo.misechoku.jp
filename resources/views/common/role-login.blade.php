@extends('layouts.app')

@section('title', $title)
@section('body-class', $bodyClass)
@section('guide_message', $guideMessage)

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
                        <a href="{{ route('login.demo') }}" class="role-login-role {{ $role === 'cast' ? 'is-active' : '' }}">キャスト</a>
                        <a href="{{ route('login.demo') }}" class="role-login-role {{ $role === 'shop' ? 'is-active' : '' }}">店舗</a>
                        <a href="{{ route('login.demo') }}" class="role-login-role">運営</a>
                    </div>

                    <div class="role-login-copy">
                        <h2 class="role-login-title">{{ $heroTitle }}</h2>
                    </div>

                    <form method="POST" action="{{ $formAction }}" class="role-login-form">
                        @csrf

                        <label class="role-login-field">
                            <span>メールアドレス</span>
                            <div class="role-login-input-wrap">
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="example@misechoku.jp">
                            </div>
                        </label>

                        <label class="role-login-field">
                            <span>パスワード</span>
                            <div class="role-login-input-wrap">
                                <input type="password" name="password" placeholder="••••••••">
                            </div>
                        </label>

                        <button type="submit" class="role-login-submit">
                            <span>ログイン</span>
                        </button>
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
                radial-gradient(circle at 20% 20%, rgba(229, 193, 88, 0.1), transparent 24%),
                radial-gradient(circle at 80% 74%, rgba(179, 138, 34, 0.14), transparent 28%),
                linear-gradient(180deg, rgba(18, 4, 5, 0.68), rgba(18, 4, 5, 0.94));
        }

        body.page-auth-login {
            background: #120405;
            color: #f5e6e6;
            font-family: "Helvetica Neue", Arial, "Hiragino Sans", "Hiragino Kaku Gothic ProN", "Meiryo", sans-serif;
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
            background: linear-gradient(180deg, rgba(18, 4, 5, 0.62) 0%, rgba(18, 4, 5, 0.82) 45%, #120405 100%);
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
            background: rgba(230, 208, 128, 0.1);
        }

        .role-login-bg-glow-right {
            right: 8%;
            bottom: 10%;
            background: rgba(179, 138, 34, 0.1);
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
            color: #f9efcf;
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
            border: 1px solid rgba(229, 193, 88, 0.24);
            background: rgba(255, 255, 255, 0.05);
            color: #f8ead0;
        }

        .role-login-alert-error {
            border: 1px solid rgba(255, 177, 177, 0.32);
            background: rgba(122, 24, 44, 0.42);
            color: #fff1f2;
        }

        .role-login-card {
            overflow: hidden;
            border: 1px solid rgba(229, 193, 88, 0.2);
            border-radius: 30px;
            background: rgba(26, 11, 14, 0.8);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            max-width: 100%;
            box-sizing: border-box;
        }

        .role-login-tabs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            border-bottom: 1px solid rgba(229, 193, 88, 0.1);
        }

        .role-login-tab {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 56px;
            padding: 16px 12px;
            color: #a89090;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.18em;
        }

        .role-login-tab.is-active {
            color: #e6d080;
        }

        .role-login-tab.is-active::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2px;
            background: linear-gradient(90deg, #e5c158, #b38a22);
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.5);
        }

        .role-login-card-body {
            padding: 24px;
        }

        .role-login-role-switch {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 18px;
        }

        .role-login-role {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            border: 1px solid #3d1a1f;
            border-radius: 14px;
            background: rgba(18, 4, 5, 0.58);
            color: #a89090;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .role-login-role.is-active {
            border-color: rgba(230, 208, 128, 0.42);
            color: #e6d080;
            background: rgba(35, 15, 18, 0.95);
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
            color: #e6d080;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.14em;
        }

        .role-login-input-wrap {
            position: relative;
        }

        .role-login-input-wrap input {
            width: 100%;
            min-height: 54px;
            padding: 0 16px;
            border-radius: 16px;
            border: 1px solid #3d1a1f;
            background: rgba(18, 4, 5, 0.5);
            color: #fff;
            font-size: 0.94rem;
            box-sizing: border-box;
            max-width: 100%;
        }

        .role-login-input-wrap input::placeholder {
            color: rgba(214, 198, 198, 0.48);
        }

        .role-login-input-wrap input:focus {
            outline: none;
            border-color: rgba(230, 208, 128, 0.5);
            box-shadow: 0 0 0 3px rgba(230, 208, 128, 0.12);
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
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .role-login-submit:hover,
        .role-login-secondary:hover {
            transform: translateY(-1px);
        }

        .role-login-submit {
            border: none;
            background: linear-gradient(90deg, #e5c158 0%, #fdf0b2 50%, #b38a22 100%);
            color: #120405;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .role-login-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 18px;
        }

        .role-login-secondary {
            border: 1px solid rgba(229, 193, 88, 0.22);
            background: rgba(255, 255, 255, 0.04);
            color: #fff4d6;
        }

        .role-login-back {
            text-align: center;
            color: rgba(246, 217, 139, 0.9);
            text-decoration: none;
            font-size: 0.84rem;
        }

        @media (max-width: 640px) {
            .role-login-page {
                padding-top: 24px;
                padding-left: var(--content-padding-x, 12px);
                padding-right: var(--content-padding-x, 12px);
            }

            .role-login-card-body {
                padding: 20px 18px;
            }

            .role-login-role-switch {
                grid-template-columns: 1fr;
            }
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
