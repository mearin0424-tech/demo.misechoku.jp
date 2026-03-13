@extends('layouts.app')

@section('title', $title)
@section('body-class', $bodyClass)
@section('guide_message', $guideMessage)

@section('content')
    <div class="role-login-page">
        <section class="role-login-hero">
            <p class="role-login-eyebrow">{{ $eyebrow }}</p>
            <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="role-login-logo">
            <h1 class="role-login-title">{{ $heroTitle }}</h1>
            <p class="role-login-desc">{{ $heroDescription }}</p>
        </section>

        <form method="POST" action="{{ $formAction }}" class="role-login-form">
            @csrf

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

            <section class="role-login-card">
                <div class="role-login-card-head">
                    <h2>ログイン情報</h2>
                    <p>デモ用のため、入力内容は保存されません。</p>
                </div>

                <label class="role-login-field">
                    <span>メールアドレス</span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ $role === 'cast' ? 'cast@example.com' : 'shop@example.com' }}">
                </label>

                <label class="role-login-field">
                    <span>パスワード</span>
                    <input type="password" name="password" placeholder="8文字以上で入力">
                </label>

                <button type="submit" class="role-login-submit">
                    {{ $role === 'cast' ? 'キャストとしてログイン' : '店舗としてログイン' }}
                </button>
            </section>

            <div class="role-login-links">
                <a href="{{ $registerUrl }}" class="role-login-secondary">{{ $registerLabel }}</a>
                <a href="{{ $alternateUrl }}" class="role-login-secondary">{{ $alternateLabel }}</a>
                <a href="{{ route('login.demo') }}" class="role-login-back">デモログイン一覧へ戻る</a>
            </div>
        </form>
    </div>

    <style>
        body.page-auth-login #bottom-nav,
        body.page-auth-login #side-menu,
        body.page-auth-login .header-right {
            display: none !important;
        }

        body.page-auth-login main {
            padding-bottom: 40px;
        }

        body.page-auth-login.page-auth-login-cast {
            background:
                radial-gradient(circle at top left, rgba(229, 193, 88, 0.18), transparent 32%),
                linear-gradient(180deg, #120405 0%, #190509 45%, #110406 100%);
        }

        body.page-auth-login.page-auth-login-shop {
            background:
                radial-gradient(circle at top right, rgba(229, 193, 88, 0.18), transparent 32%),
                linear-gradient(180deg, #13060b 0%, #1d0c12 45%, #120406 100%);
        }

        body.page-auth-login #bg-layer {
            background:
                radial-gradient(circle at 18% 12%, rgba(229, 193, 88, 0.08), transparent 22%),
                radial-gradient(circle at 85% 18%, rgba(255, 255, 255, 0.05), transparent 18%),
                radial-gradient(circle at 50% 100%, rgba(122, 24, 44, 0.22), transparent 30%);
        }

        .role-login-page {
            max-width: 560px;
            margin: 0 auto;
            padding: 36px 0 0;
        }

        .role-login-hero,
        .role-login-card {
            border: 1px solid rgba(229, 193, 88, 0.2);
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02)),
                linear-gradient(135deg, rgba(49, 17, 23, 0.96), rgba(20, 7, 10, 0.98));
            box-shadow:
                0 24px 60px rgba(0, 0, 0, 0.42),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .role-login-hero {
            padding: 32px 28px 26px;
            text-align: center;
            margin-bottom: 18px;
        }

        .role-login-eyebrow {
            margin: 0 0 14px;
            font-size: 0.72rem;
            letter-spacing: 0.28em;
            color: rgba(253, 240, 178, 0.78);
        }

        .role-login-logo {
            width: 210px;
            max-width: 72%;
            margin-bottom: 18px;
            filter: drop-shadow(0 8px 24px rgba(0, 0, 0, 0.35));
        }

        .role-login-title {
            margin: 0 0 10px;
            font-size: clamp(1.7rem, 4vw, 2.1rem);
            line-height: 1.35;
            color: #f9efcf;
        }

        .role-login-desc {
            margin: 0 auto;
            max-width: 30rem;
            font-size: 0.94rem;
            line-height: 1.85;
            color: rgba(236, 221, 221, 0.88);
        }

        .role-login-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .role-login-alert {
            padding: 12px 14px;
            border-radius: 16px;
            font-size: 0.84rem;
            line-height: 1.7;
        }

        .role-login-alert-info {
            border: 1px solid rgba(229, 193, 88, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: #f8ead0;
        }

        .role-login-alert-error {
            border: 1px solid rgba(255, 177, 177, 0.32);
            background: rgba(122, 24, 44, 0.42);
            color: #fff1f2;
        }

        .role-login-card {
            padding: 24px 22px;
        }

        .role-login-card-head {
            margin-bottom: 18px;
        }

        .role-login-card-head h2 {
            margin: 0 0 6px;
            color: #fff4d6;
            font-size: 1.05rem;
        }

        .role-login-card-head p {
            margin: 0;
            color: rgba(218, 199, 199, 0.76);
            font-size: 0.82rem;
            line-height: 1.7;
        }

        .role-login-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 14px;
            color: #f8f1e1;
            font-size: 0.84rem;
        }

        .role-login-field input {
            width: 100%;
            padding: 13px 14px;
            border-radius: 16px;
            border: 1px solid rgba(229, 193, 88, 0.16);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            font-size: 0.94rem;
        }

        .role-login-field input::placeholder {
            color: rgba(214, 198, 198, 0.48);
        }

        .role-login-field input:focus {
            outline: none;
            border-color: rgba(253, 240, 178, 0.72);
            box-shadow: 0 0 0 3px rgba(229, 193, 88, 0.12);
        }

        .role-login-submit,
        .role-login-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 52px;
            padding: 14px 18px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
        }

        .role-login-submit {
            border: none;
            background: linear-gradient(135deg, #f4df9c, #c99722);
            color: #2a1208;
            cursor: pointer;
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.28);
        }

        .role-login-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
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
                padding-top: 22px;
            }

            .role-login-hero {
                padding: 28px 20px 24px;
                border-radius: 24px;
            }

            .role-login-card {
                padding: 20px 18px;
                border-radius: 24px;
            }
        }
    </style>
@endsection
