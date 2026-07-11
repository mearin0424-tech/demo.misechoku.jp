/**
 * ミセチョク — AI コンシェルジュ 自由入力チャット
 *
 * data-ai-chat-root をルートとして、自由入力 → タイピング演出 → AI 返答 → 店舗カード を描画する。
 * サーバ側 /cast/search/ai-chat は OSS モデル（Groq / Ollama など OpenAI 互換 API）で
 * 返答を生成する。連携が無効／失敗時は AiChatTemplateService のテンプレ返答に自動フォールバック。
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'ai-chat-transcript-v1';
    var STORAGE_TTL_MS = 1000 * 60 * 60 * 2; // 2 時間: 古すぎる履歴は破棄

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatReply(text) {
        return escapeHtml(text || '').replace(/\n/g, '<br>');
    }

    function getCsrf() {
        var el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    document.addEventListener('DOMContentLoaded', function () {
        var root = document.querySelector('[data-ai-chat-root]');
        if (!root) return;

        var endpoint = root.getAttribute('data-endpoint');
        var avatar = root.getAttribute('data-avatar') || '';
        var thread = root.querySelector('[data-ai-thread]');
        var form = root.querySelector('[data-ai-form]');
        var input = root.querySelector('[data-ai-input]');
        var sendBtn = root.querySelector('[data-ai-send]');
        var quickReplyArea = root.querySelector('[data-ai-quick-replies]');

        /** @type {{role:string, content:string, cards?:any[], source?:string, ts?:number}[]} */
        var transcript = [];
        var isBusy = false;

        // ------------------------------------------------------------
        // 永続化：sessionStorage に短期保存（同じタブでリロードしても続きから）
        // ------------------------------------------------------------
        function saveTranscript() {
            try {
                var payload = { ts: Date.now(), items: transcript };
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
            } catch (_) {}
        }
        function restoreTranscript() {
            try {
                var raw = sessionStorage.getItem(STORAGE_KEY);
                if (!raw) return null;
                var payload = JSON.parse(raw);
                if (!payload || !Array.isArray(payload.items)) return null;
                if (typeof payload.ts !== 'number' || Date.now() - payload.ts > STORAGE_TTL_MS) {
                    sessionStorage.removeItem(STORAGE_KEY);
                    return null;
                }
                return payload.items;
            } catch (_) {
                return null;
            }
        }
        function clearTranscript() {
            try { sessionStorage.removeItem(STORAGE_KEY); } catch (_) {}
        }

        // ------------------------------------------------------------
        // 描画ヘルパ
        // ------------------------------------------------------------
        function scrollToBottom(smooth) {
            window.setTimeout(function () {
                if (smooth && thread.scrollTo) {
                    thread.scrollTo({ top: thread.scrollHeight, behavior: 'smooth' });
                } else {
                    thread.scrollTop = thread.scrollHeight;
                }
            }, 30);
        }

        function appendUser(text, opts) {
            var div = document.createElement('div');
            div.className = 'ai-chat__msg ai-chat__msg--user';
            div.innerHTML = '<div class="ai-chat__bubble ai-chat__bubble--user">' + escapeHtml(text) + '</div>';
            thread.appendChild(div);
            scrollToBottom();
            if (!opts || !opts.silent) {
                transcript.push({ role: 'user', content: text, ts: Date.now() });
                saveTranscript();
            }
        }

        function appendTyping() {
            var div = document.createElement('div');
            div.className = 'ai-chat__msg ai-chat__msg--ai ai-chat__msg--typing';
            div.setAttribute('aria-live', 'polite');
            div.setAttribute('aria-label', 'AI が返信を作成しています');
            div.innerHTML =
                '<div class="ai-chat__avatar">' +
                (avatar ? '<img src="' + escapeHtml(avatar) + '" alt="">' : '<i class="fas fa-robot"></i>') +
                '</div>' +
                '<div class="ai-chat__bubble ai-chat__bubble--ai">' +
                '  <span class="ai-chat__dot"></span>' +
                '  <span class="ai-chat__dot"></span>' +
                '  <span class="ai-chat__dot"></span>' +
                '</div>';
            thread.appendChild(div);
            scrollToBottom();
            return div;
        }

        function appendAi(text, opts) {
            var div = document.createElement('div');
            div.className = 'ai-chat__msg ai-chat__msg--ai';
            var source = opts && opts.source ? opts.source : '';
            var badge = source === 'llm'
                ? '<span class="ai-chat__source" title="オープンソース LLM で生成"><i class="fas fa-microchip"></i> AI</span>'
                : '';
            div.innerHTML =
                '<div class="ai-chat__avatar">' +
                (avatar ? '<img src="' + escapeHtml(avatar) + '" alt="">' : '<i class="fas fa-robot"></i>') +
                '</div>' +
                '<div class="ai-chat__bubble ai-chat__bubble--ai">' +
                '  <span class="ai-chat__body" data-ai-body></span>' +
                (badge ? ('<span class="ai-chat__meta">' + badge + '</span>') : '') +
                '</div>';
            thread.appendChild(div);

            // 文字ごとにフェードイン風に見せる（体感の "生成中" 感）
            var target = div.querySelector('[data-ai-body]');
            if (opts && opts.instant) {
                target.innerHTML = formatReply(text);
                scrollToBottom();
            } else {
                revealTextGradually(target, text);
            }

            if (!opts || !opts.silent) {
                transcript.push({ role: 'ai', content: text, source: source || null, ts: Date.now() });
                saveTranscript();
            }
        }

        function revealTextGradually(el, text) {
            var chars = String(text || '').split('');
            var i = 0;
            var chunk = Math.max(1, Math.floor(chars.length / 40)); // 40 ステップ弱で終わる
            var frame;
            (function step() {
                var slice = chars.slice(0, Math.min(i + chunk, chars.length)).join('');
                el.innerHTML = formatReply(slice);
                if (i < chars.length) {
                    i += chunk;
                    scrollToBottom();
                    frame = window.setTimeout(step, 18);
                } else {
                    scrollToBottom(true);
                }
            })();
            return function cancel() { if (frame) window.clearTimeout(frame); };
        }

        function appendCards(recs, opts) {
            if (!recs || !recs.length) return;
            var wrap = document.createElement('div');
            wrap.className = 'ai-chat__cards';

            recs.forEach(function (r) {
                var area = [r.pref, r.city].filter(Boolean).join(' ');
                var wage = r.wage ? '時給 ' + r.wage.toLocaleString() + '円〜' : '';
                var reward = r.reward ? '採用報酬 ' + r.reward.toLocaleString() + '円' : '';
                var meta = [area, wage, reward].filter(Boolean).join(' / ');

                wrap.insertAdjacentHTML('beforeend',
                    '<a href="' + escapeHtml(r.url) + '" class="ai-chat__card" target="_blank" rel="noopener">' +
                    '  <div class="ai-chat__card-thumb">' +
                    '    <img src="' + escapeHtml(r.image) + '" alt="' + escapeHtml(r.name || '') + '" loading="lazy">' +
                    '  </div>' +
                    '  <div class="ai-chat__card-body">' +
                    '    <p class="ai-chat__card-title">' + escapeHtml(r.name) + '</p>' +
                    '    <p class="ai-chat__card-meta">' + escapeHtml(meta) + '</p>' +
                    '    <p class="ai-chat__card-reason"><i class="fas fa-sparkles"></i> ' + escapeHtml(r.reason || '') + '</p>' +
                    '  </div>' +
                    '  <span class="ai-chat__card-cta">求人を見る →</span>' +
                    '</a>'
                );
            });

            thread.appendChild(wrap);
            scrollToBottom();

            if (!opts || !opts.silent) {
                // 直近の ai 発話にカードを付与
                for (var i = transcript.length - 1; i >= 0; i--) {
                    if (transcript[i].role === 'ai') {
                        transcript[i].cards = recs;
                        break;
                    }
                }
                saveTranscript();
            }
        }

        function renderQuickReplies(replies) {
            if (!quickReplyArea) return;
            quickReplyArea.innerHTML = '';
            if (!replies || !replies.length) return;
            replies.forEach(function (text) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ai-chat__quick';
                btn.textContent = text;
                btn.addEventListener('click', function () {
                    if (isBusy) return;
                    submitMessage(text);
                });
                quickReplyArea.appendChild(btn);
            });
        }

        // ------------------------------------------------------------
        // 送信
        // ------------------------------------------------------------
        function submitMessage(text) {
            var msg = (text || '').trim();
            if (!msg || isBusy) return;
            isBusy = true;
            if (sendBtn) sendBtn.disabled = true;
            if (input) input.disabled = true;

            appendUser(msg);
            if (input) input.value = '';
            renderQuickReplies([]); // 送信中はクイックリプライ非表示

            var typingEl = appendTyping();

            // LLM に送る履歴（過去 12 ターン程度）
            var histForServer = transcript
                .filter(function (t) { return t.role === 'user' || t.role === 'ai'; })
                .slice(-24)
                .map(function (t) { return { role: t.role === 'ai' ? 'assistant' : 'user', content: t.content }; });

            // タイピング演出用の最低表示時間
            var minWait = 700 + Math.floor(Math.random() * 400);
            var startedAt = Date.now();

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrf(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message: msg, history: histForServer }),
            })
                .then(function (res) {
                    if (!res.ok) throw new Error('AI chat failed: ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    var wait = Math.max(0, minWait - (Date.now() - startedAt));
                    window.setTimeout(function () {
                        if (typingEl && typingEl.parentNode) typingEl.parentNode.removeChild(typingEl);
                        appendAi(data.reply || '', { source: data.source || '' });
                        appendCards(data.recommendations || []);
                        renderQuickReplies(data.quick_replies || []);
                        finishTurn();
                    }, wait);
                })
                .catch(function () {
                    window.setTimeout(function () {
                        if (typingEl && typingEl.parentNode) typingEl.parentNode.removeChild(typingEl);
                        appendAi(
                            'ごめん、いま少し繋がりにくいみたい💦 もう一度送ってもらえる？\n(通信環境が悪い時にもこの表示が出るよ)',
                            { instant: true }
                        );
                        renderQuickReplies(['再試行', '別のお店を提案して', '未経験OKに絞って']);
                        finishTurn();
                    }, 500);
                });
        }

        function finishTurn() {
            isBusy = false;
            if (sendBtn) sendBtn.disabled = false;
            if (input) {
                input.disabled = false;
                input.focus();
            }
        }

        // ------------------------------------------------------------
        // 「会話をリセット」ボタン（動的注入）
        // ------------------------------------------------------------
        function injectResetButton() {
            if (root.querySelector('[data-ai-reset]')) return;
            var head = root.querySelector('.ai-chat__header');
            if (!head) return;
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ai-chat__reset';
            btn.setAttribute('data-ai-reset', '');
            btn.setAttribute('aria-label', '会話をリセット');
            btn.innerHTML = '<i class="fas fa-arrow-rotate-left"></i>';
            btn.title = '会話をリセット';
            btn.addEventListener('click', function () {
                if (isBusy) return;
                transcript = [];
                clearTranscript();
                thread.innerHTML = '';
                initialGreeting();
            });
            head.appendChild(btn);
        }

        // ------------------------------------------------------------
        // 起動時
        // ------------------------------------------------------------
        function initialGreeting() {
            var greet = 'こんにちは✨ あなたにピッタリのお店、AIが一緒に探すよ！\n例えば「六本木で時給高いお店」「未経験OKでノルマ緩いところ」みたいに教えてくれると見つけやすいよ💎';
            appendAi(greet, { instant: true, silent: true });
            transcript.push({ role: 'ai', content: greet, ts: Date.now() });
            saveTranscript();
            renderQuickReplies([
                '六本木で時給高いお店',
                '未経験OKのお店',
                'ノルマ無しで働きたい',
                '銀座のクラブを見る',
            ]);
        }

        injectResetButton();

        var saved = restoreTranscript();
        if (saved && saved.length) {
            transcript = saved;
            saved.forEach(function (item) {
                if (item.role === 'user') {
                    appendUser(item.content, { silent: true });
                } else if (item.role === 'ai') {
                    appendAi(item.content, { instant: true, silent: true, source: item.source || '' });
                    if (Array.isArray(item.cards)) appendCards(item.cards, { silent: true });
                }
            });
        } else {
            initialGreeting();
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                submitMessage(input ? input.value : '');
            });
        }
    });
})();
