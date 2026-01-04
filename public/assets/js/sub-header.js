/**
 * Sub-header Tab System (Universal)
 */
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.sub-header-tabs .tab-item');
    
    if (tabs.length === 0) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const wrapper = this.closest('.sub-header-wrapper');
            const container = this.closest('.has-sub-header');

            if (!container || !targetId) return;

            // 1. 全タブの active を解除し、クリックされたものに付与
            const allTabs = wrapper.querySelectorAll('.tab-item');
            allTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // 2. 全パネルを非表示にし、対象のIDだけを表示
            const allPanes = container.querySelectorAll('.tab-pane');
            allPanes.forEach(p => {
                p.classList.remove('active');
                p.style.display = 'none'; // 確実に隠す
            });

            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add('active');
                targetPane.style.display = 'block'; // 確実に表示
            }
            
            // 3. ページ上部へスクロール
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
});