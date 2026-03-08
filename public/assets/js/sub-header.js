/**
 * Sub-header Tab System (Robust Version)
 */
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.sub-header-tabs .tab-item');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            if (!targetId) return;

            // 1. タブの親要素とコンテンツ全体の親要素を確実に取得
            const wrapper = this.closest('.sub-header-wrapper');
            // パネルは content-wrapper 内にある（TALK は has-sub-header の外に pane があるため）
            const container = this.closest('.content-wrapper') || document.body;

            // 2. 全タブの active 解除 (クリックされたタブと同じグループ内)
            if (wrapper) {
                wrapper.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
            }
            this.classList.add('active');

            // 3. 全パネルの非表示 (同一 content-wrapper 内の .tab-pane を対象)
            const allPanes = container.querySelectorAll('.tab-pane');
            allPanes.forEach(p => {
                p.classList.remove('active');
                p.style.display = 'none'; // 強制非表示
            });

            // 4. 対象パネルの表示
            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add('active');
                targetPane.style.display = 'block'; // 強制表示
            }
            
            window.scrollTo(0, 0);
        });
    });
});