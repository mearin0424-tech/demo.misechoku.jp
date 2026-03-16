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

        {{-- アイコン＋ひとこと（モーダルで編集） --}}
        <div class="mypage-hero">
            <div class="shop-icon-wrapper">
                <img src="{{ (isset($subImages[0]) ? $subImages[0]['url'] : null) ?? asset('assets/images/common/no-image.png') }}" class="shop-icon-main" id="main-icon-display" alt="">
                <button type="button" class="btn-add-icon" onclick="document.getElementById('gallery-upload').click()" aria-label="写真を追加">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="shop-word-bubble glass-panel">
                <p id="display-word" class="shop-word-text {{ empty(trim($shopData['word'] ?? '')) ? 'is-placeholder' : '' }}" data-placeholder="ひとことを入力すると、タイムラインに表示されます。">{{ !empty(trim($shopData['word'] ?? '')) ? $shopData['word'] : 'ひとことを入力すると、タイムラインに表示されます。' }}</p>
                <div class="shop-word-bubble-footer">
                    <span id="display-word-updated" class="shop-word-bubble-updated">最終更新 {{ $shopData['appeal_updated_at'] ?? '未設定' }}</span>
                    <button type="button" class="btn-word-edit" id="open-word-edit-btn" aria-label="ひとことを編集">
                        <i class="fas fa-pen"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- 評価・応募数・採用数（キャストのLIKE・マッチ・ボーナスと同様の統計行） --}}
        <div class="mypage-stats-row" aria-label="統計">
            <a href="{{ route('shop.mypage.review.index') }}" class="mypage-stat-panel mypage-stat-panel-link">
                <span class="mypage-stat-icon"><i class="fas fa-star"></i></span>
                <span class="mypage-stat-label">評価</span>
                <span class="mypage-stat-value">{{ $shopData['review_avg'] }}</span>
            </a>
            <div class="mypage-stat-panel">
                <span class="mypage-stat-icon"><i class="fas fa-envelope"></i></span>
                <span class="mypage-stat-label">応募数</span>
                <span class="mypage-stat-value">{{ number_format((int) ($shopData['applicant_count'] ?? 0)) }}</span>
            </div>
            <div class="mypage-stat-panel">
                <span class="mypage-stat-icon"><i class="fas fa-user-check"></i></span>
                <span class="mypage-stat-label">採用数</span>
                <span class="mypage-stat-value">{{ number_format((int) ($shopData['hired_count'] ?? 0)) }}</span>
            </div>
        </div>

        <div class="mypage-detail-box">
            {{-- メニュー（キャストマイページと同じボタンデザイン） --}}
            @include('shops.mypage.parts.menu', ['current' => 'profile', 'fullWidth' => false])

            {{-- プロフィール情報（住所・編集のみ。ひとことは吹き出しでモーダル編集） --}}
            <div class="mypage-section profile-info-section">
                <div class="section-title-row">
                    <h2 class="section-title">プロフィール情報</h2>
                    <button type="button" class="btn-outline-gold" id="open-profile-edit-btn">編集</button>
                </div>
                <p class="shop-access-text">
                    <i class="fas fa-map-marker-alt"></i> {{ $shopData['pref'] }}{{ $shopData['city'] }}{{ $shopData['addr1'] }}
                </p>
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
                             data-image-url="{{ $img['url'] ?? '' }}">
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
<div id="image-preview-modal" class="mypage-modal-overlay gallery-preview-overlay" role="dialog" aria-label="画像プレビュー">
    <div class="gallery-preview-inner">
        <img id="modal-img" src="" alt="" class="mypage-modal-preview-img">
        <div class="gallery-preview-actions">
            <button type="button" class="btn-action btn-action-secondary gallery-preview-btn-close" id="gallery-preview-close-btn">閉じる</button>
            <button type="button" id="gallery-preview-delete-btn" class="btn-action gallery-preview-btn-delete">削除</button>
        </div>
    </div>
</div>

