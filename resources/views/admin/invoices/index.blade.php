@extends('layouts.admin')

@section('title', '請求書発行')

@section('content')
    @php
        // ペンディング案件の集計
        $castWaitingCount = 0;
        $issueWaitingCount = 0;
        if (!empty($pending)) {
            foreach ($pending as $d) {
                if ((int) ($d['status_code'] ?? 0) === \App\Services\BillingManagementService::STATUS_CAST_REQUESTED) {
                    $castWaitingCount++;
                } else {
                    $issueWaitingCount++;
                }
            }
        }
        $totalAchievement = (int) (($adminOperationAchievements ?? [])['admin.invoices.index'] ?? 0);
    @endphp

    <div class="admin-page">
        @include('admin.parts.operation-nav', ['active' => 'invoices'])

        <div class="u-flex-between">
            <h1 class="admin-title">請求書発行</h1>
            @include('admin.parts.operation-achievement', ['operationAchievementRoute' => 'admin.invoices.index'])
        </div>
        <p class="admin-description">
            キャストからの入金依頼（店舗承認待ち）と店舗承認後の請求書発行をまとめて扱います。入金照合・キャスト振込は「入金確認・振込」画面です。
        </p>

        @if(session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="admin-alert admin-alert-error">{{ session('error') }}</div>
        @endif

        {{-- KPI --}}
        <section class="dashboard-kpi-grid">
            <article class="dashboard-kpi-card">
                <div class="dashboard-kpi-head">
                    <div class="dashboard-kpi-title">店舗承認待ち</div>
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="dashboard-kpi-main">
                    <span class="dashboard-kpi-value">{{ number_format($castWaitingCount) }}</span>
                    <span class="dashboard-kpi-unit">件</span>
                </div>
            </article>
            <article class="dashboard-kpi-card">
                <div class="dashboard-kpi-head">
                    <div class="dashboard-kpi-title">発行待ち</div>
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="dashboard-kpi-main">
                    <span class="dashboard-kpi-value">{{ number_format($issueWaitingCount) }}</span>
                    <span class="dashboard-kpi-unit">件</span>
                </div>
            </article>
            <article class="dashboard-kpi-card">
                <div class="dashboard-kpi-head">
                    <div class="dashboard-kpi-title">累計発行</div>
                    <i class="fas fa-circle-check"></i>
                </div>
                <div class="dashboard-kpi-main">
                    <span class="dashboard-kpi-value">{{ number_format($totalAchievement) }}</span>
                    <span class="dashboard-kpi-unit">件</span>
                </div>
            </article>
        </section>

        {{-- 入金依頼・請求書発行待ち --}}
        <section class="admin-panel">
            <h2 class="admin-panel-title">入金依頼・請求書発行</h2>
            <p class="admin-note u-mb-12">
                キャストから入金依頼が届いて店舗承認前の案件、および店舗承認済みで請求書未発行の案件です。
            </p>

            @if(!empty($pending))
                <div class="invoice-pending-list">
                    @foreach($pending as $deposit)
                        @php
                            $statusCode = (int) ($deposit['status_code'] ?? 0);
                            $isCastRequestOnly = $statusCode === \App\Services\BillingManagementService::STATUS_CAST_REQUESTED;
                            $createdAt = !empty($deposit['created_at']) ? \Carbon\Carbon::parse($deposit['created_at']) : null;
                            $daysElapsed = $createdAt ? (int) $createdAt->diffInDays(now()) : null;
                            $dueClass = $daysElapsed === null ? '' : ($daysElapsed >= 7 ? 'is-overdue' : ($daysElapsed >= 3 ? 'is-soon' : ''));
                        @endphp
                        <div class="invoice-pending-card" id="invoice-pending-{{ $deposit['id'] }}">
                            <div class="invoice-pending-card-info">
                                <div class="invoice-pending-card-title">
                                    #{{ $deposit['id'] }} {{ $deposit['shop_name'] }} / {{ $deposit['cast_name'] }}
                                </div>
                                <div class="invoice-pending-card-meta">
                                    @if($isCastRequestOnly)
                                        <span class="admin-status-badge is-warning">店舗承認待ち</span>
                                    @else
                                        <span class="admin-status-badge is-info">請求書発行待ち</span>
                                    @endif
                                    @if($daysElapsed !== null)
                                        <span class="invoice-pending-card-due {{ $dueClass }}">
                                            <i class="fas fa-clock"></i>
                                            申請から {{ $daysElapsed }} 日経過
                                        </span>
                                    @endif
                                    @if(!empty($deposit['next_action']))
                                        <div class="admin-note u-mt-4">{{ $deposit['next_action'] }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="invoice-pending-card-amount">¥{{ number_format($deposit['invoice_amount'] ?? 0) }}</div>
                            <div class="invoice-pending-card-actions">
                                @if($isCastRequestOnly)
                                    <span class="admin-note">店舗承認後に発行できます</span>
                                @elseif($adminBank)
                                    <a href="{{ route('admin.deposits.invoice.show', $deposit['id']) }}" class="btn-action manage">
                                        <i class="fas fa-file-invoice"></i> 発行する
                                    </a>
                                @else
                                    <a href="{{ route('admin.bank.index') }}" class="btn-action manage" title="請求書プレビューには運営口座の登録が必要です">
                                        <i class="fas fa-university"></i> 口座登録へ
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="invoice-empty-note">
                    <i class="fas fa-circle-check" aria-hidden="true"></i>
                    現在、入金依頼待ち・請求書発行待ちの案件はありません。
                </div>
            @endif

            <div class="u-mt-16">
                <a href="{{ route('admin.deposits.index') }}" class="invoice-link-deposits">
                    <i class="fas fa-list"></i> 入金確認・振込一覧へ
                </a>
            </div>
        </section>

        {{-- 運営口座 --}}
        <section class="admin-panel invoice-admin-bank">
            <div class="billing-detail-row u-mb-12">
                <h2 class="admin-panel-title u-mb-0">運営口座</h2>
                <a href="{{ route('admin.bank.index') }}" class="btn-action btn-action-secondary">
                    <i class="fas fa-university"></i> 口座登録・編集
                </a>
            </div>
            <p class="admin-note">請求書に印字される振込先です。発行前に内容を確認してください。</p>
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
                <div class="admin-alert admin-alert-warning u-mt-12">
                    <strong>運営口座が未登録です。</strong> 請求書発行前に上の「口座登録・編集」から登録してください。
                </div>
            @endif
        </section>

        {{-- 帳票テンプレート（折りたたみ） --}}
        <details class="admin-accordion">
            <summary class="admin-accordion-summary">
                <div class="admin-accordion-title">
                    <span class="admin-accordion-title-main"><i class="fas fa-file-pdf"></i> 帳票テンプレート設定・プレビュー</span>
                    <span class="admin-accordion-title-sub">発行元名・ロゴ・備考文の設定とテンプレート PDF のダウンロード</span>
                </div>
            </summary>
            <div class="admin-accordion-body">
                <p class="admin-note u-mb-12">
                    発行元名・ロゴ・備考文は「テンプレートを設定」で変更できます。ダウンロードする帳票はレイアウト確認用で、金額・宛先・日付の数字は含みません。
                </p>
                <div class="invoice-issue-hero-actions">
                    <a href="{{ route('admin.invoices.template-settings') }}" class="btn-action">
                        <i class="fas fa-cog"></i> テンプレートを設定
                    </a>
                    <a href="{{ route('admin.deposits.invoice-template.download') }}" class="invoice-template-dl" target="_blank" rel="noopener">
                        <i class="fas fa-file-pdf"></i> テンプレートを PDF プレビュー
                    </a>
                </div>
            </div>
        </details>

        {{-- 手動発行（折りたたみ・警告強化） --}}
        <details class="admin-accordion">
            <summary class="admin-accordion-summary">
                <div class="admin-accordion-title">
                    <span class="admin-accordion-title-main"><i class="fas fa-triangle-exclamation"></i> 手動で請求書を発行（回避策）</span>
                    <span class="admin-accordion-title-sub">障害時のみ使用。通常は上の一覧から発行してください</span>
                </div>
            </summary>
            <div class="admin-accordion-body">
                <div class="invoice-manual-warning">
                    <strong>注意事項（回避策としての利用に限定してください）</strong><br>
                    通常は上の「入金依頼・請求書発行」一覧からプレビュー経由で発行してください。<br>
                    システム不具合や運用上の理由で、ステータスが「入金依頼確認済み」でない場合に限り、ここから宛先（入金申請）を指定して手動で請求書を発行できます。<br>
                    誤用するとフローと実態がずれるため、必要な場合のみご利用ください。
                </div>
                @if(!empty($manualTargets))
                    @include('admin.invoices.partials.manual-issue-form', ['manualTargets' => $manualTargets])
                @else
                    <p class="invoice-empty-note">手動発行の対象（請求書未発行の入金申請）はありません。</p>
                @endif
            </div>
        </details>
    </div>
@endsection

@push('admin-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-manual-invoice');
    if (!form) return;
    var submit = form.querySelector('[data-check-submit]');
    var items = form.querySelectorAll('[data-check-item]');
    var select = document.getElementById('manual-deposit-select');
    var bonus = document.getElementById('manual_bonus');
    var system = document.getElementById('manual_system');
    var invoiceTotal = document.getElementById('manual_invoice_total');

    function syncSubmit() {
        if (!submit || !items.length) return;
        submit.disabled = Array.from(items).some(function (el) { return !el.checked; });
    }
    items.forEach(function (el) { el.addEventListener('change', syncSubmit); });
    syncSubmit();

    function syncInvoiceTotalFromParts() {
        if (!bonus || !system || !invoiceTotal) return;
        var b = parseInt(bonus.value, 10) || 0;
        var s = parseInt(system.value, 10) || 0;
        invoiceTotal.value = String(b + s);
    }
    if (bonus) bonus.addEventListener('input', syncInvoiceTotalFromParts);
    if (system) system.addEventListener('input', syncInvoiceTotalFromParts);

    function fillFromDepositOption(opt) {
        if (!opt || !opt.value) return;
        var sn = document.getElementById('manual_shop_name');
        var sa = document.getElementById('manual_shop_address');
        var se = document.getElementById('manual_shop_email');
        var cn = document.getElementById('manual_cast_name');
        var ct = document.getElementById('manual_cast_transfer');
        if (sn) sn.value = opt.dataset.shopName || '';
        if (sa) sa.value = opt.dataset.shopAddress || '';
        if (se) se.value = opt.dataset.shopEmail || '';
        if (cn) cn.value = opt.dataset.castName || '';
        if (bonus) bonus.value = opt.dataset.bonus || '0';
        if (system) system.value = opt.dataset.system || '0';
        if (invoiceTotal) invoiceTotal.value = opt.dataset.invoice || '0';
        if (ct) ct.value = opt.dataset.castTransfer || '0';
    }

    if (select) {
        select.addEventListener('change', function () {
            fillFromDepositOption(select.options[select.selectedIndex]);
        });
    }
});
</script>
@endpush
