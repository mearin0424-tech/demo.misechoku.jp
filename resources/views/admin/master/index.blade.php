@extends('layouts.admin')

@section('title', 'マスタ設定管理')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">マスタ設定管理</h1>
        <p class="admin-description">
            レビュー項目や検索で利用するタグなど、全体で共通利用するマスタデータを管理します。<br>
            編集画面は今後拡張予定です。
        </p>

        <div class="admin-grid">
            <div class="admin-card">
                <h2>レビュー項目マスタ</h2>
                <p>レビューの評価項目（接客、雰囲気など）の一覧・追加・無効化。</p>
                <p class="admin-note">※ 実装予定</p>
            </div>

            <div class="admin-card">
                <h2>検索タグマスタ</h2>
                <p>検索条件で利用するタグ（エリア、業態、こだわり条件など）の管理。</p>
                <p class="admin-note">※ 実装予定</p>
            </div>

            <div class="admin-card">
                <h2>コラムカテゴリ／タグ</h2>
                <p>お役立ちコラムで利用するカテゴリ・タグの管理。</p>
                <p class="admin-note">※ ColumnRepository と連携予定</p>
            </div>
        </div>
    </div>

    <style>
        .admin-page {
            padding: 24px 0;
        }
        .admin-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #e5e7eb;
        }
        .admin-description {
            font-size: 0.9rem;
            color: #cbd5f5;
            margin-bottom: 16px;
            line-height: 1.6;
        }
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
        }
        .admin-card {
            padding: 12px 14px;
            border-radius: 8px;
            background: rgba(17, 24, 39, 0.95);
            border: 1px solid rgba(55, 65, 81, 0.9);
            color: #e5e7eb;
        }
        .admin-card h2 {
            font-size: 0.95rem;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .admin-card p {
            font-size: 0.85rem;
            line-height: 1.5;
        }
        .admin-note {
            margin-top: 6px;
            font-size: 0.8rem;
            color: #9ca3af;
        }
    </style>
@endsection

