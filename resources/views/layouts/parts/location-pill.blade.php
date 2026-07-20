{{-- 探索拠点（現在地 or パスポート）の表示＋切替ピル
     使い方:
        @include('layouts.parts.location-pill')

     呼び出し元のレイアウト／コントローラから $userLocation 変数（UserLocationService::getActiveLocation の結果 or null）が渡されることを想定。
     渡されない場合は app() 経由でその場で解決する。
     - キャスト側: 現在地／住所指定（パスポート）を保存できるモーダルを開く
     - 店舗側: 拠点は店舗住所固定（仕様）のため、説明のみのモーダルを開く
--}}
@php
    $locationService = app(\App\Services\UserLocationService::class);
    if (!isset($userLocation)) {
        $userLocation = $locationService->getActiveLocation();
    }
    $locationMaxKm = (int) ($locationService->getEffectiveMaxDistanceKm() ?? 0);
    $isCastSide = request()->is('cast/*');
    $modeLabel = match ($userLocation['mode'] ?? null) {
        'current' => '現在地',
        'passport' => '指定位置',
        'profile' => $isCastSide ? 'プロフィール住所' : '店舗住所',
        default => '未設定',
    };
    // profile モードは label 自体が「プロフィール住所／店舗住所」なのでチップと重複させない
    $showModeChip = in_array($userLocation['mode'] ?? null, ['current', 'passport'], true);
@endphp

<div class="location-pill-wrap">
    <button type="button"
            class="location-pill {{ $userLocation ? 'is-set' : 'is-unset' }}"
            id="location-pill-trigger"
            aria-haspopup="dialog"
            aria-controls="location-modal-overlay">
        @if($userLocation)
            <i class="fas fa-location-dot location-pill__icon location-pill__icon--set" aria-hidden="true"></i>
            @if($showModeChip)
                <span class="location-pill__mode">{{ $modeLabel }}</span>
            @endif
            <span class="location-pill__label">{{ !empty($userLocation['label']) ? $userLocation['label'] : $modeLabel }}</span>
            @if($locationMaxKm > 0)
                <span class="location-pill__radius">半径{{ $locationMaxKm }}km</span>
            @endif
            <span class="location-pill__cta">{{ $isCastSide ? '変更' : '詳細' }}</span>
        @else
            <i class="fas fa-location-dot location-pill__icon location-pill__icon--unset" aria-hidden="true"></i>
            <span class="location-pill__label location-pill__label--unset">位置情報が未設定です（距離の表示・並び替えが無効）</span>
            <span class="location-pill__cta location-pill__cta--unset">設定する</span>
        @endif
        <i class="fas fa-chevron-right location-pill__chev" aria-hidden="true"></i>
    </button>
</div>

{{-- モーダル（同じページに1つだけ） --}}
<div id="location-modal-overlay" class="location-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-label="探索拠点の設定">
    <div class="location-modal">
        <button type="button" class="location-modal__close js-location-close" aria-label="閉じる">
            <i class="fas fa-xmark"></i>
        </button>
        <h2 class="location-modal__title">{{ $isCastSide ? '探索拠点を設定' : '探索拠点について' }}</h2>

        @if($isCastSide)
            <p class="location-modal__lead">
                お店との距離を表示するには、現在地を取得するか、任意の住所・駅名を指定してください。
            </p>

            <div class="location-modal__section">
                <h3 class="location-modal__section-title">現在地から探す</h3>
                <button type="button" id="location-use-current" class="location-modal__btn-primary">
                    <i class="fas fa-crosshairs"></i> 端末の現在地を取得
                </button>
                <p class="location-modal__hint">ブラウザの位置情報の許可が必要です。</p>
            </div>

            <div class="location-modal__divider"><span>または</span></div>

            <div class="location-modal__section">
                <h3 class="location-modal__section-title">パスポートモード（住所・駅名で指定）</h3>
                <form id="location-passport-form" class="location-modal__form">
                    <input type="text" name="address" class="location-modal__input" placeholder="例: 東京都港区六本木 / 新宿駅" autocomplete="off" required>
                    <button type="submit" class="location-modal__btn-secondary">
                        <i class="fas fa-paper-plane"></i> この位置で検索
                    </button>
                </form>
            </div>
        @else
            {{-- 店舗側：拠点は店舗住所に固定（キャスト側のようなモード切替は無い仕様） --}}
            <p class="location-modal__lead">
                店舗の探索拠点は<strong>登録済みの店舗住所</strong>に固定されています。
                キャストとの距離はこの住所を起点に表示されます。
            </p>
            @if(!$userLocation)
                <p class="location-modal__lead">
                    店舗住所（緯度経度）が未登録のため、距離の表示・並び替えが無効になっています。
                    プロフィール編集から住所を登録してください。
                </p>
                @if(Route::has('shop.profile.edit'))
                    <a href="{{ route('shop.profile.edit') }}" class="location-modal__btn-primary" style="text-decoration:none;">
                        <i class="fas fa-pen"></i> プロフィール編集で住所を登録
                    </a>
                @endif
            @else
                <p class="location-modal__hint">検索半径（○km圏内）は詳細検索から変更できます。</p>
            @endif
        @endif

        @if($userLocation)
            <div class="location-modal__current">
                <span>現在の探索拠点：<strong>{{ $modeLabel }}</strong></span>
                @if(!empty($userLocation['label']) && $userLocation['label'] !== $modeLabel)
                    <span class="location-modal__current-label">{{ $userLocation['label'] }}</span>
                @endif
                @if($isCastSide && in_array($userLocation['mode'] ?? null, ['current', 'passport'], true))
                    <button type="button" id="location-clear" class="location-modal__btn-ghost">
                        <i class="fas fa-rotate-left"></i> 解除
                    </button>
                @endif
            </div>
        @endif

        <p id="location-modal-message" class="location-modal__message" hidden></p>
    </div>
