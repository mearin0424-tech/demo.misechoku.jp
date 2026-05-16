@extends('layouts.app')

@section('title', 'アカウント設定')

@section('content')
<div class="setting-page">
    <div class="setting-header">
        <h1 class="setting-title">アカウント設定</h1>
        <p class="setting-lead">メールアドレス変更・パスワード変更・LINE連携・退会手続きをここで行います。</p>
    </div>

    @if(session('message'))
        <div class="setting-flash setting-flash--success" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <span>{{ session('message') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="setting-flash setting-flash--error" role="alert">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- メールアドレス変更 --}}
    <section class="setting-section">
        <h2 class="setting-section-title"><i class="fas fa-envelope"></i> メールアドレス変更</h2>
        <form method="POST" action="{{ route('setting.account.email.update') }}" autocomplete="off">
            @csrf
            <div class="setting-form-group">
                <label for="current-email">現在のメールアドレス</label>
                <input id="current-email" type="email" value="{{ $currentEmail ?? '' }}" disabled>
            </div>
            <div class="setting-form-group">
                <label for="new-email">新しいメールアドレス</label>
                <input id="new-email"
                       type="email"
                       name="new_email"
                       maxlength="255"
                       required
                       placeholder="new@example.com"
                       value="{{ old('new_email') }}">
                @error('new_email')<p class="setting-error">{{ $message }}</p>@enderror
            </div>
            <div class="setting-form-group">
                <label for="email-current-password">現在のパスワード（確認）</label>
                <input id="email-current-password"
                       type="password"
                       name="current_password"
                       required
                       placeholder="セキュリティ確認のため入力"
                       autocomplete="current-password">
                @error('current_password')<p class="setting-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="setting-submit">
                <i class="fas fa-pen"></i> メールアドレスを変更する
            </button>
        </form>
    </section>

    {{-- パスワード変更 --}}
    <section class="setting-section">
        <h2 class="setting-section-title"><i class="fas fa-key"></i> パスワード変更</h2>
        <form method="POST" action="{{ route('setting.account.password.update') }}" autocomplete="off">
            @csrf
            <div class="setting-form-group">
                <label for="current-password">現在のパスワード</label>
                <input id="current-password"
                       type="password"
                       name="current_password"
                       required
                       autocomplete="current-password"
                       placeholder="現在のパスワード">
            </div>
            <div class="setting-form-group">
                <label for="new-password">新しいパスワード</label>
                <input id="new-password"
                       type="password"
                       name="new_password"
                       required
                       minlength="8"
                       autocomplete="new-password"
                       placeholder="半角英数字8文字以上">
                @error('new_password')<p class="setting-error">{{ $message }}</p>@enderror
            </div>
            <div class="setting-form-group">
                <label for="new-password-confirm">新しいパスワード（確認用）</label>
                <input id="new-password-confirm"
                       type="password"
                       name="new_password_confirmation"
                       required
                       minlength="8"
                       autocomplete="new-password"
                       placeholder="もう一度入力してください">
            </div>
            <button type="submit" class="setting-submit">
                <i class="fas fa-shield"></i> パスワードを変更する
            </button>
        </form>
    </section>

    {{-- LINE連携 --}}
    <section class="setting-section">
        <h2 class="setting-section-title"><i class="fab fa-line"></i> LINE連携</h2>
        <div class="setting-row">
            <div class="setting-row-main">
                <div class="setting-row-label">LINEでログイン・通知</div>
                <div class="setting-row-desc">
                    LINEと連携すると、LINEでログインでき、リマインダー通知をLINEにも送れます。
                    @if ($lineLinked ?? false)
                        <strong class="setting-linked"><i class="fas fa-check-circle"></i> 連携済み</strong>
                    @endif
                </div>
            </div>
            @if ($lineLinked ?? false)
                <form method="POST" action="{{ route('setting.account.line.unlink') }}"
                      onsubmit="return confirm('LINEとの連携を解除します。よろしいですか？');"
                      style="margin:0;">
                    @csrf
                    <button type="submit" class="setting-btn setting-btn-unlink">連携解除</button>
                </form>
            @else
                <a href="{{ $lineLinkUrl ?? '#' }}" class="setting-btn setting-btn-line">LINEと連携</a>
            @endif
        </div>
    </section>

    {{-- 退会 --}}
    <section class="setting-section setting-section--danger">
        <h2 class="setting-section-title"><i class="fas fa-user-slash"></i> 退会手続き</h2>
        <div class="withdraw-warning" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <p>退会すると、これまでのやりとり履歴・プロフィール情報・キープ／いいね情報は閲覧できなくなります。</p>
                <p class="withdraw-warning-small">なお、利用規約・個人情報保護方針に基づき、一定期間データを保管する場合があります。</p>
            </div>
        </div>
        <form method="POST" action="{{ route('setting.account.withdraw') }}"
              onsubmit="return confirm('本当に退会しますか？この操作は取り消せません。');"
              autocomplete="off">
            @csrf
            <div class="setting-form-group">
                <label for="reason">退会理由（任意）</label>
                <textarea id="reason" name="reason" rows="4" maxlength="1000"
                          placeholder="サービス改善のため、差し支えなければお聞かせください。">{{ old('reason') }}</textarea>
            </div>
            <div class="setting-form-group">
                <label for="withdraw-current-password">現在のパスワード（確認）</label>
                <input id="withdraw-current-password"
                       type="password"
                       name="current_password"
                       required
                       autocomplete="current-password"
                       placeholder="セキュリティ確認のため入力">
            </div>
            <label class="withdraw-check">
                <input type="checkbox" name="agreement" value="1" required>
                <span>退会後はアカウントを元に戻せないことを理解しました。</span>
            </label>
            <button type="submit" class="setting-submit setting-submit-danger">
                <i class="fas fa-right-from-bracket"></i> 退会を申し込む
            </button>
        </form>
    </section>
