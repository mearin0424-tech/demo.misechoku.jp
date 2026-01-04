@extends('layouts.app')

{{-- 
    共通ヘッダー（layouts.header）に表示するタイトル。
    ここを定義するだけで、ヘッダー中央に名前が表示されます。
--}}
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
    {{-- 
        【重要】独自のヘッダーDIVは削除しました。
        layouts.header.blade.php が「戻るボタン」と「タイトル」を自動で表示します。
    --}}

    {{-- メッセージ表示エリア --}}
    <div class="chat-messages" id="chat-messages">
        @forelse($messages as $msg)
            {{-- 
                message-row: flexコンテナ。
                msg-right: 自分のメッセージ（右寄せ）。
                msg-left: 相手のメッセージ（左寄せ）。
            --}}
            <div class="message-row {{ $msg->is_mine ? 'msg-right' : 'msg-left' }}">
                <div class="message-bubble">
                    {{-- 改行を反映するために CSS で white-space: pre-wrap を設定済み --}}
                    {{ $msg->content }}
                    
                    {{-- 送信時刻 --}}
                    <span class="block text-right text-[10px] opacity-50 mt-1">
                        {{ $msg->created_at->format('H:i') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 mt-20">
                <i class="fas fa-comments opacity-20 text-5xl mb-4 block"></i>
                <p>メッセージはまだありません</p>
            </div>
        @endforelse
    </div>

    {{-- 
        入力エリア 
        chat-input-area 自体は talk.css で fixed (600px幅中央) に制御されています。
    --}}
    <div class="chat-input-area">
        {{-- 
            JSがAjax送信する際に必要な URL と 相手ID を data属性として持たせます。
            ここを消すとメッセージが送信できなくなるので注意です。
        --}}
        <form id="chat-form" data-url="{{ $sendUrl }}" data-partner-id="{{ $partnerId }}">
            @csrf
            <div class="chat-input-wrapper">
                {{-- inputではなくtextareaを使うことで自動リサイズに対応 --}}
                <textarea 
                    name="message" 
                    rows="1" 
                    placeholder="メッセージを入力..."
                    class="focus:outline-none"
                ></textarea>

                <button type="submit" class="btn-send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection