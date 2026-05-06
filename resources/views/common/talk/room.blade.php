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
    window.currentTalkStatusCode = @json($currentStatusCode ?? 'chatting');
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
    $talkJobKindLabelMap = ['trial' => '体験入店', 'fulltime' => '本入店', 'help' => 'ヘルプ'];
    $currentTalkJobKindValue = $selectedTalkJobKind ?? $initialTalkJobKind ?? null;
    $currentTalkJobKindLabel = $talkJobKindLabelMap[$currentTalkJobKindValue] ?? '未選択';
    $isInterviewOfferLocked = in_array(($currentStatusCode ?? ''), ['hired', 'rejected'], true);
@endphp

<div id="talk-room-container" class="flex flex-col h-full bg-[#120505]">
    {{-- LINE風：相手のアイコンと名前（上部バー） --}}
    <div class="talk-room-header">
        <div class="talk-room-header-inner">
            <img src="{{ $partnerAvatar }}" alt="" class="talk-room-header-avatar">
            <span class="talk-room-header-name">{{ $partnerName }}</span>
        </div>
        <div class="talk-room-shop-badges">
                <span class="talk-status-label">
                    <span class="talk-status-dot"></span>
                    <span class="talk-status-caption">状況:</span>
                    <span class="talk-status-value">{{ $currentStatusLabel ?? 'やり取り中' }}</span>
                </span>
                @if(!$isCast)
                    <button type="button" id="open-job-kind-modal" class="talk-job-kind-badge" @if(empty($canSelectTalkJobKind)) disabled @endif>
                        <span class="talk-job-kind-caption">種別:</span>
                        <span id="talk-job-kind-current" class="talk-job-kind-value">{{ $currentTalkJobKindLabel }}</span>
                        @if(!empty($canSelectTalkJobKind))
                            <i class="fas fa-chevron-down" aria-hidden="true"></i>
                        @endif
                    </button>
                @else
                    <span class="talk-job-kind-badge talk-job-kind-badge-static">
                        <span class="talk-job-kind-caption">種別:</span>
                        <span id="talk-job-kind-current" class="talk-job-kind-value">{{ $currentTalkJobKindLabel }}</span>
                    </span>
                @endif
        </div>
        @if(empty($blockState['blocked_by_other']))
        <div class="talk-room-header-block">
            <form action="{{ $blockUrl }}" method="POST">
                @csrf
                <input type="hidden" name="partner_id" value="{{ $partnerId }}">
                <button type="submit" class="btn-interview {{ !empty($blockState['blocked_by_me']) ? 'btn-interview-result' : 'btn-interview-result--negative' }}">
                    <i class="fas {{ !empty($blockState['blocked_by_me']) ? 'fa-unlock' : 'fa-ban' }}"></i>
                    <span>{{ !empty($blockState['blocked_by_me']) ? 'ブロック解除' : 'ブロック' }}</span>
                </button>
            </form>
        </div>
        <div class="talk-room-header-actions">
            @if($isCast && !empty($reviewApplicationId))
                <button type="button" class="btn-interview btn-review-post" data-application-id="{{ $reviewApplicationId }}" title="レビュー投稿">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ $currentTalkJobKindValue === 'fulltime' ? 'ボーナス達成報告' : '勤務完了報告' }}</span>
                </button>
            @endif
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
                            $isInvalidatedOffer = !empty($msg->is_invalidated);
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
                                        @if(!empty($canConfirmInterview) && !$msg->selected_option && !$msg->is_mine && empty($blockState['is_blocked']) && !$isInvalidatedOffer)
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
                                                @elseif($isInvalidatedOffer)
                                                    <span class="interview-option-action">無効</span>
                                                @endif
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                            @if($selectedOption)
                                <p class="interview-note">確定日時: {{ $selectedOption->format('Y年n月j日 H:i') }}</p>
                            @elseif($isInvalidatedOffer)
                                <p class="interview-note">この候補日は更新により無効になりました。</p>
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
                    @elseif($msg->type === 6)
                        <div class="message-bubble message-bubble-image">
                            @if(!empty($msg->image_url))
                                <a href="{{ $msg->image_url }}" target="_blank" rel="noopener noreferrer" class="message-image-link">
                                    <img src="{{ $msg->image_url }}" alt="送信画像" class="message-image">
                                </a>
                            @endif
                            @if(!empty($msg->content))
                                <p class="message-image-caption">{!! nl2br(e($msg->content)) !!}</p>
                            @endif
                        </div>
                    @elseif($msg->type === 7)
                        <div class="message-bubble message-bubble-interview message-bubble-cancel-request">
                            <div class="interview-title">
                                <i class="fas fa-rotate-left"></i>
                                <span>面談キャンセル依頼</span>
                            </div>
                            <p class="interview-body-copy">この面談をキャンセルして、やり取り中に戻しますか？</p>
                            @if($isCast && !$msg->is_mine && empty($blockState['is_blocked']))
                                <button type="button" class="interview-change-schedule-btn js-interview-cancel-accept">承諾する</button>
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

    @if($isCast && !empty($canCancelStatus))
        <input type="hidden" id="send-cancel-status-enabled" value="1">
    @endif

    @if($isCast && !empty($canRequestFulltime))
        <div class="talk-result-panel">
            <div class="talk-result-panel-copy">
                <span class="talk-result-panel-title">本入店リクエスト</span>
                <p>体験採用後に、本入店希望を店舗へ送信できます。</p>
            </div>
            <div class="talk-result-panel-actions">
                <button type="button" id="send-fulltime-request" class="btn-interview btn-interview-result">
                    <i class="fas fa-paper-plane"></i>
                    <span>本入店をリクエスト</span>
                </button>
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
                        <input type="hidden" name="talk_topic" value="{{ $initialTalkTopic ?? '' }}">
                        <input type="hidden" name="talk_job_kind" value="{{ $initialTalkJobKind ?? '' }}">
                    @endif
                    <button type="button" id="open-talk-action-menu" class="btn-chat-action" aria-label="メニューを開く">
                        <i class="fas fa-plus"></i>
                    </button>
                    <div class="chat-input-wrapper">
                        <textarea name="message" rows="1" placeholder="メッセージを入力..." class="focus:outline-none"></textarea>
                    </div>
                    <input type="file" id="talk-image-input" name="image" accept="image/*" style="display:none;">
                    <button type="submit" class="btn-send" aria-label="送信"><i class="fas fa-paper-plane"></i></button>
                </div>
            </form>
        </div>
    @endif
