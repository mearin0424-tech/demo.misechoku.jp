/**
 * Talk Room Logic (Real-time update with Server)
 */
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const isCastRoom = typeof window.isCastTalkRoom !== 'undefined' ? !!window.isCastTalkRoom : false;

    if (!chatMessages) return;

    const messageInput = chatForm ? chatForm.querySelector('textarea[name="message"]') : null;
    const sendButton = chatForm ? chatForm.querySelector('#talk-send-btn') : null;
    const ngWarnEl = chatForm ? chatForm.querySelector('#talk-ng-warn') : null;
    const ngWarnTextEl = ngWarnEl ? ngWarnEl.querySelector('.talk-ng-warn-text') : null;
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

    // ===== NGワード検査 =====
    const ngPayload = (window.talkNgPayload && typeof window.talkNgPayload === 'object') ? window.talkNgPayload : { patterns: [], words: [] };
    const ngPatterns = Array.isArray(ngPayload.patterns) ? ngPayload.patterns.map(function (p) {
        try {
            return { re: new RegExp(p.re, p.flags || ''), label: p.label || 'NGワード' };
        } catch (e) {
            return null;
        }
    }).filter(Boolean) : [];
    const ngWordList = Array.isArray(ngPayload.words) ? ngPayload.words.map(function (w) { return String(w || '').toLowerCase(); }).filter(Boolean) : [];

    function detectNg(text) {
        if (!text) return null;
        const normalized = String(text);
        for (let i = 0; i < ngPatterns.length; i++) {
            const m = normalized.match(ngPatterns[i].re);
            if (m) return { hit: m[0], label: ngPatterns[i].label };
        }
        const lower = normalized.toLowerCase();
        for (let i = 0; i < ngWordList.length; i++) {
            const w = ngWordList[i];
            if (w && lower.indexOf(w) !== -1) {
                return { hit: w, label: 'NGワード' };
            }
        }
        return null;
    }

    function setSendDisabled(disabled) {
        if (!sendButton) return;
        sendButton.disabled = !!disabled;
        sendButton.classList.toggle('is-disabled', !!disabled);
    }

    function showNgWarning(label, hit) {
        if (!ngWarnEl) return;
        if (ngWarnTextEl) {
            ngWarnTextEl.textContent = '使用できない表現が含まれています：' + (label || 'NGワード') + (hit ? '（' + hit + '）' : '');
        }
        ngWarnEl.hidden = false;
    }

    function clearNgWarning() {
        if (!ngWarnEl) return;
        ngWarnEl.hidden = true;
    }

    function evaluateNgState() {
        const value = messageInput ? messageInput.value : '';
        const result = detectNg(value);
        if (result) {
            showNgWarning(result.label, result.hit);
            setSendDisabled(true);
            return false;
        }
        clearNgWarning();
        setSendDisabled(value.trim() === '');
        return true;
    }

    if (messageInput) {
        messageInput.addEventListener('input', evaluateNgState);
        evaluateNgState();
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

            // クライアント側 NG ワード検査
            const ngHit = detectNg(content);
            if (ngHit) {
                showNgWarning(ngHit.label, ngHit.hit);
                setSendDisabled(true);
                if (typeof messageInput.focus === 'function') messageInput.focus();
                return;
            }

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
                const msg = String((error && error.message) || '');
                // スカウト送信上限（429）：本文を復元して案内を表示
                if (msg.indexOf('スカウト送信上限') !== -1) {
                    if (errorMsg) errorMsg.remove();
                    messageInput.value = content;
                    autoResize();
                    (window.appToast || window.alert)(msg, 'error');
                    isSubmitting = false;
                    submitBtn.disabled = false;
                    return;
                }
                // サーバ側 NG 検出（422）の場合は本文を復元して警告表示
                if (msg.indexOf('使用できない表現') !== -1) {
                    if (errorMsg) errorMsg.remove();
                    messageInput.value = content;
                    showNgWarning('NGワード', '');
                    if (ngWarnTextEl) ngWarnTextEl.textContent = msg;
                    setSendDisabled(true);
                }
            } finally {
                isSubmitting = false;
                submitBtn.disabled = false;
                messageInput.focus();
                evaluateNgState();
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

    // ========================================================================
    // 種別スイッチ（サブヘッダー内・インライン 2択）
    // 新規入店 (trial) / ヘルプ (help) をタップで即時保存。
    // 面談日確定後や本入店ロック時は disabled 属性で無効化される。
    // ========================================================================
    const talkJobKindSwitch = document.querySelector('.talk-job-kind-switch');
    if (talkJobKindSwitch) {
        const switchActionUrl = talkJobKindSwitch.getAttribute('data-action-url');
        const switchPartnerId = talkJobKindSwitch.getAttribute('data-partner-id');
        const switchCsrfToken = (document.querySelector('meta[name="csrf-token"]')
            && document.querySelector('meta[name="csrf-token"]').getAttribute('content')) || '';
        const isSwitchDisabled = talkJobKindSwitch.classList.contains('is-disabled');
        const segments = talkJobKindSwitch.querySelectorAll('.talk-job-kind-switch__segment');

        const setSwitchActive = (kind) => {
            talkJobKindSwitch.setAttribute('data-current', kind || '');
            segments.forEach(function (seg) {
                const on = seg.getAttribute('data-job-kind') === kind;
                seg.classList.toggle('is-active', on);
                seg.setAttribute('aria-checked', on ? 'true' : 'false');
            });
            const currentEl = document.getElementById('talk-job-kind-current');
            if (currentEl) currentEl.setAttribute('data-job-kind-current', kind || '');
        };

        segments.forEach(function (seg) {
            seg.addEventListener('click', async function () {
                if (isSwitchDisabled || seg.disabled) return;
                const selected = seg.getAttribute('data-job-kind');
                if (!selected || selected === currentSavedTalkJobKind) return;

                const prev = currentSavedTalkJobKind;
                // Optimistic UI: 先に見た目を切り替える
                setSwitchActive(selected);
                segments.forEach(function (s) { s.disabled = true; });
                try {
                    await postJson(switchActionUrl, switchCsrfToken, {
                        partner_id: switchPartnerId,
                        action_type: 'set_job_kind',
                        job_kind: selected,
                    });
                    currentSavedTalkJobKind = selected;
                    if (typeof renderTalkKindGuidance === 'function') {
                        renderTalkKindGuidance(currentSavedTalkJobKind);
                    }
                    // 既存の select（互換用モーダル内）にも反映
                    if (talkRoomJobKindSelect) talkRoomJobKindSelect.value = selected;
                } catch (error) {
                    // 失敗したら元に戻す
                    setSwitchActive(prev);
                    (window.appToast || window.alert)(
                        (error && error.message) || '種別の保存に失敗しました。',
                        'error'
                    );
                } finally {
                    segments.forEach(function (s) { s.disabled = false; });
                }
            });
        });
    }

    // ========================================================================
    // クイック定型文（4 スロット）— モーダル内で挿入／編集／デフォルト復帰
    // 共有: shop / cast 両ブランチから openTemplateMenu / closeTemplateMenu を呼ぶ。
    // ========================================================================
    const templateMenuOverlay = document.getElementById('talk-template-menu-overlay');
    const templateMenuList = document.getElementById('talk-template-menu-list');
    const templateMenuCloseButtons = document.querySelectorAll('.js-talk-template-close');
    let cachedQuickTemplateSlots = Array.isArray(window.talkQuickTemplates)
        ? window.talkQuickTemplates.slice()
        : [];

    const getTalkCsrfToken = () => {
        if (chatForm) {
            const tok = chatForm.querySelector('input[name="_token"]');
            if (tok) return tok.value;
        }
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    const closeTemplateMenu = () => {
        if (templateMenuOverlay) templateMenuOverlay.setAttribute('aria-hidden', 'true');
    };

    const buildSlotCard = (slot) => {
        const card = document.createElement('div');
        card.className = 'talk-template-slot-card' + (slot.is_custom ? ' is-custom' : '');

        const useBtn = document.createElement('button');
        useBtn.type = 'button';
        useBtn.className = 'talk-template-slot-use';

        const num = document.createElement('span');
        num.className = 'talk-template-slot-no';
        num.textContent = '定型文' + slot.slot;

        const body = document.createElement('span');
        body.className = 'talk-template-slot-body';
        body.textContent = slot.body || slot.default_body || '';

        useBtn.appendChild(num);
        useBtn.appendChild(body);
        useBtn.addEventListener('click', function () {
            if (!messageInput) return;
            closeTemplateMenu();
            messageInput.value = body.textContent;
            messageInput.dispatchEvent(new Event('input', { bubbles: true }));
            messageInput.focus();
        });

        const editBtn = document.createElement('button');
        editBtn.type = 'button';
        editBtn.className = 'talk-template-slot-edit-btn';
        editBtn.title = 'この定型文を編集';
        editBtn.setAttribute('aria-label', 'この定型文を編集');
        editBtn.innerHTML = '<i class="fas fa-pen" aria-hidden="true"></i>';
        editBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleSlotEdit(card, slot);
        });

        const viewRow = document.createElement('div');
        viewRow.className = 'talk-template-slot-view';
        viewRow.appendChild(useBtn);
        viewRow.appendChild(editBtn);
        card.appendChild(viewRow);
        return card;
    };

    const toggleSlotEdit = (card, slot) => {
        const existing = card.querySelector('.talk-template-slot-edit-form');
        const viewRow = card.querySelector('.talk-template-slot-view');
        if (existing) {
            existing.remove();
            if (viewRow) viewRow.style.display = '';
            return;
        }
        if (viewRow) viewRow.style.display = 'none';

        const form = document.createElement('div');
        form.className = 'talk-template-slot-edit-form';

        const label = document.createElement('label');
        label.className = 'talk-template-slot-edit-label';
        label.textContent = '定型文' + slot.slot + ' の本文';
        form.appendChild(label);

        const textarea = document.createElement('textarea');
        textarea.value = slot.body || slot.default_body || '';
        textarea.rows = 4;
        textarea.maxLength = 2000;
        textarea.className = 'talk-template-slot-textarea';
        form.appendChild(textarea);

        const err = document.createElement('p');
        err.className = 'talk-template-slot-error';
        err.hidden = true;
        form.appendChild(err);

        const actions = document.createElement('div');
        actions.className = 'talk-template-slot-edit-actions';

        const saveBtn = document.createElement('button');
        saveBtn.type = 'button';
        saveBtn.className = 'talk-template-slot-save';
        saveBtn.textContent = '保存';
        saveBtn.addEventListener('click', async function () {
            const bodyText = textarea.value.trim();
            if (!bodyText) {
                err.textContent = '本文を入力してください。';
                err.hidden = false;
                return;
            }
            saveBtn.disabled = true;
            try {
                const res = await fetch('/setting/talk-templates/slot/' + slot.slot, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getTalkCsrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ body: bodyText }),
                });
                const j = await res.json();
                if (!res.ok || !j.success) throw new Error(j.message || '保存に失敗しました。');
                cachedQuickTemplateSlots = Array.isArray(j.slots) ? j.slots : cachedQuickTemplateSlots;
                window.talkQuickTemplates = cachedQuickTemplateSlots;
                renderTemplateSlots();
            } catch (ex) {
                err.textContent = ex.message || '保存に失敗しました。';
                err.hidden = false;
            } finally {
                saveBtn.disabled = false;
            }
        });

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'talk-template-slot-cancel';
        cancelBtn.textContent = 'キャンセル';
        cancelBtn.addEventListener('click', function () {
            renderTemplateSlots();
        });

        const resetBtn = document.createElement('button');
        resetBtn.type = 'button';
        resetBtn.className = 'talk-template-slot-reset';
        resetBtn.textContent = 'デフォルトに戻す';
        resetBtn.hidden = !slot.is_custom;
        resetBtn.addEventListener('click', async function () {
            if (!window.confirm('このスロットをデフォルトの文面に戻します。よろしいですか？')) return;
            resetBtn.disabled = true;
            try {
                const res = await fetch('/setting/talk-templates/slot/' + slot.slot, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getTalkCsrfToken(),
                        'Accept': 'application/json',
                    },
                });
                const j = await res.json();
                if (!res.ok || !j.success) throw new Error(j.message || 'リセットに失敗しました。');
                cachedQuickTemplateSlots = Array.isArray(j.slots) ? j.slots : cachedQuickTemplateSlots;
                window.talkQuickTemplates = cachedQuickTemplateSlots;
                renderTemplateSlots();
            } catch (ex) {
                err.textContent = ex.message || 'リセットに失敗しました。';
                err.hidden = false;
            } finally {
                resetBtn.disabled = false;
            }
        });

        actions.appendChild(saveBtn);
        actions.appendChild(cancelBtn);
        actions.appendChild(resetBtn);
        form.appendChild(actions);
        card.appendChild(form);
    };

    // ステータスごとの定型文セクションを1つ作る
    const buildStatusSection = (group) => {
        const section = document.createElement('section');
        section.className = 'talk-template-status-section';

        const heading = document.createElement('h3');
        heading.className = 'talk-template-status-heading';
        heading.textContent = group.status_label || '';
        section.appendChild(heading);

        const list = document.createElement('div');
        list.className = 'talk-template-status-list';
        (group.items || []).forEach(function (text) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'talk-template-status-item';
            btn.textContent = text;
            btn.addEventListener('click', function () {
                if (!messageInput) return;
                closeTemplateMenu();
                messageInput.value = text;
                messageInput.dispatchEvent(new Event('input', { bubbles: true }));
                messageInput.focus();
            });
            list.appendChild(btn);
        });
        section.appendChild(list);
        return section;
    };

    // マイ定型文（4スロット・編集可能）のセクションを作る
    const buildMineSection = () => {
        const mySection = document.createElement('section');
        mySection.className = 'talk-template-status-section talk-template-status-section--mine';

        const myHeading = document.createElement('h3');
        myHeading.className = 'talk-template-status-heading';
        myHeading.textContent = 'マイ定型文（編集可能・4スロット）';
        mySection.appendChild(myHeading);

        if (!cachedQuickTemplateSlots.length) {
            const empty = document.createElement('p');
            empty.className = 'talk-template-empty';
            empty.textContent = 'マイ定型文が利用できません。';
            mySection.appendChild(empty);
        } else {
            const slotWrap = document.createElement('div');
            slotWrap.className = 'talk-template-status-list talk-template-status-list--slots';
            cachedQuickTemplateSlots.forEach(function (slot) {
                slotWrap.appendChild(buildSlotCard(slot));
            });
            mySection.appendChild(slotWrap);
        }
        return mySection;
    };

    // 定型文メニュー：ステータス別パネルを左右スライド（タブ + スワイプ）で切り替える。
    // 初期表示は現在のトークステータスに合わせる。
    const renderTemplateSlots = () => {
        if (!templateMenuList) return;
        templateMenuList.innerHTML = '';

        const allGroups = (Array.isArray(window.talkAllQuickReplies) ? window.talkAllQuickReplies : [])
            .filter(function (g) { return g && Array.isArray(g.items) && g.items.length > 0; });

        const panels = allGroups.map(function (group) {
            return {
                key: String(group.status_key || group.status_code || ''),
                label: group.status_label || '',
                build: function () { return buildStatusSection(group); }
            };
        });
        panels.push({ key: 'mine', label: 'マイ定型文', build: buildMineSection });

        const tabs = document.createElement('div');
        tabs.className = 'talk-template-tabs';
        tabs.setAttribute('role', 'tablist');
        tabs.setAttribute('aria-label', '状況で定型文を切り替え');

        const track = document.createElement('div');
        track.className = 'talk-template-track';

        panels.forEach(function (p, i) {
            const tab = document.createElement('button');
            tab.type = 'button';
            tab.className = 'talk-template-tab';
            tab.setAttribute('role', 'tab');
            tab.textContent = p.label;
            tab.addEventListener('click', function () { scrollToPanel(i, true); });
            tabs.appendChild(tab);

            const panel = document.createElement('div');
            panel.className = 'talk-template-panel';
            panel.appendChild(p.build());
            track.appendChild(panel);
        });

        templateMenuList.appendChild(tabs);
        templateMenuList.appendChild(track);

        function setActiveTab(i) {
            Array.prototype.forEach.call(tabs.children, function (t, idx) {
                t.classList.toggle('is-active', idx === i);
                t.setAttribute('aria-selected', idx === i ? 'true' : 'false');
            });
            if (tabs.children[i] && tabs.children[i].scrollIntoView) {
                tabs.children[i].scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
            }
        }
        function scrollToPanel(i, smooth) {
            const panel = track.children[i];
            if (!panel) return;
            track.scrollTo({ left: panel.offsetLeft, behavior: smooth ? 'smooth' : 'auto' });
            setActiveTab(i);
        }
        // スワイプでスライドした時もタブのアクティブを追従
        let scrollSyncTimer = null;
        track.addEventListener('scroll', function () {
            clearTimeout(scrollSyncTimer);
            scrollSyncTimer = setTimeout(function () {
                const i = Math.round(track.scrollLeft / Math.max(1, track.clientWidth));
                setActiveTab(Math.max(0, Math.min(panels.length - 1, i)));
            }, 60);
        }, { passive: true });

        // 初期表示 = 現在のトークステータス（該当なしなら先頭）
        const currentKey = String(window.currentTalkStatusCode || '');
        let initial = panels.findIndex(function (p) { return p.key === currentKey; });
        if (initial < 0) initial = 0;
        requestAnimationFrame(function () { scrollToPanel(initial, false); });
    };

    const openTemplateMenu = () => {
        if (!templateMenuOverlay) return;
        renderTemplateSlots();
        templateMenuOverlay.setAttribute('aria-hidden', 'false');
    };

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
        const openTemplateSendMenu = document.getElementById('open-template-send-menu');
        const openWorkCompleteReportMenu = document.getElementById('open-work-complete-report-menu');
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
        const openTemplateSendMenu = document.getElementById('open-template-send-menu');
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