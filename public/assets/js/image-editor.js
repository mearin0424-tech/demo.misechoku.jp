/**
 * ミセチョク — 汎用画像エディタ
 *
 * Cropper.js を土台に、以下の機能を追加した本格的な画像編集モーダルを提供する:
 *   ・自由回転 / 90°回転 / 反転（左右・上下）
 *   ・アスペクト比の切り替え（自由・1:1・4:5・3:4・16:9・9:16）
 *   ・明るさ・コントラスト・彩度・色温度（暖色/寒色）調整
 *   ・プリセットフィルタ（標準 / 鮮やか / やわらか / モノクロ / セピア / ヴィンテージ / ウォーム / クール）
 *   ・全リセット
 *
 * 使い方:
 *
 *   window.MisechokuImageEditor.open(file, {
 *       aspectRatio:      4/5,             // 固定比を強制するなら数値、自由なら null
 *       aspectPresets:    ['4:5','1:1','16:9','free'],  // 切替可能な比。null で切替不可
 *       outputWidth:      1200,            // 出力ピクセル幅（アス比があるなら height は自動）
 *       outputHeight:     1600,
 *       outputFormat:     'image/jpeg',
 *       outputQuality:    0.9,
 *       title:            'プロフィール写真を編集',
 *       primaryLabel:     '完了',
 *       enableFilters:    true,
 *       enableRotate:     true,
 *       enableFlip:       true,
 *   }).then(function (blob) { ... }).catch(function (err) { ... });
 *
 * 未対応環境（Cropper 未ロード）の場合は Promise.reject する。
 */
