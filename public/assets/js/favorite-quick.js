/* タイムライン行のクイックアクション（KEEP / LIKE）トグル
 * - <a> の中ではなく外側に配置し、stopPropagation で行リンクへの伝播を防ぐ
 * - 結果は API レスポンスで is_active を受けて aria-pressed をトグル
 */
(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    if (!csrfToken) return;

    const endpoint = '/api/favorites/toggle';

    /* ---------------- ページ間の状態同期 ----------------
     * スワイプ → プロフィール詳細 → 「戻る」(bfcache) のように画面を行き来すると、
     * サーバーレンダリング時点の古い状態が表示されて挙動がズレて見える。
     * 直近のトグル結果を sessionStorage に保存し、ページ表示時に同じ対象の
     * ボタン / カウンタへ適用することで、全画面で LIKE / KEEP の状態を一致させる。 */
    const SYNC_KEY = 'fav-sync-v1';

    function readSyncStates() {
        try { return JSON.parse(sessionStorage.getItem(SYNC_KEY) || '{}'); } catch (e) { return {}; }
    }
    function writeSyncState(action, itemType, itemId, isActive, likeCount) {
        try {
            const s = readSyncStates();
            s[action + ':' + itemType + ':' + itemId] = {
                a: isActive ? 1 : 0,
                c: (typeof likeCount === 'number') ? likeCount : null,
            };
            sessionStorage.setItem(SYNC_KEY, JSON.stringify(s));
        } catch (e) { /* private mode 等では同期なしで続行 */ }
    }
    function applySyncStates() {
        const s = readSyncStates();
        Object.keys(s).forEach(function (key) {
            const st = s[key];
            const sep1 = key.indexOf(':');
            const sep2 = key.indexOf(':', sep1 + 1);
            if (sep1 < 0 || sep2 < 0) return;
            const action = key.slice(0, sep1);
            const itemType = key.slice(sep1 + 1, sep2);
            const itemId = key.slice(sep2 + 1);
            const isActive = st.a === 1;

            document.querySelectorAll(
                '[data-fav-toggle][data-action="' + action + '"][data-item-type="' + itemType + '"][data-item-id="' + itemId + '"]'
            ).forEach(function (btn) {
                btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                btn.classList.toggle('is-active', isActive);
                if (action === 'like' && typeof st.c === 'number') {
                    const cntEl = btn.querySelector('[data-fav-count]');
                    if (cntEl) cntEl.textContent = st.c.toLocaleString();
                }
            });
            if (action === 'like' && typeof st.c === 'number') {
                document.querySelectorAll('[data-fav-count-target="' + itemType + ':' + itemId + '"]')
                    .forEach(function (el) { el.textContent = st.c.toLocaleString(); });
            }
        });
    }
    document.addEventListener('DOMContentLoaded', applySyncStates);
    // bfcache から復元されたときも最新のローカル状態を反映
    window.addEventListener('pageshow', function (e) { if (e.persisted) applySyncStates(); });

    function getCookieValue(name) {
        const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[\-.+*]/g, '\\$&') + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : null;
    }

    /* ---------------- 状態適用ヘルパ ----------------
       対象（action × item）に紐づくページ内の全ボタン・全カウンタへ一括反映する。
       likeCount が null のときはカウントは触らない。 */
    function applyStateToDom(action, itemType, itemId, isActive, likeCount) {
        document.querySelectorAll(
            '[data-fav-toggle][data-action="' + action + '"][data-item-type="' + itemType + '"][data-item-id="' + itemId + '"]'
        ).forEach(function (el) {
            el.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            el.classList.toggle('is-active', isActive);
            if (action === 'like' && typeof likeCount === 'number') {
                const cntEl = el.querySelector('[data-fav-count]');
                if (cntEl) cntEl.textContent = likeCount.toLocaleString();
            }
        });
        if (action === 'like' && typeof likeCount === 'number') {
            document.querySelectorAll('[data-fav-count-target="' + itemType + ':' + itemId + '"]')
                .forEach(function (el) { el.textContent = likeCount.toLocaleString(); });
        }
    }

    function readDisplayedCount(btn) {
        const cntEl = btn.querySelector('[data-fav-count]');
        if (!cntEl) return null;
        const n = parseInt(String(cntEl.textContent).replace(/[^\d]/g, ''), 10);
        return Number.isFinite(n) ? n : null;
    }

    async function toggleFavorite(btn) {
        if (btn.classList.contains('is-busy')) return;
        btn.classList.add('is-busy');

        const payload = {
            action: btn.dataset.action,
            item_type: btn.dataset.itemType,
            item_id: btn.dataset.itemId,
        };

        /* ---- 楽観的更新：押した瞬間に UI を反転（Tinder 的な即応性）----
           失敗時は prev の状態にロールバックする。 */
        const prevActive = btn.getAttribute('aria-pressed') === 'true';
        const prevCount = readDisplayedCount(btn);
        const nextActive = !prevActive;
        const nextCount = (payload.action === 'like' && prevCount !== null)
            ? Math.max(0, prevCount + (nextActive ? 1 : -1))
            : null;
        applyStateToDom(payload.action, payload.item_type, payload.item_id, nextActive, nextCount);
        if (navigator.vibrate) { try { navigator.vibrate(10); } catch (e) {} }

        function rollback() {
            applyStateToDom(payload.action, payload.item_type, payload.item_id, prevActive, prevCount);
        }

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            if (res.status === 401) {
                // 未ログイン → ログイン画面へ（route('login.demo') = /login）
                rollback();
                window.location.href = '/login';
                return;
            }
            if (res.status === 419) {
                // CSRF トークン失効（長時間放置後など）→ リロードで再取得
                rollback();
                showToast('セッションの有効期限が切れました。再読み込みします…');
                setTimeout(function () { window.location.reload(); }, 900);
                return;
            }
            if (res.status === 422) {
                rollback();
                const data = await res.json().catch(() => ({}));
                showToast(data.error || '操作できませんでした');
                return;
            }
            if (!res.ok) {
                rollback();
                showToast('通信エラーが発生しました');
                return;
            }

            // サーバーの正の値で最終確定（楽観値とズレていればここで補正される）
            const data = await res.json();
            const isActive = !!data.is_active;
            const likeCount = (typeof data.like_count === 'number') ? data.like_count : null;
            applyStateToDom(payload.action, payload.item_type, payload.item_id, isActive, likeCount);
            // 他画面（bfcache で戻った時など）との状態同期用に保存
            writeSyncState(payload.action, payload.item_type, payload.item_id, isActive, likeCount);

            // カウントバッジは検索リストでは表示しない方針
            //（KEEP 数は本人以外に非公開・LIKE 数はスワイプ/プロフィール画面のみ）。
            // 既存のバッジ要素が残っていれば掃除だけする。
            const staleCount = btn.querySelector('.tl-row__action-count');
            if (staleCount) staleCount.remove();

            // interaction 一覧では「解除した」=「行が消える」UX に寄せる
            if (!isActive) {
                const row = btn.closest('[data-fav-remove-on-deactivate]');
                if (row) {
                    row.classList.add('tl-row--removing');
                    setTimeout(function () { row.remove(); }, 280);
                }
            }

            // item_type に応じて「相手＝お店 / キャスト」を切替
            const partnerLabel = payload.item_type === 'shop' ? 'お店' : 'キャスト';

            if (payload.action === 'like') {
                // ✨ LIKE = 相手に通知が届く（公開アクション）
                showToast(
                    isActive
                        ? `💜 ${partnerLabel}に「いいね」を届けました`
                        : `${partnerLabel}への「いいね」を取り消しました`,
                    isActive ? 'like' : null
                );
            } else {
                // 🔖 KEEP = 自分だけのリスト（プライベート）
                showToast(
                    isActive
                        ? '🔖 あなたのキープリストに保存しました'
                        : 'キープを解除しました',
                    isActive ? 'keep' : null
                );
            }
        } catch (e) {
            rollback();
            showToast('通信エラーが発生しました');
        } finally {
            btn.classList.remove('is-busy');
        }
    }

    let toastTimer = null;
    let toastEl = null;
    function ensureToast() {
        if (toastEl) return toastEl;
        toastEl = document.createElement('div');
        toastEl.className = 'fav-toast';
        toastEl.setAttribute('role', 'status');
        document.body.appendChild(toastEl);
        return toastEl;
    }
    function showToast(msg, variant) {
        if (!msg) return;
        const el = ensureToast();
        el.textContent = msg;
        // variant: 'like' (mauve pink) / 'keep' (gold) / null (default)
        el.classList.remove('is-like', 'is-keep');
        if (variant === 'like') el.classList.add('is-like');
        else if (variant === 'keep') el.classList.add('is-keep');
        el.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { el.classList.remove('is-visible'); }, 1900);
    }

    /* ---------------- クリック捕捉 ----------------
       バブリング委譲だと、途中の要素（スワイプカードの stop-propagation 等）が
       stopPropagation するとハンドラまで届かず「押しても反応しない」事故が起きる。
       キャプチャ段階（document → target の下り）で捕捉することで、
       どの画面のどんな入れ子構造でも確実に1回だけ処理する。 */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-fav-toggle]');
        if (!btn || btn.disabled) return;
        e.preventDefault();
        e.stopPropagation();
        toggleFavorite(btn);
    }, true);
})();
