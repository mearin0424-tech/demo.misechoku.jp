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

        // 1. サーバー送信前に画面に「送信中」状態で追加（吹き出しと footer は別ブロック）
        const messageHtml = `
            <div class="message-row msg-right" id="${tempId}">
                <div class="message-block">
                    <div class="message-bubble">
                        <p class="m-0">${content.replace(/\n/g, '<br>')}</p>
                    </div>
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

    // ===============================
    // 面談日候補モーダル（店舗側のみ）
    // ===============================
    const isCastRoom = typeof window.isCastTalkRoom !== 'undefined' ? !!window.isCastTalkRoom : false;
    const partnerId = chatForm.getAttribute('data-partner-id');
    const statusStorageKey = isCastRoom ? 'talk_recruit_status_cast' : 'talk_recruit_status_shop';

    function loadStatusMap() {
        try {
            const raw = localStorage.getItem(statusStorageKey);
            if (!raw) return {};
            const parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (e) {
            return {};
        }
    }

    function saveStatusMap(map) {
        try {
            localStorage.setItem(statusStorageKey, JSON.stringify(map));
        } catch (e) {
            // ignore
        }
    }

    function setRecruitStatus(code) {
        if (!partnerId) return;
        const map = loadStatusMap();
        map[String(partnerId)] = code;
        saveStatusMap(map);
    }
    if (!isCastRoom) {
        const openInterviewBtn = document.getElementById('open-interview-modal');
        const overlay = document.getElementById('interview-modal-overlay');
        const interviewForm = overlay ? overlay.querySelector('#interview-form') : null;
        const closeBtn = overlay ? overlay.querySelector('.interview-modal-close') : null;
        const cancelBtn = overlay ? overlay.querySelector('.btn-interview-cancel') : null;
        const submitBtn = overlay ? overlay.querySelector('.btn-interview-submit') : null;
        const hireBtn = document.getElementById('send-hire-message');
        const rejectBtn = document.getElementById('send-reject-message');

        const openModal = () => {
            if (!overlay) return;
            overlay.setAttribute('aria-hidden', 'false');
        };

        const closeModal = () => {
            if (!overlay) return;
            overlay.setAttribute('aria-hidden', 'true');
        };

        if (openInterviewBtn && overlay) {
            openInterviewBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal();
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeModal();
            });
        }
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    closeModal();
                }
            });
        }

        // 採用・不採用メッセージ（店舗側のみ）
        if (hireBtn) {
            hireBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const template = 'この度は面談ありがとうございました。\n採用で進めさせていただきたいと考えております。詳細は追ってご連絡いたします。';
                messageInput.value = template;
                autoResize();
                setRecruitStatus('hired');
                chatForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            });
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const template = 'この度はご応募ありがとうございました。\n慎重に検討させていただいた結果、今回は見送らせていただくこととなりました。またご縁がございましたらよろしくお願いいたします。';
                messageInput.value = template;
                autoResize();
                setRecruitStatus('rejected');
                chatForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            });
        }

        function formatDateTimeLocal(value) {
            if (!value) return '';
            try {
                const d = new Date(value);
                if (isNaN(d.getTime())) return value;
                const y = d.getFullYear();
                const m = d.getMonth() + 1;
                const day = d.getDate();
                const hour = d.getHours();
                const min = String(d.getMinutes()).padStart(2, '0');
                return `${y}年${m}月${day}日 ${hour}:${min}`;
            } catch (e) {
                return value;
            }
        }

        if (interviewForm && submitBtn) {
            interviewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(interviewForm);
                const rawOptions = [
                    formData.get('option1'),
                    formData.get('option2'),
                    formData.get('option3'),
                ].filter(Boolean);

                if (rawOptions.length === 0) {
                    return;
                }

                submitBtn.disabled = true;

                const now = new Date();
                const timeStr = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0');
                const offerId = 'interview-' + Date.now();

                const optionsHtml = rawOptions.map((v, idx) => {
                    const label = formatDateTimeLocal(v);
                    const indexLabel = `候補${idx + 1}`;
                    return `
                        <li>
                            <button type="button" class="interview-option-btn" data-option-label="${label}">
                                <span>${indexLabel}：${label}</span>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </li>
                    `;
                }).join('');

                const bubbleHtml = `
                    <div class="message-row msg-right interview-offer" data-offer-id="${offerId}">
                        <div class="message-block">
                            <div class="message-bubble message-bubble-interview">
                                <div class="interview-title">
                                    <i class="far fa-calendar-alt"></i>
                                    <span>面談候補日を送りました</span>
                                </div>
                                <ul class="interview-option-list">
                                    ${optionsHtml}
                                </ul>
                                <p class="interview-note">※ デモ画面のため、このまま候補をタップすると「確定メッセージ」が表示されます。</p>
                            </div>
                            <div class="msg-footer">
                                <span class="msg-time">${timeStr}</span>
                                <span class="msg-status"><i class="fas fa-check"></i></span>
                            </div>
                        </div>
                    </div>
                `;

                chatMessages.insertAdjacentHTML('beforeend', bubbleHtml);
                scrollToBottom('smooth');

                // ステータス：面談調整中
                setRecruitStatus('interview_pending');

                // 入力リセット
                interviewForm.reset();
                submitBtn.disabled = false;
                closeModal();
            });

            // 候補選択（デモ用：どちらの画面からでも選択すると確定メッセージを追加）
            chatMessages.addEventListener('click', function(e) {
                const btn = e.target.closest('.interview-option-btn');
                if (!btn) return;
                const offerRow = btn.closest('.interview-offer');
                if (!offerRow) return;

                // すでに選択済みなら何もしない
                if (offerRow.dataset.selected === 'true') return;

                const label = btn.getAttribute('data-option-label') || '';

                // ボタンの見た目更新
                offerRow.dataset.selected = 'true';
                offerRow.querySelectorAll('.interview-option-btn').forEach(function(b) {
                    b.classList.toggle('is-selected', b === btn);
                    b.disabled = true;
                });

                const confirmHtml = `
                    <div class="message-row msg-left">
                        <div class="message-block">
                            <div class="message-bubble message-bubble-interview">
                                <div class="interview-title">
                                    <i class="far fa-calendar-check"></i>
                                    <span>面談日が確定しました</span>
                                </div>
                                <p class="m-0" style="font-size:0.8rem;">${label}</p>
                            </div>
                            <div class="msg-footer">
                                <span class="msg-time">${new Date().getHours()}:${String(new Date().getMinutes()).padStart(2, '0')}</span>
                            </div>
                        </div>
                    </div>
                `;
                chatMessages.insertAdjacentHTML('beforeend', confirmHtml);
                scrollToBottom('smooth');

                // ステータス：面談日決定
                setRecruitStatus('interview_fixed');
            });
        }
    }
});