@php
    $sortOptions = $sortOptions ?? [];
    $sort = $sort ?? 'hitokoto';
@endphp
<div class="search-filter-inner">
    <div class="search-filter-minimal-wrap">
        <div class="search-filter-minimal-row">
            <input type="text" id="search-keyword" class="search-filter-minimal__input" placeholder="エリア・店名などフリーワードで検索" value="{{ request('keyword', '') }}" autocomplete="off">
            <button type="button" class="search-filter-icon-btn search-filter-icon-btn--gold" id="search-keyword-submit" aria-label="検索">
                <i class="fas fa-search" aria-hidden="true"></i>
            </button>
            <button type="button" class="search-filter-icon-btn" id="search-sort-trigger" aria-label="並び替え" aria-expanded="false" aria-haspopup="true" aria-controls="search-sort-panel">
                <i class="fas fa-sort-amount-down" aria-hidden="true"></i>
            </button>
        </div>
        @include('common.search.sort-panel')
    </div>
    <div class="search-filter-summary" id="search-condition-summary" style="display: none;">
        <span class="search-filter-summary__label">指定中の条件：</span>
        <span class="search-filter-summary__text" id="search-condition-summary-text"></span>
    </div>
</div>
@include('casts.parts.detail-search-modal')
