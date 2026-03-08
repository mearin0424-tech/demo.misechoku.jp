/**
 * Discovery Home Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const slides = document.querySelectorAll('.main-swiper .swiper-slide');
    const slideCount = slides.length;

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
                    "上下でキャストを変更できるよ！",
                    "このキャストの写真は左右にスワイプしてね。",
                    "気になる人がいたら右のボタンでアクションしよう！"
                ];
                var realIndex = this.realIndex != null ? this.realIndex : this.activeIndex;
                var currentMsg = messages[realIndex % messages.length] || "素敵な出会いがありますように！";
                if (typeof window.updateCharacterMessage === 'function') {
                    window.updateCharacterMessage(currentMsg);
                }
            }
        }
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

    // 5. ガイドの表示制御（数秒後に消すなど）
    setTimeout(() => {
        const guide = document.getElementById('discovery-guide');
        if (guide) {
            guide.style.transition = 'opacity 1s ease';
            guide.style.opacity = '0.5';
        }
    }, 8000);
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