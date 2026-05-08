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
        ]
    ])
</div>

<div class="tab-content-container contents tab-page-body">

    {{-- タブ：キープ (KEEP) --}}
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
</div>
@endsection

@push('scripts')
{{-- 共通タブ切り替えJSの読み込み --}}
<script src="{{ asset('assets/js/sub-header.js') }}"></script>
@endpush
