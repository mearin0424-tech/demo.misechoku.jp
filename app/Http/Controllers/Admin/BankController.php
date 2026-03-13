<?php

namespace App\Http\Controllers\Admin;

use App\Services\BillingManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function __construct(private readonly BillingManagementService $billingManagementService)
    {
    }

    /**
     * 運営側口座情報設定画面
     *
     * お店に発行する請求書に記載される振込先口座を登録する。
     * 現時点ではデモのためセッション保存のみ。
     */
    public function index()
    {
        $stored = $this->billingManagementService->getAdminBankAccount();
        $bank = [
            'bank_name' => $stored->bank_name ?? '',
            'branch_name' => $stored->branch_name ?? '',
            'account_type' => $stored->account_type ?? 'ordinary',
            'account_number' => $stored->account_number ?? '',
            'account_name' => $stored->account_name ?? '',
        ];

        return view('admin.bank.index', compact('bank'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bank_name'      => 'required|string|max:100',
            'branch_name'    => 'nullable|string|max:100',
            'account_type'   => 'required|string|max:20',
            'account_number' => 'required|string|max:30',
            'account_name'   => 'required|string|max:100',
        ]);

        $this->billingManagementService->saveAdminBankAccount($data);

        return redirect()
            ->route('admin.bank.index')
            ->with('status', '運営の口座情報を保存しました。今後発行する請求書へ自動反映されます。');
    }
}

