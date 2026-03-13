<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>請求書発行のお知らせ</title>
</head>
<body style="margin:0; padding:24px; background:#f3f4f6; color:#111827; font-family:Arial, 'Hiragino Sans', 'Meiryo', sans-serif;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:16px; padding:32px;">
        <h1 style="margin:0 0 16px; font-size:24px;">請求書を発行しました</h1>
        <p style="margin:0 0 12px; line-height:1.8;">
            {{ $invoice['shop_name'] }} 御中
        </p>
        <p style="margin:0 0 12px; line-height:1.8;">
            ミセチョク運営事務局です。店舗へのご請求書を発行しました。<br>
            下記リンクから請求内容を確認し、ブラウザの印刷機能で PDF 保存ができます。
        </p>
        <div style="margin:20px 0; padding:18px; border-radius:12px; background:#eff6ff;">
            <div style="font-size:13px; color:#1d4ed8; margin-bottom:8px;">請求情報</div>
            <div style="font-size:14px; line-height:1.8;">
                請求書番号: {{ $invoice['invoice_number'] }}<br>
                支払期限: {{ $invoice['due_date']->format('Y年m月d日') }}<br>
                請求金額: ¥{{ number_format($invoice['invoice_amount']) }}
            </div>
        </div>
        <p style="margin:0 0 20px;">
            <a href="{{ $invoiceUrl }}" style="display:inline-block; padding:12px 18px; background:#111827; color:#ffffff; text-decoration:none; border-radius:10px; font-weight:700;">
                請求書を確認する
            </a>
        </p>
        <p style="margin:0; font-size:13px; color:#6b7280; line-height:1.8;">
            店舗からの入金確認後、運営にてキャストへの振込手続きを進めます。<br>
            ご不明点がある場合は、このメールへご返信ください。
        </p>
    </div>
</body>
</html>
