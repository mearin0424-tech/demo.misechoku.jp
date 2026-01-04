/**
 * Discovery Home Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const slides = document.querySelectorAll('.main-swiper .swiper-slide');
    const slideCount = slides.length;
    
    // 写真の左右スワイプ (Nested Swiper)
    const photoSwipers = new Swiper('.photo-swiper', {
        direction: 'horizontal',
        nested: true,
        pagination: {
            el: '.photo-pagination',
            clickable: true
        },
        preventClicks: false,
        preventClicksPropagation: false,
        speed: 400,
    });

    // メインの上下スワイプ
    const mainSwiper = new Swiper('.main-swiper', {
        direction: 'vertical',
        slidesPerView: 1,
        centeredSlides: true,
        
        // 枚数が1枚より多ければループを有効にする（エラー回避）
        loop: slideCount > 1, 
        
        speed: 500,
        mousewheel: true,
        grabCursor: true,
        effect: 'slide', 
        observer: true,
        observeParents: true,
    });

    // 3. クリックイベントの伝播停止 (ボタン類)
    const actionOverlays = document.querySelectorAll('.card-actions-overlay');
    actionOverlays.forEach(el => {
    el.addEventListener('touchstart', (e) => e.stopPropagation(), { passive: true });
    el.addEventListener('mousedown', (e) => e.stopPropagation());
    el.addEventListener('click', (e) => e.stopPropagation()); // ボタンクリック時に詳細へ飛ばないように
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