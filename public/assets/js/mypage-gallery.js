/**
 * Mypage ギャラリー: 画像ありスロットタップ→拡大表示・削除、画像なしスロットタップ→アップロード
 * グローバルに MYPAGE_GALLERY_CONFIG が定義されている必要あり。
 */
(function() {
    var config = window.MYPAGE_GALLERY_CONFIG;
    if (!config || !config.uploadUrl) return;

    var cropAspectW = typeof config.cropAspectW === 'number' && config.cropAspectW > 0 ? config.cropAspectW : 16;
    var cropAspectH = typeof config.cropAspectH === 'number' && config.cropAspectH > 0 ? config.cropAspectH : 9;
    var cropAspectRatio = cropAspectW / cropAspectH;
    var cropMaxWidth = typeof config.cropMaxWidth === 'number' && config.cropMaxWidth > 0 ? config.cropMaxWidth : 1600;
    var cropMaxHeight = typeof config.cropMaxHeight === 'number' && config.cropMaxHeight > 0 ? config.cropMaxHeight : 900;

    var _galleryPreviewImageId = null;
    var _galleryPreviewLi = null;
    var _galleryUploadSlotIndex = null;
    var _pendingUploadFile = null;
    var _pendingUploadSlotIndex = null;
    var _pendingZoom = 1;
    var _cropper = null;
    var csrfToken = config.csrfToken || '';
    var uploadUrl = config.uploadUrl;
    var deleteUrlTemplate = config.deleteUrlTemplate || '';

    function closeGalleryPreview() {
        var modal = document.getElementById('image-preview-modal');
        if (modal) modal.style.display = 'none';
        var deleteBtn = document.getElementById('gallery-preview-delete-btn');
        if (deleteBtn) deleteBtn.style.display = '';
        var recropBtn = document.getElementById('gallery-preview-recrop-btn');
        if (recropBtn) recropBtn.style.display = '';
        _galleryPreviewImageId = null;
        _galleryPreviewLi = null;
    }

    // 既存画像を再切り抜きするためのフラグ：再切り抜きで上書きアップロード後に旧 ID を削除する
    var _recropReplacingId = null;

    function run() {
        var uploadInput = document.getElementById('gallery-upload');
        if (!uploadInput) return;

        var modal = document.getElementById('image-preview-modal');
        var modalImg = document.getElementById('modal-img');
        var deleteBtn = document.getElementById('gallery-preview-delete-btn');
        var closeBtn = document.getElementById('gallery-preview-close-btn');

        var editModal = document.getElementById('image-edit-modal');
        var editPreviewImg = document.getElementById('image-edit-preview');
        var editConfirmBtn = document.getElementById('image-edit-confirm-btn');
        var editCancelBtn = document.getElementById('image-edit-cancel-btn');
        var editZoomInput = null;
        var editZoomValue = null;

        document.addEventListener('click', function(ev) {
            var slot = ev.target.closest('.photo-slot');
            if (!slot) return;
            var list = document.getElementById('gallery-list');
            if (!list || !list.contains(slot)) return;
            if (window.__galleryDragging) return;
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
                var canEditOrDelete = imageId && String(imageId).indexOf('local-') !== 0;
                if (deleteBtn) deleteBtn.style.display = canEditOrDelete ? '' : 'none';
                var recropBtnEl = document.getElementById('gallery-preview-recrop-btn');
                if (recropBtnEl) {
                    // 再切り抜きはサーバ保存済み画像のみ
                    recropBtnEl.style.display = canEditOrDelete ? '' : 'none';
                    recropBtnEl.setAttribute('data-image-url', imageUrl);
                }
                if (modal) modal.style.display = 'flex';
            } else {
                _galleryUploadSlotIndex = slotIndex;
                uploadInput.click();
            }
        }, true);

        // 再切り抜き：既存画像を fetch→Blob→Cropper モーダルに流す
        var recropBtn = document.getElementById('gallery-preview-recrop-btn');
        if (recropBtn) {
            recropBtn.addEventListener('click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                var url = recropBtn.getAttribute('data-image-url') || '';
                if (!url || !_galleryPreviewLi || !_galleryPreviewImageId) return;
                var slotIndex = parseInt(_galleryPreviewLi.getAttribute('data-slot-index'), 10);
                if (isNaN(slotIndex)) slotIndex = 0;
                var oldImageId = _galleryPreviewImageId;

                // プレビューモーダルを閉じてからエディタを開く
                closeGalleryPreview();

                fetch(url, { credentials: 'same-origin' })
                    .then(function (r) {
                        if (!r.ok) throw new Error('fetch failed');
                        return r.blob();
                    })
                    .then(function (blob) {
                        // File 化して openEditModal に渡す（name を持つ Blob として扱う）
                        var fileName = 'recrop-' + Date.now() + '.jpg';
                        var file;
                        try {
                            file = new File([blob], fileName, { type: blob.type || 'image/jpeg' });
                        } catch (e) {
                            // Safari 14- など File コンストラクタが使えない環境
                            file = blob;
                            file.name = fileName;
                        }
                        // 再切り抜き後にアップロード成功したら、旧 ID を削除する
                        _recropReplacingId = oldImageId;
                        openEditModal(file, slotIndex);
                    })
                    .catch(function () {
                        alert('画像の読み込みに失敗しました。');
                    });
            });
        }

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

        function resolveSlotIndex() {
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
            return slotIndex;
        }

        function resizeImageFallback(file, maxWidth, maxHeight) {
            return new Promise(function(resolve, reject) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = new Image();
                    img.onload = function() {
                        var ASPECT_W = cropAspectW;
                        var ASPECT_H = cropAspectH;
                        var aspect = ASPECT_W / ASPECT_H;

                        // 元画像から指定アスペクト比にクロップする（中央基準）
                        var srcRatio = img.width / img.height;
                        var targetRatio = aspect;
                        var sx, sy, sw, sh;
                        if (srcRatio > targetRatio) {
                            // 横長 → 左右をトリミング
                            sh = img.height;
                            sw = sh * targetRatio;
                            sx = (img.width - sw) / 2;
                            sy = 0;
                        } else {
                            // 縦長 → 上下をトリミング
                            sw = img.width;
                            sh = sw / targetRatio;
                            sx = 0;
                            sy = (img.height - sh) / 2;
                        }

                        // 出力サイズを決定（指定アスペクト比を維持しつつ、maxWidth/maxHeight以内 & おおよそ 2MP 以下）
                        var MAX_PIXELS = 2000000; // 約 2MP
                        var outWidth = Math.min(img.width, maxWidth || img.width);
                        var outHeight = Math.round(outWidth * ASPECT_H / ASPECT_W);
                        if (outHeight > img.height || (maxHeight && outHeight > maxHeight)) {
                            outHeight = Math.min(img.height, maxHeight || img.height);
                            outWidth = Math.round(outHeight * ASPECT_W / ASPECT_H);
                        }

                        // 2MP を超える場合は縮小
                        var pixels = outWidth * outHeight;
                        if (pixels > MAX_PIXELS) {
                            var scale = Math.sqrt(MAX_PIXELS / pixels);
                            outWidth = Math.floor(outWidth * scale);
                            outHeight = Math.floor(outHeight * scale);
                        }

                        var canvas = document.createElement('canvas');
                        canvas.width = outWidth;
                        canvas.height = outHeight;
                        var ctx = canvas.getContext('2d');

                        ctx.drawImage(img, sx, sy, sw, sh, 0, 0, outWidth, outHeight);
                        canvas.toBlob(function(blob) {
                            if (!blob) {
                                reject(new Error('画像の変換に失敗しました'));
                                return;
                            }
                            resolve(blob);
                        }, 'image/jpeg', 0.9);
                    };
                    img.onerror = function() { reject(new Error('画像の読み込みに失敗しました')); };
                    img.src = e.target.result;
                };
                reader.onerror = function() { reject(new Error('ファイルの読み込みに失敗しました')); };
                reader.readAsDataURL(file);
            });
        }

        function performUpload(blob, originalName, slotIndex) {
            var formData = new FormData();
            formData.append('image', blob, originalName || 'upload.jpg');
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

                        // 再切り抜きフローの場合：旧画像をサーバから削除
                        if (_recropReplacingId && deleteUrlTemplate) {
                            var oldId = _recropReplacingId;
                            _recropReplacingId = null;
                            // 新画像と旧 ID が同じことは無い前提（アップロードで新規 ID 採番）
                            if (String(oldId) !== String(res.id)) {
                                var delUrl = deleteUrlTemplate.replace('__ID__', oldId);
                                fetch(delUrl, {
                                    method: 'DELETE',
                                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                                    credentials: 'same-origin'
                                }).catch(function () { /* 旧画像の削除失敗はサイレント */ });
                            }
                        }
                    } else {
                        // 失敗時は再切り抜きフラグもクリア
                        _recropReplacingId = null;
                        var msg = (res && res.message) || (res.errors && res.errors.image && res.errors.image[0]) || 'アップロードに失敗しました';
                        alert(msg);
                    }
                })
                .catch(function() {
                    _recropReplacingId = null;
                    alert('アップロードに失敗しました');
                })
                .finally(function() {
                    _galleryUploadSlotIndex = null;
                    _pendingUploadFile = null;
                    _pendingUploadSlotIndex = null;
                    _pendingZoom = 1;
                });
        }

        function openEditModal(file, slotIndex) {
            if (editConfirmBtn) {
                editConfirmBtn.disabled = false;
            }
            if (!editModal || !editPreviewImg) {
                // フォールバック：そのままアップロード
                var fallbackIndex = slotIndex != null ? slotIndex : resolveSlotIndex();
                performUpload(file, file.name, fallbackIndex);
                return;
            }
            _pendingUploadFile = file;
            _pendingUploadSlotIndex = slotIndex != null ? slotIndex : resolveSlotIndex();
            _pendingZoom = 1;
            if (_cropper) {
                _cropper.destroy();
                _cropper = null;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                editPreviewImg.src = e.target.result;
                if (_cropper) {
                    _cropper.destroy();
                    _cropper = null;
                }
                if (window.Cropper) {
                    _cropper = new Cropper(editPreviewImg, {
                        aspectRatio: cropAspectRatio,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        zoomable: true,
                        movable: true,
                        scalable: false,
                        rotatable: false,
                        responsive: true,
                        background: false,
                        toggleDragModeOnDblclick: false,
                    });
                }
                if (editModal) {
                    editModal.style.display = 'flex';
                }
                try {
                    document.body.classList.add('is-image-editing');
                } catch (e) {}
            };
            reader.readAsDataURL(file);
        }

        if (editCancelBtn && editModal) {
            editCancelBtn.addEventListener('click', function() {
                editModal.style.display = 'none';
                _pendingUploadFile = null;
                _pendingUploadSlotIndex = null;
                _pendingZoom = 1;
                _recropReplacingId = null;
                if (_cropper) {
                    _cropper.destroy();
                    _cropper = null;
                }
                if (editConfirmBtn) {
                    editConfirmBtn.disabled = false;
                }
                try {
                    document.body.classList.remove('is-image-editing');
                } catch (e) {}
            });
        }

        if (editConfirmBtn && editModal) {
            editConfirmBtn.addEventListener('click', function() {
                if (!(_pendingUploadFile && typeof _pendingUploadSlotIndex === 'number')) {
                    editModal.style.display = 'none';
                    return;
                }
                var btn = editConfirmBtn;
                if (btn.disabled) return;
                btn.disabled = true;
                var MAX_WIDTH = cropMaxWidth;
                var MAX_HEIGHT = cropMaxHeight;

                var cropAndUpload = function() {
                    if (_cropper && typeof _cropper.getCroppedCanvas === 'function') {
                        var canvas = _cropper.getCroppedCanvas({
                            width: MAX_WIDTH,
                            height: MAX_HEIGHT,
                            imageSmoothingQuality: 'high'
                        });
                        if (!canvas) {
                            throw new Error('画像のトリミングに失敗しました');
                        }
                        canvas.toBlob(function(blob) {
                            if (!blob) {
                                alert('画像の加工に失敗しました');
                                btn.disabled = false;
                                return;
                            }
                            if (editModal) editModal.style.display = 'none';
                            try {
                                document.body.classList.remove('is-image-editing');
                            } catch (e) {}
                            performUpload(blob, _pendingUploadFile.name, _pendingUploadSlotIndex);
                            btn.disabled = false;
                        }, 'image/jpeg', 0.9);
                    } else {
                        // フォールバック：従来の中央トリミング
                        resizeImageFallback(_pendingUploadFile, MAX_WIDTH, MAX_HEIGHT)
                            .then(function(blob) {
                                if (editModal) editModal.style.display = 'none';
                                try {
                                    document.body.classList.remove('is-image-editing');
                                } catch (e) {}
                                performUpload(blob, _pendingUploadFile.name, _pendingUploadSlotIndex);
                            })
                            .catch(function(err) {
                                alert(err && err.message ? err.message : '画像の加工に失敗しました');
                            })
                            .finally(function() {
                                btn.disabled = false;
                            });
                    }
                };

                try {
                    cropAndUpload();
                } catch (e) {
                    alert(e && e.message ? e.message : '画像の加工に失敗しました');
                    btn.disabled = false;
                }
            });
        }

        uploadInput.addEventListener('change', function() {
            var file = this.files && this.files[0];
            this.value = '';
            if (!file) return;
            var slotIndex = resolveSlotIndex();
            openEditModal(file, slotIndex);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
