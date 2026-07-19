@extends('layouts.app-v2')

@section('title', 'SEARCH')
@section('body-class', request()->is('cast/*') && ($activeTab ?? null) === 'pane-ai' ? 'page-search page-search-ai' : 'page-search')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/search.css') }}?v=20260719-location-pill">
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

    // タブ：cast は「検索／AIコンシェルジュ／キープ」、shop は「検索／キープ」。
    // キープリストは旧 KEEPS（フッターメニュー）から SEARCH 内へ移設。
    if ($showAiTab) {
        $tabsForHeader = [
            ['id' => 'pane-list', 'label' => '検索', 'url' => route('cast.search.index', ['tab' => 'list']), 'active' => $activeTab === 'pane-list'],
            ['id' => 'pane-ai', 'label' => 'AIコンシェルジュ', 'url' => route('cast.search.index', ['tab' => 'ai']), 'active' => $activeTab === 'pane-ai'],
            ['id' => 'pane-keep', 'label' => 'キープ', 'url' => route('cast.search.index', ['tab' => 'keep']), 'active' => $activeTab === 'pane-keep'],
        ];
    } else {
        $tabsForHeader = [
            ['id' => 'pane-list', 'label' => '検索', 'url' => route('shop.search.index'), 'active' => $activeTab === 'pane-list'],
            ['id' => 'pane-keep', 'label' => 'キープ', 'url' => route('shop.search.index', ['tab' => 'keep']), 'active' => $activeTab === 'pane-keep'],
        ];
    }

    $aiTabUrl = $showAiTab ? route('cast.search.index', ['tab' => 'ai']) : null;
    $aiPersonalityTestUrl = $showAiTab
        ? asset('personality-test') . '?' . http_build_query(['return_to' => $aiTabUrl])
        : null;
@endphp

@if(!empty($tabsForHeader))
<div class="has-sub-header">
    @include('layouts.parts.sub-header', ['tabs' => $tabsForHeader])
</div>
@endif

<div class="{{ !empty($tabsForHeader) ? 'tab-page-body' : 'search-page-body' }}">
    {{-- 検索パネル：タイムライン＋一覧を統合した画面 --}}
    <div id="pane-list" class="tab-pane {{ $activeTab === 'pane-list' ? 'active' : '' }}" style="{{ $activeTab !== 'pane-list' ? 'display:none' : '' }}">
        {{-- 探索拠点の状態表示：設定済み/未設定が一目で分かるピル（タップで設定モーダル） --}}
        @include('layouts.parts.location-pill')

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
        {{-- パネル：AIコンシェルジュ（TALK 同様の全画面チャット。入力欄は最下部固定） --}}
        <div id="pane-ai" class="tab-pane {{ $activeTab === 'pane-ai' ? 'active' : '' }}" style="{{ $activeTab !== 'pane-ai' ? 'display:none' : '' }}">
            <section
                class="ai-chat"
                data-ai-chat-root
                data-endpoint="{{ route('cast.search.ai-chat') }}"
                data-avatar="{{ asset('assets/images/guide/guide-character.png') }}"
                data-personality-type="{{ $personalityType ?? '' }}"
            >
                <header class="ai-chat__header">
                    <div class="ai-chat__header-icon"><i class="fas fa-sparkles"></i></div>
                    <div class="ai-chat__header-text">
                        <p class="ai-chat__header-title">AI コンシェルジュ <span class="ai-chat__badge">BETA</span></p>
                        <p class="ai-chat__header-sub">条件・気分・悩み、なんでも話しかけてOK。あなたに合うお店を一緒に探すよ。</p>
                    </div>
                </header>

                {{-- 接客タイプ診断の状態ストリップ（登録済み: タイプ表示 / 未登録: 診断導線） --}}
                <div class="ai-chat__notice">
                    @if(!empty($personalityType))
                        <span class="ai-chat__notice-label">
                            <i class="fas fa-user-check"></i> 接客タイプ <strong>{{ $personalityType }}</strong> 登録済み
                        </span>
                        <a href="{{ $aiPersonalityTestUrl }}" target="_blank" rel="noopener noreferrer" class="ai-chat__notice-link">再診断する</a>
                    @else
                        <span class="ai-chat__notice-label">
                            <i class="fas fa-wand-magic-sparkles"></i> 接客タイプ診断でおすすめの精度が上がります
                        </span>
                        <a href="{{ $aiPersonalityTestUrl }}" target="_blank" rel="noopener noreferrer" class="ai-chat__notice-link">診断する</a>
                    @endif
                </div>

                <div class="ai-chat__thread" data-ai-thread aria-live="polite"></div>

                {{-- コンポーザー：クイックリプライ + 入力欄（チャット最下部に固定） --}}
                <div class="ai-chat__composer">
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
                </div>
            </section>
        </div>
    @endif

    {{-- パネル：キープリスト（メッセージを送る前の保存リスト + おすすめ） --}}
    <div id="pane-keep" class="tab-pane {{ $activeTab === 'pane-keep' ? 'active' : '' }}" style="{{ $activeTab !== 'pane-keep' ? 'display:none' : '' }}">
        @if($activeTab === 'pane-keep')
            @include('common.search.keep-pane')
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
<script src="{{ asset('assets/js/search-detail.js') }}?v=20260712-form-unify"></script>
<script src="{{ asset('assets/js/favorite-quick.js') }}?v=20260719-keep-only"></script>
@if($showAiTab)
<script src="{{ asset('assets/js/ai-chat.js') }}?v=20260712-ai"></script>
@endif
@endpush
