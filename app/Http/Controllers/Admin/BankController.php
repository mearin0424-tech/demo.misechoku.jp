<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * 運営側口座情報設定画面
     *
     * お店に発行する請求書に記載される振込先口座を登録する。
     * 現時点ではデモのためセッション保存のみ。
     */
    public function index()
    {
        $bank = session('admin_bank_account', [
            'bank_name'      => '',
            'branch_name'    => '',
            'account_type'   => 'ordinary',
            'account_number' => '',
            'account_name'   => '',
        ]);

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

        // 本番では設定テーブル等に永続化する想定。デモのためセッションに保持。
        session(['admin_bank_account' => $data]);

        return redirect()
            ->route('admin.bank.index')
            ->with('status', '運営の口座情報を保存しました。（デモ環境ではセッション保存のみ）');
    }
}

