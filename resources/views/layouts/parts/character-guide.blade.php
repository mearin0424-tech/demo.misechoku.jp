<div id="character-guide" class="character-guide-container {{ isset($guideMessage) ? '' : 'is-hidden' }}">
    <div class="character-guide-inner">
        <div class="character-avatar">
            <img src="{{ asset('assets/images/character.png') }}" alt="オコジョ">
        </div>
        
        <div class="character-balloon">
            <p id="character-message-content">{!! nl2br(e($guideMessage ?? '')) !!}</p>
        </div>

        <button type="button" id="character-close-trigger" class="character-close-btn">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>