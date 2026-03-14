<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BillingManagementService;
use App\Services\DocumentReviewService;

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
        $registrationKpis = [
            [
                'id' => 'cast',
                'title' => '登録キャスト数',
                'value' => '1,452',
                'unit' => '名',
                'trend' => '+12',
                'is_up' => true,
                'icon' => 'fa-users',
            ],
            [
                'id' => 'shop',
                'title' => '登録店舗 (プレミアム)',
                'value' => '215',
                'sub_value' => '48',
                'unit' => '店',
                'trend' => '+3',
                'is_up' => true,
                'icon' => 'fa-building',
            ],
        ];

        $transactionKpis = [
            [
                'id' => 'trx_count',
                'title' => '取引件数 (月)',
                'value' => '4,892',
                'unit' => '件',
                'trend' => '+124',
                'is_up' => true,
                'icon' => 'fa-chart-line',
            ],
            [
                'id' => 'trx_amount',
                'title' => '取引金額 (月)',
                'value' => '18.45',
                'unit' => 'M円',
                'trend' => '+5.2%',
                'is_up' => true,
                'icon' => 'fa-yen-sign',
            ],
        ];

        $chartData = [
            ['month' => '4月', 'cast' => 1200, 'shop' => 180, 'amount' => 12.0, 'count' => 3800],
            ['month' => '5月', 'cast' => 1250, 'shop' => 190, 'amount' => 13.5, 'count' => 4000],
            ['month' => '6月', 'cast' => 1280, 'shop' => 195, 'amount' => 12.8, 'count' => 3900],
            ['month' => '7月', 'cast' => 1320, 'shop' => 200, 'amount' => 15.0, 'count' => 4300],
            ['month' => '8月', 'cast' => 1380, 'shop' => 205, 'amount' => 16.2, 'count' => 4500],
            ['month' => '9月', 'cast' => 1410, 'shop' => 210, 'amount' => 17.5, 'count' => 4700],
            ['month' => '10月', 'cast' => 1452, 'shop' => 215, 'amount' => 18.45, 'count' => 4892],
        ];

        $documentTasks = $this->documentReviewService->getDashboardTasks();
        $billingTasks = collect($this->billingManagementService->getPendingTasks())
            ->map(function (array $task) {
                $catId = match ($task['status_code'] ?? null) {
                    BillingManagementService::STATUS_SHOP_PAYMENT_CONFIRMED => 'transfer',
                    BillingManagementService::STATUS_SHOP_PAYMENT_REPORTED => 'deposit',
                    default => 'deposit',
                };

                return [
                    'id' => 'deposit-' . ($task['id'] ?? 'unknown'),
                    'category' => $catId === 'transfer' ? '振込実行' : '入金照合',
                    'target' => $task['shop_name'] ?? $task['cast_name'] ?? '取引',
                    'type' => $catId === 'transfer' ? 'キャスト' : '店舗',
                    'status' => $task['status_label'] ?? '未処理',
                    'date' => $task['updated_at_label'] ?? ($task['task_due_date'] ?? '-'),
                    'urgency' => $catId === 'transfer' ? 'normal' : 'high',
                    'action' => $catId === 'transfer' ? '振込確認' : '着金確認',
                    'cat_id' => $catId,
                    'amount' => !empty($task['invoice_amount'])
                        ? '¥' . number_format((int) $task['invoice_amount'])
                        : (!empty($task['cast_transfer_amount']) ? '¥' . number_format((int) $task['cast_transfer_amount']) : null),
                    'url' => route('admin.deposits.index'),
                ];
            })
            ->all();

        $tasks = array_values(array_merge($documentTasks, $billingTasks));
        $taskSummary = [
            ['id' => 'kyc', 'title' => '本人確認', 'count' => collect($tasks)->where('cat_id', 'kyc')->count()],
            ['id' => 'doc', 'title' => '書類審査', 'count' => collect($tasks)->where('cat_id', 'doc')->count()],
            ['id' => 'deposit', 'title' => '入金確認', 'count' => collect($tasks)->where('cat_id', 'deposit')->count()],
            ['id' => 'transfer', 'title' => '振込実行', 'count' => collect($tasks)->where('cat_id', 'transfer')->count()],
            ['id' => 'error', 'title' => '振込エラー', 'count' => collect($tasks)->where('cat_id', 'error')->count()],
        ];

        return view('admin.dashboard', compact('registrationKpis', 'transactionKpis', 'chartData', 'taskSummary', 'tasks'));
    }
}

