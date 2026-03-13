@extends('layouts.app')

@section('title', 'マイページ')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
@endpush

@section('content')
<div class="mypage-page contents inner animate-fadeIn">
    <section class="mypage-area">
        {{-- ヒーロー：店舗名 --}}
        <h1 class="mypage-shop-name serif-font gold-gradient">{{ $shopData['shop_name'] }}</h1>

        {{-- アイコン＋ひとこと --}}
        <div class="mypage-hero">
            <div class="shop-icon-wrapper">
                <img src="{{ (isset($subImages[0]) ? $subImages[0]['url'] : null) ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display" alt="">
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

            {{-- メニュー --}}
            <div class="mypage-section mypage-quick-actions">
                <h2 class="mypage-actions-title">メニュー</h2>
                <a href="{{ route('shop.recruits.status') }}" class="btn-action-card job">
                    <span class="btn-action-icon-wrap"><i class="far fa-folder-open"></i></span>
                    <span class="btn-action-body">
                        <span class="btn-action-label">Recruit</span>
                        <span class="btn-action-text">求人の掲載</span>
                    </span>
                    <i class="fas fa-chevron-right btn-action-arrow"></i>
                </a>
                <a href="{{ route('shop.mypage.payment.index') }}" class="btn-action-card manage">
                    <span class="btn-action-icon-wrap"><i class="far fa-credit-card"></i></span>
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
                <p class="text-xs" style="color:#C9B8B8; margin-bottom:8px;">
                    営業許可証と風営許可証の<span style="color:var(--color-gold);">両方が運営に承認されるまで</span>、メッセージ後の面談日設定など一部機能はご利用いただけません。
                </p>
                <ul class="doc-list">
                    @foreach($documents as $doc)
                    <li class="doc-item">
                        <div class="doc-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="doc-info">
                            <span class="doc-name">{{ $doc['name'] }}</span>
                            @php $s = $doc['status']; @endphp
                            <span class="doc-status {{ $s === 'approved' ? 'done' : 'pending' }}" data-doc-key="{{ $doc['key'] }}">
                                @if($s === 'approved')
                                    承認済
                                @elseif($s === 'pending')
                                    提出済み（未承認）
                                @else
                                    未提出
                                @endif
                            </span>
                        </div>
                        <button type="button"
                                class="btn-action-small"
                                onclick="openDocumentUpload('{{ $doc['key'] }}')">
                            アップロード
                        </button>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Image Library（ドラッグで並び替え・タップで大表示・削除・空きタップで登録） --}}
            <div class="mypage-section gallery-edit-section">
                <div class="gallery-section-header">
                    <h2 class="section-title section-title-gold">Image Library</h2>
                    <p class="gallery-section-hint">ドラッグで並び替え（スマホは長押し）</p>
                </div>
                <ul class="responsive-gallery gallery-grid" id="gallery-list" data-sort-save-url="{{ route('shop.profile.images.order') }}" data-empty-image-url="{{ asset('assets/images/common/no-image.png') }}">
                    @for($i = 0; $i < 8; $i++)
                    @php $img = $subImages[$i] ?? null; @endphp
                    <li class="gallery-grid-item" data-slot-index="{{ $i }}">
                        <div class="photo-slot {{ $img ? 'has-img' : '' }}"
                             data-image-id="{{ $img['id'] ?? '' }}"
                             data-image-url="{{ $img['url'] ?? '' }}"
                             onclick="handleGallerySlotClick(event, this, {{ $i }})">
                            @if($img)
                                <img src="{{ $img['url'] }}" alt="" loading="lazy">
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

{{-- 画像大表示モーダル（削除ボタンで削除） --}}
<div id="image-preview-modal" class="mypage-modal-overlay gallery-preview-overlay" onclick="closeGalleryPreview(event)" role="dialog" aria-label="画像プレビュー">
    <div class="gallery-preview-inner" onclick="event.stopPropagation()">
        <img id="modal-img" src="" alt="" class="mypage-modal-preview-img">
        <div class="gallery-preview-actions">
            <button type="button" class="btn-action btn-action-secondary gallery-preview-btn-close" onclick="closeGalleryPreview()">閉じる</button>
            <button type="button" id="gallery-preview-delete-btn" class="btn-action gallery-preview-btn-delete" onclick="deleteGalleryImageFromModal(event)">削除</button>
        </div>
    </div>
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
<input type="file" id="document-upload" class="sr-only" accept=".pdf,image/*">
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="{{ asset('assets/js/gallery-sortable.js') }}"></script>
<script>
var _galleryPreviewImageId = null;
var _galleryPreviewLi = null;
var _galleryUploadSlotIndex = null;
var _currentDocumentKey = null;

function handleGallerySlotClick(ev, slotEl, slotIndex) {
    var li = slotEl.closest('li');
    var hasImg = slotEl.classList.contains('has-img');
    var imageId = slotEl.getAttribute('data-image-id');
    var imageUrl = slotEl.getAttribute('data-image-url');
    if (hasImg && imageUrl) {
        ev.preventDefault();
        ev.stopPropagation();
        _galleryPreviewImageId = imageId;
        _galleryPreviewLi = li;
        document.getElementById('modal-img').src = imageUrl;
        var deleteBtn = document.getElementById('gallery-preview-delete-btn');
        if (deleteBtn) {
            var canDelete = !!imageId;
            deleteBtn.style.display = canDelete ? '' : 'none';
        }
        document.getElementById('image-preview-modal').style.display = 'flex';
    } else {
        _galleryUploadSlotIndex = slotIndex;
        document.getElementById('gallery-upload').click();
    }
}

