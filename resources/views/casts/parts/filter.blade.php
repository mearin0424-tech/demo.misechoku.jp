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

    {{-- クイックフィルタ：モーダルを開かずワンタップで絞り込み（モーダル内フォームと同期） --}}
    <div class="search-quick-chips" role="group" aria-label="クイック絞り込み">
        <button type="button" class="search-quick-chip"
                data-quick-chip
                data-quick-actions='[{"type":"select","name":"hourly_wage","value":"4000"}]'>
            <i class="fas fa-yen-sign" aria-hidden="true"></i>時給4,000円〜
        </button>
        <button type="button" class="search-quick-chip"
                data-quick-chip
                data-quick-actions='[{"type":"checkLabel","name":"welcome_tag_ids[]","label":"未経験"}]'>
            <i class="fas fa-seedling" aria-hidden="true"></i>未経験OK
        </button>
        <button type="button" class="search-quick-chip"
                data-quick-chip
                data-quick-actions='[{"type":"checkLabel","name":"work_style_tag_ids[]","label":"ノルマ"}]'>
            <i class="fas fa-face-smile" aria-hidden="true"></i>ノルマなし
        </button>
        <button type="button" class="search-quick-chip"
                data-quick-chip
                data-quick-actions='[{"type":"checkLabel","name":"welcome_tag_ids[]","label":"体入"}]'>
            <i class="fas fa-door-open" aria-hidden="true"></i>体入OK
        </button>
    </div>

    <div class="search-filter-summary" id="search-condition-summary" style="display: none;">
        <span class="search-filter-summary__label">指定中の条件：</span>
        <span class="search-filter-summary__text" id="search-condition-summary-text"></span>
    </div>
</div>
@include('casts.parts.detail-search-modal')
