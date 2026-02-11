/**
 * Discovery Home Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const slides = document.querySelectorAll('.main-swiper .swiper-slide');
    const slideCount = slides.length;

    // メインの上下スワイプ（先に初期化して loop で DOM を確定させる）
    const mainSwiper = new Swiper('.main-swiper', {
        direction: 'vertical',
        slidesPerView: 1,
        centeredSlides: true,
        
        // 【修正】Loop Warning 回避
        // Swiperの仕様上、loop:true には slidesPerView の2倍以上の枚数が必要です。
        // 枚数が少ない場合はループをオフにします。
        loop: slideCount >= 2, 
        speed: 500,
        mousewheel: true,
        threshold: 20,
        on: {
            slideChange: function () {
                const messages = [
                    "上下でキャストを変更できるよ！",
                    "このキャストの写真は左右にスワイプしてね。",
                    "気になる人がいたら右のボタンでアクションしよう！"
                ];
                const currentMsg = messages[this.activeIndex] || "素敵な出会いがありますように！";
                if (typeof window.updateCharacterMessage === 'function') {
                    window.updateCharacterMessage(currentMsg);
                }
            }
        }
    });

    // 写真の左右スワイプ（各 .photo-swiper を個別に初期化）
    document.querySelectorAll('.photo-swiper').forEach(function (el) {
        var paginationEl = el.querySelector('.photo-pagination');
        new Swiper(el, {
            direction: 'horizontal',
            nested: true,
            pagination: paginationEl ? {
                el: paginationEl,
                clickable: true
            } : false,
            resistanceRatio: 0
        });
    });

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