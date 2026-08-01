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
    // ------------------------------------------------------------------
    // 2枚以上とばない設計（2026-08-01 修正）:
    //   1. slidesPerGroup: 1 を明示（Swiper 既定は 1 だが安全策として固定）
    //   2. preventInteractionOnTransition: true（アニメ中の新規タッチを無効化）
    //   3. mousewheel.thresholdTime: 500ms でトラックパッド慣性を抑制
    //   4. transitionStart で allowTouchMove を false に、End で true に戻す
    //      （preventInteractionOnTransition が効かない環境向けの二重ガード）
    //   5. longSwipesRatio を 0.25→0.35 / longSwipesMs 260→400 に緩和して
    //      「振り抜くまで確定しない」感を与える
    // ------------------------------------------------------------------
    const mainSwiper = new Swiper('.main-swiper', {
        direction: 'vertical',
        slidesPerView: 1,
        slidesPerGroup: 1,  // 1回のジェスチャで必ず 1 スライドだけ移動
        centeredSlides: true,
        // DISCOVERY 仕様：シームレスな無限ループ（末尾→先頭、先頭→末尾）
        loop: slideCount >= 2,
        rewind: false,
        // 340ms + CSS 側で cubic-bezier(0.22, 0.8, 0.34, 1) の easing
        speed: 340,
        // トランジション中は新しい touch/click を弾く（Swiper 8+）
        preventInteractionOnTransition: true,
        mousewheel: {
            enabled: true,
            sensitivity: 0.5,
            // 24 → 40：トラックパッドの細かい delta を1つずつ拾わない
            thresholdDelta: 40,
            // 500ms 間は同方向の追加 wheel イベントを無視（Swiper の慣性ガード）
            thresholdTime: 500,
            forceToAxis: true,
            releaseOnEdges: true,
        },
        touchRatio: 1,
        // タップ〜微動での誤反応対策：明確に引っ張った時のみスライド
        touchAngle: 26,
        threshold: 22,
        shortSwipes: false,
        longSwipes: true,
        // 0.25 → 0.35：3割以上ドラッグしないと確定させない（誤操作抑制）
        longSwipesRatio: 0.35,
        // 260 → 400：フリックのタイムウィンドウを広げてワンアクション感を出す
        longSwipesMs: 400,
        followFinger: true,
        resistance: true,
        // 0.5 → 0.72：端でのゴム感を強化（Photo swiper と統一）
        resistanceRatio: 0.72,
        touchStartPreventDefault: false,
        grabCursor: true,
        preventClicks: true,
        preventClicksPropagation: true,
        keyboard: {
            enabled: true,
            onlyInViewport: true
        },
        passiveListeners: true,
        observer: false,
        observeParents: false,
        on: {
            init: function () {
                var self = this;
                requestAnimationFrame(function () {
                    self.update();
                    // 初回表示でも文字がフワッと立ち上がるように
                    refreshActiveCard(self, 'card');
                });
            },
            // トランジション開始でタッチを封鎖、終了で解禁（二重ガード）
            slideChangeTransitionStart: function () {
                this.allowTouchMove = false;
            },
            slideChangeTransitionEnd: function () {
                refreshActiveCard(this, 'card');
                // 完了後にわずかな余韻を置いてから解禁（連続フリックの防止）
                var self = this;
                setTimeout(function () { self.allowTouchMove = true; }, 60);
            }
        }
    });

    // 各カード内の左右スワイプ（同一アカウントの複数写真）
    // ========================================================================
    // 写真セグメントバー（ストーリー型）
    // ドット式は廃止：写真枚数ぶんの等分バーで「今どこ/全何枚」が一目でわかる。
    // タップでその写真へジャンプ。
    // ========================================================================
    function buildPhotoSegBar(paginationEl, swiper, count) {
        // 旧 bullet 系のクラスが残っていると Swiper CDN CSS / home.css 内の
        // .swiper-pagination-bullets 系ルールに hit してセグメントが太くなる。
        // 完全に切り離すため一括で剥がす。
        paginationEl.classList.remove('swiper-pagination', 'swiper-pagination-bullets', 'swiper-pagination-horizontal');
        paginationEl.classList.add('photo-seg-bar');
        paginationEl.innerHTML = '';

        const segs = [];
        for (let i = 0; i < count; i++) {
            const seg = document.createElement('button');
            seg.type = 'button';
            seg.className = 'photo-seg stop-propagation';
            seg.setAttribute('aria-label', (i + 1) + '枚目の写真へ');
            seg.addEventListener('click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                swiper.slideTo(i);
            });
            paginationEl.appendChild(seg);
            segs.push(seg);
        }

        function sync() {
            const active = swiper.activeIndex || 0;
            segs.forEach(function (seg, idx) {
                seg.classList.toggle('is-active', idx === active);
                seg.classList.toggle('is-passed', idx < active);
            });
        }
        swiper.on('slideChange', sync);
        sync();
    }

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
            // 水平ジェスチャー判定：厳しすぎると横スワイプに気付かない
            touchAngle: 20,
            threshold: 10,
            // ぬるぬる感：スナップは長めに、途中で切替させない
            speed: 380,
            // creative は撤回：標準 slide + spaceBetween + CSS filter で軽量に奥行き感を出す
            effect: 'slide',
            spaceBetween: 8,
            resistance: true,
            // 端の抵抗を強めてゴム感を出す（半分より少し重い）
            resistanceRatio: 0.72,
            longSwipes: true,
            // 「最後までスワイプしないと切り替わらない」ぬるぬる感：
            //   0.20 → 0.55（半分より少し先まで指を運ばないと commit しない）
            longSwipesRatio: 0.55,
            // 早すぎるフリックで即座に切り替わらないよう猶予を延ばす
            longSwipesMs: 400,
            // shortSwipes（速度ベースの即時切替）は完全に無効化
            shortSwipes: false,
            // フォロー中の指離しで、指位置に対して滑らかに戻る／進む
            followFinger: true,
            watchOverflow: false,
            preventClicks: true,
            preventClicksPropagation: true,
            touchMoveStopPropagation: false,
            slideToClickedSlide: false,
            centeredSlides: false,
            passiveListeners: true,
            observer: false,
            observeParents: false,
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
        const swiper = new Swiper(el, options);
        photoSwipers.push(swiper);

        // Swiper 標準の bullet はやめ、ゴム風粘性のカスタムページャーを敷く
        if (paginationEl) {
            buildPhotoSegBar(paginationEl, swiper, photoSlideCount);
            // 配置は「トークする」ボタンの直下（カスタムページャーなので swiper 外でも動作する）
            var cardEl = el.closest('.cast-card');
            var talkCta = cardEl && cardEl.querySelector('.swipe-talk-cta');
            if (talkCta) {
                talkCta.insertAdjacentElement('afterend', paginationEl);
            }
        }
    });

    // ============================================================
    // トークCTA：capture 段階で最優先に処理する（保険）。
    // Swiper の preventClicks が <a> の既定遷移を殺すケースでも
    // 確実にトークルームへ遷移させる（loop 複製スライドにも効く document 委譲）
    // ============================================================
    document.addEventListener('click', function (e) {
        var cta = e.target.closest('.swipe-talk-cta');
        if (!cta) return;
        e.preventDefault();
        e.stopPropagation();
        var href = cta.getAttribute('href');
        if (href) window.location.href = href;
    }, true);

    // ============================================================
    // プロフィール遷移：キャストカード・求人カード共通で
    // 「画像エリア（.home-photo-wrap）のタップのみ」をトリガーにする。
    // ・カード全体ハンドラは廃止（下部のボタン類が誤遷移する根本原因だった）
    // ・document 委譲なので Swiper loop の複製スライドにも自動で効く
    // ・URL はキャストカード = wrap の data-detail-url / 求人カード = カードの data-detail-url
    // ============================================================
    document.addEventListener('click', function (e) {
        // ボタン・リンク・ページネーションはそれぞれの本来動作を優先
        if (e.target.closest('a[href]')) return;
        if (e.target.closest('button')) return;
        if (e.target.closest('.stop-propagation')) return;
        if (e.target.closest('.photo-pagination')) return;
        var wrap = e.target.closest('.home-photo-wrap');
        if (!wrap) return;
        if (isPhotoSwiping) return;
        var recruitCard = wrap.closest('.cast-card--recruit');
        var url = wrap.getAttribute('data-detail-url')
            || (recruitCard ? recruitCard.getAttribute('data-detail-url') : null);
        if (url) window.location.href = url;
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
            // KEEP は favorite-quick.js（data-fav-toggle）に一本化。
            // ここでは何もしない（document への委譲ハンドラが処理する）。
            if (this.hasAttribute('data-fav-toggle')) {
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

                    this.style.transform = 'scale(1.2)';
                    setTimeout(() => this.style.transform = 'scale(1)', 200);
                })
                .catch((res) => {
                    // 失敗を握りつぶさず、原因別にフィードバックする
                    if (res && res.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    if (res && res.status === 419) {
                        (window.appToast || window.alert)('セッションの有効期限が切れました。再読み込みします…');
                        setTimeout(() => window.location.reload(), 900);
                        return;
                    }
                    (window.appToast || window.alert)('通信エラーで保存できませんでした。もう一度お試しください。', 'error');
                });
        });
    });
}