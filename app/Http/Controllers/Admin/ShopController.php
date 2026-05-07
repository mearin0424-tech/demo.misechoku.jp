<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminPrivateAccessService;
use App\Services\BillingManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShopController extends Controller
{
    public function __construct(
        private readonly BillingManagementService $billingManagementService,
        private readonly AdminPrivateAccessService $privateAccessService,
    ) {
    }

    /**
     * 店舗管理一覧
     */
    public function index()
    {
        $operationSummaries = $this->billingManagementService->getOperationSummaryByEntity('shop');
        $horizontal = Schema::hasTable('shop_jobs') && Schema::hasColumn('shop_jobs', 'regular_status');

        $hasApprovalColumn = Schema::hasColumn('shops', 'approval');
        $managerLastLoginSub = DB::table('shop_managers')
            ->selectRaw('shop_id, MAX(last_login_at) as last_login_at')
            ->groupBy('shop_id');

        $query = DB::table('shops')
            ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->leftJoin('shop_jobs', 'shops.id', '=', 'shop_jobs.shop_id')
            ->leftJoinSub($managerLastLoginSub, 'manager_login', function ($join) {
                $join->on('shops.id', '=', 'manager_login.shop_id');
            })
            ->select(
                'shops.id',
                'shops.created_at',
                'shops.updated_at',
                'shops.status as shop_account_status',
                'shop_profiles.shop_name',
                'manager_login.last_login_at as latest_manager_login_at',
            );

        if ($hasApprovalColumn) {
            $query->addSelect('shops.approval');
        }

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
                    'registered_at' => $shop->created_at,
                    'last_login_at' => $shop->latest_manager_login_at ?? null,
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

    /**
     * 店舗詳細（公開情報＋運用実績は常時表示、非公開情報はパスワード解除制）
     */
    public function show(string $shopId)
    {
        $shop = DB::table('shops')->where('id', $shopId)->first();
        abort_unless($shop, 404);

        $profile = DB::table('shop_profiles')->where('shop_id', $shopId)->first();
        $job = DB::table('shop_jobs')->where('shop_id', $shopId)->first();
        $managers = DB::table('shop_managers')
            ->where('shop_id', $shopId)
            ->orderByDesc('last_login_at')
            ->get();
        $bank = DB::table('bank_accounts')
            ->where('holder_type', 'shops')
            ->where('holder_id', $shopId)
            ->first();

        // 取引履歴
        $applicationDeposits = DB::table('application_deposits')
            ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->leftJoin('cast_profiles', 'shop_job_applications.cast_id', '=', 'cast_profiles.cast_id')
            ->where('shop_jobs.shop_id', $shopId)
            ->orderByDesc('application_deposits.created_at')
            ->get([
                'application_deposits.id',
                'application_deposits.status',
                'application_deposits.invoice_number',
                'application_deposits.invoice_amount',
                'application_deposits.bonus_amount',
                'application_deposits.invoice_issued_at',
                'application_deposits.shop_payment_confirmed_at',
                'application_deposits.completed_at',
                'shop_job_applications.cast_id',
                'cast_profiles.nickname as cast_nickname',
            ]);

        $operationSummary = $this->billingManagementService->getOperationSummaryByEntity('shop')[$shopId] ?? null;

        $isUnlocked = $this->privateAccessService->isUnlocked('shop', $shopId);
        $unlockTtl = $this->privateAccessService->unlockedSecondsRemaining('shop', $shopId);

        return view('admin.shops.show', [
            'shopId' => $shopId,
            'shop' => $shop,
            'profile' => $profile,
            'job' => $job,
            'managers' => $managers,
            'bank' => $bank,
            'applicationDeposits' => $applicationDeposits,
            'operationSummary' => $operationSummary,
            'isUnlocked' => $isUnlocked,
            'unlockTtlSeconds' => $unlockTtl,
            'totalBilled' => (int) DB::table('application_deposits')
                ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
                ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
                ->where('shop_jobs.shop_id', $shopId)
                ->whereNotNull('application_deposits.invoice_amount')
                ->sum('application_deposits.invoice_amount'),
            'displayName' => $profile->shop_name ?? '未設定',
        ]);
    }

    public function unlockPrivate(Request $request, string $shopId): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $ok = $this->privateAccessService->unlockWithPassword('shop', $shopId, (string) $request->input('password'));
        if (!$ok) {
            return redirect()->route('admin.shops.show', $shopId)
                ->with('private_unlock_error', '管理者パスワードが一致しません。');
        }
        return redirect()->route('admin.shops.show', $shopId)
            ->with('status', '非公開情報を解除しました（' . (int) ($this->privateAccessService->ttlSeconds() / 60) . '分間有効）。');
    }

    public function lockPrivate(string $shopId): RedirectResponse
    {
        $this->privateAccessService->lock('shop', $shopId);
        return redirect()->route('admin.shops.show', $shopId)
            ->with('status', '非公開情報を再度ロックしました。');
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
