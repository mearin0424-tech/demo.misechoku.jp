<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BillingManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function __construct(
        private readonly BillingManagementService $billingManagementService
    ) {
    }

    /**
     * 店舗管理一覧
     */
    public function index()
    {
        $operationSummaries = $this->billingManagementService->getOperationSummaryByEntity('shop');

        $shops = DB::table('shops')
            ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->select(
                'shops.id',
                'shops.created_at',
                'shops.updated_at',
                'shops.approval',
                'shops.status as shop_account_status',
                'shop_profiles.shop_name',
                'shop_jobs.status as recruit_status',
                'shop_jobs.hourly_wage_regular'
            )
            ->orderByDesc('shops.created_at')
            ->get()
            ->map(function ($shop) use ($operationSummaries) {
                $shopId = (string) $shop->id;

                return [
                    'id' => $shop->id,
                    'name' => $shop->shop_name ?: '未設定',
                    'plan' => '未設定',
                    'fee' => 0,
                    'published_at' => $shop->created_at,
                    'document_status' => ((int) ($shop->approval ?? 0)) === 1 ? '確認済み' : '未確認',
                    'job_status' => ((int) ($shop->recruit_status ?? 0)) === 1 ? '公開中' : '非公開',
                    'job_status_key' => ((int) ($shop->recruit_status ?? 0)) === 1 ? 'active' : 'inactive',
                    'hourly_wage_regular' => (int) ($shop->hourly_wage_regular ?? 0),
                    'operation_summary' => $operationSummaries[$shopId] ?? null,
                ];
            });

        return view('admin.shops.index', [
            'shops' => $shops,
        ]);
    }

    public function toggleRecruitStatus(string $shopId): RedirectResponse
    {
        $currentStatus = DB::table('shop_jobs')->where('shop_id', $shopId)->value('status');
        $nextStatus = ((int) ($currentStatus ?? 0)) === 1 ? 0 : 1;
        $exists = DB::table('shop_jobs')->where('shop_id', $shopId)->exists();

        if ($exists) {
            DB::table('shop_jobs')
                ->where('shop_id', $shopId)
                ->update([
                    'status' => $nextStatus,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('shop_jobs')->insert([
                'shop_id' => $shopId,
                'status' => $nextStatus,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('admin.shops.index')
            ->with('status', $nextStatus === 1 ? '求人を公開しました。' : '求人を非公開にしました。');
    }
}

