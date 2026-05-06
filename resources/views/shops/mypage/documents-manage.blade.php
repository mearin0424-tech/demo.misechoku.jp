@extends('layouts.app')

@section('title', ($document['name'] ?? '許可証') . 'の提出')
@section('body-class', 'page-shop-mypage shop-mypage-v2 page-shop-documents-manage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/shop-license-documents.css') }}?v=20260511">
@endpush

@section('content')
@php
    $s = $document['status'] ?? 'not_submitted';
    $record = $document['record'] ?? null;
    $rec = $record ?? [];
    $canUploadOrSubmit = $record === null || !empty($rec['can_request_review']);
    $canWithdrawReview = !empty($rec['can_withdraw_review']);
    $isBusiness = ($document['key'] ?? '') === 'business';
    $licenseDocName = $document['name'] ?? ($isBusiness ? '営業許可証' : '風営許可証');

    $uiWithdraw = $canWithdrawReview;
    $uiSelectingServer = $canUploadOrSubmit && in_array($s, ['draft', 'rejected'], true);
    $uiUnsubmitted = !$uiWithdraw && !$uiSelectingServer && ($s === 'not_submitted' || $record === null);

    $serverFileUrl = $rec['file_url'] ?? '';
    $serverFileName = trim((string) ($rec['file_name'] ?? ''));
    $expiryVal = trim((string) ($rec['expired_at'] ?? ''));
    $updatedLabel = $rec['updated_at_label'] ?? '';
    $approvedAtLabel = $rec['approved_at'] ?? '';
    $showBusinessExpiryBlock = $isBusiness && (!$uiUnsubmitted || $uiSelectingServer || $uiWithdraw || $expiryVal !== '');

    $flowHeaderClass = 'license-manage-flow is-unsubmitted';
    $flowHeaderText = '未提出';
    if ($s === 'approved') {
        $flowHeaderClass = 'license-manage-flow is-approved-flow';
        $flowHeaderText = '承認済み';
    } elseif ($s === 'pending') {
        $flowHeaderClass = 'license-manage-flow is-uploaded-flow';
        $flowHeaderText = '提出済み（審査中）';
    }
@endphp

