{{--
    $tabs: [['id' => 'pane-1', 'label' => 'タブ1', 'active' => true], ...]
    URL付きの場合は 'url' => route(...) を渡すとリンクになる（SEARCHのタブ用）
    'icon' に Font Awesome のクラス（例: 'fas fa-bookmark'）を渡すとラベル前にアイコンを表示する
--}}
<div class="sub-header-wrapper">
    <div class="sub-header-tabs">
        @foreach($tabs as $tab)
            @if(!empty($tab['url']))
                <a href="{{ $tab['url'] }}" class="tab-item {{ ($tab['active'] ?? false) ? 'active' : '' }}">
                    @if(!empty($tab['icon']))<i class="{{ $tab['icon'] }} tab-item__icon" aria-hidden="true"></i>@endif
                    {{ $tab['label'] }}
                </a>
            @else
                <div class="tab-item {{ ($tab['active'] ?? false) ? 'active' : '' }}"
                     data-target="{{ $tab['id'] }}">
                    @if(!empty($tab['icon']))<i class="{{ $tab['icon'] }} tab-item__icon" aria-hidden="true"></i>@endif
                    {{ $tab['label'] }}
                </div>
            @endif
        @endforeach
    </div>
</div>