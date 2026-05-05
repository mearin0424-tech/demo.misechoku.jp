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

            {{-- メインビュー --}}
            <div id="license-detail-main-view">
                <p id="license-detail-ng" class="license-upload-modal__ng" style="display:none;"></p>
                <p id="license-detail-updated" class="license-upload-modal__meta" style="display:none;"></p>

                {{-- サムネイルプレビュー --}}
                <div id="license-detail-preview" class="license-upload-preview" style="display:none;"></div>

                {{-- 有効期限（営業許可証 × 選択中 or 提出済みで表示） --}}
                <div id="license-detail-expired-wrap" class="license-upload-modal__expired-wrap" style="display:none;">
                    <label class="license-upload-modal__expired-label" for="license-detail-expired-at">営業許可証の有効期限</label>
                    <input type="date" id="license-detail-expired-at" class="license-upload-modal__expired-input" value="" style="color-scheme: dark;">
                    <p id="license-detail-expired-hint" class="license-upload-modal__expired-hint">営業許可証の有効期限を年月日で入力してください（本日以降）。</p>
                </div>

                <div id="license-detail-dropzone" class="license-upload-dropzone">
                    <div id="license-detail-upload-icon" class="license-upload-dropzone__icon" style="display:none;">⇪</div>
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

            {{-- 取り下げ確認（インライン・同画面内） --}}
            <div id="license-detail-withdraw-confirm" class="license-withdraw-confirm" style="display:none;" role="alertdialog" aria-labelledby="withdraw-confirm-title">
                <p id="withdraw-confirm-title" class="license-withdraw-confirm__title">提出の取り下げ</p>
                <p class="license-withdraw-confirm__text">提出を取り下げると未提出状態に戻ります。よろしいですか？</p>
                <div class="license-withdraw-confirm__actions">
                    <button type="button" class="btn-action btn-action-secondary" id="license-detail-confirm-cancel">キャンセル</button>
                    <button type="button" class="btn-action license-withdraw-confirm__ok-btn" id="license-detail-confirm-ok">取り下げる</button>
                </div>
            </div>

        </div>
        <p id="license-detail-file-hint" class="license-upload-modal__filename" aria-live="polite"></p>
    </div>
</div>

<div id="license-detail-toast" class="license-upload-toast" style="display:none;">
    提出が完了しました。審査をお待ちください。
</div>

