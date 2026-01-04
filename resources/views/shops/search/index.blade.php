@extends('layouts.app')

@section('title', 'Search List')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/search.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/interaction.css') }}">
@endpush

@section('content')
<div id="search-screen">
    {{-- セグメントナビ（interaction.css のクラスを再利用） --}}
    <nav class="segment-nav">
        <div class="segment-btn {{ $activeTab === 'timeline' ? 'active' : '' }}" onclick="switchSearchTab('timeline')">タイムライン</div>
        <div class="segment-btn {{ $activeTab === 'list' ? 'active' : '' }}" onclick="switchSearchTab('list')">一覧・検索</div>
        <div class="segment-btn {{ $activeTab === 'ai' ? 'active' : '' }}" onclick="switchSearchTab('ai')">AIレコメンド</div>
    </nav>

    <div class="contents">

        {{-- タブ１：タイムライン --}}
        <div id="tab-timeline" class="tab-content {{ $activeTab === 'timeline' ? 'active' : '' }}">
            <div style="padding:10px 15px; text-align:right;">
                <select style="background:var(--color-sub); color:#fff; border:1px solid var(--color-border); padding:5px; border-radius:4px;">
                    <option>新着順</option>
                    <option>おすすめ順</option>
                </select>
            </div>

            @foreach($timelineData as $post)
            <div class="timeline-card">
                <div class="tl-header">
                    <img src="{{ $post['img'] }}" class="tl-icon">
                    <div class="tl-info">
                        <h3>{{ $post['name'] }}</h3>
                        <span>{{ $post['time'] }}</span>
                    </div>
                </div>
                <div class="tl-body">
                    {!! nl2br(e($post['text'])) !!}
                </div>
                <div class="tl-tags">
                    @foreach($post['tags'] as $tag)
                        <span>{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        {{-- タブ２：一覧・検索 --}}
        <div id="tab-list" class="tab-content {{ $activeTab === 'list' ? 'active' : '' }}">
            <div class="search-filter-box">
                <div class="form-group" style="margin-bottom:10px;">
                    <input type="text" placeholder="キーワード・エリア検索" style="width:100%;">
                </div>
                <div style="display:flex; gap:10px;">
                    <select style="flex:1;"><option>エリア未指定</option></select>
                    <select style="flex:1;"><option>ジャンル未指定</option></select>
                </div>
            </div>

            <ul class="connection-list">
                @foreach($casts as $cast)
                <li class="connection-item">
                    <img src="{{ $cast['img'] }}" class="conn-thumb">
                    <div class="conn-info">
                        <div class="conn-name">{{ $cast['name'] }} ({{ $cast['age'] }})</div>
                        <div class="conn-date">#{{ implode(' #', $cast['tags']) }}</div>
                    </div>
                    <a href="{{ route('cast.profile.show', ['id' => $cast['id']]) }}" class="conn-action">詳細</a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- タブ３：AIレコメンド --}}
        <div id="tab-ai" class="tab-content {{ $activeTab === 'ai' ? 'active' : '' }}">
            <div style="text-align:center; padding:50px 20px; color:#ccc;">
                <i class="fas fa-robot" style="font-size:3rem; margin-bottom:20px; color:var(--color-gold);"></i>
                <h3 class="serif-font" style="color:var(--color-text-header);">AIマッチング</h3>
                <p style="margin-top:15px; font-size:0.9rem;">あなたの好みを分析して<br>おすすめのキャストを表示します。</p>
                <button class="btn-gold" style="margin-top:30px;">診断をはじめる</button>
            </div>
        </div>

    </div>
</div>

{{-- 検索画面専用FAB --}}
<div class="fab-container">
    <div id="fab-menu-list" class="fab-menu-list" style="display:none;">
        <button class="fab-menu-item" onclick="alert('今すぐ入れる子検索')">
            <span class="fab-label">今すぐ入れる子</span>
            <div class="fab-icon-circle"><i class="fas fa-user-clock"></i></div>
        </button>
        <button class="fab-menu-item" onclick="alert('条件検索')">
            <span class="fab-label">条件で探す</span>
            <div class="fab-icon-circle"><i class="fas fa-search"></i></div>
        </button>
    </div>
    
    <div id="fab-trigger" class="stop-propagation" onclick="toggleFab()">
        <div id="fab-icon-face" class="fab-main-icon">
            <div class="fab-icon-circle main-trigger-circle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
        <div id="fab-icon-close" class="fab-close" style="display:none;"><i class="fas fa-times"></i></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function switchSearchTab(tabName) {
        const tabs = document.querySelectorAll('.segment-btn');
        const contents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));

        // クリックされた要素に active を付与
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
        
        // URLパラメータの更新（任意）
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
    }
</script>
@endpush