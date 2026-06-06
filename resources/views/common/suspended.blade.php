@extends('layouts.app-v2')

@section('title', 'アカウント停止中')

@section('content')
<div class="suspended-page">
    <div class="suspended-card">
        <div class="suspended-card__icon" aria-hidden="true">
            <i class="fas fa-ban"></i>
        </div>
        <h1 class="suspended-card__title">アカウントは停止中です</h1>
        <p class="suspended-card__lead">
            @if($displayName)
                <strong>{{ $displayName }}</strong> 様のアカウントは、現在運営側で停止しています。
            @else
                ご利用のアカウントは、現在運営側で停止しています。
            @endif
        </p>
        <p class="suspended-card__lead suspended-card__lead--small">
            停止中は、求人検索や応募・トーク機能などのサービスはご利用いただけません。<br>
            停止に関するご質問・解除のご希望は、下記の問合せフォームより運営までご連絡ください。
        </p>

        <div class="suspended-card__actions">
            <a href="{{ route('pages.support.form') }}" class="btn-suspended-primary">
                <i class="fas fa-paper-plane"></i>
                運営へ問合せを送る
            </a>
            <a href="{{ route('auth.logout') }}" class="btn-suspended-secondary">
                <i class="fas fa-right-from-bracket"></i>
                ログアウト
            </a>
        </div>

        @if($accountId)
            <p class="suspended-card__id">アカウントID：<code>{{ $accountId }}</code></p>
        @endif
    </div>
</div>

<style>
.suspended-page {
    min-height: calc(100vh - 80px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px 16px;
}
.suspended-card {
    width: 100%;
    max-width: 520px;
    padding: 32px 24px;
    border-radius: 22px;
    background: linear-gradient(180deg, rgba(74, 18, 42, 0.94), rgba(35, 8, 21, 0.95));
    border: 1px solid rgba(220, 181, 104, 0.45);
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.35);
    color: #f8e9c8;
    text-align: center;
}
.suspended-card__icon {
    width: 88px;
    height: 88px;
    margin: 0 auto 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(220, 38, 38, 0.18);
    border: 2px solid rgba(220, 38, 38, 0.7);
    color: #fca5a5;
    font-size: 2rem;
}
.suspended-card__title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #ffe2a3;
    margin: 0 0 12px;
    font-family: var(--font-sans);
    letter-spacing: 0.06em;
}
.suspended-card__lead {
    font-size: 0.96rem;
    line-height: 1.7;
    color: #f8e9c8;
    margin: 0 0 12px;
}
.suspended-card__lead--small {
    font-size: 0.85rem;
    color: rgba(248, 233, 200, 0.78);
}
.suspended-card__actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 24px 0 16px;
}
.btn-suspended-primary,
.btn-suspended-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 18px;
    border-radius: 999px;
    font-weight: 700;
    text-decoration: none;
    transition: transform 0.1s ease, background 0.15s ease;
}
.btn-suspended-primary {
    background: linear-gradient(135deg, #dcb568, #b8860b);
    color: #2a1406;
    border: 0;
    box-shadow: 0 4px 16px rgba(220, 181, 104, 0.3);
}
.btn-suspended-primary:hover { transform: translateY(-1px); color: #2a1406; }
.btn-suspended-secondary {
    background: transparent;
    border: 1px solid rgba(248, 233, 200, 0.35);
    color: #f8e9c8;
}
.btn-suspended-secondary:hover { background: rgba(248, 233, 200, 0.06); color: #f8e9c8; }
.suspended-card__id {
    margin: 12px 0 0;
    font-size: 0.78rem;
    color: rgba(248, 233, 200, 0.6);
}
.suspended-card__id code {
    background: rgba(255, 255, 255, 0.06);
    padding: 1px 8px;
    border-radius: 6px;
    color: #ffe2a3;
}
</style>
@endsection
