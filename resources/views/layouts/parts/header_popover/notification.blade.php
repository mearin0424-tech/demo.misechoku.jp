<div id="header-notification-popup" class="header-popup stop-propagation" style="display:none;">
    <div class="task-popup-header">
        <h4>お知らせ</h4>
        <button class="btn-close-popup" onclick="togglePopup('header-notification-popup')">&times;</button>
    </div>
    <div class="notification-popup-content">
        @if(isset($notifications) && count($notifications) > 0)
            @foreach($notifications as $item)
                <div style="padding:10px; border-bottom:1px solid #333; font-size:0.85rem; color:#fff;">
                    {{ $item['title'] }}
                </div>
            @endforeach
        @else
            <div style="padding:15px; color:#999; text-align:center; font-size:0.8rem;">新しいお知らせはありません。</div>
        @endif
    </div>
</div>