</div>

{{-- 面談日候補 送信モーダル（店舗側のみ利用） --}}
<div id="talk-action-menu-overlay" class="interview-modal-overlay interview-modal-overlay-sheet" aria-hidden="true">
    <div class="interview-modal interview-menu-sheet">
        <div class="interview-modal-header">
            <h2>メニュー</h2>
            <button type="button" class="interview-modal-close js-talk-action-menu-close" aria-label="閉じる">&times;</button>
        </div>
        <div class="talk-action-grid">
            @if(!$isCast)
                <button type="button" id="open-interview-modal" class="talk-action-item talk-action-item-primary {{ $isInterviewOfferLocked ? 'talk-action-item-disabled' : '' }}" @if($isInterviewOfferLocked) disabled @endif>
                    <span class="talk-action-icon"><i class="far fa-calendar-alt"></i></span>
                    <span>面談候補日を送信</span>
                </button>
            @endif
            <button type="button" id="open-image-send-menu" class="talk-action-item talk-action-item-disabled" disabled>
                <span class="talk-action-icon"><i class="far fa-image"></i></span>
                <span>現在実装中</span>
            </button>
            <button type="button" id="open-template-send-menu" class="talk-action-item">
                <span class="talk-action-icon"><i class="far fa-file-alt"></i></span>
                <span>定型文を使う</span>
            </button>
            @if(!empty($canSelectResult))
                <button type="button" id="open-hire-modal-menu" class="talk-action-item">
                    <span class="talk-action-icon"><i class="fas fa-circle-check"></i></span>
                    <span>採用を送る</span>
                </button>
                <button type="button" id="open-reject-modal-menu" class="talk-action-item">
                    <span class="talk-action-icon"><i class="fas fa-circle-xmark"></i></span>
                    <span>不採用を送る</span>
                </button>
            @endif
        </div>
    </div>
</div>

<div id="talk-template-menu-overlay" class="interview-modal-overlay interview-modal-overlay-sheet" aria-hidden="true">
    <div class="interview-modal interview-menu-sheet">
        <div class="interview-modal-header">
            <h2>定型文を選択</h2>
            <button type="button" class="interview-modal-close js-talk-template-close" aria-label="閉じる">&times;</button>
        </div>
        <div id="talk-template-menu-list" class="talk-template-list"></div>
    </div>
