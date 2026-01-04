{{-- オコジョガイド：右下浮遊デザイン --}}
<div id="character-guide" class="discovery-guide {{ isset($guideMessage) ? '' : 'is-hidden' }}">
    {{-- 左側の吹き出し --}}
    <div class="guide-speech-bubble">
        <p id="character-message-content">{!! nl2br(e($guideMessage ?? '')) !!}</p>
    </div>
    
    {{-- 右側のキャラクター --}}
    <div class="guide-character-wrap">
        <img src="{{ asset('assets/images/guide/guide-character.png') }}" alt="ガイド">
        {{-- ×ボタン：キャラクターの肩付近に配置 --}}
        <button type="button" id="character-close-trigger" class="guide-close-x">×</button>
    </div>
</div>