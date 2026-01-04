@extends('layouts.app')

@section('header_title', $partnerName . ' 様')

@push('styles')
{{-- トークルーム専用の装飾。メッセージの左右寄せや入力欄の固定など --}}
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
@endpush

@push('scripts')
{{-- 送信ボタンの制御や、テキストエリアの自動リサイズなど --}}
<script src="{{ asset('assets/js/talk-room.js') }}"></script>
@endpush

@section('content')
@php
    $isCast = request()->is('cast/*');
    // 送信先URLを動的に判定
    $sendUrl = $isCast ? route('cast.talk.send') : route('shop.talk.send');
@endphp

<div id="talk-room-container" class="flex flex-col h-full bg-[#120505]">

    {{-- メッセージ表示エリア --}}
    <div class="chat-messages" id="chat-messages">
        @forelse($messages as $msg)
        <div class="message-row {{ $msg->is_mine ? 'msg-right' : 'msg-left' }}">
            <div class="message-bubble">
                {{ $msg->content }}
                
                <div class="msg-footer">
                    <span class="msg-time">{{ $msg->created_at->format('H:i') }}</span>
                    {{-- 自分が送ったメッセージかつ送信済みの場合はチェックマークを表示 --}}
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

    {{-- 
        入力エリア 
        chat-input-area 自体は talk.css で fixed (600px幅中央) に制御されています。
    --}}
    <div class="chat-input-area">

        <form id="chat-form" data-url="{{ $sendUrl }}" data-partner-id="{{ $partnerId }}">
            @csrf
            <div class="chat-input-wrapper">
                <textarea name="message" rows="1" placeholder="メッセージを入力..." class="focus:outline-none"></textarea>
                <button type="submit" class="btn-send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection