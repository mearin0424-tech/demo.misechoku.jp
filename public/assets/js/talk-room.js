/**
 * Talk Room Logic
 * 最新メッセージへのスクロールと擬似送信処理
 */

document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    
    if (!chatMessages || !chatForm) return;

    const messageInput = chatForm.querySelector('input[type="text"]');

    /**
     * 一番下までスクロールさせる関数
     */
    const scrollToBottom = (behavior = 'auto') => {
        chatMessages.scrollTo({
            top: chatMessages.scrollHeight,
            behavior: behavior
        });
    };

    // 1. 初期表示時に一番下へ
    scrollToBottom();

    // 2. 送信処理（モック）
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        if (!content) return;

        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                        now.getMinutes().toString().padStart(2, '0');

        // メッセージ要素の生成
        const messageHtml = `
            <div class="flex justify-end msg-right">
                <div class="message-bubble">
                    <p>${content}</p>
                    <span class="msg-time">${timeStr}</span>
                </div>
            </div>
        `;

        chatMessages.insertAdjacentHTML('beforeend', messageHtml);
        messageInput.value = '';
        messageInput.focus();

        // 送信後は滑らかにスクロール
        scrollToBottom('smooth');
    });

    // 3. キーボード表示時の位置補正
    messageInput.addEventListener('focus', () => {
        setTimeout(() => scrollToBottom('smooth'), 300);
    });
});