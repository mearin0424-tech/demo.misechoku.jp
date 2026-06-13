/* グローバル トースト
 * 使い方:
 *   window.appToast('保存しました')
 *   window.appToast('保存に失敗しました', 'error')
 *   window.appToast('処理中…', 'info', 3000)
 * variant: 'success' | 'error' | 'info' | null（default = success系）
 */
(function () {
    'use strict';
    if (window.appToast) return;

    let toastEl = null;
    let toastTimer = null;

    function ensureEl() {
        if (toastEl) return toastEl;
        toastEl = document.createElement('div');
        toastEl.className = 'app-toast';
        toastEl.setAttribute('role', 'status');
        toastEl.setAttribute('aria-live', 'polite');
        document.body.appendChild(toastEl);
        return toastEl;
    }

    window.appToast = function (msg, variant, duration) {
        if (!msg) return;
        const el = ensureEl();
        el.textContent = msg;
        el.classList.remove('is-success', 'is-error', 'is-info');
        if (variant === 'error') el.classList.add('is-error');
        else if (variant === 'info') el.classList.add('is-info');
        else el.classList.add('is-success');
        el.classList.add('is-visible');
        clearTimeout(toastTimer);
        const d = typeof duration === 'number' ? duration : 2200;
        toastTimer = setTimeout(function () { el.classList.remove('is-visible'); }, d);
    };
})();
