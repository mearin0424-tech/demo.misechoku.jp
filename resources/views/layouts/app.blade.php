<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title'){{ isset($title) ? ' | ' . config('app.name', 'ミセチョク') : config('app.name', 'ミセチョク') }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Shippori+Mincho:wght@400;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-header.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/character-guide.css') }}">

    <script src="{{ asset('assets/js/app.js') }}" defer></script>

    @stack('styles')
</head>
    
<body>
    <div id="bg-layer"></div>
    <div id="menu-overlay" class="menu-overlay"></div>

    <div id="app">
        {{-- メインレイアウト部分を縦並びのFlexコンテナとして包む --}}
        <div class="main-layout-container flex-1 flex flex-col min-w-0">

            {{-- ヘッダー --}}
            @include('layouts.parts.header')

            {{-- @yield('guide_message') で各ページの設定内容を注入する --}}
            @include('layouts.parts.character-guide', ['guideMessage' => $__env->yieldContent('guide_message')])

            {{-- メインコンテンツ --}}
            <main id="main-content">
                @yield('content')
            </main>

            {{-- ボトムナビ（モバイル用固定） --}}
            @include('layouts.parts.footer')
        </div>
        {{-- サイドバー --}}
        @include('layouts.parts.sidebar')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('assets/js/character-guide.js') }}"></script>
    @stack('scripts')
</body>
</html>