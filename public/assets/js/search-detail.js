/**
 * 詳細検索モーダル（賃貸検索風）の開閉・アコーディオン・条件サマリ
 * cast/search の「一覧・検索」で使用
 */
(function () {
    var modal = document.getElementById('detail-search-modal');
    var openBtn = document.getElementById('open-detail-search');
    if (!modal || !openBtn) return;

    var form = document.getElementById('detail-search-form');
    var keywordInput = document.getElementById('search-keyword');
    var badgeEl = document.getElementById('detail-search-badge');
    var summaryEl = document.getElementById('search-condition-summary');
    var summaryTextEl = document.getElementById('search-condition-summary-text');

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    openBtn.addEventListener('click', openModal);
    modal.querySelectorAll('[data-close-modal]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    // アコーディオン
    modal.querySelectorAll('[data-accordion]').forEach(function (block) {
        var head = block.querySelector('[data-accordion-trigger]');
        var body = block.querySelector('.detail-search-accordion__body');
        if (!head || !body) return;
        head.addEventListener('click', function () {
            var isOpen = body.hidden;
            body.hidden = !isOpen;
            block.setAttribute('data-open', isOpen ? 'true' : 'false');
            head.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    function countConditions() {
        if (!form) return 0;
        var n = 0;
        form.querySelectorAll('input[type="checkbox"]:checked').forEach(function () { n++; });
        return n;
    }

    function getSummaryLines() {
        if (!form) return [];
        var lines = [];
        form.querySelectorAll('.detail-search-accordion').forEach(function (acc) {
            var label = acc.querySelector('.detail-search-accordion__head span:first-child');
            var checked = acc.querySelectorAll('input[type="checkbox"]:checked');
            if (checked.length) {
                var vals = Array.from(checked).map(function (c) { return c.value; }).join('・');
                lines.push((label ? label.textContent : '') + '：' + vals);
            }
        });
        return lines;
    }

    function updateBadgeAndSummary() {
        var count = countConditions();
        if (badgeEl) {
            badgeEl.textContent = count;
            badgeEl.style.display = count > 0 ? 'inline-flex' : 'none';
        }
        if (summaryTextEl && summaryEl) {
            var lines = getSummaryLines();
            if (lines.length) {
                summaryTextEl.textContent = lines.slice(0, 3).join(' / ') + (lines.length > 3 ? ' …' : '');
                summaryEl.style.display = 'block';
            } else {
                summaryEl.style.display = 'none';
            }
        }
    }

    if (form) {
        form.addEventListener('change', updateBadgeAndSummary);
        updateBadgeAndSummary();
    }

    // 条件をクリア
    modal.querySelector('[data-detail-search-reset]').addEventListener('click', function () {
        if (!form) return;
        form.querySelectorAll('input[type="checkbox"]').forEach(function (c) { c.checked = false; });
        form.querySelectorAll('input[type="text"]').forEach(function (i) { i.value = ''; });
        form.querySelectorAll('input[type="radio"]').forEach(function (r) {
            r.checked = r.value === 'current';
        });
        if (keywordInput) keywordInput.value = '';
        updateBadgeAndSummary();
    });

    function buildSearchParams() {
        var params = ['tab=pane-list'];
        if (keywordInput && keywordInput.value.trim()) {
            params.push('keyword=' + encodeURIComponent(keywordInput.value.trim()));
        }
        if (form) {
            form.querySelectorAll('input[type="radio"]:checked').forEach(function (r) {
                params.push(r.name + '=' + encodeURIComponent(r.value));
            });
            form.querySelectorAll('input[type="checkbox"]:checked').forEach(function (c) {
                if (c.name && c.value) params.push(c.name + '=' + encodeURIComponent(c.value));
            });
        }
        return params;
    }

    function doSearch(params) {
        var base = window.location.pathname;
        window.location.href = base + (params.length ? '?' + params.join('&') : '');
    }

    // 簡単キーワード検索ボタン（入力欄横の「検索」）
    var simpleSubmitBtn = document.getElementById('search-keyword-submit');
    if (simpleSubmitBtn && keywordInput) {
        simpleSubmitBtn.addEventListener('click', function () {
            doSearch(buildSearchParams());
        });
    }
    if (keywordInput) {
        keywordInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                doSearch(buildSearchParams());
            }
        });
    }

    // この条件で検索（詳細検索モーダル内の「この条件で検索」）
    modal.querySelector('[data-detail-search-submit]').addEventListener('click', function () {
        closeModal();
        doSearch(buildSearchParams());
    });
})();
