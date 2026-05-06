<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BillingManagementService;
use App\Services\DocumentReviewService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BillingManagementService $billingManagementService,
        private readonly DocumentReviewService $documentReviewService
    ) {
    }

    /** 管理者ダッシュボード */
    public function index()
    {
        $now = Carbon::now();
        $startThisMonth = $now->copy()->startOfMonth();
        $startPrevMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endPrevMonth = $startThisMonth->copy()->subSecond();

        $castTotal = Schema::hasTable('casts')
            ? (int) DB::table('casts')->count()
            : 0;
        $castPrevTotal = Schema::hasTable('casts')
            ? (int) DB::table('casts')->where('created_at', '<', $startThisMonth)->count()
            : 0;
        $castMonthlyDelta = max(0, $castTotal - $castPrevTotal);

        $shopTotal = Schema::hasTable('shops')
            ? (int) DB::table('shops')->count()
            : 0;
        $shopPrevTotal = Schema::hasTable('shops')
            ? (int) DB::table('shops')->where('created_at', '<', $startThisMonth)->count()
            : 0;
        $shopMonthlyDelta = max(0, $shopTotal - $shopPrevTotal);

        $trxCountThisMonth = 0;
        $trxCountPrevMonth = 0;
        $trxAmountThisMonth = 0;
        $trxAmountPrevMonth = 0;
        $hasDepositAmountColumn = Schema::hasTable('application_deposits') && Schema::hasColumn('application_deposits', 'invoice_amount');
        if (Schema::hasTable('application_deposits')) {
            $trxCountThisMonth = (int) DB::table('application_deposits')
                ->whereBetween('created_at', [$startThisMonth, $now])
                ->count();
            $trxCountPrevMonth = (int) DB::table('application_deposits')
                ->whereBetween('created_at', [$startPrevMonth, $endPrevMonth])
                ->count();

            if ($hasDepositAmountColumn) {
                $trxAmountThisMonth = (int) DB::table('application_deposits')
                    ->whereBetween('created_at', [$startThisMonth, $now])
                    ->sum('invoice_amount');
                $trxAmountPrevMonth = (int) DB::table('application_deposits')
                    ->whereBetween('created_at', [$startPrevMonth, $endPrevMonth])
                    ->sum('invoice_amount');
            }
        }

        $registrationKpis = [
            [
                'id' => 'cast',
                'title' => '登録キャスト数',
                'value' => number_format($castTotal),
                'unit' => '名',
                'trend' => ($castMonthlyDelta >= 0 ? '+' : '') . number_format($castMonthlyDelta),
                'is_up' => $castMonthlyDelta >= 0,
                'icon' => 'fa-users',
            ],
            [
                'id' => 'shop',
                'title' => '登録店舗 (プレミアム)',
                'value' => number_format($shopTotal),
                'sub_value' => '0',
                'unit' => '店',
                'trend' => ($shopMonthlyDelta >= 0 ? '+' : '') . number_format($shopMonthlyDelta),
                'is_up' => $shopMonthlyDelta >= 0,
                'icon' => 'fa-building',
            ],
        ];

        $transactionKpis = [
            [
                'id' => 'trx_count',
                'title' => '取引件数 (月)',
                'value' => number_format($trxCountThisMonth),
                'unit' => '件',
                'trend' => ($trxCountThisMonth - $trxCountPrevMonth >= 0 ? '+' : '') . number_format($trxCountThisMonth - $trxCountPrevMonth),
                'is_up' => ($trxCountThisMonth - $trxCountPrevMonth) >= 0,
                'icon' => 'fa-chart-line',
            ],
            [
                'id' => 'trx_amount',
                'title' => '取引金額 (月)',
                'value' => number_format($trxAmountThisMonth / 1000000, 2),
                'unit' => 'M円',
                'trend' => ($trxAmountPrevMonth > 0
                    ? (($trxAmountThisMonth - $trxAmountPrevMonth) >= 0 ? '+' : '') . number_format((($trxAmountThisMonth - $trxAmountPrevMonth) / $trxAmountPrevMonth) * 100, 1) . '%'
                    : '+0.0%'),
                'is_up' => ($trxAmountThisMonth - $trxAmountPrevMonth) >= 0,
                'icon' => 'fa-yen-sign',
            ],
        ];

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $castCount = Schema::hasTable('casts')
                ? (int) DB::table('casts')->where('created_at', '<=', $monthEnd)->count()
                : 0;
            $shopCount = Schema::hasTable('shops')
                ? (int) DB::table('shops')->where('created_at', '<=', $monthEnd)->count()
                : 0;
            $monthTrxCount = Schema::hasTable('application_deposits')
                ? (int) DB::table('application_deposits')->whereBetween('created_at', [$monthStart, $monthEnd])->count()
                : 0;
            $monthTrxAmount = $hasDepositAmountColumn
                ? (int) DB::table('application_deposits')->whereBetween('created_at', [$monthStart, $monthEnd])->sum('invoice_amount')
                : 0;

            $chartData[] = [
                'month' => $monthStart->format('n月'),
                'cast' => $castCount,
                'shop' => $shopCount,
                'amount' => round($monthTrxAmount / 1000000, 2),
                'count' => $monthTrxCount,
            ];
        }

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

        return view('admin.dashboard', compact('registrationKpis', 'transactionKpis', 'chartData', 'taskSummary', 'tasks'));
    }
}

