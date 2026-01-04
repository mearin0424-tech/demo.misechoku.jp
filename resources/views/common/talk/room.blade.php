@extends('layouts.app')

@section('content')
@php
    $isCast = request()->is('cast/*');
    $backRoute = $isCast ? 'cast.talk.index' : 'shop.talk.index';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
@endpush

@push('scripts')
{{-- 切り出した外部JSを読み込む --}}
<script src="{{ asset('assets/js/talk-room.js') }}"></script>
@endpush

<div id="talk-room-container" class="flex flex-col h-full bg-[#120505]">
    {{-- ヘッダー --}}
    <div class="talk-header p-4 border-b border-[#4d1a1a] flex items-center bg-[#220a0a]">
        <a href="{{ route($backRoute) }}" class="mr-4 text-[#d4af37] text-xl">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h2 class="text-lg font-bold text-white">{{ $partnerName }} 様</h2>
    </div>

    {{-- メッセージ表示エリア --}}
    <div class="chat-messages" id="chat-messages">
    @foreach($messages as $msg)
        <div class="flex {{ $msg->is_mine ? 'justify-end msg-right' : 'justify-start msg-left' }}">
            <div class="message-bubble">
                <p>{{ $msg->content }}</p>
                <span class="msg-time">{{ $msg->created_at->format('H:i') }}</span>
            </div>
        </div>
    @endforeach
</div>

    {{-- 入力エリア --}}
   <div class="chat-input-area p-4 bg-[#220a0a] border-t border-[#4d1a1a]">
        <form class="flex gap-2" id="chat-form" 
            data-url="{{ route(request()->is('cast/*') ? 'cast.talk.send' : 'shop.talk.send') }}" 
            data-partner-id="{{ $partnerId }}">
            @csrf {{-- CSRFトークンを追加 --}}
            <div class="chat-input-wrapper flex-1">
                <input type="text" name="message" class="w-full bg-transparent text-white outline-none" placeholder="メッセージを入力...">
            </div>
            <button type="submit" class="btn-send">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>
@endsection