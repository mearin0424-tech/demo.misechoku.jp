@extends('layouts.admin')

@section('title', '請求書発行')

@section('content')
    <div class="admin-page">
        <h1 class="admin-title">請求書発行</h1>
        <p class="admin-description">
            キャストからの入金依頼（店舗承認待ち）と、店舗承認後の請求書発行をここでまとめて扱います。帳票テンプレートは体裁のみ（数値・請求データは含みません）。確定発行はプレビュー画面から行ってください。入金照合・キャスト振込は「入金確認・振込」画面です。
        </p>

        @include('admin.parts.operation-achievement', ['operationAchievementRoute' => 'admin.invoices.index'])

        @if(session('status'))
            <div class="admin-alert">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="admin-alert admin-alert-error">
                {{ session('error') }}
            </div>
        @endif

        {{-- 帳票テンプレートのダウンロード・設定 --}}
        <section class="invoice-issue-hero">
            <div>
                <h2 class="invoice-issue-hero-title">請求書 帳票テンプレート</h2>
                <p class="invoice-issue-hero-desc">
                    発行元名・ロゴ・備考文は「テンプレートを設定」で変更できます。ダウンロードする帳票はレイアウト確認用で、金額・宛先・日付の数字は含みません（お振込先もプレースホルダー表示です）。
                </p>
            </div>
            <div class="invoice-issue-hero-actions">
                <a href="{{ route('admin.invoices.template-settings') }}" class="btn-action">
                    <i class="fas fa-cog"></i> テンプレートを設定
                </a>
                <a href="{{ route('admin.deposits.invoice-template.download') }}" class="invoice-template-dl" target="_blank" rel="noopener">
                    <i class="fas fa-file-pdf"></i> 帳票テンプレートをダウンロード（PDF）
                </a>
            </div>
        </section>

        {{-- 運営口座（請求書記載の振込先） --}}
        <section class="admin-panel invoice-admin-bank">
            <div class="billing-detail-row" style="margin-bottom: 14px;">
                <h2 class="admin-panel-title" style="margin-bottom: 0;">運営口座</h2>
                <a href="{{ route('admin.bank.index') }}" class="btn-action manage">
                    <i class="fas fa-university"></i> 口座登録・編集
                </a>
            </div>
            <p class="admin-note" style="margin-bottom: 0;">請求書に印字される振込先です。発行前に内容を確認してください。</p>
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
                <p class="admin-note" style="margin-top: 12px;">請求書発行前に、上の「口座登録・編集」から振込先口座を登録してください。</p>
            @endif
        </section>

        {{-- 入金依頼・請求書発行待ち --}}
        <section class="admin-panel">
            <h2 class="admin-panel-title">入金依頼・請求書発行</h2>
            <p class="admin-note" style="margin-bottom: 14px;">
                キャストから入金依頼が届いているが店舗承認前の案件、および店舗承認済みで請求書未発行の案件です。店舗承認後は「発行する」でプレビューを開き、確定発行できます。入金照合・キャスト振込は「入金確認・振込」画面で行います。
            </p>

            @if(!empty($pending))
                <p class="invoice-pending-count">{{ count($pending) }} 件</p>
                <div class="invoice-pending-list">
                    @foreach($pending as $deposit)
                        @php
                            $isCastRequestOnly = (int) ($deposit['status_code'] ?? 0) === \App\Services\BillingManagementService::STATUS_CAST_REQUESTED;
                        @endphp
                        <div class="invoice-pending-card" id="invoice-pending-{{ $deposit['id'] }}">
                            <div class="invoice-pending-card-info">
                                <div class="invoice-pending-card-title">#{{ $deposit['id'] }} {{ $deposit['shop_name'] }} / {{ $deposit['cast_name'] }}</div>
                                <div class="invoice-pending-card-meta">
                                    @if($isCastRequestOnly)
                                        <span class="billing-status-chip" style="margin-right:8px;">キャスト入金依頼 · 店舗承認待ち</span>
                                    @else
                                        <span class="billing-status-chip" style="margin-right:8px;">請求書発行待ち</span>
                                    @endif
                                    {{ $deposit['next_action'] ?? '' }}
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
                    現在、入金依頼待ち・請求書発行待ちの案件はありません。
                </div>
            @endif

            <div style="margin-top: 18px;">
                <a href="{{ route('admin.deposits.index') }}" class="invoice-link-deposits">
                    <i class="fas fa-list"></i> 入金確認・振込一覧へ
                </a>
            </div>
        </section>

        {{-- 手動で請求書を発行（障害時等の回避策） --}}
        <section class="admin-panel">
            <h2 class="admin-panel-title">手動で請求書を発行（回避策）</h2>
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
        </section>
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
