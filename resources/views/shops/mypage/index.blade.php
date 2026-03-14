@extends('layouts.app')

@section('title', 'マイページ')
@php
    $showLicenseGuide = collect($documents ?? [])->contains(fn ($doc) => ($doc['status'] ?? null) === 'not_submitted');
@endphp
@section('guide_message')
    @if($showLicenseGuide)
        営業許可証または風営許可証が、まだそろっていないようです。両方がそろいますと、面談日設定などの機能もご利用いただけますので、先にこちらをご準備ください。
    @endif
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<style>
    .document-upload-list {
        display: grid;
        gap: 10px;
    }

    .document-upload-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid rgba(212, 175, 55, 0.12);
        background: rgba(255,255,255,0.025);
    }

    .document-upload-main {
        min-width: 0;
        flex: 1;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .document-upload-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: #fff8ea;
        white-space: nowrap;
    }

    .document-upload-meta {
        font-size: 0.73rem;
        line-height: 1.5;
        color: #bdaaaa;
        white-space: nowrap;
    }

    .document-status-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 92px;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }

    .document-status-chip.is-approved {
        color: #dcfce7;
        background: rgba(34, 197, 94, 0.14);
        border: 1px solid rgba(34, 197, 94, 0.24);
    }

    .document-status-chip.is-rejected {
        color: #fee2e2;
        background: rgba(248, 113, 113, 0.12);
        border: 1px solid rgba(248, 113, 113, 0.24);
    }

    .document-status-chip.is-pending,
    .document-status-chip.is-not-submitted {
        color: #f6e7af;
        background: rgba(212, 175, 55, 0.12);
        border: 1px solid rgba(212, 175, 55, 0.22);
    }

    .document-upload-notice {
        margin: 0 0 10px;
        font-size: 0.76rem;
        line-height: 1.7;
        color: #cdbcbc;
    }

    .document-upload-link {
        color: #f4d77b;
        font-size: 0.76rem;
        text-decoration: none;
        white-space: nowrap;
    }

    .document-upload-link:hover {
        opacity: 0.85;
    }

    .document-upload-form {
        display: contents;
    }

    .document-upload-actions {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .document-upload-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(212, 175, 55, 0.24);
        background: rgba(212, 175, 55, 0.10);
        color: #f8e7b0;
        font-size: 0.75rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.2s, border-color 0.2s, transform 0.15s;
    }

    .document-upload-trigger:hover {
        background: rgba(212, 175, 55, 0.16);
        border-color: rgba(212, 175, 55, 0.34);
    }

    .document-upload-input {
        display: none;
    }

    .document-upload-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.10);
        background: rgba(255,255,255,0.04);
        color: #f2e7c4;
        font-size: 0.75rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: background 0.2s, border-color 0.2s, transform 0.15s;
    }

    .document-upload-link:hover {
        background: rgba(255,255,255,0.08);
        border-color: rgba(212, 175, 55, 0.24);
        color: #f8e7b0;
    }

    .document-upload-error {
        width: 100%;
        margin-top: 8px;
        font-size: 0.75rem;
        color: #ffcdcd;
    }

    @media (max-width: 640px) {
        .document-upload-card {
            gap: 10px;
        }

        .document-upload-actions {
            margin-left: auto;
        }

        .document-upload-main {
            gap: 6px;
        }

        .document-upload-meta {
            width: 100%;
            white-space: normal;
        }
    }
</style>
@endpush

