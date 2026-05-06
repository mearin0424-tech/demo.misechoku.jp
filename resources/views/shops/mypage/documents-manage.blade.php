@extends('layouts.app')

@section('title', '許可証の管理')
@section('body-class', 'page-shop-mypage shop-mypage-v2 page-shop-documents-manage')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/shop-license-documents.css') }}?v=20260506">
@endpush

@section('content')
@php
    $s = $document['status'] ?? 'not_submitted';
    $record = $document['record'] ?? null;
    $canUploadOrSubmit = $record === null || !empty($record['can_request_review']);
    $canWithdrawReview = !empty($record['can_withdraw_review']);
    $isBusiness = ($document['key'] ?? '') === 'business';

    $uiWithdraw = $canWithdrawReview;
    $uiSelectingServer = $canUploadOrSubmit && in_array($s, ['draft', 'rejected'], true);
    $uiUnsubmitted = !$uiWithdraw && !$uiSelectingServer && ($s === 'not_submitted' || $record === null);

    $serverFileUrl = $record['file_url'] ?? '';
    $serverIsPdf = !empty($record['file_is_pdf']);
    $expiryVal = $record['expired_at'] ?? '';
    $updatedLabel = $record['updated_at_label'] ?? '';

    $flowHeaderClass = 'license-manage-flow is-unsubmitted';
    $flowHeaderText = '未提出';
    if ($uiWithdraw) {
        $flowHeaderClass = $s === 'approved' ? 'license-manage-flow is-approved-flow' : 'license-manage-flow is-uploaded-flow';
        $flowHeaderText = $s === 'approved' ? '承認済み' : 'アップロード済み';
    } elseif ($uiSelectingServer) {
        $flowHeaderClass = 'license-manage-flow is-selecting';
        $flowHeaderText = 'ファイル選択中';
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

        @if($isBusiness)
            {{-- 営業許可証：モーダル風パネル（未提出 / 選択中 / 提出済み・取り下げ） --}}
            <div
                id="license-business-root"
                class="license-manage-shell"
                data-csrf="{{ csrf_token() }}"
                data-upload-url="{{ route('shop.mypage.documents.upload') }}"
                data-review-url="{{ route('shop.mypage.documents.request-review') }}"
                data-doc-type="business"
                data-initial-unsubmitted="{{ $uiUnsubmitted ? '1' : '0' }}"
                data-initial-selecting-server="{{ $uiSelectingServer ? '1' : '0' }}"
                data-has-server-file="{{ ($uiSelectingServer && $serverFileUrl !== '') ? '1' : '0' }}"
                data-server-file-url="{{ $serverFileUrl }}"
                data-server-is-pdf="{{ $serverIsPdf ? '1' : '0' }}"
            >
                <div class="license-manage-card">
                    <header class="license-manage-header">
                        <div class="license-manage-header__titles">
                            <h1 class="license-manage-title">営業許可証</h1>
                            <span id="license-business-flow" class="{{ $flowHeaderClass }}">{{ $flowHeaderText }}</span>
                        </div>
                        <a href="{{ route('shop.mypage.index') }}" class="license-manage-close" aria-label="閉じる">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </a>
                    </header>

                    <div class="license-manage-body">
                        @if($s === 'rejected' && !empty($record['ng_reason']))
                            <p class="license-manage-ng">差し戻し理由: {{ $record['ng_reason'] }}</p>
                        @endif

                        @if(!empty($record['expiring_soon']))
                            <p class="license-manage-expiring-chip">{{ $record['expiration_notice_label'] ?? '更新期限半年以内' }}</p>
                        @endif

                        <div id="license-business-meta" class="license-manage-meta" @if(!$uiSelectingServer && !$uiWithdraw) style="display:none;" @endif>
                            <p>最終更新: <span id="license-meta-updated">{{ $uiWithdraw || $uiSelectingServer ? ($updatedLabel !== '' ? $updatedLabel : '----/--/-- --:--') : '----/--/-- --:--' }}</span></p>
                            <p>登録済み有効期限: <span id="license-meta-expiry">{{ $expiryVal !== '' ? $expiryVal : '未登録' }}</span></p>
                        </div>

                        <div id="license-business-preview-wrap" class="license-manage-preview-wrap" @if(!$uiSelectingServer && !$uiWithdraw) style="display:none;" @endif>
                            <div id="license-business-preview-inner" class="license-manage-preview-inner">
                                @if($uiSelectingServer || $uiWithdraw)
                                    @if($serverIsPdf)
                                        <div class="license-manage-preview-tile license-manage-preview-tile--pdf">
                                            <svg class="license-manage-preview-tile__doc" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            <span class="license-manage-preview-tile__fname">PDF</span>
                                        </div>
                                    @else
                                        <div class="license-manage-preview-tile">
                                            <img src="{{ $serverFileUrl }}" alt="アップロード済みのプレビュー" class="license-manage-preview-tile__img">
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div id="license-business-expiry-block" class="license-manage-expiry-block" @if(!$uiSelectingServer && !$uiWithdraw) style="display:none;" @endif>
                            <label class="license-manage-label" for="license-business-expired-at">営業許可証の有効期限</label>
                            <div class="license-manage-date-wrap">
                                <input type="date" id="license-business-expired-at" name="expired_at" class="license-manage-date-input"
                                    value="{{ $expiryVal }}"
                                    min="{{ now()->format('Y-m-d') }}"
                                    @if($uiWithdraw) readonly @endif
                                    style="color-scheme: dark;">
                                <span class="license-manage-date-icon" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </span>
                            </div>
                            <p id="license-business-expiry-hint" class="license-manage-hint" @if($uiWithdraw) style="display:none;" @endif>
                                営業許可証の有効期限を年月日形式で入力してください（本日以降）。
                            </p>
                        </div>

                        <div class="license-manage-dropzone">
                            <input type="file" id="license-business-file" class="sr-only" accept=".pdf,.png,.jpg,.jpeg,image/*,application/pdf" tabindex="-1">

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
                                    <button type="button" id="license-business-withdraw-open" class="license-manage-btn license-manage-btn--danger-outline">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        提出取り下げ
                                    </button>
                                </div>
                            @else
                                <div id="license-business-state-unsubmitted" class="license-manage-state license-manage-state--empty" @if(!$uiUnsubmitted) style="display:none;" @endif>
                                    <svg class="license-manage-state__upload" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <p class="license-manage-state__text">
                                        営業許可証の画像、またはPDFファイルをアップロードしてください。<br>
                                        内容がはっきりと読み取れることを確認してください。
                                    </p>
                                    <button type="button" id="license-business-pick-first" class="license-manage-btn license-manage-btn--primary-wide">
                                        ファイルを選択
                                    </button>
                                </div>

                                <div id="license-business-state-selecting" class="license-manage-state license-manage-state--actions" @if(!$uiSelectingServer) style="display:none;" @endif>
                                    <div class="license-manage-action-row">
                                        <button type="button" id="license-business-pick-again" class="license-manage-btn license-manage-btn--secondary-line">
                                            ファイルを変更
                                        </button>
                                        <button type="button" id="license-business-submit" class="license-manage-btn license-manage-btn--primary-wide">
                                            提出
                                        </button>
                                    </div>
                                    <p id="license-business-submit-hint" class="license-manage-footnote"></p>
                                </div>
                            @endif
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
                                <input type="hidden" name="type" value="business">
                                <button type="submit" class="license-manage-btn license-manage-btn--danger-fill">取り下げる</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shop-doc-onboarding-actions">
                <a href="{{ route('shop.mypage.index') }}" class="is-muted">マイページへ戻る</a>
            </div>

            @unless($uiWithdraw)
            <script>
            (function () {
                var root = document.getElementById('license-business-root');
                if (!root) return;

                var csrf = root.getAttribute('data-csrf') || '';
                var uploadUrl = root.getAttribute('data-upload-url') || '';
                var reviewUrl = root.getAttribute('data-review-url') || '';
                var docType = root.getAttribute('data-doc-type') || 'business';
                var initialUnsubmitted = root.getAttribute('data-initial-unsubmitted') === '1';
                var initialSelectingServer = root.getAttribute('data-initial-selecting-server') === '1';
                var hasServerFile = root.getAttribute('data-has-server-file') === '1';
                var serverFileUrl = root.getAttribute('data-server-file-url') || '';
                var serverIsPdf = root.getAttribute('data-server-is-pdf') === '1';

                var flowEl = document.getElementById('license-business-flow');
                var metaEl = document.getElementById('license-business-meta');
                var metaUpdated = document.getElementById('license-meta-updated');
                var metaExpiry = document.getElementById('license-meta-expiry');
                var previewWrap = document.getElementById('license-business-preview-wrap');
                var previewInner = document.getElementById('license-business-preview-inner');
                var expiryBlock = document.getElementById('license-business-expiry-block');
                var expiryInput = document.getElementById('license-business-expired-at');
                var expiryHint = document.getElementById('license-business-expiry-hint');
                var fileInput = document.getElementById('license-business-file');
                var stateUnsub = document.getElementById('license-business-state-unsubmitted');
                var stateSel = document.getElementById('license-business-state-selecting');
                var pickFirst = document.getElementById('license-business-pick-first');
                var pickAgain = document.getElementById('license-business-pick-again');
                var submitBtn = document.getElementById('license-business-submit');
                var submitHint = document.getElementById('license-business-submit-hint');

                var objectUrl = null;
                var localSelecting = false;

                function setFlowUnsubmitted() {
                    if (!flowEl) return;
                    flowEl.className = 'license-manage-flow is-unsubmitted';
                    flowEl.textContent = '未提出';
                }
                function setFlowSelecting() {
                    if (!flowEl) return;
                    flowEl.className = 'license-manage-flow is-selecting';
                    flowEl.textContent = 'ファイル選択中';
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
                    if (file.type === 'application/pdf') {
                        var tile = document.createElement('div');
                        tile.className = 'license-manage-preview-tile license-manage-preview-tile--pdf';
                        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                        svg.setAttribute('class', 'license-manage-preview-tile__doc');
                        svg.setAttribute('width', '40');
                        svg.setAttribute('height', '40');
                        svg.setAttribute('viewBox', '0 0 24 24');
                        svg.setAttribute('fill', 'none');
                        svg.setAttribute('stroke', 'currentColor');
                        svg.setAttribute('stroke-width', '1.5');
                        tile.appendChild(svg);
                        var p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        p.setAttribute('d', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z');
                        svg.appendChild(p);
                        var poly = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
                        poly.setAttribute('points', '14 2 14 8 20 8');
                        svg.appendChild(poly);
                        var fn = document.createElement('span');
                        fn.className = 'license-manage-preview-tile__fname';
                        fn.textContent = file.name || 'PDF';
                        tile.appendChild(fn);
                        previewInner.appendChild(tile);
                    } else {
                        objectUrl = URL.createObjectURL(file);
                        var t2 = document.createElement('div');
                        t2.className = 'license-manage-preview-tile';
                        var img = document.createElement('img');
                        img.src = objectUrl;
                        img.alt = 'プレビュー';
                        img.className = 'license-manage-preview-tile__img';
                        t2.appendChild(img);
                        previewInner.appendChild(t2);
                    }
                }

                function showSelectingUi() {
                    localSelecting = true;
                    if (stateUnsub) stateUnsub.style.display = 'none';
                    if (stateSel) stateSel.style.display = 'block';
                    if (metaEl) metaEl.style.display = 'block';
                    if (previewWrap) previewWrap.style.display = 'block';
                    if (expiryBlock) expiryBlock.style.display = 'block';
                    if (expiryHint) expiryHint.style.display = 'block';
                    if (expiryInput) expiryInput.removeAttribute('readonly');
                    setFlowSelecting();
                    syncMetaExpiry();
                    updateSubmitEnabled();
                }

                function syncMetaExpiry() {
                    if (!metaExpiry || !expiryInput) return;
                    metaExpiry.textContent = expiryInput.value ? expiryInput.value : '未登録';
                }

                function updateSubmitEnabled() {
                    if (!submitBtn || !expiryInput) return;
                    var ok = !!expiryInput.value;
                    var hasFile = (fileInput && fileInput.files && fileInput.files.length > 0) || (hasServerFile && initialSelectingServer && !localSelecting);
                    if (localSelecting) {
                        hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                    }
                    submitBtn.disabled = !(ok && hasFile);
                    submitBtn.classList.toggle('is-disabled', submitBtn.disabled);
                    if (submitHint) {
                        submitHint.textContent = submitBtn.disabled && ok && !hasFile ? 'ファイルを選択してください。' : '';
                    }
                }

                function openPick() {
                    if (fileInput) fileInput.click();
                }

                if (pickFirst) pickFirst.addEventListener('click', openPick);
                if (pickAgain) pickAgain.addEventListener('click', openPick);

                if (expiryInput) {
                    expiryInput.addEventListener('input', function () {
                        syncMetaExpiry();
                        updateSubmitEnabled();
                    });
                    expiryInput.addEventListener('change', function () {
                        syncMetaExpiry();
                        updateSubmitEnabled();
                    });
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
                    syncMetaExpiry();
                    updateSubmitEnabled();
                }

                function parseJsonSafe(r) {
                    return r.json().catch(function () { return {}; });
                }

                if (submitBtn) {
                    submitBtn.addEventListener('click', function () {
                        if (!expiryInput || !expiryInput.value) {
                            alert('営業許可証の有効期限を入力してください。');
                            expiryInput.focus();
                            return;
                        }
                        var file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
                        if (!file && !hasServerFile) {
                            alert('ファイルを選択してください。');
                            return;
                        }

                        submitBtn.disabled = true;

                        var expiredAt = expiryInput.value;
                        var chain = Promise.resolve();

                        if (file) {
                            var fd = new FormData();
                            fd.append('_token', csrf);
                            fd.append('type', docType);
                            fd.append('file', file);
                            fd.append('expired_at', expiredAt);
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
                            return fetch(reviewUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({ type: docType, expired_at: expiredAt })
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

                if (initialUnsubmitted) {
                    setFlowUnsubmitted();
                }
            })();
            </script>
            @endunless

            @if($uiWithdraw)
            <script>
            (function () {
                var openBtn = document.getElementById('license-business-withdraw-open');
                var overlay = document.getElementById('license-withdraw-overlay');
                var cancelBtn = document.getElementById('license-withdraw-cancel');
                if (!openBtn || !overlay) return;
                openBtn.addEventListener('click', function () {
                    overlay.style.display = 'flex';
                });
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function () {
                        overlay.style.display = 'none';
                    });
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

        @else
            {{-- 風営許可証：従来レイアウト --}}
            <h1 class="mypage-shop-name serif-font shop-mypage-store-title">{{ $document['name'] ?? '許可証' }}</h1>

            <div class="shop-mypage-section document-section">
                <div class="shop-mypage-license-card">
                    <div class="shop-mypage-license-card-body">
                        <p class="document-upload-name">{{ $document['name'] ?? '許可証' }}</p>
                        <div class="document-status-row">
                            <span class="document-status-chip is-{{ str_replace('_', '-', $s) }}">
                                {{ $document['status_label'] ?? '未提出' }}
                            </span>
                        </div>
                        <p class="document-upload-meta">
                            最終更新: {{ $record ? ($record['updated_at_label'] ?? '—') : '—' }}
                        </p>
                        @if($s === 'rejected' && $record && !empty($record['ng_reason']))
                            <p class="license-upload-modal__ng">差し戻し理由: {{ $record['ng_reason'] }}</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($record && !empty($record['file_url']))
                <p style="margin: 12px 0 20px;">
                    <a href="{{ $record['file_url'] }}" target="_blank" rel="noopener noreferrer" class="is-muted">現在のファイルを確認する</a>
                </p>
            @endif

            @if($canUploadOrSubmit)
                <form method="post" action="{{ route('shop.mypage.documents.upload') }}" enctype="multipart/form-data" style="margin-bottom:14px;">
                    @csrf
                    <input type="hidden" name="type" value="{{ $document['key'] }}">
                    <label for="license-file" class="license-upload-modal__expired-label">ファイルを選択（PDF/JPG/PNG, 8MBまで）</label>
                    <input id="license-file" type="file" name="file" required accept=".pdf,.png,.jpg,.jpeg,image/*,application/pdf">
                    <div class="shop-doc-onboarding-actions" style="margin-top:12px;">
                        <button type="submit" class="is-primary">ファイルをアップロード</button>
                    </div>
                </form>

                <form method="post" action="{{ route('shop.mypage.documents.request-review') }}" style="margin-bottom:14px;">
                    @csrf
                    <input type="hidden" name="type" value="{{ $document['key'] }}">
                    <div class="shop-doc-onboarding-actions" style="margin-top:12px;">
                        <button type="submit" class="is-primary">提出する</button>
                    </div>
                </form>
            @endif

            @if($canWithdrawReview)
                <form method="post" action="{{ route('shop.mypage.documents.withdraw-review') }}" onsubmit="return confirm('提出を取り下げます。よろしいですか？');">
                    @csrf
                    <input type="hidden" name="type" value="{{ $document['key'] }}">
                    <div class="shop-doc-onboarding-actions">
                        <button type="submit" class="is-secondary">提出取り下げ</button>
                    </div>
                </form>
            @endif

            <div class="shop-doc-onboarding-actions">
                <a href="{{ route('shop.mypage.index') }}" class="is-muted">マイページへ戻る</a>
            </div>
        @endif
    </section>
</div>
@endsection
