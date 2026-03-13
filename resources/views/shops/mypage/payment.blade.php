@extends('layouts.app')

@section('title', '採用・請求管理')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mypage.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/management.css') }}">
<style>
    .input-hint {
        margin-top: 6px;
        font-size: 0.72rem;
        line-height: 1.6;
        color: #9f8d8d;
    }
    .management-request-card {
        padding: 18px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.03);
        margin-bottom: 14px;
    }
    .management-request-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 10px;
    }
    .management-request-title {
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
    }
    .management-request-meta,
    .management-request-text {
        font-size: 0.84rem;
        line-height: 1.7;
        color: #d7c8c8;
    }
    .management-request-text {
        margin-top: 10px;
        white-space: pre-wrap;
    }
    .management-review-list {
        display: grid;
        gap: 8px;
        margin-top: 12px;
    }
    .management-review-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(255,255,255,0.04);
        color: #f6eeee;
        font-size: 0.84rem;
    }
    .management-checklist {
        display: grid;
        gap: 10px;
        margin: 14px 0 0;
    }
    .management-check-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.88rem;
        line-height: 1.6;
        color: #f2e8e8;
    }
</style>
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
            <span class="currency">¥</span>{{ number_format(($summary['unpaid_total'] ?? 0)) }}
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

    @if(session('status'))
        <section class="management-invoices">
            <p class="management-summary-note">{{ session('status') }}</p>
        </section>
    @endif

    @if(session('error'))
        <section class="management-invoices">
            <p class="management-summary-note" style="color:#fca5a5;">{{ session('error') }}</p>
        </section>
    @endif

    {{-- 採用ステータス（やり取り中のキャスト） --}}
    <section class="management-invoices">
        <h2 class="management-invoices-title">採用ステータス（やり取り中のキャスト）</h2>
        @php $cList = $candidates ?? []; @endphp
        @forelse($cList as $c)
            <div class="management-hire-card">
                <div class="management-hire-main">
                    <div class="management-hire-name-row">
                        <span class="management-hire-name">{{ $c['name'] }}</span>
                        @if(!empty($c['age']))
                            <span class="management-hire-age">({{ $c['age'] }}歳)</span>
                        @endif
                        @if(!empty($c['job_type']))
                            <span class="management-hire-pill">{{ $c['job_type'] }}</span>
                        @endif
                    </div>
                    <p class="management-hire-status">
                        <span class="management-hire-status-badge management-hire-status-{{ $c['status_tag'] ?? 'other' }}">
                            {{ $c['status_label'] }}
                        </span>
                        @if(!empty($c['next_step']))
                            <span class="management-hire-next">次のアクション：{{ $c['next_step'] }}</span>
                        @endif
                    </p>
                    @if(!empty($c['last_message']))
                        <p class="management-hire-message">「{{ $c['last_message'] }}」</p>
                    @endif
                </div>
                <div class="management-hire-meta">
                    @if(!empty($c['interview_at']))
                        <p class="management-hire-meta-row">
                            <span class="label"><i class="fas fa-calendar-alt"></i> 面談日</span>
                            <span class="value">{{ \Carbon\Carbon::parse($c['interview_at'])->format('m/d H:i') }}</span>
                        </p>
                    @endif
                    @if(!empty($c['deadline_at']))
                        <p class="management-hire-meta-row">
                            <span class="label"><i class="fas fa-clock"></i> 振込期限</span>
                            <span class="value">{{ \Carbon\Carbon::parse($c['deadline_at'])->format('m/d') }}</span>
                        </p>
                    @endif
                </div>
            </div>
        @empty
            <p class="management-invoices-empty">現在、やり取り中のキャストはありません。</p>
        @endforelse
    </section>

    <section class="management-invoices">
        <h2 class="management-invoices-title">採用・入金カレンダー</h2>
        @php
            $events = collect($calendarEvents ?? [])->sortBy('date')->groupBy('date');
        @endphp
        @forelse($events as $date => $rows)
            <div class="management-calendar-day">
                <div class="management-calendar-date">
                    <span class="date-main">{{ \Carbon\Carbon::parse($date)->format('m/d') }}</span>
                    <span class="date-sub">{{ \Carbon\Carbon::parse($date)->isoFormat('ddd') }}</span>
                </div>
                <div class="management-calendar-events">
                    @foreach($rows as $e)
                        @php
                            $type = $e['type'] ?? 'other';
                        @endphp
                        <div class="management-calendar-event management-calendar-event-{{ $type }}">
                            <span class="event-icon">
                                @if($type === 'interview')
                                    <i class="fas fa-user-clock"></i>
                                @elseif($type === 'deadline')
                                    <i class="fas fa-hourglass-half"></i>
                                @elseif($type === 'deposit')
                                    <i class="fas fa-coins"></i>
                                @else
                                    <i class="fas fa-dot-circle"></i>
                                @endif
                            </span>
                            <div class="event-body">
                                <span class="event-label">{{ $e['label'] }}</span>
                                <span class="event-meta">
                                    @if(!empty($e['time']))
                                        {{ $e['time'] }} / 
                                    @endif
                                    {{ $e['actor'] === 'admin' ? '運営' : ($e['actor'] === 'shop' ? '店舗' : 'キャスト') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="management-invoices-empty">直近の予定は登録されていません。</p>
        @endforelse
    </section>

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
                    @if(!empty($approvalTarget['requested_at']))
                        / 申請日時: {{ $approvalTarget['requested_at'] }}
                    @endif
                </div>
                <div class="management-request-text">{{ $approvalTarget['bonus_condition'] ?: '求人情報に登録した条件に従って確認してください。' }}</div>
                @if(!empty($approvalTarget['review_posted_at']) || !empty($approvalTarget['review_comment']))
                    <div class="management-request-text" style="margin-top:14px;">
                        レビュー投稿
                        @if(!empty($approvalTarget['review_posted_at']))
                            （{{ $approvalTarget['review_posted_at'] }}）
                        @endif
                        @if(!empty($approvalTarget['review_average']))
                            / 総合 {{ number_format((float) $approvalTarget['review_average'], 1) }}
                        @endif
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
                @endif
            </div>
        @endif
        @php $flow = $depositFlow ?? ['cast' => '未申請','shop' => '未稼働','admin' => '未稼働']; @endphp
        <table class="admin-table" style="margin-bottom:8px;">
            <thead>
                <tr>
                    <th>アクター</th>
                    <th>ステータス</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>キャスト</td><td>{{ $flow['cast'] }}</td></tr>
                <tr><td>店舗</td><td>{{ $flow['shop'] }}</td></tr>
                <tr><td>運営</td><td>{{ $flow['admin'] }}</td></tr>
            </tbody>
        </table>
        <div class="management-actions">
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
                    <button type="submit" class="btn-action manage">
                        ノルマ達成を確認し、店舗審査を完了する
                    </button>
                </form>
            @endif
        </div>
    </section>

    <section class="management-invoices">
        <h2 class="management-invoices-title">店舗からの入金報告</h2>
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
                    <input type="text" name="reference" class="bank-input" value="{{ old('reference', $paymentForm['reference'] ?? '') }}" placeholder="RCP-20260313-01">
                </div>
                <div class="management-actions">
                    <button type="submit" class="btn-action manage">
                        運営へ入金報告する
                    </button>
                </div>
            </form>
            <p class="management-summary-note">
                実際の銀行振込は店舗側で行い、この画面では運営に共有するための報告情報を登録します。
            </p>
        @else
            <p class="management-invoices-empty">現在、入金報告が必要な請求はありません。</p>
        @endif
    </section>

    <section class="management-invoices">
        <h2 class="management-invoices-title">請求履歴</h2>
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
                @if(!empty($inv['invoice_url']))
                    <a href="{{ $inv['invoice_url'] }}" class="management-invoice-pdf" target="_blank" rel="noopener">
                        <i class="fas fa-file-pdf"></i> 請求書
                    </a>
                @endif
            </div>
        </div>
        @empty
            <p class="management-invoices-empty">請求履歴はありません。</p>
        @endforelse
    </section>

    <section class="management-bank-section">
        <h2 class="management-invoices-title">店舗口座情報</h2>
        <p class="management-summary-note">
            店舗側で管理している口座情報を保存しておくと、運営との照合時に便利です。
        </p>
        <form id="shop-bank-form" class="management-bank-form" data-bank-autocomplete>
            @csrf
            <div class="bank-form-row">
                <label class="bank-label">金融機関名</label>
                <input type="text" name="bank_name" class="bank-input" value="{{ $shopBank['bank_name'] ?? '' }}" placeholder="〇〇銀行" autocomplete="off" list="shop-bank-suggestions" data-bank-name-input required>
                <input type="hidden" name="bank_code" value="{{ $shopBank['bank_code'] ?? '' }}" data-bank-code-input>
                <datalist id="shop-bank-suggestions" data-bank-list></datalist>
                <p class="input-hint">金融機関名を入力すると候補が表示されます。候補から選ぶと支店候補も検索しやすくなります。</p>
            </div>
            <div class="bank-form-row">
                <label class="bank-label">支店名</label>
                <input type="text" name="branch_name" class="bank-input" value="{{ $shopBank['branch_name'] ?? '' }}" placeholder="△△支店" autocomplete="off" list="shop-branch-suggestions" data-branch-name-input>
                <input type="hidden" name="branch_code" value="{{ $shopBank['branch_code'] ?? '' }}" data-branch-code-input>
                <datalist id="shop-branch-suggestions" data-branch-list></datalist>
                <p class="input-hint">支店名は、金融機関を候補から選択したあとに候補表示されます。</p>
            </div>
            <div class="bank-form-row">
                <label class="bank-label">口座種別</label>
                <select name="account_type" class="bank-input" required>
                    <option value="ordinary" {{ ($shopBank['account_type'] ?? 'ordinary') === 'ordinary' ? 'selected' : '' }}>普通</option>
                    <option value="checking" {{ ($shopBank['account_type'] ?? '') === 'checking' ? 'selected' : '' }}>当座</option>
                </select>
            </div>
            <div class="bank-form-row">
                <label class="bank-label">口座番号</label>
                <input type="text" name="account_number" class="bank-input" value="{{ $shopBank['account_number'] ?? '' }}" placeholder="1234567" inputmode="numeric" maxlength="7" pattern="[0-9]*" data-account-number-input required>
                <p class="input-hint">口座番号は7桁の数字で入力してください。</p>
            </div>
            <div class="bank-form-row">
                <label class="bank-label">口座名義（カナ）</label>
                <input type="text" name="account_name" class="bank-input" value="{{ $shopBank['account_name'] ?? '' }}" placeholder="ミセチョクタロウ" data-account-name-input required>
                <p class="input-hint">ひらがな・半角ｶﾅで入力しても、自動で口座名義向けの形式に整えます。</p>
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