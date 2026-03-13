<div id="mypage-personality-test-panel" class="mypage-personality-test" style="display: none;">
    <div id="shindan-app" data-base-path="/personality-test/">
        <button class="btn-back-to-top" type="button">トップに戻る</button>

        <div id="start-screen">
            <h1 class="title"><span>接客タイプ診断</span></h1>
            <p class="subtitle">What is Your Hospitality Style</p>
            <p class="description">
                あなたにピッタリな接客スタイルは？<br>
                12の質問であなたの強みを診断します！
            </p>

            <img src="{{ asset('personality-test/images/top_image.png') }}" alt="トップ画像" id="top-image">
            <br><br>
            <button id="start-btn" type="button" disabled>読み込み中...</button>
        </div>

        <form id="shindan-form">
            <h2 class="form-title">接客タイプ診断</h2>
            <div id="loading">診断データを読み込んでいます...</div>
            <button type="submit" id="submit-btn" style="display:none;">診断する</button>
        </form>

        <div id="result">
            <h2>あなたの接客タイプは...</h2>
            <h3 id="result-type-title"></h3>

            <img id="result-image" src="" alt="診断結果イラスト" style="display: none;">

            <div class="result-section">
                <p class="section-title">あなたの強み</p>
                <div id="result-strength" class="result-content"></div>

                <p class="section-title">ニガテかも</p>
                <div id="result-weakness" class="result-content"></div>
            </div>

            <p id="result-description" class="detail-description"></p>

            <div id="result-breakdown"></div>
            <div id="share-buttons">
                <a href="#" id="share-x" class="share-btn" target="_blank">Xでシェア</a>
                <a href="#" id="share-line" class="share-btn" target="_blank">LINEでシェア</a>
            </div>
            <p id="save-status" style="margin-top: 16px; text-align: center; color: #6A4C9C;"></p>
        </div>
    </div>
</div>
