@extends('layouts.app')

@section('title', 'マスタ設定管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">マスタ設定管理</h1>
        <p class="admin-description">
            レビュー項目や検索で利用するタグなど、全体で共通利用するマスタデータを管理します。<br>
            ここから各マスタ編集画面へ遷移できるようにします（詳細画面は今後拡張）。
        </p>

        <div class="admin-grid">
            <div class="admin-card">
                <h2>レビュー項目マスタ</h2>
                <p>レビューの評価項目（接客、雰囲気など）を追加・編集・無効化します。</p>
                <p class="admin-badge">今後実装予定</p>
            </div>

            <div class="admin-card">
                <h2>検索タグマスタ</h2>
                <p>検索条件で利用するタグ（エリア、業態、こだわり条件など）を管理します。</p>
                <p class="admin-badge">今後実装予定</p>
            </div>

            <div class="admin-card">
                <h2>コラムカテゴリ／タグ</h2>
                <p>お役立ちコラムで利用するカテゴリ・タグを管理します。</p>
                <p class="admin-badge">ColumnRepository と連携予定</p>
            </div>
        </div>
    </div>

    <style>
        .admin-page {
            padding: 24px 0;
        }
        .admin-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #FDF0B2;
        }
        .admin-description {
            font-size: 0.9rem;
            color: #e5d4d4;
            margin-bottom: 18px;
            line-height: 1.6;
        }
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .admin-card {
            padding: 16px;
            border-radius: 14px;
            background: radial-gradient(circle at 0% 0%, rgba(251, 191, 36, 0.1), rgba(24, 16, 4, 0.96));
            border: 1px solid rgba(251, 191, 36, 0.5);
            color: #fef9c3;
        }
        .admin-card h2 {
            font-size: 1rem;
            margin-bottom: 6px;
            font-weight: 700;
        }
        .admin-card p {
            font-size: 0.85rem;
            line-height: 1.6;
        }
        .admin-badge {
            display: inline-block;
            margin-top: 10px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(30, 64, 175, 0.85);
            font-size: 0.75rem;
            color: #e5e7eb;
        }
    </style>
@endsection

