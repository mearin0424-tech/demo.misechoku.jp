@extends('layouts.app')

@section('title', $notice->title)

@php
    $indexRoute = $isGuest
        ? 'pages.support.notices'
        : ($isCast ? 'cast.notices.index' : 'shop.notices.index');
@endphp

@section('content')
<div class="support-column-page support-column-detail support-notice-detail">
    <nav class="support-column-breadcrumb">
        <a href="{{ route($indexRoute) }}">お知らせ一覧</a>
        <span aria-hidden="true">／</span>
        <span>{{ $notice->title }}</span>
    </nav>

    <article class="support-column-article">
        <header class="support-column-article-header">
            <h1 class="support-column-title">{{ $notice->title }}</h1>
            <p class="support-column-item-meta">
                公開：{{ $notice->published_at?->format('Y-m-d H:i') ?? '-' }}
            </p>
        </header>
        <div class="support-column-body">
            {!! nl2br(e($notice->body)) !!}
        </div>
    </article>
</div>
@endsection

@push('styles')
<style>
.support-notice-detail {
    padding: 20px 16px 40px;
    color: #f9f5f5;
}
</style>
@endpush
