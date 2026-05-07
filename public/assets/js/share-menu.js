/**
 * 共有メニュー：丸ボタンの開閉、ネイティブ共有、URL コピー。
 */
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    function closeAllMenus(except) {
        document.querySelectorAll('.share-menu__panel').forEach(function (panel) {
            if (panel === except) return;
            panel.hidden = true;
            var trig = panel.previousElementSibling;
            if (trig && trig.classList.contains('share-menu__trigger')) {
                trig.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function showCopiedToast(panel) {
        var existing = panel.parentElement.querySelector('.share-menu__copied-toast');
        if (existing) existing.remove();
        var toast = document.createElement('div');
        toast.className = 'share-menu__copied-toast';
        toast.textContent = 'URLをコピーしました';
        panel.parentElement.appendChild(toast);
        setTimeout(function () { toast.style.opacity = '0'; }, 1200);
        setTimeout(function () { toast.remove(); }, 1700);
    }

    ready(function () {
        document.querySelectorAll('[data-share-menu-trigger]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var id = btn.getAttribute('aria-controls');
                var panel = id ? document.getElementById(id) : null;
                if (!panel) return;
                var willOpen = panel.hidden;
                closeAllMenus(panel);
                panel.hidden = !willOpen;
                btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });
        });

        document.querySelectorAll('[data-share-menu-panel]').forEach(function (panel) {
            // ネイティブ共有
            var nativeBtn = panel.querySelector('[data-share-action="native"]');
            if (nativeBtn) {
                nativeBtn.addEventListener('click', function () {
                    var url = panel.getAttribute('data-share-url') || '';
                    var title = panel.getAttribute('data-share-title') || '';
                    var text = panel.getAttribute('data-share-text') || '';
                    if (navigator.share && url) {
                        navigator.share({ url: url, title: title, text: text }).catch(function () { /* 無視 */ });
                    } else if (url) {
                        // フォールバック: 新規タブで開く
                        window.open(url, '_blank', 'noopener');
                    }
                    panel.hidden = true;
                });
            }
            // URL コピー
            var copyBtn = panel.querySelector('[data-share-action="copy"]');
            if (copyBtn) {
                copyBtn.addEventListener('click', function () {
                    var url = panel.getAttribute('data-share-url') || '';
                    if (!url) return;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(function () {
                            showCopiedToast(panel);
                        }).catch(function () {
                            window.prompt('URLをコピーしてください', url);
                        });
                    } else {
                        window.prompt('URLをコピーしてください', url);
                    }
                });
            }
        });

        // 外側クリック・Escape で閉じる
        document.addEventListener('click', function (e) {
            if (e.target.closest('.share-menu')) return;
            closeAllMenus(null);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAllMenus(null);
        });
    });
})();
