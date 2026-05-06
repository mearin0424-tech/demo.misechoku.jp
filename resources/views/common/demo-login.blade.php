@extends('layouts.app')

@php
    $selectedRole = old('role', $roleGroups[0]['key'] ?? 'cast');
    $demoEmails = [
        'cast' => 'cast@demo.com',
        'shop' => 'shop@demo.com',
        'admin' => 'admin@demo.com',
    ];
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
                                        <span>{{ $group['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>

                            @if (in_array($selectedRole, ['cast', 'shop']))
                            <a href="{{ route('login.line.redirect', ['role' => $selectedRole]) }}" class="demo-login-line-btn" id="demo-login-line-btn" data-base-url="{{ route('login.line.redirect') }}">
                                <span class="demo-login-line-btn-shine"></span>
                                <span>LINEでログイン</span>
                            </a>
                            @endif

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
                                    </div>
                                </label>

                                <label class="demo-login-field">
                                    <span>メールアドレス</span>
                                    <div class="demo-login-input-wrap">
                                        <input type="email" id="demo-email-display" value="" readonly>
                                    </div>
                                </label>

                                <label class="demo-login-field">
                                    <span>パスワード</span>
                                    <div class="demo-login-input-wrap">
                                        <input type="password" value="demo_password_123" readonly>
                                    </div>
                                </label>
                            </div>

                            <p class="demo-login-inline-note">
                                アカウントを選んでログインできます。
                            </p>

                            <button type="submit" name="auth_channel" value="standard" class="demo-login-submit">
                                <span>ログイン</span>
                            </button>
                        </form>
                    </div>

                    <div class="demo-login-panel" data-auth-panel="register">
                        <div class="demo-register-panel">
                            <h2 class="demo-register-title">選択してください</h2>

                            <div class="demo-register-links">
                                <a href="{{ route('cast.register') }}" class="demo-register-link">
                                    <span>キャスト登録</span>
                                </a>
                                <a href="{{ route('shop.register') }}" class="demo-register-link">
                                    <span>店舗登録</span>
                                </a>
                                <a href="{{ route('login.demo') }}" class="demo-register-link">
                                    <span>運営ログイン</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
            background:
                radial-gradient(circle at top, rgba(255, 166, 201, 0.16), transparent 36%),
                linear-gradient(180deg, var(--color-main) 0%, var(--dark-bg) 100%);
            color: var(--color-text);
            font-family: "Helvetica Neue", Arial, "Hiragino Sans", "Hiragino Kaku Gothic ProN", "Meiryo", sans-serif;
        }

        body.page-demo-login #bg-layer {
            background:
                radial-gradient(circle at 22% 20%, rgba(220, 181, 104, 0.14), transparent 26%),
                radial-gradient(circle at 80% 72%, rgba(220, 181, 104, 0.16), transparent 30%),
                linear-gradient(180deg, rgba(74, 18, 42, 0.66), rgba(56, 13, 31, 0.95));
        }

        body.page-demo-login main {
            padding: 0;
        }

        body.page-demo-login .content-wrapper {
            width: 100%;
            max-width: 100%;
            padding: 0;
            background: transparent;
            box-shadow: none;
            overflow-x: hidden;
        }

        body.page-demo-login #app {
            max-width: 100%;
        }

        .demo-login-shell {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            width: 100%;
            max-width: 100vw;
            box-sizing: border-box;
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
            background: linear-gradient(180deg, rgba(74, 18, 42, 0.62) 0%, rgba(56, 13, 31, 0.84) 45%, rgba(56, 13, 31, 0.98) 100%);
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
            width: 100%;
            max-width: min(460px, calc(100vw - 2 * var(--content-padding-x, 16px)));
            margin: 0 auto;
            padding: 40px max(16px, var(--content-padding-x)) 28px;
            box-sizing: border-box;
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
            max-width: 100%;
        }

        .demo-login-card {
            overflow: hidden;
            border: 1px solid rgba(220, 181, 104, 0.24);
            border-radius: 30px;
            background: linear-gradient(155deg, rgba(255, 240, 245, 0.06), rgba(92, 23, 53, 0.88));
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            max-width: 100%;
            box-sizing: border-box;
        }

        .demo-login-tabs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            border-bottom: 1px solid rgba(220, 181, 104, 0.18);
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
            color: var(--gold-light);
        }

        .demo-login-tab.is-active::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--gold-deep));
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
            border-color: rgba(220, 181, 104, 0.45);
            color: var(--gold-light);
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
        .demo-register-link:hover {
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
            color: var(--gold-light);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.14em;
        }

        .demo-login-select-wrap,
        .demo-login-input-wrap {
            position: relative;
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
            box-sizing: border-box;
            max-width: 100%;
        }

        .demo-login-select {
            display: none;
            padding: 0 14px;
        }

        .demo-login-select.is-active {
            display: block;
        }

        .demo-login-input-wrap input {
            padding: 0 16px;
        }

        .demo-login-select:focus,
        .demo-login-input-wrap input:focus {
            outline: none;
            border-color: rgba(220, 181, 104, 0.56);
            box-shadow: 0 0 0 3px rgba(220, 181, 104, 0.16);
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
            background: linear-gradient(145deg, var(--gold), var(--gold-deep));
            color: #2a1406;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }

        .demo-register-title {
            margin: 0;
            color: #f8eed0;
            font-size: 1.2rem;
        }

        .demo-register-links {
            display: grid;
            gap: 12px;
            margin-top: 18px;
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
                padding-left: var(--content-padding-x, 12px);
                padding-right: var(--content-padding-x, 12px);
            }

            .demo-login-form,
            .demo-register-panel {
                padding-left: 18px;
                padding-right: 18px;
            }

            .demo-role-switch {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 360px) {
            .demo-login-page {
                padding-left: 10px;
                padding-right: 10px;
            }

            .demo-login-form,
            .demo-register-panel {
                padding-left: 14px;
                padding-right: 14px;
            }

            .demo-login-footer-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 8px;
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

                const lineBtn = document.getElementById('demo-login-line-btn');
                const switchRole = function (role) {
                    roleInput.value = role;

                    roleButtons.forEach(function (button) {
                        button.classList.toggle('is-active', button.dataset.roleTab === role);
                    });

                    accountSelects.forEach(function (select) {
                        select.classList.toggle('is-active', select.dataset.accountSelect === role);
                    });

                    if (lineBtn) {
                        if (role === 'cast' || role === 'shop') {
                            lineBtn.style.display = '';
                            lineBtn.href = lineBtn.getAttribute('data-base-url') + '?role=' + encodeURIComponent(role);
                        } else {
                            lineBtn.style.display = 'none';
                        }
                    }

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

                switchRole(roleInput.value || '{{ $selectedRole }}');
            });
        </script>
    @endpush
@endsection

