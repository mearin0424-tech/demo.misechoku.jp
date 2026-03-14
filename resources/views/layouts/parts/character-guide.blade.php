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
            request()->is('*/search*') => '気になるお相手をお探しください。条件検索やAI検索もご利用いただけます。',
            str_contains($bodyClass, 'page-talk-room'),
            request()->is('*/talk/*') => 'メッセージをお送りいただき、やり取りをお進めください。日程調整やご確認もこちらで行えます。',
            str_contains($bodyClass, 'page-talk-list'),
            request()->is('*/talk') => 'やり取りをご希望のお相手をお選びいただき、気になる会話をお開きください。',
            str_contains($bodyClass, 'page-cast-mypage'),
            request()->is('cast/mypage*'),
            request()->is('shop/mypage*') => 'プロフィールや設定を整えていただくと、よりマッチしやすくなります。',
            str_contains($bodyClass, 'page-cast-profile'),
            str_contains($bodyClass, 'page-shop-profile'),
            request()->is('*/profile*') => 'お写真やプロフィールをご覧いただき、気になるお相手かご確認ください。',
            request()->is('*/recruit*') => '求人内容をご確認のうえ、気になる募集は保存やご応募へお進みください。',
            request()->is('setting/*') => 'アカウント設定を見直していただくと、より安心してご利用いただけます。',
            request()->is('support/*'),
            request()->is('about'),
            request()->is('terms'),
            request()->is('privacy') => '気になる情報をご確認いただき、ご不明点がございましたらサポートもご覧ください。',
            default => '気になる項目をタップして、次の画面へお進みください。'
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