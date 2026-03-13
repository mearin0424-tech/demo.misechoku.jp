@extends('layouts.app')

@section('title', 'ログイン（デモ）')
@section('body-class', 'page-demo-login')
@section('guide_message', "ようこそ、ミセチョクへ。\n体験したい立場を選ぶだけで、すぐにデモを始められるよ。")

@section('content')
    <div class="demo-login-page">
        <div class="demo-login-hero">
            <p class="demo-login-eyebrow">LUXURY DEMO ENTRANCE</p>
            <div class="demo-login-logo-wrap">
                <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="demo-login-logo">
            </div>

            <h1 class="demo-login-title">体験したい視点から、ミセチョクへ。</h1>
            <p class="demo-login-desc">
                管理者・店舗・キャストの各導線を、
                落ち着いた世界観のままスムーズに試せるデモログインです。
            </p>
        </div>

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
                <span class="demo-login-btn-icon"><i class="fas fa-crown"></i></span>
                <span class="demo-login-btn-copy">
                    <span class="demo-login-btn-label">BACK OFFICE</span>
                    <span class="demo-login-btn-main">管理者としてログイン</span>
                    <span class="demo-login-btn-sub">運営用ダッシュボードを確認</span>
                </span>
                <span class="demo-login-btn-arrow"><i class="fas fa-arrow-right"></i></span>
            </button>

            <button type="submit" name="role" value="shop" class="demo-login-btn demo-login-btn-shop">
                <span class="demo-login-btn-icon"><i class="fas fa-store"></i></span>
                <span class="demo-login-btn-copy">
                    <span class="demo-login-btn-label">SHOP ACCOUNT</span>
                    <span class="demo-login-btn-main">店舗としてログイン</span>
                    <span class="demo-login-btn-sub">ホーム・求人・マイページを確認</span>
                </span>
                <span class="demo-login-btn-arrow"><i class="fas fa-arrow-right"></i></span>
            </button>

            <button type="submit" name="role" value="cast" class="demo-login-btn demo-login-btn-cast">
                <span class="demo-login-btn-icon"><i class="fas fa-gem"></i></span>
                <span class="demo-login-btn-copy">
                    <span class="demo-login-btn-label">CAST ACCOUNT</span>
                    <span class="demo-login-btn-main">キャストとしてログイン</span>
                    <span class="demo-login-btn-sub">ホーム・検索・マイページを確認</span>
                </span>
                <span class="demo-login-btn-arrow"><i class="fas fa-arrow-right"></i></span>
            </button>
        </form>

        <div class="demo-register-links">
            <a href="{{ route('cast.register') }}" class="demo-register-link">
                <i class="fas fa-user-plus"></i>
                <span>キャスト新規登録</span>
            </a>
            <a href="{{ route('shop.register') }}" class="demo-register-link">
                <i class="fas fa-store"></i>
                <span>店舗新規登録</span>
            </a>
        </div>

        <div class="demo-login-note">
            <span class="demo-login-note-badge">DEMO</span>
            <p>認証はデモ用です。アカウント情報の入力なしで各画面の体験を始められます。</p>
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
        }

        .demo-login-btn {
            position: relative;
            width: 100%;
            padding: 18px 18px 18px 16px;
            border-radius: 22px;
            border: 1px solid rgba(229, 193, 88, 0.18);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.015)),
                linear-gradient(135deg, rgba(35, 13, 17, 0.98), rgba(21, 7, 10, 0.98));
            color: #f6ead0;
            cursor: pointer;
            text-align: left;
            display: grid;
            grid-template-columns: 52px 1fr auto;
            gap: 14px;
            align-items: center;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
            overflow: hidden;
        }

        .demo-login-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(229, 193, 88, 0.08), transparent 28%, transparent 72%, rgba(229, 193, 88, 0.08));
            opacity: 0;
            transition: opacity 0.18s ease;
        }

        .demo-login-btn:hover {
            transform: translateY(-2px);
            border-color: rgba(229, 193, 88, 0.42);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.3);
        }

        .demo-login-btn:hover::before {
            opacity: 1;
        }

        .demo-login-btn:focus-visible {
            outline: none;
            border-color: rgba(253, 240, 178, 0.86);
            box-shadow: 0 0 0 3px rgba(229, 193, 88, 0.18);
        }

        .demo-login-btn-icon {
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

        .demo-login-btn-copy {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }

        .demo-login-btn-label {
            font-size: 0.68rem;
            letter-spacing: 0.22em;
            color: rgba(253, 240, 178, 0.62);
        }

        .demo-login-btn-main {
            font-size: 1rem;
            font-weight: 700;
            color: #fff7e4;
        }

        .demo-login-btn-sub {
            font-size: 0.82rem;
            line-height: 1.6;
            color: rgba(218, 199, 199, 0.78);
        }

        .demo-login-btn-arrow {
            position: relative;
            z-index: 1;
            color: rgba(229, 193, 88, 0.9);
            font-size: 0.9rem;
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
            margin-top: 16px;
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

            .demo-login-btn {
                grid-template-columns: 46px 1fr auto;
                gap: 12px;
                padding: 16px 14px;
                border-radius: 18px;
            }

            .demo-login-btn-icon {
                width: 46px;
                height: 46px;
                border-radius: 14px;
            }

            .demo-login-note {
                align-items: flex-start;
            }

            .demo-register-links {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const guide = document.getElementById('character-guide');
                const form = document.querySelector('.demo-login-form');

                if (!guide || typeof window.updateCharacterMessage !== 'function') {
                    return;
                }

                window.forceCharacterGuideVisible = true;
                window.updateCharacterMessage("オコジョがご案内するよ。\n入りたい立場を選ぶだけで、すぐに試せるよ。");

                const releaseGuide = function () {
                    window.forceCharacterGuideVisible = false;
                    guide.classList.add('bubble-hidden');
                };

                if (form) {
                    form.addEventListener('pointerdown', releaseGuide, { once: true });
                    form.addEventListener('submit', function () {
                        window.forceCharacterGuideVisible = false;
                    });
                }
            });
        </script>
    @endpush
@endsection