<div class="mypage-page contents inner animate-fadeIn shop-mypage-v2">
    <section class="mypage-area">
        @if(session('message'))
            <div id="license-page-toast" class="license-manage-toast" role="status">
                <svg class="license-manage-toast__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="profile-edit-errors license-manage-flash-errors">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div
            id="license-doc-root"
            class="license-manage-shell"
            data-csrf="{{ csrf_token() }}"
            data-upload-url="{{ route('shop.mypage.documents.upload') }}"
            data-review-url="{{ route('shop.mypage.documents.request-review') }}"
            data-doc-type="{{ $document['key'] }}"
            data-is-business="{{ $isBusiness ? '1' : '0' }}"
            data-initial-unsubmitted="{{ $uiUnsubmitted ? '1' : '0' }}"
            data-initial-selecting-server="{{ $uiSelectingServer ? '1' : '0' }}"
            data-has-server-file="{{ (($uiSelectingServer || $uiWithdraw) && $serverFileUrl !== '') ? '1' : '0' }}"
            data-server-file-url="{{ $serverFileUrl }}"
        >
            <div class="license-manage-card">
                <header class="license-manage-header license-manage-header--status-only">
                    <p class="license-manage-status-kv">
                        <span class="license-manage-status-kv__key">status</span><span class="license-manage-status-kv__colon">:</span>
                        <span id="license-doc-flow" class="{{ $flowHeaderClass }}">{{ $flowHeaderText }}</span>
                    </p>
                </header>

                <div class="license-manage-body">
                    @if($s === 'rejected' && !empty($rec['ng_reason']))
                        <p class="license-manage-ng">差し戻し理由: {{ $rec['ng_reason'] }}</p>
                    @endif

                    <div id="license-doc-file-row" class="license-manage-file-row" @if(!$uiSelectingServer && !$uiWithdraw) style="display:none;" @endif>
                        <div class="license-manage-file-stack">
                            <span class="license-manage-file-icon" aria-hidden="true">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </span>
                            <div id="license-doc-preview-inner" class="license-manage-filename-area">
                                @if($uiSelectingServer || $uiWithdraw)
                                    <div class="license-manage-filename-box">
                                        <a href="{{ $serverFileUrl }}" target="_blank" rel="noopener noreferrer" class="license-manage-filename-link">{{ $serverFileName !== '' ? $serverFileName : 'アップロード済みファイル' }}</a>
                                    </div>
                                @endif
                            </div>
                            <div id="license-doc-meta" class="license-manage-preview-meta license-manage-preview-meta--stacked">
                                <p class="license-manage-meta-updated">最終更新: <span id="license-meta-updated">{{ $uiWithdraw || $uiSelectingServer ? ($updatedLabel !== '' ? $updatedLabel : '----/--/-- --:--') : '----/--/-- --:--' }}</span></p>
                                @if($s === 'approved' && $approvedAtLabel !== '')
                                    <p class="license-manage-approved-at">承認日: {{ $approvedAtLabel }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($isBusiness)
                        <div id="license-doc-expiry-block" class="license-manage-expiry-block" @if(!$showBusinessExpiryBlock) style="display:none;" @endif>
                            <label class="license-manage-label" for="license-doc-expired-at">営業許可証の有効期限</label>
                            <div class="license-manage-date-wrap">
                                <input type="date" id="license-doc-expired-at" name="expired_at" class="license-manage-date-input"
                                    value="{{ $expiryVal }}"
                                    @if(!$uiWithdraw && $expiryVal === '') min="{{ now()->format('Y-m-d') }}" @endif
                                    @if($uiWithdraw) readonly @endif
                                    style="color-scheme: dark;">
                                <span class="license-manage-date-icon" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </span>
                            </div>
                            <p id="license-doc-expiry-hint" class="license-manage-hint" @if($uiWithdraw) style="display:none;" @endif>
                                営業許可証の有効期限を年月日形式で入力してください（本日以降）。
                            </p>
                            @if(!empty($rec['expiring_soon']))
                                <p class="license-manage-expiring-chip">{{ $rec['expiration_notice_label'] ?? '更新期限半年以内' }}</p>
                            @endif
                        </div>
                    @endif

                    <div class="license-manage-dropzone">
                        <input type="file" id="license-doc-file" class="sr-only" accept=".pdf,.png,.jpg,.jpeg,image/*,application/pdf" tabindex="-1">

                        @if($uiWithdraw)
                            <div class="license-manage-state license-manage-state--submitted">
                                <svg class="license-manage-state__icon" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                @if($s === 'approved')
                                    <p class="license-manage-state__text">
                                        承認済みです。ファイルを再アップロードする場合は、一度提出を取り下げてください。
                                    </p>
                                @else
                                    <p class="license-manage-state__text">
                                        現在、審査待ちです。承認されるまでしばらくお待ちください。<br>
                                        ファイルを再アップロードする場合は、一度提出を取り下げてください。
                                    </p>
                                @endif
                                <button type="button" id="license-doc-withdraw-open" class="license-manage-btn license-manage-btn--danger-outline">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    提出取り下げ
                                </button>
                            </div>
                        @else
                            <div id="license-doc-state-unsubmitted" class="license-manage-state license-manage-state--empty" @if(!$uiUnsubmitted) style="display:none;" @endif>
                                <svg class="license-manage-state__upload" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <p class="license-manage-state__text">
                                    {{ $licenseDocName }}の画像、またはPDFファイルをアップロードしてください。<br>
                                    内容がはっきりと読み取れることを確認してください。
                                </p>
                                <button type="button" id="license-doc-pick-first" class="license-manage-btn license-manage-btn--primary-wide">
                                    ファイルを選択
                                </button>
                            </div>

                            <div id="license-doc-state-selecting" class="license-manage-state license-manage-state--actions" @if(!$uiSelectingServer) style="display:none;" @endif>
                                <div class="license-manage-action-row">
                                    <button type="button" id="license-doc-pick-again" class="license-manage-btn license-manage-btn--secondary-line">
                                        ファイルを変更
                                    </button>
                                    <button type="button" id="license-doc-submit" class="license-manage-btn license-manage-btn--primary-wide">
                                        提出
                                    </button>
                                </div>
                                <p id="license-doc-submit-hint" class="license-manage-footnote"></p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div id="license-withdraw-overlay" class="license-manage-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="license-withdraw-title">
            <div class="license-manage-modal-panel">
                <div class="license-manage-modal-head">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <h2 id="license-withdraw-title" class="license-manage-modal-title">提出の取り下げ</h2>
                </div>
                <p class="license-manage-modal-text">
                    アップロードしたファイルは削除されます。提出を取り下げますか？
                </p>
                <div class="license-manage-modal-actions">
                    <button type="button" id="license-withdraw-cancel" class="license-manage-btn license-manage-btn--ghost">キャンセル</button>
                    <form method="post" action="{{ route('shop.mypage.documents.withdraw-review') }}" class="license-manage-withdraw-form">
                        @csrf
                        <input type="hidden" name="type" value="{{ $document['key'] }}">
                        <button type="submit" class="license-manage-btn license-manage-btn--danger-fill">取り下げる</button>
                    </form>
                </div>
            </div>
        </div>

        @unless($uiWithdraw)
        <script>
        (function () {
            var root = document.getElementById('license-doc-root');
            if (!root) return;

            var csrf = root.getAttribute('data-csrf') || '';
            var uploadUrl = root.getAttribute('data-upload-url') || '';
            var reviewUrl = root.getAttribute('data-review-url') || '';
            var docType = root.getAttribute('data-doc-type') || '';
            var isBusiness = root.getAttribute('data-is-business') === '1';
            var initialUnsubmitted = root.getAttribute('data-initial-unsubmitted') === '1';
            var initialSelectingServer = root.getAttribute('data-initial-selecting-server') === '1';
            var hasServerFileInitial = root.getAttribute('data-has-server-file') === '1';

            var flowEl = document.getElementById('license-doc-flow');
            var fileRow = document.getElementById('license-doc-file-row');
            var previewInner = document.getElementById('license-doc-preview-inner');
            var expiryBlock = document.getElementById('license-doc-expiry-block');
            var expiryInput = document.getElementById('license-doc-expired-at');
            var expiryHint = document.getElementById('license-doc-expiry-hint');
            var fileInput = document.getElementById('license-doc-file');
            var stateUnsub = document.getElementById('license-doc-state-unsubmitted');
            var stateSel = document.getElementById('license-doc-state-selecting');
            var pickFirst = document.getElementById('license-doc-pick-first');
            var pickAgain = document.getElementById('license-doc-pick-again');
            var submitBtn = document.getElementById('license-doc-submit');
            var submitHint = document.getElementById('license-doc-submit-hint');

            var objectUrl = null;
            var localSelecting = false;
            var hasServerFile = hasServerFileInitial;

            function setFlowUnsubmitted() {
                if (!flowEl) return;
                flowEl.className = 'license-manage-flow is-unsubmitted';
                flowEl.textContent = '未提出';
            }
            function setFlowSelecting() {
                if (!flowEl) return;
                flowEl.className = 'license-manage-flow is-unsubmitted';
                flowEl.textContent = '未提出';
            }

            function revokePreview() {
                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }
            }

            function renderClientPreview(file) {
                if (!previewInner) return;
                revokePreview();
                previewInner.innerHTML = '';
                var fname = (file && file.name) ? file.name : '選択したファイル';
                var box = document.createElement('div');
                box.className = 'license-manage-filename-box';
                var span = document.createElement('span');
                span.className = 'license-manage-filename-text';
                span.textContent = fname;
                box.appendChild(span);
                previewInner.appendChild(box);
            }

            function showSelectingUi() {
                localSelecting = true;
                if (stateUnsub) stateUnsub.style.display = 'none';
                if (stateSel) stateSel.style.display = 'block';
                if (fileRow) fileRow.style.display = 'flex';
                if (isBusiness) {
                    if (expiryBlock) expiryBlock.style.display = 'block';
                    if (expiryHint) expiryHint.style.display = 'block';
                    if (expiryInput) expiryInput.removeAttribute('readonly');
                }
                setFlowSelecting();
                updateSubmitEnabled();
            }

            function updateSubmitEnabled() {
                if (!submitBtn) return;
                var hasFile = (fileInput && fileInput.files && fileInput.files.length > 0) || (hasServerFile && initialSelectingServer && !localSelecting);
                if (localSelecting) {
                    hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                }
                var validExpiry = !isBusiness || (expiryInput && !!expiryInput.value);
                submitBtn.disabled = !(hasFile && validExpiry);
                submitBtn.classList.toggle('is-disabled', submitBtn.disabled);
                if (submitHint) {
                    if (!hasFile) {
                        submitHint.textContent = 'ファイルを選択してください。';
                    } else if (!validExpiry) {
                        submitHint.textContent = '営業許可証の有効期限を入力してください。';
                    } else {
                        submitHint.textContent = '';
                    }
                }
            }

            function openPick() {
                if (fileInput) fileInput.click();
            }
            if (pickFirst) pickFirst.addEventListener('click', openPick);
            if (pickAgain) pickAgain.addEventListener('click', openPick);

            if (expiryInput) {
                expiryInput.addEventListener('input', updateSubmitEnabled);
                expiryInput.addEventListener('change', updateSubmitEnabled);
            }

            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    if (!fileInput.files || !fileInput.files.length) return;
                    showSelectingUi();
                    renderClientPreview(fileInput.files[0]);
                    hasServerFile = false;
                    updateSubmitEnabled();
                });
            }

            if (initialSelectingServer) {
                updateSubmitEnabled();
            }

            function parseJsonSafe(r) {
                return r.json().catch(function () { return {}; });
            }

            if (submitBtn) {
                submitBtn.addEventListener('click', function () {
                    if (isBusiness && (!expiryInput || !expiryInput.value)) {
                        alert('営業許可証の有効期限を入力してください。');
                        if (expiryInput) expiryInput.focus();
                        return;
                    }
                    var file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
                    if (!file && !hasServerFile) {
                        alert('ファイルを選択してください。');
                        return;
                    }

                    submitBtn.disabled = true;
                    var expiredAt = isBusiness && expiryInput ? expiryInput.value : '';
                    var chain = Promise.resolve();

                    if (file) {
                        var fd = new FormData();
                        fd.append('_token', csrf);
                        fd.append('type', docType);
                        fd.append('file', file);
                        if (isBusiness) fd.append('expired_at', expiredAt);
                        chain = chain.then(function () {
                            return fetch(uploadUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: fd
                            }).then(function (r) {
                                return parseJsonSafe(r).then(function (json) {
                                    if (!r.ok) throw json;
                                    return json;
                                });
                            });
                        });
                    }

                    chain.then(function () {
                        var body = { type: docType };
                        if (isBusiness) body.expired_at = expiredAt;
                        return fetch(reviewUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(body)
                        }).then(function (r) {
                            return parseJsonSafe(r).then(function (json) {
                                if (!r.ok) throw json;
                                return json;
                            });
                        });
                    }).then(function () {
                        window.location.reload();
                    }).catch(function (err) {
                        var msgs = err && err.errors ? Object.values(err.errors).flat() : [];
                        alert(msgs[0] || (err && err.message) || '処理に失敗しました。');
                        submitBtn.disabled = false;
                        updateSubmitEnabled();
                    });
                });
            }

            if (initialUnsubmitted) setFlowUnsubmitted();
        })();
        </script>
        @endunless

        @if($uiWithdraw)
        <script>
        (function () {
            var openBtn = document.getElementById('license-doc-withdraw-open');
            var overlay = document.getElementById('license-withdraw-overlay');
            var cancelBtn = document.getElementById('license-withdraw-cancel');
            if (!openBtn || !overlay) return;
            openBtn.addEventListener('click', function () { overlay.style.display = 'flex'; });
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function () { overlay.style.display = 'none'; });
            }
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) overlay.style.display = 'none';
            });
        })();
        </script>
        @endif

        @if(session('message'))
        <script>
        (function () {
            var t = document.getElementById('license-page-toast');
            if (!t) return;
            setTimeout(function () { t.style.display = 'none'; }, 3000);
        })();
        </script>
        @endif
    </section>
</div>
@endsection