@section('content')
<div class="mypage-page contents inner animate-fadeIn">
    <section class="mypage-area">
        {{-- ヒーロー：店舗名 --}}
        <h1 class="mypage-shop-name serif-font gold-gradient">{{ $shopData['shop_name'] }}</h1>

        {{-- アイコン＋アピール --}}
        <div class="mypage-hero">
            <div class="shop-icon-wrapper">
                <img src="{{ (isset($subImages[0]) ? $subImages[0]['url'] : null) ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display" alt="">
                <button type="button" class="btn-add-icon" onclick="document.getElementById('gallery-upload').click()" aria-label="写真を追加">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="shop-word-bubble glass-panel" onclick="openWordEdit()" role="button" tabindex="0">
                <span class="shop-word-bubble-label">Appeal</span>
                <span class="shop-word-bubble-note">タイムラインに表示される店舗アピールです</span>
                <p id="display-word" class="shop-word-text">{{ $shopData['word'] }}</p>
                <div class="shop-word-bubble-footer">
                    <span class="shop-word-bubble-hint">タップしてすぐ編集</span>
                    <span id="display-word-updated" class="shop-word-bubble-updated">最終更新 {{ $shopData['appeal_updated_at'] ?? '未設定' }}</span>
                </div>
                <button type="button" class="btn-word-edit" aria-label="アピールを編集">
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
                <p class="document-upload-notice">
                    提出状況を確認しながら、そのまま差し替えできます。
                </p>
                <div class="document-upload-list">
                    @foreach($documents as $doc)
                        @php
                            $s = $doc['status'];
                            $record = $doc['record'] ?? null;
                            $statusLabel = [
                                'approved' => '承認済',
                                'pending' => '提出済み（未承認）',
                                'rejected' => '不備・却下',
                                'not_submitted' => '未提出',
                            ][$s] ?? '未提出';
                        @endphp
                        <div class="document-upload-card">
                            <div class="document-upload-main">
                                <span class="document-upload-name">{{ $doc['name'] }}</span>
                                <span class="document-status-chip is-{{ str_replace('_', '-', $s) }}" data-doc-key="{{ $doc['key'] }}">
                                    {{ $statusLabel }}
                                </span>
                                <span class="document-upload-meta">
                                    @if($record && !empty($record['updated_at_label']))
                                        最終更新 {{ $record['updated_at_label'] }}
                                    @else
                                        まだ提出されていません
                                    @endif
                                </span>
                                @if($record && !empty($record['file_url']))
                                    <a href="{{ $record['file_url'] }}" target="_blank" rel="noopener" class="document-upload-link">確認</a>
                                @endif
                            </div>
                            <form class="shop-document-form document-upload-form" data-doc-key="{{ $doc['key'] }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="type" value="{{ $doc['key'] }}">
                                <div class="document-upload-actions">
                                    @if($record && !empty($record['file_url']))
                                        <a href="{{ $record['file_url'] }}" target="_blank" rel="noopener" class="document-upload-link">確認</a>
                                    @endif
                                    <label class="document-upload-trigger">
                                        更新
                                        <input type="file" name="file" class="document-upload-input" accept=".pdf,image/*" required data-auto-upload-input>
                                    </label>
                                </div>
                            </form>
                        </div>
                        @if($record && !empty($record['ng_reason']))
                            <p class="document-upload-error">差し戻し理由: {{ $record['ng_reason'] }}</p>
                        @endif
                    @endforeach
                </div>
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

{{-- アピール編集モーダル --}}
<div id="modal-word" class="mypage-modal-overlay modal-word-edit" style="display:none;">
    <div class="mypage-modal-panel glass-panel">
        <h3 class="mypage-modal-title serif-font">タイムライン用アピールを編集</h3>
        <textarea id="word-input" rows="3" class="mypage-modal-textarea" placeholder="新人大歓迎！働きやすさもお任せください。"></textarea>
        <div class="mypage-modal-actions">
            <button type="button" class="btn-action btn-action-secondary" onclick="closeWordEdit()">戻る</button>
            <button type="button" class="btn-action btn-action-primary" onclick="saveWord()">保存</button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" class="sr-only" accept="image/*">
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="{{ asset('assets/js/gallery-sortable.js') }}"></script>
<script>
var _galleryPreviewImageId = null;
var _galleryPreviewLi = null;
var _galleryUploadSlotIndex = null;

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
    document.getElementById('word-input').value = document.getElementById('display-word').innerText.trim();
}
function closeWordEdit() {
    document.getElementById('modal-word').style.display = 'none';
}
function openProfileEdit() {
    location.href = "{{ route('shop.profile.store.edit') }}";
}
function saveWord() {
    var val = document.getElementById('word-input').value.trim();
    var fallback = 'タイムラインに載るアピールを設定しましょう';
    document.getElementById('display-word').innerText = val || fallback;
    var updated = document.getElementById('display-word-updated');
    if (updated) {
        var now = new Date();
        var date = now.getFullYear() + '/' + String(now.getMonth() + 1).padStart(2, '0') + '/' + String(now.getDate()).padStart(2, '0');
        var time = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        updated.innerText = '最終更新 ' + date + ' ' + time;
    }
    closeWordEdit();
    // TODO: API で保存する場合はここで送信
}

(function() {
    var forms = document.querySelectorAll('.shop-document-form');
    forms.forEach(function(form) {
        var fileInput = form.querySelector('[data-auto-upload-input]');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (!fileInput.files || !fileInput.files.length) return;
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            });
        }
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(form);
            fetch('{{ route("shop.mypage.documents.upload") }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            }).then(function(r) {
                return r.json().then(function(json) {
                    if (!r.ok) throw json;
                    return json;
                });
            }).then(function(res) {
                alert(res.message || '書類をアップロードしました。');
                window.location.reload();
            }).catch(function(error) {
                var messages = error && error.errors ? Object.values(error.errors).flat() : [];
                alert(messages[0] || 'アップロードに失敗しました。');
            });
        });
    });
})();
</script>
@endpush
