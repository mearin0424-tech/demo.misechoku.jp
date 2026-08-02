{{-- Emergency help broadcast modal.
     Fetches tier A/B candidates from /shop/help-broadcast/candidates on open,
     lets the shop select up to N recipients + a message body, POSTs to /send. --}}
<div id="help-broadcast-modal" class="help-broadcast-modal" hidden role="dialog" aria-modal="true" aria-labelledby="help-broadcast-title">
    <div class="help-broadcast-modal__overlay" data-help-close></div>
    <div class="help-broadcast-modal__panel">
        <button type="button" class="help-broadcast-modal__close" data-help-close aria-label="閉じる">×</button>
        <h3 id="help-broadcast-title" class="help-broadcast-modal__title">
            <i class="fas fa-bullhorn" aria-hidden="true"></i> 緊急ヘルプの一斉送信
        </h3>
        <p class="help-broadcast-modal__lead">
            「今すぐ入れる」と宣言中／直近ログイン中のキャストへ、同じメッセージを一括送信します。<br>
            <small>スカウト送信の 1 日上限にカウントされます。同じキャストへの再送信は 6 時間おきに制限があります。</small>
        </p>

        <div class="help-broadcast-modal__section">
            <label class="help-broadcast-modal__label">送信メッセージ</label>
            <textarea id="help-broadcast-body" rows="4" maxlength="500"
                      placeholder="例：本日◯時から急遽ヘルプ入れる方いませんか？時給◯円で対応いたします。ご連絡お待ちしています！">今からヘルプで入れませんか？急遽ピンチヒッターを探しています。</textarea>
        </div>

        <div class="help-broadcast-modal__section">
            <label class="help-broadcast-modal__label">
                送信先を選ぶ
                <span class="help-broadcast-modal__count" data-help-count>0 名選択</span>
            </label>
            <div class="help-broadcast-modal__list" data-help-list>
                <p class="help-broadcast-modal__loading">候補を読み込み中...</p>
            </div>
        </div>

        <p class="help-broadcast-modal__feedback" data-help-feedback hidden></p>

        <div class="help-broadcast-modal__actions">
            <button type="button" class="help-broadcast-modal__btn help-broadcast-modal__btn--ghost" data-help-close>キャンセル</button>
            <button type="button" class="help-broadcast-modal__btn help-broadcast-modal__btn--primary" data-help-send disabled>
                <i class="fas fa-paper-plane"></i> 選択したキャストへ送信
            </button>
        </div>
    </div>
</div>

<style>
/* Fixed FAB above bottom nav */
.help-broadcast-fab {
    position: fixed;
    right: calc(env(safe-area-inset-right, 0px) + 16px);
    bottom: calc(var(--footer-height, 75px) + env(safe-area-inset-bottom, 0px) + 20px);
    z-index: 900;
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 18px;
    border-radius: 999px;
    background: linear-gradient(135deg, #f0e6a8 0%, #c5a059 60%, #b58a3c 100%);
    color: #1a1206;
    border: 0;
    font-size: 0.86rem; font-weight: 800;
    letter-spacing: 0.02em;
    box-shadow: 0 8px 20px rgba(197, 160, 89, 0.45), 0 0 0 3px rgba(255, 252, 230, 0.35);
    cursor: pointer;
    transition: transform 0.12s ease, box-shadow 0.15s ease;
}
.help-broadcast-fab:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(197, 160, 89, 0.5); }
.help-broadcast-fab:active { transform: scale(0.96); }
.help-broadcast-fab i { font-size: 0.95rem; }