</div>
@endsection

@push('styles')
<style>
.setting-page { padding: 24px var(--content-padding-x, 16px) 32px; color: #f9f5f5; }
.setting-header { margin-bottom: 18px; }
.setting-title { font-family: var(--font-sans); font-size: 1.4rem; margin-bottom: 8px; color: var(--mypage-gold, #dcb568); }
.setting-lead { font-size: 0.86rem; line-height: 1.6; color: #d1c1c1; }

.setting-flash {
    margin: 0 0 14px;
    padding: 12px 14px;
    border-radius: 12px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 0.86rem;
    line-height: 1.55;
}
.setting-flash ul { margin: 0; padding-left: 1.1em; }
.setting-flash--success { background: rgba(16, 185, 129, 0.10); border: 1px solid rgba(16, 185, 129, 0.45); color: #6ee7b7; }
.setting-flash--success i { color: #6ee7b7; }
.setting-flash--error { background: rgba(220, 38, 38, 0.10); border: 1px solid rgba(220, 38, 38, 0.45); color: #fca5a5; }
.setting-flash--error i { color: #fca5a5; margin-top: 2px; }

.setting-section {
    margin-bottom: 16px;
    background: rgba(20, 7, 15, 0.85);
    border-radius: 18px;
    padding: 16px 16px 18px;
    border: 1px solid rgba(220, 181, 104, 0.32);
}
.setting-section--danger { border-color: rgba(220, 38, 38, 0.4); }
.setting-section-title {
    font-size: 0.95rem;
    font-weight: 800;
    margin: 0 0 14px;
    color: #f8e9c8;
    display: flex;
    align-items: center;
    gap: 8px;
}
.setting-section-title i { color: var(--mypage-gold, #dcb568); font-size: 0.85rem; }
.setting-section--danger .setting-section-title i { color: #fca5a5; }

.setting-form-group { margin-bottom: 12px; }
.setting-form-group label { display: block; font-size: 0.78rem; margin-bottom: 6px; color: #f5e0c4; font-weight: 700; }
.setting-form-group input,
.setting-form-group textarea {
    width: 100%;
    box-sizing: border-box;
    border-radius: 10px;
    border: 1px solid rgba(220, 181, 104, 0.3);
    background: rgba(8, 4, 6, 0.85);
    padding: 11px 12px;
    font-size: 0.92rem;
    color: #fafafa;
    font-family: inherit;
}
.setting-form-group input:focus,
.setting-form-group textarea:focus {
    outline: none;
    border-color: rgba(220, 181, 104, 0.65);
    box-shadow: 0 0 0 3px rgba(220, 181, 104, 0.15);
}
.setting-form-group input:disabled { opacity: 0.7; cursor: not-allowed; }
.setting-form-group textarea { resize: vertical; min-height: 100px; }
.setting-form-group input::placeholder,
.setting-form-group textarea::placeholder { color: #8d7878; }
.setting-error { color: #fca5a5; font-size: 0.78rem; margin: 6px 0 0; }

.setting-submit {
    width: 100%;
    margin-top: 6px;
    padding: 12px 14px;
    border-radius: 999px;
    border: 0;
    font-size: 0.92rem;
    font-weight: 800;
    color: #2a1406;
    background: linear-gradient(135deg, #ffe2a3, #dcb568 50%, #b8860b);
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(220, 181, 104, 0.4);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.setting-submit:hover { transform: translateY(-1px); }
.setting-submit-danger {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    color: #fff;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
}

.setting-row { padding: 4px 0; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.setting-row-main { flex: 1; min-width: 0; }
.setting-row-label { font-size: 0.92rem; margin-bottom: 4px; font-weight: 700; color: #f8e9c8; }
.setting-row-desc { font-size: 0.78rem; color: #b69f9f; line-height: 1.55; }
.setting-linked { color: #6ee7b7; margin-left: 6px; font-size: 0.78rem; font-weight: 800; }

.setting-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 18px;
    border-radius: 999px;
    font-size: 0.86rem;
    font-weight: 700;
    text-decoration: none;
    border: 0;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s ease;
}
.setting-btn:hover { opacity: 0.92; }
.setting-btn-line { background: #06c755; color: #fff; }
.setting-btn-unlink {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #f8e9c8;
}
.setting-btn-unlink:hover { background: rgba(255, 255, 255, 0.06); }

.withdraw-warning {
    display: flex;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    background: rgba(248, 113, 113, 0.10);
    border: 1px solid rgba(220, 38, 38, 0.35);
    color: #fecaca;
    font-size: 0.82rem;
    margin-bottom: 14px;
    line-height: 1.55;
}
.withdraw-warning i { margin-top: 2px; color: #fca5a5; }
.withdraw-warning-small { margin-top: 4px; font-size: 0.75rem; opacity: 0.8; }
.withdraw-check { display: flex; align-items: flex-start; gap: 8px; font-size: 0.82rem; color: #f9f5f5; margin: 4px 0 12px; cursor: pointer; }
.withdraw-check input { margin-top: 3px; accent-color: #ef4444; }
</style>
@endpush
