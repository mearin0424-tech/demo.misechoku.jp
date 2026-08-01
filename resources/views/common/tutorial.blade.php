@extends('layouts.app-v2')

@section('title', $title)
@section('body-class', 'page-tutorial page-tutorial-' . $role)

@section('content')
<div class="tutorial-shell" aria-label="かんたんチュートリアル">
    {{-- Skip：右上・小さく --}}
    <a href="{{ $skipUrl }}" class="tutorial-skip" data-form-guard-bypass>スキップ</a>

    {{-- 横スクロールで各スライドをスナップ表示 --}}
    <div class="tutorial-track" id="tutorial-track">
        @foreach($slides as $i => $slide)
            @php $isLast = $i === count($slides) - 1; @endphp
            <section class="tutorial-slide" aria-label="スライド {{ $i + 1 }} / {{ count($slides) }}">
                <div class="tutorial-slide__icon" aria-hidden="true">
                    <i class="fas {{ $slide['icon'] }}"></i>
                </div>
                <h1 class="tutorial-slide__title">{{ $slide['title'] }}</h1>
                <p class="tutorial-slide__body">{!! nl2br(e($slide['body'])) !!}</p>

                @if($isLast)
                    <a href="{{ $startUrl }}" class="tutorial-cta" data-form-guard-bypass>
                        <i class="fas fa-play"></i> はじめる
                    </a>
                @endif
            </section>
        @endforeach
    </div>

    {{-- ドット + Next --}}
    <div class="tutorial-controls">
        <div class="tutorial-dots" id="tutorial-dots" aria-hidden="true">
            @foreach($slides as $i => $slide)
                <span class="tutorial-dot {{ $i === 0 ? 'is-active' : '' }}" data-tutorial-dot="{{ $i }}"></span>
            @endforeach
        </div>
        <button type="button" id="tutorial-next" class="tutorial-next" aria-label="次へ">
            次へ <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>

<style>
/* ==========================================================================
   Tutorial — 新規登録直後のかんたんチュートリアル
   ライトテーマ / 全画面 / 横スクロールスナップ
   ========================================================================== */
body.page-tutorial {
    overflow: hidden;
    background: linear-gradient(160deg, #f8f5ff 0%, #eee6fb 100%);
}
body.page-tutorial #global-header,
body.page-tutorial nav[data-bottom-nav],
body.page-tutorial #side-menu,
body.page-tutorial .header-right,
body.page-tutorial #character-guide {
    display: none !important;
}
body.page-tutorial main#main-content {
    height: 100vh !important;
    height: 100dvh !important;
    padding: 0 !important;
    min-height: 0 !important;
    overflow: hidden !important;
}
body.page-tutorial .content-wrapper {
    padding: 0 !important;
    max-width: 100% !important;
    background: transparent !important;
    box-shadow: none !important;
    height: 100%;
}

.tutorial-shell {
    position: relative;
    width: 100%;
    height: 100dvh;
    display: flex;
    flex-direction: column;
    color: #1e1a30;
}

