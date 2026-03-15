@extends('layouts.app')

@section('title', '請求・入金管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/management.css') }}">
<style>
    .management-request-card {
        padding: 18px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.03);
        margin-bottom: 14px;
    }
    .management-request-head { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 10px; }
    .management-request-title { font-size: 1rem; font-weight: 700; color: #fff; }
    .management-request-meta, .management-request-text { font-size: 0.84rem; line-height: 1.7; color: #d7c8c8; }
    .management-request-text { margin-top: 10px; white-space: pre-wrap; }
    .management-review-list { display: grid; gap: 8px; margin-top: 12px; }
    .management-review-row { display: flex; justify-content: space-between; gap: 12px; padding: 10px 12px; border-radius: 12px; background: rgba(255,255,255,0.04); color: #f6eeee; font-size: 0.84rem; }
    .management-checklist { display: grid; gap: 10px; margin: 14px 0 0; }
    .management-check-row { display: flex; align-items: flex-start; gap: 10px; font-size: 0.88rem; line-height: 1.6; color: #f2e8e8; }
    .payment-flow-desc { font-size: 0.8rem; color: #b8a8a8; line-height: 1.6; margin: 12px 0 0; }
    .payment-flow-list { margin: 12px 0 0; padding-left: 1.4em; font-size: 0.88rem; line-height: 1.8; color: #e8e1d5; }
    .payment-flow-list li { margin-bottom: 6px; }
</style>
@endpush

@section('content')
<div class="management-page contents animate-fadeIn">
    <header class="management-header">
        <a href="{{ route('shop.mypage.index') }}" class="management-back">
            <i class="fas fa-chevron-left"></i> マイページへ
        </a>
        <div class="management-title-block">
            <h1 class="management-title serif-font">請求・入金管理</h1>
            <p class="management-sub">請求見込・請求書のダウンロード・運営口座への入金</p>
        </div>
    </header>

    {{-- 請求見込金額（採用したキャストに応じた請求額） --}}
    <section class="management-summary">
        <p class="management-summary-label">請求見込金額</p>
        <p class="management-summary-amount">
            <span class="currency">¥</span>{{ number_format(($summary['unpaid_total'] ?? 0)) }}
        </p>
        <p class="payment-flow-desc">採用キャストに応じて運営から請求されます。請求書発行後、記載の運営口座へお振り込みください。</p>
        @if(!empty($summary['next_settlement']))
            <p class="management-summary-note">次回請求予定: {{ $summary['next_settlement'] }}</p>
        @else
            <p class="management-summary-note">現在、未払いの請求はありません。</p>
        @endif
    </section>

    @if(session('status'))
        <section class="management-invoices">
            <p class="management-summary-note" style="color:#86efac;">{{ session('status') }}</p>
        </section>
    @endif
    @if(session('error'))
        <section class="management-invoices">
            <p class="management-summary-note" style="color:#fca5a5;">{{ session('error') }}</p>
        </section>
    @endif

    {{-- 請求書（PDF）・入金の流れ --}}
    <section class="management-invoices">
        <h2 class="management-invoices-title">請求書の受け取りと入金の流れ</h2>
        <ol class="payment-flow-list">
            <li>運営から請求書が発行されます（PDFでダウンロードできます）</li>
            <li>請求書に記載の<strong>運営口座</strong>へ、記載金額を振り込んでください</li>
            <li>振込後、この画面から「入金報告」を行ってください</li>
        </ol>
    </section>

    {{-- 現在の入金ステータス（承認待ち・フロー・入金報告） --}}
    <section class="management-invoices">
        <h2 class="management-invoices-title">現在の入金ステータス</h2>
        @if(!empty($approvalTarget))
            <div class="management-request-card">
                <div class="management-request-head">
                    <span class="management-request-title">{{ $approvalTarget['cast_name'] ?? 'キャスト' }} さんの申請内容</span>
                    <span class="management-invoice-status status-pending">承認待ち</span>
                </div>
                <div class="management-request-meta">
                    申請金額: ¥{{ number_format((int) ($approvalTarget['bonus_amount'] ?? 0)) }}
                    @if(!empty($approvalTarget['requested_at'])) / 申請日時: {{ $approvalTarget['requested_at'] }} @endif
                </div>
                <div class="management-request-text">{{ $approvalTarget['bonus_condition'] ?: '求人情報に登録した条件に従って確認してください。' }}</div>
                @if(!empty($approvalTarget['review_posted_at']) || !empty($approvalTarget['review_comment']))
                    <div class="management-request-text" style="margin-top:14px;">
                        レビュー投稿
                        @if(!empty($approvalTarget['review_posted_at'])) （{{ $approvalTarget['review_posted_at'] }}） @endif
                        @if(!empty($approvalTarget['review_average'])) / 総合 {{ number_format((float) $approvalTarget['review_average'], 1) }} @endif
                    </div>
                    @if(!empty($approvalTarget['review_details']))
                        <div class="management-review-list">
                            @foreach($approvalTarget['review_details'] as $detail)
                                <div class="management-review-row">
                                    <span>{{ $detail['name'] }}</span>
                                    <strong>{{ number_format((float) $detail['score'], 1) }} / 5</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($approvalTarget['review_comment']))
                        <div class="management-request-text">{{ $approvalTarget['review_comment'] }}</div>
                    @endif
                    @if(!empty($approvalTarget['review_id']))
                        <div class="management-actions" style="margin-top:12px;">
                            <a href="{{ route('shop.mypage.review.index') }}#review-{{ $approvalTarget['review_id'] }}" class="btn-action manage">レビュー一覧で確認する</a>
                        </div>
                    @endif
                @endif
            </div>
        @endif
        @php $flow = $depositFlow ?? ['cast' => '—', 'shop' => '—', 'admin' => '—']; @endphp
        <table class="admin-table" style="margin-bottom:8px;">
            <thead>
                <tr><th>キャスト</th><th>店舗</th><th>運営</th></tr>
            </thead>
            <tbody>
                <tr><td>{{ $flow['cast'] }}</td><td>{{ $flow['shop'] }}</td><td>{{ $flow['admin'] }}</td></tr>
            </tbody>
        </table>
        @if(($currentDeposit['status_code'] ?? null) === 1)
            <form method="POST" action="{{ route('shop.mypage.deposit.approve') }}">
                @csrf
                <div class="management-checklist">
                    <label class="management-check-row">
                        <input type="checkbox" name="confirm_bonus_condition" value="1">
                        <span>求人情報に登録したボーナス達成条件と、今回の申請内容が一致していることを確認しました。</span>
                    </label>
                    <label class="management-check-row">
                        <input type="checkbox" name="confirm_review_checked" value="1">
                        <span>キャストのレビュー内容を確認し、店舗審査を進めて問題ないことを確認しました。</span>
                    </label>
                </div>
                <button type="submit" class="btn-action manage">ノルマ達成を確認し、店舗審査を完了する</button>
            </form>
        @endif
    </section>

    {{-- 店舗からの入金報告（運営口座へ振込後） --}}
    <section class="management-invoices">
        <h2 class="management-invoices-title">入金報告</h2>
        <p class="payment-flow-desc">運営口座へ振り込みが完了したら、報告金額・振込日時を入力して送信してください。</p>
        @if($canReportPayment ?? false)
            <form method="POST" action="{{ route('shop.mypage.deposit.pay') }}" class="management-bank-form">
                @csrf
                <div class="bank-form-row">
                    <label class="bank-label">報告金額</label>
                    <input type="number" name="reported_amount" class="bank-input" value="{{ old('reported_amount', $paymentForm['reported_amount'] ?? '') }}" min="1" required>
                </div>
                <div class="bank-form-row">
                    <label class="bank-label">振込日時</label>
                    <input type="datetime-local" name="reported_at" class="bank-input" value="{{ old('reported_at', $paymentForm['reported_at'] ?? now()->format('Y-m-d\\TH:i')) }}" required>
                </div>
                <div class="bank-form-row">
                    <label class="bank-label">振込管理番号 / メモ</label>
                    <input type="text" name="reference" class="bank-input" value="{{ old('reference', $paymentForm['reference'] ?? '') }}" placeholder="例: RCP-20260313-01">
                </div>
                <div class="management-actions">
                    <button type="submit" class="btn-action manage">運営へ入金報告する</button>
                </div>
            </form>
        @else
            <p class="management-invoices-empty">現在、入金報告が必要な請求はありません。</p>
        @endif
    </section>

    {{-- 請求履歴（請求書PDFダウンロード） --}}
    <section class="management-invoices">
        <h2 class="management-invoices-title">請求履歴・請求書（PDF）</h2>
        <p class="payment-flow-desc">過去に発行された請求書はこちらからダウンロードできます。</p>
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
                    @if(!empty($inv['invoice_pdf_url']))
                        <a href="{{ $inv['invoice_pdf_url'] }}" class="management-invoice-pdf" target="_blank" rel="noopener">
                            <i class="fas fa-file-pdf"></i> 請求書をダウンロード（PDF）
                        </a>
                    @elseif(!empty($inv['invoice_url']))
                        <a href="{{ $inv['invoice_url'] }}" class="management-invoice-pdf" target="_blank" rel="noopener">
                            <i class="fas fa-file-pdf"></i> 請求書を表示（印刷でPDF保存可）
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <p class="management-invoices-empty">請求履歴はありません。</p>
        @endforelse
    </section>
</div>
@endsection
