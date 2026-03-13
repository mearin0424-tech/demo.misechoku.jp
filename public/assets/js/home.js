/**
 * Discovery Home Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const mainSwiperEl = document.querySelector('.main-swiper');
    const mainWrapper = mainSwiperEl ? mainSwiperEl.querySelector(':scope > .swiper-wrapper') : null;
    const mainSlides = mainWrapper ? Array.from(mainWrapper.children).filter((el) => el.classList.contains('swiper-slide')) : [];
    const slideCount = mainSlides.length;
    let isPhotoSwiping = false;

    function releasePhotoSwipeLock() {
        isPhotoSwiping = false;
    }

    // メインの上下スワイプ（モダン・操作性重視）
    const mainSwiper = new Swiper('.main-swiper', {
        direction: 'vertical',
        slidesPerView: 1,
        centeredSlides: true,
        loop: slideCount >= 2,
        speed: 400,
        mousewheel: {
            enabled: true,
            sensitivity: 0.8,
            thresholdDelta: 20
        },
        touchRatio: 1,
        touchAngle: 25,
        threshold: 12,
        resistance: true,
        resistanceRatio: 0.82,
        touchStartPreventDefault: false,
        grabCursor: true,
        preventClicks: true,
        preventClicksPropagation: true,
        keyboard: {
            enabled: true,
            onlyInViewport: true
        },
        observer: true,
        observeParents: true,
        on: {
            init: function () {
                var self = this;
                setTimeout(function () { self.update(); }, 100);
            },
            slideChange: function () {
                var messages = [
                    "上下スワイプで次 / 前のアカウントに移動できるよ！\n左右スワイプでこの人の別の写真が見られるよ。",
                    "左右にスワイプしてこのキャストの他の写真をチェックしてみてね。\n上下スワイプで別のキャストに切り替わるよ。",
                    "気になる人がいたら右側のボタンから「いいね」「キープ」「メッセージ」を使ってみよう！"
                ];
                var realIndex = this.realIndex != null ? this.realIndex : this.activeIndex;
                var currentMsg = messages[realIndex % messages.length] || "素敵な出会いがありますように！";
                if (typeof window.updateCharacterMessage === 'function') {
                    window.updateCharacterMessage(currentMsg);
                }
            }
        }
    });

    // 各カード内の左右スワイプ（同一アカウントの複数写真）
    const photoSwipers = [];
    document.querySelectorAll('.photo-swiper').forEach((el) => {
        const paginationEl = el.querySelector('.photo-pagination');
        const photoSlideCount = el.querySelectorAll(':scope > .swiper-wrapper > .swiper-slide').length;
        const options = {
            direction: 'horizontal',
            slidesPerView: 1,
            loop: false,
            nested: true,
            allowTouchMove: photoSlideCount > 1,
            touchStartPreventDefault: false,
            touchReleaseOnEdges: true,
            touchAngle: 35,
            threshold: 4,
            speed: 280,
            resistance: true,
            resistanceRatio: 0.85,
            longSwipes: true,
            longSwipesRatio: 0.12,
            longSwipesMs: 180,
            shortSwipes: true,
            followFinger: true,
            watchOverflow: true,
            preventClicks: true,
            preventClicksPropagation: true,
            touchMoveStopPropagation: true,
            on: {
                touchStart: function () {
                    isPhotoSwiping = false;
                },
                touchMove: function () {
                    isPhotoSwiping = true;
                },
                transitionEnd: function () {
                    setTimeout(releasePhotoSwipeLock, 80);
                },
                touchEnd: function () {
                    setTimeout(releasePhotoSwipeLock, 80);
                },
                touchCancel: function () {
                    setTimeout(releasePhotoSwipeLock, 80);
                }
            }
        };
        if (paginationEl) {
            options.pagination = {
                el: paginationEl,
                clickable: true
            };
        }
        const swiper = new Swiper(el, options);
        photoSwipers.push(swiper);
    });

    // 写真エリアは「Swiperでスワイプ / clickで詳細遷移」
    document.querySelectorAll('.home-photo-wrap').forEach((wrap) => {
        const detailUrl = wrap.getAttribute('data-detail-url');
        if (!detailUrl) return;
        wrap.addEventListener('click', function () {
            if (isPhotoSwiping) return;
            window.location.href = detailUrl;
        });
    });

    // リサイズ・ビューポート変化時に Swiper を更新（モバイルのアドレスバー表示切替など）
    window.addEventListener('resize', function () {
        if (mainSwiper) mainSwiper.update();
    });
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', function () {
            if (mainSwiper) mainSwiper.update();
        });
    }

    // 3. クリックイベントの伝播停止 (ボタン類)
    document.querySelectorAll('.stop-propagation').forEach(el => {
        el.addEventListener('touchstart', (e) => e.stopPropagation(), {passive: true});
        el.addEventListener('mousedown', (e) => e.stopPropagation());
    });

    // 4. アクションボタンの簡易動作
    initActionButtons();

    // 5. キャラクターガイドの少しフェード
    setTimeout(() => {
        const guide = document.getElementById('discovery-guide');
        if (guide) {
            guide.style.transition = 'opacity 1s ease';
            guide.style.opacity = '0.5';
        }
    }, 8000);

    // 6. 初回・久しぶり用オンボーディング（ホームスワイプガイド）
    const onboardingOverlay = document.getElementById('home-swipe-onboarding');
    const ONBOARDING_KEY = 'home_swipe_onboarding_last_shown_at';
    const ONBOARDING_INTERVAL_DAYS = 90;

    function shouldShowOnboarding() {
        try {
            const raw = localStorage.getItem(ONBOARDING_KEY);
            if (!raw) return true; // 新アカウント（初回）
            const last = parseInt(raw, 10);
            if (!last) return true;
            const diffMs = Date.now() - last;
            const thresholdMs = ONBOARDING_INTERVAL_DAYS * 24 * 60 * 60 * 1000;
            return diffMs >= thresholdMs;
        } catch (e) {
            return true;
        }
    }

    function markOnboardingShown() {
        try {
            localStorage.setItem(ONBOARDING_KEY, String(Date.now()));
        } catch (e) {
            // ignore
        }
    }

    function showOnboarding() {
        if (!onboardingOverlay) return;
        onboardingOverlay.classList.add('is-active');
        onboardingOverlay.setAttribute('aria-hidden', 'false');
        window.forceCharacterGuideVisible = true;
        if (typeof window.updateCharacterMessage === 'function') {
            window.updateCharacterMessage(
                "上下スワイプで次 / 前のアカウントに移動できるよ！\n" +
                "左右スワイプで同じアカウントの別の写真を見られるよ。\n" +
                "気になる人がいたら右側のボタンからアクションしてみてね。"
            );
        }
    }

    function hideOnboarding() {
        if (!onboardingOverlay) return;
        onboardingOverlay.classList.remove('is-active');
        onboardingOverlay.setAttribute('aria-hidden', 'true');
        window.forceCharacterGuideVisible = false;
        markOnboardingShown();
    }

    if (onboardingOverlay && shouldShowOnboarding()) {
        showOnboarding();
        const closeHandler = (e) => {
            e.preventDefault();
            hideOnboarding();
        };
        onboardingOverlay.addEventListener('click', closeHandler, { once: true });
        onboardingOverlay.addEventListener('touchstart', closeHandler, { passive: true, once: true });
    }
});

/**
 * ボタンクリック時のエフェクト等
 */
function initActionButtons() {
    const buttons = document.querySelectorAll('.action-circle-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // ここにLike/Keepの非同期処理(Ajax)などを追加可能
            if (!this.classList.contains('message')) {
                e.preventDefault();
                this.style.transform = 'scale(1.2)';
                setTimeout(() => this.style.transform = 'scale(1)', 200);
            }
        });
    });
}