/* Modal */
.help-broadcast-modal {
    position: fixed; inset: 0; z-index: 3800;
    display: none; align-items: center; justify-content: center;
    padding: 20px 12px;
}
.help-broadcast-modal:not([hidden]) { display: flex; }
.help-broadcast-modal__overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.78); backdrop-filter: blur(4px); }
.help-broadcast-modal__panel {
    position: relative; width: min(500px, 100%); max-height: 90vh;
    display: flex; flex-direction: column;
    background: #fff; color: #1e1a30;
    border-radius: 16px; padding: 20px 18px 16px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.5);
    overflow: hidden;
}
.help-broadcast-modal__close {
    position: absolute; top: 10px; right: 10px;
    background: transparent; border: 0;
    font-size: 1.4rem; color: #8b84a1;
    cursor: pointer; padding: 4px 8px;
}
.help-broadcast-modal__title {
    margin: 0 0 8px; font-size: 1.05rem; font-weight: 800;
    color: #b45309;
    display: flex; align-items: center; gap: 8px;
}
.help-broadcast-modal__lead {
    margin: 0 0 12px; font-size: 0.8rem; color: #4a4560; line-height: 1.55;
}
.help-broadcast-modal__lead small { color: #8b84a1; }
.help-broadcast-modal__section { margin: 10px 0; }
.help-broadcast-modal__label {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.78rem; font-weight: 800; color: #1e1a30;
    margin: 0 0 6px;
}
.help-broadcast-modal__count {
    margin-left: auto;
    padding: 3px 10px; border-radius: 999px;
    background: rgba(180, 83, 9, 0.12); color: #b45309;
    font-size: 0.72rem;
}
.help-broadcast-modal__section textarea {
    width: 100%; box-sizing: border-box;
    padding: 10px 12px; border-radius: 10px;
    border: 1px solid rgba(124,58,237,0.22);
    font-size: 0.88rem; font-family: inherit; resize: vertical;
    min-height: 80px; line-height: 1.55;
}
.help-broadcast-modal__list {
    max-height: 280px; overflow-y: auto;
    border: 1px solid rgba(124,58,237,0.14);
    border-radius: 10px; padding: 4px;
    display: flex; flex-direction: column; gap: 4px;
}
.help-broadcast-modal__loading { text-align: center; padding: 20px; color: #8b84a1; margin: 0; }
.help-broadcast-modal__item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; border-radius: 8px;
    cursor: pointer;
}
.help-broadcast-modal__item:hover { background: rgba(124,58,237,0.05); }
.help-broadcast-modal__item.is-checked { background: rgba(180, 83, 9, 0.08); }
.help-broadcast-modal__item input { margin: 0; }
.help-broadcast-modal__item-name { flex: 1; font-size: 0.86rem; font-weight: 700; color: #1e1a30; }
.help-broadcast-modal__tier {
    padding: 2px 8px; border-radius: 999px;
    font-size: 0.66rem; font-weight: 800; letter-spacing: 0.02em;
}
.help-broadcast-modal__tier--A {
    background: linear-gradient(105deg, #f0e6a8, #c5a059); color: #1a1206;
}
.help-broadcast-modal__tier--B { background: rgba(74, 222, 128, 0.18); color: #059669; }
.help-broadcast-modal__feedback {
    margin: 8px 0 0; padding: 8px 10px; border-radius: 8px;
    font-size: 0.8rem;
}
.help-broadcast-modal__feedback.is-error { background: rgba(220,38,38,0.08); color: #b91c1c; border: 1px solid rgba(220,38,38,0.32); }
.help-broadcast-modal__feedback.is-success { background: rgba(16,185,129,0.08); color: #047857; border: 1px solid rgba(16,185,129,0.32); }
.help-broadcast-modal__actions {
    margin-top: 12px; display: flex; gap: 8px; justify-content: flex-end;
}
.help-broadcast-modal__btn {
    padding: 10px 18px; border-radius: 10px;
    font-size: 0.86rem; font-weight: 700;
    border: 1px solid transparent; cursor: pointer;
}
.help-broadcast-modal__btn--ghost {
    background: #fff; border-color: rgba(124,58,237,0.24); color: #4a4560;
}
.help-broadcast-modal__btn--primary {
    background: linear-gradient(135deg, #f0e6a8, #c5a059);
    color: #1a1206; font-weight: 800;
}
.help-broadcast-modal__btn:disabled { opacity: 0.55; cursor: not-allowed; }
</style>

<script>
(function () {
    var fab = document.getElementById('help-broadcast-fab');
    var modal = document.getElementById('help-broadcast-modal');
    if (!fab || !modal) return;

    var listEl = modal.querySelector('[data-help-list]');
    var countEl = modal.querySelector('[data-help-count]');
    var sendBtn = modal.querySelector('[data-help-send]');
    var feedbackEl = modal.querySelector('[data-help-feedback]');
    var bodyInput = document.getElementById('help-broadcast-body');
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    var candidatesUrl = '{{ route("shop.help-broadcast.candidates") }}';
    var sendUrl = '{{ route("shop.help-broadcast.send") }}';

    function setFeedback(kind, text) {
        if (!feedbackEl) return;
        if (!kind) { feedbackEl.hidden = true; feedbackEl.className = 'help-broadcast-modal__feedback'; return; }
        feedbackEl.className = 'help-broadcast-modal__feedback is-' + kind;
        feedbackEl.textContent = text;
        feedbackEl.hidden = false;
    }

    function updateCount() {
        var checked = listEl.querySelectorAll('input[type=checkbox]:checked').length;
        countEl.textContent = checked + ' 名選択';
        sendBtn.disabled = (checked === 0);
    }

    function loadCandidates() {
        listEl.innerHTML = '<p class="help-broadcast-modal__loading">候補を読み込み中...</p>';
        fetch(candidatesUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.success || !res.items || res.items.length === 0) {
                    listEl.innerHTML = '<p class="help-broadcast-modal__loading">「今すぐ入れる」宣言中／直近アクティブなキャストは現在いません。</p>';
                    return;
                }
                listEl.innerHTML = '';
                res.items.forEach(function (item) {
                    var label = document.createElement('label');
                    label.className = 'help-broadcast-modal__item';
                    label.innerHTML = ''
                        + '<input type="checkbox" value="' + item.id + '">'
                        + '<span class="help-broadcast-modal__item-name">' + item.name + '</span>'
                        + '<span class="help-broadcast-modal__tier help-broadcast-modal__tier--' + item.tier + '">Tier ' + item.tier + '</span>';
                    label.querySelector('input').addEventListener('change', function () {
                        label.classList.toggle('is-checked', this.checked);
                        updateCount();
                    });
                    listEl.appendChild(label);
                });
                updateCount();
            })
            .catch(function () {
                listEl.innerHTML = '<p class="help-broadcast-modal__loading">候補の読み込みに失敗しました。</p>';
            });
    }

    function openModal() {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        setFeedback(null);
        loadCandidates();
    }
    function closeModal() {
        modal.hidden = true;
        document.body.style.overflow = '';
    }

    fab.addEventListener('click', openModal);
    modal.querySelectorAll('[data-help-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
    });

    sendBtn.addEventListener('click', function () {
        setFeedback(null);
        var ids = Array.from(listEl.querySelectorAll('input[type=checkbox]:checked')).map(function (i) { return i.value; });
        var body = (bodyInput.value || '').trim();
        if (ids.length === 0) { setFeedback('error', '送信先を 1 名以上選択してください。'); return; }
        if (body.length < 5) { setFeedback('error', 'メッセージ本文は 5 文字以上入力してください。'); return; }
        sendBtn.disabled = true;
        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ cast_ids: ids, body: body })
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (res) {
            if (res.ok && res.body.success) {
                setFeedback('success', res.body.message + (res.body.skipped_count > 0 ? '（' + res.body.skipped_count + ' 名はクールダウン中のためスキップしました）' : ''));
                setTimeout(closeModal, 2200);
            } else {
                sendBtn.disabled = false;
                setFeedback('error', (res.body && res.body.message) || '送信に失敗しました。');
            }
        })
        .catch(function () {
            sendBtn.disabled = false;
            setFeedback('error', '通信エラーで送信できませんでした。');
        });
    });
})();
</script>
