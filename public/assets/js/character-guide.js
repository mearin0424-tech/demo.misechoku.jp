document.addEventListener('DOMContentLoaded', function() {
    const characterGuide = document.getElementById('character-guide');
    const characterWrap = characterGuide ? characterGuide.querySelector('.guide-character-wrap') : null;
    const messageContent = document.getElementById('character-message-content');

    function hideBubble() {
        if (window.forceCharacterGuideVisible) {
            return;
        }
        if (characterGuide && !characterGuide.classList.contains('is-hidden')) {
            characterGuide.classList.add('bubble-hidden');
        }
    }

    function showBubble() {
        if (characterGuide) {
            characterGuide.classList.remove('bubble-hidden');
        }
    }

    function toggleBubble(e) {
        if (e) {
            e.stopPropagation();
        }
        if (!characterGuide || characterGuide.classList.contains('is-hidden')) {
            return;
        }

        if (characterGuide.classList.contains('bubble-hidden')) {
            showBubble();
            return;
        }

        hideBubble();
    }

    if (characterWrap && characterGuide) {
        characterWrap.addEventListener('click', toggleBubble);
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
        } else {
            characterGuide.classList.add('is-hidden');
        }
    };
});