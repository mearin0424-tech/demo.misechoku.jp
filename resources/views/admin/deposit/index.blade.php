@extends('layouts.admin')

@section('title', '入金・振込管理')

@push('admin-styles')
<style>
    .billing-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }
    .billing-summary-value {
        margin-top: 10px;
        font-size: 1.6rem;
        font-weight: 800;
    }
    .billing-detail-card {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr);
        gap: 18px;
    }
    .billing-meta-list,
    .billing-amount-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin: 12px 0 0;
    }
    .billing-meta-item {
        padding: 12px 14px;
        border-radius: 14px;
        background: #171a20;
        border: 1px solid rgba(255,255,255,0.08);
    }
    .billing-meta-label {
        font-size: 0.72rem;
        color: var(--admin-muted);
        margin-bottom: 6px;
    }
    .billing-meta-value {
        font-size: 0.92rem;
        font-weight: 700;
    }
    .billing-status-chip {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        background: rgba(96, 165, 250, 0.15);
        color: #dbeafe;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .billing-action-box {
        padding: 16px;
        border-radius: 16px;
        background: #171a20;
        border: 1px solid rgba(255,255,255,0.08);
    }
    .billing-action-box + .billing-action-box {
        margin-top: 12px;
    }
    .billing-action-title {
        margin: 0 0 10px;
        font-size: 0.92rem;
        font-weight: 700;
    }
    .billing-check-grid {
        display: grid;
        gap: 10px;
        margin-top: 12px;
    }
    .billing-check-item {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        font-size: 0.84rem;
        color: var(--admin-sub);
    }
    .billing-inline-form {
        display: grid;
        gap: 12px;
    }
    .billing-note {
        margin-top: 10px;
        color: var(--admin-muted);
        font-size: 0.78rem;
        line-height: 1.6;
    }
    .billing-review-box {
        margin-top: 14px;
        padding: 14px;
        border-radius: 14px;
        background: #171a20;
        border: 1px solid rgba(255,255,255,0.08);
    }
    .billing-review-title {
        margin: 0 0 8px;
        font-size: 0.84rem;
        font-weight: 700;
    }
    .billing-review-text {
        font-size: 0.82rem;
        line-height: 1.7;
        color: var(--admin-sub);
        white-space: pre-wrap;
    }
    .billing-review-grid {
        display: grid;
        gap: 8px;
        margin-top: 10px;
    }
    .billing-review-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 12px;
        border-radius: 12px;
        background: rgba(255,255,255,0.04);
        font-size: 0.8rem;
    }
    @media (max-width: 980px) {
        .billing-detail-card {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">入金・振込管理</h1>
        <p class="admin-description">
            店舗への請求書発行、店舗入金の照合、キャストへの振込記録までを一気通貫で管理します。<br>
            ステータスはシステム固定で持ち、運営の実務フローをこの画面でサポートします。
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

        @if($errors->any())
            <div class="admin-alert" style="background: rgba(248, 113, 113, 0.12); border-color: rgba(248, 113, 113, 0.3); color: #fee2e2;">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="billing-summary-grid">
            <div class="admin-card">
                <h2>請求書発行待ち</h2>
                <p>店舗承認済みで、まだ請求書を発行していない案件数です。</p>
                <div class="billing-summary-value">{{ number_format($summary['invoice_pending'] ?? 0) }}</div>
            </div>
            <div class="admin-card">
                <h2>店舗入金照合待ち</h2>
                <p>店舗が入金報告済みで、運営の着金確認が必要な件数です。</p>
                <div class="billing-summary-value">{{ number_format($summary['payment_confirmation_pending'] ?? 0) }}</div>
            </div>
            <div class="admin-card">
                <h2>キャスト振込待ち</h2>
                <p>店舗入金確認済みで、キャスト振込処理が必要な件数です。</p>
                <div class="billing-summary-value">{{ number_format($summary['cast_transfer_pending'] ?? 0) }}</div>
            </div>
            <div class="admin-card">
                <h2>請求総額</h2>
                <p>現在一覧にある請求レコードの請求合計金額です。</p>
                <div class="billing-summary-value">¥{{ number_format($summary['invoice_total'] ?? 0) }}</div>
            </div>
        </section>

        <section class="admin-panel">
            <h2 class="admin-panel-title">運営口座</h2>
            @if($adminBank)
                <div class="billing-meta-list">
                    <div class="billing-meta-item">
                        <div class="billing-meta-label">金融機関</div>
                        <div class="billing-meta-value">{{ $adminBank->bank_name }}</div>
                    </div>
                    <div class="billing-meta-item">
                        <div class="billing-meta-label">支店名</div>
                        <div class="billing-meta-value">{{ $adminBank->branch_name ?: '未設定' }}</div>
                    </div>
                    <div class="billing-meta-item">
                        <div class="billing-meta-label">口座種別 / 口座番号</div>
                        <div class="billing-meta-value">{{ in_array($adminBank->account_type, ['current', 'checking'], true) ? '当座' : '普通' }} / {{ $adminBank->account_number }}</div>
                    </div>
                    <div class="billing-meta-item">
                        <div class="billing-meta-label">口座名義</div>
                        <div class="billing-meta-value">{{ $adminBank->account_name }}</div>
                    </div>
                </div>
            @else
                <p class="admin-note">請求書発行前に、`運営口座情報設定` から請求書記載用の振込先口座を登録してください。</p>
            @endif
        </section>

        @forelse($deposits as $deposit)
            <section class="admin-panel" id="deposit-{{ $deposit['id'] }}">
                <div class="billing-detail-card">
                    <div>
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                            <div>
                                <h2 class="admin-panel-title" style="margin-bottom:6px;">
                                    #{{ $deposit['id'] }} {{ $deposit['shop_name'] }} / {{ $deposit['cast_name'] }}
                                </h2>
                                <span class="billing-status-chip">{{ $deposit['status_label'] }}</span>
                            </div>
                            <div class="management-actions" style="margin-top:0;">
                                @if(!empty($deposit['invoice_number']))
                                    <a href="{{ route('admin.deposits.invoice.show', $deposit['id']) }}" class="btn-action manage" target="_blank" rel="noopener">
                                        <i class="fas fa-file-invoice"></i> 請求書表示
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="billing-meta-list">
                            <div class="billing-meta-item">
                                <div class="billing-meta-label">次アクション</div>
                                <div class="billing-meta-value">{{ $deposit['next_action'] }}</div>
                            </div>
                            <div class="billing-meta-item">
                                <div class="billing-meta-label">請求番号</div>
                                <div class="billing-meta-value">{{ $deposit['invoice_number'] ?: '未発行' }}</div>
                            </div>
                            <div class="billing-meta-item">
                                <div class="billing-meta-label">請求発行日 / 支払期限</div>
                                <div class="billing-meta-value">
                                    {{ $deposit['invoice_issued_at'] ?: '未発行' }}
                                    @if($deposit['invoice_due_date'])
                                        / {{ $deposit['invoice_due_date'] }}
                                    @endif
                                </div>
                            </div>
                            <div class="billing-meta-item">
                                <div class="billing-meta-label">店舗入金報告</div>
                                <div class="billing-meta-value">{{ $deposit['shop_payment_reported_at'] ?: '未報告' }}</div>
                            </div>
                        </div>

                        <div class="billing-amount-list">
                            <div class="billing-meta-item">
                                <div class="billing-meta-label">キャスト振込額</div>
                                <div class="billing-meta-value">¥{{ number_format($deposit['cast_transfer_amount']) }}</div>
                            </div>
                            <div class="billing-meta-item">
                                <div class="billing-meta-label">運営手数料</div>
                                <div class="billing-meta-value">¥{{ number_format($deposit['system_fee_amount']) }}</div>
                            </div>
                            <div class="billing-meta-item">
                                <div class="billing-meta-label">店舗請求額</div>
                                <div class="billing-meta-value">¥{{ number_format($deposit['invoice_amount']) }}</div>
                            </div>
                            <div class="billing-meta-item">
                                <div class="billing-meta-label">店舗報告金額</div>
                                <div class="billing-meta-value">
                                    {{ $deposit['shop_payment_reported_amount'] ? '¥' . number_format($deposit['shop_payment_reported_amount']) : '未報告' }}
                                </div>
                            </div>
                        </div>

                        @if(!empty($deposit['bonus_condition']) || !empty($deposit['review_comment']) || !empty($deposit['review_details']))
                            <div class="billing-review-box">
                                @if(!empty($deposit['bonus_condition']))
                                    <h3 class="billing-review-title">求人に登録されたボーナス達成条件</h3>
                                    <div class="billing-review-text">{{ $deposit['bonus_condition'] }}</div>
                                @endif
                                @if(!empty($deposit['review_posted_at']) || !empty($deposit['review_comment']) || !empty($deposit['review_details']))
                                    <h3 class="billing-review-title" style="margin-top:12px;">
                                        キャストレビュー
                                        @if(!empty($deposit['review_posted_at']))
                                            ({{ $deposit['review_posted_at'] }})
                                        @endif
                                        @if(!empty($deposit['review_average']))
                                            / 総合 {{ number_format((float) $deposit['review_average'], 1) }}
                                        @endif
                                    </h3>
                                    @if(!empty($deposit['review_details']))
                                        <div class="billing-review-grid">
                                            @foreach($deposit['review_details'] as $detail)
                                                <div class="billing-review-item">
                                                    <span>{{ $detail['name'] }}</span>
                                                    <strong>{{ number_format((float) $detail['score'], 1) }} / 5</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(!empty($deposit['review_comment']))
                                        <div class="billing-review-text" style="margin-top:10px;">{{ $deposit['review_comment'] }}</div>
                                    @endif
                                @endif
                            </div>
                        @endif

                        <table class="admin-table" style="min-width: 0; margin-top:14px;">
                            <thead>
                                <tr>
                                    <th>アクター</th>
                                    <th>ステータス</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>キャスト</td><td>{{ $deposit['flow']['cast'] }}</td></tr>
                                <tr><td>店舗</td><td>{{ $deposit['flow']['shop'] }}</td></tr>
                                <tr><td>運営</td><td>{{ $deposit['flow']['admin'] }}</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <div class="billing-action-box">
                            <h3 class="billing-action-title">口座情報</h3>
                            <p class="admin-note">店舗口座: {{ $deposit['has_shop_bank'] ? '登録済み' : '未登録' }}</p>
                            <p class="admin-note">キャスト口座: {{ $deposit['has_cast_bank'] ? '登録済み' : '未登録' }}</p>
                            @if(!empty($deposit['cast_transfer_reference']))
                                <p class="admin-note">振込管理番号: {{ $deposit['cast_transfer_reference'] }}</p>
                            @endif
                        </div>

                        @if($deposit['status_code'] === 2)
                            <div class="billing-action-box">
                                <h3 class="billing-action-title">請求書発行</h3>
                                <p class="admin-note">店舗承認済みです。運営口座を載せた請求書を発行し、店舗へ送付します。</p>
                                <form method="POST" action="{{ route('admin.deposits.invoice.issue', $deposit['id']) }}" class="billing-inline-form">
                                    @csrf
                                    <div class="billing-check-grid" data-check-group>
                                        <label class="billing-check-item"><input type="checkbox" name="confirm_shop_approved" value="1" data-check-item> 店舗承認済みの申請内容を確認した</label>
                                        <label class="billing-check-item"><input type="checkbox" name="confirm_admin_bank_ready" value="1" data-check-item> 請求書に記載する運営口座情報を確認した</label>
                                    </div>
                                    <div class="management-actions">
                                        <button type="submit" class="btn-action manage" data-check-submit {{ $adminBank ? '' : 'disabled' }}>
                                            <i class="fas fa-paper-plane"></i> 請求書を発行して送付
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        @if($deposit['status_code'] === 4)
                            <div class="billing-action-box">
                                <h3 class="billing-action-title">店舗入金照合</h3>
                                <form method="POST" action="{{ route('admin.deposits.shop-payment.confirm', $deposit['id']) }}" class="billing-inline-form">
                                    @csrf
                                    <div class="admin-form-row" style="margin-bottom:0;">
                                        <label class="admin-label">確認済み金額</label>
                                        <input type="number" name="confirmed_amount" class="admin-input" value="{{ $deposit['invoice_amount'] }}" min="1" required>
                                    </div>
                                    <div class="billing-check-grid" data-check-group>
                                        <label class="billing-check-item"><input type="checkbox" name="confirm_amount_checked" value="1" data-check-item> 金額を照合した</label>
                                        <label class="billing-check-item"><input type="checkbox" name="confirm_report_checked" value="1" data-check-item> 店舗の入金報告日時を確認した</label>
                                        <label class="billing-check-item"><input type="checkbox" name="confirm_bank_checked" value="1" data-check-item> 銀行口座の着金を確認した</label>
                                    </div>
                                    <div class="management-actions">
                                        <button type="submit" class="btn-action manage" data-check-submit disabled>
                                            <i class="fas fa-check"></i> 店舗入金を確認済みにする
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        @if($deposit['status_code'] === 5)
                            <div class="billing-action-box">
                                <h3 class="billing-action-title">キャスト振込記録</h3>
                                <form method="POST" action="{{ route('admin.deposits.cast-transfer.execute', $deposit['id']) }}" class="billing-inline-form">
                                    @csrf
                                    <div class="admin-form-row" style="margin-bottom:0;">
                                        <label class="admin-label">振込日時</label>
                                        <input type="datetime-local" name="transferred_at" class="admin-input" value="{{ now()->format('Y-m-d\\TH:i') }}" required>
                                    </div>
                                    <div class="admin-form-row" style="margin-bottom:0;">
                                        <label class="admin-label">振込管理番号</label>
                                        <input type="text" name="reference" class="admin-input" placeholder="TRF-20260313-01">
                                    </div>
                                    <div class="admin-form-row" style="margin-bottom:0;">
                                        <label class="admin-label">備考</label>
                                        <textarea name="note" class="admin-input" rows="3" placeholder="銀行窓口で実行、受付票確認済み"></textarea>
                                    </div>
                                    <div class="billing-check-grid" data-check-group>
                                        <label class="billing-check-item"><input type="checkbox" name="confirm_transfer_amount" value="1" data-check-item> 金額を確認した</label>
                                        <label class="billing-check-item"><input type="checkbox" name="confirm_account_name" value="1" data-check-item> 口座名義を確認した</label>
                                        <label class="billing-check-item"><input type="checkbox" name="confirm_transfer_executed" value="1" data-check-item> 銀行で振込を実行した</label>
                                        <label class="billing-check-item"><input type="checkbox" name="confirm_receipt_checked" value="1" data-check-item> 受付票を確認した</label>
                                    </div>
                                    <div class="management-actions">
                                        <button type="submit" class="btn-action manage" data-check-submit disabled>
                                            <i class="fas fa-yen-sign"></i> キャスト振込を記録する
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        @if(!in_array($deposit['status_code'], [2, 4, 5], true))
                            <div class="billing-action-box">
                                <h3 class="billing-action-title">現在の状況</h3>
                                <p class="billing-note">この案件で今すぐ必要な運営アクションはありません。進行に応じてタスク管理へ自動表示されます。</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @empty
            <div class="admin-panel">
                <p class="admin-note">入金・振込データがまだありません。</p>
            </div>
        @endforelse
    </div>
@endsection

@push('admin-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-check-group]').forEach(function (group) {
        var submit = group.parentElement.querySelector('[data-check-submit]');
        var items = group.querySelectorAll('[data-check-item]');
        if (!submit || !items.length) return;

        var sync = function () {
            submit.disabled = Array.from(items).some(function (item) { return !item.checked; });
        };

        items.forEach(function (item) { item.addEventListener('change', sync); });
        sync();
    });
});
</script>
@endpush

