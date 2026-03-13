<?php

namespace App\Http\Controllers\Admin;

use App\Services\BillingManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
    public function issueInvoice(int $deposit)
    {
        $result = $this->billingManagementService->issueInvoice($deposit);

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
     * 店舗へ送付する署名付き請求書ビュー
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
}

