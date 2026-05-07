{{-- 請求書：帳票テンプレート（billing/invoice-body）を組み込み、管理画面用にツールバーを付与 --}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>請求書 {{ $invoice['invoice_number'] }}</title>
    <style>
        body { margin: 0; background: #f3f4f6; color: #111827; font-family: "Helvetica Neue", Arial, "Hiragino Sans", "Meiryo", sans-serif; }
        .invoice-shell { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .invoice-toolbar { display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 16px; }
        .invoice-btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 14px; border-radius: 10px; border: 1px solid #d1d5db; background: #111827; color: #fff; text-decoration: none; cursor: pointer; font-size: 14px; font-weight: 700; }
        .invoice-paper { background: #fff; border-radius: 20px; padding: 36px; box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12); }
        .invoice-wrap { max-width: none; padding: 0; }
        .invoice-header { border-bottom: 2px solid #111827; padding-bottom: 12pt; margin-bottom: 16pt; }
        .invoice-title { font-size: 22pt; font-weight: 800; letter-spacing: 0.12em; margin: 0 0 8pt; }
        .invoice-meta { font-size: 9pt; color: #4b5563; line-height: 1.6; }
        .invoice-issuer { text-align: right; font-size: 9pt; color: #4b5563; margin-top: 4pt; }
        .invoice-to { margin-bottom: 16pt; }
        .invoice-to-name { font-size: 14pt; font-weight: 800; margin: 0 0 4pt; }
        .invoice-to-addr { font-size: 9pt; color: #4b5563; line-height: 1.5; }
        .invoice-total { margin: 14pt 0; padding: 12pt 14pt; background: #fdf4f7; border-radius: 8pt; }
        .invoice-total-label { font-size: 9pt; color: #4A122A; font-weight: 700; margin-bottom: 4pt; }
        .invoice-total-value { font-size: 18pt; font-weight: 800; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-top: 8pt; font-size: 10pt; }
        .invoice-table th, .invoice-table td { border: 1px solid #e5e7eb; padding: 10pt 12pt; text-align: left; vertical-align: top; }
        .invoice-table th { width: 26%; background: #f9fafb; color: #4b5563; font-weight: 700; }
        .invoice-bank { margin-top: 18pt; padding: 14pt 16pt; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8pt; }
        .invoice-bank-title { margin: 0 0 8pt; font-size: 12pt; font-weight: 800; }
        .invoice-bank-detail { font-size: 10pt; line-height: 1.7; color: #374151; }
        @media print {
            body { background: #fff; }
            .invoice-shell { margin: 0; max-width: none; padding: 0; }
            .invoice-toolbar { display: none !important; }
            .invoice-paper { box-shadow: none; border-radius: 0; padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="invoice-shell">
        @if(!$printMode)
        <div class="invoice-toolbar">
            <a href="{{ route('admin.deposits.invoice.pdf', ['deposit' => $invoice['deposit_id']]) }}" class="invoice-btn" target="_blank" rel="noopener">PDFでダウンロード</a>
            <button type="button" class="invoice-btn" onclick="window.print()">印刷 / 別名でPDF保存</button>
            <a href="{{ route('admin.invoices.index') }}" class="invoice-btn" style="background:#4b5563;">請求書発行へ戻る</a>
            <a href="{{ route('admin.deposits.index') }}" class="invoice-btn" style="background:#374151;">入金確認・振込へ</a>
        </div>
        @endif
        <div class="invoice-paper">
            @include('billing.invoice-body')
        </div>
    </div>
</body>
</html>
