{{-- 非公開情報ゲート: パスワード再入力でロック解除する共通パーツ
     使い方:
        @include('admin.parts.private-gate', [
            'isUnlocked' => $isUnlocked,
            'unlockTtlSeconds' => $unlockTtlSeconds,
            'unlockUrl' => route('admin.casts.unlock-private', $castId),
            'lockUrl' => route('admin.casts.lock-private', $castId),
        ])
        @if($isUnlocked)
            <section class="admin-panel admin-private-section">…</section>
        @endif
--}}
@php
    $unlockError = session('private_unlock_error');
    $minutesRemaining = isset($unlockTtlSeconds) ? (int) ceil(((int) $unlockTtlSeconds) / 60) : 0;
@endphp

@if(!$isUnlocked)
    <section class="admin-panel admin-private-gate">
        <div class="admin-private-gate__icon">
            <i class="fas fa-lock"></i>
        </div>
        <div class="admin-private-gate__body">
            <h2 class="admin-panel-title u-mb-0">非公開情報はロックされています</h2>
            <p class="admin-note u-mb-0">
                連絡先（電話番号・メール・住所）、本名、口座情報、内部メモなどの厳重情報は、管理者パスワードを再入力すると一時的に表示できます。<br>
                解除中は <strong>15&nbsp;分</strong> 経過で自動的に再ロックされます。
            </p>
            <form method="POST" action="{{ $unlockUrl }}" class="admin-private-gate__form">
                @csrf
                <label class="admin-private-gate__field">
                    <span>管理者パスワード</span>
                    <input type="password" name="password" required autocomplete="current-password" placeholder="現在のログインパスワードを入力">
                </label>
                <button type="submit" class="btn-action manage">
                    <i class="fas fa-unlock-alt"></i> 非公開情報を解除する
                </button>
            </form>
            @if($unlockError)
                <p class="admin-private-gate__error" role="alert">
                    <i class="fas fa-circle-exclamation"></i> {{ $unlockError }}
                </p>
            @endif
        </div>
    </section>
@else
    <div class="admin-private-status" role="status">
        <span class="admin-private-status__pill">
            <i class="fas fa-unlock-alt"></i>
            非公開情報を解除中（残り 約{{ $minutesRemaining }} 分）
        </span>
        <form method="POST" action="{{ $lockUrl }}" class="u-inline">
            @csrf
            <button type="submit" class="admin-private-status__lock">
                <i class="fas fa-lock"></i> いますぐロック
            </button>
        </form>
    </div>
@endif
