@extends('layouts.app')

@section('title', 'INTERACTION')

@push('styles')
{{-- 共通サブヘッダーおよび画面専用CSSの読み込み --}}
<link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/interaction.css') }}">
@endpush

@section('content')
<div class="has-sub-header">
    @include('layouts.parts.sub-header', [
        'tabs' => [
            ['id' => 'pane-keep', 'label' => 'キープ', 'active' => true],
            ['id' => 'pane-like', 'label' => 'ライク', 'active' => false],
            ['id' => 'pane-footprint', 'label' => '足あと', 'active' => false]
        ]
    ])
</div>

<div class="tab-content-container contents tab-page-body">
    
    {{-- タブ１：キープ (KEEP) --}}
    <div id="pane-keep" class="tab-pane active">
            @if (empty($keepCasts))
                <div class="no-data-wrapper">
                    <i class="fas fa-star opacity-10 text-5xl mb-3 block"></i>
                    <p class="no-data-msg">お気に入り登録したキャストはいません。</p>
                </div>
            @else
                @foreach($keepCasts as $c)
                    @include('shops.interaction.keep', ['c' => $c, 'profileRoute' => $profileRoute ?? 'cast.profile.show'])
                @endforeach
            @endif
        </div>

        {{-- タブ２：ライク (LIKE/MATCH) --}}
        <div id="pane-like" class="tab-pane">
            {{-- ライク内専用の切り替え（ここは既存ロジックを維持） --}}
            <div class="sub-nav-mini">
                <button class="sub-nav-btn active" onclick="filterLikes('to-me')">RECEIVED</button>
                <button class="sub-nav-btn" onclick="filterLikes('from-me')">SENT</button>
            </div>
            
            <div id="like-list-container">
                @if (empty($likeCasts))
                    <div class="no-data-wrapper">
                        <i class="fas fa-heart opacity-10 text-5xl mb-3 block"></i>
                        <p class="no-data-msg">いいねはまだ届いていません。</p>
                    </div>
                @else
                    @foreach($likeCasts as $c)
                        @include('shops.interaction.like', ['c' => $c, 'profileRoute' => $profileRoute ?? 'cast.profile.show'])
                    @endforeach
                @endif
            </div>
        </div>

        {{-- タブ３：足あと (FOOTPRINT) --}}
        <div id="pane-footprint" class="tab-pane">
            @if (empty($footprintCasts))
                <div class="no-data-wrapper">
                    <i class="fas fa-shoe-prints opacity-10 text-5xl mb-3 block"></i>
                    <p class="no-data-msg">まだ足あと（訪問履歴）はありません。</p>
                </div>
            @else
                @foreach($footprintCasts as $c)
                    @include('shops.interaction.footprint', ['c' => $c, 'profileRoute' => $profileRoute ?? 'cast.profile.show'])
                @endforeach
            @endif
        </div>
</div>
@endsection

@push('scripts')
{{-- 共通タブ切り替えJSの読み込み --}}
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
<script>
    /**
     * ライクパネル内専用のフィルタリングロジック
     */
    function filterLikes(type) {
        // ライクタブ内のサブボタンのみ切り替え
        const container = document.getElementById('pane-like');
        if (!container) return;
        const btns = container.querySelectorAll('.sub-nav-btn');
        btns.forEach(btn => btn.classList.remove('active'));
        if (event && event.currentTarget) event.currentTarget.classList.add('active');
    }
</script>
@endpush