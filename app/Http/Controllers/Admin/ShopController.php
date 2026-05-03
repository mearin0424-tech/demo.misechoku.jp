<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BillingManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $horizontal = Schema::hasTable('shop_jobs') && Schema::hasColumn('shop_jobs', 'regular_status');

        $query = DB::table('shops')
            ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->select(
                'shops.id',
                'shops.created_at',
                'shops.updated_at',
                'shops.approval',
                'shops.status as shop_account_status',
                'shop_profiles.shop_name',
            );

        if ($horizontal) {
            $query->addSelect(
                'shop_jobs.regular_status',
                'shop_jobs.trial_status',
                'shop_jobs.help_status'
            );
            if (Schema::hasColumn('shop_jobs', 'regular_hourly_wage')) {
                $query->addSelect('shop_jobs.regular_hourly_wage as job_hourly_wage');
            } elseif (Schema::hasColumn('shop_jobs', 'hourly_wage_regular')) {
                $query->addSelect('shop_jobs.hourly_wage_regular as job_hourly_wage');
            }
        } else {
            if (Schema::hasColumn('shop_jobs', 'status')) {
                $query->addSelect('shop_jobs.status as recruit_status');
            }
            if (Schema::hasColumn('shop_jobs', 'hourly_wage_regular')) {
                $query->addSelect('shop_jobs.hourly_wage_regular as job_hourly_wage');
            }
        }

        $shops = $query
            ->orderByDesc('shops.created_at')
            ->get()
            ->map(function ($shop) use ($operationSummaries, $horizontal) {
                $shopId = (string) $shop->id;

                if ($horizontal) {
                    $reg = (int) ($shop->regular_status ?? 0);
                    $jobStatusLabel = sprintf(
                        '本入:%s / 体験:%s / ヘルプ:%s',
                        $reg === 1 ? '公開' : '非公開',
                        (int) ($shop->trial_status ?? 0) === 1 ? '公開' : '非公開',
                        (int) ($shop->help_status ?? 0) === 1 ? '公開' : '非公開'
                    );
                    $jobStatusKey = $reg === 1 ? 'active' : 'inactive';
                    $adminRecruitToggles = [
                        [
                            'job_type' => 1,
                            'label' => '本入',
                            'is_on' => $reg === 1,
                        ],
                        [
                            'job_type' => 2,
                            'label' => '体験',
                            'is_on' => (int) ($shop->trial_status ?? 0) === 1,
                        ],
                        [
                            'job_type' => 3,
                            'label' => 'ヘルプ',
                            'is_on' => (int) ($shop->help_status ?? 0) === 1,
                        ],
                    ];
                } else {
                    $st = (int) ($shop->recruit_status ?? 0);
                    $jobStatusLabel = $st === 1 ? '公開中' : '非公開';
                    $jobStatusKey = $st === 1 ? 'active' : 'inactive';
                    $adminRecruitToggles = [
                        [
                            'job_type' => 1,
                            'label' => '求人',
                            'is_on' => $st === 1,
                        ],
                    ];
                }

                return [
                    'id' => $shop->id,
                    'name' => $shop->shop_name ?: '未設定',
                    'plan' => '未設定',
                    'fee' => 0,
                    'published_at' => $shop->created_at,
                    'document_status' => ((int) ($shop->approval ?? 0)) === 1 ? '確認済み' : '未確認',
                    'job_status' => $jobStatusLabel,
                    'job_status_key' => $jobStatusKey,
                    'hourly_wage_regular' => (int) ($shop->job_hourly_wage ?? 0),
                    'recruit_schema_horizontal' => $horizontal,
                    'admin_recruit_toggles' => $adminRecruitToggles,
                    'operation_summary' => $operationSummaries[$shopId] ?? null,
                ];
            });

        return view('admin.shops.index', [
            'shops' => $shops,
        ]);
    }

    public function toggleRecruitStatus(Request $request, string $shopId): RedirectResponse
    {
        $jt = (int) $request->input('job_type', 1);
        if (!in_array($jt, [1, 2, 3], true)) {
            $jt = 1;
        }

        if (Schema::hasTable('shop_jobs') && Schema::hasColumn('shop_jobs', 'regular_status')) {
            $col = match ($jt) {
                2 => 'trial_status',
                3 => 'help_status',
                default => 'regular_status',
            };
            if (!Schema::hasColumn('shop_jobs', $col)) {
                return redirect()
                    ->route('admin.shops.index')
                    ->with('status', '求人ステータス列が見つかりません。');
            }

            $row = DB::table('shop_jobs')->where('shop_id', $shopId)->first();
            if (!$row) {
                $insert = [
                    'shop_id' => $shopId,
                    'regular_status' => 0,
                    'trial_status' => 0,
                    'help_status' => 0,
                    $col => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $insert = $this->filterExistingShopJobColumns($insert);
                DB::table('shop_jobs')->insert($insert);

                return redirect()
                    ->route('admin.shops.index')
                    ->with('status', '求人を公開しました。');
            }

            $cur = (int) ($row->{$col} ?? 0);
            $next = $cur === 1 ? 0 : 1;
            DB::table('shop_jobs')
                ->where('shop_id', $shopId)
                ->update([
                    $col => $next,
                    'updated_at' => now(),
                ]);

            return redirect()
                ->route('admin.shops.index')
                ->with('status', $next === 1 ? '求人を公開しました。' : '求人を非公開にしました。');
        }

        $currentStatus = Schema::hasColumn('shop_jobs', 'status')
            ? DB::table('shop_jobs')->where('shop_id', $shopId)->value('status')
            : null;
        $nextStatus = ((int) ($currentStatus ?? 0)) === 1 ? 0 : 1;
        $exists = DB::table('shop_jobs')->where('shop_id', $shopId)->exists();

        if ($exists) {
            $upd = ['updated_at' => now()];
            if (Schema::hasColumn('shop_jobs', 'status')) {
                $upd['status'] = $nextStatus;
            }
            $upd = $this->filterExistingShopJobColumns($upd);
            if (!empty($upd)) {
                DB::table('shop_jobs')
                    ->where('shop_id', $shopId)
                    ->update($upd);
            }
        } else {
            $insert = [
                'shop_id' => $shopId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('shop_jobs', 'status')) {
                $insert['status'] = $nextStatus;
            }
            $insert = $this->filterExistingShopJobColumns($insert);
            DB::table('shop_jobs')->insert($insert);
        }

        return redirect()
            ->route('admin.shops.index')
            ->with('status', $nextStatus === 1 ? '求人を公開しました。' : '求人を非公開にしました。');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function filterExistingShopJobColumns(array $payload): array
    {
        if (!Schema::hasTable('shop_jobs')) {
            return [];
        }

        return collect($payload)
            ->filter(fn ($value, $column) => Schema::hasColumn('shop_jobs', (string) $column))
            ->all();
    }
}
