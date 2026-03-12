@extends('layouts.app')

@section('title', '管理ダッシュボード')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">管理ダッシュボード</h1>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="admin-grid">
            <a href="{{ route('bk.deposits.index') }}" class="admin-card">
                <h2>入金・振込管理</h2>
                <p>店舗からの入金状況とキャストへの振込ステータスを一覧で確認します。</p>
            </a>

            <a href="{{ route('bk.sales.index') }}" class="admin-card">
                <h2>売上管理</h2>
                <p>サブスクリプションと仲介料の売上状況を集計・確認します。</p>
            </a>

            <a href="{{ route('bk.masters.index') }}" class="admin-card">
                <h2>マスタ設定管理</h2>
                <p>レビュー項目や検索タグなどのマスタデータを設定します。</p>
            </a>

            <a href="{{ route('bk.columns.index') }}" class="admin-card">
                <h2>コラム管理</h2>
                <p>お役立ちコラムの作成・編集・公開設定を行います。</p>
            </a>

            <a href="{{ route('bk.inquiries.index') }}" class="admin-card">
                <h2>問合せ管理</h2>
                <p>運営への問い合わせ内容を確認し、対応状況を管理します。</p>
            </a>
        </div>
    </div>

    <style>
        .admin-page {
            padding: 24px 0;
        }
        .admin-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: #FDF0B2;
        }
        .admin-alert {
            background: rgba(56, 189, 248, 0.12);
            border: 1px solid rgba(56, 189, 248, 0.7);
            color: #e0f2fe;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .admin-card {
            display: block;
            padding: 16px;
            border-radius: 14px;
            background: radial-gradient(circle at 0% 0%, rgba(253, 240, 178, 0.08), rgba(12, 4, 6, 0.9));
            border: 1px solid rgba(230, 208, 128, 0.3);
            text-decoration: none;
            color: #F5E6E6;
            transition: transform 0.12s ease, box-shadow 0.16s ease, border-color 0.16s ease;
        }
        .admin-card h2 {
            font-size: 1rem;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .admin-card p {
            font-size: 0.85rem;
            opacity: 0.85;
            line-height: 1.5;
        }
        .admin-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.6);
            border-color: rgba(252, 211, 77, 0.85);
        }
    </style>
@endsection

