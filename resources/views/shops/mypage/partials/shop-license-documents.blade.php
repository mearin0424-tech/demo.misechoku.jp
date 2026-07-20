@php
    $documents = $documents ?? [];
@endphp

<div class="shop-mypage-section license-section" id="license-section">
    {{-- タイトル：英字ラベルのみ → アイコン + 日本語 + 一言説明のわかりやすい見出しに --}}
    <div class="license-section__head">
        <h3 class="license-section__title">
            <i class="fas fa-file-shield" aria-hidden="true"></i>許可証の登録
            <span class="license-section__title-en">LICENSES</span>
        </h3>
        <p class="license-section__lead">掲載に必要な書類です。すべて承認されると求人票を公開できます。</p>
    </div>

    @if(session('message'))
        <p class="license-section__flash">{{ session('message') }}</p>
    @endif
    @if($errors->any())
        <div class="license-section__errors">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="license-accordion-list">
        @foreach($documents as $doc)
            @php
                $status        = $doc['status'] ?? 'not_submitted';
                $record        = $doc['record'] ?? [];
                $statusLabel   = $doc['status_label']   ?? '未提出';
                $displayName   = $doc['display_name']   ?? ($doc['name'] ?? '許可証');
                $issuer        = $doc['issuer']         ?? '';
                $description   = $doc['description']    ?? null;
                $descriptionHtml = $doc['description_html'] ?? null;
                $expiryRequired = !empty($doc['expiry_required']);
                $expiryNote    = $doc['expiry_note']    ?? '';
                $supportNote   = $doc['support_note']   ?? null;
                $isApproved    = $status === 'approved';
                $isPending     = $status === 'pending';
                $isRejected    = $status === 'rejected';
                // 承認済み以外は初期展開
                $defaultOpen   = !$isApproved;
                $accordionId   = 'license-acc-' . $doc['key'];

                // 承認済み or 審査中（取り下げ可能）→ withdraw UI
                $showWithdrawUi = !empty($record['can_withdraw_review']);
                // それ以外（未提出 / 差戻し / 一時保存）→ upload UI
                $showUploadUi   = !$showWithdrawUi;

                $serverFileUrl  = $record['file_url']        ?? '';
                $serverFileName = $record['file_name']       ?? '';
                $expiryVal      = $record['expired_at']      ?? '';
                $updatedLabel   = $record['updated_at_label']?? '';
                $approvedAtLabel = $record['approved_at']    ?? '';
                $ngReason       = $record['ng_reason']       ?? '';
                $expiringSoon   = !empty($record['expiring_soon']);
                $expirationNoticeLabel = $record['expiration_notice_label'] ?? '更新期限半年以内';
            @endphp

            <article class="license-accordion is-{{ $status }}"
                     id="{{ $accordionId }}"
                     data-license-accordion
                     data-status="{{ $status }}"
                     data-doc-key="{{ $doc['key'] }}"
                     data-expiry-required="{{ $expiryRequired ? '1' : '0' }}"
                     @if($defaultOpen) data-open="true" @endif>
                <button type="button"
                        class="license-accordion__head"
                        aria-expanded="{{ $defaultOpen ? 'true' : 'false' }}"
                        aria-controls="{{ $accordionId }}-body"
                        data-license-accordion-toggle>
                    <span class="license-accordion__title-block">
                        <span class="license-accordion__title">{{ $displayName }}</span>
                        @if($issuer !== '')
                            <span class="license-accordion__issuer">{{ $issuer }}</span>
                        @endif
                    </span>
                    <span class="license-accordion__status license-accordion__status--{{ str_replace('_','-',$status) }}">
                        {{ $statusLabel }}
                    </span>
                    @if($expiringSoon)
                        <span class="license-accordion__chip">{{ $expirationNoticeLabel }}</span>
                    @endif
                    <span class="license-accordion__chevron" aria-hidden="true">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </button>

                <div class="license-accordion__body" id="{{ $accordionId }}-body" @if(!$defaultOpen) hidden @endif>
                    {{-- 注釈文：常時表示すると文字が多く見づらいため、折りたたみのヒントに格納 --}}
                    @if($descriptionHtml !== null || !empty($description))
                        <details class="license-accordion__hint">
                            <summary><i class="fas fa-circle-info" aria-hidden="true"></i> この書類について</summary>
                            <div class="license-accordion__hint-body">
                                @if($descriptionHtml !== null)
                                    {!! $descriptionHtml !!}
                                @elseif(!empty($description))
                                    <p>{{ $description }}</p>
                                @endif
                            </div>
                        </details>
                    @endif

                    @if($isRejected && !empty($ngReason))
                        <p class="license-accordion__ng">差し戻し理由: {{ $ngReason }}</p>
                    @endif

                    {{-- 承認済み or 審査中：取り下げ UI --}}
                    @if($showWithdrawUi)
                        <div class="license-accordion__panel">
                            <div class="license-accordion__file-row">
                                <span class="license-accordion__file-icon" aria-hidden="true">
                                    <i class="fas fa-file-alt"></i>
                                </span>
                                <div class="license-accordion__file-info">
                                    @if($serverFileUrl !== '')
                                        <a href="{{ $serverFileUrl }}" target="_blank" rel="noopener noreferrer" class="license-accordion__file-link">
                                            {{ $serverFileName !== '' ? $serverFileName : 'アップロード済みファイル' }}
                                        </a>
                                    @else
                                        <span>アップロード済みファイル</span>
                                    @endif
                                    @if($updatedLabel !== '')
                                        <span class="license-accordion__file-meta">最終更新: {{ $updatedLabel }}</span>
                                    @endif
                                    @if($isApproved && $approvedAtLabel !== '')
                                        <span class="license-accordion__file-meta">承認日: {{ $approvedAtLabel }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($expiryVal !== '' || $expiryRequired)
                                <div class="license-accordion__field">
                                    <label class="license-accordion__label">有効期限</label>
                                    <input type="date" class="license-accordion__input" value="{{ $expiryVal }}" readonly>
                                </div>
                            @endif

                            <div class="license-accordion__withdraw-zone">
                                <p class="license-accordion__withdraw-warning">
                                    <i class="fas fa-info-circle"></i>
                                    @if($isApproved)
                                        承認済みです。ファイルを差し替える場合は一度提出を取り下げてください。
                                    @else
                                        審査中です。ファイルを差し替える場合は一度提出を取り下げてください。
                                    @endif
                                </p>
                                <button type="button"
                                        class="license-accordion__btn license-accordion__btn--danger"
                                        data-license-withdraw-trigger
                                        data-doc-key="{{ $doc['key'] }}">
                                    <i class="fas fa-trash-alt"></i> 提出取り下げ
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- 未提出・差戻し・下書き：アップロード UI --}}
                        <form class="license-accordion__panel license-accordion__upload-form"
                              data-license-upload-form
                              data-doc-key="{{ $doc['key'] }}"
                              data-upload-url="{{ route('shop.mypage.documents.upload') }}"
                              data-review-url="{{ route('shop.mypage.documents.request-review') }}">
                            @csrf
                            <input type="hidden" name="type" value="{{ $doc['key'] }}">

                            <div class="license-accordion__dropzone" data-license-dropzone>
                                @if($serverFileUrl !== '' && $serverFileName !== '')
                                    <div class="license-accordion__file-row" data-license-current-file>
                                        <span class="license-accordion__file-icon" aria-hidden="true"><i class="fas fa-file-alt"></i></span>
                                        <div class="license-accordion__file-info">
                                            <a href="{{ $serverFileUrl }}" target="_blank" rel="noopener noreferrer" class="license-accordion__file-link">
                                                {{ $serverFileName }}
                                            </a>
                                            <span class="license-accordion__file-meta">前回アップロード済（差し替え可）</span>
                                        </div>
                                    </div>
                                @endif

                                <input type="file"
                                       name="file"
                                       accept=".pdf,.png,.jpg,.jpeg,image/*,application/pdf"
                                       class="sr-only"
                                       data-license-file-input>
                                <div class="license-accordion__dropzone-inner">
                                    <i class="fas fa-cloud-upload-alt license-accordion__dropzone-icon"></i>
                                    <p class="license-accordion__dropzone-text">
                                        画像 または PDF をドラッグ＆ドロップ<br>
                                        または下のボタンから選択してください
                                    </p>
                                    <button type="button" class="license-accordion__btn license-accordion__btn--secondary" data-license-pick>
                                        ファイルを選択
                                    </button>
                                    <p class="license-accordion__file-selected" data-license-file-name hidden></p>
                                    {{-- 選択した画像のプレビュー（PDF はファイル名表示のみ） --}}
                                    <img class="license-accordion__preview" data-license-preview alt="選択ファイルのプレビュー" hidden>
                                </div>
                            </div>

                            <div class="license-accordion__field">
                                <label class="license-accordion__label" for="{{ $accordionId }}-expired">
                                    有効期限
                                    @if($expiryRequired)
                                        <span class="license-accordion__required">必須</span>
                                    @else
                                        <span class="license-accordion__optional">任意</span>
                                    @endif
                                </label>
                                <input type="date"
                                       id="{{ $accordionId }}-expired"
                                       name="expired_at"
                                       class="license-accordion__input"
                                       value="{{ $expiryVal }}"
                                       min="{{ now()->format('Y-m-d') }}"
                                       @if($expiryRequired) required @endif>
                                @if($expiryNote !== '')
                                    <p class="license-accordion__field-note">{{ $expiryNote }}</p>
                                @endif
                            </div>

                            <button type="submit"
                                    class="license-accordion__btn license-accordion__btn--primary license-accordion__btn--full"
                                    data-license-submit>
                                <i class="fas fa-paper-plane"></i> この内容で提出する
                            </button>
                            <p class="license-accordion__submit-feedback" data-license-submit-feedback hidden></p>

                            {{-- 例外ケースへの相談窓口（枠2のみ） --}}
                            @if(!empty($supportNote))
                                <div class="license-accordion__support">
                                    <p class="license-accordion__support-text">{{ $supportNote }}</p>
                                    <a href="{{ route('pages.support.form') }}" class="license-accordion__support-link">
                                        <i class="fas fa-comments"></i> サポート窓口に相談する
                                    </a>
                                </div>
                            @endif
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    {{-- 取り下げ確認モーダル（共通） --}}
    <div class="license-withdraw-modal" data-license-withdraw-modal hidden role="dialog" aria-modal="true" aria-labelledby="license-withdraw-title">
        <div class="license-withdraw-modal__overlay" data-license-withdraw-close></div>
        <div class="license-withdraw-modal__panel">
            <h3 id="license-withdraw-title" class="license-withdraw-modal__title">
                <i class="fas fa-exclamation-triangle"></i> 提出の取り下げ
            </h3>
            <p class="license-withdraw-modal__text">アップロードしたファイルは削除されます。提出を取り下げますか？</p>
            <form method="POST" action="{{ route('shop.mypage.documents.withdraw-review') }}" class="license-withdraw-modal__form">
                @csrf
                <input type="hidden" name="type" value="" data-license-withdraw-type>
                <div class="license-withdraw-modal__actions">
                    <button type="button" class="license-accordion__btn license-accordion__btn--ghost" data-license-withdraw-close>キャンセル</button>
                    <button type="submit" class="license-accordion__btn license-accordion__btn--danger">取り下げる</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ===== Licenses Accordion ===== */
