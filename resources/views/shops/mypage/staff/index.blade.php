@extends('layouts.app-v2')

@section('title', 'スタッフ管理')
@section('body-class', 'page-shop-mypage-staff')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<style>
    .staff-shell { padding: 0 16px 32px; max-width: 720px; margin: 0 auto; }
    .staff-shell .mypage-page-title {
        font-family: var(--font-sans);
        font-size: 1.2rem; font-weight: 700;
        color: #e6dffc; margin: 16px 0 8px;
    }
    .staff-shell .staff-lead {
        color: #a0a0a0; font-size: 0.86rem; line-height: 1.85;
        margin: 0 0 18px;
    }
    .staff-shell .staff-lead strong { color: #e6dffc; }

    .staff-flash {
        background: rgba(var(--accent-rgb, 214, 112, 162), 0.12);
        border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.4);
        color: #e6dffc;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 14px;
        font-size: 0.88rem;
    }
    .staff-flash--error {
        background: rgba(248, 113, 113, 0.12);
        border-color: rgba(248, 113, 113, 0.5);
        color: #fecaca;
    }

    .staff-actions {
        display: flex; justify-content: flex-end;
        margin: 8px 0 14px;
    }
    .staff-btn-primary {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 18px; border-radius: 999px;
        background: linear-gradient(135deg, #eba8c8, #b0507f);
        color: #fff; font-weight: 700; font-size: 0.88rem;
        text-decoration: none;
        box-shadow:
            inset 0 2px 4px rgba(255,255,255,.3),
            inset 0 -3px 4px rgba(0,0,0,.35),
            0 8px 20px rgba(124,58,237,.45);
        border: 1px solid rgba(255,255,255,.12);
        transition: transform .15s ease;
    }
    .staff-btn-primary:hover { transform: translateY(-1px); }
    .staff-btn-primary:active { transform: translateY(1px); }

    .staff-list { display: flex; flex-direction: column; gap: 12px; }
    .staff-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.25);
        border-radius: 16px;
        padding: 16px 18px;
        display: flex; align-items: center; gap: 14px;
        position: relative;
    }
    .staff-card__avatar {
        flex: 0 0 auto;
        width: 44px; height: 44px; border-radius: 50%;
        background: linear-gradient(135deg, #eba8c8, #b0507f);
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 1rem;
        box-shadow: 0 0 12px rgba(var(--accent-rgb, 214, 112, 162), .45);
    }
    .staff-card__main { flex: 1 1 auto; min-width: 0; }
    .staff-card__name {
        font-size: 1rem; font-weight: 800; color: #f5f5f5;
        margin: 0 0 4px;
        word-break: break-all;
    }
    .staff-card__email {
        font-size: 0.8rem; color: #a0a0a0;
        margin: 0;
        word-break: break-all;
    }
    .staff-card__meta {
        display: flex; gap: 6px; align-items: center; flex-wrap: wrap;
        margin-top: 6px;
    }
    .staff-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 999px;
        font-size: 0.7rem; font-weight: 700;
        letter-spacing: 0.04em;
    }
    .staff-badge--owner {
        background: linear-gradient(135deg, #f6d36a, #b8860b);
        color: #2a1d0f;
        box-shadow: 0 0 8px rgba(212, 175, 55, .35);
    }
    .staff-badge--staff {
        background: rgba(var(--accent-rgb, 214, 112, 162), 0.18);
        color: #f2cadf;
        border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.4);
    }
    .staff-badge--me {
        background: rgba(255,255,255,.08);
        color: #f5f5f5;
        border: 1px solid rgba(255,255,255,.18);
    }
    .staff-badge--disabled {
        background: rgba(160, 160, 160, 0.14);
        color: #a0a0a0;
        border: 1px solid rgba(160, 160, 160, 0.3);
    }
    .staff-card__last {
        font-size: 0.72rem; color: #6b6b6b;
        margin-top: 4px;
    }
    .staff-card__delete {
        flex: 0 0 auto;
        background: transparent;
        border: 1px solid rgba(248, 113, 113, 0.4);
        color: #fca5a5;
        border-radius: 999px;
        padding: 7px 14px;
        font-size: 0.78rem; font-weight: 700;
        cursor: pointer;
        transition: background .15s ease;
    }
    .staff-card__delete:hover {
        background: rgba(248, 113, 113, 0.12);
        color: #fecaca;
    }
    .staff-card__delete[disabled] {
        opacity: .4; cursor: not-allowed;
    }

    .staff-notice {
        margin-top: 18px;
        padding: 14px 16px;
        background: rgba(255,255,255,0.03);
        border: 1px dashed rgba(var(--accent-rgb, 214, 112, 162), 0.3);
        border-radius: 12px;
        font-size: 0.78rem; line-height: 1.85;
        color: #a0a0a0;
    }
    .staff-notice strong { color: #f2cadf; }
</style>
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn">
    <section class="mypage-area">
        <div class="staff-shell">
            <h1 class="mypage-page-title">スタッフ管理</h1>
            <p class="staff-lead">
                1つのお店に対して<strong>複数のログインアカウント</strong>を持たせることができます。<br>
                オーナー権限のスタッフのみ、アカウントの追加・削除が可能です。
            </p>

            @if (session('message'))
                <div class="staff-flash">{{ session('message') }}</div>
            @endif
            @if ($errors->any())
                <div class="staff-flash staff-flash--error">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            @if ($isOwner)
                <div class="staff-actions">
                    <a href="{{ route('shop.mypage.staff.create') }}" class="staff-btn-primary">
                        <i class="fas fa-user-plus"></i> スタッフを追加
                    </a>
                </div>
            @endif

            <div class="staff-list">
                @foreach ($managers as $manager)
                    @php
                        $isSelf      = $manager->id === $currentManagerId;
                        $isOwnerRow  = (int) $manager->role === \App\Models\ShopManager::ROLE_OWNER;
                        $isActive    = (int) $manager->status === \App\Models\ShopManager::STATUS_ACTIVE;
                        $displayName = (string) ($manager->name ?? '—');
                        $initial     = $displayName !== '' ? mb_substr($displayName, 0, 1) : '?';
                        $lastLogin   = $manager->last_login_at
                            ? \Carbon\Carbon::parse($manager->last_login_at)->format('Y/m/d H:i')
                            : null;
                    @endphp
                    <article class="staff-card">
                        <span class="staff-card__avatar">{{ $initial }}</span>
                        <div class="staff-card__main">
                            <p class="staff-card__name">{{ $displayName }}</p>
                            <p class="staff-card__email">{{ $manager->email }}</p>
                            <div class="staff-card__meta">
                                @if ($isOwnerRow)
                                    <span class="staff-badge staff-badge--owner"><i class="fas fa-crown"></i> オーナー</span>
                                @else
                                    <span class="staff-badge staff-badge--staff">スタッフ</span>
                                @endif
                                @if ($isSelf)
                                    <span class="staff-badge staff-badge--me">自分</span>
                                @endif
                                @if (!$isActive)
                                    <span class="staff-badge staff-badge--disabled">停止中</span>
                                @endif
                            </div>
                            @if ($lastLogin)
                                <p class="staff-card__last">最終ログイン: {{ $lastLogin }}</p>
                            @else
                                <p class="staff-card__last">最終ログイン: —</p>
                            @endif
                        </div>
                        @if ($isOwner && !$isSelf)
                            <form
                                method="POST"
                                action="{{ route('shop.mypage.staff.destroy', ['id' => $manager->id]) }}"
                                onsubmit="return confirm('「{{ $displayName }}」を削除します。よろしいですか？');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="staff-card__delete">
                                    <i class="fas fa-trash-alt"></i> 削除
                                </button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="staff-notice">
                <strong>権限について</strong><br>
                ・<strong>オーナー</strong>：スタッフの追加・削除、店舗情報の変更、すべての操作が可能<br>
                ・<strong>スタッフ</strong>：応募者対応・メッセージ・求人ステータス変更など、日常業務のみ可能
            </div>
        </div>
    </section>
</div>
@endsection
