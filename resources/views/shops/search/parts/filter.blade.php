<div class="search-filter-inner">
    <div class="search-filter-simple">
        <label class="search-filter-simple__label">簡単キーワード検索</label>
        <div class="search-filter-simple-row">
            <input type="text" id="search-keyword" placeholder="名前・エリア・キーワードなど" class="search-filter-simple__input" value="{{ request('keyword', '') }}">
            <div class="search-filter-actions">
                <button type="button" class="search-filter-detail-btn" id="open-detail-search" aria-controls="detail-search-modal" aria-expanded="false">
                    <i class="fas fa-sliders-h"></i>
                    <span>詳細検索</span>
                    <span class="search-filter-detail-btn__badge" id="detail-search-badge" style="display: none;">0</span>
                </button>
            </div>
        </div>
    </div>
    <div class="search-filter-summary" id="search-condition-summary" style="display: none;">
        <span class="search-filter-summary__label">指定中の条件：</span>
        <span class="search-filter-summary__text" id="search-condition-summary-text"></span>
    </div>
</div>
@include('shops.search.parts.detail-search-modal')
