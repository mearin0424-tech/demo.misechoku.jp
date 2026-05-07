@extends('layouts.app')

@section('title', 'ミセチョク')

@push('styles')
<style>
    .welcome-container {
        text-align: center;
        padding: 64px 0 48px;
    }
    .welcome-logo {
        width: min(250px, 64vw);
        margin-bottom: 36px;
    }
    .welcome-title {
        font-family: var(--font-serif);
        color: var(--gold);
        margin: 0 0 28px;
        font-size: clamp(1.05rem, 4.6vw, 1.4rem);
        line-height: 1.5;
        letter-spacing: 0.04em;
        padding: 0 var(--content-padding-x);
    }
    .welcome-choice-box {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 0 var(--content-padding-x);
    }
    .welcome-choice-card {
        width: 100%;
        box-sizing: border-box;
        padding: 28px 22px;
        border-radius: 18px;
        background: var(--color-card);
        border: 1px solid var(--gold);
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.45);
    }
    .welcome-choice-card i {
        font-size: 2.6rem;
        color: var(--gold);
        margin-bottom: 16px;
        display: block;
    }
    .welcome-choice-card h3 {
        margin: 0 0 8px;
        font-family: var(--font-serif);
        color: var(--color-text-header);
        font-size: 1.05rem;
        letter-spacing: 0.04em;
    }
    .welcome-choice-card p {
        margin: 0 0 18px;
        font-size: 0.84rem;
        color: var(--color-text);
        line-height: 1.6;
    }
    .welcome-choice-card .btn-gold {
        display: inline-flex;
        justify-content: center;
        width: 100%;
        box-sizing: border-box;
    }
    .welcome-detail-link {
        display: block;
        margin-top: 14px;
        color: var(--color-text);
        font-size: 0.78rem;
        opacity: 0.72;
    }
    .welcome-detail-link:hover {
        color: var(--gold-light);
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="welcome-container">
    <img src="{{ asset('assets/images/common/logo-yoko.png') }}" alt="ミセチョク" class="welcome-logo">

    <h1 class="welcome-title">新しい出会いのカタチを、直感で。</h1>

    <div class="welcome-choice-box">
        <div class="welcome-choice-card">
            <i class="fas fa-glass-cheers" aria-hidden="true"></i>
            <h3>キャストの方</h3>
            <p>理想のお店と直接つながる</p>
            <a href="{{ route('login.demo') }}" class="btn-gold">ログイン / 登録</a>
            <a href="{{ route('lp.cast') }}" class="welcome-detail-link">もっと詳しく</a>
        </div>

        <div class="welcome-choice-card">
            <i class="fas fa-store" aria-hidden="true"></i>
            <h3>店舗の方</h3>
            <p>最高のキャストを直感で探す</p>
            <a href="{{ route('login.demo') }}" class="btn-gold">ログイン / 登録</a>
            <a href="{{ route('lp.shop') }}" class="welcome-detail-link">もっと詳しく</a>
        </div>
    </div>
</div>
@endsection
