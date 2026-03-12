<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | 管理画面</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --admin-bg: #111827;
            --admin-surface: #1f2937;
            --admin-surface-soft: #111827;
            --admin-border: #4b5563;
            --admin-text: #e5e7eb;
            --admin-muted: #9ca3af;
            --admin-accent: #60a5fa;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--admin-bg);
            color: var(--admin-text);
        }
        .admin-layout {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .admin-header {
            height: 56px;
            padding: 0 16px;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #020617;
        }
        .admin-header-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            font-weight: 600;
        }
        .admin-header-title span {
            opacity: 0.85;
        }
        .admin-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-logout-btn {
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid var(--admin-border);
            background: transparent;
            color: var(--admin-text);
            font-size: 0.8rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .admin-logout-btn:hover {
            background: #b91c1c;
            border-color: #fca5a5;
            color: #fef2f2;
        }
        .admin-main {
            flex: 1;
            padding: 16px;
        }
        .admin-shell {
            max-width: 1040px;
            margin: 0 auto;
        }
    </style>
    @stack('admin-styles')
</head>
<body>
    <div class="admin-layout">
        <header class="admin-header">
            <div class="admin-header-title">
                <i class="fas fa-gauge"></i>
                <span>ミセチョク 管理画面</span>
            </div>
            <div class="admin-header-actions">
                <button class="admin-logout-btn" onclick="if(confirm('ログアウトしますか？')) location.href='{{ route('login.demo') }}'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>ログアウト</span>
                </button>
            </div>
        </header>
        <main class="admin-main">
            <div class="admin-shell">
                @yield('content')
            </div>
        </main>
    </div>
    @stack('admin-scripts')
</body>
</html>

