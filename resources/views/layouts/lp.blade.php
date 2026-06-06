<!DOCTYPE html>
<html lang="ja">
<head>
    @php
        $pageTitle = trim($__env->yieldContent('title'));
        $metaTitle = trim($__env->yieldContent('meta_title'));
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: 'ミセチョクのデモサイトです。';
        $metaImage = trim($__env->yieldContent('meta_image')) ?: asset('assets/images/pwa/icon-512.png');
        $canonicalUrl = trim($__env->yieldContent('canonical')) ?: url()->current();
        $resolvedTitle = $metaTitle !== ''
            ? $metaTitle
            : ($pageTitle !== '' ? $pageTitle . ' | ' . config('app.name', 'ミセチョク') : config('app.name', 'ミセチョク'));
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="{{ config('app.name', 'ミセチョク') }}">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $resolvedTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $resolvedTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">
    <meta name="theme-color" content="#fbf7ee">
    <link rel="icon" href="data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" fill="#fbf7ee"/><text x="16" y="22" font-size="18" text-anchor="middle" fill="#a78bfa">店</text></svg>') }}" type="image/svg+xml">
    <title>{{ $resolvedTitle }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            margin: 0;
            font-family: var(--font-sans);
            color: #2a1d0f;
            background: #fbf7ee;
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }
        img { max-width: 100%; height: auto; display: block; }
        a { color: inherit; }
        button { font-family: inherit; }
    </style>

    @stack('styles')
</head>
<body class="@yield('body-class')">
    @yield('content')
    @stack('scripts')
</body>
</html>
