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

    // --- 拒否ボタンの動作ロジック (新規追加) ---
    const rejectButtons = document.querySelectorAll('.js-reject-request');

    rejectButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // ポップアップを表示
            const confirmed = window.confirm('このメッセージを拒否しますか？');
            
            if (confirmed) {
                // YESの場合、一番近い親要素の .request-card を探して非表示にする
                const card = this.closest('.request-card');
                if (card) {
                    // アニメーションを付けて消す（任意）
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    
                    setTimeout(() => {
                        card.style.display = 'none';
                        
                        // 全てのカードが消えたかチェックし、空のメッセージを出す処理などをここに入れても良い
                        checkEmptyRequests();
                    }, 300);
                }
            }
        });
    });

    /**
     * リクエストが空になったかチェックする関数
     */
    function checkEmptyRequests() {
        const pane = document.getElementById('pane-requests');
        const visibleCards = pane.querySelectorAll('.request-card[style*="display: none"]').length;
        const totalCards = pane.querySelectorAll('.request-card').length;

        if (visibleCards === totalCards) {
            // ここで「リクエストはありません」のHTMLを挿入するなどの処理が可能
        }
    }
});