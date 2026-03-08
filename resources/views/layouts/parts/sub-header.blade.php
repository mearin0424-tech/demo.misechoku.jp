{{-- 
    $tabs: [['id' => 'pane-1', 'label' => 'タブ1', 'active' => true], ...] 
    URL付きの場合は 'url' => route(...) を渡すとリンクになる（SEARCHのタブ用）
--}}
<div class="sub-header-wrapper">
    <div class="sub-header-tabs">
        @foreach($tabs as $tab)
            @if(!empty($tab['url']))
                <a href="{{ $tab['url'] }}" class="tab-item {{ ($tab['active'] ?? false) ? 'active' : '' }}">
                    {{ $tab['label'] }}
                </a>
            @else
                <div class="tab-item {{ ($tab['active'] ?? false) ? 'active' : '' }}" 
                     data-target="{{ $tab['id'] }}">
                    {{ $tab['label'] }}
                </div>
            @endif
        @endforeach
    </div>
</div>