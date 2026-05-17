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

    /**
     * カード本文の "下からふわっ" アニメーションを再トリガーする。
     * mode: 'card'  → 上下スワイプ（カード切替）。スタガーで全要素を立ち上げる。
     *       'photo' → 左右スワイプ（写真切替）。軽量バリアント。
     */
    function refreshCardContent(card, mode) {
        if (!card) return;
        var fullClass = 'is-content-fresh';
        var photoClass = 'is-photo-fresh';
        // どちらのアニメーションも一旦リセット
        card.classList.remove(fullClass, photoClass, 'is-flipping');
        // 強制リフロー → 再付与で animation を再生
        // eslint-disable-next-line no-unused-expressions
        void card.offsetWidth;
        if (mode === 'photo') {
            card.classList.add(photoClass);
        } else {
            card.classList.add(fullClass, 'is-flipping');
        }
    }

    function refreshActiveCard(swiperInstance, mode) {
        if (!swiperInstance) return;
        var slide = swiperInstance.slides && swiperInstance.slides[swiperInstance.activeIndex];
        var card = slide && (slide.classList.contains('cast-card') ? slide : slide.querySelector('.cast-card'));
        refreshCardContent(card, mode || 'card');
    }

    // メインの上下スワイプ（モダン・操作性重視）
    const mainSwiper = new Swiper('.main-swiper', {
        direction: 'vertical',
        slidesPerView: 1,
        centeredSlides: true,
        // DISCOVERY 仕様：シームレスな無限ループ（末尾→先頭、先頭→末尾）
        loop: slideCount >= 2,
        rewind: false,
        speed: 400,
        mousewheel: {
            enabled: true,
            sensitivity: 0.8,
            thresholdDelta: 20
        },
        touchRatio: 1,
        // ある程度斜めのジェスチャーも縦として拾う
        touchAngle: 35,
        threshold: 10,
        resistance: true,
        resistanceRatio: 0.7,
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
                setTimeout(function () {
                    self.update();
                    // 初回表示でも文字がフワッと立ち上がるように
                    refreshActiveCard(self, 'card');
                }, 100);
            },
            slideChangeTransitionStart: function () {
                // 上下スワイプの切替時：本文を一旦消してから新カードで再生
                refreshActiveCard(this, 'card');
            },
            slideChangeTransitionEnd: function () {
                // 完全到達後にもう一度ピン留めし、stagger を確実に再生
                refreshActiveCard(this, 'card');
            }
        }
    });

    // 各カード内の左右スワイプ（同一アカウントの複数写真）
    const photoSwipers = [];
    document.querySelectorAll('.photo-swiper').forEach((el) => {
        const paginationEl = el.querySelector('.photo-pagination');
        const photoSlideCount = el.querySelectorAll(':scope > .swiper-wrapper > .swiper-slide').length;

        // 1枚のみ：横Swiperを動かさない＝縦のメインSwiperと競合しない（キャスト側求人カードが軽いのと同じ挙動）
        if (photoSlideCount <= 1) {
            el.classList.add('photo-swiper--single');
            if (paginationEl) {
                paginationEl.style.display = 'none';
            }
            return;
        }

        const options = {
            direction: 'horizontal',
            slidesPerView: 1,
            slidesPerGroup: 1,
            spaceBetween: 0,
            loop: false,
            nested: true,
            allowTouchMove: true,
            touchStartPreventDefault: false,
            touchReleaseOnEdges: true,
            // より水平に近いジェスチャーのみ横スワイプ扱いにし、斜め〜縦は親（上下スワイプ）へ譲る
            touchAngle: 18,
            threshold: 12,
            speed: 300,
            resistance: true,
            resistanceRatio: 0.6,
            longSwipes: true,
            longSwipesRatio: 0.15,
            longSwipesMs: 150,
            shortSwipes: true,
            followFinger: true,
            watchOverflow: false,
            preventClicks: true,
            preventClicksPropagation: true,
            // 縦方向の動きは親のメインSwiperに伝搬させる
            touchMoveStopPropagation: false,
            slideToClickedSlide: false,
            centeredSlides: false,
            observer: true,
            observeParents: true,
            on: {
                touchStart: function () {
                    isPhotoSwiping = false;
                },
                touchMove: function () {
                    isPhotoSwiping = true;
                },
                slideChange: function () {
                    // 写真切替で本文をフワッと再表示
                    var card = el.closest('.cast-card');
                    refreshCardContent(card, 'photo');
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

    // 写真エリアは「Swiperでスワイプ / clickで詳細遷移」（求人スワイプカードはカード全体で求人へ）
    document.querySelectorAll('.home-photo-wrap').forEach((wrap) => {
        const detailUrl = wrap.getAttribute('data-detail-url');
        if (!detailUrl) return;
        if (wrap.closest('.cast-card--recruit')) return;
        wrap.addEventListener('click', function () {
            if (isPhotoSwiping) return;
            window.location.href = detailUrl;
        });
    });

    document.querySelectorAll('.cast-card--recruit[data-detail-url]').forEach(function (card) {
        var recruitUrl = card.getAttribute('data-detail-url');
        if (!recruitUrl) return;
        card.addEventListener('click', function (e) {
            if (e.target.closest('.stop-propagation')) return;
            if (e.target.closest('.photo-pagination')) return;
            if (isPhotoSwiping) return;
            window.location.href = recruitUrl;
        });
    });

    // リサイズ・ビューポート変化時に Swiper を更新（モバイルのアドレスバー表示切替など）
    function updateAllPhotoSwipers() {
        photoSwipers.forEach(function (ps) {
            if (ps && typeof ps.update === 'function') ps.update();
        });
    }
    window.addEventListener('resize', function () {
        if (mainSwiper) mainSwiper.update();
        updateAllPhotoSwipers();
    });
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', function () {
            if (mainSwiper) mainSwiper.update();
            updateAllPhotoSwipers();
        });
    }

    // 3. クリックイベントの伝播停止 (ボタン類)
    document.querySelectorAll('.stop-propagation').forEach(el => {
        el.addEventListener('touchstart', (e) => e.stopPropagation(), {passive: true});
        el.addEventListener('mousedown', (e) => e.stopPropagation());
    });

    // 4. アクションボタンの簡易動作（Ajax連携）
    initActionButtons();

    // 5. 初回・久しぶり用オンボーディング（ホームスワイプガイド・オコジョは表示しない）
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
    if (!buttons.length) return;

    const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenEl ? csrfTokenEl.getAttribute('content') : null;

    buttons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (this.classList.contains('message')) {
                return;
            }

            e.preventDefault();

            const action = this.dataset.action;
            const itemId = this.dataset.itemId;
            const itemType = this.dataset.itemType;

            if (!action || !itemId || !itemType) {
                // 単純なエフェクトのみ
                this.style.transform = 'scale(1.2)';
                setTimeout(() => this.style.transform = 'scale(1)', 200);
                return;
            }

            const payload = {
                action: action,
                item_id: itemId,
                item_type: itemType,
            };

            fetch('/api/favorites/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin',
            })
                .then(res => res.ok ? res.json() : Promise.reject(res))
                .then(data => {
                    if (!data || !data.ok) return;

                    if (typeof data.is_active !== 'undefined') {
                        this.classList.toggle('is-active', !!data.is_active);
                    }

                    if (data.action === 'like' && typeof data.like_count === 'number') {
                        const countEl = this.querySelector('.action-btn-count');
                        if (countEl) {
                            countEl.textContent = data.like_count;
                        }
                    }

                    this.style.transform = 'scale(1.2)';
                    setTimeout(() => this.style.transform = 'scale(1)', 200);
                })
                .catch(() => {
                    // エラー時もエフェクトは動かす（UX優先、実際の保存はサーバーログで確認）
                    this.style.transform = 'scale(1.2)';
                    setTimeout(() => this.style.transform = 'scale(1)', 200);
                });
        });
    });
}