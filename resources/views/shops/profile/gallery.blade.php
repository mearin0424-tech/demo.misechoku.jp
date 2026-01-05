@extends('layouts.app')

@section('title', '写真登録・編集')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/gallery.css') }}">
@endpush

@section('content')
<div class="contents inner p-4 animate-fadeIn gallery-container">
    {{-- ヘッダーエリア --}}
    <div class="flex justify-between items-center mb-8">
        <div class="title-area">
            <h2 class="serif-font text-2xl gold-gradient tracking-tight">Media Library</h2>
            <p class="text-[10px] text-gray-500 uppercase tracking-[0.2em] mt-1">Photo Registration</p>
        </div>
        <button type="submit" form="gallery-form" class="text-gold text-sm font-semibold hover:opacity-70 transition-opacity">Done</button>
    </div>
    
    {{-- オコジョガイド：グラスモーフィズムデザイン --}}
    <div class="flex flex-col items-center mb-10 glass-panel p-5 rounded-2xl border-gold/10">
        <img src="{{ asset('assets/images/guide/guide-character.png') }}" class="w-16 mb-3 animate-bounce-slow" alt="ガイド">
        <p class="text-xs text-gray-300 text-center leading-relaxed font-light">
            最大5枚まで登録できるよ！<br>
            <span class="text-gold font-bold">長押しドラッグ</span>で並び替えができるよ。
        </p>
    </div>

    <section class="box_form">
        <form id="gallery-form" action="{{ route('shop.gallery.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @php
                $guides = [
                    ['label' => '外観・看板', 'icon' => 'fa-store', 'desc' => 'お店の顔'],
                    ['label' => '内装・ラウンジ', 'icon' => 'fa-couch', 'desc' => '雰囲気'],
                    ['label' => 'VIPルーム', 'icon' => 'fa-crown', 'desc' => '高級感'],
                    ['label' => 'キャスト集合', 'icon' => 'fa-users', 'desc' => '賑やかさ'],
                    ['label' => '自由な一枚', 'icon' => 'fa-image', 'desc' => 'お気に入りを'],
                ];
            @endphp

            <ul id="sortable-images2">
                @for($i=0; $i<5; $i++)
                    @php $imgSrc = $subImages[$i] ?? null; @endphp
                    <li data-index="{{ $i }}">
                        <label for="file_{{ $i }}" class="block w-full h-full">
                            <input type="file" id="file_{{ $i }}" name="images[]" class="hidden" onchange="previewImage(this, 'preview_{{ $i }}')">
                            
                            <div class="photo-slot">
                                <img id="preview_{{ $i }}" src="{{ $imgSrc }}" class="w-full h-full object-cover {{ $imgSrc ? '' : 'hidden' }}">
                                
                                {{-- 画像がない時のガイド表示 --}}
                                <div class="slot-guide {{ $imgSrc ? 'hidden' : '' }}">
                                    <i class="fas {{ $guides[$i]['icon'] }}"></i>
                                    <span class="guide-label">{{ $guides[$i]['label'] }}</span>
                                    <span class="guide-desc">{{ $guides[$i]['desc'] }}</span>
                                </div>
                                
                                {{-- プラスボタン --}}
                                <div class="plus-badge {{ $imgSrc ? 'hidden' : '' }}">
                                    <i class="fas fa-plus"></i>
                                </div>

                                {{-- 画像がある時の削除ボタン --}}
                                @if($imgSrc)
                                    <button type="button" class="delete-btn" onclick="removeImage(this)">×</button>
                                @endif
                            </div>
                        </label>
                    </li>
                @endfor
            </ul>
            
            <div class="mt-12 pb-20">
                <button type="submit" class="btn-gold-luxe">
                    この内容で保存する
                </button>
                <p class="text-center text-[10px] text-gray-600 uppercase tracking-widest mt-4">Safe & Secure Upload</p>
            </div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
/**
 * 1. 画像プレビュー機能
 */
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const slot = input.closest('.photo-slot');
            const img = document.getElementById(previewId);
            const guide = slot.querySelector('.slot-guide');
            const badge = slot.querySelector('.plus-badge');

            img.src = e.target.result;
            img.classList.remove('hidden');
            
            if(guide) guide.classList.add('hidden');
            if(badge) badge.classList.add('hidden');

            if(!slot.querySelector('.delete-btn')){
                const btn = document.createElement('button');
                btn.className = 'delete-btn';
                btn.innerHTML = '×';
                btn.type = 'button';
                btn.onclick = function(e){ 
                    e.preventDefault(); 
                    e.stopPropagation();
                    removeImage(this); 
                };
                slot.appendChild(btn);
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * 2. 画像削除機能
 */
function removeImage(btn) {
    if(confirm('この写真を削除しますか？')) {
        const slot = btn.closest('.photo-slot');
        const img = slot.querySelector('img');
        const input = slot.closest('li').querySelector('input[type="file"]');
        const guide = slot.querySelector('.slot-guide');
        const badge = slot.querySelector('.plus-badge');
        
        img.src = '';
        img.classList.add('hidden');
        input.value = '';
        
        if(guide) guide.classList.remove('hidden');
        if(badge) badge.classList.remove('hidden');
        
        btn.remove();
    }
}

/**
 * 3. ドラッグ＆ドロップロジック
 */
document.addEventListener("DOMContentLoaded", () => {
    const sortableList = document.getElementById("sortable-images2");
    let draggedItem = null;
    let longPressTimer;
    let isDragging = false;

    sortableList.addEventListener("touchstart", (e) => {
        const target = e.target.closest("li");
        if (!target || e.target.classList.contains('delete-btn')) return;

        draggedItem = target;
        longPressTimer = setTimeout(() => {
            isDragging = true;
            draggedItem.querySelector('.photo-slot').classList.add("dragging");
            if (window.navigator.vibrate) window.navigator.vibrate(50);
        }, 500); 
    }, { passive: true });

    sortableList.addEventListener("touchmove", (e) => {
        if (!isDragging || !draggedItem) return;
        e.preventDefault();

        const touch = e.touches[0];
        const overElement = document.elementFromPoint(touch.clientX, touch.clientY);
        const targetLi = overElement ? overElement.closest("li") : null;

        if (targetLi && targetLi !== draggedItem) {
            const rect = targetLi.getBoundingClientRect();
            const midpoint = rect.top + rect.height / 2;
            
            if (touch.clientY < midpoint) {
                sortableList.insertBefore(draggedItem, targetLi);
            } else {
                sortableList.insertBefore(draggedItem, targetLi.nextSibling);
            }
        }
    }, { passive: false });

    const endDrag = () => {
        clearTimeout(longPressTimer);
        if (draggedItem) {
            draggedItem.querySelector('.photo-slot').classList.remove("dragging");
            draggedItem = null;
        }
        isDragging = false;
    };

    sortableList.addEventListener("touchend", endDrag);
    sortableList.addEventListener("touchcancel", endDrag);
});
</script>
@endpush