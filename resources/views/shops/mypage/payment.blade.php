@extends('layouts.app')

@section('title', '採用・請求管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/management.css') }}">
@endpush

@section('content')
<div class="management-page contents animate-fadeIn">
    <header class="management-header">
        <a href="{{ route('shop.mypage.index') }}" class="management-back">
            <i class="fas fa-chevron-left"></i> マイページへ
        </a>
        <div class="management-title-block">
            <h1 class="management-title serif-font">MANAGEMENT</h1>
            <p class="management-sub">採用・請求・口座管理</p>
        </div>
    </header>

    <section class="management-summary">
        <p class="management-summary-label">未払い合計</p>
        <p class="management-summary-amount"><span class="currency">¥</span>120,000</p>
        <p class="management-summary-note">次回の決済予定日: 2025年2月5日</p>
    </section>

    <section class="management-invoices">
        <h2 class="management-invoices-title">Billing History</h2>
        @forelse($invoices as $inv)
        <div class="management-invoice-item">
            <div class="management-invoice-info">
                <span class="management-invoice-title">{{ $inv['title'] }}</span>
                <span class="management-invoice-date">{{ $inv['date'] }}</span>
            </div>
            <div class="management-invoice-meta">
                <span class="management-invoice-amount">¥{{ number_format($inv['amount']) }}</span>
                <span class="management-invoice-status {{ $inv['status'] === 'paid' ? 'status-paid' : 'status-pending' }}">
                    {{ $inv['status'] === 'paid' ? '支払い済み' : '未決済' }}
                </span>
                @if($inv['status'] === 'paid')
                    <a href="#" class="management-invoice-pdf"><i class="fas fa-file-pdf"></i> 領収書</a>
                @endif
            </div>
        </div>
        @empty
            <p class="management-invoices-empty">請求履歴はありません。</p>
        @endforelse
    </section>

    <div class="management-actions">
        <a href="{{ route('maintenance') }}" class="btn-action manage">
            <i class="fas fa-university"></i> お支払い情報の変更
        </a>
    </div>
</div>
@endsection