/* Skip リンク（右上・控えめ） */
.tutorial-skip {
    position: absolute;
    top: max(14px, env(safe-area-inset-top));
    right: 16px;
    z-index: 10;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(124, 58, 237, 0.20);
    color: #6d28d9;
    font-size: 0.80rem;
    font-weight: 700;
    text-decoration: none;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    transition: background 0.15s ease;
}
.tutorial-skip:hover { background: #ffffff; }

/* スライドトラック：横スクロールスナップ */
.tutorial-track {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.tutorial-track::-webkit-scrollbar { display: none; }

.tutorial-slide {
    flex: 0 0 100%;
    width: 100%;
    height: 100%;
    scroll-snap-align: start;
    scroll-snap-stop: always;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 32px 100px;
    box-sizing: border-box;
    text-align: center;
}
.tutorial-slide__icon {
    width: 108px;
    height: 108px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(145deg, #a78bfa, #7c3aed);
    color: #ffffff;
    font-size: 2.8rem;
    box-shadow:
        0 12px 28px rgba(124, 58, 237, 0.30),
        inset 0 2px 4px rgba(255, 255, 255, 0.30),
        inset 0 -2px 4px rgba(0, 0, 0, 0.10);
    margin-bottom: 28px;
    animation: tutorial-icon-in 0.55s cubic-bezier(0.22, 0.8, 0.34, 1) both;
}
@keyframes tutorial-icon-in {
    from { opacity: 0; transform: scale(0.6) translateY(20px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.tutorial-slide__title {
    margin: 0 0 14px;
    font-size: 1.55rem;
    font-weight: 800;
    color: #1e1a30;
    letter-spacing: -0.01em;
    line-height: 1.35;
}
.tutorial-slide__body {
    margin: 0;
    max-width: 320px;
    font-size: 0.94rem;
    line-height: 1.85;
    color: #4a4560;
    font-weight: 500;
}

/* 最終スライドの「はじめる」CTA */
.tutorial-cta {
    margin-top: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 54px;
    padding: 14px 36px;
    border-radius: 999px;
    background: linear-gradient(135deg, #a78bfa, #7c3aed);
    color: #ffffff;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-decoration: none;
    box-shadow:
        0 8px 20px rgba(124, 58, 237, 0.35),
        inset 0 1px 0 rgba(255, 255, 255, 0.25);
    transition: transform 0.12s ease;
}
.tutorial-cta:active { transform: scale(0.97); }
.tutorial-cta i { font-size: 0.85rem; }

/* コントロール群（下部固定・ドット + Next） */
.tutorial-controls {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 20px calc(20px + env(safe-area-inset-bottom, 0px));
    background: linear-gradient(to top, rgba(238, 230, 251, 1) 40%, rgba(238, 230, 251, 0));
}

.tutorial-dots {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.tutorial-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(124, 58, 237, 0.22);
    transition: background 0.2s ease, transform 0.2s ease, width 0.2s ease;
}
.tutorial-dot.is-active {
    width: 26px;
    border-radius: 999px;
    background: #7c3aed;
}

.tutorial-next {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 42px;
    padding: 8px 18px;
    border-radius: 999px;
    background: #ffffff;
    border: 1px solid rgba(124, 58, 237, 0.30);
    color: #6d28d9;
    font-size: 0.90rem;
    font-weight: 800;
    cursor: pointer;
    transition: background 0.12s ease, transform 0.10s ease;
}
.tutorial-next:hover {
    background: rgba(124, 58, 237, 0.06);
}
.tutorial-next:active { transform: scale(0.97); }
.tutorial-next[hidden] { display: none !important; }
.tutorial-next i { font-size: 0.78rem; }
</style>

<script>
(function () {
    'use strict';
    var track = document.getElementById('tutorial-track');
    var nextBtn = document.getElementById('tutorial-next');
    var dots = document.querySelectorAll('[data-tutorial-dot]');
    if (!track || !nextBtn) return;

    var total = dots.length;
    var current = 0;

    function scrollToSlide(i) {
        if (i < 0 || i >= total) return;
        var slide = track.children[i];
        if (!slide) return;
        track.scrollTo({ left: slide.offsetLeft, behavior: 'smooth' });
    }

    function setActive(i) {
        current = Math.max(0, Math.min(total - 1, i));
        dots.forEach(function (d, idx) {
            d.classList.toggle('is-active', idx === current);
        });
        // 最終スライドでは Next を隠す（CTA が代わりに出る）
        nextBtn.hidden = (current === total - 1);
    }

    // スワイプ → dot 同期
    var scrollTimer = null;
    track.addEventListener('scroll', function () {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function () {
            var i = Math.round(track.scrollLeft / Math.max(1, track.clientWidth));
            setActive(i);
        }, 60);
    }, { passive: true });

    // Next
    nextBtn.addEventListener('click', function () {
        if (current < total - 1) scrollToSlide(current + 1);
    });

    // ドット直接クリック
    dots.forEach(function (d, idx) {
        d.style.cursor = 'pointer';
        d.addEventListener('click', function () { scrollToSlide(idx); });
    });

    setActive(0);
})();
</script>
@endsection
