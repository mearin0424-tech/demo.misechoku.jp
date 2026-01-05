@extends('layouts.app')

@section('title', 'Media Library')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/gallery.css') }}">
@endpush

@section('content')
<div class="animate-fadeIn flex flex-col h-full bg-[#0a0a0a]">
    <div class="p-6 flex items-center justify-between border-b border-white/5 bg-[#0a0a0a]">
        <a href="{{ route('shop.mypage.index') }}" class="text-gray-400 flex items-center gap-1 no-underline">
            <i class="fas fa-chevron-left"></i>
            <span class="text-sm">Back</span>
        </a>
        <h2 class="text-lg serif-font gold-gradient m-0">Media Library</h2>
        <button type="submit" form="gallery-form" class="text-gold text-sm font-semibold bg-transparent border-none">Done</button>
    </div>

    <div class="flex-1 overflow-y-auto p-6 pb-32">
        <div class="mb-6">
            <h3 class="text-sm text-white font-semibold m-0">Edit Photos</h3>
            <p class="text-xs text-gray-500 mt-1">最大8枚。ガイドに沿って設定してください。</p>
        </div>

        <form id="gallery-form" action="{{ route('shop.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            {{-- photoGuidesの忠実な定義 --}}
            @php
            $photoGuides = [
                ['label' => '顔のアップ', 'icon' => 'fa-expand-arrows-alt', 'desc' => '表情がわかる'],
                ['label' => '胸上のショット', 'icon' => 'fa-user-tie', 'desc' => '清潔感のある'],
                ['label' => '全身の姿', 'icon' => 'fa-street-view', 'desc' => 'スタイルがわかる'],
                ['label' => '最高の笑顔', 'icon' => 'fa-smile-beam', 'desc' => '親しみやすさ'],
                ['label' => '趣味のひと時', 'icon' => 'fa-camera-retro', 'desc' => '自分らしさ'],
                ['label' => 'ライフスタイル', 'icon' => 'fa-mug-hot', 'desc' => '日常の風景'],
                ['label' => '自由な一枚', 'icon' => 'fa-images', 'desc' => 'お気に入りを'],
                ['label' => '自由な一枚', 'icon' => 'fa-images', 'desc' => 'お気に入りを']
            ];
            @endphp

            <div class="grid grid-cols-3 gap-3">
                @for($i=0; $i<8; $i++)
                    @php $imgSrc = $subImages[$i] ?? null; @endphp
                    <div class="photo-slot group" onclick="document.getElementById('file_{{ $i }}').click()">
                        <input type="file" id="file_{{ $i }}" name="images[]" class="hidden" onchange="previewImage(this, {{ $i }})">
                        
                        {{-- 画像がある場合 --}}
                        <img id="preview_{{ $i }}" src="{{ $imgSrc }}" class="w-full h-full object-cover {{ $imgSrc ? '' : 'hidden' }}">
                        
                        @if($imgSrc)
                            <button type="button" class="absolute top-1 right-1 w-5 h-5 bg-black/60 rounded-full flex items-center justify-center text-white border-none" onclick="event.stopPropagation(); removeImage(this, {{ $i }})">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        @endif

                        {{-- 画像がない場合（ガイド表示） --}}
                        <div id="guide_{{ $i }}" class="flex flex-col items-center p-2 text-center pointer-events-none {{ $imgSrc ? 'hidden' : '' }}">
                            <div class="w-10 h-10 flex items-center justify-center mb-1">
                                <i class="fas {{ $photoGuides[$i]['icon'] }} text-gold opacity-30 text-2xl"></i>
                            </div>
                            <span class="text-[9px] text-gray-400 font-semibold mb-0.5">{{ $photoGuides[$i]['label'] }}</span>
                            <span class="text-[7px] text-gray-600 uppercase tracking-tighter">{{ $photoGuides[$i]['desc'] }}</span>
                            <div class="absolute bottom-1 right-1 w-5 h-5 gold-bg rounded-full flex items-center justify-center text-black">
                                <i class="fas fa-plus text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            {{-- ヒントセクション --}}
            <div class="mt-8 glass-panel p-5 rounded-2xl border-gold/10">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-gold/10 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-magic text-gold"></i>
                    </div>
                    <div>
                        <h4 class="text-sm text-white font-semibold italic m-0">Luxe Tips</h4>
                        <p class="text-xs text-gray-400 leading-relaxed mt-1 mb-0">
                            高品質な写真は出会いの質を高めます。ガイドに沿った写真を揃えることで、より魅力的なプロフィールになります。
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input, index) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview_' + index);
            const guide = document.getElementById('guide_' + index);
            const slot = preview.parentElement;

            preview.src = e.target.result;
            preview.classList.remove('hidden');
            guide.classList.add('hidden');

            // 削除ボタンがない場合のみ生成
            if (!slot.querySelector('.delete-btn-dynamic')) {
                const btn = document.createElement('button');
                btn.className = 'absolute top-1 right-1 w-5 h-5 bg-black/60 rounded-full flex items-center justify-center text-white border-none delete-btn-dynamic';
                btn.innerHTML = '<i class="fas fa-times text-[10px]"></i>';
                btn.onclick = function(e) {
                    e.stopPropagation();
                    removeImage(btn, index);
                };
                slot.appendChild(btn);
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage(btn, index) {
    if(confirm('写真を削除しますか？')) {
        const preview = document.getElementById('preview_' + index);
        const guide = document.getElementById('guide_' + index);
        const input = document.getElementById('file_' + index);

        preview.src = '';
        preview.classList.add('hidden');
        guide.classList.remove('hidden');
        input.value = '';
        btn.remove();
    }
}
</script>
@endpush