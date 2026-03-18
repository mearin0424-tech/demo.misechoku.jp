@php
    $bodyClass = trim($__env->yieldContent('body-class'));
    $resolvedGuideMessage = '';

    // オコジョガイドは SEARCH と LIKES（つながり / interaction）の画面でのみ表示。ホームのスワイプ画面では表示しない
    $isHome = str_contains($bodyClass, 'page-home');
    $isSearch = str_contains($bodyClass, 'page-search') || request()->is('*/search*');
    $isLikes = str_contains($bodyClass, 'page-interaction') || request()->is('*/interaction*');
    if (!$isHome && ($isSearch || $isLikes)) {
        // オコジョガイド：優良支払店バッヂの条件説明
        $resolvedGuideMessage = implode("\n", [
            '【優良支払店バッヂとは？】',
            '直近3ヶ月のあいだに発生した請求・入金データについて、',
            '・すべての案件が「店舗入金確認済み」まで完了していて、',
            '・請求書発行から店舗入金確認までが10日以内',
            'の店舗だけに付与される、安全性重視のバッヂです。',
        ]);
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