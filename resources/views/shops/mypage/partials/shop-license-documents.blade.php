@php
    $documents = $documents ?? [];
@endphp

<div class="shop-mypage-section document-section">
    <h3 class="shop-mypage-section-label">Licenses</h3>
    @foreach($documents as $doc)
        @php
            $s = $doc['status'] ?? 'not_submitted';
            $record = $doc['record'] ?? null;
            $label = $doc['status_label'] ?? ($s === 'approved' ? '承認済み' : ($s === 'rejected' ? '差し戻し' : ($s === 'pending' ? '審査中' : '未提出')));
            $isMissing = $s === 'not_submitted';
        @endphp
        <button type="button"
            class="shop-mypage-license-card js-license-card {{ $isMissing ? 'is-missing' : '' }}"
            data-doc-key="{{ $doc['key'] }}"
            data-doc-name="{{ $doc['name'] }}"
            data-doc-status="{{ $s }}"
            data-doc-status-label="{{ $label }}"
            data-doc-url="{{ $record['file_url'] ?? '' }}"
            data-doc-updated="{{ $record['updated_at_label'] ?? '' }}"
            data-ng-reason="{{ $record['ng_reason'] ?? '' }}"
            data-doc-expired-at="{{ $record['expired_at'] ?? '' }}">
            <div class="shop-mypage-license-card-body">
                <p class="document-upload-name">{{ $doc['name'] }}</p>
                <div class="document-status-row">
                    <span class="document-status-chip is-{{ str_replace('_', '-', $s) }}">{{ $label }}</span>
                    @if(!empty($record['expiring_soon']))
                        <span class="document-expiring-soon-chip">{{ $record['expiration_notice_label'] ?? '更新期限半年以内' }}</span>
                    @endif
                </div>
                <p class="document-upload-meta">
                    @if($isMissing)
                        タップしてファイルを提出してください
                    @else
                        最終更新: {{ $record['updated_at_label'] ?? '—' }}
                    @endif
                </p>
            </div>
        </button>
    @endforeach
</div>

<div id="modal-license-detail" class="mypage-modal-overlay modal-word-edit" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="license-detail-title">
    <div class="mypage-modal-panel glass-panel license-upload-modal">
        <div class="license-upload-modal__head">
            <h3 id="license-detail-title" class="mypage-modal-title serif-font">許可証の提出</h3>
            <button type="button" class="license-upload-modal__close" id="license-detail-close-btn" aria-label="閉じる">×</button>
        </div>
        <div class="license-upload-modal__body">
            <p id="license-detail-name" class="license-upload-modal__doc-name"></p>
            <div class="license-upload-modal__status-row">
                <span id="license-detail-status-chip" class="document-status-chip is-not-submitted" role="status"></span>
            </div>
            <p id="license-detail-ng" class="license-upload-modal__ng" style="display:none;"></p>
            <p id="license-detail-updated" class="license-upload-modal__meta"></p>
            <div id="license-detail-expired-wrap" class="license-upload-modal__expired-wrap" style="display:none;">
                <label for="license-detail-expired-at" class="license-upload-modal__expired-label">営業許可証の有効期限</label>
                <input type="date" id="license-detail-expired-at" class="license-upload-modal__expired-input">
                <p class="license-upload-modal__expired-hint">営業許可証の有効期限を年月日で入力してください。</p>
            </div>
            <div id="license-detail-dropzone" class="license-upload-dropzone">
                <p class="license-upload-dropzone__hint">PDF、JPEG、PNG（最大 8MB）をドラッグ＆ドロップするか、「ファイルを選択」からアップロードしてください。</p>
                <input type="hidden" id="license-detail-type" value="">
                <input type="file" id="license-detail-file" class="sr-only" accept=".pdf,.png,.jpg,.jpeg,image/*,application/pdf">
                <div class="license-upload-modal__actions">
                    <button type="button" class="btn-action btn-action-primary" id="license-detail-pick-btn">ファイルを選択</button>
                    <a id="license-detail-view-link" href="#" target="_blank" rel="noopener" class="btn-action btn-action-secondary" style="display:none;">書類を表示</a>
                </div>
            </div>
        </div>
        <p id="license-detail-file-hint" class="license-upload-modal__filename" aria-live="polite"></p>
    </div>
</div>

