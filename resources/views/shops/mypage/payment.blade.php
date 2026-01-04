@extends('layouts.app')

@section('title', '請求・口座管理')

@push('styles')
<style>
    .payment-container { padding: 15px; }
    .summary-card {
        background: linear-gradient(135deg, var(--color-sub), #1a0505);
        border: 1px solid var(--color-gold);
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .summary-label { font-size: 0.75rem; color: var(--color-gold); letter-spacing: 0.1em; margin-bottom: 10px; }
    .summary-amount { font-size: 2.2rem; font-weight: bold; color: #fff; }
    .summary-amount span { font-size: 1rem; margin-right: 5px; }

    .invoice-section { margin-top: 30px; }
    .invoice-title { font-size: 1rem; color: #888; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
    
    .invoice-item {
        background: var(--color-card);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .invoice-info .title { font-weight: bold; font-size: 0.95rem; display: block; margin-bottom: 4px; }
    .invoice-info .date { font-size: 0.7rem; color: #666; }
    
    .invoice-meta { text-align: right; }
    .invoice-meta .amount { font-weight: bold; color: var(--color-gold); font-size: 1.1rem; display: block; }
    .invoice-meta .status-label { font-size: 0.6rem; padding: 2px 6px; border-radius: 4px; margin-top: 5px; display: inline-block; }
    .status-paid { background: #1e3a1e; color: #4caf50; }
    .status-pending { background: #3d3d1a; color: #d4af37; }

    .btn-pdf { font-size: 0.65rem; color: var(--color-accent); text-decoration: none; margin-top: 8px; display: block; }
</style>
@endpush

@section('content')
<div class="payment-container">
    {{-- 売上・請求サマリー --}}
    <div class="summary-card">
        <div class="summary-label">TOTAL UNPAID AMOUNT</div>
        <div class="summary-amount numeric-font"><span>¥</span>120,000</div>
        <p style="font-size:0.65rem; color:#888; margin-top:10px;">次回の決済予定日: 2025年2月5日</p>
    </div>

    {{-- 履歴リスト --}}
    <div class="invoice-section">
        <h3 class="invoice-title serif-font">Billing History</h3>
        
        @forelse($invoices as $inv)
        <div class="invoice-item">
            <div class="invoice-info">
                <span class="title">{{ $inv['title'] }}</span>
                <span class="date numeric-font">{{ $inv['date'] }}</span>
            </div>
            <div class="invoice-meta">
                <span class="amount numeric-font">¥{{ number_format($inv['amount']) }}</span>
                <span class="status-label {{ $inv['status'] === 'paid' ? 'status-paid' : 'status-pending' }}">
                    {{ $inv['status'] === 'paid' ? '支払い済み' : '未決済' }}
                </span>
                @if($inv['status'] === 'paid')
                    <a href="#" class="btn-pdf"><i class="fas fa-file-pdf"></i> 領収書ダウンロード</a>
                @endif
            </div>
        </div>
        @empty
            <p class="text-center py-10 opacity-40">請求履歴はありません。</p>
        @endforelse
    </div>

    {{-- 銀行口座設定リンク --}}
    <div style="margin-top:40px;">
        <a href="{{ route('common.settings.account') }}" class="btn-action manage">
            <i class="fas fa-university"></i> お支払い情報の変更
        </a>
    </div>
</div>
@endsection