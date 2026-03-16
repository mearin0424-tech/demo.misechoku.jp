<?php

namespace App\Http\Controllers\Admin;

use App\Services\BillingManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DepositController extends Controller
{
    public function __construct(private readonly BillingManagementService $billingManagementService)
    {
    }

    /**
     * 入金・振込管理一覧
     */
    public function index()
    {
        $dashboard = $this->billingManagementService->getAdminBillingDashboard();

        return view('admin.deposit.index', [
            'deposits' => $dashboard['deposits'],
            'summary' => $dashboard['summary'],
            'adminBank' => $this->billingManagementService->getAdminBankAccount(),
        ]);
    }

    /**
     * 運営側：店舗への請求書発行
     */
    public function issueInvoice(Request $request, int $deposit)
    {
        $payload = $request->validate([
            'confirm_shop_approved' => 'required|accepted',
            'confirm_admin_bank_ready' => 'required|accepted',
        ]);

        $result = $this->billingManagementService->issueInvoice($deposit, $payload);

        return redirect()
            ->route('admin.deposits.index')
            ->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    /**
     * 運営側：店舗からの入金照合
     */
    public function confirmShopPayment(Request $request, int $deposit)
    {
        $payload = $request->validate([
            'confirmed_amount' => 'required|integer|min:1',
            'confirm_amount_checked' => 'required|accepted',
            'confirm_report_checked' => 'required|accepted',
            'confirm_bank_checked' => 'required|accepted',
        ]);

        $result = $this->billingManagementService->confirmShopPayment($deposit, $payload);

        return redirect()
            ->route('admin.deposits.index')
            ->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    /**
     * 運営側：キャストへの振込実行
     */
    public function transferCast(Request $request, int $deposit)
    {
        $payload = $request->validate([
            'transferred_at' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
            'confirm_transfer_amount' => 'required|accepted',
            'confirm_account_name' => 'required|accepted',
            'confirm_transfer_executed' => 'required|accepted',
            'confirm_receipt_checked' => 'required|accepted',
        ]);

        $result = $this->billingManagementService->executeCastTransfer($deposit, $payload);

        return redirect()
            ->route('admin.deposits.index')
            ->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    /**
     * 管理画面から請求書プレビューを表示
     */
    public function showInvoice(int $deposit)
    {
        $invoice = $this->billingManagementService->getInvoiceData($deposit);

        abort_unless($invoice, 404);

        return view('admin.deposit.invoice', [
            'invoice' => $invoice,
            'printMode' => false,
        ]);
    }

    /**
     * 店舗へ送付する署名付き請求書ビュー（HTML）
     */
    public function showSignedInvoice(int $deposit)
    {
        $invoice = $this->billingManagementService->getInvoiceData($deposit);

        abort_unless($invoice, 404);

        return view('admin.deposit.invoice', [
            'invoice' => $invoice,
            'printMode' => true,
        ]);
    }

    /**
     * 管理画面：請求書をPDFでダウンロード（帳票テンプレート使用）
     */
    public function downloadInvoicePdf(int $deposit): Response
    {
        $invoice = $this->billingManagementService->getInvoiceData($deposit);

        abort_unless($invoice, 404);

        return $this->invoiceToPdfResponse($invoice, '請求書_' . Str::slug($invoice['invoice_number']) . '.pdf');
    }

    /**
     * 店舗向け：署名付きURLで請求書をPDFダウンロード
     * Dompdf 未導入時はHTMLを表示し、ブラウザの印刷でPDF保存を案内する。
     */
    public function showSignedInvoicePdf(int $deposit)
    {
        $invoice = $this->billingManagementService->getInvoiceData($deposit);

        abort_unless($invoice, 404);

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return view('admin.deposit.invoice', [
                'invoice' => $invoice,
                'printMode' => true,
            ])->with('status', 'PDFは「印刷」→「PDFに保存」でダウンロードできます。');
        }

        $filename = '請求書_' . Str::slug($invoice['invoice_number']) . '.pdf';

        return $this->invoiceToPdfResponse($invoice, $filename);
    }

    /**
     * 請求書帳票テンプレートをサンプルデータでPDFダウンロード（運営管理画面用）
     * DomPDF 未導入時は印刷用HTMLプレビューを返し、別タブで同じ画面が開く問題を避ける。
     */
    public function downloadInvoiceTemplate(): Response|\Illuminate\Contracts\View\View
    {
        $invoice = $this->billingManagementService->getSampleInvoiceData();

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return view('admin.deposit.invoice-template-preview', ['invoice' => $invoice]);
        }

        return $this->invoiceToPdfResponse($invoice, '請求書_帳票テンプレート.pdf');
    }

    /**
     * 請求書データを帳票テンプレートでPDF化してレスポンスを返す。
     * barryvdh/laravel-dompdf がインストールされていない場合はHTML表示へリダイレクト。
     */
    private function invoiceToPdfResponse(array $invoice, string $filename): Response
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            if ($invoice['deposit_id'] > 0) {
                return redirect()
                    ->route('admin.deposits.invoice.show', ['deposit' => $invoice['deposit_id']])
                    ->with('status', 'PDF生成には barryvdh/laravel-dompdf のインストールが必要です。画面の「印刷」から「PDFに保存」を選択してください。');
            }
            return redirect()
                ->route('admin.invoices.index')
                ->with('status', 'PDF生成には barryvdh/laravel-dompdf のインストールが必要です。テンプレートは「帳票テンプレートをダウンロード」で開いた画面の印刷からPDFに保存できます。');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('billing.invoice-template', ['invoice' => $invoice]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}

