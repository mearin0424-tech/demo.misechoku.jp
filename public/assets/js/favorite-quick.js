/* タイムライン行・スワイプ・プロフィールのクイックアクション（KEEP）トグル
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
     * ボタンへ適用することで、全画面で KEEP の状態を一致させる。 */
    const SYNC_KEY = 'fav-sync-v1';

    function readSyncStates() {
        try { return JSON.parse(sessionStorage.getItem(SYNC_KEY) || '{}'); } catch (e) { return {}; }
    }
    function writeSyncState(action, itemType, itemId, isActive) {
        try {
            const s = readSyncStates();
            s[action + ':' + itemType + ':' + itemId] = { a: isActive ? 1 : 0 };
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
            });
        });
    }
    document.addEventListener('DOMContentLoaded', applySyncStates);
    // bfcache から復元されたときも最新のローカル状態を反映
    window.addEventListener('pageshow', function (e) { if (e.persisted) applySyncStates(); });

    /* ---------------- 状態適用ヘルパ ----------------
       対象（action × item）に紐づくページ内の全ボタンへ一括反映する。 */
    function applyStateToDom(action, itemType, itemId, isActive) {
        document.querySelectorAll(
            '[data-fav-toggle][data-action="' + action + '"][data-item-type="' + itemType + '"][data-item-id="' + itemId + '"]'
        ).forEach(function (el) {
            el.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            el.classList.toggle('is-active', isActive);
        });
    }

    async function toggleFavorite(btn) {
        if (btn.classList.contains('is-busy')) return;
        btn.classList.add('is-busy');

        const payload = {
            action: btn.dataset.action,
            item_type: btn.dataset.itemType,
            item_id: btn.dataset.itemId,
        };

        /* ---- 楽観的更新：押した瞬間に UI を反転 ----
           失敗時は prev の状態にロールバックする。 */
        const prevActive = btn.getAttribute('aria-pressed') === 'true';
        const nextActive = !prevActive;
        applyStateToDom(payload.action, payload.item_type, payload.item_id, nextActive);
        if (navigator.vibrate) { try { navigator.vibrate(10); } catch (e) {} }

        function rollback() {
            applyStateToDom(payload.action, payload.item_type, payload.item_id, prevActive);
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
            applyStateToDom(payload.action, payload.item_type, payload.item_id, isActive);
            // 他画面（bfcache で戻った時など）との状態同期用に保存
            writeSyncState(payload.action, payload.item_type, payload.item_id, isActive);

            // interaction 一覧では「解除した」=「行が消える」UX に寄せる
            if (!isActive) {
                const row = btn.closest('[data-fav-remove-on-deactivate]');
                if (row) {
                    row.classList.add('tl-row--removing');
                    setTimeout(function () { row.remove(); }, 280);
                }
            }

            // 🔖 KEEP = 自分だけのリスト（プライベート）
            showToast(
                isActive
                    ? '🔖 あなたのキープリストに保存しました'
                    : 'キープを解除しました',
                isActive ? 'keep' : null
            );
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
        // variant: 'keep' (gold) / null (default)
        el.classList.remove('is-like', 'is-keep');
        if (variant === 'keep') el.classList.add('is-keep');
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
