@extends('layouts.app')

@section('title', $article->title)

@php
    $indexRoute = $isGuest
        ? 'pages.support.column'
        : ($isCast ? 'cast.column.index' : 'shop.column.index');
@endphp

@section('content')
<div class="support-column-page support-column-detail">
    <nav class="support-column-breadcrumb">
        <a href="{{ route($indexRoute) }}">お役立ちコラム一覧</a>
        <span aria-hidden="true">／</span>
        <span>{{ $article->title }}</span>
    </nav>

    <article class="support-column-article">
        <header class="support-column-article-header">
            <h1 class="support-column-title">{{ $article->title }}</h1>
            <p class="support-column-item-meta">
                @if($article->columnCategory)
                    カテゴリ：{{ $article->columnCategory->name }} ／
                @endif
                公開：{{ $article->published_at?->format('Y-m-d H:i') ?? '-' }}
            </p>
        </header>
        <div class="support-column-body">
            {!! nl2br(e($article->body)) !!}
        </div>
    </article>
</div>
@endsection

@push('styles')
<style>
.support-column-page.support-column-detail {
    padding: 20px 16px 40px;
    color: #f9f5f5;
}

.support-column-breadcrumb {
    font-size: 0.78rem;
    margin-bottom: 20px;
    color: #b69f9f;
}

.support-column-breadcrumb a {
    color: var(--color-gold, #d4af37);
    text-decoration: none;
}

.support-column-breadcrumb a:hover {
    text-decoration: underline;
}

.support-column-article-header {
    margin-bottom: 20px;
}

.support-column-article .support-column-title {
    font-size: 1.25rem;
    line-height: 1.45;
    margin-bottom: 10px;
}

.support-column-body {
    font-size: 0.92rem;
    line-height: 1.85;
    color: #efe3e3;
    word-break: break-word;
}
</style>
@endpush
