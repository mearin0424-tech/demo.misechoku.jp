@extends('layouts.app')

@section('title', 'Payment')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/management.css') }}">
<style>
    .payment-mobile-shell {
        width: 100%;
        margin: 0 auto 28px;
        background: transparent;
        border: none;
        border-radius: 0;
        box-shadow: none;
        overflow: visible;
        color: #EAE0D5;
    }
    .payment-mobile-body { padding: 16px; }
    .payment-summary-card {
        border: 1px solid #382A25; border-radius: 12px; background: #1A1412; padding: 20px 14px; margin-bottom: 18px;
        text-align: center;
    }
    .payment-summary-card.is-alert { border-color: rgba(127, 29, 29, 0.55); background: rgba(38, 15, 17, 0.95); }
    .payment-summary-label { font-size: 11px; font-weight: 700; color: #8A7C74; margin-bottom: 4px; }
    .payment-summary-card.is-alert .payment-summary-label { color: #f87171; }
    .payment-summary-count { font-size: 42px; line-height: 1; font-weight: 700; color: #fff; letter-spacing: 0.02em; }
    .payment-summary-count span { font-size: 15px; color: #8A7C74; margin-left: 3px; font-weight: 500; }
    .payment-summary-total { margin-top: 5px; font-size: 14px; color: #C8A951; font-weight: 700; }
    .payment-summary-note { margin: 9px auto 0; width: 96%; font-size: 10px; line-height: 1.6; color: #9A8C84; }
    .payment-task-title {
        display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #fff; margin: 0 0 10px;
    }
    .payment-task-title i { color: #C8A951; }
    .payment-segment {
        display: flex; gap: 4px; background: #1A1412; border: 1px solid #2A221E; border-radius: 10px; padding: 4px; margin-bottom: 12px;
    }
    .payment-segment button {
        flex: 1; border: none; border-radius: 8px; background: transparent; color: #8A7C74; font-size: 11px; padding: 8px 4px; font-weight: 600; cursor: pointer;
        position: relative;
    }
    .payment-segment button.is-active { background: #2A221E; color: #fff; }
    .payment-segment button.is-unpaid-active { color: #C8A951; }
    .payment-dot { width: 6px; height: 6px; border-radius: 999px; background: #ef4444; display: inline-block; margin-left: 5px; vertical-align: middle; }
    .payment-task-list { display: grid; gap: 10px; }
    .payment-task-empty {
        padding: 36px 10px; text-align: center; font-size: 11px; color: #5A4D45; border-radius: 12px; background: #1A1412; border: 1px solid #2A221E;
    }
    .payment-task-card {
        position: relative; border: 1px solid #382A25; border-radius: 12px; background: #1A1113; padding: 14px; overflow: hidden;
    }
    .payment-task-card.is-delayed { border-color: rgba(127, 29, 29, 0.6); background: rgba(69, 14, 18, 0.2); }
    .payment-task-delay-line { position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: rgba(239, 68, 68, 0.85); }
    .payment-task-top { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
    .payment-task-name { font-size: 11px; font-weight: 700; color: #fff; line-height: 1.45; }
    .payment-task-id { margin-top: 3px; color: #8A7C74; font-size: 9px; }
    .payment-task-status { font-size: 9px; font-weight: 700; padding: 2px 8px; border-radius: 999px; border: 1px solid; white-space: nowrap; }
    .payment-task-status.is-paid { background: rgba(6, 95, 70, 0.35); color: #6ee7b7; border-color: rgba(4, 120, 87, 0.5); }
    .payment-task-status.is-unpaid { background: rgba(200, 169, 81, 0.15); color: #C8A951; border-color: rgba(200, 169, 81, 0.4); }
    .payment-task-status.is-expected { background: rgba(63, 63, 70, 0.5); color: #a1a1aa; border-color: rgba(82, 82, 91, 0.55); }
    .payment-task-alert {
        margin-bottom: 10px; font-size: 10px; color: #fca5a5; display: flex; align-items: center; gap: 6px;
        background: rgba(127, 29, 29, 0.25); border: 1px solid rgba(127, 29, 29, 0.45); border-radius: 8px; padding: 7px 8px;
    }
    .payment-task-middle {
        display: flex; justify-content: space-between; align-items: end; border-bottom: 1px solid #2A221E; margin-bottom: 9px; padding-bottom: 9px;
    }
    .payment-task-middle span { color: #8A7C74; font-size: 10px; }
    .payment-task-middle strong { color: #fff; font-size: 18px; letter-spacing: 0.02em; }
    .payment-task-bottom { display: flex; justify-content: space-between; align-items: center; }
    .payment-task-date { color: #8A7C74; font-size: 9px; display: flex; align-items: center; gap: 5px; }
    .payment-invoice-link {
        color: #C8A951; text-decoration: none; font-size: 9px; display: inline-flex; align-items: center; gap: 5px;
        background: rgba(200, 169, 81, 0.1); border: 1px solid rgba(200, 169, 81, 0.3); border-radius: 6px; padding: 5px 8px;
    }
    .payment-invoice-wait { font-size: 9px; color: #5A4D45; }
</style>
@endpush

@section('content')
@php
    $invoiceRows = collect($invoices ?? [])->map(function ($inv, $idx) {
        $isPaid = ($inv['status'] ?? '') === 'paid';
        $status = $isPaid ? 'paid' : 'unpaid';
        $dateRaw = (string) ($inv['date'] ?? '');
        $isDelayed = false;
        if (!$isPaid && $dateRaw !== '') {
            $ts = strtotime($dateRaw);
            $isDelayed = $ts !== false ? $ts < strtotime(date('Y-m-d')) : false;
        }

        return [
            'id' => 'INV-' . str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT),
            'title' => (string) ($inv['title'] ?? '請求タスク'),
            'status' => $status,
            'status_label' => $isPaid ? '入金済み' : '入金待ち',
            'amount' => (int) ($inv['amount'] ?? 0),
            'date' => $dateRaw !== '' ? $dateRaw : '日付未設定',
            'invoice_url' => (string) (($inv['invoice_pdf_url'] ?? '') ?: ($inv['invoice_url'] ?? '')),
            'has_invoice' => !empty($inv['invoice_pdf_url']) || !empty($inv['invoice_url']),
            'is_delayed' => $isDelayed,
            'delay_message' => $isDelayed ? '支払期限を過ぎています。お早めにご対応ください。' : '',
        ];
    })->values();

    $expectedRows = collect();
    if (!empty($summary['next_settlement']) || (($summary['unpaid_total'] ?? 0) > 0 && $invoiceRows->isEmpty())) {
        $expectedRows->push([
            'id' => 'EST-0001',
            'title' => '次回請求見込',
            'status' => 'expected',
            'status_label' => '請求見込',
            'amount' => (int) ($summary['unpaid_total'] ?? 0),
            'date' => (string) ($summary['next_settlement'] ?? '未定'),
            'invoice_url' => '',
            'has_invoice' => false,
            'is_delayed' => false,
            'delay_message' => '',
        ]);
    }

    $taskRows = $expectedRows->concat($invoiceRows)->values();
    $unpaidCount = $taskRows->where('status', 'unpaid')->count();
    $unpaidTotal = (int) $taskRows->where('status', 'unpaid')->sum('amount');
@endphp
<div class="management-page contents animate-fadeIn">
    <section class="payment-mobile-shell">
        <div class="payment-mobile-body">
            <div class="payment-summary-card {{ $unpaidCount > 0 ? 'is-alert' : '' }}">
                <p class="payment-summary-label">入金待ちのタスク</p>
                <div class="payment-summary-count">{{ $unpaidCount }}<span>件</span></div>
                @if($unpaidCount > 0)
                    <p class="payment-summary-total">合計 ¥{{ number_format($unpaidTotal) }}</p>
                @endif
                <p class="payment-summary-note">
                    @if($unpaidCount > 0)
                        支払期限が設定されている請求書があります。期限内のお振込をお願いいたします。
                    @else
                        現在、入金が必要な請求書はありません。
                    @endif
                </p>
            </div>

            <h2 class="payment-task-title"><i class="fas fa-clock"></i>入金タスク・履歴</h2>
            <div class="payment-segment" id="payment-tab-group">
                <button type="button" data-tab="expected">請求見込</button>
                <button type="button" data-tab="unpaid" class="is-active is-unpaid-active">入金待ち @if($unpaidCount > 0)<span class="payment-dot"></span>@endif</button>
                <button type="button" data-tab="paid">入金済み</button>
            </div>

            <div class="payment-task-list" id="payment-task-list"></div>
            <div class="payment-task-empty" id="payment-task-empty" hidden>該当するデータがありません</div>
        </div>
    </section>
</div>

@push('scripts')
<script>
(function() {
    var tasks = @json($taskRows);
    var state = { tab: 'unpaid' };
    var tabGroup = document.getElementById('payment-tab-group');
    var listEl = document.getElementById('payment-task-list');
    var emptyEl = document.getElementById('payment-task-empty');
    if (!tabGroup || !listEl || !emptyEl) return;

    function render() {
        var rows = tasks.filter(function(task) { return task.status === state.tab; });
        listEl.innerHTML = '';
        emptyEl.hidden = rows.length !== 0;
        if (rows.length === 0) return;

        rows.forEach(function(task) {
            var card = document.createElement('article');
            card.className = 'payment-task-card' + (task.is_delayed ? ' is-delayed' : '');
            var statusClass = task.status === 'paid' ? 'is-paid' : (task.status === 'unpaid' ? 'is-unpaid' : 'is-expected');
            var leftLine = task.is_delayed ? '<div class="payment-task-delay-line"></div>' : '';
            var alert = task.is_delayed
                ? '<div class="payment-task-alert"><i class="fas fa-exclamation-circle"></i><span>' + (task.delay_message || '') + '</span></div>'
                : '';
            var invoiceAction = task.has_invoice
                ? '<a class="payment-invoice-link" href="' + task.invoice_url + '" target="_blank" rel="noopener"><i class="fas fa-file-download"></i>請求書を確認</a>'
                : '<span class="payment-invoice-wait">発行待ち</span>';

            card.innerHTML =
                leftLine +
                '<div class="payment-task-top">' +
                    '<div>' +
                        '<div class="payment-task-name">' + (task.title || '') + '</div>' +
                        '<div class="payment-task-id">' + (task.id || '') + '</div>' +
                    '</div>' +
                    '<span class="payment-task-status ' + statusClass + '">' + (task.status_label || '') + '</span>' +
                '</div>' +
                alert +
                '<div class="payment-task-middle"><span>ご請求額</span><strong>¥' + Number(task.amount || 0).toLocaleString() + '</strong></div>' +
                '<div class="payment-task-bottom">' +
                    '<div class="payment-task-date"><i class="fas fa-clock"></i>' + (task.date || '') + '</div>' +
                    invoiceAction +
                '</div>';
            listEl.appendChild(card);
        });
    }

    tabGroup.addEventListener('click', function(e) {
        var button = e.target.closest('button[data-tab]');
        if (!button) return;
        state.tab = button.getAttribute('data-tab');
        tabGroup.querySelectorAll('button').forEach(function(el) {
            el.classList.remove('is-active', 'is-unpaid-active');
        });
        button.classList.add('is-active');
        if (state.tab === 'unpaid') {
            button.classList.add('is-unpaid-active');
        }
        render();
    });

    render();
})();
</script>
@endpush
@endsection
