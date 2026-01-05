<div class="timeline-card">
    <div class="tl-header">
        <img src="{{ $post['img'] }}" class="tl-icon">
        <div class="tl-info">
            <h3>{{ $post['name'] }}</h3>
            <span>{{ $post['time'] }}</span>
        </div>
    </div>
    <div class="tl-body">{!! nl2br(e($post['text'])) !!}</div>
</div>