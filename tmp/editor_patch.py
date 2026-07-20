import io
p = 'public/assets/js/image-editor.js'
with io.open(p, encoding='utf-8') as f: s = f.read()

# 1. filter live apply: 切り抜き枠内にも filter を適用
old = """    ImageEditor.prototype._applyLiveFilter = function () {
        var css = this._buildFilterCss();
        // Cropper のキャンバスコンテナ側に filter をあてる（プレビュー全体に反映）
        if (this.rootEl) {
            var cc = this.rootEl.querySelector('.cropper-canvas img');
            if (cc) cc.style.filter = css;
            // ハンドラ側は透過にして視認性維持
        }
    };"""
new = """    ImageEditor.prototype._applyLiveFilter = function () {
        var css = this._buildFilterCss() || 'none';
        if (!this.rootEl) return;
        // バグ修正：Cropper は「外周プレビュー(.cropper-canvas img)」と
        // 「切り抜き枠内の複製(.cropper-view-box img)」を別のノードで持つ。
        // 両方に filter を当てないと「暗くしても枠内は明るいまま」の食い違いになる。
        var nodes = this.rootEl.querySelectorAll('.cropper-canvas img, .cropper-view-box img');
        nodes.forEach(function (img) {
            img.style.filter = css;
            img.style.webkitFilter = css;
        });
    };"""
assert old in s, 'apply block'
s = s.replace(old, new)

# 2. crop/cropmove でも再適用
s = s.replace("            ready: function () { self._applyLiveFilter(); },",
              "            ready: function () { self._applyLiveFilter(); },\n            crop: function () { self._applyLiveFilter(); },\n            cropmove: function () { self._applyLiveFilter(); },")

# 3. state.text 追加
old_state = """            filterPreset: 'none',
            adjust: {
                brightness: 100,   // %
                contrast:   100,
                saturate:   100,
                warmth:     0,     // -50..50
            },
        };"""
new_state = """            filterPreset: 'none',
            adjust: {
                brightness: 100,   // %
                contrast:   100,
                saturate:   100,
                warmth:     0,     // -50..50
            },
            text: {
                content: '',
                color: '#ffffff',
                size: 42,
                pos: 'bottom',
                bg: false,
            },
        };"""
assert old_state in s, 'state block'
s = s.replace(old_state, new_state)

# 4. タブに「文字」追加
s = s.replace("            tabs.push({ key: 'filter', icon: 'fa-wand-magic-sparkles', label: 'フィルタ' });\n        }",
              "            tabs.push({ key: 'filter', icon: 'fa-wand-magic-sparkles', label: 'フィルタ' });\n            tabs.push({ key: 'text',   icon: 'fa-font',                   label: '文字' });\n        }")
s = s.replace("            if (t.key === 'filter')     self._buildFilterPanel(panel);",
              "            if (t.key === 'filter')     self._buildFilterPanel(panel);\n            if (t.key === 'text')       self._buildTextPanel(panel);")

