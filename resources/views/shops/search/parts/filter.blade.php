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
        </div>
        @include('common.search.sort-panel')
    </div>

    {{-- クイックフィルタ：モーダルを開かずワンタップで絞り込み（モーダル内フォームと同期） --}}
    <div class="search-quick-chips" role="group" aria-label="クイック絞り込み">
        <button type="button" class="search-quick-chip"
                data-quick-chip
                data-quick-actions='[{"type":"input","name":"age_min","value":"20"},{"type":"input","name":"age_max","value":"25"}]'>
            <i class="fas fa-cake-candles" aria-hidden="true"></i>20代前半
        </button>
        <button type="button" class="search-quick-chip"
                data-quick-chip
                data-quick-actions='[{"type":"radio","name":"night_work_exp","value":"yes"}]'>
            <i class="fas fa-medal" aria-hidden="true"></i>経験者
        </button>
        <button type="button" class="search-quick-chip"
                data-quick-chip
                data-quick-actions='[{"type":"radio","name":"shift_frequency","value":"週3回以上"}]'>
            <i class="fas fa-calendar-week" aria-hidden="true"></i>週3回以上
        </button>
        <button type="button" class="search-quick-chip"
                data-quick-chip
                data-quick-actions='[{"type":"checkbox","name":"work_periods[]","value":"night"}]'>
            <i class="fas fa-moon" aria-hidden="true"></i>夜出勤OK
        </button>
    </div>

    <div class="search-filter-summary" id="search-condition-summary" style="display: none;">
        <span class="search-filter-summary__label">指定中の条件：</span>
        <span class="search-filter-summary__text" id="search-condition-summary-text"></span>
    </div>
</div>
@include('shops.search.parts.detail-search-modal')
