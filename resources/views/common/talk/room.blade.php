@extends('layouts.app')

@section('header_title', $partnerName . ' 様')
@section('body-class', 'talk-room-page')

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
@endphp

<div id="talk-room-container" class="talk-room">
    {{-- メッセージ表示エリア --}}
    <div class="chat-messages" id="chat-messages">
        @forelse($messages as $msg)
            <div class="message-row {{ $msg->is_mine ? 'msg-right' : 'msg-left' }}">
                <div class="message-bubble">
                    <p class="msg-text">{{ $msg->content }}</p>
                    <div class="msg-footer">
                        <span class="msg-time">{{ $msg->created_at->format('H:i') }}</span>
                        @if($msg->is_mine)
                            <span class="msg-status"><i class="fas fa-check"></i></span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="talk-room-empty">
                <div class="talk-room-empty-icon"><i class="fas fa-comments"></i></div>
                <p class="talk-room-empty-title">メッセージはまだありません</p>
                <p class="talk-room-empty-desc">最初のメッセージを送って会話を始めましょう</p>
            </div>
        @endforelse
    </div>

    {{-- 入力エリア --}}
    <div class="chat-input-area">
        <form id="chat-form" data-url="{{ $sendUrl }}" data-partner-id="{{ $partnerId }}">
            @csrf
            <div class="chat-input-wrapper">
                <textarea name="message" rows="1" placeholder="メッセージを入力..." maxlength="500"></textarea>
                <button type="submit" class="btn-send" title="送信"><i class="fas fa-paper-plane"></i></button>
            </div>
        </form>
    </div>
</div>
@endsection