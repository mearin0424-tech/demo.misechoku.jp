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
        <div class="flex {{ $msg->is_mine ? 'justify-end msg-right' : 'justify-start msg-left' }} w-full mb-4">
            <div class="message-bubble">
                <span class="msg-time block text-right text-[10px] opacity-60 mt-1">
                    {{ $msg->created_at->format('H:i') }}
                </span>
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