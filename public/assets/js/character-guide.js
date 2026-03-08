document.addEventListener('DOMContentLoaded', function() {
    const characterGuide = document.getElementById('character-guide');
    const closeBtn = document.getElementById('character-close-trigger');
    const characterWrap = characterGuide ? characterGuide.querySelector('.guide-character-wrap') : null;
    const messageContent = document.getElementById('character-message-content');
    const BUBBLE_AUTO_HIDE_MS = 3000;
    let bubbleHideTimer = null;

    // 吹き出しを隠す（スワイプ・操作時 or 3秒経過）
    function hideBubble() {
        if (bubbleHideTimer) {
            clearTimeout(bubbleHideTimer);
            bubbleHideTimer = null;
        }
        if (characterGuide && !characterGuide.classList.contains('is-hidden')) {
            characterGuide.classList.add('bubble-hidden');
        }
    }

    // 3秒後に吹き出しを隠すタイマーを開始
    function startBubbleAutoHide() {
        if (bubbleHideTimer) clearTimeout(bubbleHideTimer);
        if (!characterGuide || characterGuide.classList.contains('is-hidden')) return;
        bubbleHideTimer = setTimeout(hideBubble, BUBBLE_AUTO_HIDE_MS);
    }

    // 吹き出しを表示（オコジョタップ時）
    function showBubble(e) {
        if (e) e.stopPropagation();
        if (characterGuide) {
            characterGuide.classList.remove('bubble-hidden');
            startBubbleAutoHide(); // 再表示後も3秒で消す
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

    // スクロール・スワイプ・タッチ・クリックで吹き出しを隠す
    if (characterGuide) {
        window.addEventListener('scroll', hideBubble, { passive: true });
        document.addEventListener('touchstart', hideBubble, { passive: true });
        document.addEventListener('touchmove', hideBubble, { passive: true });
        document.addEventListener('click', function(e) {
            if (characterGuide.contains(e.target)) return;
            hideBubble();
        });
        // 初回表示時は3秒で吹き出しを隠す
        if (!characterGuide.classList.contains('is-hidden')) {
            startBubbleAutoHide();
        }
    }

    // 外部（Swiperなど）からメッセージを更新する関数
    window.updateCharacterMessage = function(newMessage) {
        if (!messageContent || !characterGuide) return;

        if (newMessage && newMessage.trim() !== "") {
            messageContent.innerHTML = newMessage.replace(/\n/g, '<br>');
            characterGuide.classList.remove('is-hidden');
            characterGuide.classList.remove('bubble-hidden');
            characterGuide.style.opacity = '1';
            characterGuide.style.transform = 'translateY(0)';
            startBubbleAutoHide();
        } else {
            characterGuide.classList.add('is-hidden');
        }
    };
});