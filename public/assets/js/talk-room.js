/**
 * Talk Room Logic (Optimistic UI & Real-time Update)
 */
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    
    // エラー防止用のガード
    if (!chatMessages || !chatForm) return;

    // textarea[name="message"] を取得（inputから修正）
    const messageInput = chatForm.querySelector('textarea[name="message"]');
    if (!messageInput) return;

    /**
     * 最下部へスクロール
     */
    const scrollToBottom = (behavior = 'auto') => {
        chatMessages.scrollTo({
            top: chatMessages.scrollHeight,
            behavior: behavior
        });
    };

    /**
     * テキストエリアの自動リサイズ
     */
    const autoResize = () => {
        messageInput.style.height = 'auto';
        messageInput.style.height = (messageInput.scrollHeight) + 'px';
    };

    // 初期表示で最下部へ
    scrollToBottom();

    // 入力イベントでリサイズ実行
    messageInput.addEventListener('input', autoResize);

    // 送信処理
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        if (!content) return;

        // 設定情報の取得
        const url = chatForm.getAttribute('data-url');
        const partnerId = chatForm.getAttribute('data-partner-id');
        const token = chatForm.querySelector('input[name="_token"]').value;
        const submitBtn = chatForm.querySelector('button');

        // 二重送信防止
        submitBtn.disabled = true;

        // 一時的なIDを生成（送信完了時に要素を特定するため）
        const tempId = 'msg-' + Date.now();
        const now = new Date();
        const timeStr = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0');

        /**
         * 1. サーバー送信前に画面にメッセージを追加（Optimistic UI）
         * 送信中はチェックマークを .msg-status.sending で点滅させる
         */
        const messageHtml = `
            <div class="message-row msg-right" id="${tempId}">
                <div class="message-bubble">
                    <p class="whitespace-pre-wrap m-0">${content.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, '<br>')}</p>
                    <div class="msg-footer">
                        <span class="msg-time">${timeStr}</span>
                        <span class="msg-status sending"><i class="fas fa-check"></i></span>
                    </div>
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', messageHtml);
        
        // 入力欄をクリアしてリサイズをリセット
        messageInput.value = '';
        autoResize();
        scrollToBottom('smooth');

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    partner_id: partnerId,
                    message: content
                })
            });

            const result = await response.json();

            if (result.success) {
                /**
                 * 2. 送信成功時の処理
                 * 「送信中」アニメーションクラスを取り除き、確定状態にする
                 */
                const sentElement = document.getElementById(tempId);
                if (sentElement) {
                    const statusIcon = sentElement.querySelector('.msg-status');
                    statusIcon.classList.remove('sending'); // 点滅解除
                    statusIcon.style.opacity = '1';         // はっきり表示
                }
            } else {
                throw new Error('Send failed');
            }
        } catch (error) {
            console.error('Error:', error);
            // エラー時はチェックマークを警告アイコンに変更
            const errorElement = document.getElementById(tempId);
            if (errorElement) {
                const statusIcon = errorElement.querySelector('.msg-status');
                statusIcon.innerHTML = '<i class="fas fa-exclamation-circle text-red-500"></i>';
                statusIcon.classList.remove('sending');
            }
        } finally {
            submitBtn.disabled = false;
            messageInput.focus();
        }
    });

    // キーボード表示時のスクロール補正
    messageInput.addEventListener('focus', () => {
        setTimeout(() => scrollToBottom('smooth'), 300);
    });
});