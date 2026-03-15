<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BillingManagementService;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly BillingManagementService $billingManagementService)
    {
    }

    /**
     * 請求書発行画面（発行待ち一覧・帳票テンプレートDL・入金振込管理への導線）
     */
    public function index(): View
    {
        $dashboard = $this->billingManagementService->getAdminBillingDashboard();
        $pending = collect($dashboard['deposits'])->where('status_code', BillingManagementService::STATUS_SHOP_APPROVED)->values()->all();
        $summary = $dashboard['summary'];
        $adminBank = $this->billingManagementService->getAdminBankAccount();

        return view('admin.invoices.index', [
            'pending' => $pending,
            'summary' => $summary,
            'adminBank' => $adminBank,
        ]);
    }
}
