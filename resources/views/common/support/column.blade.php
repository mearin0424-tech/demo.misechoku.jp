@extends('layouts.app')

@section('title', 'サポートコラム')

@section('content')
<div class="support-column-page">
    <div class="support-column-header">
        <h1 class="support-column-title">サポートコラム</h1>
        <p class="support-column-lead">
            ミセチョクの使い方や活用アイデアをまとめたコラムページです。<br>
            ※デモ環境のため、以下はダミーのコンテンツ例です。
        </p>
    </div>

    <div class="support-column-list">
        <article class="support-column-item">
            <h2 class="support-column-item-title">初めてのミセチョクガイド</h2>
            <p class="support-column-item-meta">カテゴリ：基本操作 / 更新日：2026-03-10</p>
            <p class="support-column-item-summary">
                アカウント登録から検索、トークまで、ミセチョクの基本的な流れをコンパクトにまとめた入門ガイドです。
            </p>
        </article>

        <article class="support-column-item">
            <h2 class="support-column-item-title">店舗・キャストのおすすめ活用シーン</h2>
            <p class="support-column-item-meta">カテゴリ：活用事例 / 更新日：2026-03-10</p>
            <p class="support-column-item-summary">
                店舗側・キャスト側それぞれの目線で、どのようなシーンでミセチョクを活用できるかを紹介します。
            </p>
        </article>

        <article class="support-column-item">
            <h2 class="support-column-item-title">よくある質問（FAQ）のまとめ</h2>
            <p class="support-column-item-meta">カテゴリ：サポート / 更新日：2026-03-10</p>
            <p class="support-column-item-summary">
                ログインや通知設定など、よくお問い合わせいただく内容をQ&amp;A形式で整理しています。
            </p>
        </article>
    </div>
</div>
@endsection

@push('styles')
<style>
.support-column-page {
    padding: 24px 16px 32px;
    color: #f9f5f5;
}

@media (min-width: 768px) {
    .support-column-page {
        padding: 32px 24px 40px;
    }
}

.support-column-header {
    margin-bottom: 24px;
}

.support-column-title {
    font-family: 'Shippori Mincho', 'Noto Sans JP', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 1.4rem;
    margin-bottom: 8px;
    color: var(--color-gold, #d4af37);
}

.support-column-lead {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #d1c1c1;
}

.support-column-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.support-column-item {
    background: rgba(20, 7, 15, 0.9);
    border-radius: 16px;
    padding: 16px 14px;
    border: 1px solid rgba(212, 175, 55, 0.4);
}

@media (min-width: 768px) {
    .support-column-item {
        padding: 18px 20px;
    }
}

.support-column-item-title {
    font-size: 1.05rem;
    margin-bottom: 6px;
}

.support-column-item-meta {
    font-size: 0.72rem;
    color: #b69f9f;
    margin-bottom: 8px;
}

.support-column-item-summary {
    font-size: 0.85rem;
    line-height: 1.7;
    color: #efe3e3;
}
</style>
@endpush

