@extends('layouts.app')

@php
    $selectedRole = old('role', $roleGroups[0]['key'] ?? 'cast');
    $demoEmails = [
        'cast' => 'cast@demo.com',
        'shop' => 'shop@demo.com',
        'admin' => 'admin@demo.com',
    ];
    $quickAccessGroups = collect($roleGroups)
        ->map(function ($group) use ($demoEmails) {
            $group['demo_email'] = $demoEmails[$group['key']] ?? 'demo@misechoku.jp';
            $group['primary_account'] = $group['accounts'][0] ?? null;

            return $group;
        })
        ->filter(fn ($group) => $group['primary_account'])
        ->values();
@endphp

@section('title', 'ログイン（デモ）')
@section('body-class', 'page-demo-login')
@section('guide_message', '')

@section('content')
    <div class="demo-login-shell">
        <div class="demo-login-bg">
            <div class="demo-login-bg-photo"></div>
            <div class="demo-login-bg-overlay"></div>
            <div class="demo-login-bg-glow demo-login-bg-glow-left"></div>
            <div class="demo-login-bg-glow demo-login-bg-glow-right"></div>
        </div>

        <div class="demo-login-page">
            <div class="demo-login-brand">
                <div class="demo-login-logo-wrap">
                    <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="demo-login-logo">
                </div>
                <h1 class="demo-login-brand-title">MISECHOKU</h1>
                <p class="demo-login-brand-subtitle">ナイトワーク特化型マッチングプラットフォーム</p>
            </div>

            @if (session('message'))
                <div class="demo-login-alert demo-login-alert-info">
                    {{ session('message') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="demo-login-alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="demo-login-card-wrap">
                <div class="demo-login-card">
                    <div class="demo-login-tabs" role="tablist" aria-label="認証切り替え">
                        <button type="button" class="demo-login-tab is-active" data-auth-tab="login">ログイン</button>
                        <button type="button" class="demo-login-tab" data-auth-tab="register">新規登録</button>
                    </div>

                    <div class="demo-login-panel is-active" data-auth-panel="login">
                        <form method="POST" action="{{ route('login.demo.post') }}" class="demo-login-form" id="demo-login-form">
                            @csrf
                            <input type="hidden" name="role" id="demo-role-input" value="{{ $selectedRole }}">
                            <input type="hidden" name="account_id" id="demo-account-input" value="">

                            <div class="demo-role-switch" role="tablist" aria-label="ログインロール切り替え">
                                @foreach ($roleGroups as $group)
                                    <button
                                        type="button"
                                        class="demo-role-chip {{ $selectedRole === $group['key'] ? 'is-active' : '' }}"
                                        data-role-tab="{{ $group['key'] }}"
                                        data-demo-email="{{ $demoEmails[$group['key']] ?? 'demo@misechoku.jp' }}"
                                        data-role-label="{{ $group['label'] }}"
                                    >
                                        <i class="fas {{ $group['icon'] }}"></i>
                                        <span>{{ $group['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <button type="submit" name="auth_channel" value="line" class="demo-login-line-btn">
                                <span class="demo-login-line-btn-shine"></span>
                                <i class="fab fa-line"></i>
                                <span>LINEでログイン</span>
                            </button>

                            <div class="demo-login-divider">
                                <span>OR</span>
                            </div>

                            <div class="demo-login-field-group">
                                <label class="demo-login-field">
                                    <span>ログイン種別</span>
                                    <div class="demo-login-select-wrap">
                                        @foreach ($roleGroups as $group)
                                            <select
                                                class="demo-login-select {{ $selectedRole === $group['key'] ? 'is-active' : '' }}"
                                                data-account-select="{{ $group['key'] }}"
                                            >
                                                @foreach ($group['accounts'] as $account)
                                                    <option
                                                        value="{{ $account['id'] }}"
                                                        @selected(old('role', $selectedRole) === $group['key'] && old('account_id', $group['accounts'][0]['id'] ?? null) == $account['id'])
                                                    >
                                                        {{ $account['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endforeach
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </label>

                                <label class="demo-login-field">
                                    <span>メールアドレス</span>
                                    <div class="demo-login-input-wrap">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" id="demo-email-display" value="" readonly>
                                    </div>
                                </label>

                                <label class="demo-login-field">
                                    <div class="demo-login-label-row">
                                        <span>パスワード</span>
                                        <small>デモ用固定</small>
                                    </div>
                                    <div class="demo-login-input-wrap">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" value="demo_password_123" readonly>
                                    </div>
                                </label>
                            </div>

                            <p class="demo-login-inline-note">
                                既存アカウントを選ぶだけで、通常ログインと LINE ログインの両方を体験できます。
                            </p>

                            <button type="submit" name="auth_channel" value="standard" class="demo-login-submit">
                                <span>ログイン</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>

                    <div class="demo-login-panel" data-auth-panel="register">
                        <div class="demo-register-panel">
                            <p class="demo-register-eyebrow">REGISTER</p>
                            <h2 class="demo-register-title">新規登録もすぐに試せます</h2>
                            <p class="demo-register-desc">
                                デモではキャストと店舗マネージャーの登録画面へ進めます。
                                管理運営者は既存アカウントからログインしてください。
                            </p>

                            <div class="demo-register-links">
                                <a href="{{ route('cast.register') }}" class="demo-register-link">
                                    <i class="fas fa-user-plus"></i>
                                    <span>キャスト新規登録</span>
                                </a>
                                <a href="{{ route('shop.register') }}" class="demo-register-link">
                                    <i class="fas fa-store"></i>
                                    <span>店舗マネージャー新規登録</span>
                                </a>
                            </div>

                            <div class="demo-register-note">
                                <i class="fas fa-crown"></i>
                                <span>運営管理者はデモ用テストアカウントからお試しください。</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($quickAccessGroups->isNotEmpty())
                <section class="demo-quick-login">
                    <div class="demo-quick-login-head">
                        <i class="fas fa-star"></i>
                        <h2>【デモ用】テストアカウント</h2>
                    </div>

                    <div class="demo-quick-login-grid">
                        @foreach ($quickAccessGroups as $group)
                            <button
                                type="button"
                                class="demo-quick-login-item"
                                data-quick-role="{{ $group['key'] }}"
                                data-quick-account="{{ $group['primary_account']['id'] }}"
                                data-quick-channel="standard"
                            >
                                <div class="demo-quick-login-icon">
                                    <i class="fas {{ $group['icon'] }}"></i>
                                </div>
                                <div class="demo-quick-login-copy">
                                    <p>{{ $group['label'] }}</p>
                                    <span>{{ $group['demo_email'] }}</span>
                                </div>
                                <i class="fas fa-chevron-right demo-quick-login-arrow"></i>
                            </button>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('login.demo.post') }}" id="demo-quick-login-form" class="demo-hidden-form">
                        @csrf
                        <input type="hidden" name="role" id="demo-quick-role-input">
                        <input type="hidden" name="account_id" id="demo-quick-account-input">
                        <input type="hidden" name="auth_channel" id="demo-quick-channel-input" value="standard">
                    </form>
                </section>
            @endif

            <div class="demo-login-footer-links">
                <a href="{{ route('pages.official.terms') }}">利用規約</a>
                <span>|</span>
                <a href="{{ route('pages.official.privacy') }}">プライバシーポリシー</a>
            </div>
        </div>
    </div>

    <style>
        body.page-demo-login #global-header,
        body.page-demo-login #bottom-nav,
        body.page-demo-login #side-menu,
        body.page-demo-login #character-guide,
        body.page-demo-login .header-right {
            display: none !important;
        }

        body.page-demo-login,
        body.page-demo-login #app,
        body.page-demo-login .main-layout-container,
        body.page-demo-login main {
            min-height: 100vh;
        }

        body.page-demo-login {
            background: #120405;
            color: #f5e6e6;
            font-family: "Helvetica Neue", Arial, "Hiragino Sans", "Hiragino Kaku Gothic ProN", "Meiryo", sans-serif;
        }

        body.page-demo-login #bg-layer {
            background:
                radial-gradient(circle at 22% 20%, rgba(230, 208, 128, 0.12), transparent 26%),
                radial-gradient(circle at 80% 72%, rgba(179, 138, 34, 0.18), transparent 30%),
                linear-gradient(180deg, rgba(18, 4, 5, 0.6), rgba(18, 4, 5, 0.95));
        }

        body.page-demo-login main {
            padding: 0;
        }

        body.page-demo-login .content-wrapper {
            width: 100%;
            max-width: none;
            padding: 0;
            background: transparent;
            box-shadow: none;
        }

        .demo-hidden-form {
            display: none;
        }

        .demo-login-shell {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .demo-login-bg,
        .demo-login-bg-photo,
        .demo-login-bg-overlay {
            position: absolute;
            inset: 0;
        }

        .demo-login-bg-photo {
            background:
                linear-gradient(135deg, rgba(10, 2, 3, 0.12), rgba(10, 2, 3, 0.48)),
                url('https://images.unsplash.com/photo-1514933651103-005eec06c04b?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            opacity: 0.32;
            transform: scale(1.06);
            filter: blur(4px);
        }

        .demo-login-bg-overlay {
            background: linear-gradient(180deg, rgba(18, 4, 5, 0.62) 0%, rgba(18, 4, 5, 0.82) 45%, #120405 100%);
        }

        .demo-login-bg-glow {
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
        }

        .demo-login-bg-glow-left {
            top: 12%;
            left: 8%;
            background: rgba(230, 208, 128, 0.12);
        }

        .demo-login-bg-glow-right {
            right: 8%;
            bottom: 10%;
            background: rgba(179, 138, 34, 0.12);
        }

        .demo-login-page {
            position: relative;
            z-index: 1;
            width: min(100%, 460px);
            margin: 0 auto;
            padding: 40px 16px 28px;
        }

        .demo-login-brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .demo-login-logo-wrap {
            margin-bottom: 12px;
        }

        .demo-login-logo {
            width: 210px;
            max-width: 72%;
            filter: drop-shadow(0 10px 28px rgba(0, 0, 0, 0.4));
        }

        .demo-login-brand-title {
            margin: 0 0 8px;
            font-family: "Yu Mincho", "游明朝", "Hiragino Mincho ProN", "Noto Serif JP", serif;
            font-size: clamp(2rem, 6vw, 2.5rem);
            letter-spacing: 0.18em;
            color: transparent;
            background: linear-gradient(90deg, #e5c158 0%, #fdf0b2 50%, #b38a22 100%);
            background-clip: text;
            -webkit-background-clip: text;
        }

        .demo-login-brand-subtitle {
            margin: 0;
            font-size: 0.72rem;
            letter-spacing: 0.25em;
            color: #a89090;
        }

        .demo-login-alert {
            margin-bottom: 14px;
            padding: 13px 14px;
            border: 1px solid rgba(255, 177, 177, 0.32);
            border-radius: 18px;
            background: rgba(122, 24, 44, 0.42);
            color: #fff1f2;
            font-size: 0.82rem;
            line-height: 1.7;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .demo-login-alert-info {
            border-color: rgba(230, 208, 128, 0.24);
            background: rgba(255, 255, 255, 0.05);
            color: #f9efcf;
        }

        .demo-login-card-wrap {
            position: relative;
        }

        .demo-login-card {
            overflow: hidden;
            border: 1px solid rgba(230, 208, 128, 0.2);
            border-radius: 30px;
            background: rgba(26, 11, 14, 0.8);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        .demo-login-tabs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            border-bottom: 1px solid rgba(230, 208, 128, 0.1);
        }

        .demo-login-tab {
            position: relative;
            padding: 18px 12px 16px;
            border: 0;
            background: transparent;
            color: #a89090;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .demo-login-tab.is-active {
            color: #e6d080;
        }

        .demo-login-tab.is-active::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2px;
            background: linear-gradient(90deg, #e5c158, #b38a22);
            box-shadow: 0 0 12px rgba(212, 175, 55, 0.5);
        }

        .demo-login-panel {
            display: none;
        }

        .demo-login-panel.is-active {
            display: block;
        }

        .demo-login-form,
        .demo-register-panel {
            padding: 24px;
        }

        .demo-role-switch {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 18px;
        }

        .demo-role-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 8px;
            border: 1px solid rgba(61, 26, 31, 1);
            border-radius: 14px;
            background: rgba(18, 4, 5, 0.58);
            color: #a89090;
            font-size: 0.74rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .demo-role-chip.is-active {
            border-color: rgba(230, 208, 128, 0.42);
            color: #e6d080;
            background: rgba(35, 15, 18, 0.95);
        }

        .demo-login-line-btn,
        .demo-login-submit {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 54px;
            border: 0;
            border-radius: 16px;
            font-weight: 700;
            letter-spacing: 0.08em;
            cursor: pointer;
            transition: transform 0.18s ease, filter 0.18s ease, box-shadow 0.18s ease;
            overflow: hidden;
        }

        .demo-login-line-btn:hover,
        .demo-login-submit:hover,
        .demo-register-link:hover,
        .demo-quick-login-item:hover {
            transform: translateY(-1px);
        }

        .demo-login-line-btn {
            margin-bottom: 18px;
            background: #06c755;
            color: #fff;
            box-shadow: 0 4px 15px rgba(6, 199, 85, 0.3);
        }

        .demo-login-line-btn-shine {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.18), transparent);
            transform: translateX(-100%);
        }

        .demo-login-line-btn:hover .demo-login-line-btn-shine {
            animation: demoLineShine 1.5s ease infinite;
        }

        .demo-login-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            color: #a89090;
            font-size: 0.74rem;
            letter-spacing: 0.22em;
        }

        .demo-login-divider::before,
        .demo-login-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(230, 208, 128, 0.1);
        }

        .demo-login-field-group {
            display: grid;
            gap: 14px;
        }

        .demo-login-field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .demo-login-field > span,
        .demo-login-label-row span {
            margin-left: 2px;
            color: #e6d080;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.14em;
        }

        .demo-login-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .demo-login-label-row small {
            color: #a89090;
            font-size: 0.68rem;
            letter-spacing: 0.08em;
        }

        .demo-login-select-wrap,
        .demo-login-input-wrap {
            position: relative;
        }

        .demo-login-select-wrap i,
        .demo-login-input-wrap i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #a89090;
            pointer-events: none;
        }

        .demo-login-select-wrap i {
            right: 14px;
        }

        .demo-login-input-wrap i {
            left: 14px;
        }

        .demo-login-select,
        .demo-login-input-wrap input {
            width: 100%;
            min-height: 54px;
            border: 1px solid #3d1a1f;
            border-radius: 16px;
            background: rgba(18, 4, 5, 0.5);
            color: #f5e6e6;
            font-size: 0.92rem;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .demo-login-select {
            display: none;
            padding: 0 42px 0 14px;
            appearance: none;
        }

        .demo-login-select.is-active {
            display: block;
        }

        .demo-login-input-wrap input {
            padding: 0 16px 0 42px;
        }

        .demo-login-select:focus,
        .demo-login-input-wrap input:focus {
            outline: none;
            border-color: rgba(230, 208, 128, 0.5);
            box-shadow: 0 0 0 3px rgba(230, 208, 128, 0.12);
        }

        .demo-login-input-wrap input[readonly] {
            cursor: default;
        }

        .demo-login-inline-note {
            margin: 14px 2px 0;
            color: #a89090;
            font-size: 0.76rem;
            line-height: 1.8;
        }

        .demo-login-submit {
            margin-top: 18px;
            background: linear-gradient(90deg, #e5c158 0%, #fdf0b2 50%, #b38a22 100%);
            color: #120405;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .demo-register-eyebrow {
            margin: 0 0 10px;
            color: #e6d080;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.22em;
        }

        .demo-register-title {
            margin: 0 0 10px;
            color: #f8eed0;
            font-size: 1.2rem;
        }

        .demo-register-desc {
            margin: 0;
            color: #cebcbc;
            font-size: 0.86rem;
            line-height: 1.9;
        }

        .demo-register-links {
            display: grid;
            gap: 12px;
            margin-top: 20px;
        }

        .demo-register-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 54px;
            border: 1px solid rgba(230, 208, 128, 0.18);
            border-radius: 16px;
            background: rgba(18, 4, 5, 0.46);
            color: #f5e6e6;
            text-decoration: none;
            transition: border-color 0.18s ease, background 0.18s ease, transform 0.18s ease;
        }

        .demo-register-note {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
            padding: 14px 16px;
            border: 1px solid rgba(230, 208, 128, 0.14);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.03);
            color: #a89090;
            font-size: 0.8rem;
            line-height: 1.7;
        }

        .demo-register-note i {
            color: #e6d080;
        }

        .demo-quick-login {
            margin-top: 24px;
            padding: 20px;
            border: 1px solid rgba(230, 208, 128, 0.2);
            border-radius: 24px;
            background: rgba(26, 11, 14, 0.6);
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.24);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .demo-quick-login-head {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 14px;
            color: #e6d080;
        }

        .demo-quick-login-head h2 {
            margin: 0;
            font-size: 0.78rem;
            letter-spacing: 0.18em;
        }

        .demo-quick-login-grid {
            display: grid;
            gap: 10px;
        }

        .demo-quick-login-item {
            display: grid;
            grid-template-columns: 40px 1fr auto;
            gap: 12px;
            align-items: center;
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #3d1a1f;
            border-radius: 16px;
            background: rgba(18, 4, 5, 0.8);
            color: #f5e6e6;
            text-align: left;
            cursor: pointer;
            transition: border-color 0.18s ease, background 0.18s ease, transform 0.18s ease;
        }

        .demo-quick-login-item:hover {
            border-color: rgba(230, 208, 128, 0.38);
            background: #230f12;
        }

        .demo-quick-login-icon {
            display: grid;
            place-items: center;
            width: 40px;
            height: 40px;
            border: 1px solid rgba(230, 208, 128, 0.2);
            border-radius: 999px;
            color: #e6d080;
            background: #1a0b0e;
        }

        .demo-quick-login-copy p {
            margin: 0 0 3px;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .demo-quick-login-copy span {
            color: #a89090;
            font-size: 0.68rem;
            letter-spacing: 0.12em;
        }

        .demo-quick-login-arrow {
            color: #a89090;
        }

        .demo-login-footer-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 24px;
            color: #3d1a1f;
            font-size: 0.7rem;
            letter-spacing: 0.16em;
        }

        .demo-login-footer-links a {
            color: #a89090;
            text-decoration: none;
        }

        .demo-login-footer-links a:hover {
            color: #e6d080;
        }

        @keyframes demoLineShine {
            100% {
                transform: translateX(100%);
            }
        }

        @media (max-width: 640px) {
            .demo-login-page {
                padding-top: 24px;
            }

            .demo-login-form,
            .demo-register-panel,
            .demo-quick-login {
                padding-left: 18px;
                padding-right: 18px;
            }

            .demo-role-switch {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const loginForm = document.getElementById('demo-login-form');
                if (!loginForm) {
                    return;
                }

                const roleInput = document.getElementById('demo-role-input');
                const accountInput = document.getElementById('demo-account-input');
                const emailDisplay = document.getElementById('demo-email-display');
                const roleButtons = Array.from(document.querySelectorAll('[data-role-tab]'));
                const accountSelects = Array.from(document.querySelectorAll('[data-account-select]'));
                const tabButtons = Array.from(document.querySelectorAll('[data-auth-tab]'));
                const tabPanels = Array.from(document.querySelectorAll('[data-auth-panel]'));

                const syncAccountInput = function (role) {
                    const activeSelect = document.querySelector('[data-account-select="' + role + '"]');
                    const activeRoleButton = document.querySelector('[data-role-tab="' + role + '"]');

                    if (!activeSelect || !activeRoleButton) {
                        return;
                    }

                    accountInput.value = activeSelect.value;
                    emailDisplay.value = activeRoleButton.dataset.demoEmail || 'demo@misechoku.jp';
                };

                const switchRole = function (role) {
                    roleInput.value = role;

                    roleButtons.forEach(function (button) {
                        button.classList.toggle('is-active', button.dataset.roleTab === role);
                    });

                    accountSelects.forEach(function (select) {
                        select.classList.toggle('is-active', select.dataset.accountSelect === role);
                    });

                    syncAccountInput(role);
                };

                roleButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        switchRole(button.dataset.roleTab);
                    });
                });

                accountSelects.forEach(function (select) {
                    select.addEventListener('change', function () {
                        if (select.dataset.accountSelect === roleInput.value) {
                            accountInput.value = select.value;
                        }
                    });
                });

                tabButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        const target = button.dataset.authTab;

                        tabButtons.forEach(function (tabButton) {
                            tabButton.classList.toggle('is-active', tabButton === button);
                        });

                        tabPanels.forEach(function (panel) {
                            panel.classList.toggle('is-active', panel.dataset.authPanel === target);
                        });
                    });
                });

                document.querySelectorAll('[data-quick-role]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const form = document.getElementById('demo-quick-login-form');
                        const roleField = document.getElementById('demo-quick-role-input');
                        const accountField = document.getElementById('demo-quick-account-input');
                        const channelField = document.getElementById('demo-quick-channel-input');

                        if (!form || !roleField || !accountField || !channelField) {
                            return;
                        }

                        roleField.value = button.dataset.quickRole;
                        accountField.value = button.dataset.quickAccount;
                        channelField.value = button.dataset.quickChannel || 'standard';
                        form.submit();
                    });
                });

                switchRole(roleInput.value || '{{ $selectedRole }}');
            });
        </script>
    @endpush
@endsection

