@extends('layouts.app-v2')

@section('title', $title)
@section('body-class', $bodyClass)

@section('content')
<div class="role-login-shell">
    <div class="role-login-bg">
        <div class="role-login-bg-photo"></div>
        <div class="role-login-bg-overlay"></div>
    </div>

    <div class="role-login-page">
        <div class="role-login-brand">
            <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="role-login-logo">
        </div>

        @if ($errors->any())
            <div class="role-login-alert role-login-alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="role-login-card">
            <div class="role-login-card-body">
                <div class="role-login-copy">
                    <h2 class="role-login-title">新しいパスワードを設定</h2>
                    <p class="role-login-lead" style="margin: 8px 0 20px; font-size: 0.86rem; color: rgba(255,255,255,0.72); line-height:1.65;">
                        新しいパスワードを入力してください（8 文字以上）。
                    </p>
                </div>

                <form method="POST" action="{{ route('password.reset.post') }}" class="role-login-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <label class="role-login-field">
                        <span>メールアドレス</span>
                        <div class="role-login-input-wrap">
                            <i class="fas fa-envelope role-login-input-icon" aria-hidden="true"></i>
                            <input type="email" name="email" value="{{ old('email', $email) }}"
                                   autocomplete="email" required readonly>
                        </div>
                    </label>

                    <label class="role-login-field">
                        <span>新しいパスワード</span>
                        <div class="role-login-input-wrap">
                            <i class="fas fa-lock role-login-input-icon" aria-hidden="true"></i>
                            <input type="password" name="password" placeholder="8文字以上"
                                   autocomplete="new-password" required minlength="8">
                        </div>
                    </label>

                    <label class="role-login-field">
                        <span>新しいパスワード（確認）</span>
                        <div class="role-login-input-wrap">
                            <i class="fas fa-lock role-login-input-icon" aria-hidden="true"></i>
                            <input type="password" name="password_confirmation" placeholder="もう一度入力"
                                   autocomplete="new-password" required minlength="8">
                        </div>
                    </label>

                    <button type="submit" class="role-login-submit">
                        <i class="fas fa-check" aria-hidden="true"></i>
                        <span>パスワードを更新</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="role-login-links">
            <a href="{{ route('cast.login') }}" class="role-login-back">
                <i class="fas fa-arrow-left"></i> ログイン画面に戻る
            </a>
        </div>
    </div>
</div>
@endsection
