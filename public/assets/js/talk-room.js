/**
 * Talk Room Logic (Real-time update with Server)
 */
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    
    // ガード：要素がない画面では実行しない
    if (!chatMessages || !chatForm) return;

    // 重要：input から textarea に修正
    const messageInput = chatForm.querySelector('textarea[name="message"]');
    if (!messageInput) return;

    const scrollToBottom = (behavior = 'auto') => {
        chatMessages.scrollTo({
            top: chatMessages.scrollHeight,
            behavior: behavior
        });
    };

    // テキストエリアの自動リサイズ
    const autoResize = () => {
        messageInput.style.height = 'auto';
        messageInput.style.height = (messageInput.scrollHeight) + 'px';
    };

    // 初期表示で最下部へ
    scrollToBottom();

    // 入力イベント
    messageInput.addEventListener('input', autoResize);

    // 送信処理
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        if (!content) return;

        const url = chatForm.getAttribute('data-url');
        const partnerId = chatForm.getAttribute('data-partner-id');
        const token = chatForm.querySelector('input[name="_token"]').value;
        const submitBtn = chatForm.querySelector('button');

        // 二重送信防止
        submitBtn.disabled = true;

        // 一時的なIDと時刻の生成
        const tempId = 'msg-' + Date.now();
        const now = new Date();
        const timeStr = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0');

        // 1. サーバー送信前に画面に「送信中」状態で追加
        const messageHtml = `
            <div class="message-row msg-right" id="${tempId}">
                <div class="message-bubble">
                    <p class="msg-text">${content.replace(/\n/g, '<br>')}</p>
                    <div class="msg-footer">
                        <span class="msg-time">${timeStr}</span>
                        <span class="msg-status sending"><i class="fas fa-check"></i></span>
                    </div>
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', messageHtml);
        
        // 入力欄リセット
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
                body: JSON.stringify({ partner_id: partnerId, message: content })
            });

            const result = await response.json();

            if (result.success) {
                // 2. 成功：点滅を止めて確定
                const sentMsg = document.getElementById(tempId);
                if (sentMsg) {
                    sentMsg.querySelector('.msg-status').classList.remove('sending');
                }
            } else {
                throw new Error('Failed');
            }
        } catch (error) {
            // 3. 失敗：エラー表示
            const errorMsg = document.getElementById(tempId);
            if (errorMsg) {
                errorMsg.querySelector('.msg-status').innerHTML = '<i class="fas fa-exclamation-circle text-red-500"></i>';
                errorMsg.querySelector('.msg-status').classList.remove('sending');
            }
        } finally {
            submitBtn.disabled = false;
            messageInput.focus();
        }
    });

    // キーボード補正
    messageInput.addEventListener('focus', () => {
        setTimeout(() => scrollToBottom('smooth'), 300);
    });
});