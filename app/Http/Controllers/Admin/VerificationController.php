<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * 本人確認・書類提出ステータス一覧（デモ用）
     */
    public function index()
    {
        $castIdentityStatus = session('cast_identity_status', 'not_submitted');
        $shopDocStatus = session('shop_documents_status', [
            'business_license' => 'not_submitted',
            'adult_entertainment_license' => 'not_submitted',
        ]);

        return view('admin.verification.index', [
            'castIdentityStatus' => $castIdentityStatus,
            'shopDocStatus' => $shopDocStatus,
        ]);
    }

    /**
     * キャスト本人確認の承認
     */
    public function approveCast(Request $request)
    {
        session(['cast_identity_status' => 'approved']);

        return redirect()
            ->route('admin.verification.index')
            ->with('status', 'キャストの本人確認を承認しました。（デモ）');
    }

    /**
     * 店舗の書類提出の承認
     */
    public function approveShopDocument(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:business_license,adult_entertainment_license',
        ]);

        $statuses = session('shop_documents_status', [
            'business_license' => 'not_submitted',
            'adult_entertainment_license' => 'not_submitted',
        ]);

        $statuses[$data['type']] = 'approved';
        session(['shop_documents_status' => $statuses]);

        return redirect()
            ->route('admin.verification.index')
            ->with('status', '店舗の書類（' . $data['type'] . '）を承認しました。（デモ）');
    }
}

