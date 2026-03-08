@extends('layouts.app')

{{-- 各ページから渡されるオコジョのメッセージをセット --}}
@section('guide_message', $guideMessage ?? '気になる相手を探してみてね！')

@section('title', 'SEARCH')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/search.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">
@endpush

@section('content')
@php
    // 現在のプレフィックス（shop または cast）を取得
    $prefix = request()->is('cast/*') ? 'cast' : 'shop';
    // 読み込む部品のビュー名プレフィックス（cast は casts.parts、shop は shops.search.parts）
    $partsView = $prefix === 'cast' ? 'casts.parts' : 'shops.search.parts';
    $listTabLabel = $prefix === 'cast' ? '求人検索' : '一覧・検索';
@endphp

<div class="has-sub-header">
    @include('layouts.parts.sub-header', [
        'tabs' => [
            ['id' => 'pane-timeline', 'label' => 'タイムライン', 'active' => true],
            ['id' => 'pane-list', 'label' => $listTabLabel, 'active' => false],
            ['id' => 'pane-ai', 'label' => 'AIレコメンド', 'active' => false]
        ]
    ])
</div>

<div class="contents tab-page-body">
    {{-- パネル1：タイムライン --}}
    <div id="pane-timeline" class="tab-pane active">
            @forelse($timelineData as $post)
                {{-- 役割に応じたタイムラインカードを読み込む --}}
                @include($partsView . '.timeline-card', ['post' => $post])
            @empty
                <div class="text-center py-20 text-sm opacity-40">投稿はありません。</div>
            @endforelse
        </div>

        {{-- パネル2：一覧・検索 / 求人検索（cast時） --}}
        <div id="pane-list" class="tab-pane">
            <div class="search-filter-box">
                {{-- 役割に応じたフィルター（検索窓）を読み込む --}}
                @include($partsView . '.filter')
            </div>
            
            <ul class="connection-list">
                @forelse($items as $item)
                    {{-- 役割に応じたリストアイテム（キャスト用/店舗用）を読み込む --}}
                    @include($partsView . '.list-item', ['item' => $item])
                @empty
                    <div class="text-center py-20 text-sm opacity-40">該当する相手は見つかりませんでした。</div>
                @endforelse
            </ul>
        </div>

        {{-- パネル3：AI --}}
        <div id="pane-ai" class="tab-pane">
            <div class="text-center py-20">
                <i class="fas fa-robot text-4xl text-[#d4af37] mb-4"></i>
                <h3 class="text-white">AIマッチング</h3>
                <p class="text-gray-400 text-sm mt-2">あなたにおすすめの{{ $prefix === 'shop' ? 'キャスト' : 'お店' }}を表示します。</p>
            </div>
        </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
<script src="{{ asset('assets/js/search-detail.js') }}"></script>
@endpush