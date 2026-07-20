let questionData = [];
let resultData = {};
const axisDescriptions = {
    L: { title: 'リード型 (L)', text: '会話の主導権を握り、積極的に場を盛り上げるのが得意なタイプです。' },
    F: { title: 'フォロワー型 (F)', text: '聞き役に徹し、お客様のペースに合わせて心地よい空間を作るのが得意なタイプです。' },
    C: { title: '恋人型 (C)', text: '「女性らしさ」や「色気」を武器に、お客様を異性としてドキドキさせるのが得意なタイプです。' },
    P: { title: 'パートナー型 (P)', text: '「知性」や「人間的な面白さ」を武器に、お客様と対等な関係を築くのが得意なタイプです。' },
    I: { title: '懐（ふところ）型 (I)', text: '「人懐っこさ」や「素の自分」を見せ、短時間でお客様の懐に飛び込むのが得意なタイプです。' },
    O: { title: '領域（テリトリー）型 (O)', text: '「プロとしての距離感」を保ち、「憧れ」や「ミステリアスさ」を演出するのが得意なタイプです。' },
    H: { title: 'ハンター型 (H)', text: '「瞬発力」で、イベントなど短期集中的に大きな結果を出すのが得意なタイプです。' },
    R: { title: 'リレーション型 (R)', text: '「マメな連絡」や「継続力」で、お客様との関係をじっくり育てるのが得意なタイプです。' }
};

function getShindanApp() {
    return document.getElementById('shindan-app');
}

function getRepoPath() {
    return getShindanApp()?.dataset.basePath || './';
}

function getReturnUrl() {
    const url = new URL(window.location.href);
    const value = url.searchParams.get('return_to') || '';
    return value;
}

