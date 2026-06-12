/**
 * ミセチョク — AIレコメンド自由入力チャット（テンプレ駆動）
 *
 * data-ai-chat-root をルートとして、自由入力 → タイピング演出 → AI 返答 → 店舗カード を描画する。
 * 真の LLM は呼ばず、サーバ側の /cast/search/ai-chat (AiChatTemplateService) がテンプレを返す。
 */
(function () {
    'use strict';

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

        var history = []; // 簡易履歴（先方は今は読まないが互換のため）
        var isBusy = false;

        // --- 描画ヘルパ ---
        function scrollToBottom() {
            window.setTimeout(function () {
                thread.scrollTop = thread.scrollHeight;
            }, 30);
        }

        function appendUser(text) {
            var div = document.createElement('div');
            div.className = 'ai-chat__msg ai-chat__msg--user';
            div.innerHTML =
                '<div class="ai-chat__bubble ai-chat__bubble--user">' + escapeHtml(text) + '</div>';
            thread.appendChild(div);
            scrollToBottom();
        }

        function appendTyping() {
            var div = document.createElement('div');
            div.className = 'ai-chat__msg ai-chat__msg--ai ai-chat__msg--typing';
            div.innerHTML =
                '<div class="ai-chat__avatar">' +
                (avatar ? '<img src="' + escapeHtml(avatar) + '" alt="AIアシスタント">' : '<i class="fas fa-robot"></i>') +
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

        function appendAi(text) {
            var div = document.createElement('div');
            div.className = 'ai-chat__msg ai-chat__msg--ai';
            div.innerHTML =
                '<div class="ai-chat__avatar">' +
                (avatar ? '<img src="' + escapeHtml(avatar) + '" alt="AIアシスタント">' : '<i class="fas fa-robot"></i>') +
                '</div>' +
                '<div class="ai-chat__bubble ai-chat__bubble--ai">' + formatReply(text) + '</div>';
            thread.appendChild(div);
            scrollToBottom();
        }

        function appendCards(recs) {
            if (!recs || !recs.length) return;
            var wrap = document.createElement('div');
            wrap.className = 'ai-chat__cards';

            recs.forEach(function (r) {
                var area = [r.pref, r.city].filter(Boolean).join(' ');
                var wage = r.wage ? '時給 ' + r.wage.toLocaleString() + '円〜' : '';
                var reward = r.reward ? '採用報酬 ' + r.reward.toLocaleString() + '円' : '';
                var meta = [area, wage, reward].filter(Boolean).join(' / ');

                wrap.innerHTML +=
                    '<a href="' + escapeHtml(r.url) + '" class="ai-chat__card" target="_blank" rel="noopener">' +
                    '  <div class="ai-chat__card-thumb">' +
                    '    <img src="' + escapeHtml(r.image) + '" alt="" loading="lazy">' +
                    '  </div>' +
                    '  <div class="ai-chat__card-body">' +
                    '    <p class="ai-chat__card-title">' + escapeHtml(r.name) + '</p>' +
                    '    <p class="ai-chat__card-meta">' + escapeHtml(meta) + '</p>' +
                    '    <p class="ai-chat__card-reason"><i class="fas fa-sparkles"></i> ' + escapeHtml(r.reason) + '</p>' +
                    '  </div>' +
                    '  <span class="ai-chat__card-cta">求人を見る →</span>' +
                    '</a>';
            });

            thread.appendChild(wrap);
            scrollToBottom();
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

        // --- 送信 ---
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
            history.push({ role: 'user', content: msg });

            // 体感のためにわざと 0.9〜1.6 秒遅延（タイピング演出）
            var delay = 900 + Math.floor(Math.random() * 700);

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrf(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message: msg, history: history }),
            })
                .then(function (res) {
                    if (!res.ok) throw new Error('AI chat failed: ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    window.setTimeout(function () {
                        if (typingEl && typingEl.parentNode) typingEl.parentNode.removeChild(typingEl);
                        appendAi(data.reply || '');
                        appendCards(data.recommendations || []);
                        renderQuickReplies(data.quick_replies || []);
                        history.push({ role: 'ai', content: data.reply || '' });
                        finishTurn();
                    }, delay);
                })
                .catch(function () {
                    window.setTimeout(function () {
                        if (typingEl && typingEl.parentNode) typingEl.parentNode.removeChild(typingEl);
                        appendAi('ごめん、いま少し調子悪いみたい💦 もう一回送ってもらえる？');
                        finishTurn();
                    }, 600);
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

        // --- 初期化（オープニング） ---
        appendAi('こんにちは✨ あなたにピッタリのお店、AIが一緒に探すよ！\n例えば「六本木で時給高いお店」「未経験OKでノルマ緩いところ」みたいに教えてくれると見つけやすいよ💎');
        renderQuickReplies([
            '六本木で時給高いお店',
            '未経験OKのお店',
            'ノルマ無しで働きたい',
            '銀座のクラブを見る',
        ]);

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                submitMessage(input ? input.value : '');
            });
        }
    });
})();
