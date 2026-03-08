@extends('layouts.app')

@section('title', 'マイページ')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/gallery.css') }}">
@endpush

@section('content')
<div class="mypage-page contents inner animate-fadeIn">
    <section class="mypage-area">
        {{-- ヒーロー：店舗名 --}}
        <h1 class="mypage-shop-name serif-font gold-gradient">{{ $shopData['shop_name'] }}</h1>

        {{-- アイコン＋ひとこと --}}
        <div class="mypage-hero">
            <div class="shop-icon-wrapper">
                <img src="{{ $subImages[0] ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display" alt="">
                <button type="button" class="btn-add-icon" onclick="document.getElementById('gallery-upload').click()" aria-label="写真を追加">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="shop-word-bubble glass-panel" onclick="openWordEdit()" role="button" tabindex="0">
                <p id="display-word" class="shop-word-text">{{ $shopData['word'] }}</p>
                <button type="button" class="btn-word-edit" aria-label="ひとことを編集">
                    <i class="fas fa-pen"></i>
                </button>
            </div>
        </div>

        {{-- レビューカード --}}
        <a href="{{ route('shop.mypage.review.index') }}" class="mypage-review-card shop-review-link">
            <span class="review-stars"><i class="fas fa-star"></i> {{ $shopData['review_avg'] }}</span>
            <span class="review-count">({{ $shopData['review_count'] }}件)</span>
            <i class="fas fa-chevron-right review-arrow"></i>
        </a>

        <div class="mypage-detail-box">
            {{-- プロフィール情報 --}}
            <div class="mypage-section profile-info-section">
                <div class="section-title-row">
                    <h2 class="section-title">プロフィール情報</h2>
                    <button type="button" class="btn-outline-gold" onclick="openProfileEdit()">編集</button>
                </div>
                <p class="shop-access-text">
                    <i class="fas fa-map-marker-alt"></i> {{ $shopData['pref'] }}{{ $shopData['city'] }}{{ $shopData['addr1'] }}
                </p>
                <div class="shop-overview-text" id="display-overview">
                    {!! nl2br(e($shopData['overview'])) !!}
                </div>
            </div>

            {{-- クイックアクション --}}
            <div class="mypage-section mypage-quick-actions">
                <h2 class="mypage-actions-title">クイックアクション</h2>
                <a href="{{ route('shop.recruits.status') }}" class="btn-action-card job">
                    <span class="btn-action-icon-wrap"><i class="fas fa-briefcase"></i></span>
                    <span class="btn-action-body">
                        <span class="btn-action-label">Recruit Status</span>
                        <span class="btn-action-text">求人情報の確認・編集</span>
                    </span>
                    <i class="fas fa-chevron-right btn-action-arrow"></i>
                </a>
                <a href="{{ route('shop.mypage.payment.index') }}" class="btn-action-card manage">
                    <span class="btn-action-icon-wrap"><i class="fas fa-file-invoice-dollar"></i></span>
                    <span class="btn-action-body">
                        <span class="btn-action-label">MANAGEMENT</span>
                        <span class="btn-action-text">採用・請求管理</span>
                    </span>
                    <i class="fas fa-chevron-right btn-action-arrow"></i>
                </a>
            </div>

            {{-- 書類管理 --}}
            <div class="mypage-section document-section">
                <h2 class="section-title section-title-gold">書類管理</h2>
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
                        <i class="fas fa-chevron-right doc-arrow"></i>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Image Library --}}
            <div class="mypage-section gallery-edit-section">
                <div class="gallery-section-header">
                    <h2 class="section-title section-title-gold">Image Library</h2>
                    <a href="{{ route('shop.profile.gallery.edit') }}" class="gallery-edit-link">編集する</a>
                </div>
                <ul class="responsive-gallery gallery-grid" id="gallery-list" onclick="location.href='{{ route('shop.profile.gallery.edit') }}';">
                    @for($i = 0; $i < 8; $i++)
                    <li class="gallery-grid-item">
                        <div class="photo-slot {{ isset($subImages[$i]) ? 'has-img' : '' }}">
                            @if(isset($subImages[$i]))
                                <img src="{{ $subImages[$i] }}" alt="" loading="lazy">
                                @if($i === 0)
                                    <span class="photo-slot-badge">MAIN</span>
                                @endif
                            @else
                                <span class="photo-slot-empty"><i class="fas fa-image"></i></span>
                            @endif
                        </div>
                    </li>
                    @endfor
                </ul>
            </div>
        </div>
    </section>
</div>

{{-- 画像プレビューモーダル --}}
<div id="image-preview-modal" class="mypage-modal-overlay" onclick="this.style.display='none'" role="button" tabindex="0" aria-label="閉じる">
    <img id="modal-img" src="" alt="" class="mypage-modal-preview-img">
</div>

{{-- ひとこと編集モーダル --}}
<div id="modal-word" class="mypage-modal-overlay modal-word-edit" style="display:none;">
    <div class="mypage-modal-panel glass-panel">
        <h3 class="mypage-modal-title serif-font">ひとこと編集</h3>
        <textarea id="word-input" rows="3" class="mypage-modal-textarea" placeholder="新人大歓迎！"></textarea>
        <div class="mypage-modal-actions">
            <button type="button" class="btn-action btn-action-secondary" onclick="closeWordEdit()">戻る</button>
            <button type="button" class="btn-action btn-action-primary" onclick="saveWord()">保存</button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" class="sr-only" accept="image/*">
@endsection

@push('scripts')
<script>
function previewFullImage(src) {
    document.getElementById('modal-img').src = src;
    document.getElementById('image-preview-modal').style.display = 'flex';
}
function removeGalleryItem(btn) {
    if (confirm('この写真をマイページから非表示にしますか？')) {
        btn.closest('li').remove();
    }
}
function openWordEdit() {
    document.getElementById('modal-word').style.display = 'flex';
    document.getElementById('word-input').value = document.getElementById('display-word').innerText;
}
function closeWordEdit() {
    document.getElementById('modal-word').style.display = 'none';
}
function openProfileEdit() {
    location.href = "{{ route('shop.profile.edit') }}";
}
function saveWord() {
    var val = document.getElementById('word-input').value.trim();
    document.getElementById('display-word').innerText = val || 'ひとことを設定しましょう';
    closeWordEdit();
    // TODO: API で保存する場合はここで送信
}
</script>
@endpush
