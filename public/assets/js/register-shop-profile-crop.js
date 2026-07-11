/**
 * 店舗新規登録フォーム：メイン画像を汎用画像エディタ (MisechokuImageEditor) で
 * 編集してから file input にセットする。
 *
 * 従来の Cropper 単機能モーダル (#register-shop-crop-modal 等) は残っていても不要になる。
 * MisechokuImageEditor 未ロード時の後方互換として、旧モーダルにフォールバックする。
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('shop-register-profile-image');
        if (!input) return;

        // ===== モダンパス: MisechokuImageEditor =====
        if (window.MisechokuImageEditor && typeof window.MisechokuImageEditor.open === 'function') {
            input.addEventListener('change', function () {
                var file = this.files && this.files[0];
                this.value = '';
                if (!file) return;

                window.MisechokuImageEditor.open(file, {
                    title: '店舗メイン画像を編集',
                    // 店舗は 16:9 (横長) を既定にしつつ、他のアスペクトも選べる
                    aspectRatio: 16 / 9,
                    aspectPresets: null,
                    outputWidth: 1600,
                    outputHeight: 900,
                    outputFormat: 'image/jpeg',
                    outputQuality: 0.9,
                    enableFilters: true,
                    enableRotate: true,
                    enableFlip: true,
                }).then(function (blob) {
                    var base = (file.name || 'shop').replace(/\.[^.]+$/, '');
                    var outName = base + '.jpg';
                    try {
                        var dt = new DataTransfer();
                        dt.items.add(new File([blob], outName, { type: 'image/jpeg' }));
                        input.files = dt.files;
                    } catch (err) {
                        (window.appToast || window.alert)('ブラウザが画像の差し替えに対応していません。別のブラウザでお試しください。', 'error');
                    }
                }).catch(function (err) {
                    if (err && err.message && err.message !== 'cancelled') {
                        (window.appToast || window.alert)(err.message, 'error');
                    }
                });
            });
            return;
        }

        // ===== 旧パス: Cropper 単機能モーダル =====
        var modal = document.getElementById('register-shop-crop-modal');
        var img = document.getElementById('register-shop-crop-preview');
        var btnCancel = document.getElementById('register-shop-crop-cancel');
        var btnConfirm = document.getElementById('register-shop-crop-confirm');
        if (!modal || !img || !btnCancel || !btnConfirm || !window.Cropper) return;

        var cropper = null;
        var pendingFile = null;

        function destroyCropper() { if (cropper) { cropper.destroy(); cropper = null; } }
        function closeModal() { modal.style.display = 'none'; destroyCropper(); img.removeAttribute('src'); pendingFile = null; }

        input.addEventListener('change', function () {
            var file = this.files && this.files[0];
            this.value = '';
            if (!file) return;
            pendingFile = file;
            var reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                destroyCropper();
                cropper = new Cropper(img, {
                    aspectRatio: 16 / 9, viewMode: 1, dragMode: 'move', autoCropArea: 1,
                    zoomable: true, movable: true, scalable: false, rotatable: false,
                    responsive: true, background: false, toggleDragModeOnDblclick: false,
                });
                modal.style.display = 'flex';
            };
            reader.onerror = function () { pendingFile = null; (window.appToast || window.alert)('画像の読み込みに失敗しました', 'error'); };
            reader.readAsDataURL(file);
        });

        btnCancel.addEventListener('click', function () { closeModal(); input.value = ''; });
        btnConfirm.addEventListener('click', function () {
            if (!cropper || !pendingFile) return;
            if (btnConfirm.disabled) return;
            btnConfirm.disabled = true;
            var canvas = cropper.getCroppedCanvas({ width: 1600, height: 900, imageSmoothingQuality: 'high' });
            if (!canvas) { (window.appToast || window.alert)('画像のトリミングに失敗しました', 'error'); btnConfirm.disabled = false; return; }
            canvas.toBlob(function (blob) {
                btnConfirm.disabled = false;
                if (!blob) { (window.appToast || window.alert)('画像の加工に失敗しました', 'error'); return; }
                var base = (pendingFile.name || 'shop').replace(/\.[^.]+$/, '');
                var outName = base + '.jpg';
                try {
                    var dt = new DataTransfer();
                    dt.items.add(new File([blob], outName, { type: 'image/jpeg' }));
                    input.files = dt.files;
                } catch (err) {
                    (window.appToast || window.alert)('ブラウザが画像の差し替えに対応していません。別のブラウザでお試しください。', 'error');
                    return;
                }
                closeModal();
            }, 'image/jpeg', 0.9);
        });
        modal.addEventListener('click', function (ev) { if (ev.target === modal) btnCancel.click(); });
    });
})();
