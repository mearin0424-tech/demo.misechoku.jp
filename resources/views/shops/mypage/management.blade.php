@extends('layouts.app')

@section('title', '採用・入金管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/recruitment.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/management.css') }}?v=20260506">
<style>
    /* ===== 入金パネル用スタイル（既存 payment.blade.php から移植） ===== */
    .payment-mobile-shell { width: 100%; margin: 0 auto 28px; background: transparent; border: none; border-radius: 0; box-shadow: none; overflow: visible; color: #EAE0D5; }
    .payment-mobile-body { padding: 16px; }
    .payment-summary-card { border: 1px solid #382A25; border-radius: 12px; background: #1A1412; padding: 20px 14px; margin-bottom: 18px; text-align: center; }
    .payment-summary-card.is-alert { border-color: rgba(127, 29, 29, 0.55); background: rgba(38, 15, 17, 0.95); }
    .payment-summary-label { font-size: 11px; font-weight: 700; color: #8A7C74; margin-bottom: 4px; }
    .payment-summary-card.is-alert .payment-summary-label { color: #f87171; }
    .payment-summary-count { font-size: 42px; line-height: 1; font-weight: 700; color: #fff; letter-spacing: 0.02em; }
    .payment-summary-count span { font-size: 15px; color: #8A7C74; margin-left: 3px; font-weight: 500; }
    .payment-summary-total { margin-top: 5px; font-size: 14px; color: #C8A951; font-weight: 700; }
    .payment-summary-note { margin: 9px auto 0; width: 96%; font-size: 10px; line-height: 1.6; color: #9A8C84; }
    .payment-task-title { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #fff; margin: 0 0 10px; }
    .payment-task-title i { color: #C8A951; }
    .payment-segment { display: flex; gap: 4px; background: #1A1412; border: 1px solid #2A221E; border-radius: 10px; padding: 4px; margin-bottom: 12px; }
    .payment-segment button { flex: 1; border: none; border-radius: 8px; background: transparent; color: #8A7C74; font-size: 11px; padding: 8px 4px; font-weight: 600; cursor: pointer; position: relative; }
    .payment-segment button.is-active { background: #2A221E; color: #fff; }
    .payment-segment button.is-unpaid-active { color: #C8A951; }
    .payment-dot { width: 6px; height: 6px; border-radius: 999px; background: #ef4444; display: inline-block; margin-left: 5px; vertical-align: middle; }
    .payment-task-list { display: grid; gap: 10px; }
    .payment-task-empty { padding: 36px 10px; text-align: center; font-size: 11px; color: #5A4D45; border-radius: 12px; background: #1A1412; border: 1px solid #2A221E; }
    .payment-task-card { position: relative; border: 1px solid #382A25; border-radius: 12px; background: #1A1113; padding: 14px; overflow: hidden; }
    .payment-task-card.is-delayed { border-color: rgba(127, 29, 29, 0.6); background: rgba(69, 14, 18, 0.2); }
    .payment-task-delay-line { position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: rgba(239, 68, 68, 0.85); }
    .payment-task-top { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
    .payment-task-name { font-size: 11px; font-weight: 700; color: #fff; line-height: 1.45; }
    .payment-task-id { margin-top: 3px; color: #8A7C74; font-size: 9px; }
    .payment-task-status { font-size: 9px; font-weight: 700; padding: 2px 8px; border-radius: 999px; border: 1px solid; white-space: nowrap; }
    .payment-task-status.is-paid { background: rgba(6, 95, 70, 0.35); color: #6ee7b7; border-color: rgba(4, 120, 87, 0.5); }
    .payment-task-status.is-unpaid { background: rgba(200, 169, 81, 0.15); color: #C8A951; border-color: rgba(200, 169, 81, 0.4); }
    .payment-task-status.is-expected { background: rgba(63, 63, 70, 0.5); color: #a1a1aa; border-color: rgba(82, 82, 91, 0.55); }
    .payment-task-alert { margin-bottom: 10px; font-size: 10px; color: #fca5a5; display: flex; align-items: center; gap: 6px; background: rgba(127, 29, 29, 0.25); border: 1px solid rgba(127, 29, 29, 0.45); border-radius: 8px; padding: 7px 8px; }
    .payment-task-middle { display: flex; justify-content: space-between; align-items: end; border-bottom: 1px solid #2A221E; margin-bottom: 9px; padding-bottom: 9px; }
    .payment-task-middle span { color: #8A7C74; font-size: 10px; }
    .payment-task-middle strong { color: #fff; font-size: 18px; letter-spacing: 0.02em; }
    .payment-task-bottom { display: flex; justify-content: space-between; align-items: center; }
    .payment-task-date { color: #8A7C74; font-size: 9px; display: flex; align-items: center; gap: 5px; }
    .payment-invoice-link { color: #C8A951; text-decoration: none; font-size: 9px; display: inline-flex; align-items: center; gap: 5px; background: rgba(200, 169, 81, 0.1); border: 1px solid rgba(200, 169, 81, 0.3); border-radius: 6px; padding: 5px 8px; }
    .payment-invoice-wait { font-size: 9px; color: #5A4D45; }

    /* ===== 採用・入金管理 統合タブ ===== */
    .management-shell { padding: 0; }
    .management-head { padding: 14px 16px 0; }
    .management-page-title { margin: 0 0 12px; font-size: 1.05rem; font-weight: 700; color: #fff; letter-spacing: 0.04em; }
    .management-tabs {
        display: flex;
        gap: 4px;
        background: #1A1412;
        border: 1px solid #2A221E;
        border-radius: 12px;
        padding: 4px;
        margin: 0 16px 12px;
    }
    .management-tab {
        flex: 1;
        position: relative;
        border: none;
        border-radius: 9px;
        background: transparent;
        color: #8A7C74;
        font-size: 13px;
        font-weight: 600;
        padding: 10px 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .management-tab.is-active { background: #2A221E; color: #fff; }
    .management-tab-count { font-size: 11px; color: #8A7C74; font-weight: 500; }
    .management-tab.is-active .management-tab-count { color: #C8A951; }
    .management-tab-dot {
        position: absolute;
        top: 6px;
        right: 10px;
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #ef4444;
        box-shadow: 0 0 0 2px #1A1412;
    }
    .management-pane[hidden] { display: none; }
    .rsm-meta-status-overdue { color: #fca5a5 !important; }
    [data-field="interviewDate"].is-overdue { color: #fca5a5 !important; }
    @media (min-width: 375px) and (max-width: 430px) {
        .management-head { padding: 12px 12px 0; }
        .management-page-title { font-size: 1.15rem; margin-bottom: 10px; }
        .management-tabs { margin: 0 12px 10px; }
        .management-tab { font-size: 14px; padding: 11px 8px; }
        .management-tab-count { font-size: 12px; }

        .payment-mobile-body { padding: 12px; }
        .payment-summary-label { font-size: 12px; }
        .payment-summary-count { font-size: 46px; }
        .payment-summary-count span { font-size: 16px; }
        .payment-summary-total { font-size: 15px; }
        .payment-summary-note { font-size: 11px; line-height: 1.65; }

        .payment-task-title { font-size: 13px; }
        .payment-segment button { font-size: 12px; padding: 9px 5px; }
        .payment-task-empty { font-size: 12px; }
        .payment-task-name { font-size: 12px; line-height: 1.5; }
        .payment-task-id { font-size: 10px; }
        .payment-task-status { font-size: 10px; }
        .payment-task-alert { font-size: 11px; }
        .payment-task-middle span { font-size: 11px; }
        .payment-task-middle strong { font-size: 20px; }
        .payment-task-date { font-size: 10px; }
        .payment-invoice-link { font-size: 10px; }
        .payment-invoice-wait { font-size: 10px; }
    }
</style>
@endpush

@section('content')
<div class="management-shell animate-fadeIn">
    <div class="management-head">
        <h1 class="management-page-title">採用・入金管理</h1>
    </div>

    <nav class="management-tabs" role="tablist" aria-label="採用・入金切替">
        <button type="button"
                class="management-tab"
                data-pane="recruit"
                role="tab"
                aria-selected="false"
                aria-controls="management-pane-recruit"
                id="management-tab-recruit">
            採用
            <span class="management-tab-count">{{ $recruitInProgressCount ?? 0 }}</span>
            @if(!empty($recruitBadge))
                <span class="management-tab-dot" aria-label="未対応の採用タスクがあります"></span>
            @endif
        </button>
        <button type="button"
                class="management-tab"
                data-pane="payment"
                role="tab"
                aria-selected="false"
                aria-controls="management-pane-payment"
                id="management-tab-payment">
            入金
            <span class="management-tab-count">{{ $paymentPendingCount ?? 0 }}</span>
            @if(!empty($paymentBadge))
                <span class="management-tab-dot" aria-label="未対応の入金タスクがあります"></span>
            @endif
        </button>
    </nav>

    @include('shops.mypage.partials.management-recruit')
    @include('shops.mypage.partials.management-payment')
</div>
@endsection

@push('scripts')
<script>
(function() {
    var tabs = document.querySelectorAll('.management-tab');
    var panes = document.querySelectorAll('.management-pane');
    if (!tabs.length || !panes.length) return;

    function activate(name) {
        tabs.forEach(function(t) {
            var on = t.getAttribute('data-pane') === name;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panes.forEach(function(p) {
            p.hidden = p.getAttribute('data-pane') !== name;
        });
        try {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', name);
            window.history.replaceState({}, '', url);
        } catch (e) {}
    }

    tabs.forEach(function(t) {
        t.addEventListener('click', function() {
            activate(t.getAttribute('data-pane'));
        });
    });

    var initial = (new URLSearchParams(window.location.search).get('tab')) || @json($tab ?? 'recruit');
    if (initial !== 'recruit' && initial !== 'payment') initial = 'recruit';
    activate(initial);
})();
</script>
@endpush
