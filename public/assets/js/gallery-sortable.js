/**
 * Image Library: ドラッグ＆ドロップ（スマホは長押し）で並び替え
 * Sortable.js の読み込み後にこのスクリプトを実行すること
 */
(function () {
    'use strict';

    function initGallerySortable() {
        if (typeof Sortable === 'undefined') return;
        var list = document.getElementById('gallery-list');
        if (!list) return;

        Sortable.create(list, {
            animation: 200,
            delayOnTouchOnly: true,
            delay: 400,
            draggable: '.gallery-grid-item',
            ghostClass: 'gallery-sortable-ghost',
            chosenClass: 'gallery-sortable-chosen',
            dragClass: 'gallery-sortable-drag',
            onEnd: function () {
                var items = list.querySelectorAll('.gallery-grid-item');
                items.forEach(function (li, i) {
                    li.setAttribute('data-slot-index', i);
                    var slot = li.querySelector('.photo-slot');
                    if (!slot) return;
                    var badge = slot.querySelector('.photo-slot-badge');
                    if (i === 0) {
                        if (!badge) {
                            var mainSpan = document.createElement('span');
                            mainSpan.className = 'photo-slot-badge';
                            mainSpan.textContent = 'MAIN';
                            slot.appendChild(mainSpan);
                        }
                    } else {
                        if (badge) badge.remove();
                    }
                });
                var mainIcon = document.getElementById('main-icon-display');
                if (mainIcon) {
                    var firstSlot = list.querySelector('.gallery-grid-item .photo-slot.has-img');
                    if (firstSlot) {
                        var url = firstSlot.getAttribute('data-image-url');
                        if (url) mainIcon.src = url;
                    }
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGallerySortable);
    } else {
        initGallerySortable();
    }
})();
