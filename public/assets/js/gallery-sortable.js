/**
 * Image Library: ドラッグ＆ドロップ（スマホは長押し）で並び替え
 * Sortable.js の読み込み後にこのスクリプトを実行すること
 */
(function () {
    'use strict';

    function getGalleryList() {
        return document.getElementById('gallery-list');
    }

    function refreshGalleryMainState(list) {
        if (!list) return;

        var items = list.querySelectorAll('.gallery-grid-item');
        var firstImageSlot = null;

        items.forEach(function (li, i) {
            li.setAttribute('data-slot-index', i);
            var slot = li.querySelector('.photo-slot');
            if (!slot) return;

            var badge = slot.querySelector('.photo-slot-badge');
            if (badge) badge.remove();

            if (!firstImageSlot && slot.classList.contains('has-img')) {
                firstImageSlot = slot;
            }
        });

        if (firstImageSlot) {
            var mainSpan = document.createElement('span');
            mainSpan.className = 'photo-slot-badge';
            mainSpan.textContent = 'MAIN';
            firstImageSlot.appendChild(mainSpan);
        }

        var mainIcon = document.getElementById('main-icon-display');
        if (mainIcon) {
            var emptyImageUrl = list.getAttribute('data-empty-image-url') || '';
            var url = firstImageSlot ? firstImageSlot.getAttribute('data-image-url') : '';
            mainIcon.src = url || emptyImageUrl;
        }
    }

    function collectOrderedImageIds(list) {
        if (!list) return [];

        return Array.from(list.querySelectorAll('.gallery-grid-item .photo-slot.has-img'))
            .map(function (slot) {
                return slot.getAttribute('data-image-id');
            })
            .filter(function (id) {
                return !!id && !String(id).startsWith('local-');
            })
            .map(function (id) {
                return parseInt(id, 10);
            })
            .filter(function (id) {
                return !Number.isNaN(id) && id > 0;
            });
    }

    function persistGalleryOrder(list) {
        if (!list) return Promise.resolve();

        var saveUrl = list.getAttribute('data-sort-save-url');
        if (!saveUrl) return Promise.resolve();

        var token = document.querySelector('meta[name="csrf-token"]');
        var imageIds = collectOrderedImageIds(list);

        return fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
            },
            body: JSON.stringify({ images: imageIds })
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Failed to save gallery order');
            }
            return response.json();
        }).then(function (result) {
            if (!result || result.success !== true) {
                throw new Error((result && result.message) || 'Failed to save gallery order');
            }
            return result;
        }).catch(function (error) {
            console.error(error);
            alert('画像の並び順保存に失敗しました。');
        });
    }

    function initGallerySortable() {
        if (typeof Sortable === 'undefined') return;
        var list = getGalleryList();
        if (!list) return;

        refreshGalleryMainState(list);

        Sortable.create(list, {
            animation: 200,
            delayOnTouchOnly: true,
            delay: 400,
            draggable: '.gallery-grid-item',
            ghostClass: 'gallery-sortable-ghost',
            chosenClass: 'gallery-sortable-chosen',
            dragClass: 'gallery-sortable-drag',
            onEnd: function () {
                refreshGalleryMainState(list);
                persistGalleryOrder(list);
            }
        });
    }

    window.refreshGalleryMainState = refreshGalleryMainState;
    window.persistGalleryOrder = persistGalleryOrder;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGallerySortable);
    } else {
        initGallerySortable();
    }
})();
