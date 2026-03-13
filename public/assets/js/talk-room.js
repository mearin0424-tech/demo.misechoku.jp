/**
 * Talk Room Logic (Real-time update with Server)
 */
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const isCastRoom = typeof window.isCastTalkRoom !== 'undefined' ? !!window.isCastTalkRoom : false;

    if (!chatMessages) return;

    const messageInput = chatForm ? chatForm.querySelector('textarea[name="message"]') : null;

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

    scrollToBottom();
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function postJson(url, token, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            let message = 'Request failed';
            try {
                const errorData = await response.json();
                message = errorData.message || message;
            } catch (e) {
                // JSON で返らないケースは共通メッセージにフォールバックする
            }
            throw new Error(message);
        }

        return response.json();
    }

    if (chatForm && messageInput) {
        messageInput.addEventListener('input', autoResize);

        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const content = messageInput.value.trim();
            if (!content) return;

            const url = chatForm.getAttribute('data-url');
            const partnerId = chatForm.getAttribute('data-partner-id');
            const token = chatForm.querySelector('input[name="_token"]').value;
            const submitBtn = chatForm.querySelector('button');
            submitBtn.disabled = true;

            const tempId = 'msg-' + Date.now();
            const now = new Date();
            const timeStr = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0');
            const messageHtml = `
                <div class="message-row msg-right" id="${tempId}">
                    <div class="message-block">
                        <div class="message-bubble">
                            <p class="m-0">${escapeHtml(content).replace(/\n/g, '<br>')}</p>
                        </div>
                        <div class="msg-footer">
                            <span class="msg-time">${timeStr}</span>
                            <span class="msg-status sending"><i class="fas fa-check"></i></span>
                        </div>
                    </div>
                </div>
            `;
            chatMessages.insertAdjacentHTML('beforeend', messageHtml);

            messageInput.value = '';
            autoResize();
            scrollToBottom('smooth');

            try {
                const result = await postJson(url, token, { partner_id: partnerId, message: content });
                if (!result.success) {
                    throw new Error('Failed');
                }
                const sentMsg = document.getElementById(tempId);
                if (sentMsg) {
                    sentMsg.querySelector('.msg-status').classList.remove('sending');
                }
            } catch (error) {
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

        messageInput.addEventListener('focus', () => {
            setTimeout(() => scrollToBottom('smooth'), 300);
        });
    }

    // ===============================
    // 面談日候補モーダル（店舗側のみ）
    // ===============================
    const partnerId = chatForm ? chatForm.getAttribute('data-partner-id') : null;

    if (!isCastRoom && chatForm) {
        const actionUrl = chatForm.getAttribute('data-action-url');
        const token = chatForm.querySelector('input[name="_token"]').value;
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
            hireBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                if (!window.confirm('このユーザーを採用として送信しますか？')) {
                    return;
                }
                hireBtn.disabled = true;
                try {
                    await postJson(actionUrl, token, { partner_id: partnerId, action_type: 'hired' });
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || '採用メッセージの送信に失敗しました。');
                    hireBtn.disabled = false;
                }
            });
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                if (!window.confirm('このユーザーを不採用として送信しますか？')) {
                    return;
                }
                rejectBtn.disabled = true;
                try {
                    await postJson(actionUrl, token, { partner_id: partnerId, action_type: 'rejected' });
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || '不採用メッセージの送信に失敗しました。');
                    rejectBtn.disabled = false;
                }
            });
        }

        if (interviewForm && submitBtn) {
            interviewForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(interviewForm);
                const options = [
                    formData.get('option1'),
                    formData.get('option2'),
                    formData.get('option3'),
                ].map(function(v) {
                    return v ? String(v).replace('T', ' ') : '';
                }).filter(Boolean);

                if (options.length === 0) {
                    window.alert('面談候補日を1件以上入力してください。');
                    return;
                }

                submitBtn.disabled = true;
                try {
                    await postJson(actionUrl, token, {
                        partner_id: partnerId,
                        action_type: 'interview_offer',
                        options: options
                    });
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || '面談候補日の送信に失敗しました。');
                    submitBtn.disabled = false;
                }
            });
        }
    }

    if (isCastRoom && chatForm) {
        const actionUrl = chatForm.getAttribute('data-action-url');
        const token = chatForm.querySelector('input[name="_token"]').value;
        const confirmOverlay = document.getElementById('interview-confirm-overlay');
        const confirmSelected = document.getElementById('interview-confirm-selected');
        const confirmSubmitBtn = document.getElementById('interview-confirm-submit');
        const confirmCloseButtons = document.querySelectorAll('.js-interview-confirm-close');
        let pendingInterviewSelection = null;

        const openConfirmModal = (selection) => {
            if (!confirmOverlay || !confirmSelected) return;
            pendingInterviewSelection = selection;
            confirmSelected.textContent = selection.displayLabel || selection.selectedOption;
            confirmOverlay.setAttribute('aria-hidden', 'false');
        };

        const closeConfirmModal = () => {
            if (!confirmOverlay) return;
            confirmOverlay.setAttribute('aria-hidden', 'true');
            pendingInterviewSelection = null;
            if (confirmSubmitBtn) {
                confirmSubmitBtn.disabled = false;
            }
        };

        confirmCloseButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                closeConfirmModal();
            });
        });

        if (confirmOverlay) {
            confirmOverlay.addEventListener('click', function(e) {
                if (e.target === confirmOverlay) {
                    closeConfirmModal();
                }
            });
        }

        if (confirmSubmitBtn) {
            confirmSubmitBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                if (!pendingInterviewSelection) return;

                confirmSubmitBtn.disabled = true;
                try {
                    await postJson(actionUrl, token, {
                        partner_id: partnerId,
                        action_type: 'interview_confirm',
                        offer_token: pendingInterviewSelection.offerToken,
                        selected_option: pendingInterviewSelection.selectedOption
                    });
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || '面談日の確定に失敗しました。');
                    confirmSubmitBtn.disabled = false;
                }
            });
        }

        chatMessages.addEventListener('click', async function(e) {
            const btn = e.target.closest('.interview-option-btn');
            if (!btn) return;
            e.preventDefault();

            const offerToken = btn.getAttribute('data-offer-token');
            const selectedOption = btn.getAttribute('data-option-label');
            const displayLabel = btn.getAttribute('data-option-display');
            if (!offerToken || !selectedOption) return;

            openConfirmModal({
                offerToken: offerToken,
                selectedOption: selectedOption,
                displayLabel: displayLabel
            });
        });
    }
});