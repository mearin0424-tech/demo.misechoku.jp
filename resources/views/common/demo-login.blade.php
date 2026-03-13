@extends('layouts.app')

@section('title', 'ログイン（デモ）')
@section('body-class', 'page-demo-login')
@section('guide_message', "ようこそ、ミセチョクへ。\n役割とアカウントを選ぶだけで、通常ログインもLINEログインも試せるよ。")

@section('content')
    <div class="demo-login-page">
        <div class="demo-login-hero">
            <p class="demo-login-eyebrow">LUXURY DEMO ENTRANCE</p>
            <div class="demo-login-logo-wrap">
                <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="demo-login-logo">
            </div>

            <h1 class="demo-login-title">体験したい視点から、ミセチョクへ。</h1>
            <p class="demo-login-desc">
                管理運営者・店舗マネージャー・キャストの既存データからアカウントを選び、
                通常ログインと LINE ログインの両方をデモ体験できます。
            </p>
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

        <div class="demo-login-grid">
            @foreach ($roleGroups as $group)
                <form method="POST" action="{{ route('login.demo.post') }}" class="demo-login-card">
                    @csrf
                    <input type="hidden" name="role" value="{{ $group['key'] }}">

                    <div class="demo-login-card-head">
                        <span class="demo-login-card-icon"><i class="fas {{ $group['icon'] }}"></i></span>
                        <div class="demo-login-card-copy">
                            <p class="demo-login-btn-label">{{ $group['eyebrow'] }}</p>
                            <h2 class="demo-login-card-title">{{ $group['label'] }}</h2>
                            <p class="demo-login-card-desc">{{ $group['description'] }}</p>
                        </div>
                    </div>

                    <label class="demo-login-field">
                        <span>登録済みデータから選択</span>
                        <select name="account_id" required>
                            @foreach ($group['accounts'] as $account)
                                <option value="{{ $account['id'] }}" @selected(old('role') === $group['key'] && old('account_id') === $account['id'])>
                                    {{ $account['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="demo-login-actions">
                        <button type="submit" name="auth_channel" value="standard" class="demo-login-action demo-login-action-primary">
                            <i class="fas fa-arrow-right-to-bracket"></i>
                            <span>通常ログイン</span>
                        </button>
                        <button type="submit" name="auth_channel" value="line" class="demo-login-action demo-login-action-line">
                            <i class="fab fa-line"></i>
                            <span>LINEでログイン</span>
                        </button>
                    </div>

                    @if ($group['register_url'])
                        <a href="{{ $group['register_url'] }}" class="demo-login-inline-link">{{ $group['register_label'] }}</a>
                    @else
                        <p class="demo-login-inline-note">{{ $group['register_label'] }}</p>
                    @endif
                </form>
            @endforeach
        </div>

        <section class="demo-register-panel">
            <div class="demo-register-panel-head">
                <p class="demo-login-eyebrow">REGISTER</p>
                <h2 class="demo-register-panel-title">新規登録もすぐに試せます</h2>
                <p class="demo-register-panel-desc">
                    デモではキャストと店舗マネージャーの登録画面へ遷移できます。
                    管理運営者は既存の運営アカウントを選択してお試しください。
                </p>
            </div>

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
        </section>

        <div class="demo-login-note">
            <span class="demo-login-note-badge">DEMO</span>
            <p>認証はデモ用です。実パスワード入力は不要で、既存アカウントを選択して各画面の体験を始められます。</p>
        </div>
    </div>

    <style>
        body.page-demo-login {
            background:
                radial-gradient(circle at top, rgba(229, 193, 88, 0.18), transparent 34%),
                linear-gradient(180deg, #120405 0%, #190509 42%, #120405 100%);
        }

        body.page-demo-login #bg-layer {
            background:
                radial-gradient(circle at 18% 12%, rgba(229, 193, 88, 0.08), transparent 22%),
                radial-gradient(circle at 85% 18%, rgba(255, 255, 255, 0.05), transparent 18%),
                radial-gradient(circle at 50% 100%, rgba(122, 24, 44, 0.25), transparent 32%);
        }

        body.page-demo-login main {
            padding-bottom: 36px;
        }

        .demo-login-page {
            position: relative;
            max-width: 560px;
            margin: 0 auto;
            padding: 44px 0 24px;
        }

        .demo-login-page::before {
            content: '';
            position: absolute;
            inset: 18px 0 auto;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(229, 193, 88, 0.45), transparent);
        }

        .demo-login-hero {
            position: relative;
            padding: 36px 28px 28px;
            margin-bottom: 18px;
            border: 1px solid rgba(229, 193, 88, 0.22);
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02)),
                linear-gradient(135deg, rgba(49, 17, 23, 0.96), rgba(20, 7, 10, 0.98));
            box-shadow:
                0 24px 60px rgba(0, 0, 0, 0.42),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            text-align: center;
            overflow: hidden;
        }

        .demo-login-hero::after {
            content: '';
            position: absolute;
            inset: auto -50px -70px auto;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(229, 193, 88, 0.16), transparent 68%);
            pointer-events: none;
        }

        .demo-login-eyebrow {
            margin: 0 0 16px;
            font-size: 0.72rem;
            letter-spacing: 0.28em;
            color: rgba(253, 240, 178, 0.78);
        }

        .demo-login-logo-wrap {
            margin-bottom: 22px;
        }

        .demo-login-logo {
            width: 220px;
            max-width: 72%;
            filter: drop-shadow(0 8px 24px rgba(0, 0, 0, 0.35));
        }

        .demo-login-title {
            margin-bottom: 10px;
            font-size: clamp(1.75rem, 4vw, 2.2rem);
            line-height: 1.35;
            color: #f9efcf;
        }

        .demo-login-desc {
            max-width: 28rem;
            margin: 0 auto;
            font-size: 0.95rem;
            line-height: 1.9;
            color: rgba(236, 221, 221, 0.88);
        }

        .demo-login-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .demo-login-alert {
            padding: 12px 14px;
            border: 1px solid rgba(255, 177, 177, 0.38);
            border-radius: 16px;
            background: rgba(122, 24, 44, 0.42);
            color: #fff1f2;
            font-size: 0.84rem;
            line-height: 1.7;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.22);
            text-align: left;
            margin-bottom: 16px;
        }

        .demo-login-alert-info {
            border-color: rgba(229, 193, 88, 0.24);
            background: rgba(255, 255, 255, 0.045);
            color: #f9efcf;
        }

        .demo-login-grid {
            display: grid;
            gap: 14px;
        }

        .demo-login-card {
            position: relative;
            width: 100%;
            padding: 20px 18px 18px;
            border-radius: 22px;
            border: 1px solid rgba(229, 193, 88, 0.18);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.015)),
                linear-gradient(135deg, rgba(35, 13, 17, 0.98), rgba(21, 7, 10, 0.98));
            color: #f6ead0;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
            overflow: hidden;
        }

        .demo-login-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(229, 193, 88, 0.08), transparent 28%, transparent 72%, rgba(229, 193, 88, 0.08));
            opacity: 0;
            transition: opacity 0.18s ease;
            pointer-events: none;
        }

        .demo-login-card:hover {
            transform: translateY(-2px);
            border-color: rgba(229, 193, 88, 0.42);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.3);
        }

        .demo-login-card:hover::before {
            opacity: 1;
        }

        .demo-login-card-head {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 52px 1fr;
            gap: 14px;
            align-items: start;
        }

        .demo-login-card-icon {
            position: relative;
            z-index: 1;
            display: grid;
            place-items: center;
            width: 52px;
            height: 52px;
            border-radius: 16px;
            border: 1px solid rgba(229, 193, 88, 0.2);
            background: linear-gradient(145deg, rgba(229, 193, 88, 0.14), rgba(82, 28, 39, 0.2));
            color: #f6d98b;
            font-size: 1rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .demo-login-card-copy {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .demo-login-btn-label {
            font-size: 0.68rem;
            letter-spacing: 0.22em;
            color: rgba(253, 240, 178, 0.62);
        }

        .demo-login-card-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #fff7e4;
        }

        .demo-login-card-desc {
            margin: 0;
            font-size: 0.82rem;
            line-height: 1.6;
            color: rgba(218, 199, 199, 0.78);
        }

        .demo-login-field {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .demo-login-field span {
            color: #f7ecd3;
            font-size: 0.84rem;
        }

        .demo-login-field select {
            width: 100%;
            padding: 13px 14px;
            border-radius: 16px;
            border: 1px solid rgba(229, 193, 88, 0.16);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            font-size: 0.93rem;
        }

        .demo-login-field select:focus {
            outline: none;
            border-color: rgba(253, 240, 178, 0.72);
            box-shadow: 0 0 0 3px rgba(229, 193, 88, 0.12);
        }

        .demo-login-actions {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .demo-login-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 48px;
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid transparent;
            cursor: pointer;
            font-size: 0.84rem;
            font-weight: 700;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .demo-login-action:hover {
            transform: translateY(-1px);
        }

        .demo-login-action-primary {
            background: linear-gradient(135deg, #f4df9c, #c99722);
            color: #2a1208;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.22);
        }

        .demo-login-action-line {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(6, 199, 85, 0.34);
            color: #ecfff4;
        }

        .demo-login-inline-link,
        .demo-login-inline-note {
            position: relative;
            z-index: 1;
            margin: 0;
            font-size: 0.81rem;
            line-height: 1.7;
        }

        .demo-login-inline-link {
            color: #f6d98b;
            text-decoration: none;
        }

        .demo-login-inline-note {
            color: rgba(218, 199, 199, 0.74);
        }

        .demo-register-panel {
            margin-top: 18px;
            padding: 22px 20px;
            border-radius: 22px;
            border: 1px solid rgba(229, 193, 88, 0.16);
            background: rgba(255, 255, 255, 0.035);
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.2);
        }

        .demo-register-panel-head {
            margin-bottom: 14px;
        }

        .demo-register-panel-title {
            margin: 0 0 8px;
            font-size: 1.1rem;
            color: #fff4d6;
        }

        .demo-register-panel-desc {
            margin: 0;
            color: rgba(231, 217, 217, 0.82);
            line-height: 1.8;
            font-size: 0.84rem;
        }

        .demo-login-note {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 18px;
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid rgba(229, 193, 88, 0.14);
            background: rgba(255, 255, 255, 0.04);
            color: rgba(231, 217, 217, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .demo-register-links {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .demo-register-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 50px;
            padding: 12px 14px;
            border-radius: 18px;
            border: 1px solid rgba(229, 193, 88, 0.18);
            background: rgba(255, 255, 255, 0.035);
            color: #f7ecd3;
            text-decoration: none;
            font-size: 0.88rem;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .demo-register-link:hover {
            transform: translateY(-1px);
            border-color: rgba(229, 193, 88, 0.4);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.24);
        }

        .demo-login-note-badge {
            flex-shrink: 0;
            padding: 5px 10px;
            border-radius: 999px;
            border: 1px solid rgba(229, 193, 88, 0.25);
            background: rgba(229, 193, 88, 0.1);
            color: #f6d98b;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.18em;
        }

        .demo-login-note p {
            margin: 0;
            font-size: 0.8rem;
            line-height: 1.8;
        }

        body.page-demo-login .guide-speech-bubble {
            background: linear-gradient(180deg, #2a1116, #17070a);
            border: 1px solid rgba(229, 193, 88, 0.24);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.42);
        }

        body.page-demo-login .guide-speech-bubble::after {
            border-color: transparent transparent transparent #17070a;
        }

        body.page-demo-login .guide-speech-bubble p {
            color: #f9efcf;
            font-weight: 700;
        }

        body.page-demo-login .guide-close-x {
            background: linear-gradient(135deg, #e5c158, #b38a22);
            color: #190509;
        }

        @media (max-width: 640px) {
            .demo-login-page {
                padding-top: 28px;
            }

            .demo-login-hero {
                padding: 28px 20px 24px;
                border-radius: 24px;
            }

            .demo-login-card {
                padding: 18px 14px 16px;
                border-radius: 18px;
            }

            .demo-login-card-head {
                grid-template-columns: 46px 1fr;
                gap: 12px;
            }

            .demo-login-card-icon {
                width: 46px;
                height: 46px;
                border-radius: 14px;
            }

            .demo-login-actions,
            .demo-register-links {
                grid-template-columns: 1fr;
            }

            .demo-login-note {
                align-items: flex-start;
            }

            .demo-register-panel {
                padding: 20px 16px;
            }
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const guide = document.getElementById('character-guide');

                if (!guide || typeof window.updateCharacterMessage !== 'function') {
                    return;
                }

                window.forceCharacterGuideVisible = true;
                window.updateCharacterMessage("オコジョがご案内するよ。\n役割とアカウントを選んだら、通常ログインかLINEログインを押してね。");

                const releaseGuide = function () {
                    window.forceCharacterGuideVisible = false;
                    guide.classList.add('bubble-hidden');
                };

                document.querySelectorAll('.demo-login-card').forEach(function (card) {
                    card.addEventListener('pointerdown', releaseGuide, { once: true });
                    card.addEventListener('submit', function () {
                        window.forceCharacterGuideVisible = false;
                    });
                });
            });
        </script>
    @endpush
@endsection

