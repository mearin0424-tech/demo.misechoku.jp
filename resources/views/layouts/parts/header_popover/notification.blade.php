@php
    // ----- 共通: notification アイテムから安全に値を取り出すヘルパ -----
    $notifField = static function ($item, string $key, $default = null) {
        if (is_array($item)) {
            return $item[$key] ?? $default;
        }
        if (is_object($item)) {
            return $item->{$key} ?? $default;
        }
        return $default;
    };
@endphp
<div id="header-notification-popup" class="header-popup stop-propagation" style="display:none;" data-notification-popup>
    <div class="task-popup-header">
        <h4>お知らせ</h4>
        <div style="display:flex; gap:6px; align-items:center;">
            @php
                $hasUnread = false;
                if (!empty($notifications)) {
                    foreach ($notifications as $__n) {
                        if ($notifField($__n, 'is_unread', false)) { $hasUnread = true; break; }
                    }
                }
            @endphp
            @if($hasUnread)
                <button type="button" class="notif-popup-readall" data-notif-mark-all aria-label="すべて既読にする">
                    全て既読
                </button>
            @endif
            <button class="btn-close-popup" onclick="togglePopup('header-notification-popup')">&times;</button>
        </div>
    </div>
    <div class="notification-popup-content">
        <div class="notif-popup-section-label">運営からのお知らせ</div>
        @if(!empty($operationalNotices) && count($operationalNotices) > 0)
            @foreach($operationalNotices as $notice)
                @php
                    $noticeUrl   = $notifField($notice, 'url');
                    $noticeTitle = $notifField($notice, 'title', '');
                    $noticeAt    = $notifField($notice, 'published_at');
                @endphp
                @if($noticeUrl)
                    <a href="{{ $noticeUrl }}" class="notif-popup-item">
                        <div class="notif-popup-item__title">{{ $noticeTitle }}</div>
                        @if(!empty($noticeAt))
                            <div class="notif-popup-item__meta">{{ $noticeAt }}</div>
                        @endif
                    </a>
                @else
                    <div class="notif-popup-item">
                        <div class="notif-popup-item__title">{{ $noticeTitle }}</div>
                        @if(!empty($noticeAt))
                            <div class="notif-popup-item__meta">{{ $noticeAt }}</div>
                        @endif
                    </div>
                @endif
            @endforeach
        @else
            <div class="notif-popup-empty">表示できるお知らせはありません。</div>
        @endif

        <div class="notif-popup-section-label">あなたへの通知</div>
        {{-- PWA プッシュ通知：この端末で通知を受け取りたい場合のみ有効化。
             実際の通知テストはトーク送信・レビュー投稿・LIKE 等の本番フローで実行できる。 --}}
        <div class="notif-popup-pushrow">
            <button type="button" id="push-enable-btn" class="app-btn app-btn--ghost app-btn--sm notif-popup-push-btn">この端末で通知を受け取る</button>
        </div>
        @if(!empty($notifications) && count($notifications) > 0)
            @foreach($notifications as $item)
                @php
                    $url     = $notifField($item, 'url');
                    $unread  = (bool) $notifField($item, 'is_unread', false);
                    $itemId  = $notifField($item, 'id', '');
                    $title   = $notifField($item, 'title', '');
                    $body    = $notifField($item, 'body');
                    $cLabel  = $notifField($item, 'created_at_label');
                @endphp
                @if($url)
                    <a href="{{ $url }}"
                       class="notif-popup-item {{ $unread ? 'is-unread' : '' }}"
                       data-notif-item
                       data-notif-id="{{ $itemId }}">
                        @if($unread)<span class="notif-popup-item__dot" aria-hidden="true"></span>@endif
                        <div class="notif-popup-item__title">{{ $title }}</div>
                        @if(!empty($body))
                            <div class="notif-popup-item__meta">{{ $body }}</div>
                        @endif
                        @if(!empty($cLabel))
                            <div class="notif-popup-item__time">{{ $cLabel }}</div>
                        @endif
                    </a>
                @else
                    <div class="notif-popup-item {{ $unread ? 'is-unread' : '' }}"
                         data-notif-item
                         data-notif-id="{{ $itemId }}">
                        @if($unread)<span class="notif-popup-item__dot" aria-hidden="true"></span>@endif
                        <div class="notif-popup-item__title">{{ $title }}</div>
                        @if(!empty($body))
                            <div class="notif-popup-item__meta">{{ $body }}</div>
                        @endif
                        @if(!empty($cLabel))
                            <div class="notif-popup-item__time">{{ $cLabel }}</div>
                        @endif
                    </div>
                @endif
            @endforeach
        @else
            <div class="notif-popup-empty">新しいお知らせはありません。</div>
        @endif
    </div>
</div>
