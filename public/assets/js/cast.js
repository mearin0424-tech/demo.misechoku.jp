document.addEventListener('DOMContentLoaded', function() {
    // 絞り込みボタンの開閉ロジック
    const toggleBtn = document.getElementById('toggle-sort');
    const sortArea = document.getElementById('sort-area');

    if (toggleBtn && sortArea) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // 表示・非表示の切り替え
            if (sortArea.style.display === 'none' || sortArea.style.display === '') {
                sortArea.style.display = 'block';
            } else {
                sortArea.style.display = 'none';
            }
        });
    }
});