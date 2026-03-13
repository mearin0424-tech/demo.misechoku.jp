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
        var pane = root.closest('.tab-pane');
        if (pane && !pane.classList.contains('active')) {
            return;
        }

        var payload = parseJson(dataEl.textContent || '{}') || {};
        var chatBox = root.querySelector('[data-ai-chat]');
        var avatar = root.getAttribute('data-avatar') || '';
        var role = root.getAttribute('data-role') || payload.role || 'cast';
        var hospitalityAxisDescriptions = {
            L: { title: 'リード型 (L)', text: '会話の主導権を握り、積極的に場を盛り上げるのが得意なタイプです。' },
            F: { title: 'フォロワー型 (F)', text: '聞き役に徹し、お客様のペースに合わせて心地よい空間を作るのが得意なタイプです。' },
            C: { title: '恋人型 (C)', text: '「女性らしさ」や「色気」を武器に、お客様を異性としてドキドキさせるのが得意なタイプです。' },
            P: { title: 'パートナー型 (P)', text: '「知性」や「人間的な面白さ」を武器に、お客様と対等な関係を築くのが得意なタイプです。' },
            I: { title: '懐（ふところ）型 (I)', text: '「人懐っこさ」や「素の自分」を見せ、短時間でお客様の懐に飛び込むのが得意なタイプです。' },
            O: { title: '領域（テリトリー）型 (O)', text: '「プロとしての距離感」を保ち、「憧れ」や「ミステリアスさ」を演出するのが得意なタイプです。' },
            H: { title: 'ハンター型 (H)', text: '「瞬発力」で、イベントなど短期集中的に大きな結果を出すのが得意なタイプです。' },
            R: { title: 'リレーション型 (R)', text: '「マメな連絡」や「継続力」で、お客様との関係をじっくり育てるのが得意なタイプです。' }
        };
        var hospitalityQuestions = [
            { id: 'q1', axis: 'axis1', statement: '自分から積極的に話題を振って、場を盛り上げるほうだ' },
            { id: 'q2', axis: 'axis1', statement: '会話が途切れたら、すぐに新しい話題を探すのが得意だ' },
            { id: 'q3', axis: 'axis1', statement: 'お客様より、自分が主導権を握って会話を進めることが多い' },
            { id: 'q4', axis: 'axis2', statement: '自分の「女性らしさ」や「色気」を褒められることが多い' },
            { id: 'q5', axis: 'axis2', statement: 'お客様には「異性」としてドキドキしてほしい' },
            { id: 'q6', axis: 'axis2', statement: 'お客様と友達のような関係になるより、疑似恋愛を楽しみたい' },
            { id: 'q7', axis: 'axis3', statement: '初対面のお客様でも、すぐにタメ口やあだ名で呼べる' },
            { id: 'q8', axis: 'axis3', statement: '自分のプライベートな話や弱みを見せて、相手の心を開かせる' },
            { id: 'q9', axis: 'axis3', statement: 'ミステリアスな雰囲気より、人懐っこい雰囲気で接する' },
            { id: 'q10', axis: 'axis4', statement: 'マメな連絡（LINEやメール）は正直、あまり得意ではない' },
            { id: 'q11', axis: 'axis4', statement: 'コツコツと細く長くより、イベントなどで一気に売上を上げたい' },
            { id: 'q12', axis: 'axis4', statement: '接客以外の時間（店外）で、お客様との関係を育てるのは苦手だ' }
        ];
        var hospitalityResults = {
            LPIR: {
                title: '姉御肌・みんなのママ タイプ',
                strength: '人間力と信頼感で、お客様を「ファン」に変える',
                description: '場をリードする会話力(L)と、サバサバした人間的魅力(P)が光るタイプ。人懐っこく(I)、誰の懐にもスッと入るのが得意です。さらに、マメな連絡(R)も欠かさないため、お客様からは「頼れる姉貴」「地元の友達」のように深く信頼されます。色恋を超えた人間力でファンを作る、お店のムードメーカーです。',
                weakness: '頼られすぎて疲弊しやすいです。また、一歩引いて静かに待つような受け身の接客は不得意です。'
            },
            FCIH: {
                title: '天性の甘え上手・小悪魔 タイプ',
                strength: '本能をくすぐる「小悪魔的魅力」で、エースを狙う才能',
                description: '聞き上手(F)で相手を立てつつ、ここぞという場面で女性らしさ(C)を武器にする天性の才能。人懐っこく(I)甘える姿は、お客様の「守ってあげたい」本能を強烈にくすぐります。連絡は気まぐれ(H)ですが、その「手に入らなさ」がさらに魅力を高め、太客を掴むエース気質の持ち主です。',
                weakness: '長期的な関係構築が苦手で、急な来店が減ると一気に売上も落ちる傾向があります。また、同性からの支持は得にくいかもしれません。'
            },
            LPOR: {
                title: '才色兼備・プロフェッショナル タイプ',
                strength: '知性と一線を引く「格」で、ハイクラス層を魅了する',
                description: '高い知性(P)と会話力(L)で、お客様を対等なパートナーとして楽しませます。馴れ合いを嫌い、プロとしての高い一線(O)を引く姿は、まさに「高嶺の花」。しかし、営業連絡(R)は完璧にこなし、そのギャップがお客様の心を掴みます。経営者やハイクラス層からの絶大な支持を得る、孤高の才女です。',
                weakness: '完璧主義が邪魔をして、お客様の「隙」や「弱み」に共感しにくいです。高い目標を持つお客様以外には壁を感じさせてしまうことがあります。'
            },
            FPOR: {
                title: '癒し系・カウンセラー タイプ',
                strength: '「圧倒的な安心感」と「知性」で、お客様の心を解かす',
                description: '穏やかな聞き役(F)に徹し、お客様の心に寄り添います。プロの距離感(O)を保ちつつ、知的な会話(P)で相手の承認欲求を満たすのが得意。マメな連絡(R)で築いた信頼関係は固く、接待や「心から癒されたい」と願うお客様にとって、唯一無二の存在となります。',
                weakness: '受け身になりすぎて会話をリードできず、お客様を退屈にさせてしまうことがあります。異性としての魅力(C)での勝負は難しいでしょう。'
            },
            FPOH: {
                title: 'クールな戦略家タイプ',
                strength: '冷静な分析眼と「的確な一手」で結果を出す、凄腕戦略家',
                description: '一見クールな聞き役(F)で、プロの距離(O)を保ちます。しかし頭脳は常にフル回転。お客様が求める知的な会話(P)を的確に提供し、心に刺さるアドバイスも。連絡は最小限(H)ですが、お客様の状況を完璧に把握し、最適なタイミングでイベントを仕掛ける、凄腕の戦略家です。',
                weakness: '感情表現が乏しく見え、冷たい印象を持たれやすいです。長期的に顧客を「育てる」作業には忍耐力が必要です。'
            },
            FPIR: {
                title: '健気な妹・後輩タイプ',
                strength: '「裏表のない健気さ」で、お客様の庇護欲を刺激する',
                description: '聞き上手(F)で人懐っこく(I)、素直に相手の懐に飛び込みます。サバサバした性格(P)で「妹みたいだ」と可愛がられ、マメな連絡(R)で「いつも気にかけてくれる」安心感を育てるのが得意。健気で裏表のない姿が、お客様の庇護欲を刺激する愛されキャラです。',
                weakness: '友達のような関係になりすぎて、色恋的な緊張感が生まれにくいです。お客様の要求を断れず、尽くしすぎる傾向があります。'
            },
            FPIH: {
                title: '自由奔放な猫タイプ',
                strength: '「捕まえられない魅力」で、お客様を本能的に夢中にさせる',
                description: '人懐っこく(I)甘えてきたかと思えば、サバサバした態度(P)で聞き役(F)に徹する。連絡は気まぐれ(H)で、決して媚びない。そんな自由奔放な姿が「目が離せない」とお客様を夢中にさせます。捕まえられない猫のように、本能で人を惹きつける魅力の持ち主です。',
                weakness: '自分のペースを崩せず、マメさ(R)を求めるお客様は離れていきます。本質的に自由なので、組織的な動きは苦手かもしれません。'
            },
            FCOR: {
                title: '高嶺の花・お姫様タイプ',
                strength: 'お客様を「育てる」ことで、自分だけの王子様にする才能',
                description: '基本は聞き役(F)ですが、決して安売りしないプロの距離感(O)を保ちます。女性としての魅力(C)を完璧に演出し、マメな連絡(R)でじっくりとお客様を教育。「この子にふさわしい男になりたい」とお客様に努力させる、まさに「育てる」タイプのお姫様です。',
                weakness: '敷居が高く、新規のお客様が指名しにくいです。感情的な起伏が少なく、近寄りがたい印象を与えがちです。'
            },
            FCOH: {
                title: 'ミステリアスな魔性タイプ',
                strength: '「追わせる」ことを徹底し、お客様の独占欲を煽る魔性',
                description: '相手の話を聞き(F)ながらも、本心を見せないミステリアスな魅力(O)が最大武器。計算された色気(C)で相手を翻弄し、営業連絡はせず(H)にお客様から「会いたい」と言わせます。追えば逃げる、まさに魔性。気づけば高額を使わせてしまう、危険な魅力の持ち主です。',
                weakness: '連絡が来ないことに不安を感じるお客様を切り捨てがちです。感情の交流を求めてくるお客様には対応が難しいでしょう。'
            },
            FCIR: {
                title: '尽くす大和撫子タイプ',
                strength: '「徹底的に尽くす」ことで、疑似恋愛の頂点を極める',
                description: '徹底してお客様を立てる聞き役(F)。人懐っこさ(I)と女性らしさ(C)をフルに使い、お客様に尽くします。毎日のマメな連絡(R)も欠かさず、「自分だけを特別扱いしてくれる」という優越感を与えます。疑似恋愛のプロフェッショナルで、指名が途切れない人気キャストです。',
                weakness: 'お客様のペースに合わせすぎ、自己主張ができないことがあります。尽くしすぎて、対等な関係を築くのが難しいです。'
            },
            LPOH: {
                title: 'カリスマ・リーダータイプ',
                strength: '「この子と話したい」とお客様から会いに来させる別格の存在',
                description: '圧倒的な会話力(L)と知性(P)で、その場を完全に支配します。馴れ合いは好まずプロの距離(O)を保ち、営業はピンポイント(H)。「あの人と話すためだけに来た」とお客様に言わせる、別格のカリスマ。お店の「顔」として君臨する、選ばれし存在です。',
                weakness: 'カリスマ性が高すぎて、お客様に「自分にはもったいない」と感じさせてしまうことがあります。親しみやすさに欠けます。'
            },
            LPIH: {
                title: '豪快なムードメーカータイプ',
                strength: '「とにかく楽しい！」という「圧倒的な引力」で稼ぐ才能',
                description: '自分が中心(L)となって場を盛り上げ、一瞬で懐に入る(I)天才。裏表のないサバサバした性格(P)で、男女問わず人気者に。営業は気まぐれ(H)でも、その圧倒的な楽しさが全てをカバー。「あの子がいないと始まらない」と言われる、お店の太陽です。',
                weakness: '楽しさが優先され、お客様の深い悩みや感情を汲み取るのが苦手です。勢いで売上を上げるため、安定感に欠けることがあります。'
            },
            LCOR: {
                title: '完璧主義の女王様タイプ',
                strength: '「プロ意識の高さ」と「完璧なギャップ」でファンを魅了する',
                description: '会話(L)も色気(C)も自分が主導権を握ります。プロの距離感(O)を保ち、簡単には心を許しません。しかし、営業連絡(R)は誰よりも完璧。その徹底したプロ意識とギャップが、お客様を「自分だけが落とせるかも」と夢中にさせる、完璧主義の女王様です。',
                weakness: '融通が利かない印象を持たれやすいです。お客様の小さな失敗や失言を許せず、プレッシャーを与えてしまうことがあります。'
            },
            LCOH: {
                title: '華麗なるアタッカータイプ',
                strength: '「圧倒的なスター性」で、お客様の競争心を煽る攻撃型エース',
                description: '美貌と会話力(L)、そして圧倒的な色気(C)で、一瞬でお客様を魅了します。プロの距離(O)を保ち、余計な営業は一切しない(H)。その潔さとスター性が、「一晩でいいから指名したい」とお客様の競争心と独占欲を煽る、超攻撃型のエースです。',
                weakness: '高圧的な印象を与えやすく、お客様を緊張させてしまうことがあります。マメさがなく、一度離れたお客様は戻ってきにくいです。'
            },
            LCIR: {
                title: '世話焼きギャルタイプ',
                strength: '「計算された親近感」で、お客様を確実に管理する賢さ',
                description: '明るく会話をリード(L)し、人懐っこさ(I)で甘え上手。女性らしさ(C)も前面に出す、まさに「イマドキの女の子」。しかし裏では、誰よりもマメな連絡(R)でお客様を管理するしっかり者。そのギャップと計算高さで、確実に売上を立てる賢いタイプです。',
                weakness: '押しが強く、お客様のペースを乱してしまうことがあります。甘え上手ゆえに、わがままに見えてしまうこともあります。'
            },
            LCIH: {
                title: '嵐のような太陽タイプ',
                strength: '「強烈なインパクト」で、お客様を中毒にさせる天性の魅力',
                description: '会話も色恋(C)も常に自分が中心(L)。一瞬で相手の懐(I)に飛び込み、嵐のように場を盛り上げます。連絡は気まぐれ(H)ですが、その強烈なインパクトは誰にも真似できません。「またあの子と騒ぎたい」とお客様を中毒にさせる、天性の魅力の塊です。',
                weakness: '楽しすぎるがゆえに、落ち着いた接客や深い話をするのが苦手です。衝動的な接客になりやすく、計画性がないです。'
            },
            DEFAULT: {
                title: '診断不能タイプ',
                strength: '診断不能',
                description: 'あなたのユニークなタイプです。うまく診断できませんでした。もう一度お試しください。',
                weakness: 'どのタイプにも偏らず、強みが発揮しにくい可能性があります。専門家のアドバイスを求めることをおすすめします。'
            }
        };
        var savedPersonalityType = typeof payload.personalityType === 'string'
            ? payload.personalityType.toUpperCase()
            : '';
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
            matchIndex: 0,
            hospitalityAnswers: {}
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

        function getAreaOptions() {
            return [
                { value: '六本木・西麻布', label: '六本木・西麻布' },
                { value: '新宿・歌舞伎町', label: '新宿・歌舞伎町' },
                { value: '銀座・新橋', label: '銀座・新橋' },
                { value: '恵比寿・中目黒', label: '恵比寿・中目黒' },
                { value: 'こだわらない', label: 'こだわらない' }
            ];
        }

        function askAreaQuestion() {
            var typeLabel = state.answers.typeTitle
                ? state.answers.typeTitle + '（' + state.answers.type + '）'
                : state.answers.type;
            if (role === 'cast') {
                addAiMessage('**' + typeLabel + '** だね。次は**希望のエリア**を教えてね。', getAreaOptions());
            } else {
                addAiMessage('**' + typeLabel + '** を踏まえて見ていくね。まずは**希望のエリア**を教えて。', getAreaOptions());
            }
        }

        function askAreaQuestionWithoutType() {
            if (role === 'cast') {
                addAiMessage('OK、そのまま進めよう。**希望のエリア**はどのあたり？', getAreaOptions());
            } else {
                addAiMessage('OK、そのまま進めよう。まずは**希望のエリア**を教えて。', getAreaOptions());
            }
        }

        function getHospitalityScaleOptions() {
            return [
                { value: '1', label: '1' },
                { value: '2', label: '2' },
                { value: '3', label: '3' },
                { value: '4', label: '4' },
                { value: '5', label: '5' }
            ];
        }

        function askHospitalityQuestion(index) {
            var question = hospitalityQuestions[index];
            if (!question) {
                return;
            }

            var intro = '';
            if (index === 0) {
                intro = role === 'cast'
                    ? 'おつかれさま。オコジョガイドだよ。\nあなたに合う**お店**を探す前に、まずは**接客タイプ診断**をしよう。\n**1 = そう思わない / 5 = そう思う** で答えてね。\n\n'
                    : 'おつかれさま。オコジョガイドだよ。\n希望に合う**キャスト**を見つける前に、まずは**接客タイプ診断**をしよう。\n**1 = そう思わない / 5 = そう思う** で答えてね。\n\n';
            }

            addAiMessage(intro + 'Q' + String(index + 1) + '. ' + question.statement, getHospitalityScaleOptions(), 'type_selection');
        }

        function calculateHospitalityDiagnosis() {
            var scores = {
                axis1: { A: 0, B: 0 },
                axis2: { A: 0, B: 0 },
                axis3: { A: 0, B: 0 },
                axis4: { A: 0, B: 0 }
            };

            hospitalityQuestions.forEach(function (question) {
                var answer = state.hospitalityAnswers[question.id];
                if (!scores[question.axis]) {
                    return;
                }

                switch (answer) {
                    case '5':
                        scores[question.axis].A += 2;
                        break;
                    case '4':
                        scores[question.axis].A += 1;
                        break;
                    case '2':
                        scores[question.axis].B += 1;
                        break;
                    case '1':
                        scores[question.axis].B += 2;
                        break;
                }
            });

            var type = [
                scores.axis1.A > scores.axis1.B ? 'L' : 'F',
                scores.axis2.A > scores.axis2.B ? 'C' : 'P',
                scores.axis3.A > scores.axis3.B ? 'I' : 'O',
                scores.axis4.A > scores.axis4.B ? 'H' : 'R'
            ].join('');
            var result = hospitalityResults[type] || hospitalityResults.DEFAULT;

            return {
                type: type,
                title: result.title,
                strength: result.strength,
                description: result.description,
                weakness: result.weakness
            };
        }

        function buildHospitalityBreakdown(type) {
            return String(type || '').split('').map(function (key) {
                var axis = hospitalityAxisDescriptions[key];
                if (!axis) {
                    return '';
                }

                return '・**' + axis.title + '** ' + axis.text;
            }).filter(Boolean).join('\n');
        }

        function applyHospitalityDiagnosis(type) {
            var normalizedType = String(type || '').toUpperCase();
            var diagnosis = hospitalityResults[normalizedType];
            if (!diagnosis) {
                return false;
            }

            state.answers.type = normalizedType;
            state.answers.typeTitle = diagnosis.title;
            state.answers.typeStrength = diagnosis.strength;

            return true;
        }

        function startWithSavedPersonalityType() {
            if (!applyHospitalityDiagnosis(savedPersonalityType)) {
                return false;
            }

            state.step = 2;
            addAiMessage(
                '登録済みの接客タイプ診断結果は **(' + state.answers.type + ') ' + state.answers.typeTitle + '** だよ。\n' +
                'この結果をもとに、おすすめを探していくね。'
            );
            window.setTimeout(function () {
                askAreaQuestion();
            }, 250);

            return true;
        }

        function handleHospitalityDiagnosis(text) {
            if (typeof state.step !== 'string' || state.step.indexOf('quiz') !== 0) {
                return false;
            }

            if (!/^[1-5]$/.test(text)) {
                return false;
            }

            var currentIndex = parseInt(state.step.replace('quiz', ''), 10) - 1;
            if (isNaN(currentIndex) || !hospitalityQuestions[currentIndex]) {
                return false;
            }

            state.hospitalityAnswers[hospitalityQuestions[currentIndex].id] = text;

            if (currentIndex < hospitalityQuestions.length - 1) {
                state.step = 'quiz' + String(currentIndex + 2);
                askHospitalityQuestion(currentIndex + 1);
                return true;
            }

            var diagnosis = calculateHospitalityDiagnosis();
            state.answers.type = diagnosis.type;
            state.answers.typeTitle = diagnosis.title;
            state.answers.typeStrength = diagnosis.strength;
            state.hospitalityAnswers = {};
            state.step = 2;
            addAiMessage(
                '診断結果は **(' + diagnosis.type + ') ' + diagnosis.title + '** だったよ。\n\n' +
                '**あなたの強み**\n' + diagnosis.strength + '\n\n' +
                '**ニガテかも**\n' + diagnosis.weakness + '\n\n' +
                diagnosis.description + '\n\n' +
                buildHospitalityBreakdown(diagnosis.type)
            );
            window.setTimeout(function () {
                askAreaQuestion();
            }, 250);
            return true;
        }

        function nextQuestionCast(text) {
            switch (state.step) {
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
                        { value: '絶対ムリ！気楽にやりたい', label: '絶対ムリ！気楽にやりたい' },
                        { value: '稼げるなら戦う', label: '稼げるなら戦う' }
                    ]);
                    return;

                case 4:
                    state.answers.norma = text.indexOf('ムリ') !== -1 ? 'norma_loose' : 'norma_hard';
                    state.step = 5;
                    addAiMessage('スタッフさんとの距離感はどうしたい？', [
                        { value: '手取り足取り教えてほしい', label: '手取り足取り教えてほしい' },
                        { value: '自由にやらせてほしい', label: '自由にやらせてほしい' }
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
            if (role === 'cast' && startWithSavedPersonalityType()) {
                return;
            }

            askHospitalityQuestion(0);
        }

        function processAnswer(text) {
            if (!text) {
                return;
            }

            disableOptionButtons();
            addUserMessage(text);

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

                if (handleHospitalityDiagnosis(text)) {
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
            state.step = 'quiz1';
            state.answers = {};
            state.matches = [];
            state.matchIndex = 0;
            state.hospitalityAnswers = {};
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

        init();
    });
})();
