{{-- KEEP ボタン（両カード共通）
     $itemId: int  対象アカウントID
     $itemType: 'shop'|'cast'  お気に入り対象種別（求人カードは 'shop'、キャストカードは 'cast'）
     $isKept: bool 現在キープ済みか --}}
<button
    type="button"
    class="swipe-keep-corner swipe-keep-corner--inline stop-propagation {{ !empty($isKept) ? 'is-active' : '' }}"
    data-fav-toggle
    data-item-id="{{ $itemId }}"
    data-item-type="{{ $itemType }}"
    data-action="keep"
    aria-label="キープ"
    aria-pressed="{{ !empty($isKept) ? 'true' : 'false' }}"
>
    <i class="fas fa-bookmark" aria-hidden="true"></i>
</button>
