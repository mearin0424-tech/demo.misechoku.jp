{{-- 星評価（0〜5、0.5刻み）。$score: float, $size: sm|md|lg --}}
@php
    $raw = max(0, min(5, (float) ($score ?? 0)));
    $rounded = round($raw * 2) / 2;
    $sizeKey = $size ?? 'md';
    $sizeClass = 'review-stars--' . (in_array($sizeKey, ['sm', 'md', 'lg'], true) ? $sizeKey : 'md');
@endphp
<div class="review-stars {{ $sizeClass }}" role="img" aria-label="評価 {{ number_format($rounded, 1) }} / 5">
    @for($i = 1; $i <= 5; $i++)
        @if($i <= $rounded)
            <i class="fas fa-star review-star review-star--full" aria-hidden="true"></i>
        @elseif($i - 0.5 <= $rounded)
            <i class="fas fa-star-half-alt review-star review-star--half" aria-hidden="true"></i>
        @else
            <i class="far fa-star review-star review-star--empty" aria-hidden="true"></i>
        @endif
    @endfor
</div>
