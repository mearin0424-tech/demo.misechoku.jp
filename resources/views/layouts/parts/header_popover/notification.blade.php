<div id="header-notification-popup" class="header-popup stop-propagation" style="display:none;">
    <div class="task-popup-header">
        <h4>お知らせ</h4>
        <button class="btn-close-popup" onclick="togglePopup('header-notification-popup')">&times;</button>
    </div>
    <div class="notification-popup-content">
        <div class="notif-popup-section-label">運営からのお知らせ</div>
        @if(isset($operationalNotices) && count($operationalNotices) > 0)
            @foreach($operationalNotices as $notice)
                @php $noticeUrl = $notice['url'] ?? null; @endphp
                @if($noticeUrl)
                    <a href="{{ $noticeUrl }}" class="notif-popup-item">
                        <div class="notif-popup-item__title">{{ $notice['title'] }}</div>
                        @if(!empty($notice['published_at']))
                            <div class="notif-popup-item__meta">{{ $notice['published_at'] }}</div>
                        @endif
                    </a>
                @else
                    <div class="notif-popup-item">
                        <div class="notif-popup-item__title">{{ $notice['title'] }}</div>
                        @if(!empty($notice['published_at']))
                            <div class="notif-popup-item__meta">{{ $notice['published_at'] }}</div>
                        @endif
                    </div>
                @endif
            @endforeach
        @else
            <div class="notif-popup-empty">表示できるお知らせはありません。</div>
        @endif

        <div class="notif-popup-section-label">あなたへの通知</div>
        {{-- Push 通知テスト（PWA） --}}
        <div class="notif-popup-pushrow">
            <button type="button" id="push-enable-btn" class="app-btn app-btn--ghost app-btn--sm notif-popup-push-btn">通知を有効にする</button>
            <button type="button" id="push-test-btn" class="app-btn app-btn--outline app-btn--sm notif-popup-push-btn">テスト通知を送る</button>
        </div>
        @if(isset($notifications) && count($notifications) > 0)
            @foreach($notifications as $item)
                @php $url = $item['url'] ?? null; @endphp
                @if($url)
                    <a href="{{ $url }}" class="notif-popup-item">
                        <div class="notif-popup-item__title">{{ $item['title'] }}</div>
                        @if(!empty($item['body']))
                            <div class="notif-popup-item__meta">{{ $item['body'] }}</div>
                        @endif
                    </a>
                @else
                    <div class="notif-popup-item">
                        <div class="notif-popup-item__title">{{ $item['title'] }}</div>
                        @if(!empty($item['body']))
                            <div class="notif-popup-item__meta">{{ $item['body'] }}</div>
                        @endif
                    </div>
                @endif
            @endforeach
        @else
            <div class="notif-popup-empty">新しいお知らせはありません。</div>
        @endif
    </div>
</div>
