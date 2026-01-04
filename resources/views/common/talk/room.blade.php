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
@endphp

<div id="talk-room-container" class="flex flex-col h-full bg-[#120505]">
    {{-- メッセージ表示エリア --}}
    <div class="chat-messages" id="chat-messages">
        @forelse($messages as $msg)
            <div class="message-row {{ $msg->is_mine ? 'msg-right' : 'msg-left' }}">
                <div class="message-bubble">
                    <p class="m-0">{{ $msg->content }}</p>
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
@endsection