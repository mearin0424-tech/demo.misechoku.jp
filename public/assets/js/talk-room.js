/**
 * Talk Room Logic (Real-time update with Server)
 */
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const isCastRoom = typeof window.isCastTalkRoom !== 'undefined' ? !!window.isCastTalkRoom : false;

    if (!chatMessages) return;

    const messageInput = chatForm ? chatForm.querySelector('textarea[name="message"]') : null;
    const imageInput = chatForm ? chatForm.querySelector('#talk-image-input') : null;
    const talkTopicField = chatForm ? chatForm.querySelector('[name="talk_topic"]') : null;
    const talkJobKindField = chatForm ? chatForm.querySelector('[name="talk_job_kind"]') : null;
    const initialTalkTopic = typeof window.initialTalkTopic !== 'undefined' ? window.initialTalkTopic : null;
    const initialTalkJobKind = typeof window.initialTalkJobKind !== 'undefined' ? window.initialTalkJobKind : null;
    const hasTalkMessages = typeof window.hasTalkMessages !== 'undefined' ? !!window.hasTalkMessages : false;
    const selectedTalkJobKind = typeof window.selectedTalkJobKind !== 'undefined' ? window.selectedTalkJobKind : null;
    const canSelectTalkJobKind = typeof window.canSelectTalkJobKind !== 'undefined' ? !!window.canSelectTalkJobKind : false;
    const currentTalkStatusCode = typeof window.currentTalkStatusCode !== 'undefined' ? window.currentTalkStatusCode : 'chatting';
    const talkJobKindCurrent = document.getElementById('talk-job-kind-current');

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

    function ensureEmptyStateRemoved() {
        const emptyState = chatMessages.querySelector('.talk-empty-state');
        if (emptyState) emptyState.remove();
    }

    function appendImageMessage(url, caption, timeStr) {
        const safeUrl = escapeHtml(url || '');
        const safeCaption = escapeHtml((caption || '').trim()).replace(/\n/g, '<br>');
        const captionHtml = safeCaption ? ('<p class="message-image-caption">' + safeCaption + '</p>') : '';
        const messageHtml = '<div class="message-row msg-right"><div class="message-block"><div class="message-inline"><div class="msg-meta"><span class="msg-status"><i class="fas fa-check"></i></span><span class="msg-time">' + timeStr + '</span></div><div class="message-bubble message-bubble-image"><a href="' + safeUrl + '" target="_blank" rel="noopener noreferrer" class="message-image-link"><img src="' + safeUrl + '" alt="送信画像" class="message-image"></a>' + captionHtml + '</div></div></div></div>';
        ensureEmptyStateRemoved();
        chatMessages.insertAdjacentHTML('beforeend', messageHtml);
        scrollToBottom('smooth');
    }

    // 共通フォールバック: どの分岐でも + メニューを開閉できるようにする
    const globalActionMenuBtn = document.getElementById('open-talk-action-menu');
    const globalActionMenuOverlay = document.getElementById('talk-action-menu-overlay');
    const globalActionMenuCloseButtons = document.querySelectorAll('.js-talk-action-menu-close');
    if (globalActionMenuBtn && globalActionMenuOverlay) {
        globalActionMenuBtn.addEventListener('click', function (e) {
            e.preventDefault();
            globalActionMenuOverlay.setAttribute('aria-hidden', 'false');
        });
    }
    globalActionMenuCloseButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (globalActionMenuOverlay) globalActionMenuOverlay.setAttribute('aria-hidden', 'true');
        });
    });
    if (globalActionMenuOverlay) {
        globalActionMenuOverlay.addEventListener('click', function (e) {
            if (e.target === globalActionMenuOverlay) {
                globalActionMenuOverlay.setAttribute('aria-hidden', 'true');
            }
        });
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
        if (talkTopicField && initialTalkTopic) {
            talkTopicField.value = initialTalkTopic;
        }
        if (talkJobKindField && initialTalkJobKind) {
            talkJobKindField.value = initialTalkJobKind;
        }
        if (talkTopicField && talkJobKindField && talkTopicField.tagName === 'SELECT') {
            const talkJobKindWrap = document.getElementById('talk-job-kind-wrap');
            const syncTalkJobKindVisibility = () => {
                const hidden = talkTopicField.value === 'other';
                talkJobKindField.disabled = hidden;
                if (talkJobKindWrap) {
                    talkJobKindWrap.style.display = hidden ? 'none' : '';
                }
            };
            talkTopicField.addEventListener('change', syncTalkJobKindVisibility);
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
            ensureEmptyStateRemoved();
            chatMessages.insertAdjacentHTML('beforeend', messageHtml);

            messageInput.value = '';
            autoResize();
            scrollToBottom('smooth');

            try {
                const payload = { partner_id: partnerId, message: content };
                if (isCastRoom && !hasTalkMessages && talkTopicField) {
                    payload.talk_topic = talkTopicField.value;
                    if (talkJobKindField && !talkJobKindField.disabled) {
                        payload.talk_job_kind = talkJobKindField.value;
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
    const talkJobKindSaveStatus = document.getElementById('talk-job-kind-save-status');
    const talkJobKindGuidance = document.getElementById('talk-job-kind-guidance');
    let currentSavedTalkJobKind = selectedTalkJobKind || '';
    const talkKindLabelMap = {
        trial: '体験入店',
        fulltime: '本入店',
        help: 'ヘルプ'
    };
    const renderTalkKindGuidance = (kind) => {
        if (!kind) {
            if (talkJobKindGuidance) {
                talkJobKindGuidance.textContent = '面談日を送る前に求人種別を確定してください。面談日確定後は変更できません。';
            }
            if (talkJobKindCurrent) talkJobKindCurrent.textContent = '未選択';
            return;
        }
        const label = talkKindLabelMap[kind] || '未選択';
        if (talkJobKindGuidance) {
            talkJobKindGuidance.textContent = '現在の求人種別: ' + label + '。面談日確定後は変更できません。';
        }
        if (talkJobKindCurrent) talkJobKindCurrent.textContent = label;
    };
    if (talkRoomJobKindSelect && selectedTalkJobKind) {
        talkRoomJobKindSelect.value = selectedTalkJobKind;
        if (talkJobKindSaveStatus) {
            talkJobKindSaveStatus.textContent = '保存済み';
        }
    }
    renderTalkKindGuidance(talkRoomJobKindSelect ? talkRoomJobKindSelect.value : currentSavedTalkJobKind);
    if (talkRoomJobKindSelect) {
        talkRoomJobKindSelect.addEventListener('change', function () {
            renderTalkKindGuidance(talkRoomJobKindSelect.value);
            if (!saveTalkJobKindBtn || !canSelectTalkJobKind) return;
            if (talkRoomJobKindSelect.value && talkRoomJobKindSelect.value === currentSavedTalkJobKind) {
                if (talkJobKindSaveStatus) talkJobKindSaveStatus.textContent = '保存済み';
            } else {
                if (talkJobKindSaveStatus) talkJobKindSaveStatus.textContent = '未保存';
            }
        });
    }

    if (!isCastRoom && chatForm) {
        const actionUrl = chatForm.getAttribute('data-action-url');
        const token = chatForm.querySelector('input[name="_token"]').value;
        const openInterviewBtn = document.getElementById('open-interview-modal');
        const openTalkActionMenuBtn = document.getElementById('open-talk-action-menu');
        const talkActionMenuOverlay = document.getElementById('talk-action-menu-overlay');
        const talkActionMenuCloseButtons = document.querySelectorAll('.js-talk-action-menu-close');
        const openJobKindModalBtn = document.getElementById('open-job-kind-modal');
        const jobKindOverlay = document.getElementById('job-kind-modal-overlay');
        const closeJobKindButtons = document.querySelectorAll('.js-job-kind-close');
        const openImageSendMenu = document.getElementById('open-image-send-menu');
        const openTemplateSendMenu = document.getElementById('open-template-send-menu');
        const openWorkCompleteReportMenu = document.getElementById('open-work-complete-report-menu');
        const templateMenuOverlay = document.getElementById('talk-template-menu-overlay');
        const templateMenuList = document.getElementById('talk-template-menu-list');
        const templateMenuCloseButtons = document.querySelectorAll('.js-talk-template-close');
        const overlay = document.getElementById('interview-modal-overlay');
        const interviewForm = overlay ? overlay.querySelector('#interview-form') : null;
        const closeBtn = overlay ? overlay.querySelector('.interview-modal-close') : null;
        const cancelBtn = overlay ? overlay.querySelector('.btn-interview-cancel') : null;
        const submitBtn = overlay ? overlay.querySelector('.btn-interview-submit') : null;
        const hireBtn = document.getElementById('send-hire-message');
        const rejectBtn = document.getElementById('send-reject-message');
        const cancelStatusBtn = document.getElementById('send-cancel-status');
        const openHireModalFromMenu = document.getElementById('open-hire-modal-menu');
        const openRejectModalFromMenu = document.getElementById('open-reject-modal-menu');
        const openCancelStatusFromMenu = document.getElementById('open-cancel-status-menu');
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
        const quickTemplates = [
            'ありがとうございます。内容を確認して折り返します。',
            '承知しました。よろしくお願いします。',
            '本日はご連絡ありがとうございます。'
        ];
        const closeTemplateMenu = () => {
            if (templateMenuOverlay) templateMenuOverlay.setAttribute('aria-hidden', 'true');
        };
        const openTemplateMenu = () => {
            if (!templateMenuOverlay || !templateMenuList) return;
            templateMenuList.innerHTML = '';
            quickTemplates.forEach(function (text) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'talk-template-item';
                button.textContent = text;
                button.addEventListener('click', function () {
                    if (!messageInput) return;
                    closeTemplateMenu();
                    messageInput.value = text;
                    messageInput.dispatchEvent(new Event('input', { bubbles: true }));
                    messageInput.focus();
                });
                templateMenuList.appendChild(button);
            });
            templateMenuOverlay.setAttribute('aria-hidden', 'false');
        };
        let currentResultAction = null;
        const renderResultDescByKind = () => {
            if (!resultDesc || !resultEmploymentKind) return;
            const kind = resultEmploymentKind.value;
            const kindLabel = talkKindLabelMap[kind] || '未選択';
            if (currentResultAction === 'hired') {
                resultDesc.textContent = '選択中の求人種別: ' + kindLabel + '。採用時給を入力し、採用テンプレートを選択して文面を編集してください。';
            } else if (currentResultAction === 'rejected') {
                resultDesc.textContent = '選択中の求人種別: ' + kindLabel + '。不採用テンプレートを選択し、必要に応じて文面を編集してください。';
            }
        };
        const bindTalkJobKindSave = () => {
            if (!(talkRoomJobKindSelect && saveTalkJobKindBtn && canSelectTalkJobKind)) return;
            saveTalkJobKindBtn.addEventListener('click', async function() {
                const selected = talkRoomJobKindSelect.value;
                if (!selected) {
                    window.alert('求人種別を選択してください。');
                    return;
                }
                if (selected === currentSavedTalkJobKind) {
                    if (talkJobKindSaveStatus) talkJobKindSaveStatus.textContent = '保存済み';
                    return;
                }
                saveTalkJobKindBtn.disabled = true;
                if (talkJobKindSaveStatus) talkJobKindSaveStatus.textContent = '保存中...';
                try {
                    await postJson(actionUrl, token, {
                        partner_id: partnerId,
                        action_type: 'set_job_kind',
                        job_kind: selected
                    });
                    currentSavedTalkJobKind = selected;
                    renderTalkKindGuidance(currentSavedTalkJobKind);
                    if (talkJobKindSaveStatus) {
                        talkJobKindSaveStatus.textContent = '保存済み';
                    }
                    saveTalkJobKindBtn.disabled = false;
                    closeJobKindModal();
                } catch (error) {
                    window.alert(error.message || '求人種別の保存に失敗しました。');
                    if (talkJobKindSaveStatus) talkJobKindSaveStatus.textContent = '保存失敗';
                    saveTalkJobKindBtn.disabled = false;
                }
            });
        };
        bindTalkJobKindSave();

        const openModal = () => {
            if (!overlay) return;
            const now = new Date();
            const max = new Date(now.getTime());
            max.setMonth(max.getMonth() + 2);
            const toDate = (d) => {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return y + '-' + m + '-' + day;
            };
            const minDate = toDate(now);
            const maxDate = toDate(max);
            interviewForm && interviewForm.querySelectorAll('input[type="date"]').forEach(function (input) {
                input.min = minDate;
                input.max = maxDate;
            });
            overlay.setAttribute('aria-hidden', 'false');
        };

        const closeModal = () => {
            if (!overlay) return;
            overlay.setAttribute('aria-hidden', 'true');
        };

        const openTalkActionMenu = () => {
            if (!talkActionMenuOverlay) return;
            talkActionMenuOverlay.setAttribute('aria-hidden', 'false');
        };

        const closeTalkActionMenu = () => {
            if (!talkActionMenuOverlay) return;
            talkActionMenuOverlay.setAttribute('aria-hidden', 'true');
        };

        const openJobKindModal = () => {
            if (!jobKindOverlay) return;
            jobKindOverlay.setAttribute('aria-hidden', 'false');
        };

        const closeJobKindModal = () => {
            if (!jobKindOverlay) return;
            jobKindOverlay.setAttribute('aria-hidden', 'true');
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
            if (resultEmploymentKind && currentSavedTalkJobKind && talkKindLabelMap[currentSavedTalkJobKind]) {
                resultEmploymentKind.value = currentSavedTalkJobKind;
            }
            renderResultDescByKind();
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
        if (resultEmploymentKind) {
            resultEmploymentKind.addEventListener('change', renderResultDescByKind);
        }

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

        if (openTalkActionMenuBtn && talkActionMenuOverlay) {
            openTalkActionMenuBtn.addEventListener('click', function (e) {
                e.preventDefault();
                openTalkActionMenu();
            });
        }
        talkActionMenuCloseButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                closeTalkActionMenu();
            });
        });
        if (talkActionMenuOverlay) {
            talkActionMenuOverlay.addEventListener('click', function (e) {
                if (e.target === talkActionMenuOverlay) closeTalkActionMenu();
            });
        }
        if (openJobKindModalBtn && jobKindOverlay) {
            openJobKindModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openJobKindModal();
            });
        }
        closeJobKindButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                closeJobKindModal();
            });
        });
        if (jobKindOverlay) {
            jobKindOverlay.addEventListener('click', function(e) {
                if (e.target === jobKindOverlay) closeJobKindModal();
            });
        }
        if (openImageSendMenu && imageInput) {
            openImageSendMenu.addEventListener('click', function(e) {
                e.preventDefault();
                closeTalkActionMenu();
                imageInput.click();
            });
        }
        if (imageInput) {
            imageInput.addEventListener('change', async function() {
                const file = imageInput.files && imageInput.files[0] ? imageInput.files[0] : null;
                if (!file) return;
                const formData = new FormData();
                formData.append('partner_id', partnerId || '');
                formData.append('image', file);
                if (isCastRoom && !hasTalkMessages && talkTopicField && talkTopicField.value) {
                    formData.append('talk_topic', talkTopicField.value);
                    if (talkJobKindField && !talkJobKindField.disabled && talkJobKindField.value) {
                        formData.append('talk_job_kind', talkJobKindField.value);
                    }
                }
                try {
                    const response = await fetch(chatForm.getAttribute('data-url'), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error((result && result.message) || '画像送信に失敗しました。');
                    }
                    appendImageMessage((result.data && result.data.image_url) || '', '', (result.data && result.data.time) || '');
                } catch (error) {
                    window.alert(error.message || '画像送信に失敗しました。');
                } finally {
                    imageInput.value = '';
                }
            });
        }
        templateMenuCloseButtons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                closeTemplateMenu();
            });
        });
        if (templateMenuOverlay) {
            templateMenuOverlay.addEventListener('click', function (e) {
                if (e.target === templateMenuOverlay) closeTemplateMenu();
            });
        }
        if (openTemplateSendMenu && messageInput) {
            openTemplateSendMenu.addEventListener('click', function(e) {
                e.preventDefault();
                closeTalkActionMenu();
                openTemplateMenu();
            });
        }

        if (openInterviewBtn && overlay) {
            openInterviewBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeTalkActionMenu();
                if (currentTalkStatusCode === 'interview_fixed') {
                    if (!window.confirm('面談キャンセル依頼を送信しますか？キャストが承諾すると、やり取り中に戻ります。')) return;
                    postJson(actionUrl, token, {
                        partner_id: partnerId,
                        action_type: 'interview_cancel_request'
                    }).then(function () {
                        window.location.reload();
                    }).catch(function (error) {
                        window.alert(error.message || '面談キャンセル依頼の送信に失敗しました。');
                    });
                    return;
                }
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
        if (openHireModalFromMenu) {
            openHireModalFromMenu.addEventListener('click', function(e) {
                e.preventDefault();
                closeTalkActionMenu();
                openResultModal('hired');
            });
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openResultModal('rejected');
            });
        }
        if (openRejectModalFromMenu) {
            openRejectModalFromMenu.addEventListener('click', function(e) {
                e.preventDefault();
                closeTalkActionMenu();
                openResultModal('rejected');
            });
        }
        chatMessages.querySelectorAll('.js-open-result-action').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const actionType = btn.getAttribute('data-result-action');
                if (actionType === 'hired' || actionType === 'rejected') {
                    openResultModal(actionType);
                }
            });
        });

        if (resultSubmitBtn && resultTextarea) {
            resultSubmitBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                if (!currentResultAction) return;

                const message = resultTextarea.value.trim();
                if (currentResultAction === 'hired' && !message) {
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
                        const hiredWage = hiredHourlyWageInput.value.trim();
                        if (!hiredWage) {
                            window.alert('採用時給（確定）を入力してください。');
                            resultSubmitBtn.disabled = false;
                            return;
                        }
                        payload.hired_regular_hourly_wage = hiredWage;
                    }
                    if (currentResultAction === 'rejected') {
                        payload.message = message; // 不採用理由は内部管理用にのみ使用
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
        if (openCancelStatusFromMenu && cancelStatusBtn) {
            openCancelStatusFromMenu.addEventListener('click', function(e) {
                e.preventDefault();
                closeTalkActionMenu();
                cancelStatusBtn.click();
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
                    ['option1_date', 'option1_time'],
                    ['option2_date', 'option2_time'],
                    ['option3_date', 'option3_time'],
                ].map(function (keys) {
                    const d = String(formData.get(keys[0]) || '').trim();
                    const t = String(formData.get(keys[1]) || '').trim();
                    if (!d || !t) return '';
                    return d + ' ' + t + ':00';
                }).filter(Boolean);

                if (options.length === 0) {
                    window.alert('面談候補日を1件以上入力してください。');
                    return;
                }
                const now = new Date();
                const max = new Date(now.getTime());
                max.setMonth(max.getMonth() + 2);
                for (const option of options) {
                    const dt = new Date(option.replace(' ', 'T'));
                    if (Number.isNaN(dt.getTime())) {
                        window.alert('日時の形式が不正です。');
                        return;
                    }
                    if (dt.getTime() < now.getTime()) {
                        window.alert('面談候補日は現在日時より後を指定してください。');
                        return;
                    }
                    if (dt.getTime() > max.getTime()) {
                        window.alert('面談候補日は2か月後まで指定できます。');
                        return;
                    }
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
            if (talkActionMenuOverlay && talkActionMenuOverlay.getAttribute('aria-hidden') === 'false') {
                closeTalkActionMenu();
            }
            if (jobKindOverlay && jobKindOverlay.getAttribute('aria-hidden') === 'false') {
                closeJobKindModal();
            }
            if (resultOverlay && resultOverlay.getAttribute('aria-hidden') === 'false') {
                closeResultModal();
            }
        });
    }

    if (isCastRoom && chatForm) {
        const actionUrl = chatForm.getAttribute('data-action-url');
        const token = chatForm.querySelector('input[name="_token"]').value;
        const openTalkActionMenuBtn = document.getElementById('open-talk-action-menu');
        const talkActionMenuOverlay = document.getElementById('talk-action-menu-overlay');
        const talkActionMenuCloseButtons = document.querySelectorAll('.js-talk-action-menu-close');
        const openImageSendMenu = document.getElementById('open-image-send-menu');
        const openTemplateSendMenu = document.getElementById('open-template-send-menu');
        const templateMenuOverlay = document.getElementById('talk-template-menu-overlay');
        const templateMenuList = document.getElementById('talk-template-menu-list');
        const templateMenuCloseButtons = document.querySelectorAll('.js-talk-template-close');
        const quickTemplates = [
            '本日はよろしくお願いします。',
            'ご確認ありがとうございます。承知しました。',
            '問題なければこの内容で進めさせてください。'
        ];
        const closeTemplateMenu = () => {
            if (templateMenuOverlay) templateMenuOverlay.setAttribute('aria-hidden', 'true');
        };
        const openTemplateMenu = () => {
            if (!templateMenuOverlay || !templateMenuList) return;
            templateMenuList.innerHTML = '';
            quickTemplates.forEach(function (text) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'talk-template-item';
                button.textContent = text;
                button.addEventListener('click', function () {
                    if (!messageInput) return;
                    closeTemplateMenu();
                    messageInput.value = text;
                    messageInput.dispatchEvent(new Event('input', { bubbles: true }));
                    messageInput.focus();
                });
                templateMenuList.appendChild(button);
            });
            templateMenuOverlay.setAttribute('aria-hidden', 'false');
        };
        const fulltimeRequestBtn = document.getElementById('send-fulltime-request');
        const confirmOverlay = document.getElementById('interview-confirm-overlay');
        const confirmSelected = document.getElementById('interview-confirm-selected');
        const confirmSubmitBtn = document.getElementById('interview-confirm-submit');
        const confirmCloseButtons = document.querySelectorAll('.js-interview-confirm-close');
        let pendingInterviewSelection = null;
        const openTalkActionMenu = () => {
            if (!talkActionMenuOverlay) return;
            talkActionMenuOverlay.setAttribute('aria-hidden', 'false');
        };
        const closeTalkActionMenu = () => {
            if (!talkActionMenuOverlay) return;
            talkActionMenuOverlay.setAttribute('aria-hidden', 'true');
        };
        if (openTalkActionMenuBtn && talkActionMenuOverlay) {
            openTalkActionMenuBtn.addEventListener('click', function (e) {
                e.preventDefault();
                openTalkActionMenu();
            });
        }
        talkActionMenuCloseButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                closeTalkActionMenu();
            });
        });
        if (talkActionMenuOverlay) {
            talkActionMenuOverlay.addEventListener('click', function (e) {
                if (e.target === talkActionMenuOverlay) closeTalkActionMenu();
            });
        }
        if (openImageSendMenu && imageInput) {
            openImageSendMenu.addEventListener('click', function(e) {
                e.preventDefault();
                closeTalkActionMenu();
                imageInput.click();
            });
        }
        if (imageInput) {
            imageInput.addEventListener('change', async function() {
                const file = imageInput.files && imageInput.files[0] ? imageInput.files[0] : null;
                if (!file) return;
                const formData = new FormData();
                formData.append('partner_id', partnerId || '');
                formData.append('image', file);
                if (!hasTalkMessages && talkTopicField && talkTopicField.value) {
                    formData.append('talk_topic', talkTopicField.value);
                    if (talkJobKindField && !talkJobKindField.disabled && talkJobKindField.value) {
                        formData.append('talk_job_kind', talkJobKindField.value);
                    }
                }
                try {
                    const response = await fetch(chatForm.getAttribute('data-url'), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error((result && result.message) || '画像送信に失敗しました。');
                    }
                    appendImageMessage((result.data && result.data.image_url) || '', '', (result.data && result.data.time) || '');
                } catch (error) {
                    window.alert(error.message || '画像送信に失敗しました。');
                } finally {
                    imageInput.value = '';
                }
            });
        }
        templateMenuCloseButtons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                closeTemplateMenu();
            });
        });
        if (templateMenuOverlay) {
            templateMenuOverlay.addEventListener('click', function (e) {
                if (e.target === templateMenuOverlay) closeTemplateMenu();
            });
        }
        if (openTemplateSendMenu && messageInput) {
            openTemplateSendMenu.addEventListener('click', function(e) {
                e.preventDefault();
                closeTalkActionMenu();
                openTemplateMenu();
            });
        }
        if (openWorkCompleteReportMenu) {
            openWorkCompleteReportMenu.addEventListener('click', function () {
                closeTalkActionMenu();
            });
        }
        if (talkRoomJobKindSelect && saveTalkJobKindBtn && canSelectTalkJobKind) {
            saveTalkJobKindBtn.addEventListener('click', async function() {
                const selected = talkRoomJobKindSelect.value;
                if (!selected) {
                    window.alert('求人種別を選択してください。');
                    return;
                }
                if (selected === currentSavedTalkJobKind) {
                    if (talkJobKindSaveStatus) talkJobKindSaveStatus.textContent = '保存済み';
                    return;
                }
                saveTalkJobKindBtn.disabled = true;
                if (talkJobKindSaveStatus) talkJobKindSaveStatus.textContent = '保存中...';
                try {
                    await postJson(actionUrl, token, {
                        partner_id: partnerId,
                        action_type: 'set_job_kind',
                        job_kind: selected
                    });
                    currentSavedTalkJobKind = selected;
                    if (talkJobKindSaveStatus) {
                        talkJobKindSaveStatus.textContent = '保存済み';
                    }
                    saveTalkJobKindBtn.disabled = false;
                } catch (error) {
                    window.alert(error.message || '求人種別の保存に失敗しました。');
                    if (talkJobKindSaveStatus) talkJobKindSaveStatus.textContent = '保存失敗';
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

        if (fulltimeRequestBtn) {
            fulltimeRequestBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                if (!window.confirm('本入店リクエストを送信しますか？')) return;
                fulltimeRequestBtn.disabled = true;
                try {
                    await postJson(actionUrl, token, {
                        partner_id: partnerId,
                        action_type: 'fulltime_request'
                    });
                    window.location.reload();
                } catch (error) {
                    window.alert(error.message || '本入店リクエストの送信に失敗しました。');
                    fulltimeRequestBtn.disabled = false;
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

        chatMessages.addEventListener('click', async function (e) {
            const acceptBtn = e.target.closest('.js-interview-cancel-accept');
            if (!acceptBtn) return;
            e.preventDefault();
            if (!window.confirm('面談キャンセルを承諾して、やり取り中に戻しますか？')) return;
            acceptBtn.disabled = true;
            try {
                await postJson(actionUrl, token, {
                    partner_id: partnerId,
                    action_type: 'interview_cancel_accept'
                });
                window.location.reload();
            } catch (error) {
                window.alert(error.message || '承諾に失敗しました。');
                acceptBtn.disabled = false;
            }
        });
    }
});