/**
 * オコジョガイド共通ロジック
 * 閉じる処理および外部からのメッセージ更新を管理します。
 */
document.addEventListener('DOMContentLoaded', function() {
    // 要素の取得
    const characterGuide = document.getElementById('character-guide');
    const closeBtn = document.getElementById('character-close-trigger');
    const messageContent = document.getElementById('character-message-content');

    // 1. 「×」ボタン押下でガイドを非表示にする
    if (closeBtn && characterGuide) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // スタイルで非表示にする（is-hiddenクラスの付与）
            characterGuide.classList.add('is-hidden');
            
            // オプション：一度閉じたらセッション中は出さない場合はここでlocalStorage等に保存可能
            // localStorage.setItem('character_guide_closed', 'true');
        });
    }

    /**
     * オコジョのセリフを動的に変更するグローバル関数
     * Swiperのイベント（slideChange）や、特定の操作時に呼び出してください。
     * * @param {string} newMessage - 表示したい新しいメッセージ
     * * 使用例: window.updateCharacterMessage("新しいメッセージだよ！");
     */
    window.updateCharacterMessage = function(newMessage) {
        if (!messageContent || !characterGuide) {
            console.warn('Character Guide elements not found.');
            return;
        }

        if (newMessage && newMessage.trim() !== "") {
            // 改行コードを<br>に変換して反映
            const formattedMessage = newMessage.replace(/\n/g, '<br>');
            messageContent.innerHTML = formattedMessage;
            
            // メッセージがある場合は表示する
            characterGuide.classList.remove('is-hidden');
        } else {
            // メッセージが空の場合はガイド自体を隠す
            characterGuide.classList.add('is-hidden');
        }
    };
});