{{-- 画像スライダー（両カード共通）
     $images: string[]  1枚以上の画像パス
     $altName: string   画像の alt に用いる名前
     $isRecruit: bool   求人カード時は eager 読み込み・alt を簡素化 --}}
@php
    $images = $images ?? [];
    if (empty($images)) {
        $images = [asset('assets/images/common/no-image.png')];
    }
    $imageCount = count($images);
    $altName = $altName ?? '';
    $isRecruit = $isRecruit ?? false;
@endphp
<div class="photo-swiper swiper {{ $imageCount <= 1 ? 'photo-swiper--single' : '' }}">
    <div class="swiper-wrapper">
        @foreach($images as $index => $imgPath)
        <div class="swiper-slide">
            <img
                src="{{ $imgPath }}"
                alt="{{ $isRecruit ? $altName : ($altName . 'の写真' . ($imageCount > 1 ? '（' . ($index + 1) . '枚目）' : '')) }}"
                class="home-photo"
                loading="{{ $index === 0 && $isRecruit ? 'eager' : 'lazy' }}"
            >
        </div>
        @endforeach
    </div>
    @if($imageCount > 1)
    <div class="photo-pagination swiper-pagination stop-propagation"></div>
    @endif
</div>
