@php
    use App\Services\CharacterGuideService;

    $bodyClass = trim($__env->yieldContent('body-class'));
    $sectionGuide = trim((string) ($guideMessage ?? ''));

    // 1. ページから @section('guide_message') で渡された文言を最優先
    $resolvedGuideMessage = $sectionGuide;
    $isExplicitlyHidden = false;
    $hasExplicitOverride = false;

    if ($sectionGuide !== '') {
        $hasExplicitOverride = true;
    }

    // 2. 明示的にメッセージが渡されていない場合は、運営管理 DB の設定を見る
    $routeName = optional(request()->route())->getName();
    if (!$hasExplicitOverride && $routeName) {
        try {
            $setting = app(CharacterGuideService::class)->getForRoute($routeName);
            if (!$setting['enabled']) {
                $isExplicitlyHidden = true;
            } else {
                $resolvedGuideMessage = $setting['message'];
            }
        } catch (\Throwable $e) {
            // DB 未準備時などは静かにスキップ（後続のフォールバックに委ねる）
        }
    }

    // 3. それでもメッセージが空かつ DB で明示的に非表示でもなければ、旧来のヒューリスティックでフォールバック
    if (!$hasExplicitOverride && !$isExplicitlyHidden && $resolvedGuideMessage === '') {
        $isHome = str_contains($bodyClass, 'page-home');
        $isLikes = str_contains($bodyClass, 'page-interaction') || request()->is('*/interaction*');
        if (!$isHome && $isLikes) {
            $resolvedGuideMessage = '「優良店」バッヂは、直近の請求・入金がスムーズな店舗に付く信頼の目印です。';
        }
    }

    $shouldShow = !$isExplicitlyHidden && $resolvedGuideMessage !== '';
@endphp

{{-- オコジョガイド：右下浮遊デザイン。閉じるボタンはキャラクター右下に配置（吹き出し外） --}}
<div id="character-guide" class="discovery-guide {{ $shouldShow ? '' : 'is-hidden' }}">
    {{-- 左側の吹き出し --}}
    <div class="guide-speech-bubble">
        <p id="character-message-content">{!! nl2br(e($resolvedGuideMessage)) !!}</p>
    </div>
    {{-- 右側のキャラクター（×ボタンはキャラ右下に絶対配置） --}}
    <div class="guide-character-wrap">
        <img src="{{ asset('assets/images/guide/guide-character.png') }}" alt="ガイド">
        <button type="button" class="guide-close-btn" id="character-guide-close" aria-label="ガイドを閉じる">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>
</div>
