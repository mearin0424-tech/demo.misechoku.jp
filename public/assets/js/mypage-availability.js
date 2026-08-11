/**
 * Cast MyPage "Available Today" declaration card behavior
 *
 * Dependencies (provided by the view):
 *   #availability-card
 *     data-availability-declare-url  POST endpoint (declare)
 *     data-availability-clear-url    DELETE endpoint (clear)
 *
 *   window.MYPAGE_AVAILABILITY_CONFIG = { csrfToken: '...' }
 *
 * Buttons are swapped between the declared / cleared states, so clicks are
 * delegated on the card element.
 * The declaration is fixed to end-of-day ("today only"), matching the shop
 * side's behavior. There is no hours selection.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var availCard = document.getElementById('availability-card');
        if (!availCard) return;

        var config = window.MYPAGE_AVAILABILITY_CONFIG || {};
        var csrfToken = config.csrfToken || '';
        var declareUrl = availCard.getAttribute('data-availability-declare-url');
        var clearUrl = availCard.getAttribute('data-availability-clear-url');

        var actionsEl = availCard.querySelector('[data-availability-actions]');
        var titleEl = availCard.querySelector('.cast-avail__title');
        var iconEl = availCard.querySelector('.cast-avail__icon i');
        var leadEl = availCard.querySelector('[data-availability-remaining]');

        function renderActiveState(remainingLabel) {
            availCard.classList.add('is-active');
            if (iconEl) iconEl.className = 'fas fa-bolt';
            if (titleEl) titleEl.textContent = '今から入れます：宣言中';
            if (leadEl) leadEl.textContent = (remainingLabel || '有効中') + '・本日 23:59 まで有効';
            if (actionsEl) {
                actionsEl.innerHTML = '<button type="button" class="cast-avail__btn cast-avail__btn--danger" data-availability-clear><i class="fas fa-xmark"></i> OFF</button>';
            }
        }

        function renderInactiveState() {
            availCard.classList.remove('is-active');
            if (iconEl) iconEl.className = 'fas fa-clock';
            if (titleEl) titleEl.textContent = '今から入れます';
            if (leadEl) leadEl.textContent = '本日中、近くの店舗の SWIPE で優先表示されます';
            if (actionsEl) {
                actionsEl.innerHTML = '<button type="button" class="cast-avail__btn cast-avail__btn--primary" data-availability-declare><i class="fas fa-bolt"></i> 本日 ON</button>';
            }
        }

        availCard.addEventListener('click', function (e) {
            var declareBtn = e.target.closest('[data-availability-declare]');
            var clearBtn = e.target.closest('[data-availability-clear]');

            if (declareBtn) {
                declareBtn.disabled = true;

                fetch(declareUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: '{}'
                })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
                .then(function (res) {
                    if (res.ok && res.body && res.body.success) {
                        renderActiveState(res.body.remaining_label || '本日中');
                        (window.appToast || function () {})('「今から入れます」を本日 ON にしました', 'success');
                    } else {
                        declareBtn.disabled = false;
                        (window.appToast || window.alert)('宣言できませんでした。もう一度お試しください', 'error');
                    }
                })
                .catch(function () {
                    declareBtn.disabled = false;
                    (window.appToast || window.alert)('通信エラーで宣言できませんでした', 'error');
                });
                return;
            }

            if (clearBtn) {
                clearBtn.disabled = true;
                fetch(clearUrl, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
                .then(function (res) {
                    if (res.ok && res.body && res.body.success) {
                        renderInactiveState();
                        (window.appToast || function () {})('宣言を取り消しました', 'success');
                    } else {
                        clearBtn.disabled = false;
                        (window.appToast || window.alert)('取り消せませんでした', 'error');
                    }
                })
                .catch(function () {
                    clearBtn.disabled = false;
                    (window.appToast || window.alert)('通信エラーで取り消せませんでした', 'error');
                });
            }
        });
    });
}());
