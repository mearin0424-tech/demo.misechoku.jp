@extends('layouts.app-v2')

@section('title', 'SEARCH')
@section('body-class', request()->is('cast/*') && ($activeTab ?? null) === 'pane-ai' ? 'page-search page-search-ai' : 'page-search')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/search.css') }}?v=20260614-toast">
<link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">
@endpush

@section('content')
@php
    // 現在のプレフィックス（shop または cast）を取得
    $prefix = request()->is('cast/*') ? 'cast' : 'shop';
    $showAiTab = $prefix === 'cast';
    $partsView = $prefix === 'cast' ? 'casts.parts' : 'shops.search.parts';
    $activeTab = $activeTab ?? 'pane-list';
    $searchTab = $searchTab ?? 'list';

    // タブ：cast は「検索／AIレコメンド」の 2 タブ、shop はタブなし。
    $tabsForHeader = [];
    if ($showAiTab) {
        $tabsForHeader = [
            ['id' => 'pane-list', 'label' => '検索', 'url' => route('cast.search.index', ['tab' => 'list']), 'active' => $activeTab === 'pane-list'],
            ['id' => 'pane-ai', 'label' => 'AIレコメンド', 'url' => route('cast.search.index', ['tab' => 'ai']), 'active' => $activeTab === 'pane-ai'],
        ];
    }

    $aiTabUrl = $showAiTab ? route('cast.search.index', ['tab' => 'ai']) : null;
    $aiPersonalityTestUrl = $showAiTab
        ? asset('personality-test') . '?' . http_build_query(['return_to' => $aiTabUrl])
        : null;
    $aiRecommendItems = $showAiTab ? collect($items)->map(function (array $item) use ($prefix) {
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
    })->values()->all() : [];
@endphp

@if(!empty($tabsForHeader))
<div class="has-sub-header">
    @include('layouts.parts.sub-header', ['tabs' => $tabsForHeader])
</div>
@endif

<div class="{{ !empty($tabsForHeader) ? 'tab-page-body' : 'search-page-body' }}">
    {{-- 検索パネル：タイムライン＋一覧を統合した画面 --}}
    <div id="pane-list" class="tab-pane {{ $activeTab === 'pane-list' ? 'active' : '' }}" style="{{ $activeTab !== 'pane-list' ? 'display:none' : '' }}">
        <div class="search-filter-box">
            {{-- 役割に応じたフィルター（検索窓・並び替え）／詳細検索は FAB --}}
            @include($partsView . '.filter')
        </div>

        <button type="button" id="open-detail-search" class="search-detail-fab" aria-controls="detail-search-modal" aria-expanded="false" aria-label="詳細検索">
            <i class="fas fa-sliders-h" aria-hidden="true"></i>
            <span id="detail-search-badge" class="search-detail-fab__badge" style="display: none;" aria-hidden="true">0</span>
        </button>

        <ul class="connection-list connection-list--search">
            @forelse($items as $item)
                {{-- 役割に応じたリストアイテム（キャスト用/店舗用） --}}
                @include($partsView . '.list-item', ['item' => $item])
            @empty
                <div class="text-center py-20 text-sm opacity-40">該当する相手は見つかりませんでした。</div>
            @endforelse
        </ul>
    </div>

    @if($showAiTab)
        {{-- パネル：AI --}}
        <div id="pane-ai" class="tab-pane {{ $activeTab === 'pane-ai' ? 'active' : '' }}" style="{{ $activeTab !== 'pane-ai' ? 'display:none' : '' }}">
            {{-- 自由入力 AIチャット（テンプレ駆動・LLM非使用） --}}
            @if($prefix === 'cast')
                <section
                    class="ai-chat"
                    data-ai-chat-root
                    data-endpoint="{{ route('cast.search.ai-chat') }}"
                    data-avatar="{{ asset('assets/images/guide/guide-character.png') }}"
                >
                    <header class="ai-chat__header">
                        <div class="ai-chat__header-icon"><i class="fas fa-sparkles"></i></div>
                        <div class="ai-chat__header-text">
                            <p class="ai-chat__header-title">AI コンシェルジュ <span class="ai-chat__badge">BETA</span></p>
                            <p class="ai-chat__header-sub">自由に話しかけてみてね。あなたに合うお店を見つけるよ。</p>
                        </div>
                    </header>
                    <div class="ai-chat__thread" data-ai-thread aria-live="polite"></div>
                    <div class="ai-chat__quick-replies" data-ai-quick-replies></div>
                    <form class="ai-chat__form" data-ai-form autocomplete="off">
                        <input
                            type="text" class="ai-chat__input" data-ai-input
                            name="message" maxlength="500" required
                            placeholder="例: 六本木で未経験OK、時給4500円以上"
                        >
                        <button type="submit" class="ai-chat__send" data-ai-send aria-label="送信">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </section>
            @endif

            @if(empty($personalityType))
                <div class="ai-recommend__intro-card">
                    <div class="ai-recommend__intro-title">接客タイプ診断結果をご利用いただくと、おすすめの精度を高められます</div>
                    <p class="ai-recommend__intro-text">
                        AIレコメンドでは診断ロジックは実行せず、保存済みの診断結果のみを読み込みます。<br>
                        まだ未登録の場合は、先に接客タイプ診断をご実施ください。
                    </p>
                    <a href="{{ $aiPersonalityTestUrl }}" target="_blank" rel="noopener noreferrer" class="ai-recommend__intro-link">
                        接客タイプ診断を開く
                    </a>
                </div>
            @endif
            <div
                class="ai-recommend"
                data-ai-recommend-root
                data-role="{{ $prefix }}"
                data-avatar="{{ asset('assets/images/guide/guide-character.png') }}"
            >
                <div class="ai-recommend__chat" data-ai-chat aria-live="polite"></div>
            </div>

            <script type="application/json" id="ai-recommend-data">{!! json_encode([
                'role' => $prefix,
                'items' => $aiRecommendItems,
                'personalityType' => $personalityType ?? null,
                'personalityTestUrl' => $aiPersonalityTestUrl,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
<script src="{{ asset('assets/js/search-detail.js') }}"></script>
<script src="{{ asset('assets/js/favorite-quick.js') }}?v=20260614-toast"></script>
@if($showAiTab)
<script src="{{ asset('assets/js/ai-recommend.js') }}"></script>
@if($prefix === 'cast')
<script src="{{ asset('assets/js/ai-chat.js') }}"></script>
@endif
@endif
@endpush
