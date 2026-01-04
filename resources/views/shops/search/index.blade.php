@extends('layouts.app')

@section('title', 'SEARCH')

@push('styles')
{{-- 検索画面専用、共通サブヘッダー、およびリスト用のCSSを読み込み --}}
<link rel="stylesheet" href="{{ asset('assets/css/search.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/interaction.css') }}">
@endpush

@section('content')
@php
    // 現在のアクティブタブを判定（デフォルトは timeline）
    $activeTab = request()->query('tab', 'timeline');
@endphp

<div class="has-sub-header">
    {{-- 共通サブヘッダー：デザインと配置を他画面と統一 --}}
    @include('layouts.parts.sub-header', [
        'tabs' => [
            ['id' => 'pane-timeline', 'label' => 'タイムライン', 'active' => ($activeTab === 'timeline')],
            ['id' => 'pane-list', 'label' => '一覧・検索', 'active' => ($activeTab === 'list')],
            ['id' => 'pane-ai', 'label' => 'AIレコメンド', 'active' => ($activeTab === 'ai')]
        ]
    ])

    <div class="contents">

        {{-- 1. タイムラインパネル --}}
        <div id="pane-timeline" class="tab-pane {{ $activeTab === 'timeline' ? 'active' : '' }}">
            <div class="filter-sort-row">
                <select class="custom-select">
                    <option>新着順</option>
                    <option>おすすめ順</option>
                </select>
            </div>

            <div class="timeline-container">
                @foreach($timelineData as $post)
                <div class="timeline-card">
                    <div class="tl-header">
                        <img src="{{ $post['img'] }}" class="tl-icon" alt="{{ $post['name'] }}">
                        <div class="tl-info">
                            <h3>{{ $post['name'] }}</h3>
                            <span>{{ $post['time'] }}</span>
                        </div>
                    </div>
                    <div class="tl-body">
                        {!! nl2br(e($post['text'])) !!}
                    </div>
                    @if(!empty($post['tags']))
                    <div class="tl-tags">
                        @foreach($post['tags'] as $tag)
                            <span class="tag-item">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- 2. 一覧・検索パネル --}}
        <div id="pane-list" class="tab-pane {{ $activeTab === 'list' ? 'active' : '' }}">
            <div class="search-filter-box">
                <div class="search-input-group">
                    <input type="text" class="search-input-field" placeholder="キーワード・エリア検索">
                    <i class="fas fa-search search-inner-icon"></i>
                </div>
                <div class="search-select-row">
                    <select class="filter-select"><option>エリア未指定</option></select>
                    <select class="filter-select"><option>ジャンル未指定</option></select>
                </div>
            </div>

            <ul class="connection-list">
                @foreach($casts as $cast)
                <li class="connection-item">
                    <img src="{{ $cast['img'] }}" class="conn-thumb" alt="{{ $cast['name'] }}">
                    <div class="conn-info">
                        <div class="conn-name">{{ $cast['name'] }} ({{ $cast['age'] }})</div>
                        <div class="conn-tags">#{{ implode(' #', $cast['tags']) }}</div>
                    </div>
                    {{-- 遷移先ルートを profile.show に統一 --}}
                    <a href="{{ route('profile.show', ['id' => $cast['id']]) }}" class="conn-action-btn">詳細</a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- 3. AIレコメンドパネル --}}
        <div id="pane-ai" class="tab-pane {{ $activeTab === 'ai' ? 'active' : '' }}">
            <div class="ai-matching-intro">
                <div class="ai-icon-wrapper">
                    <i class="fas fa-robot"></i>
                </div>
                <h3 class="serif-font">AIマッチング</h3>
                <p>あなたの好みを分析して<br>おすすめのキャストを表示します。</p>
                <button class="btn-gold-outline">診断をはじめる</button>
            </div>
        </div>

    </div>
</div>

{{-- FABメニューは不要なため削除されました --}}

@endsection

@push('scripts')
{{-- 共通タブ切り替えロジックを読み込み --}}
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
@endpush