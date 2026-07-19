@extends('layouts.app-v2')

@section('title', 'お知らせ')

@php
    $showRoute = 'pages.support.notices.show';
@endphp

@section('content')
<div class="support-column-page support-notice-page">
    <div class="support-column-header">
        {{-- タイトルはヘッダー中央に表示（統一方針）。ページ内はリード文のみ --}}
        <p class="page-lead">
            運営からのお知らせです。
            @if($isGuest)
                <br>ログインすると、キャスト・店舗向けの内容をそれぞれのアプリ内からも閲覧できます。
            @endif
        </p>
    </div>

    <div class="support-column-list">
        @forelse($notices as $notice)
            <article class="support-column-item">
                <a href="{{ route($showRoute, $notice->slug) }}" class="support-column-item-link">
                    <h2 class="support-column-item-title">{{ $notice->title }}</h2>
                    <p class="support-column-item-meta">
                        更新：{{ $notice->published_at?->format('Y-m-d') ?? $notice->updated_at->format('Y-m-d') }}
                    </p>
                </a>
            </article>
        @empty
            <p class="support-column-empty">表示できるお知らせがありません。</p>
        @endforelse
    </div>

    @if($notices->hasPages())
        <div class="support-column-pagination">
            {{ $notices->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.support-notice-page {
    padding: 24px 16px 32px;
    color: #f5f5f5;
}

@media (min-width: 768px) {
    .support-notice-page {
        padding: 32px 24px 40px;
    }
}
</style>
@endpush
