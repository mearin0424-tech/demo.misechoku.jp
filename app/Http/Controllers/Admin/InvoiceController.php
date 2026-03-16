<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BillingManagementService;
use App\Services\InvoiceTemplateSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly BillingManagementService $billingManagementService,
        private readonly InvoiceTemplateSettingsService $templateSettings
    ) {
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

    /**
     * 請求書テンプレート設定画面
     */
    public function templateSettings(): View
    {
        $settings = $this->templateSettings->getForInvoice();

        return view('admin.invoices.template-settings', [
            'issuer_name' => $settings['issuer_name'],
            'issuer_email' => $settings['issuer_email'],
            'logo_url' => $settings['logo_url'],
            'footer_text' => $settings['footer_text'],
        ]);
    }

    /**
     * 請求書テンプレート設定の保存
     */
    public function updateTemplateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'issuer_name' => 'nullable|string|max:255',
            'issuer_email' => 'nullable|email|max:255',
            'logo_url' => 'nullable|url|max:500',
            'footer_text' => 'nullable|string|max:2000',
        ]);

        $this->templateSettings->saveFromRequest($request->only([
            'issuer_name', 'issuer_email', 'logo_url', 'footer_text',
        ]));

        return redirect()
            ->route('admin.invoices.template-settings')
            ->with('status', '請求書テンプレートの設定を保存しました。');
    }
}
