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
    <div class="talk-header-fixed bg-[#220a0a] border-b border-[#4d1a1a] p-4 flex items-center justify-between">
        <a href="{{ route(request()->is('cast/*') ? 'cast.talk.index' : 'shop.talk.index') }}">
            <i class="fas fa-chevron-left text-white"></i>
        </a>
        <h2 class="text-white text-lg font-bold">{{ $partnerName }} 様</h2>
        <div class="w-4"></div> 
    </div>

    {{-- メッセージ表示エリア --}}
    <div class="chat-messages" id="chat-messages">
        @forelse($messages as $msg)
            <div class="message-row {{ $msg->is_mine ? 'msg-right' : 'msg-left' }}">
                <div class="message-bubble">
                    {{ $msg->content }}
                    <span class="block text-right text-[10px] opacity-50 mt-1">
                        {{ $msg->created_at->format('H:i') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 mt-10">メッセージはまだありません</div>
        @endforelse
    </div>

    {{-- 入力エリア --}}
   <div class="chat-input-area">
        <form id="chat-form">
            @csrf
            <div class="chat-input-wrapper">
                <textarea name="message" rows="1" placeholder="メッセージを入力..."></textarea>
                <button type="submit" class="btn-send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection