@php
    $bodyClass = trim($__env->yieldContent('body-class'));
    $resolvedGuideMessage = '';

    // オコジョガイドは SEARCH と LIKES（つながり / interaction）の画面でのみ表示
    $isSearch = str_contains($bodyClass, 'page-search') || request()->is('*/search*');
    $isLikes = str_contains($bodyClass, 'page-interaction') || request()->is('*/interaction*');
    if ($isSearch || $isLikes) {
        $resolvedGuideMessage = '現在ガイドのセリフは実装中です';
    }
@endphp

{{-- オコジョガイド：右下浮遊デザイン（×で非表示にできる） --}}
<div id="character-guide" class="discovery-guide {{ $resolvedGuideMessage !== '' ? '' : 'is-hidden' }}">
    {{-- 左側の吹き出し --}}
    <div class="guide-speech-bubble">
        <button type="button" class="guide-close-btn" id="character-guide-close" aria-label="ガイドを閉じる">&times;</button>
        <p id="character-message-content">{!! nl2br(e($resolvedGuideMessage)) !!}</p>
    </div>
    {{-- 右側のキャラクター --}}
    <div class="guide-character-wrap">
        <img src="{{ asset('assets/images/guide/guide-character.png') }}" alt="ガイド">
    </div>
</div>