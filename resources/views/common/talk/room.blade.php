@extends('layouts.app')

@php
    $isCast = request()->is('cast/*');
@endphp

@section('title', ($partnerName ?? 'トーク') . ' 様')
@section('header_title', $partnerName . ' 様')
@section('body-class', 'page-talk page-talk-room')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
@if($isCast)
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/review-modal.css') }}">
@endif
<style>
    .result-template-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 12px 0;
    }
    .result-template-button {
        border: 1px solid rgba(229, 193, 88, 0.35);
        background: rgba(255, 255, 255, 0.06);
        color: #f4e7c2;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 0.85rem;
        cursor: pointer;
    }
    .result-message-textarea {
        width: 100%;
        min-height: 140px;
        border-radius: 14px;
        border: 1px solid rgba(229, 193, 88, 0.22);
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
        padding: 14px;
        resize: vertical;
    }
    .hired-wage-field-wrap {
        display: none;
        margin: 12px 0 4px;
    }
    .hired-wage-field-wrap.is-visible {
        display: block;
    }
    .hired-wage-field-wrap label {
        display: block;
        font-size: 0.82rem;
        color: #d4c4a4;
        margin-bottom: 6px;
    }
    .hired-wage-field-wrap input {
        width: 100%;
        border-radius: 12px;
        border: 1px solid rgba(229, 193, 88, 0.22);
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
        padding: 10px 12px;
        font-size: 0.95rem;
    }
</style>
@endpush

@push('scripts')
<script>
    window.isCastTalkRoom = {!! request()->is('cast/*') ? 'true' : 'false' !!};
    window.talkResultMessageTemplates = @json($resultMessageTemplates ?? []);
    window.initialTalkTopic = @json($initialTalkTopic ?? null);
    window.initialTalkJobKind = @json($initialTalkJobKind ?? null);
    window.hasTalkMessages = @json($hasMessages ?? false);
    window.selectedTalkJobKind = @json($selectedTalkJobKind ?? null);
    window.canSelectTalkJobKind = @json($canSelectTalkJobKind ?? false);
</script>
<script src="{{ asset('assets/js/talk-room.js') }}"></script>
@endpush

@section('content')
@php
    $isCast = request()->is('cast/*');
    $sendUrl = $isCast ? route('cast.talk.send') : route('shop.talk.send');
    $deleteUrl = $isCast ? route('cast.talk.delete') : route('shop.talk.delete');
    $actionUrl = $actionUrl ?? ($isCast ? route('cast.talk.action') : route('shop.talk.action'));
    $blockUrl = $blockUrl ?? ($isCast ? route('cast.talk.block') : route('shop.talk.block'));
    $partnerAvatar = $partnerAvatar ?? asset('assets/images/common/no-image.png');
@endphp

