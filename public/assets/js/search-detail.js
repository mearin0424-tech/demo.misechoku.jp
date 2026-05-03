/**
 * 検索：ミニマルバー・並び替えパネル・詳細検索（FAB）・条件サマリ
 */
(function () {
    var keywordInput = document.getElementById('search-keyword');
    var simpleSubmitBtn = document.getElementById('search-keyword-submit');
    var sortTrigger = document.getElementById('search-sort-trigger');
    var sortPanel = document.getElementById('search-sort-panel');
    var sortCurrent = document.getElementById('search-sort-current');
    var modal = document.getElementById('detail-search-modal');
    var openBtn = document.getElementById('open-detail-search');
    var form = modal ? document.getElementById('detail-search-form') : null;
    var badgeEl = document.getElementById('detail-search-badge');
    var summaryEl = document.getElementById('search-condition-summary');
    var summaryTextEl = document.getElementById('search-condition-summary-text');
    var locationOptions = modal ? modal.querySelectorAll('.detail-search-location-option') : [];
    var distanceSlider = modal ? modal.querySelector('#search-distance-km') : null;
    var distanceValueEl = modal ? modal.querySelector('#search-distance-value') : null;

    function closeSortPanel() {
        if (!sortPanel || !sortTrigger) return;
        sortPanel.hidden = true;
        sortTrigger.setAttribute('aria-expanded', 'false');
    }

    function toggleSortPanel() {
        if (!sortPanel || !sortTrigger) return;
        var open = sortPanel.hidden;
        sortPanel.hidden = !open;
        sortTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    if (sortTrigger && sortPanel) {
        sortTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleSortPanel();
        });
        document.addEventListener('click', function (e) {
            if (sortPanel.hidden) return;
            if (sortTrigger.contains(e.target) || sortPanel.contains(e.target)) return;
            closeSortPanel();
        });
    }

    if (sortPanel) {
        sortPanel.querySelectorAll('[data-search-sort-value]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var v = btn.getAttribute('data-search-sort-value');
                if (sortCurrent) sortCurrent.value = v || '';
                sortPanel.querySelectorAll('[data-search-sort-value]').forEach(function (b) {
                    b.classList.toggle('is-active', b.getAttribute('data-search-sort-value') === v);
                });
                closeSortPanel();
                doSearch(buildSearchParams());
            });
        });
    }

    function openModal() {
        if (!modal) return;
        closeSortPanel();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (modal && openBtn) {
        openBtn.addEventListener('click', openModal);
        modal.querySelectorAll('[data-close-modal]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (modal.classList.contains('is-open')) {
                closeModal();
                return;
            }
            if (sortPanel && !sortPanel.hidden) closeSortPanel();
        });
    }

    locationOptions.forEach(function (label) {
        var radio = label.querySelector('input[type="radio"]');
        if (!radio) return;
        function syncSelected() {
            locationOptions.forEach(function (l) { l.classList.remove('is-selected'); });
            if (radio.checked) label.classList.add('is-selected');
        }
        radio.addEventListener('change', syncSelected);
        syncSelected();
    });

    if (distanceSlider && distanceValueEl) {
        function updateDistanceOutput() {
            var v = distanceSlider.value;
            distanceValueEl.textContent = v === '40' ? '40km以上' : v + 'km';
            var min = Number(distanceSlider.min || 0);
            var max = Number(distanceSlider.max || 100);
            var progress = max > min ? ((Number(v) - min) / (max - min)) * 100 : 0;
            distanceSlider.style.setProperty('--detail-search-slider-progress', progress + '%');
        }
        distanceSlider.addEventListener('input', updateDistanceOutput);
        updateDistanceOutput();
    }

    if (modal) {
        modal.querySelectorAll('[data-accordion]').forEach(function (block) {
            var head = block.querySelector('[data-accordion-trigger]');
            var body = block.querySelector('.detail-search-accordion__body');
            var icon = block.querySelector('.detail-search-accordion__icon');
            if (!head || !body) return;

            function syncAccordion(isOpen) {
                body.hidden = !isOpen;
                block.setAttribute('data-open', isOpen ? 'true' : 'false');
                head.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                if (icon) icon.textContent = isOpen ? '−' : '+';
            }

            syncAccordion(block.getAttribute('data-open') === 'true');
            head.addEventListener('click', function () {
                syncAccordion(body.hidden);
            });
        });
    }

    function countConditions() {
        if (!form) return 0;
        var n = 0;
        form.querySelectorAll('input[type="checkbox"]:checked').forEach(function () { n++; });
        form.querySelectorAll('select').forEach(function (select) {
            if (select.value) n++;
        });
        return n;
    }

    function getCheckedLabels(container) {
        var values = [];
        container.querySelectorAll('input[type="checkbox"]:checked').forEach(function (input) {
            var label = input.closest('label');
            var textEl = label ? label.querySelector('span:last-child') : null;
            var text = textEl ? textEl.textContent.trim() : input.value;
            if (text) values.push(text);
        });

        container.querySelectorAll('select').forEach(function (select) {
            if (!select.value) return;
            var option = select.options[select.selectedIndex];
            if (option && option.textContent) {
                values.push(option.textContent.trim());
            }
        });

        return values;
    }

    function getSummaryLines() {
        if (!form) return [];
        var lines = [];
        form.querySelectorAll('[data-summary-group]').forEach(function (group) {
            var label = group.getAttribute('data-summary-group');
            var values = getCheckedLabels(group);
            if (label && values.length) {
                lines.push(label + '：' + values.join('・'));
            }
        });
        return lines;
    }

    function updateSelectionBadges() {
        if (!form) return;
        form.querySelectorAll('[data-selection-count]').forEach(function (badge) {
            var scope = badge.closest('[data-summary-group], .detail-search-section, .detail-search-accordion') || form;
            var count = scope.querySelectorAll('input[type="checkbox"]:checked').length;
            if (count > 0) {
                badge.hidden = false;
                badge.textContent = count + '件選択中';
            } else {
                badge.hidden = true;
                badge.textContent = badge.getAttribute('data-empty-label') || '';
            }
        });
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

        updateSelectionBadges();
    }

    if (form) {
        form.addEventListener('change', updateBadgeAndSummary);
        updateBadgeAndSummary();
    }

    if (modal) {
        var resetBtn = modal.querySelector('[data-detail-search-reset]');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (!form) return;
                form.querySelectorAll('input[type="checkbox"]').forEach(function (c) { c.checked = false; });
                form.querySelectorAll('input[type="text"]').forEach(function (i) { i.value = ''; });
                form.querySelectorAll('input[type="radio"]').forEach(function (r) {
                    r.checked = r.value === 'current';
                });
                form.querySelectorAll('select').forEach(function (select) {
                    select.value = '';
                });
                var distanceInput = form.querySelector('input[name="distance_km"]');
                if (distanceInput) {
                    distanceInput.value = 20;
                    if (distanceValueEl) distanceValueEl.textContent = '20km';
                    distanceInput.dispatchEvent(new Event('input'));
                }
                locationOptions.forEach(function (l) { l.classList.remove('is-selected'); });
                var firstLocation = modal.querySelector('.detail-search-location-option input[value="current"]');
                if (firstLocation) firstLocation.closest('.detail-search-location-option').classList.add('is-selected');
                if (keywordInput) keywordInput.value = '';
                updateBadgeAndSummary();
            });
        }
    }

    function buildSearchParams(extraParams) {
        var params = [];
        if (keywordInput && keywordInput.value.trim()) {
            params.push('keyword=' + encodeURIComponent(keywordInput.value.trim()));
        }
        if (sortCurrent && sortCurrent.value) {
            params.push('sort=' + encodeURIComponent(sortCurrent.value));
        }
        if (form) {
            form.querySelectorAll('input[type="radio"]:checked').forEach(function (r) {
                params.push(r.name + '=' + encodeURIComponent(r.value));
            });
            var distanceInput = form.querySelector('input[name="distance_km"]');
            if (distanceInput && distanceInput.value) {
                params.push('distance_km=' + encodeURIComponent(distanceInput.value));
            }
            form.querySelectorAll('input[type="checkbox"]:checked').forEach(function (c) {
                if (c.name && c.value) params.push(c.name + '=' + encodeURIComponent(c.value));
            });
            form.querySelectorAll('select').forEach(function (select) {
                if (select.name && select.value) params.push(select.name + '=' + encodeURIComponent(select.value));
            });
        }
        if (extraParams && typeof extraParams === 'object') {
            Object.keys(extraParams).forEach(function (key) {
                if (extraParams[key] !== null && extraParams[key] !== undefined && extraParams[key] !== '') {
                    params.push(key + '=' + encodeURIComponent(extraParams[key]));
                }
            });
        }
        return params;
    }

    function getSearchUrl() {
        var pathname = window.location.pathname;
        if (/\/search\/ai$/.test(pathname)) {
            return pathname.replace(/\/ai$/, '/search');
        }
        if (/\/search\/(timeline|list)$/.test(pathname)) {
            return pathname.replace(/\/(timeline|list)$/, '');
        }
        return pathname;
    }

    function doSearch(params) {
        var listUrl = getSearchUrl();
        var query = params.length ? '?' + params.join('&') : '';
        window.location.href = listUrl + query;
    }

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

    if (modal) {
        var submitBtn = modal.querySelector('[data-detail-search-submit]');
        if (submitBtn) {
            submitBtn.addEventListener('click', function () {
                closeModal();
                doSearch(buildSearchParams());
            });
        }
    }
})();
