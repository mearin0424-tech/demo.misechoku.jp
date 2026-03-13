@extends('layouts.app')

@section('header_title', $partnerName . ' 様')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/talk-room.js') }}"></script>
@endpush

@section('content')
@php
    $isCast = request()->is('cast/*');
    $sendUrl = $isCast ? route('cast.talk.send') : route('shop.talk.send');
    $partnerAvatar = $partnerAvatar ?? asset('assets/images/common/no-image.png');
@endphp

<div id="talk-room-container" class="flex flex-col h-full bg-[#120505]">
    {{-- LINE風：相手のアイコンと名前（上部バー） --}}
    <div class="talk-room-header">
        <div class="talk-room-header-inner">
            <img src="{{ $partnerAvatar }}" alt="" class="talk-room-header-avatar">
            <span class="talk-room-header-name">{{ $partnerName }}</span>
        </div>
        @if(!$isCast)
        <div class="talk-room-header-actions">
            <button type="button" id="open-interview-modal" class="btn-interview">
                <i class="far fa-calendar-alt"></i>
                <span>面談日を提案</span>
            </button>
            <button type="button" id="send-hire-message" class="btn-interview btn-interview-result">
                <i class="fas fa-circle-check"></i>
                <span>採用</span>
            </button>
            <button type="button" id="send-reject-message" class="btn-interview btn-interview-result btn-interview-result--negative">
                <i class="fas fa-circle-xmark"></i>
                <span>不採用</span>
            </button>
        </div>
        @endif
    </div>

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
                    <div class="message-bubble">
                        <p class="m-0">{!! nl2br(e(trim($msg->content))) !!}</p>
                    </div>
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

    {{-- 入力エリア --}}
    <div class="chat-input-area">
        <form id="chat-form" data-url="{{ $sendUrl }}" data-partner-id="{{ $partnerId }}">
            @csrf
            <div class="chat-input-wrapper">
                {{-- JSが探している [name="message"] を確実に持たせます --}}
                <textarea name="message" rows="1" placeholder="メッセージを入力..." class="focus:outline-none"></textarea>
                <button type="submit" class="btn-send"><i class="fas fa-paper-plane"></i></button>
            </div>
        </form>
    </div>
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

@push('scripts')
<script>
    window.isCastTalkRoom = {!! $isCast ? 'true' : 'false' !!};
</script>
@endpush
@endsection