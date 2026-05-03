@php
    $sortOptions = $sortOptions ?? [];
    $sort = $sort ?? 'hitokoto';
@endphp
<div id="search-sort-panel" class="search-sort-panel" role="menu" aria-labelledby="search-sort-trigger" hidden>
    @foreach($sortOptions as $value => $label)
        <button type="button" role="menuitem" class="search-sort-panel__item {{ $sort === $value ? 'is-active' : '' }}" data-search-sort-value="{{ $value }}">{{ $label }}</button>
    @endforeach
</div>
<input type="hidden" id="search-sort-current" name="sort" value="{{ $sort }}">
