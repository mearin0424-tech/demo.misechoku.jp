/**
 * 新規登録：質問形式ウィザード + 「必須だけサクッと登録」モード
 *
 * - 既存フォームの .register-card をステップに分割し、1画面1セクションで進める
 * - 各ステップで「必須」マーク付きフィールドを検証してから次へ
 * - クイックモード：任意セクションを自動スキップ + 必須以外のフィールドを非表示
 * - サーバ検証エラーで再表示された場合（register-alert-error あり）は
 *   ウィザード化せず従来の全項目表示にフォールバックする
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('.register-form');
        if (!form) return;

        var cards = Array.prototype.slice.call(form.querySelectorAll('.register-card'));
        var termsCard = null;
        cards.forEach(function (c) { if (c.classList.contains('register-card-compact')) termsCard = c; });
        var stepCards = cards.filter(function (c) { return c !== termsCard; });
        if (stepCards.length < 2) return;

        var actions = form.querySelector('.register-actions');
        if (form.querySelector('.register-alert-error')) return; // エラー再表示時は全項目表示

        // ---------- ヘルパ ----------
        function fieldIsRequired(field) {
            var em = field.querySelector('span em');
            if (em && em.textContent.indexOf('必須') !== -1) return true;
            return !!field.querySelector('[data-was-required]');
        }
        function fieldInput(field) {
            return field.querySelector('input:not([type="hidden"]), select, textarea');
        }
        function isVisible(el) { return el.offsetParent !== null; }
        function fieldFilled(field) {
            var input = fieldInput(field);
            if (!input) return true;
            if (input.type === 'file') return !!(input.files && input.files.length > 0);
            if (input.type === 'checkbox') return input.checked;
            return String(input.value || '').trim() !== '';
        }
        function cardTitle(card) {
            var h = card.querySelector('.register-card-head h2');
            return h ? h.textContent.trim() : '';
        }

        // ネイティブ required は非表示要素で submit をブロックするため外す（サーバ検証が正）
        Array.prototype.forEach.call(form.querySelectorAll('[required]'), function (el) {
            el.removeAttribute('required');
            el.setAttribute('data-was-required', '1');
        });

        // ---------- UI 部品 ----------
        var intro = document.createElement('section');
        intro.className = 'register-card register-mode-intro';
        intro.innerHTML = ''
            + '<h2 class="register-mode-title">登録方法を選んでください</h2>'
            + '<button type="button" class="register-mode-btn register-mode-btn--wizard" data-mode="wizard">'
            + '  <strong><i class="fas fa-comments" aria-hidden="true"></i> 質問に答えて登録</strong>'
            + '  <small>1ステップずつ質問形式で進みます（おすすめ）</small>'
            + '</button>'
            + '<button type="button" class="register-mode-btn" data-mode="quick">'
            + '  <strong><i class="fas fa-bolt" aria-hidden="true"></i> 必須だけサクッと登録</strong>'
            + '  <small>必須項目のみ表示。最短で登録を済ませたい方向け</small>'
            + '</button>';
        form.insertBefore(intro, form.firstElementChild);

        var progress = document.createElement('div');
        progress.className = 'register-wizard-progress';
        progress.hidden = true;
        progress.innerHTML = '<div class="register-wizard-progress__bar"><span></span></div><p class="register-wizard-progress__label"></p>';
        form.insertBefore(progress, intro.nextSibling);

        var quickBar = document.createElement('div');
        quickBar.className = 'register-quick-note';
        quickBar.hidden = true;
        quickBar.innerHTML = '<i class="fas fa-bolt" aria-hidden="true"></i><span>必須項目のみ表示中</span>'
            + '<button type="button" class="register-quick-full">すべての項目を表示</button>';
        form.insertBefore(quickBar, progress.nextSibling);

        var nav = document.createElement('div');
        nav.className = 'register-wizard-nav';
        nav.hidden = true;
        nav.innerHTML = ''
            + '<p class="register-wizard-error" hidden>未入力の必須項目があります</p>'
            + '<div class="register-wizard-nav__row">'
            + '  <button type="button" class="register-wizard-back">戻る</button>'
            + '  <button type="button" class="register-wizard-next">次へ <i class="fas fa-arrow-right" aria-hidden="true"></i></button>'
            + '</div>'
            + '<button type="button" class="register-wizard-switch">必須だけサクッと登録に切り替える</button>';
        var backBtn = nav.querySelector('.register-wizard-back');
        var nextBtn = nav.querySelector('.register-wizard-next');
        var errEl = nav.querySelector('.register-wizard-error');

        var step = 0;

        function hideAllSteps() {
            stepCards.forEach(function (c) { c.hidden = true; });
            if (termsCard) termsCard.hidden = true;
            if (actions) actions.hidden = true;
            nav.hidden = true;
            progress.hidden = true;
        }

        function renderStep() {
            hideAllSteps();
            var card = stepCards[step];
            card.hidden = false;
            var last = step === stepCards.length - 1;
            if (last) {
                if (termsCard) termsCard.hidden = false;
                if (actions) actions.hidden = false;
            }
            nav.hidden = false;
            nextBtn.hidden = last;
            if (last) {
                form.insertBefore(nav, termsCard || actions);
            } else {
                card.parentNode.insertBefore(nav, card.nextSibling);
            }
            backBtn.disabled = step === 0;
            errEl.hidden = true;
            progress.hidden = false;
            progress.querySelector('span').style.width = Math.round(((step + 1) / stepCards.length) * 100) + '%';
            progress.querySelector('.register-wizard-progress__label').textContent =
                'STEP ' + (step + 1) + ' / ' + stepCards.length + '　' + cardTitle(card);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function validateCard(card) {
            var missing = [];
            Array.prototype.forEach.call(card.querySelectorAll('.register-field'), function (field) {
                field.classList.remove('is-missing');
                if (!fieldIsRequired(field)) return;
                if (!isVisible(field)) return;
                if (!fieldFilled(field)) missing.push(field);
            });
            missing.forEach(function (f) { f.classList.add('is-missing'); });
            return missing;
        }

        nextBtn.addEventListener('click', function () {
            var missing = validateCard(stepCards[step]);
            if (missing.length) {
                errEl.hidden = false;
                missing[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                var input = fieldInput(missing[0]);
                if (input && input.focus) { try { input.focus({ preventScroll: true }); } catch (e) {} }
                return;
            }
            errEl.hidden = true;
            if (step < stepCards.length - 1) {
                step++;
                renderStep();
            }
        });
        backBtn.addEventListener('click', function () {
            if (step > 0) { step--; renderStep(); }
        });

        // ---------- クイックモード ----------
        function setQuickVisibility(on) {
            stepCards.forEach(function (card) {
                if (card.hasAttribute('data-skip-section')) {
                    // 任意セクション：スキップを ON にしてカードごと隠す
                    var cb = card.querySelector('.register-skip-toggle input[type="checkbox"]');
                    if (on && cb && !cb.checked) {
                        cb.checked = true;
                        cb.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    card.hidden = on;
                    return;
                }
                card.hidden = false;
                Array.prototype.forEach.call(card.querySelectorAll('.register-field'), function (field) {
                    field.classList.toggle('is-quick-hidden', on && !fieldIsRequired(field));
                });
            });
            if (termsCard) termsCard.hidden = false;
            if (actions) actions.hidden = false;
        }

        function enterWizard() {
            intro.hidden = true;
            quickBar.hidden = true;
            setQuickVisibility(false);
            step = 0;
            renderStep();
        }

        function enterQuick() {
            intro.hidden = true;
            hideAllSteps();
            setQuickVisibility(true);
            quickBar.hidden = false;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        Array.prototype.forEach.call(intro.querySelectorAll('[data-mode]'), function (btn) {
            btn.addEventListener('click', function () {
                if (btn.getAttribute('data-mode') === 'quick') enterQuick();
                else enterWizard();
            });
        });
        nav.querySelector('.register-wizard-switch').addEventListener('click', enterQuick);
        quickBar.querySelector('.register-quick-full').addEventListener('click', enterWizard);

        // 初期状態：モード選択だけを表示
        hideAllSteps();
    });
})();
