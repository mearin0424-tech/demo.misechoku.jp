@php
    $title = 'システムエラー';
    if (isset($exception) && method_exists($exception, 'getStatusCode')) {
        if ($exception->getStatusCode() === 404) $title = 'ページが見つかりません';
        elseif ($exception->getStatusCode() === 503) $title = 'サービス利用不可';
    }
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | {{ config('app.name', 'ミセチョク') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { min-height: 100vh; background: #0a0a0a; display: flex; align-items: center; justify-content: center; }
        .error-screen-img { width: 100%; max-width: 100%; height: auto; display: block; object-fit: contain; }
    </style>
</head>
<body>
    <img src="{{ asset('assets/images/guide/system-error-screen.png') }}" alt="システムエラー" class="error-screen-img">
</body>
</html>
