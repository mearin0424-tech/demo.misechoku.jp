/**
 * キャストマイページ「今すぐ入れる」宣言カードの挙動
 *
 * 依存: view 側で以下を提供すること
 *   #availability-card
 *     data-availability-declare-url  POST 先（宣言）
 *     data-availability-clear-url    DELETE 先（宣言取り消し）
 *
 *   window.MYPAGE_AVAILABILITY_CONFIG = { csrfToken: '...' }
 *
 * ボタン群は宣言 / 取り消しで動的に差し替えるため、クリックはカード全体に委譲する。
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
            if (titleEl) titleEl.textContent = '今すぐ入れる：宣言中';
            if (leadEl) leadEl.textContent = (remainingLabel || '有効中') + '・店舗側で優先表示されます';
            if (actionsEl) {
                actionsEl.innerHTML = '<button type="button" class="cast-avail__btn cast-avail__btn--danger" data-availability-clear><i class="fas fa-xmark"></i> 宣言を取り消す</button>';
            }
        }

        function renderInactiveState() {
            availCard.classList.remove('is-active');
            if (iconEl) iconEl.className = 'fas fa-clock';
            if (titleEl) titleEl.textContent = '今から入れる時間を宣言する';
            if (leadEl) leadEl.textContent = '近くの店舗の DISCOVERY で最上位に表示されます';
            if (actionsEl) {
                actionsEl.innerHTML = ''
                    + '<button type="button" class="cast-avail__btn cast-avail__btn--primary" data-availability-hours="2">2 時間</button>'
                    + '<button type="button" class="cast-avail__btn cast-avail__btn--primary" data-availability-hours="4">4 時間</button>'
                    + '<button type="button" class="cast-avail__btn cast-avail__btn--primary" data-availability-hours="8">8 時間</button>';
            }
        }

        availCard.addEventListener('click', function (e) {
            var declareBtn = e.target.closest('[data-availability-hours]');
            var clearBtn = e.target.closest('[data-availability-clear]');

            if (declareBtn) {
                var hours = parseInt(declareBtn.getAttribute('data-availability-hours'), 10);
                if (!hours) return;
                var buttons = actionsEl ? actionsEl.querySelectorAll('button') : [];
                buttons.forEach(function (b) { b.disabled = true; });

                fetch(declareUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ hours: hours })
                })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
                .then(function (res) {
                    if (res.ok && res.body && res.body.success) {
                        renderActiveState('残り' + (res.body.remaining_label || (hours + '時間')));
                        (window.appToast || function () {})('「今から' + hours + '時間 入れる」で宣言しました', 'success');
                    } else {
                        buttons.forEach(function (b) { b.disabled = false; });
                        (window.appToast || window.alert)('宣言できませんでした。もう一度お試しください', 'error');
                    }
                })
                .catch(function () {
                    buttons.forEach(function (b) { b.disabled = false; });
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
