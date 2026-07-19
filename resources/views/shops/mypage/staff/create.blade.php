@extends('layouts.app-v2')

@section('title', 'スタッフを追加')
@section('body-class', 'page-shop-mypage-staff-create')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<style>
    .staff-form-shell { padding: 12px 16px 32px; max-width: 560px; margin: 0 auto; }

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
    .staff-form-field input.is-error,
    .staff-form-field select.is-error,
    .staff-form-field input[aria-invalid="true"] {
        border-color: rgba(248, 113, 113, 0.70);
        background: rgba(220, 38, 38, 0.06);
    }
    .staff-form-field input.is-error:focus,
    .staff-form-field input[aria-invalid="true"]:focus {
        box-shadow: inset 0 2px 4px rgba(0,0,0,.4), 0 0 0 3px rgba(220, 38, 38, 0.18);
    }
    .staff-form-field input.is-valid {
        border-color: rgba(110, 231, 183, 0.55);
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

    /* パスワード表示切替 */
    .staff-pw-wrap { position: relative; }
    .staff-pw-wrap input { width: 100%; box-sizing: border-box; padding-right: 46px; }
    .staff-pw-toggle {
        position: absolute; top: 50%; right: 6px; transform: translateY(-50%);
        width: 36px; height: 36px;
        border: 0; background: transparent;
        color: #8a8a8a; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px;
        min-width: 36px; min-height: 36px;
    }
    .staff-pw-toggle:hover { color: var(--accent-text, #f0a6c4); }

    /* 権限は「1つ選択 = プルダウン」の入力コンポーネント規約に従い select で実装 */
    .staff-form-field__error {
        font-size: 0.76rem; color: #fca5a5; margin-top: 2px;
        display: flex; align-items: flex-start; gap: 4px;
    }
    .staff-form-field__error::before { content: "⚠"; }
    .staff-form-field__error[hidden] { display: none; }

    .staff-form-actions {
        display: flex; gap: 10px; margin-top: 8px;
    }
    .staff-form-submit {
        flex: 1; padding: 13px 16px;
        background: var(--accent, #d670a2);
        color: var(--on-accent, #1a0814); font-weight: 800; font-size: 0.95rem;
        border: 0;
        border-radius: 999px;
        cursor: pointer;
        box-shadow: 0 6px 14px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.20), inset 0 -1px 0 rgba(0,0,0,.18);
        transition: filter .15s ease, transform .12s ease;
    }
    .staff-form-submit:hover { filter: brightness(1.06); }
    .staff-form-submit:active { transform: scale(.97); box-shadow: 0 2px 5px rgba(0,0,0,.45), inset 0 2px 4px rgba(0,0,0,.2); }
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
            {{-- タイトルはヘッダー中央に表示（統一方針）。ページ内はリード文のみ --}}
            <p class="page-lead">新しい店舗ログインアカウントを発行します。メールアドレスとパスワードを、追加するスタッフ本人に共有してください。</p>

            @if ($errors->has('shop') || $errors->has('manager_limit'))
                <div class="staff-form-flash--error">
                    @foreach ($errors->get('shop') as $error)
                        {{ $error }}<br>
                    @endforeach
                    @foreach ($errors->get('manager_limit') as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('shop.mypage.staff.store') }}" class="staff-form-card" autocomplete="off" novalidate>
                @csrf

                <div class="staff-form-field">
                    <label for="staff-name">表示名<span class="req">必須</span></label>
                    <input
                        type="text" id="staff-name" name="name"
                        value="{{ old('name') }}"
                        maxlength="255" required
                        placeholder="例：山田 花子"
                        aria-describedby="staff-name-error"
                        @error('name') aria-invalid="true" class="is-error" @enderror
                    >
                    <p class="staff-form-field__hint">店舗内・運営とのやり取りで使う名前です。</p>
                    <p class="staff-form-field__error" id="staff-name-error" @error('name') @else hidden @enderror>@error('name'){{ $message }}@enderror</p>
                </div>

                <div class="staff-form-field">
                    <label for="staff-email">メールアドレス<span class="req">必須</span></label>
                    <input
                        type="email" id="staff-email" name="email"
                        value="{{ old('email') }}"
                        maxlength="255" required
                        autocomplete="off"
                        placeholder="staff@example.com"
                        aria-describedby="staff-email-error"
                        @error('email') aria-invalid="true" class="is-error" @enderror
                    >
                    <p class="staff-form-field__hint">このメールアドレスでログインします。</p>
                    <p class="staff-form-field__error" id="staff-email-error" @error('email') @else hidden @enderror>@error('email'){{ $message }}@enderror</p>
                </div>

                <div class="staff-form-field">
                    <label for="staff-password">パスワード<span class="req">必須</span></label>
                    <div class="staff-pw-wrap">
                        <input
                            type="password" id="staff-password" name="password"
                            minlength="8" required
                            autocomplete="new-password"
                            placeholder="8文字以上"
                            aria-describedby="staff-password-error"
                            @error('password') aria-invalid="true" class="is-error" @enderror
                        >
                        <button type="button" class="staff-pw-toggle" data-pw-toggle="staff-password" aria-label="パスワードを表示">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="staff-form-field__error" id="staff-password-error" @error('password') @else hidden @enderror>@error('password'){{ $message }}@enderror</p>
                </div>

                <div class="staff-form-field">
                    <label for="staff-password-confirm">パスワード（確認）<span class="req">必須</span></label>
                    <div class="staff-pw-wrap">
                        <input
                            type="password" id="staff-password-confirm" name="password_confirmation"
                            minlength="8" required
                            autocomplete="new-password"
                            aria-describedby="staff-password-confirm-error"
                        >
                        <button type="button" class="staff-pw-toggle" data-pw-toggle="staff-password-confirm" aria-label="パスワードを表示">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="staff-form-field__error" id="staff-password-confirm-error" hidden></p>
                </div>

                <div class="staff-form-field">
                    <label for="staff-role">権限<span class="req">必須</span></label>
                    {{-- 1つ選択はプルダウンに統一（入力コンポーネント規約） --}}
                    <select id="staff-role" name="role" required>
                        <option value="{{ \App\Models\ShopManager::ROLE_STAFF }}" {{ old('role', \App\Models\ShopManager::ROLE_STAFF) == \App\Models\ShopManager::ROLE_STAFF ? 'selected' : '' }}>
                            スタッフ — 応募者対応・メッセージなど日常業務のみ
                        </option>
                        <option value="{{ \App\Models\ShopManager::ROLE_OWNER }}" {{ old('role') == \App\Models\ShopManager::ROLE_OWNER ? 'selected' : '' }}>
                            オーナー — スタッフ管理・店舗情報変更を含む全権限
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form.staff-form-card');
    if (!form) return;
    var nameInput = form.querySelector('#staff-name');
    var emailInput = form.querySelector('#staff-email');
    var pwInput = form.querySelector('#staff-password');
    var pwConfirmInput = form.querySelector('#staff-password-confirm');

    var setError = function (input, msg) {
        if (!input) return;
        var errId = input.getAttribute('aria-describedby');
        var errEl = errId ? document.getElementById(errId) : null;
        if (msg) {
            input.classList.add('is-error');
            input.classList.remove('is-valid');
            input.setAttribute('aria-invalid', 'true');
            if (errEl) { errEl.textContent = msg; errEl.hidden = false; }
        } else {
            input.classList.remove('is-error');
            input.classList.add('is-valid');
            input.removeAttribute('aria-invalid');
            if (errEl) { errEl.textContent = ''; errEl.hidden = true; }
        }
    };

    var validateName = function () {
        var v = (nameInput.value || '').trim();
        if (!v) { setError(nameInput, '表示名を入力してください。'); return false; }
        setError(nameInput, '');
        return true;
    };
    var validateEmail = function () {
        var v = (emailInput.value || '').trim();
        if (!v) { setError(emailInput, 'メールアドレスを入力してください。'); return false; }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
            setError(emailInput, 'メールアドレスの形式が正しくありません。'); return false;
        }
        setError(emailInput, '');
        return true;
    };
    var validatePw = function () {
        var v = pwInput.value || '';
        if (!v) { setError(pwInput, 'パスワードを入力してください。'); return false; }
        if (v.length < 8) { setError(pwInput, '8文字以上で入力してください。'); return false; }
        setError(pwInput, '');
        if (pwConfirmInput.value) validatePwConfirm();
        return true;
    };
    var validatePwConfirm = function () {
        var v = pwConfirmInput.value || '';
        if (!v) { setError(pwConfirmInput, '確認用パスワードを入力してください。'); return false; }
        if (v !== pwInput.value) { setError(pwConfirmInput, 'パスワードが一致しません。'); return false; }
        setError(pwConfirmInput, '');
        return true;
    };

    // パスワード表示切替
    document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.getAttribute('data-pw-toggle'));
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'パスワードを隠す' : 'パスワードを表示');
            var icon = btn.querySelector('i');
            if (icon) icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    });

    if (nameInput) nameInput.addEventListener('blur', validateName);
    if (emailInput) emailInput.addEventListener('blur', validateEmail);
    if (pwInput) pwInput.addEventListener('input', validatePw);
    if (pwConfirmInput) pwConfirmInput.addEventListener('input', validatePwConfirm);

    form.addEventListener('submit', function (e) {
        var ok = true;
        if (!validateName()) ok = false;
        if (!validateEmail()) ok = false;
        if (!validatePw()) ok = false;
        if (!validatePwConfirm()) ok = false;
        if (!ok) {
            e.preventDefault();
            var firstErr = form.querySelector('.is-error');
            if (firstErr) firstErr.focus();
        }
    });
});
</script>
@endpush
