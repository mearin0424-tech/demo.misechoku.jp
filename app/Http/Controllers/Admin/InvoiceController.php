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
     * 請求書発行画面（発行待ち一覧・手動発行・帳票テンプレートDL・入金振込管理への導線）
     */
    public function index(): View
    {
        $dashboard = $this->billingManagementService->getAdminBillingDashboard();
        $pending = collect($dashboard['deposits'])->where('status_code', BillingManagementService::STATUS_SHOP_APPROVED)->values()->all();
        $manualTargets = collect($dashboard['deposits'])->filter(fn ($d) => empty($d['invoice_number']))->values()->all();
        $summary = $dashboard['summary'];
        $adminBank = $this->billingManagementService->getAdminBankAccount();

        return view('admin.invoices.index', [
            'pending' => $pending,
            'manualTargets' => $manualTargets,
            'summary' => $summary,
            'adminBank' => $adminBank,
        ]);
    }

    /**
     * 手動で請求書を発行（障害時等の回避策）
     */
    public function issueManual(Request $request): RedirectResponse
    {
        $request->validate([
            'deposit_id' => 'required|integer|min:1',
            'confirm_manual_workaround' => 'required|accepted',
            'confirm_admin_bank_ready' => 'required|accepted',
        ]);

        $result = $this->billingManagementService->issueInvoiceManually(
            (int) $request->input('deposit_id'),
            $request->only(['confirm_manual_workaround', 'confirm_admin_bank_ready'])
        );

        return redirect()
            ->route('admin.invoices.index')
            ->with($result['success'] ? 'status' : 'error', $result['message']);
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
