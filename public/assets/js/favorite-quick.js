/* タイムライン行のクイックアクション（KEEP / LIKE）トグル
 * - <a> の中ではなく外側に配置し、stopPropagation で行リンクへの伝播を防ぐ
 * - 結果は API レスポンスで is_active を受けて aria-pressed をトグル
 */
(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    if (!csrfToken) return;

    const endpoint = '/api/favorites/toggle';

    function getCookieValue(name) {
        const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[\-.+*]/g, '\\$&') + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : null;
    }

    async function toggleFavorite(btn) {
        if (btn.classList.contains('is-busy')) return;
        btn.classList.add('is-busy');

        const payload = {
            action: btn.dataset.action,
            item_type: btn.dataset.itemType,
            item_id: btn.dataset.itemId,
        };

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
                window.location.href = '/login';
                return;
            }
            if (res.status === 419) {
                // CSRF トークン失効（長時間放置後など）→ リロードで再取得
                showToast('セッションの有効期限が切れました。再読み込みします…');
                setTimeout(function () { window.location.reload(); }, 900);
                return;
            }
            if (res.status === 422) {
                const data = await res.json().catch(() => ({}));
                showToast(data.error || '操作できませんでした');
                return;
            }
            if (!res.ok) {
                showToast('通信エラーが発生しました');
                return;
            }

            const data = await res.json();
            const isActive = !!data.is_active;
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            // スワイプカード等、is-active クラスで見た目を切り替える画面にも追従
            btn.classList.toggle('is-active', isActive);

            // LIKE カウント（スワイプ / プロフィールのみ表示）：サーバー値で同期
            if (payload.action === 'like' && typeof data.like_count === 'number') {
                const cntEl = btn.querySelector('[data-fav-count]');
                if (cntEl) cntEl.textContent = data.like_count.toLocaleString();
                // ボタン外のカウンタ（プロフィールヒーローの ❤ 数など）も同期
                document.querySelectorAll('[data-fav-count-target="' + payload.item_type + ':' + payload.item_id + '"]')
                    .forEach(function (el) { el.textContent = data.like_count.toLocaleString(); });
            }

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

    // event delegation
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-fav-toggle]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        toggleFavorite(btn);
    });
})();
