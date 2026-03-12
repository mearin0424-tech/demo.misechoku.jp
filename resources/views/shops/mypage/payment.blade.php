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
        <p class="management-summary-amount">
            <span class="currency">¥</span>{{ number_format($summary['unpaid_total'] ?? 0) }}
        </p>
        @if(!empty($summary['next_settlement']))
            <p class="management-summary-note">
                次回の決済予定日: {{ $summary['next_settlement'] }}
            </p>
        @else
            <p class="management-summary-note">
                次回決済予定日は未定です。
            </p>
        @endif
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
                @if(!empty($inv['receipt_url']))
                    <a href="{{ $inv['receipt_url'] }}" class="management-invoice-pdf" target="_blank" rel="noopener">
                        <i class="fas fa-file-pdf"></i> 領収書
                    </a>
                @endif
            </div>
        </div>
        @empty
            <p class="management-invoices-empty">請求履歴はありません。</p>
        @endforelse
    </section>

    <section class="management-bank-section">
        <h2 class="management-invoices-title">店舗の振込先口座</h2>
        <p class="management-summary-note">
            売上の振込先となる口座情報を登録してください。
        </p>
        <form id="shop-bank-form" class="management-bank-form">
            @csrf
            <div class="bank-form-row">
                <label class="bank-label">金融機関名</label>
                <input type="text" name="bank_name" class="bank-input" placeholder="〇〇銀行" required>
            </div>
            <div class="bank-form-row">
                <label class="bank-label">支店名</label>
                <input type="text" name="branch_name" class="bank-input" placeholder="△△支店">
            </div>
            <div class="bank-form-row">
                <label class="bank-label">口座種別</label>
                <select name="account_type" class="bank-input" required>
                    <option value="ordinary">普通</option>
                    <option value="checking">当座</option>
                </select>
            </div>
            <div class="bank-form-row">
                <label class="bank-label">口座番号</label>
                <input type="text" name="account_number" class="bank-input" placeholder="1234567" required>
            </div>
            <div class="bank-form-row">
                <label class="bank-label">口座名義（カナ）</label>
                <input type="text" name="account_name" class="bank-input" placeholder="ミセチョク タロウ" required>
            </div>
            <div class="management-actions">
                <button type="submit" class="btn-action manage">
                    <i class="fas fa-save"></i> 口座情報を保存
                </button>
            </div>
            <p id="shop-bank-message" class="management-summary-note" style="display:none;"></p>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('shop-bank-form');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(form);
        fetch('{{ route("shop.mypage.payment.bank.update") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        }).then(function (r) { return r.json(); })
        .then(function (res) {
            var msgEl = document.getElementById('shop-bank-message');
            if (!msgEl) return;
            msgEl.style.display = 'block';
            msgEl.textContent = res && res.message ? res.message : '保存しました。';
        }).catch(function () {
            alert('保存に失敗しました。時間をおいて再度お試しください。');
        });
    });
});
</script>
@endpush