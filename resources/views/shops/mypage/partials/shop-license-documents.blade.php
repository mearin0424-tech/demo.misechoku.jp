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
            data-doc-expired-at="{{ $record['expired_at'] ?? '' }}"
            data-can-request-review="{{ !empty($record['can_request_review']) ? '1' : '0' }}"
            data-can-withdraw-review="{{ !empty($record['can_withdraw_review']) ? '1' : '0' }}">
            <div class="shop-mypage-license-card-body">
                <p class="document-upload-name">{{ $doc['name'] }}</p>
                @if(in_array($s, ['pending', 'approved'], true))
                    <div class="document-strong-state-badge {{ $s === 'approved' ? 'is-approved' : 'is-pending' }}">
                        {{ $s === 'approved' ? '承認済み' : '審査待ち' }}
                    </div>
                @endif
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
            <h3 id="license-detail-title" class="mypage-modal-title serif-font license-upload-modal__title-row">
                <span id="license-detail-name">許可証の提出</span>
                <span id="license-detail-status-chip" class="document-status-chip is-not-submitted" role="status"></span>
                <span id="license-detail-flow-status" class="license-upload-flow-status">未提出</span>
            </h3>
            <button type="button" class="license-upload-modal__close" id="license-detail-close-btn" aria-label="閉じる">×</button>
        </div>
        <div class="license-upload-modal__body">
            <p id="license-detail-ng" class="license-upload-modal__ng" style="display:none;"></p>
            <p id="license-detail-updated" class="license-upload-modal__meta"></p>
            <p id="license-detail-expired-current" class="license-upload-modal__meta" style="display:none;"></p>
            <a id="license-detail-preview-link" class="license-upload-preview" href="#" target="_blank" rel="noopener" style="display:none;">
                <span id="license-detail-preview-state-badge" class="license-upload-preview__state-badge" style="display:none;"></span>
                <img id="license-detail-preview-image" class="license-upload-preview__image" src="" alt="書類サムネイル">
                <canvas id="license-detail-preview-canvas" class="license-upload-preview__canvas" style="display:none;"></canvas>
                <span id="license-detail-preview-fallback" class="license-upload-preview__fallback" style="display:none;">PDFを表示</span>
            </a>
            <div id="license-detail-expired-wrap" class="license-upload-modal__expired-wrap" style="display:none;">
                <label class="license-upload-modal__expired-label">営業許可証の有効期限</label>
                <input type="date" id="license-detail-expired-at" class="license-upload-modal__expired-input" value="" style="color-scheme: dark;">
                <p class="license-upload-modal__expired-hint">営業許可証の有効期限を年月日で入力してください（本日以降）。</p>
            </div>
            <div id="license-detail-dropzone" class="license-upload-dropzone">
                <p id="license-detail-upload-hint" class="license-upload-dropzone__hint" style="display:none;">PDF、JPEG、PNG（最大 8MB）をドラッグ＆ドロップするか、「ファイルを選択」からアップロードしてください。</p>
                <p id="license-detail-upload-note" class="license-upload-dropzone__note" style="display:none;">※ 許可証の審査を通過しないと求人を表示できません。</p>
                <input type="hidden" id="license-detail-type" value="">
                <input type="file" id="license-detail-file" class="sr-only" accept=".pdf,.png,.jpg,.jpeg,image/*,application/pdf">
                <div class="license-upload-modal__actions">
                    <button type="button" class="btn-action btn-action-primary" id="license-detail-pick-btn">ファイルを選択</button>
                    <button type="button" class="btn-action btn-action-secondary" id="license-detail-repick-btn" style="display:none;">ファイルを変更</button>
                </div>
                <div class="license-upload-modal__actions" style="margin-top:10px;">
                    <button type="button" class="btn-action btn-action-primary" id="license-detail-request-btn" style="display:none;">提出</button>
                    <button type="button" class="btn-action btn-action-secondary" id="license-detail-withdraw-btn" style="display:none;">提出取り下げ</button>
                </div>
            </div>
        </div>
        <p id="license-detail-file-hint" class="license-upload-modal__filename" aria-live="polite"></p>
    </div>
</div>

<div id="license-detail-toast" class="license-upload-toast" style="display:none;">
    提出が完了しました。審査をお待ちください。
</div>

