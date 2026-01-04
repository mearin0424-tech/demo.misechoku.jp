@extends('layouts.app')

@section('content')
@php
    $isCast = request()->is('cast/*');
    $backRoute = $isCast ? 'cast.talk.index' : 'shop.talk.index';
@endphp

<div id="talk-room-container" class="flex flex-col h-full bg-[#120505]">
    {{-- ヘッダー --}}
    <div class="talk-header p-4 border-b border-[#4d1a1a] flex items-center bg-[#220a0a]">
        <a href="{{ route($backRoute) }}" class="mr-4 text-[#d4af37] text-xl">
            <i class="fas fa-chevron-left"></i>
        </a>
        <h2 class="text-lg font-bold text-white">{{ $partnerName }} 様</h2>
    </div>

    {{-- メッセージ表示エリア --}}
    <div class="chat-messages flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
        @foreach($messages as $msg)
            <div class="flex {{ $msg->is_mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%] p-3 rounded-2xl {{ $msg->is_mine ? 'bg-[#d4af37] text-black rounded-tr-none' : 'bg-[#2d0b0b] text-white border border-[#4d1a1a] rounded-tl-none' }}">
                    <p class="text-sm">{{ $msg->content }}</p>
                    <span class="block text-[10px] mt-1 opacity-60 text-right">
                        {{ $msg->created_at->format('H:i') }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 入力エリア --}}
    <div class="chat-input-area p-4 bg-[#220a0a] border-t border-[#4d1a1a]">
        <form class="flex gap-2" id="chat-form" onsubmit="return false;">
            <input type="text" class="flex-1 bg-[#120505] border border-[#4d1a1a] rounded-full px-4 py-2 text-white outline-none focus:border-[#d4af37]" placeholder="メッセージを入力...">
            <button type="submit" class="w-12 h-12 bg-[#b91c1c] rounded-full flex items-center justify-center text-white active:scale-90 transition-transform">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>
@endsection