<script>
(function() {
    var cards = document.querySelectorAll('.js-license-card');
    var detailModal = document.getElementById('modal-license-detail');
    if (!cards.length || !detailModal) return;

    var detailName = document.getElementById('license-detail-name');
    var detailChip = document.getElementById('license-detail-status-chip');
    var detailNg = document.getElementById('license-detail-ng');
    var detailUpdated = document.getElementById('license-detail-updated');
    var detailType = document.getElementById('license-detail-type');
    var detailFile = document.getElementById('license-detail-file');
    var detailPickBtn = document.getElementById('license-detail-pick-btn');
    var detailViewLink = document.getElementById('license-detail-view-link');
    var detailCloseBtn = document.getElementById('license-detail-close-btn');
    var detailDropzone = document.getElementById('license-detail-dropzone');
    var detailFileHint = document.getElementById('license-detail-file-hint');
    var detailExpiredWrap = document.getElementById('license-detail-expired-wrap');
    var detailExpiredAt = document.getElementById('license-detail-expired-at');

    function setChip(chipEl, statusKey, labelText) {
        if (!chipEl) return;
        var sk = (statusKey || 'not_submitted').replace(/_/g, '-');
        chipEl.className = 'document-status-chip is-' + sk;
        chipEl.textContent = labelText || '';
    }

    function closeModal() {
        detailModal.style.display = 'none';
        if (detailFile) detailFile.value = '';
        if (detailFileHint) detailFileHint.textContent = '';
        if (detailDropzone) detailDropzone.classList.remove('is-dragover');
    }

    function uploadFile(file) {
        if (!file || !detailType) return;
        var formData = new FormData();
        var docType = detailType.value || '';
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('type', docType);
        formData.append('file', file);
        if (docType === 'business' && detailExpiredAt) {
            if (!detailExpiredAt.value) {
                alert('営業許可証の有効期限を入力してください。');
                if (detailExpiredAt.focus) detailExpiredAt.focus();
                return;
            }
            formData.append('expired_at', detailExpiredAt.value || '');
        }

        if (detailPickBtn) detailPickBtn.disabled = true;
        if (detailFileHint) detailFileHint.textContent = 'アップロード中…';

        fetch('{{ route("shop.mypage.documents.upload") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
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
            alert(messages[0] || (error && error.message) || 'アップロードに失敗しました。');
            if (detailFileHint) detailFileHint.textContent = '';
        }).finally(function() {
            if (detailPickBtn) detailPickBtn.disabled = false;
            if (detailFile) detailFile.value = '';
        });
    }

    cards.forEach(function(card) {
        card.addEventListener('click', function() {
            var docStatus = card.getAttribute('data-doc-status') || 'not_submitted';
            var docUrl = card.getAttribute('data-doc-url') || '';
            var statusLabel = card.getAttribute('data-doc-status-label') || '';
            var ngReason = card.getAttribute('data-ng-reason') || '';
            var docKey = card.getAttribute('data-doc-key') || '';
            var docExpiredAt = card.getAttribute('data-doc-expired-at') || '';

            if (detailName) detailName.textContent = card.getAttribute('data-doc-name') || '書類';
            if (detailType) detailType.value = docKey;

            setChip(detailChip, docStatus, statusLabel);

            if (detailNg) {
                if (docStatus === 'rejected' && ngReason) {
                    detailNg.style.display = 'block';
                    detailNg.textContent = '差し戻し理由: ' + ngReason;
                } else {
                    detailNg.style.display = 'none';
                    detailNg.textContent = '';
                }
            }

            if (detailUpdated) {
                if (docStatus === 'not_submitted') {
                    detailUpdated.textContent = 'まだファイルが提出されていません。';
                } else {
                    detailUpdated.textContent = '最終更新: ' + (card.getAttribute('data-doc-updated') || '—');
                }
            }

            if (detailViewLink) {
                if (docUrl && docStatus !== 'not_submitted') {
                    detailViewLink.href = docUrl;
                    detailViewLink.style.display = 'inline-flex';
                } else {
                    detailViewLink.style.display = 'none';
                }
            }

            if (detailFileHint) detailFileHint.textContent = '';
            if (detailExpiredWrap && detailExpiredAt) {
                if (docKey === 'business') {
                    detailExpiredWrap.style.display = 'block';
                    detailExpiredAt.value = docExpiredAt;
                } else {
                    detailExpiredWrap.style.display = 'none';
                    detailExpiredAt.value = '';
                }
            }

            detailModal.style.display = 'flex';
        });
    });

    detailModal.addEventListener('click', function(e) { if (e.target === detailModal) closeModal(); });
    if (detailCloseBtn) detailCloseBtn.addEventListener('click', closeModal);
    if (detailPickBtn && detailFile) {
        detailPickBtn.addEventListener('click', function() { detailFile.click(); });
    }

    if (detailFile) {
        detailFile.addEventListener('change', function() {
            if (!detailFile.files || !detailFile.files.length) return;
            uploadFile(detailFile.files[0]);
        });
    }

    if (detailDropzone && detailFile) {
        detailDropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            detailDropzone.classList.add('is-dragover');
        });
        detailDropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            if (e.target === detailDropzone) detailDropzone.classList.remove('is-dragover');
        });
        detailDropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            detailDropzone.classList.remove('is-dragover');
            var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (f) uploadFile(f);
        });
    }
})();
</script>
