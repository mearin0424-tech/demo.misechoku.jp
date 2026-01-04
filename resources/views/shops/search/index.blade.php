@extends('layouts.app')

@section('title', 'SEARCH')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/search.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">
@endpush

@section('content')
<div class="has-sub-header">
    @include('layouts.parts.sub-header', [
        'tabs' => [
            ['id' => 'pane-timeline', 'label' => 'タイムライン', 'active' => true],
            ['id' => 'pane-list', 'label' => '一覧・検索', 'active' => false],
            ['id' => 'pane-ai', 'label' => 'AIレコメンド', 'active' => false]
        ]
    ])

    <div class="contents">
        {{-- パネル1：タイムライン --}}
        <div id="pane-timeline" class="tab-pane active">
            @foreach($timelineData as $post)
            <div class="timeline-card">
                <div class="tl-header">
                    <img src="{{ $post['img'] }}" class="tl-icon">
                    <div class="tl-info">
                        <h3>{{ $post['name'] }}</h3>
                        <span>{{ $post['time'] }}</span>
                    </div>
                </div>
                <div class="tl-body">{!! nl2br(e($post['text'])) !!}</div>
            </div>
            @endforeach
        </div>

        {{-- パネル2：一覧・検索 --}}
        <div id="pane-list" class="tab-pane">
            <div class="search-filter-box">
                <input type="text" placeholder="キーワード・エリア検索" class="w-full p-2 bg-[#2a1a1a] border border-[#4d1a1a] text-white">
            </div>
            <ul class="connection-list">
                @foreach($casts as $cast)
                <li class="connection-item">
                    <img src="{{ $cast['img'] }}" class="conn-thumb">
                    <div class="conn-info">
                        <div class="conn-name">{{ $cast['name'] }} ({{ $cast['age'] }})</div>
                    </div>
                    @if(Route::has('profile.show'))
                        <a href="{{ route('cast.profile.show', ['id' => $cast['id']]) }}" class="conn-action-btn">詳細</a>
                    @else
                        <span class="text-xs opacity-30">詳細準備中</span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>

        {{-- パネル3：AI --}}
        <div id="pane-ai" class="tab-pane">
            <div class="text-center py-20">
                <i class="fas fa-robot text-4xl text-[#d4af37] mb-4"></i>
                <h3 class="text-white">AIマッチング</h3>
                <p class="text-gray-400 text-sm mt-2">おすすめのキャストを表示します。</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
@endpush