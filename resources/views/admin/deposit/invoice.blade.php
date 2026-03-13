<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>請求書 {{ $invoice['invoice_number'] }}</title>
    <style>
        body {
            margin: 0;
            background: #f3f4f6;
            color: #111827;
            font-family: "Helvetica Neue", Arial, "Hiragino Sans", "Meiryo", sans-serif;
        }
        .invoice-shell {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .invoice-toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 16px;
        }
        .invoice-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            background: #111827;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
        }
        .invoice-paper {
            background: #fff;
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            border-bottom: 2px solid #111827;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .invoice-title {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 0.08em;
            margin: 0 0 10px;
        }
        .invoice-sub,
        .invoice-meta,
        .invoice-note {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.7;
        }
        .invoice-to {
            margin-bottom: 24px;
        }
        .invoice-to-name {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 8px;
        }
        .invoice-total-box {
            margin: 24px 0;
            padding: 20px 24px;
            border-radius: 16px;
            background: #eff6ff;
        }
        .invoice-total-label {
            color: #1d4ed8;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .invoice-total-value {
            font-size: 34px;
            font-weight: 800;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .invoice-table th,
        .invoice-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 14px 10px;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }
        .invoice-table th {
            width: 28%;
            color: #4b5563;
        }
        .invoice-bank {
            margin-top: 28px;
            padding: 20px 24px;
            border-radius: 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .invoice-bank-title {
            margin: 0 0 12px;
            font-size: 18px;
            font-weight: 800;
        }
        @media print {
            body {
                background: #fff;
            }
            .invoice-shell {
                margin: 0;
                max-width: none;
                padding: 0;
            }
            .invoice-toolbar {
                display: none;
            }
            .invoice-paper {
                box-shadow: none;
                border-radius: 0;
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-shell">
        <div class="invoice-toolbar">
            <button type="button" class="invoice-btn" onclick="window.print()">PDFとして保存 / 印刷</button>
            @if(!$printMode)
                <a href="{{ route('admin.deposits.index') }}" class="invoice-btn" style="background:#374151;">管理画面へ戻る</a>
            @endif
        </div>

        <div class="invoice-paper">
            <div class="invoice-header">
                <div>
                    <h1 class="invoice-title">請 求 書</h1>
                    <div class="invoice-sub">
                        請求書番号: {{ $invoice['invoice_number'] }}<br>
                        発行日: {{ $invoice['issued_at']->format('Y年m月d日') }}<br>
                        支払期限: {{ $invoice['due_date']->format('Y年m月d日') }}
                    </div>
                </div>
                <div class="invoice-meta">
                    ミセチョク運営事務局<br>
                    support@misechoku.jp<br>
                    本請求書はシステム上の請求・振込タスク管理機能より発行されています。
                </div>
            </div>

            <div class="invoice-to">
                <p class="invoice-to-name">{{ $invoice['shop_name'] }} 御中</p>
                <div class="invoice-note">
                    {{ $invoice['shop_address'] ?: '住所未登録' }}<br>
                    {{ $invoice['shop_email'] ?: 'メール未登録' }}
                </div>
            </div>

            <div class="invoice-total-box">
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
                            店舗からのご入金確認後、運営にてキャストへの振込実務を行います。<br>
                            振込自体は手動実行であり、本システムは照合・進捗管理を支援します。
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="invoice-bank">
                <h2 class="invoice-bank-title">お振込先</h2>
                <div class="invoice-note">
                    金融機関: {{ $invoice['admin_bank']['bank_name'] }}<br>
                    支店名: {{ $invoice['admin_bank']['branch_name'] ?: '未設定' }}<br>
                    口座種別: {{ $invoice['admin_bank']['account_type_label'] }}<br>
                    口座番号: {{ $invoice['admin_bank']['account_number'] }}<br>
                    口座名義: {{ $invoice['admin_bank']['account_name'] }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
