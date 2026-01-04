<div id="header-task-popup" class="header-popup stop-propagation" style="display:none;">
    <div class="task-popup-header">
        <h4>やることリスト</h4>
        <button class="btn-close-popup" onclick="togglePopup('header-task-popup')">&times;</button>
    </div>
    <div class="task-popup-content">
        @if(isset($todoList) && count($todoList) > 0)
            @foreach($todoList as $todo)
                <div style="padding:10px; border-bottom:1px solid #333; font-size:0.85rem; color:#eee;">
                    <i class="fas fa-exclamation-circle" style="color:var(--color-accent);"></i> {{ $todo['text'] }}
                </div>
            @endforeach
        @else
            <div style="padding:15px; color:#999; text-align:center; font-size:0.8rem;">完了していないタスクはありません。</div>
        @endif
    </div>
</div>