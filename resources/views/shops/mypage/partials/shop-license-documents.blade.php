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
            </h3>
            <button type="button" class="license-upload-modal__close" id="license-detail-close-btn" aria-label="閉じる">×</button>
        </div>
        <div class="license-upload-modal__body">
            <p id="license-detail-ng" class="license-upload-modal__ng" style="display:none;"></p>
            <p id="license-detail-updated" class="license-upload-modal__meta"></p>
            <a id="license-detail-preview-link" class="license-upload-preview" href="#" target="_blank" rel="noopener" style="display:none;">
                <img id="license-detail-preview-image" class="license-upload-preview__image" src="" alt="書類サムネイル">
                <canvas id="license-detail-preview-canvas" class="license-upload-preview__canvas" style="display:none;"></canvas>
                <span id="license-detail-preview-fallback" class="license-upload-preview__fallback" style="display:none;">PDFを表示</span>
            </a>
            <div id="license-detail-expired-wrap" class="license-upload-modal__expired-wrap" style="display:none;">
                <label class="license-upload-modal__expired-label">営業許可証の有効期限</label>
                <div class="license-upload-modal__expired-grid">
                    <select id="license-detail-exp-year" class="license-upload-modal__expired-input">
                        <option value="">年</option>
                    </select>
                    <select id="license-detail-exp-month" class="license-upload-modal__expired-input">
                        <option value="">月</option>
                    </select>
                    <select id="license-detail-exp-day" class="license-upload-modal__expired-input">
                        <option value="">日</option>
                    </select>
                </div>
                <input type="hidden" id="license-detail-expired-at" value="">
                <p class="license-upload-modal__expired-hint">営業許可証の有効期限を年月日で入力してください（本日以降）。</p>
            </div>
            <div id="license-detail-dropzone" class="license-upload-dropzone">
                <p class="license-upload-dropzone__hint">PDF、JPEG、PNG（最大 8MB）をドラッグ＆ドロップするか、「ファイルを選択」からアップロードしてください。</p>
                <p class="license-upload-dropzone__note">※ 許可証の審査を通過しないと求人を表示できません。</p>
                <input type="hidden" id="license-detail-type" value="">
                <input type="file" id="license-detail-file" class="sr-only" accept=".pdf,.png,.jpg,.jpeg,image/*,application/pdf">
                <div class="license-upload-modal__actions">
                    <button type="button" class="btn-action btn-action-primary" id="license-detail-pick-btn">ファイルを選択</button>
                </div>
                <div class="license-upload-modal__actions" style="margin-top:10px;">
                    <button type="button" class="btn-action btn-action-primary" id="license-detail-request-btn" style="display:none;">審査依頼</button>
                    <button type="button" class="btn-action btn-action-secondary" id="license-detail-withdraw-btn" style="display:none;">提出取り下げ</button>
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
    var detailPreviewLink = document.getElementById('license-detail-preview-link');
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
    var detailExpYear = document.getElementById('license-detail-exp-year');
    var detailExpMonth = document.getElementById('license-detail-exp-month');
    var detailExpDay = document.getElementById('license-detail-exp-day');
    var pdfRenderToken = 0;
    var pdfJsLoadingPromise = null;

    function ensurePdfJsLoaded() {
        if (window.pdfjsLib && window.pdfjsLib.getDocument) {
            return Promise.resolve(window.pdfjsLib);
        }
        if (pdfJsLoadingPromise) {
            return pdfJsLoadingPromise;
        }
        pdfJsLoadingPromise = new Promise(function(resolve, reject) {
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.6.82/legacy/build/pdf.min.js';
            script.onload = function() {
                if (window.pdfjsLib && window.pdfjsLib.GlobalWorkerOptions) {
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.6.82/legacy/build/pdf.worker.min.js';
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

    function pad2(value) {
        var n = Number(value || 0);
        return n < 10 ? '0' + n : String(n);
    }

    function isLeapYear(year) {
        return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
    }

    function daysInMonth(year, month) {
        if (!year || !month) return 31;
        if ([1, 3, 5, 7, 8, 10, 12].indexOf(month) !== -1) return 31;
        if ([4, 6, 9, 11].indexOf(month) !== -1) return 30;
        return isLeapYear(year) ? 29 : 28;
    }

    function syncExpiredAtHidden() {
        if (!detailExpiredAt || !detailExpYear || !detailExpMonth || !detailExpDay) return;
        var y = detailExpYear.value;
        var m = detailExpMonth.value;
        var d = detailExpDay.value;
        if (!y || !m || !d) {
            detailExpiredAt.value = '';
            return;
        }
        detailExpiredAt.value = y + '-' + pad2(m) + '-' + pad2(d);
    }

    function refillDayOptions() {
        if (!detailExpYear || !detailExpMonth || !detailExpDay) return;
        var prev = detailExpDay.value;
        var year = Number(detailExpYear.value || 0);
        var month = Number(detailExpMonth.value || 0);
        var maxDays = daysInMonth(year, month);
        detailExpDay.innerHTML = '<option value="">日</option>';
        for (var d = 1; d <= maxDays; d++) {
            var option = document.createElement('option');
            option.value = String(d);
            option.textContent = String(d);
            detailExpDay.appendChild(option);
        }
        if (prev && Number(prev) <= maxDays) {
            detailExpDay.value = prev;
        }
        syncExpiredAtHidden();
    }

    function initExpireSelectors() {
        if (!detailExpYear || !detailExpMonth || !detailExpDay) return;
        if (detailExpYear.options.length <= 1) {
            var currentYear = new Date().getFullYear();
            for (var y = currentYear; y <= currentYear + 20; y++) {
                var yOpt = document.createElement('option');
                yOpt.value = String(y);
                yOpt.textContent = String(y);
                detailExpYear.appendChild(yOpt);
            }
        }
        if (detailExpMonth.options.length <= 1) {
            for (var m = 1; m <= 12; m++) {
                var mOpt = document.createElement('option');
                mOpt.value = String(m);
                mOpt.textContent = String(m);
                detailExpMonth.appendChild(mOpt);
            }
        }
        refillDayOptions();
    }

    function setExpireSelectorsFromDate(ymd) {
        initExpireSelectors();
        if (!detailExpYear || !detailExpMonth || !detailExpDay || !detailExpiredAt) return;
        if (!ymd || !/^\d{4}-\d{2}-\d{2}$/.test(ymd)) {
            detailExpYear.value = '';
            detailExpMonth.value = '';
            refillDayOptions();
            detailExpDay.value = '';
            detailExpiredAt.value = '';
            return;
        }
        var parts = ymd.split('-');
        detailExpYear.value = String(Number(parts[0]));
        detailExpMonth.value = String(Number(parts[1]));
        refillDayOptions();
        detailExpDay.value = String(Number(parts[2]));
        syncExpiredAtHidden();
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
                if (detailExpYear && detailExpYear.focus) detailExpYear.focus();
                return;
            }
            var today = new Date();
            var todayYmd = today.getFullYear() + '-' + pad2(today.getMonth() + 1) + '-' + pad2(today.getDate());
            if (detailExpiredAt.value < todayYmd) {
                alert('営業許可証の有効期限には本日以降の日付を入力してください。');
                if (detailExpYear && detailExpYear.focus) detailExpYear.focus();
                return;
            }
            formData.append('expired_at', detailExpiredAt.value || '');
        }

        if (detailPickBtn) detailPickBtn.disabled = true;
        if (requestBtn) requestBtn.disabled = true;
        if (withdrawBtn) withdrawBtn.disabled = true;
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
            if (requestBtn) requestBtn.disabled = false;
            if (withdrawBtn) withdrawBtn.disabled = false;
            if (detailFile) detailFile.value = '';
        });
    }

    function postReviewAction(url, type, successMessage) {
        if (!type) return;
        if (detailPickBtn) detailPickBtn.disabled = true;
        if (requestBtn) requestBtn.disabled = true;
        if (withdrawBtn) withdrawBtn.disabled = true;
        if (detailFileHint) detailFileHint.textContent = '送信中…';
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ type: type })
        }).then(function(r) {
            return r.json().then(function(json) {
                if (!r.ok) throw json;
                return json;
            });
        }).then(function(res) {
            alert(res.message || successMessage);
            window.location.reload();
        }).catch(function(error) {
            var messages = error && error.errors ? Object.values(error.errors).flat() : [];
            alert(messages[0] || (error && error.message) || '処理に失敗しました。');
            if (detailFileHint) detailFileHint.textContent = '';
        }).finally(function() {
            if (detailPickBtn) detailPickBtn.disabled = false;
            if (requestBtn) requestBtn.disabled = false;
            if (withdrawBtn) withdrawBtn.disabled = false;
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
            var canRequestReview = card.getAttribute('data-can-request-review') === '1';
            var canWithdrawReview = card.getAttribute('data-can-withdraw-review') === '1';

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

            if (detailPreviewLink && detailPreviewImage && detailPreviewFallback) {
                if (docUrl && docStatus !== 'not_submitted') {
                    detailPreviewLink.href = docUrl;
                    detailPreviewLink.style.display = 'block';
                    var isPdf = /\.pdf(\?|$)/i.test(docUrl);
                    if (isPdf) {
                        detailPreviewImage.style.display = 'none';
                        detailPreviewImage.src = '';
                        if (detailPreviewCanvas) {
                            detailPreviewCanvas.style.display = 'none';
                        }
                        detailPreviewFallback.style.display = 'flex';
                        renderPdfThumbnail(docUrl);
                    } else {
                        detailPreviewImage.style.display = 'block';
                        detailPreviewImage.src = docUrl;
                        if (detailPreviewCanvas) {
                            detailPreviewCanvas.style.display = 'none';
                        }
                        detailPreviewFallback.style.display = 'none';
                    }
                } else {
                    detailPreviewLink.style.display = 'none';
                    detailPreviewImage.src = '';
                    if (detailPreviewCanvas) {
                        detailPreviewCanvas.style.display = 'none';
                    }
                    detailPreviewFallback.style.display = 'none';
                }
            }

            if (detailFileHint) detailFileHint.textContent = '';
            if (detailPickBtn) {
                detailPickBtn.disabled = docStatus === 'pending';
            }
            if (docStatus === 'pending' && detailFileHint) {
                detailFileHint.textContent = '審査中は差し替えできません。再アップロードする場合は「提出取り下げ」を押してください。';
            }
            if (requestBtn) requestBtn.style.display = canRequestReview ? 'inline-flex' : 'none';
            if (withdrawBtn) withdrawBtn.style.display = canWithdrawReview ? 'inline-flex' : 'none';
            if (detailExpiredWrap && detailExpiredAt) {
                if (docKey === 'business') {
                    detailExpiredWrap.style.display = 'block';
                    setExpireSelectorsFromDate(docExpiredAt);
                } else {
                    detailExpiredWrap.style.display = 'none';
                    setExpireSelectorsFromDate('');
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
    if (requestBtn && detailType) {
        requestBtn.addEventListener('click', function() {
            postReviewAction('{{ route("shop.mypage.documents.request-review") }}', detailType.value || '', '審査依頼を送信しました。');
        });
    }
    if (withdrawBtn && detailType) {
        withdrawBtn.addEventListener('click', function() {
            postReviewAction('{{ route("shop.mypage.documents.withdraw-review") }}', detailType.value || '', '提出を取り下げました。再アップロードしてから審査依頼してください。');
        });
    }

    if (detailFile) {
        detailFile.addEventListener('change', function() {
            if (!detailFile.files || !detailFile.files.length) return;
            uploadFile(detailFile.files[0]);
        });
    }
    if (detailExpYear) {
        detailExpYear.addEventListener('change', function() {
            refillDayOptions();
            syncExpiredAtHidden();
        });
    }
    if (detailExpMonth) {
        detailExpMonth.addEventListener('change', function() {
            refillDayOptions();
            syncExpiredAtHidden();
        });
    }
    if (detailExpDay) {
        detailExpDay.addEventListener('change', syncExpiredAtHidden);
    }
    initExpireSelectors();

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
