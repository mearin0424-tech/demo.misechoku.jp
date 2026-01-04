@extends('layouts.app')

@section('content')
<div id="talk-room-container" class="flex flex-col h-full">
    <div class="talk-header p-4 border-b border-[#4d1a1a] flex items-center">
        <a href="{{ route('shop.talk.index') }}" class="mr-4 text-[#d4af37]"><i class="fas fa-chevron-left"></i></a>
        <h2 class="text-xl font-bold">{{ $castName }} 様</h2>
    </div>

    <div class="chat-messages flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
        {{-- メッセージループ --}}
        @foreach($messages as $msg)
            <div class="flex {{ $msg->is_mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] p-3 rounded-2xl {{ $msg->is_mine ? 'bg-[#d4af37] text-black' : 'bg-[#2d0b0b] text-white border border-[#4d1a1a]' }}">
                    {{ $msg->content }}
                    <span class="block text-[10px] mt-1 opacity-60 text-right">{{ $msg->created_at->format('H:i') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="chat-input-area p-4 bg-[#220a0a] border-t border-[#4d1a1a]">
        <form class="flex gap-2" id="chat-form">
            <input type="text" class="flex-1 bg-[#120505] border border-[#4d1a1a] rounded-full px-4 text-white outline-none" placeholder="メッセージを入力...">
            <button type="submit" class="w-12 h-12 bg-[#b91c1c] rounded-full flex items-center justify-center text-white">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/components/chat-handler.js'])
@endpush