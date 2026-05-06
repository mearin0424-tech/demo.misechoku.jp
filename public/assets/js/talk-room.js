/**
 * Talk Room Logic (Real-time update with Server)
 */
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const isCastRoom = typeof window.isCastTalkRoom !== 'undefined' ? !!window.isCastTalkRoom : false;

    if (!chatMessages) return;

    const messageInput = chatForm ? chatForm.querySelector('textarea[name="message"]') : null;
    const talkTopicSelect = chatForm ? chatForm.querySelector('select[name="talk_topic"]') : null;
    const talkJobKindSelect = chatForm ? chatForm.querySelector('select[name="talk_job_kind"]') : null;
    const initialTalkTopic = typeof window.initialTalkTopic !== 'undefined' ? window.initialTalkTopic : null;
    const initialTalkJobKind = typeof window.initialTalkJobKind !== 'undefined' ? window.initialTalkJobKind : null;
    const hasTalkMessages = typeof window.hasTalkMessages !== 'undefined' ? !!window.hasTalkMessages : false;
    const selectedTalkJobKind = typeof window.selectedTalkJobKind !== 'undefined' ? window.selectedTalkJobKind : null;
    const canSelectTalkJobKind = typeof window.canSelectTalkJobKind !== 'undefined' ? !!window.canSelectTalkJobKind : false;

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
        if (talkTopicSelect && initialTalkTopic) {
            talkTopicSelect.value = initialTalkTopic;
        }
        if (talkJobKindSelect && initialTalkJobKind) {
            talkJobKindSelect.value = initialTalkJobKind;
        }
        if (talkTopicSelect && talkJobKindSelect) {
            const talkJobKindWrap = document.getElementById('talk-job-kind-wrap');
            const syncTalkJobKindVisibility = () => {
                const hidden = talkTopicSelect.value === 'other';
                talkJobKindSelect.disabled = hidden;
                if (talkJobKindWrap) {
                    talkJobKindWrap.style.display = hidden ? 'none' : '';
                }
            };
            talkTopicSelect.addEventListener('change', syncTalkJobKindVisibility);
            syncTalkJobKindVisibility();
        }
        messageInput.addEventListener('input', autoResize);

        let isSubmitting = false;
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const content = messageInput.value.trim();
            if (!content) return;
            if (isSubmitting) return;
            isSubmitting = true;

            const url = chatForm.getAttribute('data-url');
            const partnerId = chatForm.getAttribute('data-partner-id');
            const token = chatForm.querySelector('input[name="_token"]').value;
            const submitBtn = chatForm.querySelector('button');
            submitBtn.disabled = true;

            const tempId = 'msg-' + Date.now();
            const now = new Date();
            const timeStr = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0');
            const normalizedContent = content.replace(/\r\n/g, '\n').replace(/\r/g, '\n').replace(/\n{2,}/g, '\n').trim();
            const bubbleHtml = '<p class="m-0">' + escapeHtml(normalizedContent).replace(/\n/g, '<br>') + '</p><span class="message-bubble-tail" aria-hidden="true"><svg viewBox="0 0 8 12" fill="currentColor"><path d="M0 0V12C3 12 8 8 8 0H0Z"/></svg></span>';
            const messageHtml = '<div class="message-row msg-right" id="' + tempId + '"><div class="message-block"><div class="message-inline"><div class="msg-meta"><span class="msg-status sending"><i class="fas fa-check"></i></span><span class="msg-time">' + timeStr + '</span></div><div class="message-bubble">' + bubbleHtml + '</div></div></div></div>';
            const emptyState = chatMessages.querySelector('.talk-empty-state');
            if (emptyState) emptyState.remove();
            chatMessages.insertAdjacentHTML('beforeend', messageHtml);

            messageInput.value = '';
            autoResize();
            scrollToBottom('smooth');

            try {
                const payload = { partner_id: partnerId, message: content };
                if (isCastRoom && !hasTalkMessages && talkTopicSelect) {
                    payload.talk_topic = talkTopicSelect.value;
                    if (talkJobKindSelect && !talkJobKindSelect.disabled) {
                        payload.talk_job_kind = talkJobKindSelect.value;
                    }
                }
                const result = await postJson(url, token, payload);
                if (!result.success) {
                    throw new Error('Failed');
                }
                const sentMsg = document.getElementById(tempId);
                if (sentMsg) {
                    sentMsg.querySelector('.msg-status').classList.remove('sending');
                    if (result.data && result.data.message_id) {
                        sentMsg.dataset.messageId = String(result.data.message_id);
                        const meta = sentMsg.querySelector('.msg-meta');
                        if (meta) {
                            const deleteBtn = document.createElement('button');
                            deleteBtn.type = 'button';
                            deleteBtn.className = 'msg-delete-btn';
                            deleteBtn.dataset.messageId = String(result.data.message_id);
                            deleteBtn.title = '削除';
                            deleteBtn.setAttribute('aria-label', 'メッセージを削除');
                            deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                            meta.insertBefore(deleteBtn, meta.firstChild);
                        }
                    }
                }
            } catch (error) {
                const errorMsg = document.getElementById(tempId);
                if (errorMsg) {
                    errorMsg.querySelector('.msg-status').innerHTML = '<i class="fas fa-exclamation-circle text-red-500"></i>';
                    errorMsg.querySelector('.msg-status').classList.remove('sending');
                }
            } finally {
                isSubmitting = false;
                submitBtn.disabled = false;
                messageInput.focus();
            }
        });

        messageInput.addEventListener('focus', () => {
            setTimeout(() => scrollToBottom('smooth'), 300);
        });
    }

    // メッセージ削除（10分以内の自分のテキストメッセージのみ）
    chatMessages.addEventListener('click', async function(e) {
        const btn = e.target.closest('.msg-delete-btn');
        if (!btn) return;
        e.preventDefault();
        const messageId = btn.dataset.messageId;
        if (!messageId) return;
        const deleteUrl = chatMessages.getAttribute('data-delete-url');
        if (!deleteUrl || !chatForm) return;
        const partnerId = chatForm.getAttribute('data-partner-id');
        const token = chatForm.querySelector('input[name="_token"]').value;
        if (!window.confirm('このメッセージを削除しますか？')) return;
        const row = btn.closest('.message-row');
        btn.disabled = true;
        try {
            await postJson(deleteUrl, token, { partner_id: partnerId, message_id: messageId });
            if (row) {
                row.remove();
                if (chatMessages.querySelectorAll('.message-row').length === 0) {
                    chatMessages.insertAdjacentHTML('afterbegin',
                        '<div class="text-center text-gray-500 mt-20 talk-empty-state">' +
                        '<i class="fas fa-comments opacity-10 text-6xl mb-4 block"></i>' +
                        '<p>メッセージはまだありません</p></div>');
                }
            }
        } catch (err) {
            window.alert(err.message || '削除に失敗しました。');
            btn.disabled = false;
        }
    });

    // ===============================
    // 面談日候補モーダル（店舗側のみ）
    // ===============================
    const partnerId = chatForm ? chatForm.getAttribute('data-partner-id') : null;
    const talkRoomJobKindSelect = document.getElementById('talk-room-job-kind');
    const saveTalkJobKindBtn = document.getElementById('save-talk-job-kind');
    if (talkRoomJobKindSelect && selectedTalkJobKind) {
        talkRoomJobKindSelect.value = selectedTalkJobKind;
    }

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
        const cancelStatusBtn = document.getElementById('send-cancel-status');
        const resultOverlay = document.getElementById('result-message-overlay');
        const resultTitle = document.getElementById('result-message-title');
        const resultDesc = document.getElementById('result-message-desc');
        const resultTemplateList = document.getElementById('result-template-list');
        const resultTextarea = document.getElementById('result-message-textarea');
        const resultSubmitBtn = document.getElementById('result-message-submit');
        const resultCloseButtons = document.querySelectorAll('.js-result-message-close');
        const resultTemplates = window.talkResultMessageTemplates || {};
        const hiredHourlyWageWrap = document.getElementById('hired-hourly-wage-wrap');
        const hiredHourlyWageInput = document.getElementById('hired-hourly-wage-input');
        const resultEmploymentKind = document.getElementById('result-employment-kind');
        let currentResultAction = null;
        if (talkRoomJobKindSelect && saveTalkJobKindBtn && canSelectTalkJobKind) {
            saveTalkJobKindBtn.addEventListener('click', async function() {
                const selected = talkRoomJobKindSelect.value;
                if (!selected) {
                    window.alert('求人種別を選択してください。');
                    return;
                }
                saveTalkJobKindBtn.disabled = true;
                try {
                    await postJson(actionUrl, token, {
                        partner_id: partnerId,
                        action_type: 'set_job_kind',
                        job_kind: selected
                    });
                    window.alert('求人種別を保存しました。');
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || '求人種別の保存に失敗しました。');
                    saveTalkJobKindBtn.disabled = false;
                }
            });
        }

        const openModal = () => {
            if (!overlay) return;
            overlay.setAttribute('aria-hidden', 'false');
        };

        const closeModal = () => {
            if (!overlay) return;
            overlay.setAttribute('aria-hidden', 'true');
        };

        const renderResultTemplates = (actionType) => {
            if (!resultTemplateList) return;
            resultTemplateList.innerHTML = '';
            (resultTemplates[actionType] || []).forEach(function(template) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'result-template-button';
                button.textContent = template.title || 'テンプレート';
                button.addEventListener('click', function() {
                    if (resultTextarea) {
                        resultTextarea.value = template.body || '';
                        resultTextarea.focus();
                    }
                });
                resultTemplateList.appendChild(button);
            });
        };

        const openResultModal = (actionType) => {
            if (!resultOverlay || !resultTextarea || !resultSubmitBtn) return;
            currentResultAction = actionType;
            renderResultTemplates(actionType);
            if (resultTitle) {
                resultTitle.textContent = actionType === 'hired' ? '採用メッセージを送信' : '不採用メッセージを送信';
            }
            if (resultDesc) {
                resultDesc.textContent = actionType === 'hired'
                    ? '採用時給を入力し、採用テンプレートを選択して文面を編集してください。'
                    : '不採用テンプレートを選択し、必要に応じて文面を編集してください。';
            }
            if (hiredHourlyWageWrap) {
                const show = actionType === 'hired';
                hiredHourlyWageWrap.classList.toggle('is-visible', show);
                hiredHourlyWageWrap.setAttribute('aria-hidden', show ? 'false' : 'true');
            }
            const defaults = resultTemplates[actionType] || [];
            resultTextarea.value = defaults[0] && defaults[0].body ? defaults[0].body : '';
            resultOverlay.setAttribute('aria-hidden', 'false');
            resultSubmitBtn.disabled = false;
            setTimeout(function() { resultTextarea.focus(); }, 0);
        };

        const closeResultModal = () => {
            if (!resultOverlay || !resultTextarea) return;
            resultOverlay.setAttribute('aria-hidden', 'true');
            currentResultAction = null;
            resultTextarea.value = '';
            if (hiredHourlyWageInput) {
                hiredHourlyWageInput.value = '';
            }
            if (hiredHourlyWageWrap) {
                hiredHourlyWageWrap.classList.remove('is-visible');
                hiredHourlyWageWrap.setAttribute('aria-hidden', 'true');
            }
            if (resultSubmitBtn) {
                resultSubmitBtn.disabled = false;
            }
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

        resultCloseButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                closeResultModal();
            });
        });

        if (resultOverlay) {
            resultOverlay.addEventListener('click', function(e) {
                if (e.target === resultOverlay) {
                    closeResultModal();
                }
            });
        }

        // 採用・不採用メッセージ（店舗側のみ）
        if (hireBtn) {
            hireBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openResultModal('hired');
            });
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openResultModal('rejected');
            });
        }

        if (resultSubmitBtn && resultTextarea) {
            resultSubmitBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                if (!currentResultAction) return;

                const message = resultTextarea.value.trim();
                if (!message) {
                    window.alert('送信メッセージを入力してください。');
                    return;
                }

                resultSubmitBtn.disabled = true;
                try {
                    const payload = {
                        partner_id: partnerId,
                        action_type: currentResultAction,
                        message: message
                    };
                    if (resultEmploymentKind) {
                        payload.employment_kind = resultEmploymentKind.value;
                    }
                    if (currentResultAction === 'hired' && hiredHourlyWageInput) {
                        payload.hired_regular_hourly_wage = hiredHourlyWageInput.value.trim();
                    }
                    await postJson(actionUrl, token, payload);
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || '結果メッセージの送信に失敗しました。');
                    resultSubmitBtn.disabled = false;
                }
            });
        }

        if (cancelStatusBtn) {
            cancelStatusBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                if (!window.confirm('現在の面談ステータスをキャンセルして、やり取り中に戻しますか？')) {
                    return;
                }
                cancelStatusBtn.disabled = true;
                try {
                    await postJson(actionUrl, token, { partner_id: partnerId, action_type: 'cancel_status' });
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || 'ステータスのキャンセルに失敗しました。');
                    cancelStatusBtn.disabled = false;
                }
            });
        }

        document.querySelectorAll('.js-interview-change-schedule').forEach(function(btn) {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                if (!window.confirm('現在の面談日をキャンセルして、別の候補日を送信しますか？')) return;
                btn.disabled = true;
                try {
                    await postJson(actionUrl, token, { partner_id: partnerId, action_type: 'cancel_status' });
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || 'キャンセルに失敗しました。');
                    btn.disabled = false;
                }
            });
        });

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

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            if (overlay && overlay.getAttribute('aria-hidden') === 'false') {
                closeModal();
            }
            if (resultOverlay && resultOverlay.getAttribute('aria-hidden') === 'false') {
                closeResultModal();
            }
        });
    }

    if (isCastRoom && chatForm) {
        const actionUrl = chatForm.getAttribute('data-action-url');
        const token = chatForm.querySelector('input[name="_token"]').value;
        const confirmOverlay = document.getElementById('interview-confirm-overlay');
        const confirmSelected = document.getElementById('interview-confirm-selected');
        const confirmSubmitBtn = document.getElementById('interview-confirm-submit');
        const confirmCloseButtons = document.querySelectorAll('.js-interview-confirm-close');
        let pendingInterviewSelection = null;
        if (talkRoomJobKindSelect && saveTalkJobKindBtn && canSelectTalkJobKind) {
            saveTalkJobKindBtn.addEventListener('click', async function() {
                const selected = talkRoomJobKindSelect.value;
                if (!selected) {
                    window.alert('求人種別を選択してください。');
                    return;
                }
                saveTalkJobKindBtn.disabled = true;
                try {
                    await postJson(actionUrl, token, {
                        partner_id: partnerId,
                        action_type: 'set_job_kind',
                        job_kind: selected
                    });
                    window.alert('求人種別を保存しました。');
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || '求人種別の保存に失敗しました。');
                    saveTalkJobKindBtn.disabled = false;
                }
            });
        }

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