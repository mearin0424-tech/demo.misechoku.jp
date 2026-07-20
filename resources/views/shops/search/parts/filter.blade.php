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
            {{-- 右端：詳細フィルター（モーダルを直接開く。バッジ=指定中の条件数） --}}
            <button type="button" class="search-filter-icon-btn search-filter-icon-btn--filter" id="open-detail-search" aria-label="詳細フィルター" aria-controls="detail-search-modal" aria-expanded="false">
                <i class="fas fa-sliders-h" aria-hidden="true"></i>
                <span id="detail-search-badge" class="search-filter-icon-btn__badge" style="display: none;" aria-hidden="true">0</span>
            </button>
        </div>
        @include('common.search.sort-panel')
    </div>

    {{-- 探索拠点の設定はマイページへ移設。開閉式の条件エリアは廃止（右端の詳細フィルターから直接モーダルへ） --}}
</div>
@include('shops.search.parts.detail-search-modal')