</div>


{{-- モーダルのスタイル同梱（2026-07-20）：
     従来は旧 app.css / search.css に定義があり、検索ページ以外（サイドメニュー経由）で
     開くと未スタイルで崩れていた。コンポーネントに CSS を同梱して全画面で成立させる。
     ライトモード補正は light-theme.css（body.theme-light .location-modal*）が上書きする。 --}}
<style id="location-modal-css-bundled">
.location-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.78);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.18s ease;
}
.location-modal-overlay[aria-hidden="false"] {
    opacity: 1;
    pointer-events: auto;
}
.location-modal {
    position: relative;
    width: min(420px, 100%);
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    background: linear-gradient(180deg, var(--color-sub), var(--dark-bg));
    border: 1px solid var(--color-border-strong);
    border-radius: 18px;
    padding: 22px 22px 18px;
    color: var(--color-text-header);
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.7);
}
.location-modal__close {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 0;
    background: rgba(255, 255, 255, 0.08);
    color: var(--color-text-muted);
    cursor: pointer;
}
.location-modal__close:hover { background: rgba(255, 255, 255, 0.16); color: var(--color-text-header); }
.location-modal__title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--color-text-header);
    margin: 0 0 8px;
    font-family: var(--font-serif);
    letter-spacing: 0.04em;
}
.location-modal__lead {
    font-size: 0.82rem;
    color: var(--color-text-muted);
    line-height: 1.7;
    margin: 0 0 16px;
}
.location-modal__section { margin-bottom: 14px; }
.location-modal__section-title {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--gold);
    margin: 0 0 8px;
    letter-spacing: 0.04em;
}
.location-modal__btn-primary,
.location-modal__btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 11px 14px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.92rem;
    cursor: pointer;
    border: 0;
}
.location-modal__btn-primary {
    background: linear-gradient(135deg, var(--gold-light), var(--gold));
    color: #1a1206;
}
.location-modal__btn-primary:hover { filter: brightness(1.05); }
.location-modal__btn-secondary {
    background: transparent;
    border: 1px solid var(--color-border-strong);
    color: var(--color-text-header);
}
.location-modal__btn-secondary:hover { background: rgba(168, 85, 247, 0.08); }
.location-modal__btn-ghost {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 999px;
    padding: 5px 10px;
    color: var(--color-text-muted);
    font-size: 0.78rem;
    cursor: pointer;
    margin-left: auto;
}
.location-modal__btn-ghost:hover { color: var(--color-text-header); border-color: rgba(255, 255, 255, 0.35); }
.location-modal__hint {
    font-size: 0.7rem;
    color: var(--color-text-muted);
    margin: 6px 0 0;
}
.location-modal__divider {
    text-align: center;
    color: var(--color-text-muted);
    font-size: 0.74rem;
    margin: 12px 0;
    position: relative;
}
.location-modal__divider::before,
.location-modal__divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 38%;
    height: 1px;
    background: var(--color-border);
}
.location-modal__divider::before { left: 0; }
.location-modal__divider::after { right: 0; }
.location-modal__form {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin: 0;
}
.location-modal__input {
    height: 42px;
    padding: 0 12px;
    border-radius: 10px;
    border: 1px solid var(--color-border-strong);
    background: rgba(255, 255, 255, 0.06);
    color: var(--color-text-header);
    font-size: 0.92rem;
}
.location-modal__input:focus {
    outline: 2px solid rgba(168, 85, 247, 0.45);
    outline-offset: 1px;
    background: rgba(255, 255, 255, 0.1);
}
.location-modal__current {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 14px;
    padding: 10px 12px;
    border-radius: 10px;
    background: rgba(168, 85, 247, 0.06);
    border: 1px solid var(--color-border);
    font-size: 0.82rem;
}
.location-modal__current-label { color: var(--color-text-header); font-weight: 700; }
.location-modal__message {
    margin-top: 10px;
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 0.82rem;
    background: var(--color-danger-bg);
    border: 1px solid rgba(248, 113, 113, 0.4);
    color: var(--color-danger);
}
.location-modal__message.is-success {
    background: var(--color-success-bg);
    border-color: rgba(74, 222, 128, 0.4);
    color: var(--color-success);
}

/* ==========================================================================
   共有メニュー（丸ボタン＋ポップアップ）
   ========================================================================== */
</style>