(function () {
    'use strict';

    if (window.MisechokuImageEditor) return;

    var ASPECT_PRESETS = {
        'free':  { label: '自由',    ratio: NaN,  icon: 'fa-vector-square' },
        '1:1':   { label: '1:1',     ratio: 1,     icon: 'fa-square-full' },
        '4:5':   { label: '4:5',     ratio: 4/5,   icon: 'fa-image-portrait' },
        '3:4':   { label: '3:4',     ratio: 3/4,   icon: 'fa-image-portrait' },
        '16:9':  { label: '16:9',    ratio: 16/9,  icon: 'fa-tv' },
        '9:16':  { label: '9:16',    ratio: 9/16,  icon: 'fa-mobile-screen' },
        '3:2':   { label: '3:2',     ratio: 3/2,   icon: 'fa-camera' },
        '2:3':   { label: '2:3',     ratio: 2/3,   icon: 'fa-image-portrait' },
    };

    var FILTER_PRESETS = [
        { key: 'none',      label: '標準',      css: '', vibe: null },
        { key: 'vivid',     label: '鮮やか',    css: 'brightness(1.05) contrast(1.12) saturate(1.28)' },
        { key: 'soft',      label: 'やわらか',   css: 'brightness(1.08) contrast(0.95) saturate(0.92)' },
        { key: 'warm',      label: 'ウォーム',   css: 'brightness(1.05) contrast(1.05) saturate(1.10) sepia(0.15)' },
        { key: 'cool',      label: 'クール',    css: 'brightness(1.02) contrast(1.08) saturate(1.05) hue-rotate(-8deg)' },
        { key: 'mono',      label: 'モノクロ',   css: 'grayscale(1) contrast(1.10)' },
        { key: 'sepia',     label: 'セピア',    css: 'sepia(0.75) contrast(1.05) brightness(1.02)' },
        { key: 'vintage',   label: 'ヴィンテージ', css: 'sepia(0.30) contrast(0.92) brightness(1.05) saturate(0.85)' },
        { key: 'noir',      label: 'ノワール',   css: 'grayscale(1) contrast(1.28) brightness(0.95)' },
    ];

    function h(tag, attrs, children) {
        var el = document.createElement(tag);
        if (attrs) {
            Object.keys(attrs).forEach(function (k) {
                if (k === 'className')      el.className = attrs[k];
                else if (k === 'text')      el.textContent = attrs[k];
                else if (k === 'html')      el.innerHTML  = attrs[k];
                else if (k.indexOf('on') === 0) el.addEventListener(k.slice(2).toLowerCase(), attrs[k]);
                else                        el.setAttribute(k, attrs[k]);
            });
        }
        if (children) {
            (Array.isArray(children) ? children : [children]).forEach(function (c) {
                if (c == null) return;
                if (typeof c === 'string') el.appendChild(document.createTextNode(c));
                else el.appendChild(c);
            });
        }
        return el;
    }

    function isProbablyHeic(file) {
        if (!file) return false;
        var name = (file.name || '').toLowerCase();
        if (/\.(heic|heif)$/.test(name)) return true;
        var type = (file.type || '').toLowerCase();
        return type === 'image/heic' || type === 'image/heif';
    }

    function fileToImage(file) {
        return new Promise(function (resolve, reject) {
            if (isProbablyHeic(file)) {
                reject(new Error('iPhone の HEIC 形式は画像編集に対応していません。端末側で JPEG に変換してから、もう一度お試しください。'));
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = new Image();
                img.onload = function () {
                    // 極端に巨大な画像は Canvas / Cropper の負荷が大きいので事前弾き
                    if (img.naturalWidth > 8000 || img.naturalHeight > 8000) {
                        reject(new Error('画像サイズが大きすぎます。8000×8000 ピクセル以下のファイルをご利用ください。'));
                        return;
                    }
                    resolve({ src: e.target.result, width: img.naturalWidth, height: img.naturalHeight });
                };
                img.onerror = function () { reject(new Error('画像の読み込みに失敗しました。ファイル形式をご確認ください。')); };
                img.src = e.target.result;
            };
            reader.onerror = function () { reject(new Error('ファイルの読み込みに失敗しました')); };
            reader.readAsDataURL(file);
        });
    }

    /**
     * options を正規化しつつ、モーダルを組み立てる。
     */
    function ImageEditor(file, opts) {
        this.file = file;
        this.opts = Object.assign({
            aspectRatio: null,
            aspectPresets: null,     // 例: ['4:5','1:1','16:9','free']
            outputWidth: 1200,
            outputHeight: null,      // aspect と width から自動計算
            outputFormat: 'image/jpeg',
            outputQuality: 0.9,
            title: '画像を編集',
            primaryLabel: '完了',
            secondaryLabel: 'キャンセル',
            enableFilters: true,
            enableRotate: true,
            enableFlip: true,
        }, opts || {});

        // aspectRatio が指定されていれば、アスペクト切替は無効化
        if (this.opts.aspectRatio && this.opts.aspectRatio > 0) {
            this.opts.aspectPresets = null;
        }

        this.state = {
            aspectKey: this._resolveInitialAspectKey(),
            rotation: 0,           // 度数（-180..180）
            flipH: false,
            flipV: false,
            zoom: 1,               // Cropper 用の倍率記録（表示用）
            filterPreset: 'none',
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
        };

        this.cropper = null;
        this.rootEl = null;
        this.imgEl = null;
        this.imageInfo = null;
        this.resolve = null;
        this.reject = null;
    }

    ImageEditor.prototype._resolveInitialAspectKey = function () {
        if (this.opts.aspectRatio && this.opts.aspectRatio > 0) {
            // マッチするプリセットを探す
            var r = this.opts.aspectRatio;
            var match = Object.keys(ASPECT_PRESETS).find(function (k) {
                return Math.abs(ASPECT_PRESETS[k].ratio - r) < 0.02;
            });
            return match || 'custom';
        }
        if (Array.isArray(this.opts.aspectPresets) && this.opts.aspectPresets.length) {
            return this.opts.aspectPresets[0];
        }
        return 'free';
    };

    ImageEditor.prototype.open = function () {
        var self = this;
        return new Promise(function (resolve, reject) {
            self.resolve = resolve;
            self.reject = reject;

            if (!window.Cropper) {
                reject(new Error('Cropper.js が読み込まれていません'));
                return;
            }

            fileToImage(self.file).then(function (info) {
                self.imageInfo = info;
                self._mount();
                self._initCropper();
                self._applyLiveFilter();
                // Body スクロールを止める + ヘッダーを隠す既存ルールに合わせて
                document.body.classList.add('is-image-editing');
            }).catch(function (err) {
                reject(err);
            });
        });
    };

    ImageEditor.prototype._mount = function () {
        var self = this;
        // ルートモーダル
        var root = h('div', { className: 'imge', role: 'dialog', 'aria-modal': 'true', 'aria-label': self.opts.title });
        // ヘッダー
        var header = h('div', { className: 'imge__header' }, [
            h('button', {
                type: 'button',
                className: 'imge__btn imge__btn--ghost',
                'aria-label': '編集をキャンセル',
                onClick: function () { self._cancel(); },
            }, [ h('i', { className: 'fas fa-times' }) ]),
            h('div', { className: 'imge__title', text: self.opts.title }),
            h('button', {
                type: 'button',
                className: 'imge__btn imge__btn--primary',
                'aria-label': self.opts.primaryLabel,
                onClick: function () { self._confirm(); },
            }, [ h('i', { className: 'fas fa-check' }), h('span', { text: self.opts.primaryLabel }) ]),
        ]);

        // ステージ
        var stage = h('div', { className: 'imge__stage' }, [
            h('img', { className: 'imge__preview', id: 'imge-preview', alt: '' }),
        ]);
        this.imgEl = stage.querySelector('#imge-preview');
        this.imgEl.src = this.imageInfo.src;

        // ツールバー（下部タブ + パネル）
        var toolbar = h('div', { className: 'imge__toolbar' });
        // タブナビ
        var tabs = [
            { key: 'crop',   icon: 'fa-crop',           label: 'トリミング' },
        ];
        if (this.opts.enableRotate || this.opts.enableFlip) tabs.push({ key: 'transform', icon: 'fa-arrows-rotate', label: '回転・反転' });
        if (this.opts.enableFilters) {
            tabs.push({ key: 'adjust', icon: 'fa-sliders', label: '調整' });
            tabs.push({ key: 'filter', icon: 'fa-wand-magic-sparkles', label: 'フィルタ' });
            tabs.push({ key: 'text',   icon: 'fa-font',                   label: '文字' });
        }

        var tabBar = h('div', { className: 'imge__tabbar', role: 'tablist' });
        var panelWrap = h('div', { className: 'imge__panels' });

        tabs.forEach(function (t, i) {
            var btn = h('button', {
                type: 'button',
                className: 'imge__tab' + (i === 0 ? ' is-active' : ''),
                role: 'tab',
                'aria-selected': i === 0 ? 'true' : 'false',
                'data-imge-tab': t.key,
                onClick: function () {
                    tabBar.querySelectorAll('.imge__tab').forEach(function (b) {
                        var active = b === btn;
                        b.classList.toggle('is-active', active);
                        b.setAttribute('aria-selected', active ? 'true' : 'false');
                    });
                    panelWrap.querySelectorAll('.imge__panel').forEach(function (p) {
                        p.classList.toggle('is-active', p.getAttribute('data-imge-panel') === t.key);
                    });
                },
            }, [ h('i', { className: 'fas ' + t.icon }), h('span', { text: t.label }) ]);
            tabBar.appendChild(btn);

            var panel = h('div', { className: 'imge__panel' + (i === 0 ? ' is-active' : ''), 'data-imge-panel': t.key });
            if (t.key === 'crop')       self._buildCropPanel(panel);
            if (t.key === 'transform')  self._buildTransformPanel(panel);
            if (t.key === 'adjust')     self._buildAdjustPanel(panel);
            if (t.key === 'filter')     self._buildFilterPanel(panel);
            if (t.key === 'text')       self._buildTextPanel(panel);
            panelWrap.appendChild(panel);
        });

        // 全リセット
        var reset = h('button', {
            type: 'button',
            className: 'imge__reset',
            'aria-label': '全ての編集をリセット',
            onClick: function () { self._resetAll(); },
        }, [ h('i', { className: 'fas fa-arrow-rotate-left' }), h('span', { text: 'リセット' }) ]);

        toolbar.appendChild(reset);
        toolbar.appendChild(panelWrap);
        toolbar.appendChild(tabBar);

        root.appendChild(header);
        root.appendChild(stage);
        root.appendChild(toolbar);

        document.body.appendChild(root);
        this.rootEl = root;

        // ESC で閉じる
        this._escHandler = function (e) { if (e.key === 'Escape') self._cancel(); };
        document.addEventListener('keydown', this._escHandler);
    };

    ImageEditor.prototype._buildCropPanel = function (panel) {
        var self = this;
        // アスペクト比選択
        var chips = h('div', { className: 'imge__chips' });
        var presets = Array.isArray(this.opts.aspectPresets) ? this.opts.aspectPresets : null;
        if (presets) {
            presets.forEach(function (k) {
                var meta = ASPECT_PRESETS[k];
                if (!meta) return;
                var chip = h('button', {
                    type: 'button',
                    className: 'imge__chip' + (k === self.state.aspectKey ? ' is-active' : ''),
                    'data-imge-aspect': k,
                    onClick: function () {
                        self.state.aspectKey = k;
                        chips.querySelectorAll('.imge__chip').forEach(function (c) {
                            c.classList.toggle('is-active', c.getAttribute('data-imge-aspect') === k);
                        });
                        if (self.cropper) self.cropper.setAspectRatio(meta.ratio);
                    },
                }, [ h('i', { className: 'fas ' + meta.icon }), h('span', { text: meta.label }) ]);
                chips.appendChild(chip);
            });
            panel.appendChild(h('div', { className: 'imge__label', text: 'アスペクト比' }));
            panel.appendChild(chips);
        }

        // ズームスライダー
        var zoomLabel = h('div', { className: 'imge__label imge__label--slider' }, [
            h('span', { text: 'ズーム' }),
            h('span', { className: 'imge__label-val', 'data-imge-zoom-val': '', text: '1.0×' }),
        ]);
        var zoomInput = h('input', {
            type: 'range', min: 0.5, max: 4, step: 0.02, value: 1,
            className: 'imge__slider',
            'aria-label': 'ズーム',
        });
        zoomInput.addEventListener('input', function () {
            var v = parseFloat(zoomInput.value) || 1;
            if (self.cropper) {
                // 相対倍率：初期スケール(_zoomBase) × スライダー値
                var base = self._zoomBase || 1;
                try { self.cropper.zoomTo(base * v); } catch (e) {}
            }
            self.state.zoom = v;
            var el = panel.querySelector('[data-imge-zoom-val]');
            if (el) el.textContent = v.toFixed(2) + '×';
        });
        panel.appendChild(zoomLabel);
        panel.appendChild(zoomInput);
        this._zoomInput = zoomInput;
    };

    ImageEditor.prototype._buildTransformPanel = function (panel) {
        var self = this;

        if (this.opts.enableRotate) {
            panel.appendChild(h('div', { className: 'imge__label', text: '回転' }));
            var rotRow = h('div', { className: 'imge__chips' }, [
                self._iconButton('fa-rotate-left', '90°左', function () { self._rotateBy(-90); }),
                self._iconButton('fa-rotate-right', '90°右', function () { self._rotateBy(90); }),
                self._iconButton('fa-arrows-rotate', '180°', function () { self._rotateBy(180); }),
            ]);
            panel.appendChild(rotRow);

            // 自由回転スライダー
            var freeLabel = h('div', { className: 'imge__label imge__label--slider' }, [
                h('span', { text: '角度' }),
                h('span', { className: 'imge__label-val', 'data-imge-rot-val': '', text: '0°' }),
            ]);
            var rotInput = h('input', {
                type: 'range', min: -45, max: 45, step: 1, value: 0,
                className: 'imge__slider',
                'aria-label': '回転角度',
            });
            rotInput.addEventListener('input', function () {
                var v = parseFloat(rotInput.value);
                // 90° 単位の基本回転からの相対角度で微調整
                var base = Math.round(self.state.rotation / 90) * 90;
                var next = base + v;
                if (self.cropper) self.cropper.rotateTo(next);
                panel.querySelector('[data-imge-rot-val]').textContent = v + '°';
            });
            panel.appendChild(freeLabel);
            panel.appendChild(rotInput);
            this._rotInput = rotInput;
        }

        if (this.opts.enableFlip) {
            panel.appendChild(h('div', { className: 'imge__label', text: '反転' }));
            var flipRow = h('div', { className: 'imge__chips' }, [
                self._iconButton('fa-arrows-left-right', '左右反転', function () {
                    self.state.flipH = !self.state.flipH;
                    if (self.cropper) self.cropper.scaleX(self.state.flipH ? -1 : 1);
                }),
                self._iconButton('fa-arrows-up-down', '上下反転', function () {
                    self.state.flipV = !self.state.flipV;
                    if (self.cropper) self.cropper.scaleY(self.state.flipV ? -1 : 1);
                }),
            ]);
            panel.appendChild(flipRow);
        }
    };

    ImageEditor.prototype._buildAdjustPanel = function (panel) {
        var self = this;
        function slider(labelText, key, min, max, step, unit) {
            var wrap = h('div', { className: 'imge__adjust-row' });
            var label = h('div', { className: 'imge__label imge__label--slider' }, [
                h('span', { text: labelText }),
                h('span', { className: 'imge__label-val', 'data-imge-adjust': key, text: self.state.adjust[key] + (unit || '') }),
            ]);
            var input = h('input', {
                type: 'range',
                min: String(min), max: String(max), step: String(step),
                value: String(self.state.adjust[key]),
                className: 'imge__slider',
                'aria-label': labelText,
            });
            input.addEventListener('input', function () {
                self.state.adjust[key] = parseFloat(input.value);
                var el = panel.querySelector('[data-imge-adjust="' + key + '"]');
                if (el) el.textContent = self.state.adjust[key] + (unit || '');
                self._applyLiveFilter();
            });
            wrap.appendChild(label);
            wrap.appendChild(input);
            return wrap;
        }

        panel.appendChild(slider('明るさ',     'brightness', 50, 150, 1, '%'));
        panel.appendChild(slider('コントラスト', 'contrast',   50, 150, 1, '%'));
        panel.appendChild(slider('彩度',       'saturate',   0, 200, 1, '%'));
        panel.appendChild(slider('色温度',     'warmth',    -50, 50, 1, ''));
    };

    ImageEditor.prototype._buildFilterPanel = function (panel) {
        var self = this;
        panel.appendChild(h('div', { className: 'imge__label', text: 'プリセット' }));
        var wrap = h('div', { className: 'imge__filters' });
        FILTER_PRESETS.forEach(function (p) {
            var btn = h('button', {
                type: 'button',
                className: 'imge__filter' + (p.key === self.state.filterPreset ? ' is-active' : ''),
                'data-imge-filter': p.key,
                'aria-label': 'フィルタ: ' + p.label,
                onClick: function () {
                    self.state.filterPreset = p.key;
                    wrap.querySelectorAll('.imge__filter').forEach(function (b) {
                        b.classList.toggle('is-active', b.getAttribute('data-imge-filter') === p.key);
                    });
                    self._applyLiveFilter();
                },
            }, [
                h('span', {
                    className: 'imge__filter-thumb',
                    style: 'background-image: url(' + (self.imageInfo.src) + '); filter: ' + (p.css || 'none') + ';',
                }),
                h('span', { className: 'imge__filter-label', text: p.label }),
            ]);
            wrap.appendChild(btn);
        });
        panel.appendChild(wrap);
    };

    ImageEditor.prototype._iconButton = function (icon, label, cb) {
        return h('button', {
            type: 'button',
            className: 'imge__chip',
            'aria-label': label,
            title: label,
            onClick: cb,
        }, [ h('i', { className: 'fas ' + icon }), h('span', { text: label }) ]);
    };

    ImageEditor.prototype._buildFilterCss = function () {
        var s = this.state;
        var parts = [];
        if (s.adjust.brightness !== 100) parts.push('brightness(' + (s.adjust.brightness / 100) + ')');
        if (s.adjust.contrast !== 100)   parts.push('contrast('   + (s.adjust.contrast / 100) + ')');
        if (s.adjust.saturate !== 100)   parts.push('saturate('   + (s.adjust.saturate / 100) + ')');
        if (s.adjust.warmth !== 0) {
            // warmth: 正で暖色、負で寒色。sepia + hue-rotate の合成で近似
            if (s.adjust.warmth > 0) {
                parts.push('sepia(' + (s.adjust.warmth / 100).toFixed(2) + ')');
            } else {
                parts.push('hue-rotate(' + (s.adjust.warmth * 0.5) + 'deg)');
            }
        }
        var preset = FILTER_PRESETS.find(function (p) { return p.key === (this.state.filterPreset); }.bind(this));
        if (preset && preset.css) parts.push(preset.css);
        return parts.join(' ');
    };

    ImageEditor.prototype._applyLiveFilter = function () {
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
    };

    ImageEditor.prototype._rotateBy = function (deg) {
        var newRot = this.state.rotation + deg;
        this.state.rotation = newRot;
        if (this.cropper) this.cropper.rotate(deg);
    };

    ImageEditor.prototype._initCropper = function () {
        var self = this;
        var ratio = null;
        if (this.opts.aspectRatio && this.opts.aspectRatio > 0) {
            ratio = this.opts.aspectRatio;
        } else if (Array.isArray(this.opts.aspectPresets) && this.opts.aspectPresets.length) {
            var meta = ASPECT_PRESETS[this.state.aspectKey];
            ratio = meta ? meta.ratio : NaN;
        } else {
            ratio = NaN;
        }
        // 出力サイズが指定されていれば、それに合った縦横比を最優先で使う
        // （aspectRatio が渡っていない/一致しない時の保険）
        var expectedRatio = ratio;
        if ((!expectedRatio || isNaN(expectedRatio)) && this.opts.outputWidth && this.opts.outputHeight) {
            expectedRatio = this.opts.outputWidth / this.opts.outputHeight;
        }
        this.cropper = new Cropper(this.imgEl, {
            aspectRatio: expectedRatio || ratio,
            viewMode: 2,          // canvas が container を覆う。縦長 crop box が確実に維持される
            dragMode: 'move',
            autoCropArea: 1,
            zoomable: true,
            movable: true,
            scalable: true,
            rotatable: true,
            responsive: true,
            checkOrientation: true, // EXIF 回転を尊重（横長化バグの根本原因のひとつ）
            background: false,
            toggleDragModeOnDblclick: false,
            ready: function () {
                // アスペクト比の再確定（一部ブラウザで初期の autoCropArea が横長になるケースの保険）
                if (expectedRatio && expectedRatio > 0) {
                    try { self.cropper.setAspectRatio(expectedRatio); } catch (e) {}
                }
                // ズームスライダーを Cropper の実スケールに同期
                self._syncZoomFromCropper();
                self._applyLiveFilter();
            },
            crop: function () { self._applyLiveFilter(); },
            cropmove: function () { self._applyLiveFilter(); },
            zoom: function (ev) {
                self.state.zoom = ev.detail.ratio || self.state.zoom;
                self._syncZoomFromCropper();
            },
        });
    };

    // Cropper の実スケールをスライダーへ反映（相対ズームで表示）
    ImageEditor.prototype._syncZoomFromCropper = function () {
        if (!this.cropper || !this._zoomInput) return;
        try {
            var cd = this.cropper.getCanvasData();
            var id = this.cropper.getImageData();
            if (!cd || !id || !id.naturalWidth) return;
            // 初期スケール（container fit）を 1.0 として、現在の倍率を計算
            if (!this._zoomBase) this._zoomBase = id.width / id.naturalWidth || 1;
            var current = (cd.width / id.naturalWidth) / this._zoomBase;
            this._zoomInput.value = String(current.toFixed(2));
            var valEl = this.rootEl && this.rootEl.querySelector('[data-imge-zoom-val]');
            if (valEl) valEl.textContent = current.toFixed(2) + '×';
        } catch (e) {}
    };

    ImageEditor.prototype._resetAll = function () {
        // 元画像に戻す
        this.state.rotation = 0;
        this.state.flipH = false;
        this.state.flipV = false;
        this.state.zoom = 1;
        this.state.filterPreset = 'none';
        this.state.adjust = { brightness: 100, contrast: 100, saturate: 100, warmth: 0 };
        if (this.cropper) {
            this.cropper.reset();
            this.cropper.scaleX(1);
            this.cropper.scaleY(1);
            this.cropper.rotateTo(0);
        }
        // UI 反映
        if (this.rootEl) {
            var chips = this.rootEl.querySelectorAll('.imge__filter');
            chips.forEach(function (c) { c.classList.toggle('is-active', c.getAttribute('data-imge-filter') === 'none'); });
            this.rootEl.querySelectorAll('[data-imge-adjust]').forEach(function (el) {
                var k = el.getAttribute('data-imge-adjust');
                var v = ({ brightness: '100%', contrast: '100%', saturate: '100%', warmth: '0' })[k];
                el.textContent = v;
            });
            this.rootEl.querySelectorAll('.imge__slider').forEach(function (s) {
                var lab = s.getAttribute('aria-label');
                if (lab === '明るさ' || lab === 'コントラスト' || lab === '彩度') s.value = '100';
                else if (lab === '色温度' || lab === '回転角度') s.value = '0';
                else if (lab === 'ズーム') s.value = '1';
            });
            var rotVal = this.rootEl.querySelector('[data-imge-rot-val]');
            if (rotVal) rotVal.textContent = '0°';
            var zoomVal = this.rootEl.querySelector('[data-imge-zoom-val]');
            if (zoomVal) zoomVal.textContent = '1.00×';
        }
        this._applyLiveFilter();
    };

    ImageEditor.prototype._confirm = function () {
        var self = this;
        if (!this.cropper) return;
        try {
            var outW = this.opts.outputWidth || 1200;
            var outH = this.opts.outputHeight
                || (this.opts.aspectRatio && this.opts.aspectRatio > 0
                    ? Math.round(outW / this.opts.aspectRatio)
                    : null);

            var canvasOpts = {
                width:  outW,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            };
            if (outH) canvasOpts.height = outH;

            var canvas = this.cropper.getCroppedCanvas(canvasOpts);
            if (!canvas) {
                this._toast('画像のトリミングに失敗しました', 'error');
                return;
            }
            // フィルタ効果を canvas に焼き付ける
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
                tctx.font = '800 ' + fontPx + 'px "Noto Sans JP", "Hiragino Sans", sans-serif';
                var cx = canvas.width / 2;
                var cy = t.pos === 'top'
                    ? Math.round(canvas.height * 0.12)
                    : (t.pos === 'middle' ? Math.round(canvas.height * 0.5) : Math.round(canvas.height * 0.88));
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
            }
            canvas.toBlob(function (blob) {
                if (!blob) {
                    self._toast('画像の書き出しに失敗しました', 'error');
                    return;
                }
                self._teardown();
                self.resolve(blob);
            }, this.opts.outputFormat, this.opts.outputQuality);
        } catch (e) {
            this._toast(e && e.message ? e.message : '画像の書き出しに失敗しました', 'error');
        }
    };

    ImageEditor.prototype._cancel = function () {
        this._teardown();
        this.reject(new Error('cancelled'));
    };

    ImageEditor.prototype._teardown = function () {
        if (this.cropper) { try { this.cropper.destroy(); } catch (e) {} this.cropper = null; }
        if (this.rootEl && this.rootEl.parentNode) this.rootEl.parentNode.removeChild(this.rootEl);
        if (this._escHandler) document.removeEventListener('keydown', this._escHandler);
        document.body.classList.remove('is-image-editing');
    };

    ImageEditor.prototype._toast = function (msg, variant) {
        if (window.appToast) window.appToast(msg, variant);
        else window.alert(msg);
    };

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
        if (t.color === '#f6d36a') {
            innerSpan.style.backgroundImage = 'linear-gradient(180deg,#fff3c4,#f6d36a 50%,#d4af37)';
            innerSpan.style.webkitBackgroundClip = 'text';
            innerSpan.style.backgroundClip = 'text';
            innerSpan.style.color = 'transparent';
            innerSpan.style.webkitTextFillColor = 'transparent';
        } else {
            innerSpan.style.backgroundImage = 'none';
            innerSpan.style.color = t.color;
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

    window.MisechokuImageEditor = {
        /**
         * @param  {File|Blob} file
         * @param  {object}    options
         * @return {Promise<Blob>}
         */
        open: function (file, options) {
            var ed = new ImageEditor(file, options);
            return ed.open();
        },
    };
})();