# 5. 文字パネル + preview 同期 メソッド
inject = r"""
    ImageEditor.prototype._buildTextPanel = function (panel) {
        var self = this;
        var wrap = h('div', { className: 'imge__text-panel' });

        var input = h('input', {
            type: 'text', className: 'imge__text-input',
            placeholder: '写真に載せる文字（20字以内）',
            maxlength: '20',
            value: self.state.text.content,
        });
        input.addEventListener('input', function () {
            self.state.text.content = input.value;
            self._syncTextPreview();
        });

        var colorRow = h('div', { className: 'imge__text-colors' });
        [
            { key: '#ffffff', label: '白', style: 'background:#ffffff' },
            { key: '#111111', label: '黒', style: 'background:#111111' },
            { key: '#f6d36a', label: '金', style: 'background:linear-gradient(135deg,#fff3c4,#d4af37)' },
            { key: '#d670a2', label: '桃', style: 'background:#d670a2' },
        ].forEach(function (c) {
            var b = h('button', {
                type: 'button',
                className: 'imge__text-color' + (self.state.text.color === c.key ? ' is-active' : ''),
                'data-color': c.key,
                'aria-label': c.label,
                style: c.style,
                onClick: function () {
                    self.state.text.color = c.key;
                    wrap.querySelectorAll('.imge__text-color').forEach(function (x) {
                        x.classList.toggle('is-active', x.getAttribute('data-color') === c.key);
                    });
                    self._syncTextPreview();
                },
            });
            colorRow.appendChild(b);
        });

        var sizeRow = h('label', { className: 'imge__text-size' }, [
            h('span', { text: 'サイズ' }),
            (function () {
                var r = h('input', { type: 'range', min: '18', max: '96', step: '2', value: String(self.state.text.size), 'aria-label': '文字サイズ' });
                r.addEventListener('input', function () {
                    self.state.text.size = parseInt(r.value, 10) || 42;
                    self._syncTextPreview();
                });
                return r;
            })(),
        ]);

        var posRow = h('div', { className: 'imge__text-pos' });
        [
            { key: 'top',    label: '上' },
            { key: 'middle', label: '中央' },
            { key: 'bottom', label: '下' },
        ].forEach(function (p) {
            var b = h('button', {
                type: 'button',
                className: 'imge__text-pos-btn' + (self.state.text.pos === p.key ? ' is-active' : ''),
                'data-pos': p.key,
                onClick: function () {
                    self.state.text.pos = p.key;
                    posRow.querySelectorAll('.imge__text-pos-btn').forEach(function (x) {
                        x.classList.toggle('is-active', x.getAttribute('data-pos') === p.key);
                    });
                    self._syncTextPreview();
                },
                text: p.label,
            });
            posRow.appendChild(b);
        });

        var bgToggle = h('label', { className: 'imge__text-bg-toggle' }, [
            (function () {
                var c = h('input', { type: 'checkbox' });
                c.checked = !!self.state.text.bg;
                c.addEventListener('change', function () { self.state.text.bg = c.checked; self._syncTextPreview(); });
                return c;
            })(),
            h('span', { text: '半透明の帯を敷く（読みやすさUP）' }),
        ]);

        wrap.appendChild(input);
        wrap.appendChild(colorRow);
        wrap.appendChild(sizeRow);
        wrap.appendChild(posRow);
        wrap.appendChild(bgToggle);
        panel.appendChild(wrap);
    };

    ImageEditor.prototype._syncTextPreview = function () {
        if (!this.rootEl) return;
        var view = this.rootEl.querySelector('.cropper-view-box');
        if (!view) return;
        var overlay = view.querySelector('.imge__text-overlay');
        if (!overlay) {
            overlay = h('div', { className: 'imge__text-overlay' });
            var span = h('span', { className: 'imge__text-overlay__inner' });
            overlay.appendChild(span);
            view.appendChild(overlay);
        }
        var t = this.state.text;
        var innerSpan = overlay.querySelector('.imge__text-overlay__inner');
        innerSpan.textContent = t.content || '';
        innerSpan.style.color = t.color;
        if (t.color === '#f6d36a') {
            innerSpan.style.backgroundImage = 'linear-gradient(180deg,#fff3c4,#f6d36a 50%,#d4af37)';
            innerSpan.style.webkitBackgroundClip = 'text';
            innerSpan.style.backgroundClip = 'text';
            innerSpan.style.color = 'transparent';
            innerSpan.style.webkitTextFillColor = 'transparent';
        } else {
            innerSpan.style.backgroundImage = 'none';
            innerSpan.style.webkitTextFillColor = t.color;
        }
        var basis = view.clientHeight || 400;
        var fontPx = Math.round(basis * (t.size / 500));
        innerSpan.style.fontSize = fontPx + 'px';
        innerSpan.style.padding = t.bg ? (Math.round(fontPx * 0.28) + 'px ' + Math.round(fontPx * 0.6) + 'px') : '0';
        innerSpan.style.background = t.bg ? 'rgba(0,0,0,0.45)' : 'transparent';
        innerSpan.style.borderRadius = t.bg ? '999px' : '0';
        innerSpan.style.textShadow = t.color === '#111111' ? '0 1px 3px rgba(255,255,255,0.35)' : '0 1px 3px rgba(0,0,0,0.55), 0 0 12px rgba(0,0,0,0.25)';
        overlay.style.display = t.content ? 'flex' : 'none';
        overlay.dataset.pos = t.pos;
    };

"""
anchor = "    window.MisechokuImageEditor = {"
assert anchor in s, 'anchor'
s = s.replace(anchor, inject + "\n" + anchor)

