{{-- 探索拠点（現在地 or パスポート）の表示＋切替ピル
     使い方:
        @include('layouts.parts.location-pill')

     呼び出し元のレイアウト／コントローラから $userLocation 変数（UserLocationService::getActiveLocation の結果 or null）が渡されることを想定。
     渡されない場合は app() 経由でその場で解決する。
--}}
@php
    if (!isset($userLocation)) {
        $userLocation = app(\App\Services\UserLocationService::class)->getActiveLocation();
    }
    $modeLabel = match ($userLocation['mode'] ?? null) {
        'current' => '現在地',
        'passport' => '指定位置',
        'profile' => 'プロフィール住所',
        default => '未設定',
    };
@endphp

<div class="location-pill-wrap">
    <button type="button"
            class="location-pill {{ $userLocation ? 'is-set' : 'is-unset' }}"
            id="location-pill-trigger"
            aria-haspopup="dialog"
            aria-controls="location-modal-overlay">
        <i class="fas fa-location-dot" aria-hidden="true"></i>
        <span class="location-pill__mode">{{ $modeLabel }}</span>
        @if($userLocation && !empty($userLocation['label']))
            <span class="location-pill__label">{{ $userLocation['label'] }}</span>
        @else
            <span class="location-pill__label">距離検索を有効にする</span>
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
        <h2 class="location-modal__title">探索拠点を設定</h2>
        <p class="location-modal__lead">
            キャスト／店舗との距離を表示するには、現在地を取得するか、任意の住所・駅名を指定してください。
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

        @if($userLocation)
            <div class="location-modal__current">
                <span>現在の探索拠点：<strong>{{ $modeLabel }}</strong></span>
                @if(!empty($userLocation['label']))
                    <span class="location-modal__current-label">{{ $userLocation['label'] }}</span>
                @endif
                <button type="button" id="location-clear" class="location-modal__btn-ghost">
                    <i class="fas fa-rotate-left"></i> 解除
                </button>
            </div>
        @endif

        <p id="location-modal-message" class="location-modal__message" hidden></p>
    </div>
</div>
