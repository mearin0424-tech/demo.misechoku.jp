@extends('layouts.app-v2')

@section('title', 'スタッフ管理')
@section('body-class', 'page-shop-mypage-staff')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<style>
    .staff-shell { padding: 12px 16px 32px; max-width: 720px; margin: 0 auto; }

    /* 件数チップ（見出し右） */
    .staff-count-chip {
        display: inline-flex; align-items: center; gap: 5px;
        margin-left: auto;
        padding: 3px 10px; border-radius: 999px;
        font-size: 0.7rem; font-weight: 700;
        background: rgba(var(--accent-rgb, 214, 112, 162), 0.12);
        border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.4);
        color: var(--accent-text, #f0a6c4);
        white-space: nowrap;
    }

    .staff-flash {
        background: rgba(168, 85, 247, 0.12);
        border: 1px solid rgba(168, 85, 247, 0.4);
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
        background: var(--accent, #d670a2);
        color: var(--on-accent, #1a0814); font-weight: 700; font-size: 0.88rem;
        text-decoration: none;
        box-shadow: 0 6px 14px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.20), inset 0 -1px 0 rgba(0,0,0,.18);
        border: 0;
        transition: filter .15s ease, transform .12s ease;
    }
    .staff-btn-primary:hover { filter: brightness(1.06); }
    .staff-btn-primary:active { transform: scale(.97); box-shadow: 0 2px 5px rgba(0,0,0,.45), inset 0 2px 4px rgba(0,0,0,.2); }

    .staff-list { display: flex; flex-direction: column; gap: 12px; }
    .staff-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        border: 1px solid rgba(168, 85, 247, 0.25);
        border-radius: 16px;
        padding: 16px 18px;
        display: flex; align-items: center; gap: 14px;
        position: relative;
    }
    .staff-card.is-disabled { opacity: 0.6; }
    .staff-card__avatar {
        flex: 0 0 auto;
        width: 44px; height: 44px; border-radius: 50%;
        background: var(--accent, #d670a2);
        display: inline-flex; align-items: center; justify-content: center;
        color: var(--on-accent, #1a0814); font-weight: 800; font-size: 1rem;
    }
    /* スタッフ権限のアバターはフラットな薄色（オーナーとの視覚差） */
    .staff-card__avatar--staff {
        background: rgba(var(--accent-rgb, 214, 112, 162), 0.14);
        border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.4);
        color: var(--accent-text, #f0a6c4);
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
        background: var(--accent, #d670a2);
        color: var(--on-accent, #1a0814);
    }
    .staff-badge--staff {
        background: rgba(168, 85, 247, 0.18);
        color: #c4b5fd;
        border: 1px solid rgba(168, 85, 247, 0.4);
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

    /* 末尾の「スタッフを追加」破線カード（オーナーのみ表示） */
    .staff-add-card {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 14px;
        border-radius: 16px;
        border: 1px dashed rgba(var(--accent-rgb, 214, 112, 162), 0.45);
        color: var(--accent-text, #f0a6c4);
        font-size: 0.86rem; font-weight: 700;
        text-decoration: none;
        transition: background .15s ease, border-color .15s ease;
    }
    .staff-add-card:hover {
        background: rgba(var(--accent-rgb, 214, 112, 162), 0.08);
        border-color: rgba(var(--accent-rgb, 214, 112, 162), 0.75);
    }

    .staff-notice {
        margin-top: 18px;
        padding: 14px 16px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 12px;
        font-size: 0.78rem; line-height: 1.7;
        color: #a0a0a0;
    }
    .staff-notice__title {
        margin: 0 0 10px;
        font-size: 0.78rem; font-weight: 800; color: #c4b5fd;
        display: flex; align-items: center; gap: 6px;
    }
    .staff-notice__row {
        display: flex; align-items: flex-start; gap: 8px;
        margin-bottom: 8px;
    }
    .staff-notice__row:last-child { margin-bottom: 0; }
    .staff-notice__row .staff-badge { flex: 0 0 auto; margin-top: 1px; }
    .staff-notice__row span:last-child:not(.staff-badge) { flex: 1; }
</style>
@endpush

@section('content')
<div class="content-wrapper animate-fadeIn">
    <section class="mypage-area">
        <div class="staff-shell">
            {{-- タイトルはヘッダー中央、説明はオコジョガイド（character_guide_settings）に集約 --}}
            <p style="margin:14px 0 0;"><span class="staff-count-chip"><i class="fas fa-user"></i>{{ count($managers) }} 名</span></p>

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
                    <article class="staff-card {{ $isActive ? '' : 'is-disabled' }}">
                        <span class="staff-card__avatar {{ $isOwnerRow ? '' : 'staff-card__avatar--staff' }}">{{ $initial }}</span>
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

                {{-- 一覧末尾にも追加導線（オーナーのみ） --}}
                @if ($isOwner)
                    <a href="{{ route('shop.mypage.staff.create') }}" class="staff-add-card">
                        <i class="fas fa-plus"></i> スタッフを追加する
                    </a>
                @endif
            </div>

            <div class="staff-notice">
                <p class="staff-notice__title"><i class="fas fa-shield-halved"></i> 権限について</p>
                <div class="staff-notice__row">
                    <span class="staff-badge staff-badge--owner"><i class="fas fa-crown"></i> オーナー</span>
                    <span><strong>1 店舗につき 1 人のみ</strong>。スタッフ管理、店舗情報・求人票の編集、許可証提出、Premium 契約など全操作が可能</span>
                </div>
                <div class="staff-notice__row">
                    <span class="staff-badge staff-badge--staff">スタッフ</span>
                    <span>応募者対応・メッセージ・面談日程調整・採用可否など日常業務が可能。店舗情報や求人票の編集はオーナー専用</span>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
