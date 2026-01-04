document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.sub-header-tabs .tab-item');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const container = this.closest('.has-sub-header');

            // 1. 全タブの active 解除
            const allTabs = this.closest('.sub-header-tabs').querySelectorAll('.tab-item');
            allTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // 2. 全パネルの active 解除と非表示
            const allPanes = container.querySelectorAll('.tab-pane');
            allPanes.forEach(p => {
                p.classList.remove('active');
                p.style.display = 'none';
            });

            // 3. 対象パネルの表示
            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add('active');
                targetPane.style.display = 'block';
            }
            window.scrollTo(0, 0);
        });
    });
});