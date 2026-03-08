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
                    @include('shops.interaction.keep', ['c' => $c, 'profileRoute' => $profileRoute ?? 'shop.castprofileview.show'])
                @endforeach
            @endif
        </div>

        {{-- タブ２：ライク (LIKE/MATCH) — 送ったLIKE / 受け取ったLIKE の2エリア表示 --}}
        <div id="pane-like" class="tab-pane">
            {{-- 受け取ったLIKE --}}
            <section class="like-area">
                <h3 class="like-area-title">受け取ったLIKE</h3>
                <div class="like-list-container">
                    @if (empty($receivedLikeCasts))
                        <div class="no-data-wrapper">
                            <i class="fas fa-heart opacity-10 text-5xl mb-3 block"></i>
                            <p class="no-data-msg">受け取ったいいねはまだありません。</p>
                        </div>
                    @else
                        @foreach($receivedLikeCasts as $c)
                            @include('shops.interaction.like', ['c' => $c, 'profileRoute' => $profileRoute ?? 'shop.castprofileview.show'])
                        @endforeach
                    @endif
                </div>
            </section>

            {{-- 送ったLIKE --}}
            <section class="like-area">
                <h3 class="like-area-title">送ったLIKE</h3>
                <div class="like-list-container">
                    @if (empty($sentLikeCasts))
                        <div class="no-data-wrapper">
                            <i class="fas fa-heart opacity-10 text-5xl mb-3 block"></i>
                            <p class="no-data-msg">送ったいいねはまだありません。</p>
                        </div>
                    @else
                        @foreach($sentLikeCasts as $c)
                            @include('shops.interaction.like', ['c' => $c, 'profileRoute' => $profileRoute ?? 'shop.castprofileview.show'])
                        @endforeach
                    @endif
                </div>
            </section>
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
                    @include('shops.interaction.footprint', ['c' => $c, 'profileRoute' => $profileRoute ?? 'shop.castprofileview.show'])
                @endforeach
            @endif
        </div>
</div>
@endsection

@push('scripts')
{{-- 共通タブ切り替えJSの読み込み --}}
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
@endpush