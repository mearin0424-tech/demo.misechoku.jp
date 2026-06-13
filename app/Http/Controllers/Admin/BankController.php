<?php

namespace App\Http\Controllers\Admin;

use App\Rules\KouzaMeig;
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
     * 入力された口座情報は BillingManagementService::saveAdminBankAccount() を経由して
     * bank_accounts テーブル（holder_type = system_account）に永続化される。
     */
    public function index()
    {
        $stored = $this->billingManagementService->getAdminBankAccount();
        $bank = [
            'bank_code' => $stored->bank_code ?? '',
            'bank_name' => $stored->bank_name ?? '',
            'bank_name_kana' => $stored->bank_name_kana ?? '',
            'branch_code' => $stored->branch_code ?? '',
            'branch_name' => $stored->branch_name ?? '',
            'branch_name_kana' => $stored->branch_name_kana ?? '',
            'account_type' => $stored->account_type ?? 'ordinary',
            'account_number' => $stored->account_number ?? '',
            'account_name' => $stored->account_name ?? '',
        ];

        return view('admin.bank.index', compact('bank'));
    }

    public function store(Request $request)
    {
        $request->merge(
            $this->billingManagementService->normalizeBankAccountData($request->all())
        );

        $data = $request->validate([
            'bank_code' => ['required', 'regex:/^\d{4}$/'],
            'bank_name'      => 'required|string|max:100',
            'branch_code' => ['required', 'regex:/^\d{3}$/'],
            'branch_name'    => 'required|string|max:100',
            'account_type'   => 'required|in:ordinary,current',
            'account_number' => ['required', 'regex:/^\d{7,8}$/'],
            'account_name'   => ['required', 'string', 'max:100', new KouzaMeig()],
        ], [
            'bank_code.required' => '金融機関を候補から選択してください。',
            'bank_code.regex' => '金融機関コードが不正です。',
            'branch_code.required' => '支店を候補から選択してください。',
            'branch_code.regex' => '支店コードが不正です。',
            'account_number.required' => '口座番号を入力してください。',
            'account_number.regex' => '口座番号は7桁または8桁の数字で入力してください。',
            'account_name.required' => '口座名義（カナ）を入力してください。',
        ]);

        $this->billingManagementService->saveAdminBankAccount($data);

        return redirect()
            ->route('admin.bank.index')
            ->with('status', '運営の口座情報を保存しました。今後発行する請求書へ自動反映されます。');
    }
}