function closeGalleryPreview(ev) {
    if (ev && ev.target !== ev.currentTarget) return;
    document.getElementById('image-preview-modal').style.display = 'none';
    var deleteBtn = document.getElementById('gallery-preview-delete-btn');
    if (deleteBtn) {
        deleteBtn.style.display = '';
    }
    _galleryPreviewImageId = null;
    _galleryPreviewLi = null;
}

function deleteGalleryImageFromModal(ev) {
    ev.preventDefault();
    ev.stopPropagation();
    if (!_galleryPreviewImageId || !_galleryPreviewLi) return;
    if (!confirm('この画像を削除しますか？')) return;
    var id = _galleryPreviewImageId;
    var li = _galleryPreviewLi;
    fetch('{{ route("shop.profile.image.delete", ["id" => "__ID__"]) }}'.replace('__ID__', id), {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(function(r) { return r.json(); }).then(function(res) {
        if (res.success) {
            var slot = li.querySelector('.photo-slot');
            slot.classList.remove('has-img');
            slot.removeAttribute('data-image-id');
            slot.removeAttribute('data-image-url');
            slot.innerHTML = '<span class="photo-slot-empty"><i class="fas fa-image"></i></span>';
            var galleryList = document.getElementById('gallery-list');
            if (window.refreshGalleryMainState && galleryList) {
                window.refreshGalleryMainState(galleryList);
            }
            if (window.persistGalleryOrder && galleryList) {
                window.persistGalleryOrder(galleryList);
            }
            closeGalleryPreview();
        } else {
            alert(res.message || '削除に失敗しました');
        }
    }).catch(function() { alert('削除に失敗しました'); });
}

(function() {
    document.getElementById('gallery-upload').addEventListener('change', function() {
        var file = this.files && this.files[0];
        if (!file) return;
        var slotIndex = _galleryUploadSlotIndex;
        if (slotIndex == null) {
            var firstEmpty = document.querySelector('#gallery-list .gallery-grid-item .photo-slot:not(.has-img)');
            slotIndex = firstEmpty ? Array.prototype.indexOf.call(document.querySelectorAll('#gallery-list .gallery-grid-item'), firstEmpty.closest('.gallery-grid-item')) : 0;
        }
        var formData = new FormData();
        formData.append('image', file);
        formData.append('slot_index', slotIndex);
        formData.append('_token', '{{ csrf_token() }}');
        fetch('{{ route("shop.profile.upload.image") }}', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success && res.path) {
                    var list = document.getElementById('gallery-list');
                    var items = list.querySelectorAll('.gallery-grid-item');
                    var li = items[slotIndex];
                    if (li) {
                        var slot = li.querySelector('.photo-slot');
                        slot.classList.add('has-img');
                        slot.setAttribute('data-image-id', res.id);
                        slot.setAttribute('data-image-url', res.path);
                        slot.innerHTML = '<img src="' + res.path + '" alt="" loading="lazy">' + (slotIndex === 0 ? '<span class="photo-slot-badge">MAIN</span>' : '');
                        if (window.refreshGalleryMainState) {
                            window.refreshGalleryMainState(list);
                        }
                        if (window.persistGalleryOrder) {
                            window.persistGalleryOrder(list);
                        }
                    }
                }
            })
            .catch(function() { alert('アップロードに失敗しました'); });
        this.value = '';
        _galleryUploadSlotIndex = null;
    });
})();

function openWordEdit() {
    document.getElementById('modal-word').style.display = 'flex';
    document.getElementById('word-input').value = document.getElementById('display-word').innerText;
}
function closeWordEdit() {
    document.getElementById('modal-word').style.display = 'none';
}
function openProfileEdit() {
    location.href = "{{ route('shop.profile.store.edit') }}";
}
function saveWord() {
    var val = document.getElementById('word-input').value.trim();
    document.getElementById('display-word').innerText = val || 'ひとことを設定しましょう';
    closeWordEdit();
    // TODO: API で保存する場合はここで送信
}

function openDocumentUpload(docKey) {
    _currentDocumentKey = docKey;
    document.getElementById('document-upload').click();
}

(function() {
    var docInput = document.getElementById('document-upload');
    if (!docInput) return;
    docInput.addEventListener('change', function() {
        var file = this.files && this.files[0];
        if (!file || !_currentDocumentKey) {
            this.value = '';
            return;
        }
        var formData = new FormData();
        formData.append('file', file);
        formData.append('type', _currentDocumentKey);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("shop.mypage.documents.upload") }}', {
            method: 'POST',
            body: formData
        }).then(function(r) { return r.json(); })
          .then(function(res) {
              if (res && res.success) {
                  var statusEls = document.querySelectorAll('.doc-status[data-doc-key="' + _currentDocumentKey + '"]');
                  statusEls.forEach(function(el) {
                      el.classList.remove('pending');
                      el.classList.add('pending'); // アップロード直後は「提出済み（未承認）」扱い
                      el.textContent = '提出済み（未承認）';
                  });
                  alert('書類をアップロードしました。運営による確認・承認をお待ちください。');
              } else {
                  alert(res && res.message ? res.message : 'アップロードに失敗しました。');
              }
          })
          .catch(function() {
              alert('アップロードに失敗しました。');
          })
          .finally(function() {
              docInput.value = '';
              _currentDocumentKey = null;
          });
    });
})();
</script>
@endpush
