/**
 * Talk List Tab Switching Logic
 */
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-item');
    const panes = document.querySelectorAll('.talk-content-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetPaneId = this.getAttribute('data-target');

            // タブの活性化切り替え
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // パネルの表示切り替え
            panes.forEach(pane => {
                pane.classList.remove('active');
                if (pane.id === `pane-${targetPaneId}`) {
                    pane.classList.add('active');
                }
            });
        });
    });
});