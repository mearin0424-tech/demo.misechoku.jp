document.addEventListener('DOMContentLoaded', function() {
    const characterGuide = document.getElementById('character-guide');
    const characterWrap = characterGuide ? characterGuide.querySelector('.guide-character-wrap') : null;
    const messageContent = document.getElementById('character-message-content');
    const closeBtn = document.getElementById('character-guide-close');

    const STORAGE_KEY = 'character-guide-dismissed';

    function isDismissedForThisPage() {
        try {
            var raw = sessionStorage.getItem(STORAGE_KEY);
            if (!raw) return false;
            var paths = JSON.parse(raw);
            return Array.isArray(paths) && paths.indexOf(window.location.pathname) !== -1;
        } catch (e) {
            return false;
        }
    }

    if (characterGuide && isDismissedForThisPage()) {
        characterGuide.classList.add('is-dismissed');
    }

    function hideBubble() {
        if (window.forceCharacterGuideVisible) {
            return;
        }
        if (characterGuide && !characterGuide.classList.contains('is-hidden') && !characterGuide.classList.contains('is-dismissed')) {
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
        if (!characterGuide || characterGuide.classList.contains('is-hidden') || characterGuide.classList.contains('is-dismissed')) {
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

    if (closeBtn && characterGuide) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            try {
                var raw = sessionStorage.getItem(STORAGE_KEY);
                var paths = raw ? JSON.parse(raw) : [];
                if (!Array.isArray(paths)) paths = [];
                if (paths.indexOf(window.location.pathname) === -1) {
                    paths.push(window.location.pathname);
                    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(paths));
                }
            } catch (err) {}
            characterGuide.classList.add('is-dismissed');
        });
    }

    // 外部（Swiperなど）からメッセージを更新する関数
    window.updateCharacterMessage = function(newMessage) {
        if (!messageContent || !characterGuide) return;

        if (newMessage && newMessage.trim() !== "") {
            messageContent.innerHTML = newMessage.replace(/\n/g, '<br>');
            characterGuide.classList.remove('is-hidden');
            characterGuide.classList.remove('bubble-hidden');
            characterGuide.classList.remove('is-dismissed');
            characterGuide.style.opacity = '1';
            characterGuide.style.transform = 'translateY(0)';
        } else {
            characterGuide.classList.add('is-hidden');
        }
    };
});