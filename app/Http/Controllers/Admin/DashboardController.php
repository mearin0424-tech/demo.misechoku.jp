<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Services\BillingManagementService;
use App\Services\DocumentReviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BillingManagementService $billingManagementService,
        private readonly DocumentReviewService $documentReviewService
    ) {
    }

    /**
     * 管理者ダッシュボード
     *
     * ※ 現時点ではダミー情報のみ表示し、後から集計ロジックを差し込める構成にしておく。
     */
    public function index()
    {
        $now = Carbon::now();
        $monthStarts = collect(range(6, 0))
            ->map(fn (int $monthsAgo) => $now->copy()->startOfMonth()->subMonths($monthsAgo))
            ->push($now->copy()->startOfMonth())
            ->values();

        $castTimeline = $this->buildMonthlyTimeline('casts', $monthStarts, true);
        $shopTimeline = $this->buildMonthlyTimeline('shops', $monthStarts, true);
        $transactionCountTimeline = $this->buildMonthlyTimeline('application_deposits', $monthStarts, false, 'count');
        $transactionAmountTimeline = $this->buildMonthlyTimeline('application_deposits', $monthStarts, false, 'sum', 'invoice_amount');

        $currentMonthStart = $now->copy()->startOfMonth();
        $previousMonthStart = $currentMonthStart->copy()->subMonth();
        $currentMonthKey = $currentMonthStart->format('Y-m');
        $previousMonthKey = $previousMonthStart->format('Y-m');

        $currentCast = (int) ($castTimeline[$currentMonthKey] ?? 0);
        $previousCast = (int) ($castTimeline[$previousMonthKey] ?? 0);
        $currentShop = (int) ($shopTimeline[$currentMonthKey] ?? 0);
        $previousShop = (int) ($shopTimeline[$previousMonthKey] ?? 0);
        $currentTransactionCount = (int) ($transactionCountTimeline[$currentMonthKey] ?? 0);
        $previousTransactionCount = (int) ($transactionCountTimeline[$previousMonthKey] ?? 0);
        $currentTransactionAmount = (int) ($transactionAmountTimeline[$currentMonthKey] ?? 0);
        $previousTransactionAmount = (int) ($transactionAmountTimeline[$previousMonthKey] ?? 0);
        $premiumShopCount = $this->countPremiumShops();

        $registrationKpis = [
            [
                'id' => 'cast',
                'title' => '登録キャスト数',
                'value' => number_format($currentCast),
                'unit' => '名',
                'trend' => $this->formatDiff($currentCast - $previousCast),
                'is_up' => $currentCast >= $previousCast,
                'icon' => 'fa-users',
            ],
            [
                'id' => 'shop',
                'title' => '登録店舗 (プレミアム)',
                'value' => number_format($currentShop),
                'sub_value' => number_format($premiumShopCount),
                'unit' => '店',
                'trend' => $this->formatDiff($currentShop - $previousShop),
                'is_up' => $currentShop >= $previousShop,
                'icon' => 'fa-building',
            ],
        ];

        $transactionKpis = [
            [
                'id' => 'trx_count',
                'title' => '取引件数 (月)',
                'value' => number_format($currentTransactionCount),
                'unit' => '件',
                'trend' => $this->formatDiff($currentTransactionCount - $previousTransactionCount),
                'is_up' => $currentTransactionCount >= $previousTransactionCount,
                'icon' => 'fa-chart-line',
            ],
            [
                'id' => 'trx_amount',
                'title' => '取引金額 (月)',
                'value' => number_format($currentTransactionAmount / 1000000, 2),
                'unit' => 'M円',
                'trend' => $this->formatRatio($currentTransactionAmount, $previousTransactionAmount),
                'is_up' => $currentTransactionAmount >= $previousTransactionAmount,
                'icon' => 'fa-yen-sign',
            ],
        ];

        $chartData = $monthStarts->map(function (Carbon $monthStart) use ($castTimeline, $shopTimeline, $transactionCountTimeline, $transactionAmountTimeline) {
            $key = $monthStart->format('Y-m');

            return [
                'month' => $monthStart->format('n月'),
                'cast' => (int) ($castTimeline[$key] ?? 0),
                'shop' => (int) ($shopTimeline[$key] ?? 0),
                'amount' => round(((int) ($transactionAmountTimeline[$key] ?? 0)) / 1000000, 2),
                'count' => (int) ($transactionCountTimeline[$key] ?? 0),
            ];
        })->all();

        $documentTasks = $this->documentReviewService->getDashboardTasks();
        $billingTasks = collect($this->billingManagementService->getPendingTasks())
            ->map(function (array $task) {
                $catId = match ($task['status_code'] ?? null) {
                    BillingManagementService::STATUS_SHOP_PAYMENT_CONFIRMED => 'transfer',
                    BillingManagementService::STATUS_SHOP_PAYMENT_REPORTED => 'deposit',
                    BillingManagementService::STATUS_CAST_REQUESTED,
                    BillingManagementService::STATUS_SHOP_APPROVED => 'invoice',
                    default => 'deposit',
                };

                $categoryLabel = match ($catId) {
                    'transfer' => '振込実行',
                    'invoice' => '請求書発行',
                    default => '入金照合',
                };

                return [
                    'id' => 'deposit-' . ($task['id'] ?? 'unknown'),
                    'category' => $categoryLabel,
                    'target' => $task['shop_name'] ?? $task['cast_name'] ?? '取引',
                    'type' => $catId === 'transfer' ? 'キャスト' : '店舗',
                    'status' => $task['status_label'] ?? '未処理',
                    'date' => $task['updated_at_label'] ?? ($task['task_due_date'] ?? '-'),
                    'urgency' => $catId === 'transfer' ? 'normal' : 'high',
                    'action' => match ($catId) {
                        'transfer' => '振込確認',
                        'invoice' => '請求対応',
                        default => '着金確認',
                    },
                    'cat_id' => $catId,
                    'amount' => !empty($task['invoice_amount'])
                        ? '¥' . number_format((int) $task['invoice_amount'])
                        : (!empty($task['cast_transfer_amount']) ? '¥' . number_format((int) $task['cast_transfer_amount']) : null),
                    'url' => $task['task_url'] ?? route('admin.deposits.index'),
                ];
            })
            ->all();

        $tasks = array_values(array_merge($documentTasks, $billingTasks));
        $taskSummary = [
            ['id' => 'kyc', 'title' => '本人確認', 'count' => collect($tasks)->where('cat_id', 'kyc')->count()],
            ['id' => 'doc', 'title' => '書類審査', 'count' => collect($tasks)->where('cat_id', 'doc')->count()],
            ['id' => 'invoice', 'title' => '請求書発行', 'count' => collect($tasks)->where('cat_id', 'invoice')->count()],
            ['id' => 'deposit', 'title' => '入金確認', 'count' => collect($tasks)->where('cat_id', 'deposit')->count()],
            ['id' => 'transfer', 'title' => '振込実行', 'count' => collect($tasks)->where('cat_id', 'transfer')->count()],
            ['id' => 'error', 'title' => '振込エラー', 'count' => collect($tasks)->where('cat_id', 'error')->count()],
        ];

        $currentPeriodLabel = $now->format('Y年n月');

        return view('admin.dashboard', compact('registrationKpis', 'transactionKpis', 'chartData', 'taskSummary', 'tasks', 'currentPeriodLabel'));
    }

    /**
     * @return array<string, int>
     */
    private function buildMonthlyTimeline(
        string $table,
        \Illuminate\Support\Collection $monthStarts,
        bool $cumulative = false,
        string $aggregate = 'count',
        ?string $sumColumn = null
    ): array {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'created_at')) {
            return $monthStarts
                ->mapWithKeys(fn (Carbon $monthStart) => [$monthStart->format('Y-m') => 0])
                ->all();
        }

        if ($aggregate === 'sum' && $sumColumn !== null && ! Schema::hasColumn($table, $sumColumn)) {
            return $monthStarts
                ->mapWithKeys(fn (Carbon $monthStart) => [$monthStart->format('Y-m') => 0])
                ->all();
        }

        $startDate = $monthStarts->first()->copy()->startOfMonth();
        $rows = DB::table($table)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym")
            ->selectRaw($aggregate === 'sum' && $sumColumn !== null ? 'COALESCE(SUM(' . $sumColumn . '), 0) as total' : 'COUNT(*) as total')
            ->where('created_at', '>=', $startDate)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $timeline = [];
        $runningTotal = $cumulative ? $this->countBeforeDate($table, $startDate) : 0;
        foreach ($monthStarts as $monthStart) {
            $key = $monthStart->format('Y-m');
            $value = (int) ($rows[$key] ?? 0);
            if ($cumulative) {
                $runningTotal += $value;
                $timeline[$key] = $runningTotal;
            } else {
                $timeline[$key] = $value;
            }
        }

        return $timeline;
    }

    private function countBeforeDate(string $table, Carbon $startDate): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'created_at')) {
            return 0;
        }

        return (int) DB::table($table)
            ->where('created_at', '<', $startDate)
            ->count();
    }

    private function countPremiumShops(): int
    {
        if (! Schema::hasTable('application_deposits') || ! Schema::hasTable('shop_job_applications') || ! Schema::hasTable('shop_jobs')) {
            return 0;
        }

        $threeMonthsAgo = Carbon::now()->subMonths(3);
        $disqualified = DB::table('application_deposits')
            ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->where('application_deposits.created_at', '>=', $threeMonthsAgo)
            ->where('application_deposits.status', '<', 5)
            ->distinct()
            ->pluck('shop_jobs.shop_id')
            ->flip()
            ->all();

        $confirmedShopIds = DB::table('application_deposits')
            ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->where('application_deposits.created_at', '>=', $threeMonthsAgo)
            ->where('application_deposits.status', '>=', 5)
            ->distinct()
            ->pluck('shop_jobs.shop_id');

        return $confirmedShopIds
            ->reject(fn ($shopId) => isset($disqualified[$shopId]))
            ->count();
    }

    private function formatDiff(int $diff): string
    {
        if ($diff === 0) {
            return '±0';
        }

        return ($diff > 0 ? '+' : '') . number_format($diff);
    }

    private function formatRatio(int $current, int $previous): string
    {
        if ($previous <= 0) {
            return $current > 0 ? '+100%' : '±0%';
        }

        $ratio = (($current - $previous) / $previous) * 100;

        return ($ratio >= 0 ? '+' : '') . number_format($ratio, 1) . '%';
    }
}

