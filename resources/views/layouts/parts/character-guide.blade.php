@php
    $bodyClass = trim($__env->yieldContent('body-class'));
    $resolvedGuideMessage = '';

    // オコジョガイドは SEARCH と LIKES（つながり / interaction）の画面でのみ表示。ホームのスワイプ画面では表示しない
    $isHome = str_contains($bodyClass, 'page-home');
    $isSearch = str_contains($bodyClass, 'page-search') || request()->is('*/search*');
    $isLikes = str_contains($bodyClass, 'page-interaction') || request()->is('*/interaction*');
    if (!$isHome && ($isSearch || $isLikes)) {
        // オコジョガイド：詳細は店舗マイページのバッヂ（モーダル）で案内（キャスト向けは一言に短縮）
        $resolvedGuideMessage = '「優良店」バッヂは、直近の請求・入金がスムーズな店舗に付く信頼の目印です。';
    }
@endphp

{{-- オコジョガイド：右下浮遊デザイン（×は吹き出し内に絶対配置し、欄外に出さない） --}}
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