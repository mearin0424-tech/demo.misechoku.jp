/**
 * Discovery Home Logic
 */

// #region agent log
function _debugHomeDimensions() {
    var hs = document.getElementById('home-screen');
    var swiper = document.querySelector('.main-swiper');
    var wrapper = document.querySelector('.main-swiper .swiper-wrapper');
    var slide0 = document.querySelector('.main-swiper .swiper-slide');
    var wrap0 = document.querySelector('.home-photo-wrap');
    var img0 = document.querySelector('.home-photo');
    var viewport = { w: window.innerWidth, h: window.innerHeight };
    var rect = function(el) { if (!el) return null; var r = el.getBoundingClientRect(); return { width: Math.round(r.width), height: Math.round(r.height), top: Math.round(r.top) }; };
    var imgData = img0 ? { src: img0.src || '', complete: img0.complete, naturalWidth: img0.naturalWidth || 0, naturalHeight: img0.naturalHeight || 0 } : null;
    var payload = { sessionId: 'b93710', runId: 'home-swipe', hypothesisId: 'H1', location: 'home.js:_debugHomeDimensions', message: 'HOME dimensions 350x620', data: { viewport: viewport, homeScreen: rect(hs), mainSwiper: rect(swiper), slide0: rect(slide0), photoWrap0: rect(wrap0), photo0: rect(img0), imgLoad: imgData }, timestamp: Date.now() };
    fetch('http://127.0.0.1:7355/ingest/ef789d77-385e-4ada-bc45-ff6baa5c7d85', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': 'b93710' }, body: JSON.stringify(payload) }).catch(function() {});
}
// #endregion

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
            init: function() {
                requestAnimationFrame(function() { requestAnimationFrame(function() { _debugHomeDimensions(); }); });
            },
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

    // 画像読み込み結果ログ（H2: 画像失敗で黒）
    var firstImg = document.querySelector('.home-photo');
    if (firstImg) {
        firstImg.addEventListener('load', function() {
            fetch('http://127.0.0.1:7355/ingest/ef789d77-385e-4ada-bc45-ff6baa5c7d85', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': 'b93710' }, body: JSON.stringify({ sessionId: 'b93710', runId: 'home-swipe', hypothesisId: 'H2', location: 'home.js:img.onload', message: 'first image loaded', data: { src: this.src, naturalWidth: this.naturalWidth, naturalHeight: this.naturalHeight }, timestamp: Date.now() }) }).catch(function() {});
        });
        firstImg.addEventListener('error', function() {
            fetch('http://127.0.0.1:7355/ingest/ef789d77-385e-4ada-bc45-ff6baa5c7d85', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': 'b93710' }, body: JSON.stringify({ sessionId: 'b93710', runId: 'home-swipe', hypothesisId: 'H2', location: 'home.js:img.onerror', message: 'first image failed', data: { src: this.src }, timestamp: Date.now() }) }).catch(function() {});
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