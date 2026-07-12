/* motion.js — 全画面共通のモーション基盤（Step1）
 * - 画像フェードイン（Instagram 風）
 * - スクロールリビール（一覧・カードが下から順に現れる）
 * すべて transform/opacity のみ。prefers-reduced-motion では何もしない。
 */
(function () {
    'use strict';

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    /* ---------------- 画像フェードイン ----------------
       すでに読み込み済み（キャッシュ命中）の画像はそのまま表示し、
       これから読み込まれる画像だけをフェードインさせる。 */
    function initImgFade(root) {
        (root || document).querySelectorAll('img:not(.no-fade)').forEach(function (img) {
            if (img.dataset.fadeInit) return;
            img.dataset.fadeInit = '1';
            if (img.complete && img.naturalWidth > 0) return;
            img.classList.add('img-fade');
            var done = function () { img.classList.add('is-loaded'); };
            img.addEventListener('load', done, { once: true });
            img.addEventListener('error', done, { once: true });
        });
    }

    /* ---------------- スクロールリビール ----------------
       リスト行・カード系だけを対象に、6件周期の軽いスタッガーで出現。 */
    var REVEAL_SELECTOR = [
        '.tl-row',
        '.case-card',
        '.mypage-tile',
        '.mypage-mini-row',
        '.staff-card',
        '.notif-popup__item',
        '.gallery-grid-item',
        '.profile-gallery-item',
        '.support-htu-feature-card',
        '.support-htu-step',
        '.support-htu-faq-item',
    ].join(', ');

    function initReveal(root) {
        if (!('IntersectionObserver' in window)) return;
        var els = (root || document).querySelectorAll(REVEAL_SELECTOR);
        if (!els.length) return;

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                en.target.classList.add('reveal-in');
                io.unobserve(en.target);
            });
        }, { rootMargin: '0px 0px -8% 0px' });

        var i = 0;
        els.forEach(function (el) {
            if (el.dataset.revealInit) return;
            el.dataset.revealInit = '1';
            el.classList.add('reveal-init');
            el.style.transitionDelay = ((i++ % 6) * 40) + 'ms';
            io.observe(el);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initImgFade();
        initReveal();
    });
})();