<script>
(function() {
    var cards = document.querySelectorAll('.js-license-card');
    var detailModal = document.getElementById('modal-license-detail');
    if (!cards.length || !detailModal) return;

    var detailName        = document.getElementById('license-detail-name');
    var detailChip        = document.getElementById('license-detail-status-chip');
    var detailFlowStatus  = document.getElementById('license-detail-flow-status');
    var detailNg          = document.getElementById('license-detail-ng');
    var detailUpdated     = document.getElementById('license-detail-updated');
    var detailPreview     = document.getElementById('license-detail-preview');
    var detailType        = document.getElementById('license-detail-type');
    var detailFile        = document.getElementById('license-detail-file');
    var detailPickBtn     = document.getElementById('license-detail-pick-btn');
    var detailRepickBtn   = document.getElementById('license-detail-repick-btn');
    var detailCloseBtn    = document.getElementById('license-detail-close-btn');
    var detailDropzone    = document.getElementById('license-detail-dropzone');
    var detailFileHint    = document.getElementById('license-detail-file-hint');
    var requestBtn        = document.getElementById('license-detail-request-btn');
    var withdrawBtn       = document.getElementById('license-detail-withdraw-btn');
    var detailExpiredWrap = document.getElementById('license-detail-expired-wrap');
    var detailExpiredAt   = document.getElementById('license-detail-expired-at');
    var detailExpiredHint = document.getElementById('license-detail-expired-hint');
    var detailUploadIcon  = document.getElementById('license-detail-upload-icon');
    var detailUploadHint  = document.getElementById('license-detail-upload-hint');
    var detailUploadNote  = document.getElementById('license-detail-upload-note');
    var toast             = document.getElementById('license-detail-toast');
    var mainView          = document.getElementById('license-detail-main-view');
    var withdrawConfirm   = document.getElementById('license-detail-withdraw-confirm');
    var confirmOk         = document.getElementById('license-detail-confirm-ok');
    var confirmCancel     = document.getElementById('license-detail-confirm-cancel');

    var selectedFile = null;
    var objectUrlToRevoke = null;
    var modalState = 'unsubmitted'; // 'unsubmitted' | 'selecting' | 'submitted'
    var currentDocStatus = 'not_submitted';
    var currentDocUrl = '';
    var currentDocType = '';
    var currentDocExpiredAt = '';
    var currentDocUpdatedAt = '';
    var currentCanRequestReview = false;
    var currentCanWithdrawReview = false;

    // ---- ユーティリティ ----

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fileNameFromUrl(url) {
        if (!url) return '';
        var noQuery = url.split('?')[0];
        var raw = noQuery.substring(noQuery.lastIndexOf('/') + 1);
        try { return decodeURIComponent(raw); } catch (e) { return raw; }
    }

    function isPdfByUrl(url) {
        return /\.pdf(\?|$)/i.test(url || '');
    }

    function revokeObjectUrl() {
        if (objectUrlToRevoke) {
            URL.revokeObjectURL(objectUrlToRevoke);
            objectUrlToRevoke = null;
        }
    }

    function setExpireValue(ymd) {
        if (!detailExpiredAt) return;
        detailExpiredAt.value = (/^\d{4}-\d{2}-\d{2}$/.test(ymd || '')) ? ymd : '';
    }

    function setChip(chipEl, statusKey, labelText) {
        if (!chipEl) return;
        chipEl.className = 'document-status-chip is-' + (statusKey || 'not_submitted').replace(/_/g, '-');
        chipEl.textContent = labelText || '';
    }

    // ---- 確認パネルの表示切り替え ----

    function showWithdrawConfirm() {
        if (mainView)       mainView.style.display = 'none';
        if (withdrawConfirm) withdrawConfirm.style.display = 'block';
        if (detailFileHint) detailFileHint.textContent = '';
    }

    function hideWithdrawConfirm() {
        if (withdrawConfirm) withdrawConfirm.style.display = 'none';
        if (mainView)        mainView.style.display = 'block';
    }

    // ---- プレビュー描画 ----

    function renderPreview(url, isPdf, fileName, badgeStatusKey) {
        if (!detailPreview) return;
        if (!url) { clearPreview(); return; }

        var badgeHtml = '';
        if (badgeStatusKey === 'pending') {
            badgeHtml = '<span class="license-upload-preview__state-badge is-pending">審査待ち</span>';
        } else if (badgeStatusKey === 'approved') {
            badgeHtml = '<span class="license-upload-preview__state-badge is-approved">承認済み</span>';
        }

        var contentHtml = isPdf
            ? '<div class="license-upload-preview__fallback">📄 ' + escapeHtml(fileName || 'ファイル') + '</div>'
            : '<img src="' + escapeHtml(url) + '" alt="プレビュー" class="license-upload-preview__image">';

        detailPreview.innerHTML = badgeHtml + contentHtml;
        detailPreview.className = 'license-upload-preview' + (badgeStatusKey ? '' : ' is-selecting');
        detailPreview.style.display = 'block';
    }

    function clearPreview() {
        if (!detailPreview) return;
        detailPreview.style.display = 'none';
        detailPreview.innerHTML = '';
    }

    // ---- UI 状態反映 ----

    function setUiByState() {
        var submitted   = modalState === 'submitted';
        var selecting   = modalState === 'selecting';
        var unsubmitted = modalState === 'unsubmitted';
        var isBusiness  = currentDocType === 'business';

        // 確認パネルを閉じてメインビューを表示
        hideWithdrawConfirm();

        // ボタン表示
        if (detailPickBtn)   detailPickBtn.style.display   = unsubmitted ? 'inline-flex' : 'none';
        if (detailRepickBtn) detailRepickBtn.style.display = selecting   ? 'inline-flex' : 'none';
        if (requestBtn)      requestBtn.style.display      = (selecting && currentCanRequestReview) ? 'inline-flex' : 'none';
        if (withdrawBtn)     withdrawBtn.style.display     = (submitted && currentCanWithdrawReview) ? 'inline-flex' : 'none';

        // ドロップゾーン内ヒント
        if (detailUploadIcon) detailUploadIcon.style.display = unsubmitted ? 'inline-flex' : 'none';
        if (detailUploadHint) detailUploadHint.style.display = unsubmitted ? 'block' : 'none';
        if (detailUploadNote) detailUploadNote.style.display = 'none';

        // ステータスチップは非表示（flow-status テキストのみ使用）
        if (detailChip) detailChip.style.display = 'none';

        // フロー表示テキスト
        if (detailFlowStatus) {
            if (submitted) {
                detailFlowStatus.textContent = '提出済み';
                detailFlowStatus.className   = 'license-upload-flow-status is-submitted';
            } else if (selecting) {
                detailFlowStatus.textContent = 'ファイル選択中';
                detailFlowStatus.className   = 'license-upload-flow-status is-selecting';
            } else {
                detailFlowStatus.textContent = '未提出';
                detailFlowStatus.className   = 'license-upload-flow-status is-unsubmitted';
            }
        }

        // 最終更新日
        if (detailUpdated) {
            detailUpdated.style.display = (selecting || submitted) ? 'block' : 'none';
            detailUpdated.textContent = '最終更新: ' + (currentDocUpdatedAt || '----/--/-- --:--');
        }

        // 有効期限入力欄（営業許可証かつ選択中 or 提出済みで表示、提出済みは readonly）
        if (detailExpiredWrap && detailExpiredAt) {
            if (isBusiness && (selecting || submitted)) {
                detailExpiredWrap.style.display = 'block';
                if (submitted) {
                    detailExpiredAt.setAttribute('readonly', '');
                } else {
                    detailExpiredAt.removeAttribute('readonly');
                }
            } else {
                detailExpiredWrap.style.display = 'none';
            }
        }
        if (detailExpiredHint) {
            detailExpiredHint.style.display = (isBusiness && selecting) ? 'block' : 'none';
        }

        // 下部ヒントテキスト
        if (detailFileHint) {
            if (submitted) {
                detailFileHint.textContent = '提出済みです。差し替える場合は「提出取り下げ」を押してください。';
            } else if (selecting) {
                detailFileHint.textContent = isBusiness
                    ? '有効期限を入力して「提出」を押してください。'
                    : '「提出」を押して審査を依頼してください。';
            } else {
                detailFileHint.textContent = '';
            }
        }

        updateSubmitButtonState();
    }

    function updateSubmitButtonState() {
        if (!requestBtn) return;
        var selecting   = modalState === 'selecting';
        var hasDoc      = !!selectedFile || !!currentDocUrl;
        var validExpire = currentDocType !== 'business' || (detailExpiredAt && !!detailExpiredAt.value);
        var enabled     = selecting && hasDoc && validExpire && currentCanRequestReview;
        requestBtn.disabled = !enabled;
        requestBtn.classList.toggle('is-disabled', !enabled);
    }

    // ---- API 通信 ----

    function uploadFile(file) {
        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('type', currentDocType);
        formData.append('file', file);

        setButtonsDisabled(true);
        if (detailFileHint) detailFileHint.textContent = 'アップロード中…';

        return fetch('{{ route("shop.mypage.documents.upload") }}', {
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
        }).finally(function() {
            setButtonsDisabled(false);
        });
    }

    function postReviewAction(url, type, expiredAt) {
        if (!type) return Promise.reject(new Error('type is empty'));
        setButtonsDisabled(true);
        if (detailFileHint) detailFileHint.textContent = '送信中…';
        var payload = { type: type };
        if (expiredAt) payload.expired_at = expiredAt;
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
        }).catch(function(error) {
            var messages = error && error.errors ? Object.values(error.errors).flat() : [];
            alert(messages[0] || (error && error.message) || '処理に失敗しました。');
            if (detailFileHint) detailFileHint.textContent = '';
            throw error;
        }).finally(function() {
            setButtonsDisabled(false);
            if (detailFileHint) detailFileHint.textContent = '';
        });
    }

    function setButtonsDisabled(disabled) {
        [detailPickBtn, detailRepickBtn, requestBtn, withdrawBtn, confirmOk, confirmCancel].forEach(function(btn) {
            if (btn) btn.disabled = disabled;
        });
    }

    // ---- モーダル操作 ----

    function closeModal() {
        detailModal.style.display = 'none';
        hideWithdrawConfirm();
        if (detailFile) detailFile.value = '';
        if (detailFileHint) detailFileHint.textContent = '';
        if (detailDropzone) detailDropzone.classList.remove('is-dragover');
        clearPreview();
        revokeObjectUrl();
        selectedFile = null;
    }

    function showToast(message) {
        if (!toast) return;
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(function() { toast.style.display = 'none'; }, 3000);
    }

    function resetToUnsubmitted() {
        modalState = 'unsubmitted';
        currentDocStatus = 'not_submitted';
        currentDocUrl = '';
        currentDocExpiredAt = '';
        // 状態遷移後のフラグ更新（取り下げ後は提出可・取り下げ不可）
        currentCanRequestReview  = true;
        currentCanWithdrawReview = false;
        revokeObjectUrl();
        selectedFile = null;
        if (detailFile) detailFile.value = '';
        setExpireValue('');
        clearPreview();
        setUiByState();
    }

    // ---- カードクリック → モーダルを開く ----

    cards.forEach(function(card) {
        card.addEventListener('click', function() {
            currentDocStatus         = card.getAttribute('data-doc-status') || 'not_submitted';
            currentDocUrl            = card.getAttribute('data-doc-url') || '';
            currentDocType           = card.getAttribute('data-doc-key') || '';
            currentDocExpiredAt      = card.getAttribute('data-doc-expired-at') || '';
            currentDocUpdatedAt      = card.getAttribute('data-doc-updated') || '';
            currentCanRequestReview  = card.getAttribute('data-can-request-review') === '1';
            currentCanWithdrawReview = card.getAttribute('data-can-withdraw-review') === '1';

            var statusLabel = card.getAttribute('data-doc-status-label') || '';
            var ngReason    = card.getAttribute('data-ng-reason') || '';

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

            revokeObjectUrl();
            selectedFile = null;
            setExpireValue(currentDocExpiredAt);

            // 状態と初期プレビューを決定
            if (currentDocStatus === 'pending' || currentDocStatus === 'approved') {
                modalState = 'submitted';
                if (currentDocUrl) {
                    renderPreview(currentDocUrl, isPdfByUrl(currentDocUrl), fileNameFromUrl(currentDocUrl), currentDocStatus);
                }
            } else if (currentDocUrl && currentCanRequestReview) {
                // ファイルはあるが未提出（差し戻し後など）
                modalState = 'selecting';
                renderPreview(currentDocUrl, isPdfByUrl(currentDocUrl), fileNameFromUrl(currentDocUrl), null);
            } else {
                modalState = 'unsubmitted';
                currentDocUrl = '';
                clearPreview();
            }

            setUiByState();
            detailModal.style.display = 'flex';
        });
    });

    // ---- モーダル閉じる ----

    detailModal.addEventListener('click', function(e) { if (e.target === detailModal) closeModal(); });
    if (detailCloseBtn) detailCloseBtn.addEventListener('click', closeModal);

    // ---- ファイル選択ボタン ----

    if (detailPickBtn && detailFile)   detailPickBtn.addEventListener('click', function() { detailFile.click(); });
    if (detailRepickBtn && detailFile) detailRepickBtn.addEventListener('click', function() { detailFile.click(); });

    // ---- ファイル変更（input[type=file]） ----

    if (detailFile) {
        detailFile.addEventListener('change', function() {
            if (!detailFile.files || !detailFile.files.length) return;
            revokeObjectUrl();
            selectedFile = detailFile.files[0];
            var isPdf = selectedFile.type === 'application/pdf';
            var objUrl = URL.createObjectURL(selectedFile);
            objectUrlToRevoke = objUrl;
            renderPreview(objUrl, isPdf, selectedFile.name, null);
            modalState = 'selecting';
            setUiByState();
        });
    }

    // ---- 有効期限変更 ----

    if (detailExpiredAt) {
        detailExpiredAt.addEventListener('change', updateSubmitButtonState);
        detailExpiredAt.addEventListener('input', updateSubmitButtonState);
    }

    // ---- 提出ボタン ----

    if (requestBtn && detailType) {
        requestBtn.addEventListener('click', function() {
            var t = detailType.value || '';
            if (t === 'business' && (!detailExpiredAt || !detailExpiredAt.value)) {
                alert('営業許可証の有効期限を入力してください。');
                if (detailExpiredAt) detailExpiredAt.focus();
                return;
            }
            if (!selectedFile && !currentDocUrl) {
                alert('ファイルを選択してください。');
                return;
            }

            var expiredAtValue = (t === 'business' && detailExpiredAt) ? detailExpiredAt.value : '';

            var doSubmit = function() {
                postReviewAction(
                    '{{ route("shop.mypage.documents.request-review") }}',
                    t,
                    expiredAtValue
                ).then(function() {
                    // 提出成功: フラグ更新（提出不可・取り下げ可に切り替え）
                    modalState = 'submitted';
                    currentDocStatus = 'pending';
                    currentDocExpiredAt = expiredAtValue || currentDocExpiredAt;
                    currentCanRequestReview  = false;
                    currentCanWithdrawReview = true;

                    // プレビューに「審査待ち」バッジを付与
                    var previewUrl  = objectUrlToRevoke || currentDocUrl;
                    var previewIsPdf = selectedFile ? selectedFile.type === 'application/pdf' : isPdfByUrl(currentDocUrl);
                    var previewName  = selectedFile ? selectedFile.name : fileNameFromUrl(currentDocUrl);
                    renderPreview(previewUrl, previewIsPdf, previewName, 'pending');

                    setUiByState();
                    showToast('提出が完了しました。審査をお待ちください。');
                });
            };

            if (selectedFile) {
                uploadFile(selectedFile).then(function(res) {
                    currentDocUrl = res.view_url || currentDocUrl;
                    doSubmit();
                }).catch(function(error) {
                    var messages = error && error.errors ? Object.values(error.errors).flat() : [];
                    alert(messages[0] || (error && error.message) || 'アップロードに失敗しました。');
                });
                return;
            }

            doSubmit();
        });
    }

    // ---- 提出取り下げボタン → インライン確認表示 ----

    if (withdrawBtn) {
        withdrawBtn.addEventListener('click', function() {
            showWithdrawConfirm();
        });
    }

    // ---- 取り下げ確認: キャンセル ----

    if (confirmCancel) {
        confirmCancel.addEventListener('click', function() {
            hideWithdrawConfirm();
        });
    }

    // ---- 取り下げ確認: 取り下げる ----

    if (confirmOk && detailType) {
        confirmOk.addEventListener('click', function() {
            postReviewAction(
                '{{ route("shop.mypage.documents.withdraw-review") }}',
                detailType.value || '',
                ''
            ).then(function() {
                resetToUnsubmitted();
                showToast('提出を取り下げました。');
            });
        });
    }

    // ---- ドラッグ＆ドロップ ----

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
            revokeObjectUrl();
            selectedFile = f;
            var isPdf = f.type === 'application/pdf';
            var objUrl = URL.createObjectURL(f);
            objectUrlToRevoke = objUrl;
            renderPreview(objUrl, isPdf, f.name, null);
            modalState = 'selecting';
            setUiByState();
        });
    }
})();
</script>
