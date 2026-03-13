@extends('layouts.app')

@section('header_title', $partnerName . ' 様')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
@endpush

@push('scripts')
<script>
    window.isCastTalkRoom = {!! request()->is('cast/*') ? 'true' : 'false' !!};
</script>
<script src="{{ asset('assets/js/talk-room.js') }}"></script>
@endpush

@section('content')
@php
    $isCast = request()->is('cast/*');
    $sendUrl = $isCast ? route('cast.talk.send') : route('shop.talk.send');
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
            @if(!$isCast && empty($blockState['is_blocked']))
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

    {{-- メッセージ表示エリア --}}
    <div class="chat-messages" id="chat-messages">
        @forelse($messages as $msg)
            <div class="message-row {{ $msg->is_mine ? 'msg-right' : 'msg-left' }}">
                @if(!$msg->is_mine)
                    <div class="msg-avatar-wrap">
                        <img src="{{ $partnerAvatar }}" alt="" class="msg-avatar">
                    </div>
                @endif
                <div class="message-block">
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
                                        @if($isCast && !$msg->selected_option && !$msg->is_mine && empty($blockState['is_blocked']))
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
                                <p class="interview-note">
                                    確定日時: {{ $selectedOption->format('Y年n月j日 H:i') }}
                                </p>
                            @endif
                        </div>
                    @elseif($msg->type === 3)
                        <div class="message-bubble message-bubble-interview">
                            <div class="interview-title">
                                <i class="far fa-calendar-check"></i>
                                <span>面談日が確定しました</span>
                            </div>
                            <p class="interview-confirmed-date">
                                {{ \Carbon\Carbon::parse($msg->selected_option)->format('Y年n月j日 H:i') }}
                            </p>
                        </div>
                    @else
                        <div class="message-bubble">
                            <p class="m-0">{!! nl2br(e(trim($msg->content))) !!}</p>
                        </div>
                    @endif
                    <div class="msg-footer">
                        <span class="msg-time">{{ $msg->created_at->format('H:i') }}</span>
                        @if($msg->is_mine)
                            <span class="msg-status"><i class="fas fa-check"></i></span>
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

    @if(!empty($canSelectResult))
        <div class="talk-result-panel">
            <div class="talk-result-panel-copy">
                <span class="talk-result-panel-title">面談結果を選択</span>
                <p>面談日が確定しています。結果をこのトーク内から送信できます。</p>
            </div>
            <div class="talk-result-panel-actions">
                <button type="button" id="send-hire-message" class="btn-interview btn-interview-result">
                    <i class="fas fa-circle-check"></i>
                    <span>採用</span>
                </button>
                <button type="button" id="send-reject-message" class="btn-interview btn-interview-result btn-interview-result--negative">
                    <i class="fas fa-circle-xmark"></i>
                    <span>不採用</span>
                </button>
            </div>
        </div>
    @endif

    {{-- 入力エリア --}}
    @if(!empty($canSend))
        <div class="chat-input-area">
            <form id="chat-form" data-url="{{ $sendUrl }}" data-action-url="{{ $actionUrl }}" data-partner-id="{{ $partnerId }}">
                @csrf
                <div class="chat-input-wrapper">
                    <textarea name="message" rows="1" placeholder="メッセージを入力..." class="focus:outline-none"></textarea>
                    <button type="submit" class="btn-send"><i class="fas fa-paper-plane"></i></button>
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

@endsection