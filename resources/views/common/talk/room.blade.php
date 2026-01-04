@extends('layouts.app')
@section('title', 'Talk')
@section('content')
@php
    $isCast = request()->is('cast/*');
    $backRoute = $isCast ? 'cast.talk.index' : 'shop.talk.index';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/talk-room.js') }}"></script>
@endpush

<div id="talk-room-container" class="flex flex-col h-full bg-[#120505]">
    {{-- ヘッダー --}}
    <div class="talk-header">
        <a href="{{ route($backRoute) }}" class="back-link"><i class="fas fa-chevron-left"></i></a>
        <h2 class="partner-name">{{ $partnerName }} 様</h2>
    </div>

    {{-- メッセージ表示エリア --}}
    <div class="chat-messages" id="chat-messages">
        @foreach($messages as $msg)
            {{-- 自分が送ったメッセージ (is_mine) は msg-right クラスで右寄せ --}}
            <div class="message-row {{ $msg->is_mine ? 'msg-right' : 'msg-left' }}">
                <div class="message-bubble">
                    <p>{{ $msg->content }}</p>
                    <span class="msg-time">{{ $msg->created_at->format('H:i') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 入力エリア --}}
   <div class="chat-input-area">
        <form id="chat-form" data-url="{{ route($isCast ? 'cast.talk.send' : 'shop.talk.send') }}" data-partner-id="{{ $partnerId }}">
            @csrf
            <div class="chat-input-wrapper">
                <input type="text" name="message" autocomplete="off" placeholder="メッセージを入力...">
                <button type="submit" class="btn-send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection