@extends('layouts.app')

{{-- 各ページから渡されるオコジョのメッセージをセット --}}
@section('guide_message', $guideMessage ?? '気になる相手を探してみてね！')

@section('title', 'SEARCH')
@section('body-class', $activeTab === 'pane-ai' ? 'page-search page-search-ai' : 'page-search')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/search.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">
@endpush

@section('content')
@php
    // 現在のプレフィックス（shop または cast）を取得
    $prefix = request()->is('cast/*') ? 'cast' : 'shop';
    $routeName = $prefix . '.search.index';
    $partsView = $prefix === 'cast' ? 'casts.parts' : 'shops.search.parts';
    $listTabLabel = $prefix === 'cast' ? '求人検索' : '一覧・検索';
    $activeTab = $activeTab ?? 'pane-timeline';
    $searchTab = $searchTab ?? 'timeline';
    $tabsForHeader = [
        ['id' => 'pane-timeline', 'label' => 'タイムライン', 'url' => route($routeName, ['tab' => 'timeline']), 'active' => $activeTab === 'pane-timeline'],
        ['id' => 'pane-list', 'label' => $listTabLabel, 'url' => route($routeName, ['tab' => 'list']), 'active' => $activeTab === 'pane-list'],
        ['id' => 'pane-ai', 'label' => 'AIレコメンド', 'url' => route($routeName, ['tab' => 'ai']), 'active' => $activeTab === 'pane-ai'],
    ];
    $aiRecommendItems = collect($items)->map(function (array $item) use ($prefix) {
        if ($prefix === 'cast') {
            return [
                'id' => (string) ($item['id'] ?? ''),
                'name' => (string) ($item['shop_name'] ?? 'ショップ'),
                'area' => trim((string) (($item['pref'] ?? '') . ' ' . ($item['city'] ?? ''))),
                'text' => trim((string) (($item['catch'] ?? '') . ' ' . ($item['overview'] ?? ''))),
                'image' => $item['main_img'] ?? asset('assets/images/common/no-image.png'),
                'url' => Route::has('cast.recruit.show') && !empty($item['id']) ? route('cast.recruit.show', $item['id']) : '#',
            ];
        }

        return [
            'id' => (string) ($item['id'] ?? ''),
            'name' => (string) ($item['name'] ?? 'キャスト'),
            'area' => trim((string) (($item['pref'] ?? '') . ' ' . ($item['city'] ?? ''))),
            'text' => (string) ($item['pr'] ?? ''),
            'image' => $item['img'] ?? asset('assets/images/common/no-image.png'),
            'age' => $item['age'] ?? null,
            'url' => Route::has('shop.castprofileview.show') && !empty($item['id']) ? route('shop.castprofileview.show', $item['id']) : '#',
        ];
    })->values()->all();
@endphp

<div class="has-sub-header">
    @include('layouts.parts.sub-header', ['tabs' => $tabsForHeader])
</div>

<div class="contents tab-page-body">
    {{-- パネル1：タイムライン --}}
    <div id="pane-timeline" class="tab-pane {{ $activeTab === 'pane-timeline' ? 'active' : '' }}" style="{{ $activeTab !== 'pane-timeline' ? 'display:none' : '' }}">
            @forelse($timelineData as $post)
                {{-- 役割に応じたタイムラインカードを読み込む --}}
                @include($partsView . '.timeline-card', ['post' => $post])
            @empty
                <div class="text-center py-20 text-sm opacity-40">投稿はありません。</div>
            @endforelse
        </div>

        {{-- パネル2：一覧・検索 / 求人検索（cast時） --}}
        <div id="pane-list" class="tab-pane {{ $activeTab === 'pane-list' ? 'active' : '' }}" style="{{ $activeTab !== 'pane-list' ? 'display:none' : '' }}">
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
        <div id="pane-ai" class="tab-pane {{ $activeTab === 'pane-ai' ? 'active' : '' }}" style="{{ $activeTab !== 'pane-ai' ? 'display:none' : '' }}">
            <div
                class="ai-recommend"
                data-ai-recommend-root
                data-role="{{ $prefix }}"
                data-avatar="{{ asset('assets/images/guide/guide-character.png') }}"
            >
                <div class="ai-recommend__hero">
                    <div class="ai-recommend__hero-icon">
                        <img src="{{ asset('assets/images/guide/guide-character.png') }}" alt="オコジョガイド">
                    </div>
                    <div class="ai-recommend__hero-body">
                        <p class="ai-recommend__eyebrow">AIレコメンド</p>
                        <h3>オコジョガイドがぴったりの{{ $prefix === 'cast' ? 'お店' : 'キャスト' }}を案内します</h3>
                        <p>16タイプと希望条件をもとに、会話形式でおすすめ候補を3件ずつ提案します。</p>
                    </div>
                    <button type="button" class="ai-recommend__reset" data-ai-reset>最初から</button>
                </div>

                <div class="ai-recommend__chat" data-ai-chat aria-live="polite"></div>

                <div class="ai-recommend__composer">
                    <input
                        type="text"
                        class="ai-recommend__input"
                        data-ai-input
                        placeholder="メッセージを入力..."
                        autocomplete="off"
                    >
                    <button type="button" class="ai-recommend__send" data-ai-send aria-label="送信">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <script type="application/json" id="ai-recommend-data">{!! json_encode([
                'role' => $prefix,
                'items' => $aiRecommendItems,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
<script src="{{ asset('assets/js/search-detail.js') }}"></script>
<script src="{{ asset('assets/js/ai-recommend.js') }}"></script>
@endpush