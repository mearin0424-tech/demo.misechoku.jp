/**
 * オコジョガイド共通ロジック
 * 閉じる処理および外部からのメッセージ更新を管理します。
 */
document.addEventListener('DOMContentLoaded', function() {
    // 要素の取得
    const okojoGuide = document.getElementById('okojo-guide');
    const closeBtn = document.getElementById('okojo-close-trigger');
    const messageContent = document.getElementById('okojo-message-content');

    // 1. 「×」ボタン押下でガイドを非表示にする
    if (closeBtn && okojoGuide) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // スタイルで非表示にする（is-hiddenクラスの付与）
            okojoGuide.classList.add('is-hidden');
            
            // オプション：一度閉じたらセッション中は出さない場合はここでlocalStorage等に保存可能
            // localStorage.setItem('okojo_guide_closed', 'true');
        });
    }

    /**
     * オコジョのセリフを動的に変更するグローバル関数
     * Swiperのイベント（slideChange）や、特定の操作時に呼び出してください。
     * * @param {string} newMessage - 表示したい新しいメッセージ
     * * 使用例: window.updateOkojoMessage("新しいメッセージだよ！");
     */
    window.updateOkojoMessage = function(newMessage) {
        if (!messageContent || !okojoGuide) {
            console.warn('Okojo Guide elements not found.');
            return;
        }

        if (newMessage && newMessage.trim() !== "") {
            // 改行コードを<br>に変換して反映
            const formattedMessage = newMessage.replace(/\n/g, '<br>');
            messageContent.innerHTML = formattedMessage;
            
            // メッセージがある場合は表示する
            okojoGuide.classList.remove('is-hidden');
        } else {
            // メッセージが空の場合はガイド自体を隠す
            okojoGuide.classList.add('is-hidden');
        }
    };
});