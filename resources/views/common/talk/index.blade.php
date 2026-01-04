@extends('layouts.app')

@section('title', 'メッセージ一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/talk.css') }}">
@endpush

@section('content')
@php
    $isCast = request()->is('cast/*');
    $requestTabText = $isCast ? 'オファー' : 'リクエスト';
@endphp

{{-- タブメニュー --}}
<div class="talk-tabs">
    <div class="tab-item active" data-target="ongoing">やり取り中</div>
    <div class="tab-item" data-target="requests">{{ $requestTabText }}</div>
</div>

<div class="talk-list-container">
    {{-- やり取り中パネル --}}
    <div id="pane-ongoing" class="talk-content-pane active">
        @forelse($ongoingTalks as $talk)
            @include('common.talk.partials.list-item', ['talk' => $talk])
        @empty
            <div class="no-messages">
                <i class="fas fa-comments"></i>
                <p>やり取り中のメッセージはありません</p>
            </div>
        @endforelse
    </div>

    {{-- リクエスト / オファー パネル --}}
    <div id="pane-requests" class="talk-content-pane">
        @forelse($requestTalks as $talk)
            @include('common.talk.partials.list-item', ['talk' => $talk])
        @empty
            <div class="no-messages">
                <i class="fas fa-paper-plane"></i>
                <p>{{ $requestTabText }}はありません</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/talk-list.js') }}"></script>
@endpush