</div>

@if(!$isCast)
<div id="job-kind-modal-overlay" class="interview-modal-overlay interview-modal-overlay-sheet" aria-hidden="true">
    <div class="interview-modal interview-menu-sheet">
        <div class="interview-modal-header">
            <h2>求人種別の設定</h2>
            <button type="button" class="interview-modal-close js-job-kind-close" aria-label="閉じる">&times;</button>
        </div>
        <p id="talk-job-kind-guidance" class="interview-modal-desc">面談日を送る前に求人種別を確定してください。面談日確定後は変更できません。</p>
        <div class="interview-option-group">
            <label for="talk-room-job-kind">現在の求人種別</label>
            <select id="talk-room-job-kind" @if(empty($canSelectTalkJobKind)) disabled @endif>
                <option value="">未選択</option>
                <option value="trial">体験入店</option>
                <option value="fulltime">本入店</option>
                <option value="help">ヘルプ</option>
            </select>
        </div>
        @if(!empty($canSelectTalkJobKind))
            <div class="interview-modal-footer">
                <button type="button" class="btn-interview-cancel js-job-kind-close">閉じる</button>
                <button type="button" id="save-talk-job-kind" class="btn-interview-submit">この内容で設定する</button>
            </div>
            <p id="talk-job-kind-save-status" class="talk-job-kind-save-status">未保存</p>
        @else
            <p class="talk-job-kind-lock-note">面談日確定後は変更できません。</p>
        @endif
    </div>
</div>

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
            <div class="interview-option-group interview-option-group-grid">
                <label>候補1</label>
                <input type="date" name="option1_date">
                <input type="time" name="option1_time">
            </div>
            <div class="interview-option-group interview-option-group-grid">
                <label>候補2（任意）</label>
                <input type="date" name="option2_date">
                <input type="time" name="option2_time">
            </div>
            <div class="interview-option-group interview-option-group-grid">
                <label>候補3（任意）</label>
                <input type="date" name="option3_date">
                <input type="time" name="option3_time">
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
<div id="work-complete-confirm-overlay" class="interview-modal-overlay" aria-hidden="true">
    <div class="interview-modal interview-confirm-modal">
        <div class="interview-modal-header">
            <h2 id="work-complete-confirm-title">勤務完了報告</h2>
            <button type="button" class="interview-modal-close js-work-complete-close" aria-label="閉じる">&times;</button>
        </div>
        <p id="work-complete-confirm-desc" class="interview-modal-desc">完了しますか？</p>
        <div class="interview-modal-footer">
            <button type="button" class="btn-interview-cancel js-work-complete-close">いいえ</button>
            <button type="button" id="work-complete-confirm-submit" class="btn-interview-submit">はい</button>
        </div>
    </div>