{{-- 画像編集モーダル（推奨サイズに合わせてトリミング） --}}
<div id="image-edit-modal" class="mypage-modal-overlay gallery-preview-overlay" role="dialog" aria-label="画像編集" style="display:none;">
    <div class="gallery-preview-inner image-edit-inner">
        <div class="image-edit-header">
            <h3 class="mypage-modal-title serif-font">画像を調整してアップロード</h3>
            <p class="image-edit-guide">
                推奨サイズは <strong>4:3（例：1600×1200px）</strong> です。<br>
                画面に表示されている範囲で中央を基準に自動トリミングし、スマホ表示でも粗くなりすぎないよう約2MPまでサイズ調整してアップロードします。
            </p>
        </div>
        <div class="image-edit-preview-wrapper">
            <div class="image-edit-frame">
                <img id="image-edit-preview" src="" alt="編集プレビュー" class="image-edit-preview-img">
                <div class="image-edit-frame-mask"></div>
            </div>
        </div>
        <div class="gallery-preview-actions image-edit-actions">
            <button type="button" class="btn-action btn-action-secondary" id="image-edit-cancel-btn">別の画像を選ぶ</button>
            <button type="button" class="btn-action btn-action-primary" id="image-edit-confirm-btn">この画像でアップロード</button>
        </div>
    </div>
</div>

{{-- ひとこと編集モーダル --}}
<div id="modal-word" class="mypage-modal-overlay modal-word-edit" style="display:none;">
    <div class="mypage-modal-panel glass-panel">
        <h3 class="mypage-modal-title serif-font">ひとことを編集</h3>
        <textarea id="word-input" rows="3" class="mypage-modal-textarea" placeholder="例：新人大歓迎！働きやすさもお任せください。"></textarea>
        <div class="mypage-modal-actions">
            <button type="button" class="btn-action btn-action-secondary" id="word-edit-cancel-btn">戻る</button>
            <button type="button" class="btn-action btn-action-primary" id="word-edit-save-btn">保存</button>
        </div>
    </div>
</div>

<input type="file" id="gallery-upload" class="sr-only" accept="image/*">
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="{{ asset('assets/js/gallery-sortable.js') }}"></script>
<script>
window.MYPAGE_GALLERY_CONFIG = {
    csrfToken: @json(csrf_token()),
    uploadUrl: @json(route('shop.profile.upload.image')),
    deleteUrlTemplate: @json(route('shop.profile.image.delete', ['id' => '__ID__']))
};
</script>
<script src="{{ asset('assets/js/mypage-gallery.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var placeholderText = 'ひとことを入力すると、タイムラインに表示されます。';
    var openWordBtn = document.getElementById('open-word-edit-btn');
    if (openWordBtn) openWordBtn.addEventListener('click', function() {
        document.getElementById('modal-word').style.display = 'flex';
        var displayEl = document.getElementById('display-word');
        var current = displayEl && displayEl.innerText ? displayEl.innerText.trim() : '';
        var wordInput = document.getElementById('word-input');
        if (wordInput) wordInput.value = (current === placeholderText) ? '' : current;
    });
    var cancelWord = document.getElementById('word-edit-cancel-btn');
    if (cancelWord) cancelWord.addEventListener('click', function() { var modalWord = document.getElementById('modal-word'); if (modalWord) modalWord.style.display = 'none'; });
    var saveWordBtn = document.getElementById('word-edit-save-btn');
    if (saveWordBtn) saveWordBtn.addEventListener('click', function() {
        var wordInputEl = document.getElementById('word-input');
        var val = (wordInputEl && wordInputEl.value || '').trim();
        var displayEl = document.getElementById('display-word');
        var updatedEl = document.getElementById('display-word-updated');
        var m = document.getElementById('modal-word');
        var btn = saveWordBtn;
        if (btn.disabled) return;
        btn.disabled = true;
        fetch('{{ route('shop.mypage.word') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ word: val })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                if (displayEl) {
                    displayEl.innerText = val || placeholderText;
                    displayEl.classList.toggle('is-placeholder', !val);
                }
                if (updatedEl && res.appeal_updated_at) {
                    updatedEl.innerText = '最終更新 ' + res.appeal_updated_at;
                }
                if (m) m.style.display = 'none';
            } else {
                alert(res.message || '保存に失敗しました');
            }
        })
        .catch(function() { alert('保存に失敗しました'); })
        .finally(function() { btn.disabled = false; });
    });
    var profileEditBtn = document.getElementById('open-profile-edit-btn');
    if (profileEditBtn) profileEditBtn.addEventListener('click', function() {
        location.href = "{{ route('shop.profile.store.edit') }}";
    });
});
</script>
<script>
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
