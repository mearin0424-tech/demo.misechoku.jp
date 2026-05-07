/**
 * 探索拠点（現在地／パスポート）モーダルの開閉と保存処理。
 * window.location.reload() で結果を反映する。
 */
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        var trigger = document.getElementById('location-pill-trigger');
        var overlay = document.getElementById('location-modal-overlay');
        if (!trigger || !overlay) return;

        var msgEl = document.getElementById('location-modal-message');
        var passportForm = document.getElementById('location-passport-form');
        var btnCurrent = document.getElementById('location-use-current');
        var btnClear = document.getElementById('location-clear');
        var closeBtns = overlay.querySelectorAll('.js-location-close');

        function open() {
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
        function close() {
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
        function showMessage(text, isSuccess) {
            if (!msgEl) return;
            msgEl.hidden = false;
            msgEl.textContent = text;
            msgEl.classList.toggle('is-success', !!isSuccess);
        }
        function clearMessage() {
            if (!msgEl) return;
            msgEl.hidden = true;
            msgEl.textContent = '';
            msgEl.classList.remove('is-success');
        }
        function csrf() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        function postLocation(payload) {
            return fetch('/setting/location', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                },
                body: JSON.stringify(payload),
            }).then(function (r) {
                return r.json().then(function (json) {
                    if (!r.ok) throw json;
                    return json;
                });
            });
        }

        trigger.addEventListener('click', function () {
            clearMessage();
            open();
        });
        closeBtns.forEach(function (btn) { btn.addEventListener('click', close); });
        overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.getAttribute('aria-hidden') === 'false') close();
        });

        if (btnCurrent) {
            btnCurrent.addEventListener('click', function () {
                clearMessage();
                if (!('geolocation' in navigator)) {
                    showMessage('このブラウザは位置情報に対応していません。', false);
                    return;
                }
                btnCurrent.disabled = true;
                btnCurrent.textContent = '位置情報を取得中...';
                navigator.geolocation.getCurrentPosition(function (pos) {
                    postLocation({
                        mode: 'current',
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        label: '現在地',
                    }).then(function () {
                        showMessage('現在地を保存しました。再読み込みします。', true);
                        setTimeout(function () { window.location.reload(); }, 600);
                    }).catch(function (err) {
                        btnCurrent.disabled = false;
                        btnCurrent.innerHTML = '<i class="fas fa-crosshairs"></i> 端末の現在地を取得';
                        showMessage((err && err.message) || '保存に失敗しました。', false);
                    });
                }, function (err) {
                    btnCurrent.disabled = false;
                    btnCurrent.innerHTML = '<i class="fas fa-crosshairs"></i> 端末の現在地を取得';
                    var msg = '位置情報の取得に失敗しました。';
                    if (err && err.code === 1) msg = '位置情報の利用が許可されていません。ブラウザ設定をご確認ください。';
                    showMessage(msg, false);
                }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 });
            });
        }

        if (passportForm) {
            passportForm.addEventListener('submit', function (e) {
                e.preventDefault();
                clearMessage();
                var input = passportForm.querySelector('input[name="address"]');
                var address = input ? input.value.trim() : '';
                if (!address) {
                    showMessage('住所または駅名を入力してください。', false);
                    return;
                }
                var submitBtn = passportForm.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;
                postLocation({ mode: 'passport', address: address, label: address })
                    .then(function () {
                        showMessage('指定位置を保存しました。再読み込みします。', true);
                        setTimeout(function () { window.location.reload(); }, 600);
                    })
                    .catch(function (err) {
                        if (submitBtn) submitBtn.disabled = false;
                        showMessage((err && err.message) || '保存に失敗しました。', false);
                    });
            });
        }

        if (btnClear) {
            btnClear.addEventListener('click', function () {
                clearMessage();
                fetch('/setting/location', {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                }).then(function (r) { return r.json(); })
                .then(function () {
                    showMessage('解除しました。再読み込みします。', true);
                    setTimeout(function () { window.location.reload(); }, 500);
                }).catch(function () {
                    showMessage('解除に失敗しました。', false);
                });
            });
        }
    });
})();
