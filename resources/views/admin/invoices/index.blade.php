@extends('layouts.admin')

@section('title', '請求書発行')

@push('admin-styles')
<style>
    .invoice-issue-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(230, 208, 128, 0.08), rgba(230, 208, 128, 0.02));
        border: 1px solid rgba(230, 208, 128, 0.18);
        margin-bottom: 20px;
    }
    .invoice-issue-hero-title {
        margin: 0 0 6px;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--admin-text);
    }
    .invoice-issue-hero-desc {
        margin: 0;
        font-size: 0.84rem;
        color: var(--admin-sub);
        line-height: 1.5;
    }
    .invoice-template-dl {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 18px;
        border-radius: 14px;
        background: var(--admin-gold);
        color: #120405;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        border: none;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.12s;
    }
    .invoice-template-dl:hover {
        opacity: 0.95;
        transform: translateY(-1px);
        color: #120405;
    }
    .invoice-pending-count {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--admin-gold);
        margin: 0 0 4px;
    }
    .invoice-pending-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .invoice-pending-card {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 14px;
        background: var(--admin-card);
        border: 1px solid rgba(230, 208, 128, 0.1);
    }
    .invoice-pending-card-info {
        flex: 1;
        min-width: 0;
    }
    .invoice-pending-card-title {
        margin: 0 0 4px;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--admin-text);
    }
    .invoice-pending-card-meta {
        font-size: 0.8rem;
        color: var(--admin-muted);
    }
    .invoice-pending-card-amount {
        font-size: 1rem;
        font-weight: 700;
        color: var(--admin-gold);
    }
    .invoice-pending-card-actions {
        display: flex;
        gap: 10px;
        flex-shrink: 0;
    }
    .invoice-empty-note {
        padding: 24px;
        text-align: center;
        color: var(--admin-muted);
        font-size: 0.9rem;
        border-radius: 14px;
        background: rgba(255,255,255,0.02);
        border: 1px dashed rgba(230, 208, 128, 0.15);
    }
    .invoice-link-deposits {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 12px;
        background: rgba(230, 208, 128, 0.08);
        border: 1px solid rgba(230, 208, 128, 0.2);
        color: var(--admin-gold);
        text-decoration: none;
        font-weight: 700;
        font-size: 0.86rem;
    }
    .invoice-link-deposits:hover {
        background: rgba(230, 208, 128, 0.14);
        color: var(--admin-gold);
    }
</style>
@endpush

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">請求書発行</h1>
        <p class="admin-description">
            店舗承認済みの案件に対して請求書を発行します。帳票テンプレートをダウンロードして体裁を確認したうえで、入金・振込管理画面から発行してください。
        </p>

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="admin-alert" style="background: rgba(248, 113, 113, 0.12); border-color: rgba(248, 113, 113, 0.3); color: #fee2e2;">
                {{ session('error') }}
            </div>
        @endif

        {{-- 帳票テンプレートのダウンロード --}}
        <section class="admin-panel">
            <div class="invoice-issue-hero">
                <div>
                    <h2 class="invoice-issue-hero-title">請求書 帳票テンプレート</h2>
                    <p class="invoice-issue-hero-desc">
                        発行前にサンプルPDFでレイアウトを確認できます。運営口座を登録済みの場合はその情報が反映されます。
                    </p>
                </div>
                <a href="{{ route('admin.deposits.invoice-template.download') }}" class="invoice-template-dl" target="_blank" rel="noopener">
                    <i class="fas fa-file-pdf"></i> 帳票テンプレートをダウンロード（PDF）
                </a>
            </div>
        </section>

        {{-- 請求書発行待ち一覧 --}}
        <section class="admin-panel">
            <h2 class="admin-panel-title">請求書発行待ち</h2>
            <p class="admin-note" style="margin-bottom: 14px;">
                店舗承認済みで、まだ請求書を発行していない案件です。下記から「入金・振込管理」の該当案件に移動して発行できます。
            </p>

            @if(!empty($pending))
                <p class="invoice-pending-count">{{ count($pending) }} 件</p>
                <div class="invoice-pending-list">
                    @foreach($pending as $deposit)
                        <div class="invoice-pending-card">
                            <div class="invoice-pending-card-info">
                                <div class="invoice-pending-card-title">#{{ $deposit['id'] }} {{ $deposit['shop_name'] }} / {{ $deposit['cast_name'] }}</div>
                                <div class="invoice-pending-card-meta">{{ $deposit['next_action'] ?? '請求書を発行する' }}</div>
                            </div>
                            <div class="invoice-pending-card-amount">¥{{ number_format($deposit['invoice_amount'] ?? 0) }}</div>
                            <div class="invoice-pending-card-actions">
                                <a href="{{ route('admin.deposits.index') }}#deposit-{{ $deposit['id'] }}" class="btn-action manage">
                                    <i class="fas fa-file-invoice"></i> 発行する
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="invoice-empty-note">
                    現在、請求書発行待ちの案件はありません。
                </div>
            @endif

            <div style="margin-top: 18px;">
                <a href="{{ route('admin.deposits.index') }}" class="invoice-link-deposits">
                    <i class="fas fa-list"></i> 入金・振込管理一覧へ
                </a>
            </div>
        </section>
    </div>
@endsection
