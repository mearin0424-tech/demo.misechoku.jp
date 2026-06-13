document.addEventListener('DOMContentLoaded', function() {
    // --- 1. 要素の取得 ---
    const sideMenu = document.getElementById('side-menu');
    const menuOverlay = document.getElementById('menu-overlay');
    const menuBtn = document.getElementById('btn-header-menu');
    
    // ポップアップ要素
    const taskPopup = document.getElementById('header-task-popup');
    const notiPopup = document.getElementById('header-notification-popup');

    // --- 2. サイドバー開閉ロジック ---
    const openSidebar = () => {
        if (sideMenu) sideMenu.classList.add('open');
        if (menuOverlay) menuOverlay.classList.add('show');
        document.body.style.overflow = 'hidden'; 
    };

    const closeSidebar = () => {
        if (sideMenu) sideMenu.classList.remove('open');
        if (menuOverlay) menuOverlay.classList.remove('show');
        document.body.style.overflow = ''; 
    };

    if (menuBtn) {
        menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            openSidebar();
        });
    }

    if (menuOverlay) {
        menuOverlay.addEventListener('click', closeSidebar);
    }

    // --- 3. ポップアップトグル関数（インライン onclick からも呼べるよう window に公開） ---
    function togglePopup(targetPopupIdOrEl, otherPopupIdOrEl) {
        const targetPopup = typeof targetPopupIdOrEl === 'string'
            ? document.getElementById(targetPopupIdOrEl)
            : targetPopupIdOrEl;
        if (!targetPopup) return;

        const otherPopup = otherPopupIdOrEl != null
            ? (typeof otherPopupIdOrEl === 'string' ? document.getElementById(otherPopupIdOrEl) : otherPopupIdOrEl)
            : null;

        const isVisible = targetPopup.style.display === 'block';

        if (otherPopup) otherPopup.style.display = 'none';
        targetPopup.style.display = isVisible ? 'none' : 'block';
    }
    window.togglePopup = togglePopup;

    // タスクボタン
    const taskBtn = document.getElementById('btn-header-task');
    if (taskBtn) {
        taskBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            togglePopup(taskPopup, notiPopup);
        });
    }

    // 通知ボタン
    const notiBtn = document.getElementById('btn-header-notification');
    if (notiBtn) {
        notiBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            togglePopup(notiPopup, taskPopup);
        });
    }

    // --- 通知の既読化 ---
    function csrf() {
        var el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }
    function updateBellBadge(unreadCount) {
        var bell = document.getElementById('btn-header-notification');
        if (!bell) return;
        var badge = bell.querySelector('.badge-notify');
        if (unreadCount > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'badge-notify';
                bell.appendChild(badge);
            }
            badge.textContent = String(unreadCount);
        } else if (badge) {
            badge.remove();
        }
    }

    // 個別アイテムクリックで既読化（リンクの場合は遷移前にPOST→そのまま遷移）
    if (notiPopup) {
        notiPopup.addEventListener('click', function (e) {
            // 全て既読
            var allBtn = e.target.closest('[data-notif-mark-all]');
            if (allBtn) {
                e.preventDefault();
                fetch('/notifications/read-all', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                }).then(function (r) { return r.json(); }).then(function (d) {
                    if (d && d.success) {
                        notiPopup.querySelectorAll('[data-notif-item].is-unread').forEach(function (el) {
                            el.classList.remove('is-unread');
                            var dot = el.querySelector('.notif-popup-item__dot');
                            if (dot) dot.remove();
                        });
                        allBtn.remove();
                        updateBellBadge(d.unread_count || 0);
                    }
                }).catch(function () {});
                return;
            }

            // 個別既読
            var item = e.target.closest('[data-notif-item]');
            if (item && item.classList.contains('is-unread')) {
                var id = item.getAttribute('data-notif-id');
                if (id) {
                    // beacon があれば優先（離脱前送信）
                    var body = new FormData();
                    body.append('_token', csrf());
                    if (navigator.sendBeacon) {
                        navigator.sendBeacon('/notifications/' + id + '/read', body);
                    } else {
                        fetch('/notifications/' + id + '/read', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            keepalive: true,
                        }).catch(function () {});
                    }
                    item.classList.remove('is-unread');
                    var dot = item.querySelector('.notif-popup-item__dot');
                    if (dot) dot.remove();
                }
                // a タグなら遷移は止めない
            }
        });
    }

    // --- 4. 画面外クリックですべて閉じる ---
    window.addEventListener('click', function(e) {
        // ポップアップの中身自体をクリックした場合は閉じない
        if (e.target.closest('.stop-propagation')) return;

        // すべてのポップアップを非表示
        if (taskPopup) taskPopup.style.display = 'none';
        if (notiPopup) notiPopup.style.display = 'none';
        
        // サイドバーを閉じる (サイドバーの外側クリックなら閉じない)
        if (sideMenu && sideMenu.classList.contains('open') && !e.target.closest('#side-menu')) {
            closeSidebar();
        }

        // FABを閉じる
        const fabContainer = document.getElementById('fab-container');
        if (fabContainer && !e.target.closest('#fab-container')) {
            fabContainer.classList.remove('active');
        }
    });
});