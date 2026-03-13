(function () {
    function parseJson(text) {
        try {
            return JSON.parse(text);
        } catch (error) {
            return null;
        }
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatMessage(text) {
        return escapeHtml(text || '')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
    }

    function unique(values) {
        return Array.from(new Set(values.filter(Boolean)));
    }

    function normalizeText(value) {
        return String(value || '').toLowerCase();
    }

    function includesAny(text, patterns) {
        return patterns.some(function (pattern) {
            return pattern.test(text);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var root = document.querySelector('[data-ai-recommend-root]');
        var dataEl = document.getElementById('ai-recommend-data');
        if (!root || !dataEl) {
            return;
        }

        var payload = parseJson(dataEl.textContent || '{}') || {};
        var chatBox = root.querySelector('[data-ai-chat]');
        var input = root.querySelector('[data-ai-input]');
        var sendButton = root.querySelector('[data-ai-send]');
        var resetButton = root.querySelector('[data-ai-reset]');
        var avatar = root.getAttribute('data-avatar') || '';
        var role = root.getAttribute('data-role') || payload.role || 'cast';
        var redirectUrl = 'https://mearin0424-tech.github.io/personality-test/personality-test.html';
        var type16Codes = [
            'LATF', 'LATP', 'LASF', 'LASP', 'LETF', 'LETP', 'LESF', 'LESP',
            'IATF', 'IATP', 'IASF', 'IASP', 'IETF', 'IETP', 'IESF', 'IESP'
        ];
        var areaGroups = {
            '六本木・西麻布': ['六本木', '西麻布', '麻布'],
            '新宿・歌舞伎町': ['新宿', '歌舞伎町'],
            '銀座・新橋': ['銀座', '新橋'],
            '恵比寿・中目黒': ['恵比寿', '中目黒', '代官山'],
            'こだわらない': []
        };
        var state = {
            step: 1,
            answers: {},
            matches: [],
            matchIndex: 0
        };

        function inferType(item) {
            var text = [item.name, item.area, item.text].join(' ');

            if (/スナック/i.test(text)) return 'スナック';
            if (/ラウンジ/i.test(text)) return 'ラウンジ';
            if (/ガールズバー|girls?\s*bar|バー|bar/i.test(text)) return 'ガールズバー';
            if (/会員制/i.test(text)) return '会員制';
            if (/キャバ|クラブ|club/i.test(text)) return 'クラブ';

            return role === 'cast' ? 'ナイトワーク' : 'キャスト';
        }

        function inferCastTraits(item) {
            var text = normalizeText([item.name, item.area, item.text].join(' '));
            var traits = [];

            if (includesAny(text, [/ノルマなし/, /自由出勤/, /未経験/, /アットホーム/, /安心/, /ゆる/])) traits.push('norma_loose');
            if (includesAny(text, [/高収入/, /バック/, /歩合/, /売上/, /指名/, /稼げ/])) traits.push('norma_hard');
            if (includesAny(text, [/落ち着/, /会員制/, /上品/, /紳士/, /丁寧/, /静か/, /ゆったり/])) traits.push('vibe_quiet');
            if (includesAny(text, [/ワイワイ/, /にぎやか/, /盛り上/, /パーティ/, /元気/, /カラオケ/])) traits.push('vibe_party');
            if (includesAny(text, [/未経験/, /サポート/, /研修/, /教育/, /育成/, /フォロー/, /安心/])) traits.push('staff_teach');
            if (includesAny(text, [/自由/, /自分らしく/, /裁量/, /任せ/, /のびのび/, /個性/])) traits.push('staff_free');

            if (traits.indexOf('norma_loose') === -1 && traits.indexOf('norma_hard') === -1) {
                traits.push(/スナック|ラウンジ|会員制/.test(item.type) ? 'norma_loose' : 'norma_hard');
            }
            if (traits.indexOf('vibe_quiet') === -1 && traits.indexOf('vibe_party') === -1) {
                traits.push(/スナック|ラウンジ|会員制|クラブ/.test(item.type) ? 'vibe_quiet' : 'vibe_party');
            }
            if (traits.indexOf('staff_teach') === -1 && traits.indexOf('staff_free') === -1) {
                traits.push(text ? 'staff_teach' : 'staff_free');
            }

            return unique(traits);
        }

        function inferShopTraits(item) {
            var text = normalizeText([item.name, item.area, item.text].join(' '));
            var traits = [];

            if (includesAny(text, [/マイペース/, /安心/, /未経験/, /穏やか/, /ゆったり/])) traits.push('ambition_soft');
            if (includesAny(text, [/高収入/, /稼ぎたい/, /向上心/, /やる気/, /売上/, /ガッツ/])) traits.push('ambition_hard');
            if (includesAny(text, [/明るい/, /元気/, /盛り上/, /社交的/, /会話好き/, /親しみ/])) traits.push('vibe_party');
            if (includesAny(text, [/丁寧/, /落ち着/, /聞き上手/, /穏やか/, /しっとり/])) traits.push('vibe_quiet');
            if (includesAny(text, [/未経験/, /学びたい/, /サポート/, /教えて/, /安心/])) traits.push('support_teach');
            if (includesAny(text, [/自由/, /自分らしく/, /任せて/, /自走/, /柔軟/])) traits.push('support_free');

            if (traits.indexOf('ambition_soft') === -1 && traits.indexOf('ambition_hard') === -1) {
                traits.push(text ? 'ambition_hard' : 'ambition_soft');
            }
            if (traits.indexOf('vibe_quiet') === -1 && traits.indexOf('vibe_party') === -1) {
                traits.push('vibe_quiet');
            }
            if (traits.indexOf('support_teach') === -1 && traits.indexOf('support_free') === -1) {
                traits.push(text ? 'support_teach' : 'support_free');
            }

            return unique(traits);
        }

        function buildTags(traits, type) {
            var tags = [];

            if (traits.indexOf('norma_loose') !== -1 || traits.indexOf('ambition_soft') !== -1) tags.push('#ゆるめ');
            if (traits.indexOf('norma_hard') !== -1 || traits.indexOf('ambition_hard') !== -1) tags.push('#ガッツリ');
            if (traits.indexOf('vibe_quiet') !== -1) tags.push('#落ち着き');
            if (traits.indexOf('vibe_party') !== -1) tags.push('#ワイワイ');
            if (traits.indexOf('staff_teach') !== -1 || traits.indexOf('support_teach') !== -1) tags.push('#育成枠');
            if (traits.indexOf('staff_free') !== -1 || traits.indexOf('support_free') !== -1) tags.push('#自由度高め');
            if (type && type !== 'キャスト' && type !== 'ナイトワーク') tags.push('#' + type);

            return unique(tags).slice(0, 4);
        }

        var allItems = Array.isArray(payload.items) ? payload.items.map(function (raw) {
            var item = {
                id: raw.id || '',
                name: raw.name || (role === 'cast' ? 'ショップ' : 'キャスト'),
                area: raw.area || 'エリア未設定',
                text: raw.text || '',
                image: raw.image || '/assets/images/common/no-image.png',
                url: raw.url || '#',
                age: raw.age || null
            };

            item.type = inferType(item);
            item.traits = role === 'cast' ? inferCastTraits(item) : inferShopTraits(item);
            item.tags = buildTags(item.traits, item.type);
            item.desc = item.text || (role === 'cast'
                ? '条件に合いそうなお店候補です。詳細ページで雰囲気を確認してみてください。'
                : '雰囲気が合いそうなキャスト候補です。プロフィール詳細で確認してみてください。');

            return item;
        }) : [];

        function disableOptionButtons() {
            root.querySelectorAll('[data-ai-answer]').forEach(function (button) {
                button.disabled = true;
            });
        }

        function scrollToBottom() {
            window.setTimeout(function () {
                window.scrollTo({
                    top: document.body.scrollHeight,
                    behavior: 'smooth'
                });
            }, 60);
        }

        function createOptionsHtml(options, mode) {
            if (!options || !options.length) {
                return '';
            }

            var classes = 'ai-recommend__options';
            if (mode === 'type_selection') {
                classes += ' ai-recommend__options--grid';
            }

            var html = '<div class="' + classes + '">';
            options.forEach(function (option) {
                var extraClass = option.wide ? ' ai-recommend__option--wide' : '';
                html += '<button type="button" class="ai-recommend__option' + extraClass + '" data-ai-answer="' + escapeHtml(option.value) + '">' + escapeHtml(option.label) + '</button>';
            });
            html += '</div>';

            return html;
        }

        function appendMessage(kind, text, options, mode) {
            var box = document.createElement('div');
            box.className = 'ai-recommend__message ai-recommend__message--' + kind;

            var bubbleRow = document.createElement('div');
            bubbleRow.className = 'ai-recommend__bubble-row';

            if (kind === 'ai') {
                bubbleRow.innerHTML =
                    '<div class="ai-recommend__avatar"><img src="' + escapeHtml(avatar) + '" alt="オコジョガイド"></div>' +
                    '<div class="ai-recommend__bubble">' + formatMessage(text) + '</div>';
            } else if (kind === 'typing') {
                box.className += ' ai-recommend__message--typing';
                bubbleRow.innerHTML =
                    '<div class="ai-recommend__avatar"><img src="' + escapeHtml(avatar) + '" alt="オコジョガイド"></div>' +
                    '<div class="ai-recommend__bubble"><i class="fas fa-ellipsis-h"></i> 入力中...</div>';
            } else {
                bubbleRow.innerHTML = '<div class="ai-recommend__bubble">' + escapeHtml(text) + '</div>';
            }

            box.appendChild(bubbleRow);

            if (kind === 'ai' && options && options.length) {
                box.insertAdjacentHTML('beforeend', createOptionsHtml(options, mode));
            }

            chatBox.appendChild(box);
            scrollToBottom();

            return box;
        }

        function addAiMessage(text, options, mode) {
            return appendMessage('ai', text, options, mode);
        }

        function addUserMessage(text) {
            return appendMessage('user', text);
        }

        function showTyping() {
            return appendMessage('typing', '');
        }

        function areaMatches(selectedArea, itemArea) {
            if (!selectedArea || selectedArea.indexOf('こだわらない') !== -1) {
                return true;
            }

            var tokens = areaGroups[selectedArea] || [selectedArea];
            return tokens.some(function (token) {
                return String(itemArea || '').indexOf(token) !== -1;
            });
        }

        function scoreCastItem(item) {
            var score = 0;

            if (state.answers.priority.indexOf('働きやすさ') !== -1) {
                if (item.traits.indexOf(state.answers.norma) !== -1) score += 5;
                if (item.traits.indexOf(state.answers.vibe) !== -1) score += 3;
                if (item.traits.indexOf(state.answers.staff) !== -1) score += 2;
                if (areaMatches(state.answers.area, item.area)) score += 1;
            } else if (state.answers.priority.indexOf('場所') !== -1) {
                if (areaMatches(state.answers.area, item.area)) score += 10;
                if (item.traits.indexOf(state.answers.vibe) !== -1) score += 1;
            } else {
                if (item.traits.indexOf('norma_hard') !== -1) score += 5;
                if (item.traits.indexOf('vibe_party') !== -1) score += 2;
                if (areaMatches(state.answers.area, item.area)) score += 1;
            }

            return score;
        }

        function scoreShopItem(item) {
            var score = 0;

            if (state.answers.priority.indexOf('相性') !== -1) {
                if (item.traits.indexOf(state.answers.ambition) !== -1) score += 4;
                if (item.traits.indexOf(state.answers.vibe) !== -1) score += 3;
                if (item.traits.indexOf(state.answers.support) !== -1) score += 3;
                if (areaMatches(state.answers.area, item.area)) score += 1;
            } else if (state.answers.priority.indexOf('場所') !== -1) {
                if (areaMatches(state.answers.area, item.area)) score += 10;
                if (item.traits.indexOf(state.answers.vibe) !== -1) score += 1;
            } else {
                if (item.traits.indexOf('ambition_hard') !== -1) score += 5;
                if (item.traits.indexOf('support_free') !== -1) score += 2;
                if (areaMatches(state.answers.area, item.area)) score += 1;
            }

            return score;
        }

        function createCard(item, reason) {
            var badge = role === 'cast'
                ? escapeHtml((item.area || 'エリア未設定') + ' / ' + (item.type || 'ナイトワーク'))
                : escapeHtml((item.area || 'エリア未設定') + ' / キャスト');
            var title = role === 'cast'
                ? escapeHtml(item.name)
                : escapeHtml(item.name + (item.age ? ' (' + item.age + ')' : ''));
            var linkLabel = role === 'cast' ? '求人詳細を見る' : 'プロフィールを見る';

            return '' +
                '<article class="ai-recommend__card">' +
                    '<div class="ai-recommend__card-media">' +
                        '<img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.name) + '">' +
                        '<div class="ai-recommend__card-badge">' + badge + '</div>' +
                    '</div>' +
                    '<div class="ai-recommend__card-body">' +
                        '<h4 class="ai-recommend__card-title">' + title + '</h4>' +
                        '<div class="ai-recommend__reason"><i class="fas fa-check-circle"></i>' + escapeHtml(reason) + '</div>' +
                        '<p class="ai-recommend__card-desc">' + escapeHtml(item.desc) + '</p>' +
                        '<div class="ai-recommend__tags">' + item.tags.map(function (tag) {
                            return '<span class="ai-recommend__tag">' + escapeHtml(tag) + '</span>';
                        }).join('') + '</div>' +
                        '<a href="' + escapeHtml(item.url) + '" class="ai-recommend__card-link">' + linkLabel + '</a>' +
                    '</div>' +
                '</article>';
        }

        function reasonText(item) {
            if (role === 'cast') {
                if (state.answers.priority.indexOf('場所') !== -1) return 'エリア希望に近い候補';
                if (state.answers.priority.indexOf('稼ぐ') !== -1) return '高収入狙いと相性が良さそう';
                if (item.traits.indexOf('norma_loose') !== -1) return 'ノルマ面で無理しにくそう';
                if (item.traits.indexOf('staff_teach') !== -1) return 'サポートが手厚そう';
                return '働きやすさの相性が良さそう';
            }

            if (state.answers.priority.indexOf('場所') !== -1) return 'エリア希望に近い候補';
            if (state.answers.priority.indexOf('即戦力') !== -1) return '即戦力イメージに近い候補';
            if (item.traits.indexOf('support_teach') !== -1) return '育成方針と相性が良さそう';
            return '雰囲気の相性が良さそう';
        }

        function showCards(batch) {
            var wrap = document.createElement('div');
            wrap.className = 'ai-recommend__cards';
            wrap.innerHTML = batch.map(function (item) {
                return createCard(item, reasonText(item));
            }).join('');
            chatBox.appendChild(wrap);
            scrollToBottom();
        }

        function showNextBatch() {
            var batch = state.matches.slice(state.matchIndex, state.matchIndex + 3);

            if (!batch.length) {
                addAiMessage('この条件で出せる候補はここまででした。条件を変えて、もう一度探してみる？', [
                    { value: 'リセットして最初から', label: 'リセットして最初から' }
                ]);
                return;
            }

            var introText = '';
            if (state.matchIndex === 0) {
                if (role === 'cast') {
                    if (state.answers.priority.indexOf('働きやすさ') !== -1) {
                        introText = 'あなたの性格と希望条件を重ねると、まずはこの3件が相性よさそうだよ。';
                    } else if (state.answers.priority.indexOf('場所') !== -1) {
                        introText = '**エリア重視**で条件が近い順に並べたよ。';
                    } else {
                        introText = '**稼ぎ重視**で勢いが合いそうな候補から出してみるね。';
                    }
                } else {
                    if (state.answers.priority.indexOf('相性') !== -1) {
                        introText = '雰囲気と育成方針の相性を見て、まずはこの3名が良さそうだよ。';
                    } else if (state.answers.priority.indexOf('場所') !== -1) {
                        introText = '**エリア重視**で候補を先に出すね。';
                    } else {
                        introText = '**即戦力重視**で前向きさが合いそうな候補から並べたよ。';
                    }
                }
            } else {
                introText = 'ほかにも相性が良さそうな候補があるよ。';
            }

            addAiMessage(introText);
            window.setTimeout(function () {
                showCards(batch);
                state.matchIndex += 3;

                if (state.matches.length > state.matchIndex) {
                    addAiMessage('どうかな？ もっと他の候補も見てみる？', [
                        { value: 'もっと他の候補を見る', label: 'もっと他の候補を見る' },
                        { value: 'この中から選ぶ', label: 'この中から選ぶ' }
                    ]);
                } else {
                    addAiMessage('おすすめは以上だよ。気になる候補があれば、そのまま詳細を開いて確認してみてね。', [
                        { value: 'リセットして最初から', label: 'もう一度やり直す' }
                    ]);
                }
            }, 250);
        }

        function calculateMatches() {
            addAiMessage(role === 'cast'
                ? '候補を整理しているよ。少し待ってね...'
                : 'キャスト候補を整理しているよ。少し待ってね...');

            window.setTimeout(function () {
                state.matches = allItems.slice().sort(function (a, b) {
                    var scoreA = role === 'cast' ? scoreCastItem(a) : scoreShopItem(a);
                    var scoreB = role === 'cast' ? scoreCastItem(b) : scoreShopItem(b);
                    return scoreB - scoreA;
                });
                state.matchIndex = 0;
                showNextBatch();
            }, 1100);
        }

        function nextQuestionCast(text) {
            switch (state.step) {
                case 1:
                    if (text.indexOf('まだ') !== -1 || text.indexOf('わからない') !== -1) {
                        state.step = 1.5;
                        addAiMessage('16タイプがわかると、より合うお店を選びやすいよ。今すぐ診断してみる？', [
                            { value: 'やってみる (診断サイトへ)', label: 'やってみる (診断サイトへ)' },
                            { value: '今はやらない', label: '今はやらない' }
                        ]);
                        return;
                    }

                    state.answers.type = text;
                    state.step = 2;
                    addAiMessage('**' + text + '** タイプだね。次は**希望のエリア**を教えてね。', [
                        { value: '六本木・西麻布', label: '六本木・西麻布' },
                        { value: '新宿・歌舞伎町', label: '新宿・歌舞伎町' },
                        { value: '銀座・新橋', label: '銀座・新橋' },
                        { value: '恵比寿・中目黒', label: '恵比寿・中目黒' },
                        { value: 'こだわらない', label: 'こだわらない' }
                    ]);
                    return;

                case 1.5:
                    if (text.indexOf('やってみる') !== -1) {
                        addAiMessage('診断が終わったら、また戻ってきて教えてね。');
                        window.setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 1200);
                        return;
                    }

                    state.answers.type = 'unknown';
                    state.step = 2;
                    addAiMessage('OK、そのまま進めよう。**希望のエリア**はどのあたり？', [
                        { value: '六本木・西麻布', label: '六本木・西麻布' },
                        { value: '新宿・歌舞伎町', label: '新宿・歌舞伎町' },
                        { value: '銀座・新橋', label: '銀座・新橋' },
                        { value: '恵比寿・中目黒', label: '恵比寿・中目黒' },
                        { value: 'こだわらない', label: 'こだわらない' }
                    ]);
                    return;

                case 2:
                    state.answers.area = text;
                    state.step = 3;
                    addAiMessage('お店の雰囲気はどっちが楽？', [
                        { value: 'ワイワイ盛り上がる (パリピ系)', label: 'ワイワイ盛り上がる (パリピ系)' },
                        { value: 'しっぽり話す (落ち着き系)', label: 'しっぽり話す (落ち着き系)' }
                    ]);
                    return;

                case 3:
                    state.answers.vibe = text.indexOf('ワイワイ') !== -1 ? 'vibe_party' : 'vibe_quiet';
                    state.step = 4;
                    addAiMessage('ぶっちゃけ、数字やノルマの温度感はどっちがいい？', [
                        { value: '絶対ムリ！気楽にやりたい (#ゆる稼ぎ)', label: '絶対ムリ！気楽にやりたい' },
                        { value: '稼げるなら戦う (#ガッツリ)', label: '稼げるなら戦う' }
                    ]);
                    return;

                case 4:
                    state.answers.norma = text.indexOf('ムリ') !== -1 ? 'norma_loose' : 'norma_hard';
                    state.step = 5;
                    addAiMessage('スタッフさんとの距離感はどうしたい？', [
                        { value: '手取り足取り教えてほしい (#育成枠)', label: '手取り足取り教えてほしい' },
                        { value: '自由にやらせてほしい (#個人商店)', label: '自由にやらせてほしい' }
                    ]);
                    return;

                case 5:
                    state.answers.staff = text.indexOf('教えて') !== -1 ? 'staff_teach' : 'staff_free';
                    state.step = 6;
                    addAiMessage('最後に、今回のお店探しで何を一番優先したい？', [
                        { value: '場所 (エリア絶対！)', label: '場所 (エリア絶対！)' },
                        { value: '働きやすさ (性格重視)', label: '働きやすさ (性格重視)' },
                        { value: 'とにかく稼ぐ', label: 'とにかく稼ぐ' }
                    ]);
                    return;

                case 6:
                    state.answers.priority = text;
                    calculateMatches();
                    state.step = 7;
                    return;

                default:
                    if (text.indexOf('この中から選ぶ') !== -1) {
                        addAiMessage('気になる候補のボタンから、そのまま詳細ページを開いてみてね。');
                        return;
                    }
                    if (text.indexOf('リセット') !== -1) {
                        init();
                    }
            }
        }

        function nextQuestionShop(text) {
            switch (state.step) {
                case 1:
                    if (text.indexOf('まだ') !== -1 || text.indexOf('わからない') !== -1) {
                        state.step = 1.5;
                        addAiMessage('16タイプがあると、雰囲気に合うキャストを絞りやすいよ。今すぐ診断してみる？', [
                            { value: 'やってみる (診断サイトへ)', label: 'やってみる (診断サイトへ)' },
                            { value: '今はやらない', label: '今はやらない' }
                        ]);
                        return;
                    }

                    state.answers.type = text;
                    state.step = 2;
                    addAiMessage('**' + text + '** タイプ向けの視点で見ていくね。まずは**希望のエリア**を教えて。', [
                        { value: '六本木・西麻布', label: '六本木・西麻布' },
                        { value: '新宿・歌舞伎町', label: '新宿・歌舞伎町' },
                        { value: '銀座・新橋', label: '銀座・新橋' },
                        { value: '恵比寿・中目黒', label: '恵比寿・中目黒' },
                        { value: 'こだわらない', label: 'こだわらない' }
                    ]);
                    return;

                case 1.5:
                    if (text.indexOf('やってみる') !== -1) {
                        addAiMessage('診断が終わったら、また戻ってきて教えてね。');
                        window.setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 1200);
                        return;
                    }

                    state.answers.type = 'unknown';
                    state.step = 2;
                    addAiMessage('OK、そのまま進めよう。まずは**希望のエリア**を教えて。', [
                        { value: '六本木・西麻布', label: '六本木・西麻布' },
                        { value: '新宿・歌舞伎町', label: '新宿・歌舞伎町' },
                        { value: '銀座・新橋', label: '銀座・新橋' },
                        { value: '恵比寿・中目黒', label: '恵比寿・中目黒' },
                        { value: 'こだわらない', label: 'こだわらない' }
                    ]);
                    return;

                case 2:
                    state.answers.area = text;
                    state.step = 3;
                    addAiMessage('採用したい接客の空気感はどっち寄り？', [
                        { value: '明るくワイワイ盛り上がるタイプ', label: '明るくワイワイ盛り上がるタイプ' },
                        { value: '落ち着いて丁寧に話せるタイプ', label: '落ち着いて丁寧に話せるタイプ' }
                    ]);
                    return;

                case 3:
                    state.answers.vibe = text.indexOf('ワイワイ') !== -1 ? 'vibe_party' : 'vibe_quiet';
                    state.step = 4;
                    addAiMessage('採用したい温度感はどっち？', [
                        { value: '安心重視でじっくり育てたい', label: '安心重視でじっくり育てたい' },
                        { value: '稼ぐ意欲が高い子がいい', label: '稼ぐ意欲が高い子がいい' }
                    ]);
                    return;

                case 4:
                    state.answers.ambition = text.indexOf('稼ぐ') !== -1 ? 'ambition_hard' : 'ambition_soft';
                    state.step = 5;
                    addAiMessage('サポートの距離感はどうしたい？', [
                        { value: '手厚く育成したい', label: '手厚く育成したい' },
                        { value: '自走できる子がいい', label: '自走できる子がいい' }
                    ]);
                    return;

                case 5:
                    state.answers.support = text.indexOf('手厚く') !== -1 ? 'support_teach' : 'support_free';
                    state.step = 6;
                    addAiMessage('最後に、今回の推薦で一番重視したいことは？', [
                        { value: '場所 (エリア絶対！)', label: '場所 (エリア絶対！)' },
                        { value: '相性 (雰囲気重視)', label: '相性 (雰囲気重視)' },
                        { value: 'とにかく即戦力', label: 'とにかく即戦力' }
                    ]);
                    return;

                case 6:
                    state.answers.priority = text;
                    calculateMatches();
                    state.step = 7;
                    return;

                default:
                    if (text.indexOf('この中から選ぶ') !== -1) {
                        addAiMessage('気になる候補のボタンから、そのままプロフィール詳細を開いてみてね。');
                        return;
                    }
                    if (text.indexOf('リセット') !== -1) {
                        init();
                    }
            }
        }

        function askInitialQuestion() {
            addAiMessage(role === 'cast'
                ? 'おつかれさま。オコジョガイドだよ。\nあなたに合う**お店**を探すために、いくつか質問させてね。\n\nまずは**16タイプ接客診断**の結果を教えて。'
                : 'おつかれさま。オコジョガイドだよ。\n希望に合う**キャスト**を見つけるために、いくつか質問させてね。\n\nまずは**16タイプ接客診断**の結果を教えて。', type16Codes.map(function (code) {
                return { value: code, label: code };
            }).concat([{ value: 'まだやっていない / わからない', label: 'まだやっていない / わからない', wide: true }]), 'type_selection');
        }

        function processAnswer(text) {
            if (!text) {
                return;
            }

            disableOptionButtons();
            addUserMessage(text);
            if (input) input.value = '';

            var typing = showTyping();
            window.setTimeout(function () {
                if (typing && typing.parentNode) {
                    typing.parentNode.removeChild(typing);
                }

                if (text.indexOf('もっと') !== -1) {
                    showNextBatch();
                    return;
                }

                if (text.indexOf('リセット') !== -1) {
                    init();
                    return;
                }

                if (role === 'cast') {
                    nextQuestionCast(text);
                } else {
                    nextQuestionShop(text);
                }
            }, 420);
        }

        function init() {
            state.step = 1;
            state.answers = {};
            state.matches = [];
            state.matchIndex = 0;
            chatBox.innerHTML = '';

            if (!allItems.length) {
                addAiMessage('今はおすすめ候補のデータがまだ少ないみたい。検索条件を変えるか、一覧タブから探してみてね。');
                return;
            }

            askInitialQuestion();
        }

        root.addEventListener('click', function (event) {
            var answerButton = event.target.closest('[data-ai-answer]');
            if (answerButton && !answerButton.disabled) {
                processAnswer(answerButton.getAttribute('data-ai-answer') || '');
            }
        });

        if (sendButton) {
            sendButton.addEventListener('click', function () {
                processAnswer((input && input.value ? input.value : '').trim());
            });
        }

        if (input) {
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    processAnswer(input.value.trim());
                }
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', init);
        }

        init();
    });
})();
