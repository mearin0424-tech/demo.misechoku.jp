document.addEventListener('DOMContentLoaded', function() {
    const characterGuide = document.getElementById('character-guide');
    const closeBtn = document.getElementById('character-close-trigger');
    const messageContent = document.getElementById('character-message-content');

    // ×ボタンで非表示
    if (closeBtn && characterGuide) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            characterGuide.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            characterGuide.style.opacity = '0';
            characterGuide.style.transform = 'translateY(10px)';
            setTimeout(() => characterGuide.classList.add('is-hidden'), 300);
        });
    }

    // 外部（Swiperなど）からメッセージを更新する関数
    window.updateCharacterMessage = function(newMessage) {
        if (!messageContent || !characterGuide) return;

        if (newMessage && newMessage.trim() !== "") {
            messageContent.innerHTML = newMessage.replace(/\n/g, '<br>');
            characterGuide.classList.remove('is-hidden');
            characterGuide.style.opacity = '1';
            characterGuide.style.transform = 'translateY(0)';
        } else {
            characterGuide.classList.add('is-hidden');
        }
    };
});