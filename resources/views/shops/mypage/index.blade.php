@extends('layouts.app')

@section('title', 'マイページ')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
{{-- ギャラリーのデザインとアニメーションを共有 --}}
<link rel="stylesheet" href="{{ asset('assets/css/gallery.css') }}">
@endpush

@section('content')
<div class="contents inner animate-fadeIn">
    <section class="mypage-area p-4">
        {{-- ショップ名：ゴールドグラデーション --}}
        <h1 class="mypage-shop-name serif-font gold-gradient text-3xl mb-6">{{ $shopData['shop_name'] }}</h1>

        {{-- ヘッダー：アイコン ＋ 吹き出し --}}
        <div class="shop-header-top flex items-center mb-8">
            <div class="shop-icon-wrapper relative w-24 h-24 mr-4 flex-shrink-0">
                <img src="{{ $subImages[0] ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main w-full h-full rounded-full border-2 border-gold object-cover" id="main-icon-display">
                <button class="btn-add-icon absolute bottom-0 right-0 w-8 h-8 bg-gold rounded-full flex items-center justify-center text-black shadow-lg" onclick="document.getElementById('gallery-upload').click()">
                    <i class="fas fa-plus text-xs"></i>
                </button>
            </div>
            <div class="shop-word-bubble flex-1 glass-panel p-4 rounded-2xl relative min-h-[70px] flex items-center cursor-pointer" onclick="openWordEdit()">
                <p id="display-word" class="text-sm text-gray-200 leading-relaxed">{{ $shopData['word'] }}</p>
                <button class="btn-word-edit absolute bottom-2 right-3 text-gold opacity-50"><i class="fas fa-pen text-xs"></i></button>
            </div>
        </div>

        {{-- レビュー概要 --}}
        <div class="review-area-top px-2 mb-8">
            <a href="{{ route('pages.support.column') }}" class="flex items-center gap-2 text-gold no-underline">
                <div class="flex text-xs">
                    @for($i=1; $i<=5; $i++)
                        <i class="{{ $i <= $shopData['review_avg'] ? 'fas' : 'far' }} fa-star"></i>
                    @endfor
                </div>
                <span class="text-sm font-bold">{{ number_format($shopData['review_avg'], 1) }}</span>
                <span class="text-xs text-gray-500">({{ $shopData['review_count'] }}件のレビュー)</span>
                <i class="fas fa-chevron-right text-[10px] ml-auto opacity-30"></i>
            </a>
        </div>

        <div class="detail-box space-y-8">
            {{-- プロフィール情報セクション --}}
            <div class="profile-info-section glass-panel p-5 rounded-2xl">
                <div class="flex justify-between items-center mb-4 border-b border-white/5 pb-2">
                    <h3 class="text-xs uppercase tracking-widest text-gold font-bold">Profile Info</h3>
                    <button class="text-[10px] text-gray-400 border border-white/10 px-3 py-1 rounded-full" onclick="openProfileEdit()">編集</button>
                </div>
                <p class="text-xs text-gray-400 mb-3 flex items-center gap-2">
                    <i class="fas fa-map-marker-alt text-gold"></i> {{ $shopData['pref'] }}{{ $shopData['city'] }}{{ $shopData['addr1'] }}
                </p>
                <div class="shop-overview-text text-sm text-gray-300 leading-relaxed" id="display-overview">
                    {!! nl2br(e($shopData['overview'])) !!}
                </div>
            </div>

            {{-- 管理アクションボタン --}}
            <div class="mypage-actions grid grid-cols-1 gap-3">
                <a href="{{ route('shop.recruits.status') }}" class="btn-gold-luxe flex items-center justify-center gap-2 text-decoration-none">
                    <i class="fas fa-edit text-xs"></i> 求人情報の確認・編集
                </a>
                <a href="{{ route('shop.mypage.payment.index') }}" class="glass-panel py-4 rounded-xl flex items-center justify-center gap-2 text-white font-bold no-underline border-white/10 hover:bg-white/5 transition-all">
                    <i class="fas fa-tasks text-gold text-xs"></i> 採用・請求管理
                </a>
            </div>

            {{-- 書類管理 --}}
            <div class="document-section">
                <h3 class="text-xs uppercase tracking-widest text-gray-500 font-bold mb-4 px-2">Documents</h3>
                <div class="space-y-2">
                    @foreach($documents as $doc)
                    <div class="doc-item glass-panel p-4 rounded-xl flex items-center gap-4">
                        <i class="fas fa-file-alt text-gray-500"></i>
                        <div class="flex-1">
                            <span class="block font-bold text-sm">{{ $doc['name'] }}</span>
                            <span class="text-[10px] {{ $doc['status'] == 'submitted' ? 'text-green-500' : 'text-accent' }}">
                                {{ $doc['status'] == 'submitted' ? '提出済' : '未提出' }}
                            </span>
                        </div>
                        <i class="fas fa-chevron-right text-xs opacity-20"></i>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ギャラリー編集：フォトスロット形式 --}}
            <div class="gallery-edit-section">
                <div class="flex justify-between items-center mb-4 px-2">
                    <h3 class="text-xs uppercase tracking-widest text-gold font-bold">Media Gallery</h3>
                    <a href="{{ route('shop.profile.gallery.edit') }}" class="text-[10px] text-gray-400 underline">管理画面へ</a>
                </div>
                
                <ul class="grid grid-cols-3 gap-3 list-none p-0" id="gallery-list">
                    @for($i=0; $i<6; $i++)
                        <li class="relative">
                            @if(isset($subImages[$i]))
                                <div class="photo-slot has-img h-full" draggable="true">
                                    <img src="{{ $subImages[$i] }}" class="w-full h-full object-cover">
                                    @if($i === 0)
                                        <span class="absolute top-0 left-0 bg-gold text-black text-[8px] px-2 py-0.5 font-bold rounded-br-lg">MAIN</span>
                                    @endif
                                    <button class="delete-btn" onclick="event.preventDefault(); alert('管理画面で削除してください')">×</button>
                                </div>
                            @else
                                <div class="photo-slot h-full" onclick="location.href='{{ route('shop.profile.gallery.edit') }}'">
                                    <div class="plus-badge">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                </div>
                            @endif
                        </li>
                    @endfor
                </ul>
            </div>
        </div>
    </section>
