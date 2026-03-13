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
@endsection

