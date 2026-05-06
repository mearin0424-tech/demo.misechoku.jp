{{-- 入金パネル：請求タスクカード一式 --}}
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

<div class="management-pane" data-pane="payment" id="management-pane-payment" hidden>
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
