@extends('layouts.app')

@section('title', '写真登録・編集')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/gallery.css') }}">
@endpush

@section('content')
<div class="contents inner p-4">
    <div class="title-area mb-6">
        <h2 class="serif-font text-xl text-white border-l-4 border-gold pl-3">写真登録・編集</h2>
    </div>
    
    {{-- おこじょガイド --}}
    <div class="flex flex-col items-center mb-8 bg-[#2d0b0b] p-4 rounded-2xl border border-gold/20">
        <img src="{{ asset('assets/images/guide/okojyo.png') }}" class="w-16 mb-2 animate-bounce-slow" alt="ガイド">
        <p class="text-sm text-gray-300 text-center leading-relaxed">
            最大5枚まで登録できるよ！<br>
            <span class="text-gold font-bold">長押しドラッグ</span>で並び替えができるよ。
        </p>
    </div>

    <section class="box_form">
        <form id="gallery-form" action="{{ route('shop.gallery.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <ul id="sortable-images2" class="col_list">
                @for($i=0; $i<5; $i++)
                    @php $imgSrc = $subImages[$i] ?? null; @endphp
                    <li data-index="{{ $i }}">
                        <label for="file_{{ $i }}" class="block w-full h-full cursor-pointer">
                            <input type="file" id="file_{{ $i }}" name="images[]" class="hidden" onchange="previewImage(this, 'preview_{{ $i }}')">
                            
                            @if($imgSrc)
                                <img id="preview_{{ $i }}" src="{{ $imgSrc }}" class="w-full h-full object-cover">
                                <button type="button" class="delete-btn" onclick="removeImage(this)">×</button>
                            @else
                                <img id="preview_{{ $i }}" class="w-full h-full object-cover hidden">
                                <div class="add-icon">
                                    <i class="fas fa-plus"></i>
                                    <span>UPLOAD</span>
                                </div>
                            @endif
                        </label>
                    </li>
                @endfor
            </ul>
            
            <div class="mt-10 pb-20">
                <button type="submit" class="btn-gold w-full py-4 shadow-xl">
                    この内容で保存する
                </button>
            </div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
// 1. 画像プレビュー機能
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(previewId);
            img.src = e.target.result;
            img.classList.remove('hidden');
            // プラスアイコンを隠す
            if(img.nextElementSibling) img.nextElementSibling.style.display = 'none';
            // 削除ボタンの生成
            if(!img.parentElement.querySelector('.delete-btn')){
                const btn = document.createElement('button');
                btn.className = 'delete-btn';
                btn.innerHTML = '×';
                btn.onclick = function(e){ e.preventDefault(); removeImage(this); };
                img.parentElement.appendChild(btn);
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// 2. 画像削除機能
function removeImage(btn) {
    if(confirm('この写真を削除しますか？')) {
        const label = btn.parentElement;
        const img = label.querySelector('img');
        const input = label.querySelector('input');
        const addIcon = label.querySelector('.add-icon');
        
        img.src = '';
        img.classList.add('hidden');
        input.value = '';
        if(addIcon) addIcon.style.display = 'flex';
        btn.remove();
    }
}

// 3. ドラッグ＆ドロップロジック（旧 photo.php 移植版）
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
            draggedItem.classList.add("dragging");
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
            draggedItem.classList.remove("dragging");
            draggedItem = null;
        }
        isDragging = false;
    };

    sortableList.addEventListener("touchend", endDrag);
    sortableList.addEventListener("touchcancel", endDrag);
});
</script>
@endpush