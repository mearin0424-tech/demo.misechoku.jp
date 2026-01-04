/**
 * Discovery Home Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    
    const slides = document.querySelectorAll('.main-swiper .swiper-slide');
    const slideCount = slides.length;
    
    // 写真の左右スワイプ (Nested Swiper)
    const photoSwipers = new Swiper('.photo-swiper', {
        direction: 'horizontal',
        nested: true, // これにより上下スワイプがメインに伝わる
        pagination: {
            el: '.photo-pagination',
            clickable: true
        },
        resistanceRatio: 0, // 端でのバウンスを抑制して縦スワイプへ移りやすくする
    });

    // メインの上下スワイプ
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
        threshold: 20, // 少しスワイプしないと反応しないようにして誤作動防止
    });

    // 3. クリックイベントの伝播停止 (ボタン類)
    document.querySelectorAll('.stop-propagation').forEach(el => {
        el.addEventListener('touchstart', (e) => e.stopPropagation());
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