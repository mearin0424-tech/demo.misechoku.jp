@extends('layouts.app-v2')

@section('title', 'お役立ちコラム')

@php
    $showRoute = $isGuest
        ? 'pages.support.column.show'
        : ($isCast ? 'cast.column.show' : 'shop.column.show');
@endphp

@section('content')
<div class="support-column-page">
    <div class="support-column-header">
        <h1 class="support-column-title">お役立ちコラム</h1>
        <p class="support-column-lead">
            ミセチョクの使い方や活用のヒントをまとめたコラムです。
            @if($isGuest)
                <br>ログインすると、キャスト・店舗向けの記事をそれぞれのアプリ内からも閲覧できます。
            @endif
        </p>
    </div>

    <div class="support-column-list">
        @forelse($articles as $article)
            <article class="support-column-item">
                <a href="{{ route($showRoute, $article->slug) }}" class="support-column-item-link">
                    <h2 class="support-column-item-title">{{ $article->title }}</h2>
                    <p class="support-column-item-meta">
                        @if($article->columnCategory)
                            カテゴリ：{{ $article->columnCategory->name }}
                            ／
                        @endif
                        更新：{{ $article->published_at?->format('Y-m-d') ?? $article->updated_at->format('Y-m-d') }}
                    </p>
                </a>
            </article>
        @empty
            <p class="support-column-empty">表示できるコラムがありません。</p>
        @endforelse
    </div>

    @if($articles->hasPages())
        <div class="support-column-pagination">
            {{ $articles->links() }}
        </div>
    @endif
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
    font-family: var(--font-sans);
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
    padding: 0;
    border: 1px solid rgba(212, 175, 55, 0.4);
    overflow: hidden;
}

.support-column-item-link {
    display: block;
    padding: 16px 14px;
    color: inherit;
    text-decoration: none;
}

@media (min-width: 768px) {
    .support-column-item-link {
        padding: 18px 20px;
    }
}

.support-column-item-link:hover .support-column-item-title {
    color: #f0e0a0;
}

.support-column-item-title {
    font-size: 1.05rem;
    margin-bottom: 6px;
    transition: color 0.15s ease;
}

.support-column-item-meta {
    font-size: 0.72rem;
    color: #b69f9f;
    margin-bottom: 8px;
}

.support-column-empty {
    font-size: 0.9rem;
    color: #b69f9f;
    text-align: center;
    padding: 24px 8px;
}

.support-column-pagination {
    margin-top: 24px;
}

.support-column-pagination .pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    list-style: none;
    padding: 0;
    margin: 0;
}

.support-column-pagination .pagination li span,
.support-column-pagination .pagination li a {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid rgba(212, 175, 55, 0.35);
    color: #efe3e3;
    font-size: 0.85rem;
}

.support-column-pagination .pagination li.active span {
    background: rgba(212, 175, 55, 0.2);
    color: #f0e0a0;
}
</style>
@endpush