.license-section__flash {
    padding: 10px 14px;
    margin: 0 0 10px;
    border-radius: 10px;
    background: var(--color-success-bg);
    color: var(--color-success);
    border: 1px solid rgba(74, 222, 128, 0.4);
    font-size: 0.84rem;
}
.license-section__errors {
    padding: 10px 14px;
    margin: 0 0 10px;
    border-radius: 10px;
    background: var(--color-danger-bg);
    color: var(--color-danger);
    border: 1px solid rgba(248, 113, 113, 0.4);
    font-size: 0.84rem;
}
.license-section__errors p { margin: 0; }

.license-accordion-list { display: flex; flex-direction: column; gap: 10px; }

.license-accordion {
    background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
    border: 1px solid var(--color-border);
    border-radius: 14px;
    overflow: hidden;
    transition: border-color 0.15s ease;
}
.license-accordion.is-approved { border-color: rgba(74, 222, 128, 0.35); }
.license-accordion.is-pending  { border-color: var(--color-border-strong); }
.license-accordion.is-rejected { border-color: rgba(248, 113, 113, 0.45); }

.license-accordion__head {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 14px;
    background: transparent;
    border: 0;
    cursor: pointer;
    text-align: left;
    color: var(--color-text-header);
    transition: background 0.15s ease;
}
.license-accordion__head:hover { background: rgba(197,160,89,0.06); }
.license-accordion__title-block { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.license-accordion__title {
    font-size: 0.96rem;
    font-weight: 800;
    color: var(--color-text-header);
    line-height: 1.3;
    word-break: break-word;
}
.license-accordion__issuer {
    font-size: 0.72rem;
    color: var(--color-text-muted);
    font-weight: 600;
    letter-spacing: 0.02em;
}
.license-accordion__status {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    border: 1px solid transparent;
    flex: 0 0 auto;
    white-space: nowrap;
}
.license-accordion__status--not-submitted {
    background: rgba(255,255,255,0.06);
    color: var(--color-text-muted);
    border-color: var(--color-border);
}
.license-accordion__status--pending,
.license-accordion__status--draft {
    background: rgba(197,160,89,0.16);
    color: var(--gold-light);
    border-color: var(--color-border-strong);
}
.license-accordion__status--approved {
    background: var(--color-success-bg);
    color: var(--color-success);
    border-color: rgba(74, 222, 128, 0.45);
}
.license-accordion__status--rejected {
    background: var(--color-danger-bg);
    color: var(--color-danger);
    border-color: rgba(248, 113, 113, 0.5);
}
.license-accordion__chip {
    font-size: 0.66rem;
    padding: 2px 8px;
    border-radius: 999px;
    background: rgba(248,113,113,0.12);
    color: var(--color-danger);
    border: 1px solid rgba(248,113,113,0.35);
    flex: 0 0 auto;
}
.license-accordion__chevron {
    flex: 0 0 auto;
    color: var(--gold);
    font-size: 0.78rem;
    transition: transform 0.2s ease;
}
.license-accordion[data-open="true"] .license-accordion__chevron,
.license-accordion__head[aria-expanded="true"] .license-accordion__chevron {
    transform: rotate(180deg);
}

.license-accordion__body {
    padding: 0 14px 14px;
    border-top: 1px solid var(--color-border);
    background: rgba(0,0,0,0.18);
}
.license-accordion__body[hidden] { display: none; }

/* 注釈文 */
.license-accordion__desc {
    padding: 12px 0 10px;
    font-size: 0.82rem;
    line-height: 1.7;
    color: var(--color-text);
}
.license-accordion__desc p { margin: 0; }
.license-accordion__desc-list {
    margin: 6px 0 0;
    padding-left: 18px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.license-accordion__desc-list li { line-height: 1.7; }
.license-accordion__desc-list strong { color: var(--gold-light); }
.license-accordion__desc-emphasis {
    color: var(--color-danger);
    font-weight: 800;
}

.license-accordion__ng {
    margin: 0 0 10px;
    padding: 10px 12px;
    border-radius: 10px;
    background: var(--color-danger-bg);
    border: 1px solid rgba(248,113,113,0.45);
    color: var(--color-danger);
    font-size: 0.82rem;
    line-height: 1.6;
}

.license-accordion__panel {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.license-accordion__file-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--color-border);
    border-radius: 10px;
}
.license-accordion__file-icon {
    flex: 0 0 auto;
    width: 32px; height: 32px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 8px;
    background: rgba(197,160,89,0.16);
    color: var(--gold);
    font-size: 0.95rem;
}
.license-accordion__file-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
.license-accordion__file-link {
    color: var(--gold-light);
    text-decoration: underline;
    text-underline-offset: 2px;
    font-size: 0.86rem;
    font-weight: 700;
    word-break: break-all;
}
.license-accordion__file-link:hover { color: var(--color-text-header); }
.license-accordion__file-meta { font-size: 0.7rem; color: var(--color-text-muted); }

.license-accordion__field { display: flex; flex-direction: column; gap: 4px; }
.license-accordion__label {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--gold);
    letter-spacing: 0.04em;
}
.license-accordion__required {
    margin-left: 6px; padding: 1px 6px; border-radius: 4px; background: rgba(248,113,113,0.16);
    color: var(--color-danger); font-size: 0.66rem; font-weight: 800;
}
.license-accordion__optional {
    margin-left: 6px; padding: 1px 6px; border-radius: 4px; background: rgba(255,255,255,0.06);
    color: var(--color-text-muted); font-size: 0.66rem; font-weight: 800;
}
.license-accordion__input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid var(--color-border);
    background: rgba(255,255,255,0.04);
    color: var(--color-text-header);
    font-size: 0.92rem;
    min-height: 42px;
    color-scheme: dark;
}
.license-accordion__input:focus {
    outline: none;
    border-color: var(--color-border-strong);
    background: rgba(255,255,255,0.06);
}
.license-accordion__input[readonly] {
    opacity: 0.65;
    cursor: not-allowed;
}
.license-accordion__field-note {
    margin: 4px 0 0;
    font-size: 0.7rem;
    color: var(--color-text-muted);
    line-height: 1.5;
}

