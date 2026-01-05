@extends('layouts.app')

@section('title', 'マイページ')

@push('styles')
{{-- 既存のCSSとLuxeデザインの共存 --}}
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/gallery.css') }}">
<style>
    /* 追加の個別修正スタイル */
    /* 1. ひとこと編集ボタン：スタイリッシュに */
    .btn-word-edit {
        background: none !important;
        border: none !important;
        color: var(--gold) !important;
        transition: opacity 0.2s;
        padding: 5px;
    }
    .btn-word-edit:hover { opacity: 0.7; }

    /* 2. レビュー：下線なし・遷移先変更 */
    .shop-review-link {
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* 4. ギャラリー：レスポンシブグリッド */
    .responsive-gallery {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* デフォルト2枚 */
        gap: 10px;
    }
    @media (min-width: 480px) { .responsive-gallery { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 768px) { .responsive-gallery { grid-template-columns: repeat(4, 1fr); } }

    /* プレビュー用簡易モーダル */
    #image-preview-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0,0,0,0.9);
        justify-content: center;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="contents inner animate-fadeIn">
    <section class="mypage-area">
        <h1 class="mypage-shop-name serif-font gold-gradient">{{ $shopData['shop_name'] }}</h1>

        <div class="shop-header-top">
            <div class="shop-icon-wrapper">
                <img src="{{ $subImages[0] ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display">
                <button class="btn-add-icon" onclick="document.getElementById('gallery-upload').click()"><i class="fas fa-plus"></i></button>
            </div>
            <div class="shop-word-bubble glass-panel" onclick="openWordEdit()">
                <p id="display-word">{{ $shopData['word'] }}</p>
                {{-- ペンアイコンをスタイリッシュに配置 --}}
                <button class="btn-word-edit" style="position:absolute; right:10px; bottom:5px;">
                    <i class="fas fa-pen"></i>
                </button>
            </div>
        </div>

        <div class="review-area-top" style="padding: 0 15px; margin-bottom: 15px;">
            {{-- 遷移先を reviews.blade.php (review.index) に変更 --}}
            <a href="{{ route('shop.mypage.review.index') }}" class="shop-review-link">
                <span class="stars text-gold"><i class="fas fa-star"></i> {{ $shopData['review_avg'] }}</span>
                <span class="count" style="color:#888;">({{ $shopData['review_count'] }}件)</span>
                <i class="fas fa-chevron-right" style="color:#444; font-size: 0.8rem;"></i>
            </a>
        </div>

        <div class="detail-box" style="padding: 0 15px;">
            <div class="profile-info-section">
                <div class="section-title-row">
                    <h3 style="font-size:1rem; color:#aaa; margin:0;">プロフィール情報</h3>
                    <button class="btn-outline-gold" onclick="openProfileEdit()">編集</button>
                </div>
                <p class="shop-access-text">
                    <i class="fas fa-map-marker-alt"></i> {{ $shopData['pref'] }}{{ $shopData['city'] }}{{ $shopData['addr1'] }}
                </p>
                <div class="shop-overview-text" id="display-overview">
                    {!! nl2br(e($shopData['overview'])) !!}
                </div>
            </div>

            {{-- 3. ボタンデザイン：元のデザイン(btn-action)に戻す --}}
            <div class="mypage-actions">
                <a href="{{ route('shop.recruits.status') }}" class="btn-action job">
                    <i class="fas fa-edit"></i> 求人情報の確認・編集
                </a>
                <a href="{{ route('shop.mypage.payment.index') }}" class="btn-action manage">
                    <i class="fas fa-tasks"></i> 採用・請求管理
                </a>
            </div>

            {{-- 3. 書類管理：元のデザインに戻す --}}
            <div class="document-section">
                <h3 class="section-title-original">書類管理</h3>
                <ul class="doc-list">
                    @foreach($documents as $doc)
                    <li class="doc-item">
                        <div class="doc-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="doc-info">
                            <span class="doc-name">{{ $doc['name'] }}</span>
                            <span class="doc-status {{ $doc['status'] == 'submitted' ? 'done' : 'pending' }}">
                                {{ $doc['status'] == 'submitted' ? '提出済' : '未提出' }}
                            </span>
                        </div>
                        <i class="fas fa-chevron-right arrow"></i>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- ギャラリー：閲覧専用（編集画面への入り口） --}}
            <div class="gallery-edit-section animate-fadeIn" style="margin-top:35px;">
                <div class="flex justify-between items-center mb-4 px-2">
                    <h3 class="text-xs uppercase tracking-widest text-gold font-bold">Media Gallery</h3>
                    <a href="{{ route('shop.profile.gallery.edit') }}" class="text-[10px] text-gray-400 underline">編集する</a>
                </div>
                
                <ul class="responsive-gallery" id="gallery-list" onclick="location.href='{{ route('shop.profile.gallery.edit') }}'" style="cursor:pointer;">
                    @for($i=0; $i<8; $i++) {{-- 8枚表示に変更 --}}
                        <li>
                            <div class="photo-slot {{ isset($subImages[$i]) ? 'has-img' : '' }}">
                                @if(isset($subImages[$i]))
                                    <img src="{{ $subImages[$i] }}" class="w-full h-full object-cover">
                                    @if($i === 0)
                                        <span class="absolute top-0 left-0 bg-gold text-black text-[8px] px-2 py-0.5 font-bold rounded-br-lg">MAIN</span>
                                    @endif
                                @else
                                    {{-- 空スロットはシンプルに --}}
                                    <div class="flex items-center justify-center opacity-20">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endfor
                </ul>
            </div>
        </div>
    </section>
</div>

{{-- 簡易プレビューモーダル --}}
<div id="image-preview-modal" onclick="this.style.display='none'">
    <img id="modal-img" src="" style="max-width: 90%; max-height: 90%; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5);">
</div>

{{-- 編集モーダル --}}
<div id="modal-word" class="modal-profile-custom" style="display:none; justify-content:center; align-items:center; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:3000;">
    <div class="glass-panel" style="width:90%; max-width:400px; padding:20px; border-radius:15px;">
        <h3 class="serif-font text-gold">ひとこと編集</h3>
        <textarea id="word-input" rows="3" style="width:100%; background:#111; color:#fff; border:1px solid #444; border-radius:8px; padding:10px; margin:15px 0;"></textarea>
        <div style="display:flex; gap:10px;">
            <button class="btn-action manage" onclick="closeWordEdit()" style="flex:1;">戻る</button>
            <button class="btn-action job" onclick="saveWord()" style="flex:1;">保存</button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" style="display:none;" accept="image/*">
@endsection

@push('scripts')
<script>
    // プレビュー表示
    function previewFullImage(src) {
        document.getElementById('modal-img').src = src;
        document.getElementById('image-preview-modal').style.display = 'flex';
    }

    // 簡易削除（見た目上の削除）
    function removeGalleryItem(btn) {
        if(confirm('この写真をマイページから非表示にしますか？')) {
            btn.closest('li').remove();
            // 実際にはAPI通信などでサーバー側も更新する必要があります
        }
    }

    function openWordEdit() {
        document.getElementById('modal-word').style.display = 'flex';
        document.getElementById('word-input').value = document.getElementById('display-word').innerText;
    }
    function closeWordEdit() { document.getElementById('modal-word').style.display = 'none'; }
    function openProfileEdit() { location.href = "{{ route('shop.profile.edit') }}"; }
</script>
@endpush