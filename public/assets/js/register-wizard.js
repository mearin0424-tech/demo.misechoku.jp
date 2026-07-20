/**
 * 新規登録：ステップ式ウィザード + 入力データ自動保存 + モダンUI強化
 *
 * ・.register-card を 1 ステップとしてウィザード表示
 * ・すべての入力を sessionStorage に自動保存（ページ離脱・エラー戻りで復元）
 * ・パスワード表示切替、入力アイコン、フォーカスハイライト等のモダンUX
 */
(function () {
    'use strict';

    var STORAGE_PREFIX = 'register-form-draft-v2:';

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('.register-form');
        if (!form) return;

        var role = document.body.classList.contains('page-auth-register-cast') ? 'cast'
            : document.body.classList.contains('page-auth-register-shop') ? 'shop'
            : 'default';
        var STORAGE_KEY = STORAGE_PREFIX + role;

        // ---- 入力の自動保存・復元 ----------------------------------------
        function saveDraft() {
            try {
                var data = {};
                form.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (el) {
                    if (el.type === 'password' || el.type === 'file' || el.type === 'hidden') return;
                    var key = el.name;
                    if (el.type === 'checkbox') {
                        // 配列 name（industry_ids[]等）は複数値、単独は真偽
                        if (/\[\]$/.test(key)) {
                            data[key] = data[key] || [];
                            if (el.checked) data[key].push(el.value);
                        } else {
                            data['@bool:' + key] = el.checked ? '1' : '';
                        }
                    } else if (el.type === 'radio') {
                        if (el.checked) data[key] = el.value;
                    } else {
                        data[key] = el.value;
                    }
                });
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify({ ts: Date.now(), data: data }));
            } catch (e) {}
        }
        function restoreDraft() {
            try {
                var raw = sessionStorage.getItem(STORAGE_KEY);
                if (!raw) return;
                var payload = JSON.parse(raw);
                // 24時間経過は破棄
                if (!payload || !payload.data || (Date.now() - (payload.ts || 0)) > 24 * 3600 * 1000) return;
                var data = payload.data;
                // 通常入力を復元（サーバから old が来ている場合は old 優先）
                form.querySelectorAll('input[name], select[name], textarea[name]').forEach(function (el) {
                    if (el.type === 'password' || el.type === 'file' || el.type === 'hidden') return;
                    var key = el.name;
                    if (el.type === 'checkbox') {
                        if (/\[\]$/.test(key)) {
                            var arr = data[key];
                            if (Array.isArray(arr) && el.value) el.checked = arr.indexOf(el.value) !== -1;
                        } else if (data['@bool:' + key] !== undefined) {
                            el.checked = data['@bool:' + key] === '1';
                        }
                    } else if (el.type === 'radio') {
                        if (data[key] !== undefined) el.checked = (el.value === data[key]);
                    } else if (data[key] !== undefined) {
                        // サーバから old（=既に value あり）が入っている場合は上書きしない
                        if (!el.value) el.value = data[key];
                    }
                });
            } catch (e) {}
        }
        function clearDraft() { try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) {} }

        // ページ表示時に復元（サーバ old と併用）
        restoreDraft();
        // すべての変更で保存
        ['input', 'change'].forEach(function (evt) {
            form.addEventListener(evt, function () { saveDraft(); }, true);
        });

        // 送信成功後（success 表示あり）はドラフトを消す
        if (form.parentElement && form.parentElement.querySelector('.register-alert-success')) {
            clearDraft();
        }
        // 送信成功で遷移した場合に備えて、次回ページで success を確認したい → ここではフォーム到達時に基本残す
        // フォームの submit 完了後にも消せるように、submit イベントで最終保存だけしておく
        form.addEventListener('submit', function () { saveDraft(); });

        // ---- モダンUI強化：パスワード表示切替 ------------------------------
        form.querySelectorAll('input[type="password"]').forEach(function (input) {
            var wrap = document.createElement('span');
            wrap.className = 'rw-pass-wrap';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rw-pass-toggle';
            btn.setAttribute('aria-label', 'パスワードを表示');
            btn.innerHTML = '<i class="fas fa-eye" aria-hidden="true"></i>';
            wrap.appendChild(btn);
            btn.addEventListener('click', function () {
                var showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                btn.setAttribute('aria-label', showing ? 'パスワードを表示' : 'パスワードを隠す');
                btn.querySelector('i').className = showing ? 'fas fa-eye' : 'fas fa-eye-slash';
            });
        });

        // ---- ステップウィザード -------------------------------------------
        // エラー再表示時はウィザード化せず全カード展開（値・エラー箇所を全部見せる）
        if (form.querySelector('.register-alert-error')) {
            // ドラフト復元と併用しつつ全表示のみ
            return;
        }

        var cards = Array.prototype.slice.call(form.querySelectorAll('.register-card'));
        if (cards.length < 2) return;
        var termsCard = cards.find(function (c) { return c.classList.contains('register-card-compact'); }) || null;
        var stepCards = cards.filter(function (c) { return c !== termsCard; });
        var actions = form.querySelector('.register-actions');
        if (!actions) return;
        actions.classList.add('register-actions--wizard-hidden');

        // ネイティブ required を一時退避（非表示要素の submit ブロック回避）
        Array.prototype.forEach.call(form.querySelectorAll('[required]'), function (el) {
            el.removeAttribute('required');
            el.setAttribute('data-was-required', '1');
        });

        var header = document.createElement('div');
        header.className = 'rw-header';
        header.innerHTML =
            '<div class="rw-progress"><span class="rw-progress__bar"></span></div>' +
            '<div class="rw-progress__meta">' +
            '<span class="rw-step-num" data-rw-num></span>' +
            '<span class="rw-step-title" data-rw-title></span>' +
            '</div>' +
            '<p class="rw-draft-hint"><i class="fas fa-cloud" aria-hidden="true"></i> 入力内容は自動的に一時保存されます</p>';
        form.insertBefore(header, form.firstElementChild);

        var nav = document.createElement('div');
        nav.className = 'rw-nav';
        nav.innerHTML =
            '<p class="rw-error" data-rw-error hidden><i class="fas fa-circle-exclamation" aria-hidden="true"></i> <span></span></p>' +
            '<div class="rw-nav__buttons">' +
            '  <button type="button" class="rw-btn rw-btn--back" data-rw-back><i class="fas fa-chevron-left"></i> 戻る</button>' +
            '  <button type="button" class="rw-btn rw-btn--next" data-rw-next>次へ <i class="fas fa-chevron-right"></i></button>' +
            '  <button type="submit" class="rw-btn rw-btn--submit" data-rw-submit hidden><i class="fas fa-check"></i> 登録する</button>' +
            '</div>';
        form.appendChild(nav);

        var backBtn = nav.querySelector('[data-rw-back]');
        var nextBtn = nav.querySelector('[data-rw-next]');
        var submitBtn = nav.querySelector('[data-rw-submit]');
        var errBox = nav.querySelector('[data-rw-error]');
        var errTxt = errBox.querySelector('span');
        var numEl = header.querySelector('[data-rw-num]');
        var titleEl = header.querySelector('[data-rw-title]');
        var barEl = header.querySelector('.rw-progress__bar');

        var step = 0;

        function cardTitle(card) {
            var h = card.querySelector('.register-card-head h2');
            return h ? h.textContent.trim() : '';
        }
        function isVisible(el) { return el.offsetParent !== null; }
        function fieldFilled(field) {
            var input = field.querySelector('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), select, textarea');
            if (input) {
                if (input.type === 'file') return !!(input.files && input.files.length);
                return String(input.value || '').trim() !== '';
            }
            var cb = field.querySelector('input[type="checkbox"]:checked');
            var rb = field.querySelector('input[type="radio"]:checked');
            return !!cb || !!rb;
        }
        function requiredMissing(card) {
            var missing = [];
            card.querySelectorAll('.register-field, .register-check, .metric-field, .bwh-field').forEach(function (field) {
                field.classList.remove('is-missing');
                var em = field.querySelector('em');
                var required = field.hasAttribute('data-required') ||
                    (em && em.textContent.indexOf('必須') !== -1) ||
                    !!field.querySelector('[data-was-required]');
                if (!required) return;
                if (!isVisible(field)) return;
                if (!fieldFilled(field)) missing.push(field);
            });
            missing.forEach(function (f) { f.classList.add('is-missing'); });
            return missing;
        }

        function render() {
            stepCards.forEach(function (c, i) { c.hidden = (i !== step); });
            if (termsCard) termsCard.hidden = (step !== stepCards.length - 1);
            var last = step === stepCards.length - 1;
            nextBtn.hidden = last;
            submitBtn.hidden = !last;
            backBtn.disabled = step === 0;
            errBox.hidden = true;
            numEl.textContent = 'STEP ' + (step + 1) + ' / ' + stepCards.length;
            titleEl.textContent = cardTitle(stepCards[step]);
            barEl.style.width = Math.round(((step + 1) / stepCards.length) * 100) + '%';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        nextBtn.addEventListener('click', function () {
            var missing = requiredMissing(stepCards[step]);
            if (missing.length) {
                errBox.hidden = false;
                errTxt.textContent = '必須項目を入力してください（' + missing.length + '件）';
                missing[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                var input = missing[0].querySelector('input, select, textarea');
                if (input) { try { input.focus({ preventScroll: true }); } catch (e) {} }
                return;
            }
            if (step < stepCards.length - 1) { step++; render(); }
        });
        backBtn.addEventListener('click', function () {
            if (step > 0) { step--; render(); }
        });
        submitBtn.addEventListener('click', function (e) {
            var terms = form.querySelector('input[name="terms"]');
            if (terms && !terms.checked) {
                e.preventDefault();
                errBox.hidden = false;
                errTxt.textContent = '利用規約とプライバシーポリシーに同意してください';
                if (termsCard) termsCard.classList.add('is-missing');
                return;
            }
            var missing = requiredMissing(stepCards[step]);
            if (missing.length) {
                e.preventDefault();
                errBox.hidden = false;
                errTxt.textContent = '必須項目を入力してください（' + missing.length + '件）';
                missing[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        });

        render();
    });
})();