<div id="license-detail-confirm-overlay" class="license-upload-confirm-overlay" style="display:none;">
    <div class="license-upload-confirm-panel">
        <h4>提出の取り下げ</h4>
        <p>提出を取り下げると、未提出状態に戻ります。取り下げますか？</p>
        <div class="license-upload-confirm-actions">
            <button type="button" class="btn-action btn-action-secondary" id="license-detail-confirm-cancel">キャンセル</button>
            <button type="button" class="btn-action" id="license-detail-confirm-ok">取り下げる</button>
        </div>
    </div>
</div>

<script>
(function() {
    var cards = document.querySelectorAll('.js-license-card');
    var detailModal = document.getElementById('modal-license-detail');
    if (!cards.length || !detailModal) return;

    var detailName = document.getElementById('license-detail-name');
    var detailChip = document.getElementById('license-detail-status-chip');
    var detailFlowStatus = document.getElementById('license-detail-flow-status');
    var detailNg = document.getElementById('license-detail-ng');
    var detailUpdated = document.getElementById('license-detail-updated');
    var detailType = document.getElementById('license-detail-type');
    var detailFile = document.getElementById('license-detail-file');
    var detailPickBtn = document.getElementById('license-detail-pick-btn');
    var detailRepickBtn = document.getElementById('license-detail-repick-btn');
    var detailPreviewLink = document.getElementById('license-detail-preview-link');
    var detailPreviewStateBadge = document.getElementById('license-detail-preview-state-badge');
    var detailPreviewImage = document.getElementById('license-detail-preview-image');
    var detailPreviewCanvas = document.getElementById('license-detail-preview-canvas');
    var detailPreviewFallback = document.getElementById('license-detail-preview-fallback');
    var detailCloseBtn = document.getElementById('license-detail-close-btn');
    var detailDropzone = document.getElementById('license-detail-dropzone');
    var detailFileHint = document.getElementById('license-detail-file-hint');
    var requestBtn = document.getElementById('license-detail-request-btn');
    var withdrawBtn = document.getElementById('license-detail-withdraw-btn');
    var detailExpiredWrap = document.getElementById('license-detail-expired-wrap');
    var detailExpiredAt = document.getElementById('license-detail-expired-at');
    var detailExpiredCurrent = document.getElementById('license-detail-expired-current');
    var detailUploadHint = document.getElementById('license-detail-upload-hint');
    var detailUploadNote = document.getElementById('license-detail-upload-note');
    var toast = document.getElementById('license-detail-toast');
    var confirmOverlay = document.getElementById('license-detail-confirm-overlay');
    var confirmOk = document.getElementById('license-detail-confirm-ok');
    var confirmCancel = document.getElementById('license-detail-confirm-cancel');
    var pdfRenderToken = 0;
    var pdfJsLoadingPromise = null;
    var selectedFile = null;
    var selectedFileUrl = '';
    var modalState = 'unsubmitted'; // unsubmitted | selecting | submitted
    var currentDocStatus = 'not_submitted';
    var currentDocUrl = '';
    var currentDocType = '';
    var currentDocExpiredAt = '';
    var currentCanRequestReview = false;
    var currentCanWithdrawReview = false;

    function ensurePdfJsLoaded() {
        if (window.pdfjsLib && window.pdfjsLib.getDocument) {
            return Promise.resolve(window.pdfjsLib);
        }
        if (pdfJsLoadingPromise) {
            return pdfJsLoadingPromise;
        }
        pdfJsLoadingPromise = new Promise(function(resolve, reject) {
            var script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
            script.onload = function() {
                if (window.pdfjsLib && window.pdfjsLib.GlobalWorkerOptions) {
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                    resolve(window.pdfjsLib);
                    return;
                }
                reject(new Error('pdf.js の初期化に失敗しました。'));
            };
            script.onerror = function() {
                reject(new Error('pdf.js の読み込みに失敗しました。'));
            };
            document.head.appendChild(script);
        }).catch(function(err) {
            pdfJsLoadingPromise = null;
            throw err;
        });
        return pdfJsLoadingPromise;
    }

    function renderPdfThumbnail(url) {
        if (!detailPreviewCanvas || !detailPreviewFallback || !detailPreviewImage) return;
        var currentToken = ++pdfRenderToken;
        detailPreviewImage.style.display = 'none';
        detailPreviewImage.src = '';
        detailPreviewCanvas.style.display = 'none';
        detailPreviewFallback.style.display = 'flex';
        detailPreviewFallback.textContent = 'PDFサムネイルを生成中…';

        ensurePdfJsLoaded()
            .then(function(pdfjsLib) {
                return pdfjsLib.getDocument({ url: url }).promise;
            })
            .then(function(pdf) {
                return pdf.getPage(1);
            })
            .then(function(page) {
                if (currentToken !== pdfRenderToken) return;
                var viewport = page.getViewport({ scale: 1 });
                var targetWidth = 720;
                var deviceScale = Math.min(window.devicePixelRatio || 1, 2);
                var scale = (targetWidth / viewport.width) * deviceScale;
                var scaledViewport = page.getViewport({ scale: scale });
                var canvas = detailPreviewCanvas;
                var context = canvas.getContext('2d');
                canvas.width = Math.ceil(scaledViewport.width);
                canvas.height = Math.ceil(scaledViewport.height);
                return page.render({
                    canvasContext: context,
                    viewport: scaledViewport
                }).promise.then(function() {
                    if (currentToken !== pdfRenderToken) return;
                    canvas.style.display = 'block';
                    detailPreviewFallback.style.display = 'none';
                });
            })
            .catch(function() {
                if (currentToken !== pdfRenderToken) return;
                detailPreviewCanvas.style.display = 'none';
                detailPreviewFallback.style.display = 'flex';
                detailPreviewFallback.textContent = 'PDFを表示';
            });
    }

    function setExpireSelectorsFromDate(ymd) {
        if (!detailExpiredAt) return;
        detailExpiredAt.value = (/^\d{4}-\d{2}-\d{2}$/.test(ymd || '')) ? ymd : '';
    }

    function formatYmd(ymd) {
        if (!ymd || !/^\d{4}-\d{2}-\d{2}$/.test(ymd)) return '';
        var p = ymd.split('-');
        return String(Number(p[0])) + '年' + String(Number(p[1])) + '月' + String(Number(p[2])) + '日';
    }

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
        resetSelectedFile();
    }

    function showToast(message) {
        if (!toast) return;
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(function() { toast.style.display = 'none'; }, 3000);
    }

    function resetSelectedFile() {
        if (selectedFileUrl) {
            URL.revokeObjectURL(selectedFileUrl);
        }
        selectedFile = null;
        selectedFileUrl = '';
        if (detailFile) detailFile.value = '';
    }

    function renderPreview(url, isPdf, fallbackText) {
        if (!detailPreviewLink || !detailPreviewImage || !detailPreviewFallback) return;
        if (!url) {
            detailPreviewLink.style.display = 'none';
            if (detailPreviewStateBadge) detailPreviewStateBadge.style.display = 'none';
            detailPreviewImage.src = '';
            if (detailPreviewCanvas) detailPreviewCanvas.style.display = 'none';
            detailPreviewFallback.style.display = 'none';
            return;
        }
        detailPreviewLink.href = url;
        detailPreviewLink.style.display = 'block';
        if (detailPreviewStateBadge) {
            if (currentDocStatus === 'pending' || currentDocStatus === 'approved') {
                detailPreviewStateBadge.style.display = 'inline-flex';
                detailPreviewStateBadge.textContent = currentDocStatus === 'approved' ? '承認済み' : '審査待ち';
                detailPreviewStateBadge.className = 'license-upload-preview__state-badge ' + (currentDocStatus === 'approved' ? 'is-approved' : 'is-pending');
            } else {
                detailPreviewStateBadge.style.display = 'none';
            }
        }
        if (isPdf) {
            detailPreviewImage.style.display = 'none';
            detailPreviewImage.src = '';
            if (detailPreviewCanvas) detailPreviewCanvas.style.display = 'none';
            detailPreviewFallback.style.display = 'flex';
            detailPreviewFallback.textContent = fallbackText || 'PDFを表示';
            renderPdfThumbnail(url);
        } else {
            detailPreviewImage.style.display = 'block';
            detailPreviewImage.src = url;
            if (detailPreviewCanvas) detailPreviewCanvas.style.display = 'none';
            detailPreviewFallback.style.display = 'none';
        }
    }

    function setUiByState() {
        var submitted = modalState === 'submitted';
        var selecting = modalState === 'selecting';
        var unsubmitted = modalState === 'unsubmitted';
        var isBusiness = currentDocType === 'business';

        if (detailPickBtn) detailPickBtn.style.display = unsubmitted ? 'inline-flex' : 'none';
        if (detailRepickBtn) detailRepickBtn.style.display = selecting ? 'inline-flex' : 'none';
        if (requestBtn) requestBtn.style.display = (selecting && currentCanRequestReview) ? 'inline-flex' : 'none';
        if (withdrawBtn) withdrawBtn.style.display = (submitted && currentCanWithdrawReview) ? 'inline-flex' : 'none';
        if (detailUploadHint) detailUploadHint.style.display = unsubmitted ? 'block' : 'none';
        if (detailUploadNote) detailUploadNote.style.display = unsubmitted ? 'block' : 'none';
        if (detailFlowStatus) {
            if (submitted) {
                detailFlowStatus.textContent = '提出済み';
                detailFlowStatus.className = 'license-upload-flow-status is-submitted';
            } else if (selecting) {
                detailFlowStatus.textContent = '選択中';
                detailFlowStatus.className = 'license-upload-flow-status is-selecting';
            } else {
                detailFlowStatus.textContent = '未提出';
                detailFlowStatus.className = 'license-upload-flow-status is-unsubmitted';
            }
        }

        if (detailExpiredWrap && detailExpiredAt) {
            if (isBusiness && (selecting || submitted)) {
                detailExpiredWrap.style.display = 'block';
                detailExpiredAt.readOnly = submitted;
                detailExpiredAt.disabled = submitted;
            } else {
                detailExpiredWrap.style.display = 'none';
            }
        }
        if (detailExpiredCurrent) {
            detailExpiredCurrent.style.display = (isBusiness && (selecting || submitted)) ? 'block' : 'none';
            detailExpiredCurrent.textContent = isBusiness
                ? ('登録済み有効期限: ' + (currentDocExpiredAt ? formatYmd(currentDocExpiredAt) : '未登録'))
                : '';
        }
        if (detailFileHint) {
            if (submitted) {
                detailFileHint.textContent = '現在、審査待ちです。承認されるまでしばらくお待ちください。ファイルを再アップロードする場合は、一度提出を取り下げてください。';
            } else if (selecting) {
                detailFileHint.textContent = '有効期限を入力して「提出」を押してください。';
            } else {
                detailFileHint.textContent = '';
            }
        }
        updateSubmitButtonState();
    }

    function updateSubmitButtonState() {
        if (!requestBtn) return;
        var selecting = modalState === 'selecting';
        var hasDoc = !!selectedFile || !!currentDocUrl;
        var validExpire = currentDocType !== 'business' || (detailExpiredAt && !!detailExpiredAt.value);
        var enabled = selecting && hasDoc && validExpire && currentCanRequestReview;
        requestBtn.disabled = !enabled;
        requestBtn.classList.toggle('is-disabled', !enabled);
    }

    function uploadFile(file) {
        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('type', currentDocType);
        formData.append('file', file);

        if (detailPickBtn) detailPickBtn.disabled = true;
        if (detailRepickBtn) detailRepickBtn.disabled = true;
        if (requestBtn) requestBtn.disabled = true;
        if (withdrawBtn) withdrawBtn.disabled = true;
        if (detailFileHint) detailFileHint.textContent = 'アップロード中…';

        return fetch('{{ route("shop.mypage.documents.upload") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        }).then(function (r) {
            return r.json().then(function(json) {
                if (!r.ok) throw json;
                return json;
            });
        }).finally(function() {
            if (detailPickBtn) detailPickBtn.disabled = false;
            if (detailRepickBtn) detailRepickBtn.disabled = false;
            if (requestBtn) requestBtn.disabled = false;
            if (withdrawBtn) withdrawBtn.disabled = false;
        });
    }

    function postReviewAction(url, type, successMessage, expiredAt) {
        if (!type) return;
        if (detailPickBtn) detailPickBtn.disabled = true;
        if (requestBtn) requestBtn.disabled = true;
        if (withdrawBtn) withdrawBtn.disabled = true;
        if (detailFileHint) detailFileHint.textContent = '送信中…';
        var payload = { type: type };
        if (expiredAt) {
            payload.expired_at = expiredAt;
        }
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(function(r) {
            return r.json().then(function(json) {
                if (!r.ok) throw json;
                return json;
            });
        }).then(function(res) {
            if (detailFileHint) detailFileHint.textContent = '';
            if (res && res.message) {
                alert(res.message);
            } else if (successMessage) {
                alert(successMessage);
            }
            return res;
        }).catch(function(error) {
            var messages = error && error.errors ? Object.values(error.errors).flat() : [];
            alert(messages[0] || (error && error.message) || '処理に失敗しました。');
            if (detailFileHint) detailFileHint.textContent = '';
            throw error;
        }).finally(function() {
            if (detailPickBtn) detailPickBtn.disabled = false;
            if (requestBtn) requestBtn.disabled = false;
            if (withdrawBtn) withdrawBtn.disabled = false;
        });
    }

    cards.forEach(function(card) {
        card.addEventListener('click', function() {
            currentDocStatus = card.getAttribute('data-doc-status') || 'not_submitted';
            currentDocUrl = card.getAttribute('data-doc-url') || '';
            var statusLabel = card.getAttribute('data-doc-status-label') || '';
            var ngReason = card.getAttribute('data-ng-reason') || '';
            currentDocType = card.getAttribute('data-doc-key') || '';
            currentDocExpiredAt = card.getAttribute('data-doc-expired-at') || '';
            var canRequestReview = card.getAttribute('data-can-request-review') === '1';
            var canWithdrawReview = card.getAttribute('data-can-withdraw-review') === '1';
            currentCanRequestReview = canRequestReview;
            currentCanWithdrawReview = canWithdrawReview;

            if (detailName) detailName.textContent = card.getAttribute('data-doc-name') || '書類';
            if (detailType) detailType.value = currentDocType;

            setChip(detailChip, currentDocStatus, statusLabel);

            if (detailNg) {
                if (currentDocStatus === 'rejected' && ngReason) {
                    detailNg.style.display = 'block';
                    detailNg.textContent = '差し戻し理由: ' + ngReason;
                } else {
                    detailNg.style.display = 'none';
                    detailNg.textContent = '';
                }
            }

            if (detailUpdated) {
                if (currentDocStatus === 'not_submitted') {
                    detailUpdated.textContent = 'まだファイルが提出されていません。';
                } else {
                    detailUpdated.textContent = '最終更新: ' + (card.getAttribute('data-doc-updated') || '—');
                }
            }

            resetSelectedFile();
            setExpireSelectorsFromDate(currentDocExpiredAt);
            if (currentDocStatus === 'pending' || currentDocStatus === 'approved') {
                modalState = 'submitted';
                renderPreview(currentDocUrl, /\.pdf(\?|$)/i.test(currentDocUrl), 'PDFを表示');
            } else if (currentDocUrl && canRequestReview) {
                modalState = 'selecting';
                renderPreview(currentDocUrl, /\.pdf(\?|$)/i.test(currentDocUrl), 'PDFを表示');
            } else {
                modalState = 'unsubmitted';
                renderPreview('', false, '');
            }
            setUiByState();

            detailModal.style.display = 'flex';
        });
    });

    detailModal.addEventListener('click', function(e) { if (e.target === detailModal) closeModal(); });
    if (detailCloseBtn) detailCloseBtn.addEventListener('click', closeModal);
    if (detailPickBtn && detailFile) detailPickBtn.addEventListener('click', function() { detailFile.click(); });
    if (detailRepickBtn && detailFile) detailRepickBtn.addEventListener('click', function() { detailFile.click(); });
    if (requestBtn && detailType) {
        requestBtn.addEventListener('click', function() {
            var t = detailType.value || '';
            if (t === 'business' && (!detailExpiredAt || !detailExpiredAt.value)) {
                alert('営業許可証の有効期限を入力してください。');
                if (detailExpiredAt && detailExpiredAt.focus) detailExpiredAt.focus();
                return;
            }
            if (!selectedFile && !currentDocUrl) {
                alert('ファイルを選択してください。');
                return;
            }
            var submitAction = function() {
                postReviewAction(
                    '{{ route("shop.mypage.documents.request-review") }}',
                    t,
                    '提出が完了しました。運営の審査をお待ちください。',
                    t === 'business' ? (detailExpiredAt ? detailExpiredAt.value : '') : ''
                ).then(function() {
                    modalState = 'submitted';
                    currentDocStatus = 'pending';
                    currentDocExpiredAt = detailExpiredAt ? detailExpiredAt.value : currentDocExpiredAt;
                    setUiByState();
                    showToast('提出が完了しました。審査をお待ちください。');
                });
            };

            if (selectedFile) {
                uploadFile(selectedFile).then(function(res) {
                    currentDocUrl = res.view_url || currentDocUrl;
                    renderPreview(currentDocUrl, /\.pdf(\?|$)/i.test(currentDocUrl), 'PDFを表示');
                    submitAction();
                }).catch(function(error) {
                    var messages = error && error.errors ? Object.values(error.errors).flat() : [];
                    alert(messages[0] || (error && error.message) || 'アップロードに失敗しました。');
                });
                return;
            }

            postReviewAction(
                    '{{ route("shop.mypage.documents.request-review") }}',
                    t,
                    '提出が完了しました。運営の審査をお待ちください。',
                    t === 'business' ? (detailExpiredAt ? detailExpiredAt.value : '') : ''
                ).then(function() {
                    modalState = 'submitted';
                    currentDocStatus = 'pending';
                    setUiByState();
                    showToast('提出が完了しました。審査をお待ちください。');
                });
        });
    }
    if (withdrawBtn && detailType) {
        withdrawBtn.addEventListener('click', function() {
            if (confirmOverlay) {
                confirmOverlay.style.display = 'flex';
            } else {
                postReviewAction('{{ route("shop.mypage.documents.withdraw-review") }}', detailType.value || '', '提出を取り下げました。再アップロードしてから審査依頼してください。')
                    .then(function() {
                        modalState = 'unsubmitted';
                        currentDocStatus = 'not_submitted';
                        currentDocUrl = '';
                        currentDocExpiredAt = '';
                        renderPreview('', false, '');
                        setExpireSelectorsFromDate('');
                        setUiByState();
                    });
            }
        });
    }

    if (detailFile) {
        detailFile.addEventListener('change', function() {
            if (!detailFile.files || !detailFile.files.length) return;
            resetSelectedFile();
            selectedFile = detailFile.files[0];
            selectedFileUrl = URL.createObjectURL(selectedFile);
            modalState = 'selecting';
            if (detailExpiredAt && currentDocType === 'business') {
                detailExpiredAt.readOnly = false;
                detailExpiredAt.disabled = false;
            }
            renderPreview(selectedFileUrl, selectedFile.type === 'application/pdf' || /\.pdf$/i.test(selectedFile.name), 'PDFを表示');
            setUiByState();
        });
    }
    if (detailExpiredAt) {
        detailExpiredAt.addEventListener('change', updateSubmitButtonState);
    }
    if (detailDropzone && detailFile) {
        detailDropzone.addEventListener('dragover', function(e) {
            if (modalState === 'submitted') return;
            e.preventDefault();
            e.stopPropagation();
            detailDropzone.classList.add('is-dragover');
        });
        detailDropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            if (e.target === detailDropzone) detailDropzone.classList.remove('is-dragover');
        });
        detailDropzone.addEventListener('drop', function(e) {
            if (modalState === 'submitted') return;
            e.preventDefault();
            e.stopPropagation();
            detailDropzone.classList.remove('is-dragover');
            var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (!f) return;
            resetSelectedFile();
            selectedFile = f;
            selectedFileUrl = URL.createObjectURL(f);
            modalState = 'selecting';
            renderPreview(selectedFileUrl, f.type === 'application/pdf' || /\.pdf$/i.test(f.name), 'PDFを表示');
            setUiByState();
        });
    }

    if (confirmCancel && confirmOverlay) {
        confirmCancel.addEventListener('click', function() {
            confirmOverlay.style.display = 'none';
        });
    }
    if (confirmOk && confirmOverlay) {
        confirmOk.addEventListener('click', function() {
            confirmOverlay.style.display = 'none';
            postReviewAction('{{ route("shop.mypage.documents.withdraw-review") }}', detailType.value || '', '提出を取り下げました。')
                .then(function() {
                    modalState = 'unsubmitted';
                    currentDocStatus = 'not_submitted';
                    currentDocUrl = '';
                    currentDocExpiredAt = '';
                    renderPreview('', false, '');
                    setExpireSelectorsFromDate('');
                    setUiByState();
                });
        });
    }
})();
</script>
