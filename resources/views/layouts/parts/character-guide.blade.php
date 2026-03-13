@php
    $bodyClass = trim($__env->yieldContent('body-class'));
    $resolvedGuideMessage = trim((string) ($guideMessage ?? ''));

    if ($resolvedGuideMessage === '') {
        $resolvedGuideMessage = match (true) {
            str_contains($bodyClass, 'page-demo-login'),
            str_contains($bodyClass, 'page-auth-login') => '',
            str_contains($bodyClass, 'page-home'),
            request()->is('shop/home'),
            request()->is('cast/home') => "上下スワイプ：次 / 前のアカウントに移動\n左右スワイプ：同じアカウントの別写真を表示\nタップ：詳細プロフィールを開く\n右側のボタン：いいね・キープ・メッセージ",
            str_contains($bodyClass, 'page-search'),
            request()->is('*/search*') => '気になる相手を探してみてね！条件検索やAI検索も使えるよ。',
            str_contains($bodyClass, 'page-talk-room'),
            request()->is('*/talk/*') => 'メッセージを送って会話を進めてみよう。日程調整や確認もここでできるよ。',
            str_contains($bodyClass, 'page-talk-list'),
            request()->is('*/talk') => 'やり取りしたい相手を選んで、気になる会話を開いてみてね。',
            str_contains($bodyClass, 'page-cast-mypage'),
            request()->is('cast/mypage*'),
            request()->is('shop/mypage*') => 'プロフィールや設定を整えると、マッチしやすくなるよ。',
            str_contains($bodyClass, 'page-cast-profile'),
            str_contains($bodyClass, 'page-shop-profile'),
            request()->is('*/profile*') => '写真やプロフィールを見て、気になる相手かチェックしてみてね。',
            request()->is('*/recruit*') => '求人内容を確認して、気になる募集は保存や応募につなげてみてね。',
            request()->is('setting/*') => 'アカウント設定を見直すと、より安心して使えるよ。',
            request()->is('support/*'),
            request()->is('about'),
            request()->is('terms'),
            request()->is('privacy') => '気になる情報を読んで、不明点があればサポートも見てみてね。',
            default => '気になる項目をタップして、次の画面へ進んでみてね。'
        };
    }
@endphp

{{-- オコジョガイド：右下浮遊デザイン --}}
<div id="character-guide" class="discovery-guide {{ $resolvedGuideMessage !== '' ? '' : 'is-hidden' }}">
    {{-- 左側の吹き出し --}}
    <div class="guide-speech-bubble">
        <p id="character-message-content">{!! nl2br(e($resolvedGuideMessage)) !!}</p>
    </div>
    
    {{-- 右側のキャラクター --}}
    <div class="guide-character-wrap">
        <img src="{{ asset('assets/images/guide/guide-character.png') }}" alt="ガイド">
    </div>
</div>