# 6. confirm 内で text 焼き付け
old_bake = """            // フィルタ効果を canvas に焼き付ける（Canvas.filter API が使える環境で有効化）
            var css = this._buildFilterCss();
            if (css) {
                var bake = document.createElement('canvas');
                bake.width = canvas.width;
                bake.height = canvas.height;
                var ctx = bake.getContext('2d');
                if (typeof ctx.filter === 'string') {
                    ctx.filter = css;
                    ctx.drawImage(canvas, 0, 0);
                    canvas = bake;
                }
                // Canvas.filter 未対応環境は crop のみで妥協（CSS filter は preview 上だけ）
            }"""
new_bake = """            // フィルタ効果を canvas に焼き付ける
            var css = this._buildFilterCss();
            if (css) {
                var bake = document.createElement('canvas');
                bake.width = canvas.width;
                bake.height = canvas.height;
                var ctx = bake.getContext('2d');
                if (typeof ctx.filter === 'string') {
                    ctx.filter = css;
                    ctx.drawImage(canvas, 0, 0);
                    ctx.filter = 'none';
                    canvas = bake;
                }
            }
            // 装飾文字を焼き付け
            if (this.state.text && this.state.text.content) {
                var tctx = canvas.getContext('2d');
                var t = this.state.text;
                var fontPx = Math.round(canvas.height * (t.size / 500) * 2.2);
                tctx.textAlign = 'center';
                tctx.textBaseline = 'middle';
                tctx.font = '800 ' + fontPx + 'px \"Noto Sans JP\", \"Hiragino Sans\", sans-serif';
                var cx = canvas.width / 2;
                var cy = t.pos === 'top'    ? Math.round(canvas.height * 0.12)
                        : t.pos === 'middle' ? Math.round(canvas.height * 0.5)
                        :                      Math.round(canvas.height * 0.88);
                var metrics = tctx.measureText(t.content);
                if (t.bg) {
                    var padX = Math.round(fontPx * 0.6);
                    var padY = Math.round(fontPx * 0.35);
                    var bw = Math.round(metrics.width) + padX * 2;
                    var bh = Math.round(fontPx * 1.1) + padY * 2;
                    tctx.fillStyle = 'rgba(0,0,0,0.50)';
                    var br = bh / 2;
                    var bx = cx - bw / 2, by = cy - bh / 2;
                    tctx.beginPath();
                    tctx.moveTo(bx + br, by);
                    tctx.lineTo(bx + bw - br, by);
                    tctx.quadraticCurveTo(bx + bw, by, bx + bw, by + br);
                    tctx.lineTo(bx + bw, by + bh - br);
                    tctx.quadraticCurveTo(bx + bw, by + bh, bx + bw - br, by + bh);
                    tctx.lineTo(bx + br, by + bh);
                    tctx.quadraticCurveTo(bx, by + bh, bx, by + bh - br);
                    tctx.lineTo(bx, by + br);
                    tctx.quadraticCurveTo(bx, by, bx + br, by);
                    tctx.closePath();
                    tctx.fill();
                }
                tctx.shadowColor = 'rgba(0,0,0,0.55)';
                tctx.shadowBlur = Math.max(4, fontPx * 0.08);
                tctx.shadowOffsetY = Math.max(1, fontPx * 0.03);
                if (t.color === '#f6d36a') {
                    var g = tctx.createLinearGradient(0, cy - fontPx / 2, 0, cy + fontPx / 2);
                    g.addColorStop(0, '#fff3c4');
                    g.addColorStop(0.5, '#f6d36a');
                    g.addColorStop(1, '#d4af37');
                    tctx.fillStyle = g;
                } else {
                    tctx.fillStyle = t.color;
                }
                tctx.fillText(t.content, cx, cy);
                tctx.shadowColor = 'transparent';
            }"""
assert old_bake in s, 'bake block'
s = s.replace(old_bake, new_bake)

with io.open(p, 'w', encoding='utf-8') as f: f.write(s)
print('editor patched')
