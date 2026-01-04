<div id="okojo-guide" class="okojo-guide-container {{ isset($guideMessage) ? '' : 'is-hidden' }}">
    <div class="okojo-guide-inner">
        <div class="okojo-avatar">
            <img src="{{ asset('assets/img/okojo-icon.png') }}" alt="オコジョ">
        </div>
        
        <div class="okojo-balloon">
            <p id="okojo-message-content">{!! nl2br(e($guideMessage ?? '')) !!}</p>
        </div>

        <button type="button" id="okojo-close-trigger" class="okojo-close-btn">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>