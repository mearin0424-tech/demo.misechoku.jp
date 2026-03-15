/**
 * Mypage ギャラリー: 画像ありスロットタップ→拡大表示・削除、画像なしスロットタップ→アップロード
 * グローバルに MYPAGE_GALLERY_CONFIG が定義されている必要あり。
 */
(function() {
    var config = window.MYPAGE_GALLERY_CONFIG;
    if (!config || !config.uploadUrl) return;

    var _galleryPreviewImageId = null;
    var _galleryPreviewLi = null;
    var _galleryUploadSlotIndex = null;
    var csrfToken = config.csrfToken || '';
    var uploadUrl = config.uploadUrl;
    var deleteUrlTemplate = config.deleteUrlTemplate || '';

    function closeGalleryPreview() {
        var modal = document.getElementById('image-preview-modal');
        if (modal) modal.style.display = 'none';
        var deleteBtn = document.getElementById('gallery-preview-delete-btn');
        if (deleteBtn) deleteBtn.style.display = '';
        _galleryPreviewImageId = null;
        _galleryPreviewLi = null;
    }

    function run() {
        var uploadInput = document.getElementById('gallery-upload');
        if (!uploadInput) return;

        var modal = document.getElementById('image-preview-modal');
        var modalImg = document.getElementById('modal-img');
        var deleteBtn = document.getElementById('gallery-preview-delete-btn');
        var closeBtn = document.getElementById('gallery-preview-close-btn');

        document.addEventListener('click', function(ev) {
            var slot = ev.target.closest('.photo-slot');
            if (!slot) return;
            var list = document.getElementById('gallery-list');
            if (!list || !list.contains(slot)) return;
            ev.preventDefault();
            ev.stopPropagation();
            var li = slot.closest('.gallery-grid-item');
            var slotIndex = parseInt(li.getAttribute('data-slot-index'), 10);
            if (isNaN(slotIndex)) slotIndex = 0;
            var imageUrl = (slot.getAttribute('data-image-url') || '').trim();
            var imageId = slot.getAttribute('data-image-id') || '';

            if (imageUrl) {
                _galleryPreviewImageId = imageId;
                _galleryPreviewLi = li;
                if (modalImg) modalImg.src = imageUrl;
                if (deleteBtn) deleteBtn.style.display = (imageId && String(imageId).indexOf('local-') !== 0) ? '' : 'none';
                if (modal) modal.style.display = 'flex';
            } else {
                _galleryUploadSlotIndex = slotIndex;
                uploadInput.click();
            }
        }, true);

        if (modal) modal.addEventListener('click', function(ev) {
            if (ev.target === this) closeGalleryPreview();
        });
        var inner = document.querySelector('#image-preview-modal .gallery-preview-inner');
        if (inner) inner.addEventListener('click', function(ev) { ev.stopPropagation(); });
        if (closeBtn) closeBtn.addEventListener('click', closeGalleryPreview);

        if (deleteBtn) deleteBtn.addEventListener('click', function(ev) {
            ev.preventDefault();
            ev.stopPropagation();
            if (!_galleryPreviewImageId || !_galleryPreviewLi) return;
            if (!confirm('この画像を削除しますか？')) return;
            var id = _galleryPreviewImageId;
            var li = _galleryPreviewLi;
            if (!id || String(id).indexOf('local-') === 0) {
                var slot = li.querySelector('.photo-slot');
                if (slot) {
                    slot.classList.remove('has-img');
                    slot.removeAttribute('data-image-id');
                    slot.removeAttribute('data-image-url');
                    slot.innerHTML = '<span class="photo-slot-empty"><i class="fas fa-image"></i></span>';
                }
                var list = document.getElementById('gallery-list');
                if (window.refreshGalleryMainState && list) window.refreshGalleryMainState(list);
                if (window.persistGalleryOrder && list) window.persistGalleryOrder(list);
                closeGalleryPreview();
                return;
            }
            var url = deleteUrlTemplate.replace('__ID__', id);
            fetch(url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (res.success) {
                    var slot = li.querySelector('.photo-slot');
                    if (slot) {
                        slot.classList.remove('has-img');
                        slot.removeAttribute('data-image-id');
                        slot.removeAttribute('data-image-url');
                        slot.innerHTML = '<span class="photo-slot-empty"><i class="fas fa-image"></i></span>';
                    }
                    var list = document.getElementById('gallery-list');
                    if (window.refreshGalleryMainState && list) window.refreshGalleryMainState(list);
                    if (window.persistGalleryOrder && list) window.persistGalleryOrder(list);
                    closeGalleryPreview();
                } else {
                    alert(res.message || '削除に失敗しました');
                }
            }).catch(function() { alert('削除に失敗しました'); });
        });

        uploadInput.addEventListener('change', function() {
            var file = this.files && this.files[0];
            this.value = '';
            if (!file) return;
            var slotIndex = _galleryUploadSlotIndex;
            if (slotIndex == null) {
                var list = document.getElementById('gallery-list');
                if (list) {
                    var firstEmpty = list.querySelector('.photo-slot:not(.has-img)');
                    var items = list.querySelectorAll('.gallery-grid-item');
                    slotIndex = firstEmpty ? Array.prototype.indexOf.call(items, firstEmpty.closest('.gallery-grid-item')) : 0;
                }
            }
            if (slotIndex == null) slotIndex = 0;
            var formData = new FormData();
            formData.append('image', file);
            formData.append('slot_index', slotIndex);
            formData.append('_token', csrfToken);
            fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function(r) {
                    return r.text().then(function(text) {
                        try { return { ok: r.ok, data: JSON.parse(text) }; }
                        catch (e) { return { ok: false, data: {} }; }
                    });
                })
                .then(function(result) {
                    var res = result.data;
                    var list = document.getElementById('gallery-list');
                    if (result.ok && res.success && res.path && res.id != null && list) {
                        var items = list.querySelectorAll('.gallery-grid-item');
                        var li = items[slotIndex];
                        if (li) {
                            var slot = li.querySelector('.photo-slot');
                            if (slot) {
                                slot.classList.add('has-img');
                                slot.setAttribute('data-image-id', res.id);
                                slot.setAttribute('data-image-url', res.path);
                                slot.innerHTML = '<img src="' + res.path + '" alt="" loading="lazy">' + (slotIndex === 0 ? '<span class="photo-slot-badge">MAIN</span>' : '');
                            }
                            if (window.refreshGalleryMainState) window.refreshGalleryMainState(list);
                            if (window.persistGalleryOrder) window.persistGalleryOrder(list);
                        }
                    } else {
                        var msg = (res && res.message) || (res.errors && res.errors.image && res.errors.image[0]) || 'アップロードに失敗しました';
                        alert(msg);
                    }
                })
                .catch(function() { alert('アップロードに失敗しました'); });
            _galleryUploadSlotIndex = null;
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