</div>

{{-- 編集モーダル：グラスモーフィズムデザイン --}}
<div id="modal-word" class="modal-profile-custom fixed inset-0 z-[3000] hidden bg-black/90 flex justify-center align-items-center p-6 backdrop-blur-sm">
    <div class="glass-panel w-full max-w-sm p-6 rounded-2xl border-white/10 shadow-2xl">
        <h3 class="serif-font text-gold text-xl mb-4">ひとこと編集</h3>
        <textarea id="word-input" rows="3" class="w-full bg-black/40 text-white border border-white/10 rounded-xl p-4 text-sm focus:outline-none focus:border-gold transition-colors"></textarea>
        <div class="grid grid-cols-2 gap-3 mt-6">
            <button class="py-3 rounded-xl border border-white/10 text-gray-400 font-bold" onclick="closeWordEdit()">戻る</button>
            <button class="btn-gold-luxe py-3" onclick="saveWord()">保存</button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" style="display:none;" accept="image/*">
@endsection

@push('scripts')
<script>
    function openWordEdit() {
        document.getElementById('modal-word').style.display = 'flex';
        document.getElementById('word-input').value = document.getElementById('display-word').innerText;
    }
    function closeWordEdit() { document.getElementById('modal-word').style.display = 'none'; }
    
    function openProfileEdit() { 
        location.href = "{{ route('shop.profile.edit') }}"; 
    }
</script>
@endpush