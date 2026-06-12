@extends('layouts.app-v2')

@section('title', 'スタッフを追加')
@section('body-class', 'page-shop-mypage-staff-create')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<style>
    .staff-form-shell { padding: 0 16px 32px; max-width: 560px; margin: 0 auto; }
    .staff-form-shell .mypage-page-title {
        font-family: var(--font-sans);
        font-size: 1.2rem; font-weight: 700; color: #e6dffc;
        margin: 16px 0 6px;
    }
    .staff-form-shell .staff-form-lead {
        color: #a0a0a0; font-size: 0.84rem; margin: 0 0 18px;
    }

    .staff-form-flash--error {
        background: rgba(248, 113, 113, 0.12);
        border: 1px solid rgba(248, 113, 113, 0.5);
        color: #fecaca;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 14px;
        font-size: 0.86rem;
    }

    .staff-form-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        border: 1px solid rgba(168, 85, 247, 0.3);
        border-radius: 16px;
        padding: 22px 20px;
        display: flex; flex-direction: column; gap: 16px;
    }
    .staff-form-field { display: flex; flex-direction: column; gap: 6px; }
    .staff-form-field label {
        font-size: 0.8rem; font-weight: 700; color: #c4b5fd;
        letter-spacing: 0.04em;
    }
    .staff-form-field label .req {
        color: #fca5a5; margin-left: 4px; font-size: 0.7rem;
    }
    .staff-form-field input,
    .staff-form-field select {
        background: rgba(168, 85, 247, 0.10);
        border: 1px solid rgba(168, 85, 247, 0.4);
        color: #f5f5f5;
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 0.92rem;
        box-shadow: inset 0 2px 4px rgba(0,0,0,.4);
        appearance: none;
        -webkit-appearance: none;
    }
    .staff-form-field input:focus,
    .staff-form-field select:focus {
        outline: none;
        border-color: #a78bfa;
        box-shadow: inset 0 2px 4px rgba(0,0,0,.4), 0 0 0 3px rgba(168, 85, 247, .2);
    }
    .staff-form-field select {
        background-image:
            linear-gradient(45deg, transparent 50%, #c4b5fd 50%),
            linear-gradient(135deg, #c4b5fd 50%, transparent 50%);
        background-position:
            calc(100% - 18px) 50%,
            calc(100% - 13px) 50%;
        background-size: 5px 5px;
        background-repeat: no-repeat;
        padding-right: 32px;
    }
    .staff-form-field__hint {
        font-size: 0.74rem; color: #6b6b6b;
    }
    .staff-form-field__error {
        font-size: 0.76rem; color: #fca5a5; margin-top: 2px;
    }

    .staff-form-actions {
        display: flex; gap: 10px; margin-top: 8px;
    }
    .staff-form-submit {
        flex: 1; padding: 13px 16px;
        background: linear-gradient(135deg, #a78bfa, #7c3aed);
        color: #fff; font-weight: 800; font-size: 0.95rem;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 999px;
        cursor: pointer;
        box-shadow:
            inset 0 2px 4px rgba(255,255,255,.3),
            inset 0 -3px 4px rgba(0,0,0,.35),
            0 8px 20px rgba(124,58,237,.45);
        transition: transform .15s ease;
    }
    .staff-form-submit:hover { transform: translateY(-1px); }
    .staff-form-submit:active { transform: translateY(1px); }
    .staff-form-cancel {
        flex: 0 0 auto; padding: 13px 22px;
        background: transparent;
        color: #a0a0a0;
        border: 1px solid rgba(160, 160, 160, 0.3);
        border-radius: 999px;
        text-decoration: none;
        font-size: 0.9rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .staff-form-cancel:hover { color: #f5f5f5; border-color: rgba(255,255,255,.4); }
</style>
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn">
    <section class="mypage-area">
        <div class="staff-form-shell">
            <h1 class="mypage-page-title">スタッフを追加</h1>
            <p class="staff-form-lead">新しい店舗ログインアカウントを発行します。</p>

            @if ($errors->any())
                <div class="staff-form-flash--error">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('shop.mypage.staff.store') }}" class="staff-form-card" autocomplete="off">
                @csrf

                <div class="staff-form-field">
                    <label for="staff-name">表示名<span class="req">必須</span></label>
                    <input
                        type="text" id="staff-name" name="name"
                        value="{{ old('name') }}"
                        maxlength="255" required
                        placeholder="例：山田 花子"
                    >
                    <p class="staff-form-field__hint">店舗内・運営とのやり取りで使う名前です。</p>
                </div>

                <div class="staff-form-field">
                    <label for="staff-email">メールアドレス<span class="req">必須</span></label>
                    <input
                        type="email" id="staff-email" name="email"
                        value="{{ old('email') }}"
                        maxlength="255" required
                        autocomplete="off"
                        placeholder="staff@example.com"
                    >
                    <p class="staff-form-field__hint">このメールアドレスでログインします。</p>
                </div>

                <div class="staff-form-field">
                    <label for="staff-password">パスワード<span class="req">必須</span></label>
                    <input
                        type="password" id="staff-password" name="password"
                        minlength="8" required
                        autocomplete="new-password"
                        placeholder="8文字以上"
                    >
                </div>

                <div class="staff-form-field">
                    <label for="staff-password-confirm">パスワード（確認）<span class="req">必須</span></label>
                    <input
                        type="password" id="staff-password-confirm" name="password_confirmation"
                        minlength="8" required
                        autocomplete="new-password"
                    >
                </div>

                <div class="staff-form-field">
                    <label for="staff-role">権限<span class="req">必須</span></label>
                    <select id="staff-role" name="role" required>
                        <option value="{{ \App\Models\ShopManager::ROLE_STAFF }}" {{ old('role', \App\Models\ShopManager::ROLE_STAFF) == \App\Models\ShopManager::ROLE_STAFF ? 'selected' : '' }}>
                            スタッフ（日常業務のみ）
                        </option>
                        <option value="{{ \App\Models\ShopManager::ROLE_OWNER }}" {{ old('role') == \App\Models\ShopManager::ROLE_OWNER ? 'selected' : '' }}>
                            オーナー（全権限）
                        </option>
                    </select>
                    <p class="staff-form-field__hint">スタッフ管理・店舗情報変更を任せる人はオーナーを選択してください。</p>
                </div>

                <div class="staff-form-actions">
                    <a href="{{ route('shop.mypage.staff.index') }}" class="staff-form-cancel">キャンセル</a>
                    <button type="submit" class="staff-form-submit">
                        <i class="fas fa-user-plus"></i> 追加する
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