function getCookie(name) {
    const escapedName = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const match = document.cookie.match(new RegExp('(?:^|; )' + escapedName + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
}

async function savePersonalityType(type) {
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };
    const xsrfToken = getCookie('XSRF-TOKEN');
    if (xsrfToken) {
        headers['X-XSRF-TOKEN'] = xsrfToken;
    }

    const response = await fetch('/cast/profile/personality-type', {
        method: 'POST',
        headers,
        credentials: 'same-origin',
        body: JSON.stringify({ personality_type: type })
    });

    if (!response.ok) {
        throw new Error('save_failed');
    }

    return response.json();
}

function reflectSavedPersonalityType(type) {
    const typeRow = document.getElementById('personality-type-row');
    const typeDisplay = document.getElementById('personality-type-display');

    if (typeRow) {
        typeRow.style.display = '';
    }
    if (typeDisplay) {
        typeDisplay.textContent = type;
    }
}

function loadCSV(url) {
    return new Promise((resolve, reject) => {
        Papa.parse(url, {
            download: true,
            header: true,
            skipEmptyLines: true,
            encoding: 'UTF-8',
            complete: (results) => resolve(results.data),
            error: (err) => reject(err)
        });
    });
}

async function loadAllData(startBtn) {
    try {
        const repoPath = getRepoPath();
        const [questions, results] = await Promise.all([
            loadCSV(repoPath + 'questions.csv'),
            loadCSV(repoPath + 'results.csv')
        ]);

        questionData = questions;
        results.forEach((row) => {
            if (!row.type) {
                return;
            }

            resultData[row.type] = {
                title: row.title,
                description: row.description,
                strength: row.strength,
                weakness: row.weakness
            };
        });

        generateQuestionsHTML();
        setupAnsweredListener();

        startBtn.disabled = false;
        startBtn.textContent = '診断をはじめる';
    } catch (error) {
        console.error('CSVファイルの読み込みに失敗しました:', error);
        startBtn.textContent = 'エラーが発生しました';
    }
}

function generateQuestionsHTML() {
    const form = document.getElementById('shindan-form');
    if (!form) {
        return;
    }

    const loadingEl = form.querySelector('#loading');
    const submitBtn = form.querySelector('#submit-btn');

    let htmlContent = '';
    let questionCount = 1;

    questionData.forEach((q) => {
        htmlContent += `
            <div class="question" data-axis="${q.axis}" data-id="${q.id}">
                <p>Q${questionCount}. ${q.statement}</p>
                <div class="likert-scale">
                    <label><input type="radio" name="${q.id}" value="1"> <span data-label="1"></span></label>
                    <label><input type="radio" name="${q.id}" value="2"> <span data-label="2"></span></label>
                    <label><input type="radio" name="${q.id}" value="3"> <span data-label="3"></span></label>
                    <label><input type="radio" name="${q.id}" value="4"> <span data-label="4"></span></label>
                    <label><input type="radio" name="${q.id}" value="5"> <span data-label="5"></span></label>
                </div>
                <div class="likert-labels">
                    <span>そう思わない</span>
                    <span>そう思う</span>
                </div>
            </div>
        `;
        questionCount += 1;
    });

    loadingEl.style.display = 'none';
    loadingEl.insertAdjacentHTML('beforebegin', htmlContent);
    submitBtn.style.display = 'block';
}

function setupAnsweredListener() {
    const form = document.getElementById('shindan-form');
    if (!form) {
        return;
    }

    form.addEventListener('change', (event) => {
        if (event.target.type !== 'radio') {
            return;
        }

        const questionDiv = event.target.closest('.question');
        if (questionDiv) {
            questionDiv.classList.add('answered');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const appRoot = getShindanApp();
    if (!appRoot) {
        return;
    }

    const startScreen = appRoot.querySelector('#start-screen');
    const shindanForm = appRoot.querySelector('#shindan-form');
    const resultScreen = appRoot.querySelector('#result');
    const startBtn = appRoot.querySelector('#start-btn');
    const backToTopBtns = appRoot.querySelectorAll('.btn-back-to-top:not(#notification-test-btn)');
    const returnToAiBtn = appRoot.querySelector('#return-to-ai-btn');
    const resultReturnToAiBtn = appRoot.querySelector('#result-return-to-ai-btn');
    const returnUrl = getReturnUrl();

    if (returnUrl) {
        [returnToAiBtn, resultReturnToAiBtn].forEach((linkEl) => {
            if (!linkEl) {
                return;
            }
            linkEl.href = returnUrl;
            linkEl.style.display = 'inline-flex';
        });
    }

    startScreen.style.display = 'block';
    shindanForm.style.display = 'none';
    resultScreen.style.display = 'none';
    backToTopBtns.forEach((btn) => {
        btn.style.display = 'none';
    });

    function resetToStart(event) {
        if (event) {
            event.preventDefault();
        }

        startScreen.style.display = 'block';
        shindanForm.style.display = 'none';
        resultScreen.style.display = 'none';
        backToTopBtns.forEach((btn) => {
            btn.style.display = 'none';
        });

        const tokenArea = appRoot.querySelector('#token-display-area');
        const tokenInfo = appRoot.querySelector('#token-info');
        if (tokenArea) tokenArea.style.display = 'none';
        if (tokenInfo) tokenInfo.style.display = 'none';

        shindanForm.reset();
        appRoot.querySelectorAll('.question.answered').forEach((q) => {
            q.classList.remove('answered');
        });

        appRoot.querySelector('#result-type-title').textContent = '';
        appRoot.querySelector('#result-strength').textContent = '';
        appRoot.querySelector('#result-weakness').textContent = '';
        appRoot.querySelector('#result-description').innerHTML = '';
        appRoot.querySelector('#result-breakdown').innerHTML = '';
        appRoot.querySelector('#result-image').style.display = 'none';

        const saveStatus = appRoot.querySelector('#save-status');
        if (saveStatus) {
            saveStatus.textContent = '';
        }

        appRoot.scrollIntoView({ behavior: 'smooth' });
    }

    startBtn.addEventListener('click', () => {
        startScreen.style.display = 'none';
        shindanForm.style.display = 'block';
        backToTopBtns.forEach((btn) => {
            btn.style.display = 'block';
        });
        appRoot.scrollIntoView({ behavior: 'smooth' });
    });

    // 「戻る」ボタン：診断トップへのリセットではなく、マイページへ戻す
    // （return_to があればそちらを優先。無ければキャストのマイページへ）
    backToTopBtns.forEach((btn) => btn.addEventListener('click', (event) => {
        event.preventDefault();
        window.location.href = returnUrl || '/cast/mypage';
    }));

    setTimeout(() => {
        const title = appRoot.querySelector('.title');
        if (title) {
            title.classList.add('visible');
        }
    }, 300);

    loadAllData(startBtn);

    shindanForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const repoPath = getRepoPath();
        const formData = new FormData(event.target);
        const answers = Object.fromEntries(formData.entries());

        if (Object.keys(answers).length < questionData.length) {
            alert('すべての質問に回答してください。');
            return;
        }

        const scores = {
            axis1: { A: 0, B: 0 },
            axis2: { A: 0, B: 0 },
            axis3: { A: 0, B: 0 },
            axis4: { A: 0, B: 0 }
        };

        questionData.forEach((q) => {
            const answer = answers[q.id];
            switch (answer) {
                case '5':
                    scores[q.axis].A += 2;
                    break;
                case '4':
                    scores[q.axis].A += 1;
                    break;
                case '2':
                    scores[q.axis].B += 1;
                    break;
                case '1':
                    scores[q.axis].B += 2;
                    break;
            }
        });

        const type = [
            scores.axis1.A > scores.axis1.B ? 'L' : 'F',
            scores.axis2.A > scores.axis2.B ? 'C' : 'P',
            scores.axis3.A > scores.axis3.B ? 'I' : 'O',
            scores.axis4.A > scores.axis4.B ? 'H' : 'R'
        ].join('');

        const result = resultData[type] || resultData.DEFAULT;
        const resultEl = appRoot.querySelector('#result');
        const resultImage = resultEl.querySelector('#result-image');

        shindanForm.style.display = 'none';
        resultEl.style.display = 'block';
        backToTopBtns.forEach((btn) => {
            btn.style.display = 'block';
        });

        resultEl.querySelector('#result-type-title').innerHTML = `(${type}) ${result.title}`;

        if (result.title !== '診断不能タイプ') {
            resultImage.src = repoPath + 'images/kotowaza_buta_shinju.png';
            resultImage.alt = result.title;
            resultImage.style.display = 'block';
        } else {
            resultImage.style.display = 'none';
        }

        resultEl.querySelector('#result-strength').textContent = result.strength;
        resultEl.querySelector('#result-weakness').textContent = result.weakness;
        resultEl.querySelector('#result-description').innerHTML = result.description.replace(/\n/g, '<br>');

        const breakdownEl = resultEl.querySelector('#result-breakdown');
        breakdownEl.innerHTML = '';

        type.split('').forEach((key) => {
            const desc = axisDescriptions[key];
            if (!desc) {
                return;
            }

            const axisChar = desc.title.includes('(R)') ? 'R' : desc.title.match(/[A-Z]/)[0];
            breakdownEl.innerHTML += `
                <div class="breakdown-item">
                    <span>${axisChar}</span>
                    <p><strong>${desc.title}</strong>${desc.text}</p>
                </div>
            `;
        });

        const shareUrl = window.location.href;
        const hashTag = '接客タイプ診断';
        const shareText = `私の接客タイプは【${result.title}（${type}）】でした！\n「${result.strength}」\nあなたも診断してみよう！\n#${hashTag}\n`;

        resultEl.querySelector('#share-x').href = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(shareUrl)}`;
        resultEl.querySelector('#share-line').href = `https://line.me/R/msg/text/?${encodeURIComponent(shareText + shareUrl)}`;

        const saveStatus = resultEl.querySelector('#save-status');
        if (saveStatus) {
            saveStatus.textContent = 'プロフィールに保存しています...';
        }

        try {
            await savePersonalityType(type);
            reflectSavedPersonalityType(type);
            if (saveStatus) {
            saveStatus.textContent = `接客タイプ診断結果（${type}）をプロフィールに保存しました。`;
            }
        } catch (error) {
            console.error('接客タイプ診断結果の保存に失敗しました:', error);
            if (saveStatus) {
                saveStatus.textContent = 'プロフィールへの保存に失敗しました。ログイン後にもう一度お試しください。';
            }
        }

        resultEl.scrollIntoView({ behavior: 'smooth' });
    });
});