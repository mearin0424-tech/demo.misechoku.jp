@extends('layouts.app-v2')

@php
    $title = 'システムエラー';
    if (isset($exception) && method_exists($exception, 'getStatusCode')) {
        if ($exception->getStatusCode() === 404) $title = 'ページが見つかりません';
        elseif ($exception->getStatusCode() === 503) $title = 'サービス利用不可';
    }
@endphp

@section('title', $title)
@section('body-class', 'page-error')

@push('head-styles')
<style>
    .error-page-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - var(--header-height, 60px) - var(--footer-height, 75px) - env(safe-area-inset-bottom, 0px));
        padding: 20px;
        text-align: center;
    }
    .error-screen-img {
        width: 100%;
        max-width: 320px;
        height: auto;
        display: block;
        margin-bottom: 18px;
        object-fit: contain;
    }
    .error-page-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--color-accent-text, #f2cadf);
        letter-spacing: 0.06em;
        margin-bottom: 8px;
    }
    .error-page-desc {
        font-size: 13px;
        color: var(--color-text-sub, #a0a0a0);
        line-height: 1.6;
        margin-bottom: 18px;
    }
    .error-page-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 22px;
        border-radius: 999px;
        background: linear-gradient(to right, var(--color-accent-grad-from, #eba8c8), var(--color-accent-grad-to, #b0507f));
        color: var(--color-on-accent-strong, #fff);
        font-weight: 700;
        text-decoration: none;
        box-shadow: var(--shadow-btn-3d);
    }
</style>
@endpush

@section('content')
<div class="error-page-wrap">
    <img src="{{ asset('assets/images/guide/system-error-screen.png') }}" alt="システムエラー" class="error-screen-img">
    <p class="error-page-title">{{ $title }}</p>
    <p class="error-page-desc">
        @if(isset($exception) && method_exists($exception, 'getStatusCode') && $exception->getStatusCode() === 404)
            お探しのページは見つかりませんでした。<br>URL をご確認の上、再度お試しください。
        @elseif(isset($exception) && method_exists($exception, 'getStatusCode') && $exception->getStatusCode() === 503)
            ただいまメンテナンス中です。しばらく時間を置いてから再度お試しください。
        @else
            一時的なエラーが発生しました。<br>時間をおいて再度お試しください。
        @endif
    </p>
    <a href="{{ url('/') }}" class="error-page-back">
        <i class="fas fa-home"></i> ホームへ戻る
    </a>
</div>
@endsection
