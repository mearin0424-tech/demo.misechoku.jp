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

    // --- 通知の既読化・フィルタ切替・バッジ更新 ---
    function csrf() {
        var el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    // ヘッダーの通知アイコン + バッジを一貫して更新
    function updateBellBadge(unreadCount) {
        var bell = document.getElementById('btn-header-notification');
        if (!bell) return;

        // 旧クラス（.badge-notify）と新クラス（.header-badge）両方を面倒みる
        var oldBadge = bell.querySelector('.badge-notify');
        var newBadge = bell.querySelector('.header-badge.is-accent');
        var display = unreadCount > 99 ? '99+' : String(unreadCount);

        if (unreadCount > 0) {
            if (oldBadge) oldBadge.textContent = display;
            if (!newBadge) {
                newBadge = document.createElement('span');
                newBadge.className = 'header-badge is-accent';
                newBadge.setAttribute('aria-hidden', 'true');
                bell.appendChild(newBadge);
            }
            newBadge.textContent = display;
            bell.classList.add('has-badge', 'is-ringing');
            bell.setAttribute('aria-label', 'お知らせ（未読 ' + unreadCount + '件）');
        } else {
            if (oldBadge) oldBadge.remove();
            if (newBadge) newBadge.remove();
            bell.classList.remove('has-badge', 'is-ringing');
            bell.setAttribute('aria-label', 'お知らせ（未読なし）');
        }

        // ポップオーバー内の見出しカウント／タブ数字も同期
        var headCount = document.querySelector('[data-notif-unread-badge]');
        var headActions = document.querySelector('.notif-popup__head-actions');
        var tabNum = document.querySelector('[data-notif-unread-num]');
        if (tabNum) tabNum.textContent = display;
        if (unreadCount === 0) {
            if (headCount) headCount.remove();
            var markAllBtn = document.querySelector('[data-notif-mark-all]');
            if (markAllBtn) markAllBtn.remove();
        } else if (headCount) {
            headCount.textContent = display;
        }
        // popup 属性
        if (notiPopup) notiPopup.setAttribute('data-unread-count', String(unreadCount));
    }

    // フィルタ（すべて / 未読のみ）を適用
    function applyNotifFilter(filter) {
        if (!notiPopup) return;
        var body = notiPopup.querySelector('[data-notif-body]');
        if (!body) return;

        var items = body.querySelectorAll('[data-notif-item]');
        var visibleUnread = 0;
        items.forEach(function (el) {
            var isUnread = el.classList.contains('is-unread');
            var show = filter === 'all' || isUnread;
            el.style.display = show ? '' : 'none';
            if (show && isUnread) visibleUnread++;
        });

        // グループ見出しを items のあるものだけに絞る
        body.querySelectorAll('[data-notif-group]').forEach(function (section) {
            var visible = section.querySelectorAll('[data-notif-item]:not([style*="display: none"]):not([style*="display:none"])');
            var anyVisible = false;
            visible.forEach(function () { anyVisible = true; });
            section.style.display = anyVisible ? '' : 'none';
        });

        // 未読フィルタで 0 件なら空状態を表示
        var unreadEmpty = body.querySelector('[data-notif-unread-empty]');
        if (unreadEmpty) {
            if (filter === 'unread') {
                var count = body.querySelectorAll('[data-notif-item].is-unread').length;
                unreadEmpty.hidden = count > 0;
            } else {
                unreadEmpty.hidden = true;
            }
        }
    }

    // タブ切替
    if (notiPopup) {
        notiPopup.addEventListener('click', function (e) {
            // タブ
            var tabBtn = e.target.closest('[data-notif-filter]');
            if (tabBtn) {
                e.preventDefault();
                notiPopup.querySelectorAll('[data-notif-filter]').forEach(function (b) {
                    var active = b === tabBtn;
                    b.classList.toggle('is-active', active);
                    b.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                applyNotifFilter(tabBtn.getAttribute('data-notif-filter'));
                return;
            }

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
                            el.setAttribute('data-notif-unread', '0');
                            var dot = el.querySelector('.notif-popup__item-dot');
                            if (dot) dot.remove();
                        });
                        updateBellBadge(d.unread_count || 0);
                        // 現在アクティブなフィルタを再適用
                        var activeTab = notiPopup.querySelector('[data-notif-filter].is-active');
                        applyNotifFilter(activeTab ? activeTab.getAttribute('data-notif-filter') : 'all');
                    }
                }).catch(function () {});
                return;
            }

            // 個別既読
            var item = e.target.closest('[data-notif-item]');
            if (item && item.classList.contains('is-unread')) {
                var id = item.getAttribute('data-notif-id');
                if (id) {
                    // Anchor items go through /notifications/{id}/visit which marks read
                    // server-side before redirecting. For non-anchor items (no navigation)
                    // we still need to POST the read API explicitly.
                    var isAnchor = item.tagName === 'A' && item.hasAttribute('href');
                    if (!isAnchor) {
                        fetch('/notifications/' + id + '/read', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrf(),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        }).catch(function () {});
                    }
                    item.classList.remove('is-unread');
                    item.setAttribute('data-notif-unread', '0');
                    var dot = item.querySelector('.notif-popup__item-dot');
                    if (dot) dot.remove();
                    // カウンタをローカル反映（サーバは遷移先で確定させる）
                    var cur = parseInt(notiPopup.getAttribute('data-unread-count') || '0', 10);
                    updateBellBadge(Math.max(0, cur - 1));
                }
                // a タグなら遷移は止めない
            }
        });
    }

    // ポップオーバーを開いた瞬間にサーバの unread-count を再取得（他タブでの既読反映）
    if (notiBtn) {
        notiBtn.addEventListener('click', function () {
            // 開いた直後だけ実行
            setTimeout(function () {
                if (!notiPopup || notiPopup.style.display !== 'block') return;
                fetch('/notifications/unread-count', {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (d) {
                        if (d && typeof d.unread_count === 'number') {
                            var cur = parseInt(notiPopup.getAttribute('data-unread-count') || '0', 10);
                            if (d.unread_count !== cur) {
                                // 数字だけ同期（DOM は次回リロードで最新化）
                                updateBellBadge(d.unread_count);
                            }
                        }
                    })
                    .catch(function () {});
            }, 20);
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