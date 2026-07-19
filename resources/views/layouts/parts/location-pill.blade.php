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
