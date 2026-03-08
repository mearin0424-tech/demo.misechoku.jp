document.addEventListener('DOMContentLoaded', function() {
    const characterGuide = document.getElementById('character-guide');
    const closeBtn = document.getElementById('character-close-trigger');
    const characterWrap = characterGuide ? characterGuide.querySelector('.guide-character-wrap') : null;
    const messageContent = document.getElementById('character-message-content');

    // 吹き出しを隠す（画面上の操作時）
    function hideBubble() {
        if (characterGuide && !characterGuide.classList.contains('is-hidden')) {
            characterGuide.classList.add('bubble-hidden');
        }
    }

    // 吹き出しを表示（オコジョ押下時）
    function showBubble(e) {
        if (e) e.stopPropagation();
        if (characterGuide) {
            characterGuide.classList.remove('bubble-hidden');
        }
    }

    // ×ボタンでガイド全体を非表示
    if (closeBtn && characterGuide) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            characterGuide.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            characterGuide.style.opacity = '0';
            characterGuide.style.transform = 'translateY(10px)';
            setTimeout(() => characterGuide.classList.add('is-hidden'), 300);
        });
    }

    // オコジョ（キャラクター）を押したら吹き出しを再表示
    if (characterWrap && characterGuide) {
        characterWrap.addEventListener('click', showBubble);
    }

    // スクロール・タッチ・他を触ったら吹き出しを隠す
    if (characterGuide) {
        window.addEventListener('scroll', hideBubble, { passive: true });
        document.addEventListener('touchstart', hideBubble, { passive: true });
        document.addEventListener('click', function(e) {
            // オコジョまたは吹き出し内のクリックはここでは隠さない（オコジョクリックは showBubble で表示に）
            if (characterGuide.contains(e.target)) return;
            hideBubble();
        });
    }

    // 外部（Swiperなど）からメッセージを更新する関数
    window.updateCharacterMessage = function(newMessage) {
        if (!messageContent || !characterGuide) return;

        if (newMessage && newMessage.trim() !== "") {
            messageContent.innerHTML = newMessage.replace(/\n/g, '<br>');
            characterGuide.classList.remove('is-hidden');
            characterGuide.classList.remove('bubble-hidden'); // メッセージ更新時は吹き出し表示
            characterGuide.style.opacity = '1';
            characterGuide.style.transform = 'translateY(0)';
        } else {
            characterGuide.classList.add('is-hidden');
        }
    };
});