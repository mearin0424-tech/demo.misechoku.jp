@extends('layouts.app')

@section('title', 'Connections')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/interaction.css') }}">

@endpush

@section('content')
<div id="connection-screen">
    {{-- セグメントナビ（旧 Connection.php のロジックを完全再現） --}}
    <nav class="segment-nav">
        <div class="segment-btn active" data-tab="keep">
            <span class="tab-label">KEEP</span>
            <span class="tab-count numeric-font">{{ count($keepCasts) }}</span>
        </div>
        <div class="segment-btn" data-tab="like">
            <span class="tab-label">MATCH</span>
            <span class="tab-count numeric-font">{{ count($likeCasts) }}</span>
        </div>
        <div class="segment-btn" data-tab="footprint">
            <span class="tab-label">FOOTPRINT</span>
            <span class="tab-count numeric-font">{{ count($footprintCasts) }}</span>
        </div>
    </nav>

    <div class="tab-content-container contents inner">
        
        {{-- タブ１：KEEP --}}
        <div id="tab-keep" class="tab-pane active">
            @if (empty($keepCasts))
                <p class="no-data-msg opacity-70">お気に入り登録したキャストはいません。</p>
            @else
                @foreach($keepCasts as $c)
                    @include('shops.interaction.keep', ['c' => $c])
                @endforeach
            @endif
        </div>

        {{-- タブ２：LIKE --}}
        <div id="tab-like" class="tab-pane">
            <div class="sub-nav">
                <button class="sub-nav-btn active" onclick="filterLikes('to-me')">RECEIVED</button>
                <button class="sub-nav-btn" onclick="filterLikes('from-me')">SENT</button>
            </div>
            <div id="like-list-container">
                @if (empty($likeCasts))
                    <p class="no-data-msg opacity-70">いいねはまだ届いていません。</p>
                @else
                    @foreach($likeCasts as $c)
                        @include('shops.interaction.like', ['c' => $c])
                    @endforeach
                @endif
            </div>
        </div>

        {{-- タブ３：FOOTPRINT --}}
        <div id="tab-footprint" class="tab-pane">
            @if (empty($footprintCasts))
                <p class="no-data-msg opacity-70">まだ足あと（訪問履歴）はありません。</p>
            @else
                @foreach($footprintCasts as $c)
                    @include('shops.interaction.footprint', ['c' => $c])
                @endforeach
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script>
        {{-- 旧 js ロジックをそのまま移植 --}}
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.segment-btn');
            const panes = document.querySelectorAll('.tab-pane');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => t.classList.remove('active'));
                    panes.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById('tab-' + tabId).classList.add('active');
                });
            });
        });

        function filterLikes(type) {
            console.log('Filter:', type);
        }
    </script>
@endpush