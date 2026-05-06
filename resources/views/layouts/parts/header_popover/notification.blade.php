<div id="header-notification-popup" class="header-popup stop-propagation" style="display:none;">
    <div class="task-popup-header">
        <h4>お知らせ</h4>
        <button class="btn-close-popup" onclick="togglePopup('header-notification-popup')">&times;</button>
    </div>
    <div class="notification-popup-content">
        <div style="padding:10px 12px; border-bottom:1px solid #333; font-size:0.76rem; color:#D4AF37;">運営からのお知らせ</div>
        @if(isset($operationalNotices) && count($operationalNotices) > 0)
            @foreach($operationalNotices as $notice)
                @php $noticeUrl = $notice['url'] ?? null; @endphp
                @if($noticeUrl)
                    <a href="{{ $noticeUrl }}" style="display:block; padding:10px; border-bottom:1px solid #333; text-decoration:none; color:#fff;">
                        <div style="font-size:0.85rem;">{{ $notice['title'] }}</div>
                        @if(!empty($notice['published_at']))
                            <div style="margin-top:4px; font-size:0.72rem; color:#bbb;">{{ $notice['published_at'] }}</div>
                        @endif
                    </a>
                @else
                    <div style="padding:10px; border-bottom:1px solid #333; font-size:0.85rem; color:#fff;">
                        <div>{{ $notice['title'] }}</div>
                        @if(!empty($notice['published_at']))
                            <div style="margin-top:4px; font-size:0.72rem; color:#bbb;">{{ $notice['published_at'] }}</div>
                        @endif
                    </div>
                @endif
            @endforeach
        @else
            <div style="padding:12px; border-bottom:1px solid #333; color:#999; text-align:center; font-size:0.8rem;">表示できるお知らせはありません。</div>
        @endif

        <div style="padding:10px 12px; border-bottom:1px solid #333; font-size:0.76rem; color:#D4AF37;">あなたへの通知</div>
        {{-- Push 通知テスト（PWA） --}}
        <div class="push-actions" style="padding:10px 12px; border-bottom:1px solid #333;">
            <button type="button" id="push-enable-btn" class="push-action-btn" style="display:block; width:100%; padding:8px 12px; margin-bottom:6px; background:#2a1a1a; color:#D4AF37; border:1px solid #444; border-radius:6px; font-size:0.8rem; cursor:pointer;">通知を有効にする</button>
            <button type="button" id="push-test-btn" class="push-action-btn" style="display:block; width:100%; padding:8px 12px; background:#1a2a1a; color:#8bc34a; border:1px solid #444; border-radius:6px; font-size:0.8rem; cursor:pointer;">テスト通知を送る</button>
        </div>
        @if(isset($notifications) && count($notifications) > 0)
            @foreach($notifications as $item)
                @php $url = $item['url'] ?? null; @endphp
                @if($url)
                    <a href="{{ $url }}" style="display:block; padding:10px; border-bottom:1px solid #333; text-decoration:none; color:#fff;">
                        <div style="font-size:0.85rem;">{{ $item['title'] }}</div>
                        @if(!empty($item['body']))
                            <div style="margin-top:4px; font-size:0.76rem; color:#bbb;">{{ $item['body'] }}</div>
                        @endif
                    </a>
                @else
                    <div style="padding:10px; border-bottom:1px solid #333; font-size:0.85rem; color:#fff;">
                        <div>{{ $item['title'] }}</div>
                        @if(!empty($item['body']))
                            <div style="margin-top:4px; font-size:0.76rem; color:#bbb;">{{ $item['body'] }}</div>
                        @endif
                    </div>
                @endif
            @endforeach
        @else
            <div style="padding:15px; color:#999; text-align:center; font-size:0.8rem;">新しいお知らせはありません。</div>
        @endif
    </div>
</div>