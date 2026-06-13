@php
    $todoField = static function ($t, string $key, $default = null) {
        if (is_array($t)) return $t[$key] ?? $default;
        if (is_object($t)) return $t->{$key} ?? $default;
        return $default;
    };
@endphp
<div id="header-task-popup" class="header-popup stop-propagation" style="display:none;">
    <div class="task-popup-header">
        <h4>やることリスト</h4>
        <button class="btn-close-popup" onclick="togglePopup('header-task-popup')">&times;</button>
    </div>
    <div class="task-popup-content">
        @if(!empty($todoList) && count($todoList) > 0)
            @foreach($todoList as $todo)
                @php
                    $url = $todoField($todo, 'url');
                    $urgency = $todoField($todo, 'urgency', 'normal');
                    $text = $todoField($todo, 'text', '');
                    $icon = $urgency === 'high' ? 'fa-circle-exclamation' : 'fa-circle-info';
                @endphp
                @if($url)
                    <a href="{{ $url }}" class="task-popup-item task-popup-item--{{ $urgency }}">
                        <i class="fas {{ $icon }}"></i>
                        <span class="task-popup-item__text">{{ $text }}</span>
                        <i class="fas fa-chevron-right task-popup-item__chev" aria-hidden="true"></i>
                    </a>
                @else
                    <div class="task-popup-item task-popup-item--{{ $urgency }}">
                        <i class="fas {{ $icon }}"></i>
                        <span class="task-popup-item__text">{{ $text }}</span>
                    </div>
                @endif
            @endforeach
        @else
            <div class="task-popup-empty">
                <i class="fas fa-circle-check"></i>
                <p>すべてのタスクが完了しています</p>
            </div>
        @endif
    </div>
</div>
