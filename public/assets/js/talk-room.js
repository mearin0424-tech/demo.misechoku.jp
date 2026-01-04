/**
 * Talk Room Logic (Real-time update with Server)
 */
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    
    if (!chatMessages || !chatForm) return;

    const messageInput = chatForm.querySelector('input[name="message"]');

    const scrollToBottom = (behavior = 'auto') => {
        chatMessages.scrollTo({
            top: chatMessages.scrollHeight,
            behavior: behavior
        });
    };

    // 初期表示で最下部へ
    scrollToBottom();

    // テキストエリアの自動リサイズ
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // 送信処理
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        if (!content) return;

        // フォームから設定情報を取得
        const url = chatForm.getAttribute('data-url');
        const partnerId = chatForm.getAttribute('data-partner-id');
        const token = chatForm.querySelector('input[name="_token"]').value;

        // ボタンを一時的に無効化（二重送信防止）
        const submitBtn = chatForm.querySelector('button');
        submitBtn.disabled = true;

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
                // サーバーから返ってきた時間を表示（なければ現在時刻）
                const timeStr = result.data ? result.data.time : new Date().getHours() + ':' + new Date().getMinutes();

                // UIにメッセージを追加
                const messageHtml = `
                    <div class="flex justify-end msg-right">
                        <div class="message-bubble">
                            <p>${content}</p>
                            <span class="msg-time">${timeStr}</span>
                        </div>
                    </div>
                `;
                chatMessages.insertAdjacentHTML('beforeend', messageHtml);
                
                // 入力欄をクリア
                messageInput.value = '';
                scrollToBottom('smooth');
            } else {
                alert('送信に失敗しました。');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('通信エラーが発生しました。');
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