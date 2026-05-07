{{-- 共有メニュー：丸ボタン1つ＋押下で SNS とコピーのメニューを展開
     使い方:
        @include('partials.share-menu', [
            'shareUrl'   => $shareUrl,
            'shareTitle' => '...',
            'shareText'  => '...',
            'menuId'     => 'recruit-share-menu',  // ページ内で一意な ID
        ])
--}}
@php
    $shareUrl = $shareUrl ?? '';
    $shareTitle = $shareTitle ?? '';
    $shareText = $shareText ?? '';
    $menuId = $menuId ?? 'share-menu-' . substr(md5($shareUrl . microtime(true)), 0, 6);
    $xShareUrl = 'https://twitter.com/intent/tweet?text=' . rawurlencode(trim($shareTitle . "\n" . $shareText)) . '&url=' . rawurlencode($shareUrl);
    $lineShareUrl = 'https://social-plugins.line.me/lineit/share?url=' . rawurlencode($shareUrl);
@endphp
<div class="share-menu" data-share-menu>
    <button type="button"
            class="share-menu__trigger"
            aria-haspopup="menu"
            aria-controls="{{ $menuId }}"
            aria-expanded="false"
            aria-label="この情報をSNSで共有"
            data-share-menu-trigger>
        <i class="fas fa-share-nodes" aria-hidden="true"></i>
    </button>
    <div id="{{ $menuId }}"
         class="share-menu__panel"
         role="menu"
         aria-label="共有メニュー"
         hidden
         data-share-menu-panel
         data-share-url="{{ $shareUrl }}"
         data-share-title="{{ $shareTitle }}"
         data-share-text="{{ $shareText }}">
        <button type="button" class="share-menu__item" role="menuitem" data-share-action="native">
            <i class="fas fa-arrow-up-from-bracket"></i> 共有...
        </button>
        <a href="{{ $xShareUrl }}" target="_blank" rel="noopener noreferrer" class="share-menu__item" role="menuitem">
            <span class="share-menu__icon-x" aria-hidden="true">𝕏</span> X (Twitter)
        </a>
        <a href="{{ $lineShareUrl }}" target="_blank" rel="noopener noreferrer" class="share-menu__item" role="menuitem">
            <i class="fab fa-line" style="color:#06C755;"></i> LINE
        </a>
        <button type="button" class="share-menu__item" role="menuitem" data-share-action="copy">
            <i class="fas fa-link"></i> URLをコピー
        </button>
    </div>
</div>
