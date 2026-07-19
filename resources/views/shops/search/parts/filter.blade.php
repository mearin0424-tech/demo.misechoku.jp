@php
    $sortOptions = $sortOptions ?? [];
    $sort = $sort ?? 'hitokoto';
@endphp
<div class="search-filter-inner">
    <div class="search-filter-minimal-wrap">
        <div class="search-filter-minimal-row">
            <input type="text" id="search-keyword" class="search-filter-minimal__input" placeholder="キャスト名・エリアなどフリーワードで検索" value="{{ request('keyword', '') }}" autocomplete="off">
            <button type="button" class="search-filter-icon-btn search-filter-icon-btn--gold" id="search-keyword-submit" aria-label="検索">
                <i class="fas fa-search" aria-hidden="true"></i>
            </button>
            <button type="button" class="search-filter-icon-btn" id="search-sort-trigger" aria-label="並び替え" aria-expanded="false" aria-haspopup="true" aria-controls="search-sort-panel">
                <i class="fas fa-sort-amount-down" aria-hidden="true"></i>
            </button>
            <button type="button" class="search-filter-icon-btn search-topbar-toggle" id="search-topbar-toggle" aria-expanded="true" aria-controls="search-topbar-extra" aria-label="検索条件エリアを開閉">
                <i class="fas fa-chevron-up" aria-hidden="true"></i>
            </button>
        </div>
        @include('common.search.sort-panel')
    </div>

    {{-- 開閉エリア：探索拠点 + 詳細フィルター + 指定中の条件（閉じると結果一覧が広がる） --}}
    <div class="search-topbar-extra" id="search-topbar-extra">
        @include('layouts.parts.location-pill')

        {{-- 詳細フィルター：旧 右下FAB を上部に一本化（IDは search-detail.js 互換） --}}
        <button type="button" id="open-detail-search" class="search-detail-inline" aria-controls="detail-search-modal" aria-expanded="false">
            <i class="fas fa-sliders-h" aria-hidden="true"></i>
            <span class="search-detail-inline__label">詳細フィルター</span>
            <span id="detail-search-badge" class="search-detail-inline__badge" style="display: none;" aria-hidden="true">0</span>
            <i class="fas fa-chevron-right search-detail-inline__chev" aria-hidden="true"></i>
        </button>

        <div class="search-filter-summary" id="search-condition-summary" style="display: none;">
            <span class="search-filter-summary__label">指定中の条件：</span>
            <span class="search-filter-summary__text" id="search-condition-summary-text"></span>
        </div>
    </div>
</div>
@include('shops.search.parts.detail-search-modal')
