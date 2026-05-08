@php
    use App\Services\CharacterGuideService;

    // 表示判定とセリフは運営管理 DB（character_guide_settings）に一本化。
    // 設定が無い／無効化されている画面では何も表示しない（デフォルト文言は持たない）。
    $resolvedGuideMessage = '';
    $shouldShow = false;

    $routeName = optional(request()->route())->getName();
    if ($routeName) {
        try {
            $setting = app(CharacterGuideService::class)->getForRoute($routeName);
            if ($setting['enabled'] && $setting['message'] !== '') {
                $resolvedGuideMessage = $setting['message'];
                $shouldShow = true;
            }
        } catch (\Throwable $e) {
            // DB 未準備時などは静かにスキップ
        }
    }
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
