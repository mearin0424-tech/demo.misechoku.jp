{{--
  請求書帳票の本文のみ（フォーマット共通）
  スタイルは呼び出し元（管理画面・PDF）で付与すること。
--}}
<div class="invoice-wrap">
    <div class="invoice-header">
        @if(!empty($invoice['logo_url']))
            <div class="invoice-logo" style="margin-bottom: 10pt;"><img src="{{ $invoice['logo_url'] }}" alt="" style="max-height: 48px; max-width: 200px;"></div>
        @endif
        <h1 class="invoice-title">請 求 書</h1>
        <div class="invoice-meta">
            請求書番号: {{ $invoice['invoice_number'] }}<br>
            発行日: {{ $invoice['issued_at']->format('Y年n月j日') }}<br>
            支払期限: {{ $invoice['due_date']->format('Y年n月j日') }}
        </div>
        <div class="invoice-issuer">
            {{ $invoice['issuer_name'] ?? 'ミセチョク運営事務局' }}<br>
            {{ $invoice['issuer_email'] ?? 'support@misechoku.jp' }}
        </div>
    </div>

    <div class="invoice-to">
        <p class="invoice-to-name">{{ $invoice['shop_name'] }} 御中</p>
        <div class="invoice-to-addr">
            {{ $invoice['shop_address'] ?: '住所未登録' }}<br>
            {{ $invoice['shop_email'] ?: 'メール未登録' }}
        </div>
    </div>

    <div class="invoice-total">
        <div class="invoice-total-label">ご請求金額（税込）</div>
        <div class="invoice-total-value">¥{{ number_format($invoice['invoice_amount']) }}</div>
    </div>

    <table class="invoice-table">
        <tbody>
            <tr>
                <th>対象キャスト</th>
                <td>{{ $invoice['cast_name'] }}</td>
            </tr>
            <tr>
                <th>キャスト振込予定額</th>
                <td>¥{{ number_format($invoice['cast_transfer_amount']) }}</td>
            </tr>
            <tr>
                <th>運営手数料</th>
                <td>¥{{ number_format($invoice['system_fee_amount']) }}</td>
            </tr>
            <tr>
                <th>請求金額合計</th>
                <td>¥{{ number_format($invoice['invoice_amount']) }}</td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    @if(!empty($invoice['footer_text']))
                        {!! nl2br(e($invoice['footer_text'])) !!}
                    @else
                        店舗からのご入金確認後、運営にてキャストへの振込を行います。<br>
                        上記支払期限までに、下記お振込先へお振り込みください。
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <div class="invoice-bank">
        <h2 class="invoice-bank-title">お振込先</h2>
        <div class="invoice-bank-detail">
            金融機関: {{ $invoice['admin_bank']['bank_name'] }}<br>
            支店名: {{ $invoice['admin_bank']['branch_name'] ?? '—' }}<br>
            口座種別: {{ $invoice['admin_bank']['account_type_label'] }}<br>
            口座番号: {{ $invoice['admin_bank']['account_number'] }}<br>
            口座名義: {{ $invoice['admin_bank']['account_name'] }}
        </div>
    </div>
</div>
