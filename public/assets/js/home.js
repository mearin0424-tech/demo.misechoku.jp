/**
 * Discovery Home Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. 写真の左右スワイプ初期化 (Nested Swiper)
    const photoSwipers = new Swiper('.photo-swiper', {
        direction: 'horizontal',
        nested: true,
        pagination: {
            el: '.photo-pagination',
            clickable: true
        },
        speed: 400,
        resistanceRatio: 0,
        // スワイプイベントが親に伝播しないよう設定
        touchStartPreventDefault: false,
    });

    // 2. メインの上下スワイプ初期化
    const mainSwiper = new Swiper('.main-swiper', {
        direction: 'vertical',
        slidesPerView: 1,
        mousewheel: true,
        speed: 600,
        spaceBetween: 0,
        loop: true,
        // 画面リサイズに対応
        on: {
            init: function () {
                this.update();
            }
        }
    });

    // 3. クリックイベントの伝播停止 (ボタン類)
    const stopProps = document.querySelectorAll('.stop-propagation');
    stopProps.forEach(el => {
        el.addEventListener('touchstart', (e) => e.stopPropagation(), { passive: true });
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