/* ドロップゾーン */
.license-accordion__dropzone {
    padding: 14px;
    border: 1px dashed var(--color-border-strong);
    border-radius: 12px;
    background: rgba(255,255,255,0.02);
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.license-accordion__dropzone.is-dragover {
    background: rgba(197,160,89,0.10);
    border-color: var(--gold);
}
.license-accordion__dropzone-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
    padding: 8px;
}
.license-accordion__dropzone-icon {
    font-size: 1.8rem;
    color: var(--gold);
}
.license-accordion__dropzone-text {
    margin: 0;
    font-size: 0.78rem;
    color: var(--color-text-muted);
    line-height: 1.6;
}
.license-accordion__file-selected {
    margin: 0;
    padding: 6px 10px;
    border-radius: 8px;
    background: rgba(197,160,89,0.12);
    color: var(--color-text-header);
    font-size: 0.78rem;
    font-weight: 600;
    word-break: break-all;
}

/* ボタン */
.license-accordion__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: 10px;
    border: 1px solid transparent;
    font-weight: 700;
    font-size: 0.86rem;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, transform 0.12s ease;
}
.license-accordion__btn:hover { transform: translateY(-1px); }
.license-accordion__btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
.license-accordion__btn--full { width: 100%; }
.license-accordion__btn--primary {
    background: linear-gradient(135deg, var(--gold-light), var(--gold));
    color: #1a1206;
    border-color: var(--color-border-strong);
    box-shadow: 0 4px 12px rgba(197,160,89,0.32);
}
.license-accordion__btn--primary:hover { box-shadow: 0 6px 16px rgba(197,160,89,0.45); }
.license-accordion__btn--secondary {
    background: rgba(255,255,255,0.04);
    color: var(--color-text-header);
    border-color: var(--color-border);
}
.license-accordion__btn--secondary:hover { background: rgba(255,255,255,0.08); border-color: var(--color-border-strong); }
.license-accordion__btn--ghost {
    background: transparent;
    color: var(--color-text);
    border-color: var(--color-border);
}
.license-accordion__btn--danger {
    background: var(--color-danger-bg);
    color: var(--color-danger);
    border-color: rgba(248,113,113,0.5);
}
.license-accordion__btn--danger:hover { background: rgba(185,28,28,0.32); color: #fee2e2; }

.license-accordion__submit-feedback {
    margin: 0;
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 0.8rem;
}
.license-accordion__submit-feedback.is-success { background: var(--color-success-bg); color: var(--color-success); border: 1px solid rgba(74,222,128,0.4); }
.license-accordion__submit-feedback.is-error { background: var(--color-danger-bg); color: var(--color-danger); border: 1px solid rgba(248,113,113,0.4); }

/* 取り下げゾーン */
.license-accordion__withdraw-zone {
    padding: 12px;
    border-radius: 10px;
    background: rgba(0,0,0,0.18);
    border: 1px solid var(--color-border);
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.license-accordion__withdraw-warning {
    margin: 0;
    font-size: 0.8rem;
    line-height: 1.6;
    color: var(--color-text);
}
.license-accordion__withdraw-warning i { color: var(--gold); margin-right: 4px; }

/* サポート相談（枠2のみ） */
.license-accordion__support {
    margin-top: 4px;
    padding: 12px;
    border-radius: 10px;
    background: rgba(197,160,89,0.06);
    border: 1px dashed var(--color-border-strong);
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.license-accordion__support-text {
    margin: 0;
    font-size: 0.78rem;
    line-height: 1.65;
    color: var(--color-text);
}
.license-accordion__support-link {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(197,160,89,0.12);
    color: var(--gold-light);
    border: 1px solid var(--color-border-strong);
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
}
.license-accordion__support-link:hover { background: rgba(197,160,89,0.22); color: var(--color-text-header); }

/* 取り下げ確認モーダル */
.license-withdraw-modal {
    position: fixed; inset: 0; z-index: 2500;
    display: none;
    align-items: center; justify-content: center;
    padding: 24px 16px;
}
.license-withdraw-modal:not([hidden]) { display: flex; }
.license-withdraw-modal__overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.78); backdrop-filter: blur(4px); }
.license-withdraw-modal__panel {
    position: relative;
    width: min(420px, 100%);
    background: linear-gradient(180deg, var(--color-sub), var(--dark-bg));
    border: 1px solid var(--color-border-strong);
    border-radius: 16px;
    padding: 18px 16px 14px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.7);
}
.license-withdraw-modal__title {
    margin: 0 0 10px;
    font-family: var(--font-sans);
    font-size: 1.02rem;
    color: var(--color-text-header);
    display: flex; align-items: center; gap: 8px;
}
.license-withdraw-modal__title i { color: var(--color-danger); }
.license-withdraw-modal__text { margin: 0 0 14px; font-size: 0.84rem; line-height: 1.7; color: var(--color-text); }
.license-withdraw-modal__actions { display: flex; gap: 8px; justify-content: flex-end; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var root = document.getElementById('license-section');
    if (!root) return;
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    // ===== アコーディオン開閉 =====
    root.querySelectorAll('[data-license-accordion-toggle]').forEach(function (head) {
        head.addEventListener('click', function () {
            var acc = head.closest('[data-license-accordion]');
            if (!acc) return;
            var body = document.getElementById(head.getAttribute('aria-controls'));
            var isOpen = head.getAttribute('aria-expanded') === 'true';
            head.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            if (body) {
                body.hidden = isOpen;
            }
            acc.setAttribute('data-open', isOpen ? 'false' : 'true');
        });
    });

    // ===== アップロードフォーム =====
    root.querySelectorAll('[data-license-upload-form]').forEach(function (form) {
        var dropzone = form.querySelector('[data-license-dropzone]');
        var fileInput = form.querySelector('[data-license-file-input]');
        var pickBtn = form.querySelector('[data-license-pick]');
        var fileNameEl = form.querySelector('[data-license-file-name]');
        var submitBtn = form.querySelector('[data-license-submit]');
        var feedback = form.querySelector('[data-license-submit-feedback]');
        var acc = form.closest('[data-license-accordion]');
        var expiryRequired = acc && acc.getAttribute('data-expiry-required') === '1';
        var expiryInput = form.querySelector('input[name="expired_at"]');
        var uploadUrl = form.getAttribute('data-upload-url');
        var reviewUrl = form.getAttribute('data-review-url');
        var docKey = form.getAttribute('data-doc-key');

        function setFeedback(state, message) {
            if (!feedback) return;
            if (!state) { feedback.hidden = true; feedback.className = 'license-accordion__submit-feedback'; feedback.textContent = ''; return; }
            feedback.hidden = false;
            feedback.className = 'license-accordion__submit-feedback is-' + state;
            feedback.textContent = message || '';
        }
        var previewEl = form.querySelector('[data-license-preview]');
        function updateFileName() {
            var f = fileInput && fileInput.files && fileInput.files[0];
            if (fileNameEl) {
                if (f) { fileNameEl.hidden = false; fileNameEl.textContent = '選択中: ' + f.name; }
                else { fileNameEl.hidden = true; fileNameEl.textContent = ''; }
            }
            // 画像ならその場でプレビュー表示（PDF はファイル名のみ）
            if (previewEl) {
                if (previewEl.dataset.blobUrl) {
                    URL.revokeObjectURL(previewEl.dataset.blobUrl);
                    delete previewEl.dataset.blobUrl;
                }
                if (f && /^image\//.test(f.type)) {
                    var blobUrl = URL.createObjectURL(f);
                    previewEl.src = blobUrl;
                    previewEl.dataset.blobUrl = blobUrl;
                    previewEl.hidden = false;
                } else {
                    previewEl.hidden = true;
                    previewEl.removeAttribute('src');
                }
            }
        }
        function isValid() {
            var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
            // 前回アップロード済みファイルがある場合（form内に data-license-current-file がある）はファイル未選択でも再提出可能
            var hasPrev = !!form.querySelector('[data-license-current-file]');
            if (!hasFile && !hasPrev) return false;
            if (expiryRequired) {
                if (!expiryInput || !expiryInput.value) return false;
            }
            return true;
        }
        function syncSubmitDisabled() {
            if (!submitBtn) return;
            submitBtn.disabled = !isValid();
        }

        if (pickBtn) pickBtn.addEventListener('click', function () { fileInput && fileInput.click(); });
        if (fileInput) {
            fileInput.addEventListener('change', function () { updateFileName(); syncSubmitDisabled(); });
        }
        if (expiryInput) {
            expiryInput.addEventListener('input', syncSubmitDisabled);
            expiryInput.addEventListener('change', syncSubmitDisabled);
        }

        // Drag & drop
        if (dropzone && fileInput) {
            ['dragenter', 'dragover'].forEach(function (ev) {
                dropzone.addEventListener(ev, function (e) { e.preventDefault(); dropzone.classList.add('is-dragover'); });
            });
            ['dragleave', 'drop'].forEach(function (ev) {
                dropzone.addEventListener(ev, function (e) { e.preventDefault(); dropzone.classList.remove('is-dragover'); });
            });
            dropzone.addEventListener('drop', function (e) {
                if (!e.dataTransfer || !e.dataTransfer.files || !e.dataTransfer.files.length) return;
                fileInput.files = e.dataTransfer.files;
                updateFileName();
                syncSubmitDisabled();
            });
        }
        syncSubmitDisabled();

        // Submit: 1) upload (file as draft) 2) request-review (set pending)
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!isValid()) {
                setFeedback('error', expiryRequired && expiryInput && !expiryInput.value
                    ? '有効期限を入力してください。'
                    : 'ファイルを選択してください。');
                return;
            }
            if (submitBtn) submitBtn.disabled = true;
            setFeedback(null);

            var file = fileInput && fileInput.files && fileInput.files[0];
            var expiredAt = expiryInput ? expiryInput.value : '';
            var chain = Promise.resolve();

            if (file) {
                var fd = new FormData();
                fd.append('_token', csrfToken);
                fd.append('type', docKey);
                fd.append('file', file);
                if (expiredAt) fd.append('expired_at', expiredAt);
                chain = chain.then(function () {
                    return fetch(uploadUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd,
                    }).then(function (r) {
                        return r.json().then(function (json) { if (!r.ok) throw json; return json; });
                    });
                });
            }

            chain.then(function () {
                var body = { type: docKey };
                if (expiredAt) body.expired_at = expiredAt;
                return fetch(reviewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                }).then(function (r) {
                    return r.json().then(function (json) { if (!r.ok) throw json; return json; });
                });
            }).then(function () {
                window.location.reload();
            }).catch(function (err) {
                var msgs = err && err.errors ? Object.values(err.errors).flat() : [];
                setFeedback('error', msgs[0] || (err && err.message) || '処理に失敗しました。');
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    });

    // ===== 取り下げ確認モーダル =====
    var withdrawModal = root.querySelector('[data-license-withdraw-modal]');
    var withdrawTypeInput = root.querySelector('[data-license-withdraw-type]');
    function openWithdrawModal(docKey) {
        if (!withdrawModal) return;
        if (withdrawTypeInput) withdrawTypeInput.value = docKey;
        withdrawModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }
    function closeWithdrawModal() {
        if (!withdrawModal) return;
        withdrawModal.hidden = true;
        document.body.style.overflow = '';
    }
    root.querySelectorAll('[data-license-withdraw-trigger]').forEach(function (btn) {
        btn.addEventListener('click', function () { openWithdrawModal(btn.getAttribute('data-doc-key') || ''); });
    });
    if (withdrawModal) {
        withdrawModal.querySelectorAll('[data-license-withdraw-close]').forEach(function (el) {
            el.addEventListener('click', closeWithdrawModal);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !withdrawModal.hidden) closeWithdrawModal();
        });
    }
})();
</script>
@endpush
