{{-- 
    $tabs: [['id' => 'pane-1', 'label' => 'タブ1', 'active' => true], ...] 
--}}
<div class="sub-header-wrapper">
    <div class="sub-header-tabs">
        @foreach($tabs as $tab)
            <div class="tab-item {{ ($tab['active'] ?? false) ? 'active' : '' }}" 
                 data-target="{{ $tab['id'] }}">
                {{ $tab['label'] }}
            </div>
        @endforeach
    </div>
</div>