</div>

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
            <p style="margin-top:6px; font-size:12px; color:#d4c4a4;">採用確定時は入力必須です。</p>
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
    var chatForm = document.getElementById('chat-form');
    var actionUrl = chatForm ? chatForm.getAttribute('data-action-url') : '';
    var csrfToken = chatForm ? chatForm.querySelector('input[name="_token"]').value : '';
    var selectedTalkJobKind = window.selectedTalkJobKind || '';
    var workCompleteOverlay = document.getElementById('work-complete-confirm-overlay');
    var workCompleteTitle = document.getElementById('work-complete-confirm-title');
    var workCompleteDesc = document.getElementById('work-complete-confirm-desc');
    var workCompleteSubmitBtn = document.getElementById('work-complete-confirm-submit');

    var pendingReviewApplicationId = null;
    var pendingReviewTarget = null;
    var bonusFlowMode = 'review';

    function showReviewModalWithTarget(applicationId, target) {
        document.getElementById('review-form-application-id').value = applicationId;
        document.getElementById('review-modal-loading').style.display = 'block';
        document.getElementById('review-modal-form-wrap').style.display = 'none';
        var errEl = document.getElementById('review-modal-error');
        if (errEl) { errEl.textContent = ''; errEl.classList.remove('show'); }
        reviewModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        buildReviewRatingCards(target.review_contents || []);
        var cmtEl = document.getElementById('review-modal-comment');
        if (cmtEl) cmtEl.value = '';
        document.getElementById('review-modal-loading').style.display = 'none';
        document.getElementById('review-modal-form-wrap').style.display = 'block';
        checkReviewFormReady();
    }

    function openWorkCompleteFlow(applicationId) {
        if (selectedTalkJobKind === 'trial' || selectedTalkJobKind === 'help') {
            bonusFlowMode = 'work_complete';
            if (workCompleteTitle) {
                workCompleteTitle.textContent = '勤務完了報告';
            }
            if (workCompleteDesc) {
                workCompleteDesc.textContent = '完了しますか？';
            }
            if (workCompleteOverlay) {
                workCompleteOverlay.setAttribute('aria-hidden', 'false');
            }
            pendingReviewApplicationId = applicationId;
            return;
        }
        fetch(requestTargetUrl + '?application_id=' + encodeURIComponent(applicationId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success || !data.request_target) {
                window.alert(data.message || 'データの取得に失敗しました。');
                return;
            }
            pendingReviewApplicationId = applicationId;
            pendingReviewTarget = data.request_target;
            bonusFlowMode = 'bonus_then_review';
            showBonusConfirmModal(applicationId, pendingReviewTarget);
        })
        .catch(function () {
            window.alert('読み込みに失敗しました。');
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
            if (id) openWorkCompleteFlow(id);
        });
    });
    document.querySelectorAll('.js-work-complete-close').forEach(function (el) {
        el.addEventListener('click', function () {
            if (workCompleteOverlay) workCompleteOverlay.setAttribute('aria-hidden', 'true');
        });
    });
    if (workCompleteOverlay) {
        workCompleteOverlay.addEventListener('click', function (e) {
            if (e.target === workCompleteOverlay) {
                workCompleteOverlay.setAttribute('aria-hidden', 'true');
            }
        });
    }
    if (workCompleteSubmitBtn) {
        workCompleteSubmitBtn.addEventListener('click', function () {
            if (!actionUrl || !csrfToken || !chatForm) return;
            if (workCompleteOverlay) workCompleteOverlay.setAttribute('aria-hidden', 'true');
            var partnerId = chatForm.getAttribute('data-partner-id');
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    partner_id: partnerId,
                    action_type: 'work_complete_report'
                })
            })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, json: j }; }); })
            .then(function (res) {
                if (!res.ok || !res.json.success) {
                    throw new Error((res.json && res.json.message) || '勤務完了報告に失敗しました。');
                }
                window.location.reload();
            })
            .catch(function (err) {
                window.alert(err.message || '勤務完了報告に失敗しました。');
            });
        });
    }
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
            if (res.success) {
                closeReviewModal();
                window.location.reload();
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
        var confirmChecked = form.querySelector('input[name="confirm_checked"]');
        var errEl = document.getElementById('bonus-confirm-error');
        if (!confirmChecked || !confirmChecked.checked) {
            errEl.textContent = 'チェックを入れてください。';
            errEl.style.display = 'block';
            return;
        }
        if (bonusFlowMode === 'bonus_then_review') {
            if (!actionUrl || !csrfToken || !chatForm) {
                errEl.textContent = '送信先の設定が不足しています。';
                errEl.style.display = 'block';
                return;
            }
            var partnerIdForBonus = chatForm.getAttribute('data-partner-id');
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    partner_id: partnerIdForBonus,
                    action_type: 'bonus_achievement_report'
                })
            })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, json: j }; }); })
            .then(function (reportRes) {
                if (!reportRes.ok || !reportRes.json.success) {
                    throw new Error((reportRes.json && reportRes.json.message) || 'ボーナス達成報告に失敗しました。');
                }
                closeBonusModal();
                if (pendingReviewApplicationId && pendingReviewTarget) {
                    if (pendingReviewTarget.review_exists) {
                        window.alert('レビュー投稿は完了しています。');
                        return;
                    }
                    showReviewModalWithTarget(pendingReviewApplicationId, pendingReviewTarget);
                }
            })
            .catch(function (reportErr) {
                errEl.textContent = reportErr.message || 'ボーナス達成報告に失敗しました。';
                errEl.style.display = 'block';
            });
            return;
        }
        var fd = new FormData(form);
        fd.set('confirm_bonus_condition', '1');
        var btn = document.getElementById('bonus-confirm-submit-btn');
        if (btn) btn.disabled = true;
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
                window.location.href = '{{ route("cast.mypage.management") }}';
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