<div id="talk-room-container" class="flex flex-col h-full bg-[#120505]">
    {{-- LINE風：相手のアイコンと名前（上部バー） --}}
    <div class="talk-room-header">
        <div class="talk-room-header-inner">
            <img src="{{ $partnerAvatar }}" alt="" class="talk-room-header-avatar">
            <span class="talk-room-header-name">{{ $partnerName }}</span>
        </div>
        @if(empty($blockState['blocked_by_other']))
        <div class="talk-room-header-actions">
            @if($isCast && !empty($reviewApplicationId))
                <button type="button" class="btn-interview btn-review-post" data-application-id="{{ $reviewApplicationId }}" title="レビュー投稿">
                    <i class="fas fa-star"></i>
                    <span>レビュー投稿</span>
                </button>
            @endif
            @if(!$isCast && !empty($canOfferInterview))
                <button type="button" id="open-interview-modal" class="btn-interview">
                    <i class="far fa-calendar-alt"></i>
                    <span>面談日を提案</span>
                </button>
            @endif
            <form action="{{ $blockUrl }}" method="POST">
                @csrf
                <input type="hidden" name="partner_id" value="{{ $partnerId }}">
                <button type="submit" class="btn-interview {{ !empty($blockState['blocked_by_me']) ? 'btn-interview-result' : 'btn-interview-result--negative' }}">
                    <i class="fas {{ !empty($blockState['blocked_by_me']) ? 'fa-unlock' : 'fa-ban' }}"></i>
                    <span>{{ !empty($blockState['blocked_by_me']) ? 'ブロック解除' : 'ブロック' }}</span>
                </button>
            </form>
        </div>
        @endif
    </div>

    @if(session('message'))
        <div class="px-4 pt-3">
            <div class="profile-edit-flash">{{ session('message') }}</div>
        </div>
    @endif

    @if(!empty($blockState['is_blocked']))
        <div class="px-4 pt-3">
            <div class="talk-block-notice">
                {{ !empty($blockState['blocked_by_me']) ? 'この相手をブロック中です。解除するまでメッセージ送信はできません。' : 'このトークは相手によってブロックされています。' }}
            </div>
        </div>
    @endif

    <div class="px-4 pt-3">
        <div class="talk-result-panel" style="padding: 12px 14px;">
            <div class="talk-result-panel-copy" style="width:100%;">
                <span class="talk-result-panel-title">求人種別</span>
                <p id="talk-job-kind-guidance" style="margin-top:6px;">面談日を送る前に求人種別を確定してください。面談日確定後は変更できません。</p>
            </div>
            <div class="talk-result-panel-actions" style="width:100%;">
                <select id="talk-room-job-kind" style="width:100%; border-radius:10px; border:1px solid rgba(229,193,88,.22); background:rgba(255,255,255,.05); color:#fff; padding:8px 10px;" @if(empty($canSelectTalkJobKind)) disabled @endif>
                    <option value="">未選択</option>
                    <option value="trial">体験入店</option>
                    <option value="fulltime">本入店</option>
                    <option value="help">ヘルプ</option>
                </select>
                @if(!empty($canSelectTalkJobKind))
                    <button type="button" id="save-talk-job-kind" class="btn-interview btn-interview-result">種別を保存</button>
                    <span id="talk-job-kind-save-status" style="font-size:12px; color:#d4c4a4;">未保存</span>
                @else
                    <span style="font-size:12px; color:#a1a1aa;">面談日確定後は変更不可</span>
                @endif
            </div>
        </div>
    </div>

    {{-- メッセージ表示エリア --}}
    <div class="chat-messages" id="chat-messages" data-delete-url="{{ $deleteUrl }}">
        @forelse($messages as $msg)
            <div class="message-row {{ $msg->is_mine ? 'msg-right' : 'msg-left' }}" data-message-id="{{ $msg->id }}">
                @if(!$msg->is_mine)
                    <div class="msg-avatar-wrap">
                        <img src="{{ $partnerAvatar }}" alt="" class="msg-avatar">
                    </div>
                @endif
                <div class="message-block">
                    <div class="message-inline">
                        @if($msg->is_mine)
                            <div class="msg-meta">
                                @if(!empty($msg->can_delete))
                                    <button type="button" class="msg-delete-btn" data-message-id="{{ $msg->id }}" title="削除" aria-label="メッセージを削除"><i class="fas fa-trash-alt"></i></button>
                                @endif
                                @if($msg->is_mine)
                                    <span class="msg-status"><i class="fas fa-check"></i></span>
                                @endif
                                <span class="msg-time">{{ $msg->created_at->format('H:i') }}</span>
                            </div>
                        @endif
                    @if($msg->type === 2)
                        @php
                            $selectedOption = $msg->selected_option ? \Carbon\Carbon::parse($msg->selected_option) : null;
                        @endphp
                        <div class="message-bubble message-bubble-interview">
                            <div class="interview-card-head">
                                <div class="interview-title">
                                    <i class="far fa-calendar-alt"></i>
                                    <span>面談候補日をお送りします</span>
                                </div>
                                <span class="interview-badge">日程調整</span>
                            </div>
                            <p class="interview-body-copy">ご都合の良い日時をお選びください。</p>
                            <ul class="interview-option-list">
                                @foreach($msg->interview_options as $option)
                                    @php
                                        $optionDate = \Carbon\Carbon::parse($option);
                                        $isSelectedOption = $msg->selected_option === $option;
                                    @endphp
                                    <li>
                                        @if(!empty($canConfirmInterview) && !$msg->selected_option && !$msg->is_mine && empty($blockState['is_blocked']))
                                            <button
                                                type="button"
                                                class="interview-option-btn"
                                                data-offer-token="{{ $msg->offer_token }}"
                                                data-option-label="{{ $option }}"
                                                data-option-display="{{ $optionDate->format('Y年n月j日 H:i') }}"
                                            >
                                                <span class="interview-option-main">
                                                    <span class="interview-option-date">{{ $optionDate->format('Y年n月j日') }}</span>
                                                    <span class="interview-option-time">{{ $optionDate->format('H:i') }}</span>
                                                </span>
                                                <span class="interview-option-action">選択する</span>
                                            </button>
                                        @else
                                            <div class="interview-option-btn {{ $isSelectedOption ? 'is-selected' : '' }}">
                                                <span class="interview-option-main">
                                                    <span class="interview-option-date">{{ $optionDate->format('Y年n月j日') }}</span>
                                                    <span class="interview-option-time">{{ $optionDate->format('H:i') }}</span>
                                                </span>
                                                @if($isSelectedOption)
                                                    <span class="interview-option-action">決定済み</span>
                                                @endif
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                            @if($selectedOption)
                                <p class="interview-note">確定日時: {{ $selectedOption->format('Y年n月j日 H:i') }}</p>
                            @endif
                            @if(!$isCast && !empty($canCancelStatus) && $msg->is_mine && $msg->selected_option)
                                <p class="interview-change-schedule-wrap">
                                    <button type="button" class="js-interview-change-schedule interview-change-schedule-btn">日程を変更</button>
                                </p>
                            @endif
                            @if($msg->is_mine)
                                <span class="message-bubble-tail" aria-hidden="true">
                                    <svg viewBox="0 0 8 12" fill="currentColor"><path d="M0 0V12C3 12 8 8 8 0H0Z"/></svg>
                                </span>
                            @endif
                        </div>
                    @elseif($msg->type === 3)
                        <div class="message-bubble message-bubble-interview message-bubble-confirmed">
                            <div class="interview-title">
                                <i class="far fa-calendar-check"></i>
                                <span>面談日が確定しました</span>
                            </div>
                            <p class="interview-confirmed-date">
                                {{ \Carbon\Carbon::parse($msg->selected_option)->format('Y年n月j日 H:i') }}
                            </p>
                            @if($msg->is_mine)
                                <span class="message-bubble-tail" aria-hidden="true">
                                    <svg viewBox="0 0 8 12" fill="currentColor"><path d="M0 0V12C3 12 8 8 8 0H0Z"/></svg>
                                </span>
                            @endif
                        </div>
                    @else
                        @php
                            $displayContent = trim((string) $msg->content);
                            $displayContent = str_replace(["\r\n", "\r"], "\n", $displayContent);
                            $displayContent = preg_replace('/\n{2,}/', "\n", $displayContent);
                        @endphp
                        <div class="message-bubble"><p class="m-0">{!! nl2br(e($displayContent)) !!}</p>@if($msg->is_mine)<span class="message-bubble-tail" aria-hidden="true"><svg viewBox="0 0 8 12" fill="currentColor"><path d="M0 0V12C3 12 8 8 8 0H0Z"/></svg></span>@endif</div>
                    @endif
                        @if(!$msg->is_mine)
                            <div class="msg-meta">
                                <span class="msg-time">{{ $msg->created_at->format('H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 mt-20">
                <i class="fas fa-comments opacity-10 text-6xl mb-4 block"></i>
                <p>メッセージはまだありません</p>
            </div>
        @endforelse
    </div>

    @if(!empty($canCancelStatus) || !empty($canSelectResult))
        <div class="talk-result-panel">
            <div class="talk-result-panel-copy">
                <span class="talk-result-panel-title">現在のステータス: {{ $currentStatusLabel ?? 'やり取り中' }}</span>
                @if(!empty($canSelectResult))
                    <p>面談日が決定しています。結果送信、またはキャンセルしてやり取り中に戻せます。</p>
                @elseif(!empty($canCancelStatus))
                    <p>このステータスでは同じ操作を重複実行できません。必要な場合はキャンセルしてやり取り中へ戻してください。</p>
                @endif
            </div>
            <div class="talk-result-panel-actions">
                @if(!empty($canCancelStatus))
                    <button type="button" id="send-cancel-status" class="btn-interview btn-interview-result btn-interview-cancel-state">
                        <i class="fas fa-rotate-left"></i>
                        <span>キャンセル</span>
                    </button>
                @endif
                @if(!empty($canSelectResult))
                    <button type="button" id="send-hire-message" class="btn-interview btn-interview-result">
                        <i class="fas fa-circle-check"></i>
                        <span>採用</span>
                    </button>
                    <button type="button" id="send-reject-message" class="btn-interview btn-interview-result btn-interview-result--negative">
                        <i class="fas fa-circle-xmark"></i>
                        <span>不採用</span>
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- 入力エリア --}}
    @if(!empty($canSend))
        <div class="chat-input-area">
            <form id="chat-form" data-url="{{ $sendUrl }}" data-action-url="{{ $actionUrl }}" data-partner-id="{{ $partnerId }}">
                @csrf
                <div class="chat-input-row">
                    @if($isCast)
                        <div style="width:100%; margin-bottom:8px;">
                            <label for="talk-topic" style="display:block; font-size:12px; color:#d4c4a4; margin-bottom:4px;">相談種別</label>
                            <select id="talk-topic" name="talk_topic" style="width:100%; border-radius:10px; border:1px solid rgba(229,193,88,.22); background:rgba(255,255,255,.05); color:#fff; padding:8px 10px;">
                                <option value="new_hire">新規採用</option>
                                <option value="help">ヘルプ</option>
                                <option value="other">その他相談</option>
                            </select>
                        </div>
                        <div id="talk-job-kind-wrap" style="width:100%; margin-bottom:8px;">
                            <label for="talk-job-kind" style="display:block; font-size:12px; color:#d4c4a4; margin-bottom:4px;">応募区分</label>
                            <select id="talk-job-kind" name="talk_job_kind" style="width:100%; border-radius:10px; border:1px solid rgba(229,193,88,.22); background:rgba(255,255,255,.05); color:#fff; padding:8px 10px;">
                                <option value="trial">体験入店</option>
                                <option value="fulltime">本入店</option>
                                <option value="help">ヘルプ</option>
                            </select>
                        </div>
                    @endif
                    <div class="chat-input-wrapper">
                        <textarea name="message" rows="1" placeholder="メッセージを入力..." class="focus:outline-none"></textarea>
                    </div>
                    <button type="submit" class="btn-send" aria-label="送信"><i class="fas fa-paper-plane"></i></button>
                </div>
            </form>
        </div>
    @endif
</div>

{{-- 面談日候補 送信モーダル（店舗側のみ利用） --}}
@if(!$isCast)
<div id="interview-modal-overlay" class="interview-modal-overlay" aria-hidden="true">
    <div class="interview-modal">
        <div class="interview-modal-header">
            <h2>面談候補日を送信</h2>
            <button type="button" class="interview-modal-close" aria-label="閉じる">&times;</button>
        </div>
        <p class="interview-modal-desc">
            候補日を2〜3件入力してください。<br>
            キャスト側の画面では、ここで入力した候補から1つ選べるUIを想定しています。
        </p>
        <form id="interview-form">
            <div class="interview-option-group">
                <label>候補1</label>
                <input type="datetime-local" name="option1">
            </div>
            <div class="interview-option-group">
                <label>候補2（任意）</label>
                <input type="datetime-local" name="option2">
            </div>
            <div class="interview-option-group">
                <label>候補3（任意）</label>
                <input type="datetime-local" name="option3">
            </div>
            <div class="interview-modal-footer">
                <button type="button" class="btn-interview-cancel">キャンセル</button>
                <button type="submit" class="btn-interview-submit">面談候補を送信</button>
            </div>
        </form>
    </div>
</div>
@endif

@if($isCast)
<div id="interview-confirm-overlay" class="interview-modal-overlay" aria-hidden="true">
    <div class="interview-modal interview-confirm-modal">
        <div class="interview-modal-header">
            <h2>この日時で確定しますか？</h2>
            <button type="button" class="interview-modal-close js-interview-confirm-close" aria-label="閉じる">&times;</button>
        </div>
        <p class="interview-modal-desc">確定後、この面談日時が店舗へ送信されます。</p>
        <div class="interview-confirm-summary">
            <span class="interview-confirm-summary-label">選択した日時</span>
            <strong id="interview-confirm-selected" class="interview-confirm-summary-value">-</strong>
        </div>
        <div class="interview-modal-footer">
            <button type="button" class="btn-interview-cancel js-interview-confirm-close">戻る</button>
            <button type="button" id="interview-confirm-submit" class="btn-interview-submit">この日時で確定</button>
        </div>
    </div>
</div>
@endif

@if(!$isCast && !empty($canSelectResult))
<div id="result-message-overlay" class="interview-modal-overlay" aria-hidden="true">
    <div class="interview-modal">
        <div class="interview-modal-header">
            <h2 id="result-message-title">結果メッセージを送信</h2>
            <button type="button" class="interview-modal-close js-result-message-close" aria-label="閉じる">&times;</button>
        </div>
        <p id="result-message-desc" class="interview-modal-desc">テンプレートを選択し、必要に応じて文面を編集してください。</p>
        <div id="hired-hourly-wage-wrap" class="hired-wage-field-wrap" aria-hidden="true">
            <label for="hired-hourly-wage-input">採用時給（円・確定）</label>
            <input type="text" id="hired-hourly-wage-input" inputmode="numeric" placeholder="例: 5000" autocomplete="off">
        </div>
        <div id="result-employment-kind-wrap" class="hired-wage-field-wrap is-visible" aria-hidden="false">
            <label for="result-employment-kind">採用区分</label>
            <select id="result-employment-kind" style="width:100%; border-radius:12px; border:1px solid rgba(229, 193, 88, 0.22); background:rgba(255,255,255,.05); color:#fff; padding:10px 12px;">
                <option value="trial">体験入店</option>
                <option value="fulltime">本入店</option>
                <option value="help">ヘルプ</option>
            </select>
        </div>
        <div class="result-template-list" id="result-template-list"></div>
        <textarea id="result-message-textarea" class="result-message-textarea" placeholder="送信するメッセージを入力"></textarea>
        <div class="interview-modal-footer">
            <button type="button" class="btn-interview-cancel js-result-message-close">キャンセル</button>
            <button type="button" id="result-message-submit" class="btn-interview-submit">送信する</button>
        </div>
    </div>
</div>
@endif

@if($isCast)
{{-- レビュー投稿モーダル（スターレーティング＋コメント） --}}
<div id="review-post-modal" class="payment-bank-modal" role="dialog" aria-labelledby="review-post-modal-title" aria-modal="true" hidden>
    <div class="payment-bank-modal-backdrop" data-close-review-modal></div>
    <div class="payment-bank-modal-panel review-modal-wrap">
        <header class="review-modal-header">
            <h3 id="review-post-modal-title" class="review-modal-title">レビュー投稿</h3>
            <button type="button" class="review-modal-close-btn" data-close-review-modal aria-label="閉じる"><i class="fas fa-times"></i></button>
        </header>
        <div class="payment-bank-modal-body review-modal-body">
            <p id="review-modal-loading" class="review-modal-loading">読み込み中...</p>
            <div id="review-modal-form-wrap" style="display:none;">
                <p class="review-modal-intro">勤務完了後、お店の雰囲気や働きやすさをレビューしてください。</p>
                <form id="review-post-form">
                    <input type="hidden" name="application_id" id="review-form-application-id" value="">
                    @csrf
                    <div class="review-rating-list" id="review-modal-scores"></div>
                    <div class="review-comment-card">
                        <label class="review-comment-label" for="review-modal-comment">レビューコメント</label>
                        <textarea id="review-modal-comment" name="review_comment" class="review-comment-textarea" rows="4" placeholder="働いてみた感想、雰囲気、条件の印象などを入力してください。" required></textarea>
                    </div>
                    <p id="review-modal-error" class="review-modal-error"></p>
                </form>
                <div class="review-modal-footer">
                    <button type="submit" form="review-post-form" class="review-submit-btn" id="review-submit-btn" disabled>
                        <i class="fas fa-paper-plane"></i> 投稿する
                    </button>
                    <p class="review-footer-hint" id="review-footer-hint">すべての項目を評価すると送信できます</p>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="bonus-confirm-modal" class="payment-bank-modal" role="dialog" aria-labelledby="bonus-confirm-modal-title" aria-modal="true" hidden>
    <div class="payment-bank-modal-backdrop" data-close-bonus-modal></div>
    <div class="payment-bank-modal-panel">
        <div class="payment-bank-modal-header">
            <h3 id="bonus-confirm-modal-title" class="payment-bank-modal-title">ボーナス条件達成確認</h3>
            <button type="button" class="payment-bank-modal-close" data-close-bonus-modal aria-label="閉じる"><i class="fas fa-times"></i></button>
        </div>
        <div class="payment-bank-modal-body">
            <p class="deposit-precheck-note">採用された時点のボーナス金・達成条件です。内容を確認のうえ「完了」で入金申請を行ってください。</p>
            <div class="deposit-precheck-card">
                <div class="deposit-precheck-title">
                    <span id="bonus-confirm-shop-name">—</span>
                    <span class="doc-status status-pending">採用済み案件</span>
                </div>
                <div class="deposit-precheck-meta">ボーナス金額: ¥<span id="bonus-confirm-amount">0</span></div>
                <div class="deposit-precheck-note" id="bonus-confirm-condition">—</div>
            </div>
            <form id="bonus-confirm-form">
                <input type="hidden" name="application_id" id="bonus-confirm-application-id" value="">
                @csrf
                <input type="hidden" name="confirm_bonus_condition" value="1">
                <label class="deposit-check-row">
                    <input type="checkbox" name="confirm_checked" value="1" required>
                    <span>上記のボーナス達成条件を確認し、申請内容に相違がないことを確認しました。</span>
                </label>
                <p id="bonus-confirm-error" class="deposit-precheck-note" style="color:#fca5a5; display:none;"></p>
                <div class="text-right mt-3">
                    <button type="submit" class="btn-action manage" id="bonus-confirm-submit-btn">完了</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($isCast)
@push('scripts')
<script>
(function () {
    var reviewModal = document.getElementById('review-post-modal');
    var bonusModal = document.getElementById('bonus-confirm-modal');
    if (!reviewModal || !bonusModal) return;
    var requestTargetUrl = '{{ route("cast.mypage.deposit.request-target") }}';
    var reviewPostUrl = '{{ route("cast.mypage.deposit.review") }}';
    var depositRequestUrl = '{{ route("cast.mypage.deposit.request") }}';

    function openReviewModal(applicationId) {
        document.getElementById('review-form-application-id').value = applicationId;
        document.getElementById('review-modal-loading').style.display = 'block';
        document.getElementById('review-modal-form-wrap').style.display = 'none';
        var errEl = document.getElementById('review-modal-error');
        if (errEl) { errEl.textContent = ''; errEl.classList.remove('show'); }
        reviewModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        fetch(requestTargetUrl + '?application_id=' + encodeURIComponent(applicationId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            document.getElementById('review-modal-loading').style.display = 'none';
            if (!data.success || !data.request_target) {
                if (errEl) { errEl.textContent = data.message || 'データの取得に失敗しました。'; errEl.classList.add('show'); }
                return;
            }
            var target = data.request_target;
            if (target.review_exists) {
                reviewModal.setAttribute('hidden', '');
                document.body.style.overflow = '';
                showBonusConfirmModal(applicationId, target);
                return;
            }
            buildReviewRatingCards(target.review_contents || []);
            var cmtEl = document.getElementById('review-modal-comment');
            if (cmtEl) cmtEl.value = '';
            document.getElementById('review-modal-form-wrap').style.display = 'block';
            checkReviewFormReady();
        })
        .catch(function () {
            document.getElementById('review-modal-loading').style.display = 'none';
            if (errEl) { errEl.textContent = '読み込みに失敗しました。'; errEl.classList.add('show'); }
        });
    }

    function buildReviewRatingCards(contents) {
        var scoresWrap = document.getElementById('review-modal-scores');
        scoresWrap.innerHTML = '';
        contents.forEach(function (c) {
            var card = document.createElement('div');
            card.className = 'review-rating-card';
            card.setAttribute('data-content-id', c.id);
            var question = document.createElement('p');
            question.className = 'review-rating-question';
            question.textContent = c.name || '';
            var row = document.createElement('div');
            row.className = 'review-rating-row';
            var stars = document.createElement('div');
            stars.className = 'review-rating-stars';
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'review_scores[' + c.id + ']';
            hidden.value = '0';
            hidden.setAttribute('data-rating-input', '1');
            for (var s = 1; s <= 5; s++) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'review-star-btn';
                btn.setAttribute('data-value', s);
                btn.innerHTML = '<i class="far fa-star"></i>';
                (function (starVal, button) {
                    button.addEventListener('click', function () {
                        hidden.value = starVal;
                        updateStarButtons(card, starVal);
                        updateRatingValueSpan(card, starVal);
                        checkReviewFormReady();
                    });
                    button.addEventListener('mouseenter', function () { setStarHover(card, starVal); });
                    button.addEventListener('mouseleave', function () { clearStarHover(card); });
                })(s, btn);
                stars.appendChild(btn);
            }
            var valueSpan = document.createElement('span');
            valueSpan.className = 'review-rating-value';
            valueSpan.textContent = '- / 5';
            row.appendChild(stars);
            row.appendChild(valueSpan);
            card.appendChild(question);
            card.appendChild(hidden);
            card.appendChild(row);
            scoresWrap.appendChild(card);
        });
    }
    function updateStarButtons(card, value) {
        var btns = card.querySelectorAll('.review-star-btn');
        btns.forEach(function (btn) {
            var v = parseInt(btn.getAttribute('data-value'), 10);
            btn.classList.toggle('active', v <= value);
            btn.querySelector('.fa-star').className = v <= value ? 'fas fa-star' : 'far fa-star';
        });
    }
    function setStarHover(card, value) {
        card.querySelectorAll('.review-rating-stars .review-star-btn').forEach(function (btn) {
            var v = parseInt(btn.getAttribute('data-value'), 10);
            btn.classList.toggle('hover', v <= value);
            if (btn.querySelector('.fa-star')) btn.querySelector('.fa-star').className = v <= value ? 'fas fa-star' : 'far fa-star';
        });
    }
    function clearStarHover(card) {
        card.querySelectorAll('.review-star-btn').forEach(function (btn) { btn.classList.remove('hover'); });
        var input = card.querySelector('input[name^="review_scores"]');
        if (input && input.value !== '0') { updateStarButtons(card, parseInt(input.value, 10)); }
    }
    function updateRatingValueSpan(card, value) {
        var span = card.querySelector('.review-rating-value');
        if (span) {
            span.textContent = value > 0 ? value + ' / 5' : '- / 5';
            span.classList.toggle('has-value', value > 0);
        }
    }
    function checkReviewFormReady() {
        var form = document.getElementById('review-post-form');
        if (!form) return;
        var allRated = true;
        form.querySelectorAll('input[data-rating-input="1"]').forEach(function (inp) {
            if (!inp.value || inp.value === '0') allRated = false;
        });
        var comment = (form.querySelector('#review-modal-comment') && form.querySelector('#review-modal-comment').value) || '';
        var ready = allRated && comment.trim().length > 0;
        var btn = document.getElementById('review-submit-btn');
        var hint = document.getElementById('review-footer-hint');
        if (btn) btn.disabled = !ready;
        if (hint) hint.style.display = ready ? 'none' : 'block';
    }
    var commentEl = document.getElementById('review-modal-comment');
    if (commentEl) { commentEl.addEventListener('input', checkReviewFormReady); commentEl.addEventListener('change', checkReviewFormReady); }
    function closeReviewModal() {
        reviewModal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    }
    function showBonusConfirmModal(applicationId, target) {
        document.getElementById('bonus-confirm-application-id').value = applicationId;
        document.getElementById('bonus-confirm-shop-name').textContent = target.shop_name || '—';
        document.getElementById('bonus-confirm-amount').textContent = (target.bonus_amount || 0).toLocaleString();
        document.getElementById('bonus-confirm-condition').innerHTML = (target.bonus_condition || '（条件の記載なし）').replace(/\n/g, '<br>');
        document.getElementById('bonus-confirm-form').querySelector('input[name="confirm_checked"]').checked = false;
        document.getElementById('bonus-confirm-error').style.display = 'none';
        bonusModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeBonusModal() {
        bonusModal.setAttribute('hidden', '');
        document.body.style.overflow = '';
    }
    document.querySelectorAll('.btn-review-post').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-application-id');
            if (id) openReviewModal(id);
        });
    });
    document.querySelectorAll('[data-close-review-modal]').forEach(function (el) { el.addEventListener('click', closeReviewModal); });
    document.querySelectorAll('[data-close-bonus-modal]').forEach(function (el) { el.addEventListener('click', closeBonusModal); });
    reviewModal.addEventListener('click', function (e) { if (e.target === reviewModal) closeReviewModal(); });
    bonusModal.addEventListener('click', function (e) { if (e.target === bonusModal) closeBonusModal(); });
    document.getElementById('review-post-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var form = this;
        var fd = new FormData(form);
        var btn = document.getElementById('review-submit-btn');
        if (btn) btn.disabled = true;
        fetch(reviewPostUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (btn) btn.disabled = false;
            if (res.success && res.request_target) {
                closeReviewModal();
                showBonusConfirmModal(parseInt(form.querySelector('input[name="application_id"]').value, 10), res.request_target);
            } else {
                var re = document.getElementById('review-modal-error');
                if (re) { re.textContent = res.message || '投稿に失敗しました。'; re.classList.add('show'); }
            }
        })
        .catch(function () {
            if (btn) btn.disabled = false;
            var re = document.getElementById('review-modal-error');
            if (re) { re.textContent = '送信に失敗しました。'; re.classList.add('show'); }
        });
    });
    document.getElementById('bonus-confirm-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var form = this;
        var fd = new FormData(form);
        fd.set('confirm_bonus_condition', '1');
        var btn = document.getElementById('bonus-confirm-submit-btn');
        if (btn) btn.disabled = true;
        var errEl = document.getElementById('bonus-confirm-error');
        fetch(depositRequestUrl, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (btn) btn.disabled = false;
            if (res.success) {
                closeBonusModal();
                window.location.href = '{{ route("cast.mypage.employment") }}';
            } else {
                errEl.textContent = res.message || '申請に失敗しました。';
                errEl.style.display = 'block';
            }
        })
        .catch(function () {
            if (btn) btn.disabled = false;
            errEl.textContent = '送信に失敗しました。';
            errEl.style.display = 'block';
        });
    });
})();
</script>
@endpush
@endif

@endsection