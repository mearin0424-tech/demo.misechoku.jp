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

        @if (session('message'))
            <div class="role-login-alert role-login-alert-info">{{ session('message') }}</div>
        @endif
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
                    <h2 class="role-login-title">パスワードの再設定</h2>
                    <p class="role-login-lead" style="margin: 8px 0 20px; font-size: 0.86rem; color: rgba(255,255,255,0.72); line-height:1.65;">
                        ご登録のメールアドレスを入力してください。<br>
                        再設定用のリンクをお送りします（有効期限 60 分）。
                    </p>
                </div>

                <form method="POST" action="{{ route('password.forgot.post') }}" class="role-login-form">
                    @csrf
                    <label class="role-login-field">
                        <span>メールアドレス</span>
                        <div class="role-login-input-wrap">
                            <i class="fas fa-envelope role-login-input-icon" aria-hidden="true"></i>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   placeholder="example@misechoku.jp" autocomplete="email" required>
                        </div>
                    </label>

                    <button type="submit" class="role-login-submit">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        <span>再設定リンクを送る</span>
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
