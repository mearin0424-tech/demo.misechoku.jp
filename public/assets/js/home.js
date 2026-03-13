/**
 * Discovery Home Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const slides = document.querySelectorAll('.main-swiper .swiper-slide');
    const slideCount = slides.length;
    let isPhotoSwiping = false;

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
        touchAngle: 45,
        threshold: 15,
        resistance: true,
        resistanceRatio: 0.7,
        touchStartPreventDefault: false,
        grabCursor: true,
        pagination: {
            el: '.home-swiper-pagination',
            clickable: true,
            dynamicBullets: slideCount > 5,
            dynamicMainBullets: 3
        },
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
        const options = {
            direction: 'horizontal',
            slidesPerView: 1,
            loop: false,
            nested: true,
            allowTouchMove: true,
            touchStartPreventDefault: false,
            touchReleaseOnEdges: true,
            touchAngle: 45,
            threshold: 6,
            on: {
                touchMove: function () {
                    isPhotoSwiping = true;
                },
                transitionEnd: function () {
                    setTimeout(function () {
                        isPhotoSwiping = false;
                    }, 120);
                },
                touchEnd: function () {
                    setTimeout(function () {
                        isPhotoSwiping = false;
                    }, 120);
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

    // 写真エリアは「スワイプ優先 / 軽いタップで詳細へ」
    document.querySelectorAll('.home-photo-wrap').forEach((wrap) => {
        const detailUrl = wrap.getAttribute('data-detail-url');
        if (!detailUrl) return;

        let startX = 0;
        let startY = 0;
        let moved = false;

        const handleStart = (event) => {
            const point = event.touches ? event.touches[0] : event;
            startX = point.clientX;
            startY = point.clientY;
            moved = false;
        };

        const handleMove = (event) => {
            const point = event.touches ? event.touches[0] : event;
            const deltaX = Math.abs(point.clientX - startX);
            const deltaY = Math.abs(point.clientY - startY);
            if (deltaX > 8 || deltaY > 8) {
                moved = true;
            }
        };

        const handleEnd = () => {
            if (!moved && !isPhotoSwiping) {
                window.location.href = detailUrl;
            }
        };

        wrap.addEventListener('touchstart', handleStart, { passive: true });
        wrap.addEventListener('touchmove', handleMove, { passive: true });
        wrap.addEventListener('touchend', handleEnd);
        wrap.addEventListener('mousedown', handleStart);
        wrap.addEventListener('mousemove', handleMove);
        wrap.addEventListener('mouseup', handleEnd);
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