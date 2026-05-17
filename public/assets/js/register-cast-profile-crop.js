/**
 * キャスト新規登録フォーム：メイン画像を 3:4（縦長）でトリミングしてから file input にセットする。
 * #cast-register-profile-image / #register-cast-crop-modal 等が存在するページでのみ動作。
 */
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('cast-register-profile-image');
        var modal = document.getElementById('register-cast-crop-modal');
        var img = document.getElementById('register-cast-crop-preview');
        var btnCancel = document.getElementById('register-cast-crop-cancel');
        var btnConfirm = document.getElementById('register-cast-crop-confirm');
        if (!input || !modal || !img || !btnCancel || !btnConfirm || !window.Cropper) {
            return;
        }

        var cropper = null;
        var pendingFile = null;

        function destroyCropper() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        }

        function closeModal() {
            modal.style.display = 'none';
            destroyCropper();
            img.removeAttribute('src');
            pendingFile = null;
        }

        input.addEventListener('change', function() {
            var file = this.files && this.files[0];
            this.value = '';
            if (!file) {
                return;
            }
            pendingFile = file;
            var reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                destroyCropper();
                cropper = new Cropper(img, {
                    aspectRatio: 4 / 5,
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
                modal.style.display = 'flex';
            };
            reader.onerror = function() {
                pendingFile = null;
                alert('画像の読み込みに失敗しました');
            };
            reader.readAsDataURL(file);
        });

        btnCancel.addEventListener('click', function() {
            closeModal();
            input.value = '';
        });

        btnConfirm.addEventListener('click', function() {
            if (!cropper || !pendingFile) {
                return;
            }
            var btn = btnConfirm;
            if (btn.disabled) {
                return;
            }
            btn.disabled = true;
            var canvas = cropper.getCroppedCanvas({
                width: 1200,
                height: 1600,
                imageSmoothingQuality: 'high',
            });
            if (!canvas) {
                alert('画像のトリミングに失敗しました');
                btn.disabled = false;
                return;
            }
            canvas.toBlob(function(blob) {
                btn.disabled = false;
                if (!blob) {
                    alert('画像の加工に失敗しました');
                    return;
                }
                var base = (pendingFile.name || 'profile').replace(/\.[^.]+$/, '');
                var outName = base + '.jpg';
                var dt = new DataTransfer();
                try {
                    dt.items.add(new File([blob], outName, { type: 'image/jpeg' }));
                    input.files = dt.files;
                } catch (err) {
                    alert('ブラウザが画像の差し替えに対応していません。別のブラウザでお試しください。');
                    return;
                }
                closeModal();
            }, 'image/jpeg', 0.9);
        });

        modal.addEventListener('click', function(ev) {
            if (ev.target === modal) {
                btnCancel.click();
            }
        });
    });
})();
