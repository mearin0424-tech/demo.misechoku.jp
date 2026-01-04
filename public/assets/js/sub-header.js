document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.sub-header-tabs .tab-item');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const wrapper = this.closest('.sub-header-tabs');
            const container = this.closest('.has-sub-header');

            // タブの表示更新
            wrapper.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // パネルの表示更新
            container.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            const targetPane = document.getElementById(targetId);
            if (targetPane) targetPane.classList.add('active');
            
            // 切り替え時に最上部へスクロール（任意）
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
});