<?php

namespace App\Services;

use App\Models\CastIdentityDocument;
use App\Models\ShopLicenseDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 管理画面サイドバー（オペレーション）の未対応バッジ・ヘッダー通知の集計
 */
class AdminOperationalSummaryService
{
    public function __construct(
        private readonly BillingManagementService $billingManagementService,
        private readonly DocumentReviewService $documentReviewService
    ) {
    }

    /**
     * ルート名 => 未対応件数（0 のときはバッジ非表示）
     *
     * @return array<string, int>
     */
    public function getOperationBadgeCounts(): array
    {
        $dashboard = $this->billingManagementService->getAdminBillingDashboard();
        $s = $dashboard['summary'];
        $v = $this->documentReviewService->getAdminVerificationData()['summary'];

        return [
            'admin.invoices.index' => (int) ($s['invoice_workflow_pending'] ?? 0),
            'admin.deposits.index' => (int) $s['payment_confirmation_pending'] + (int) $s['cast_transfer_pending'],
            'admin.verification.index' => (int) $v['cast_pending'] + (int) $v['shop_pending'],
            'admin.inquiries.index' => $this->getPendingInquiryCount(),
        ];
    }

    /**
     * オペレーション各メニューに対応する「実績」（累計の完了系件数）
     *
     * @return array<string, int>
     */
    public function getOperationAchievementCounts(): array
    {
        return [
            'admin.invoices.index' => $this->countInvoicesIssuedTotal(),
            'admin.deposits.index' => $this->countDepositFlowsCompletedTotal(),
            'admin.verification.index' => $this->countVerificationProcessedTotal(),
            'admin.inquiries.index' => $this->countInquiriesResolvedTotal(),
        ];
    }

    private function countInvoicesIssuedTotal(): int
    {
        if (! Schema::hasTable('application_deposits')) {
            return 0;
        }

        return (int) DB::table('application_deposits')
            ->whereNotNull('invoice_number')
            ->where('invoice_number', '!=', '')
            ->count();
    }

    private function countDepositFlowsCompletedTotal(): int
    {
        if (! Schema::hasTable('application_deposits')) {
            return 0;
        }

        return (int) DB::table('application_deposits')
            ->where('status', BillingManagementService::STATUS_COMPLETED)
            ->count();
    }

    private function countVerificationProcessedTotal(): int
    {
        $cast = 0;
        $shop = 0;

        if (Schema::hasTable('cast_identity_documents')) {
            $cast = (int) CastIdentityDocument::query()
                ->whereIn('status', [
                    CastIdentityDocument::STATUS_APPROVED,
                    CastIdentityDocument::STATUS_REJECTED,
                ])
                ->count();
        }

        if (Schema::hasTable('shop_license_documents')) {
            $shop = (int) ShopLicenseDocument::query()
                ->whereIn('status', [
                    ShopLicenseDocument::STATUS_APPROVED,
                    ShopLicenseDocument::STATUS_REJECTED,
                ])
                ->count();
        }

        return $cast + $shop;
    }

    private function countInquiriesResolvedTotal(): int
    {
        if (!Schema::hasTable('inquiries')) {
            return 0;
        }

        return (int) DB::table('inquiries')
            ->whereIn('status', ['対応済み', '完了', 'クローズ'])
            ->count();
    }

    /**
     * レイアウト用：通知一覧（先頭 N 件）と総件数を一度に取得
     *
     * @return array{items: array<int, array{title: string, time_label: string, icon: string, class: string, url: string}>, total_count: int}
     */
    public function getNotificationsForLayout(int $listLimit = 30): array
    {
        $all = $this->buildNotifications(500);

        return [
            'items' => array_slice($all, 0, $listLimit),
            'total_count' => count($all),
        ];
    }

    /**
     * @return array<int, array{title: string, time_label: string, icon: string, class: string, url: string, sort: int}>
     */
    private function buildNotifications(int $maxBillingTasks): array
    {
        $items = [];
        $dashboard = $this->billingManagementService->getAdminBillingDashboard();
        $summary = $dashboard['summary'];

        foreach (array_slice($this->billingManagementService->getPendingTasks(), 0, $maxBillingTasks) as $task) {
            $items[] = $this->mapBillingTaskToNotification($task);
        }

        foreach ($this->documentReviewService->getDashboardTasks() as $task) {
            $items[] = $this->mapDocumentTaskToNotification($task);
        }

        if ((int) ($summary['unconfirmed_cast_over_7days'] ?? 0) > 0) {
            $n = (int) $summary['unconfirmed_cast_over_7days'];
            $items[] = [
                'title' => 'キャストの入金確認が7日以上未完了の案件が' . $n . '件あります',
                'time_label' => '要フォロー',
                'icon' => 'fa-triangle-exclamation',
                'class' => 'is-danger',
                'url' => route('admin.deposits.index'),
                'sort' => 920,
            ];
        }

        foreach ($this->buildInquiryNotifications() as $row) {
            $items[] = $row;
        }

        usort($items, fn ($a, $b) => ($b['sort'] ?? 0) <=> ($a['sort'] ?? 0));

        return array_map(fn (array $row) => [
            'title' => $row['title'],
            'time_label' => $row['time_label'],
            'icon' => $row['icon'],
            'class' => $row['class'],
            'url' => $row['url'],
        ], $items);
    }

    private function getPendingInquiryCount(): int
    {
        if (!Schema::hasTable('inquiries')) {
            return 0;
        }

        return (int) DB::table('inquiries')
            ->where('status', '未対応')
            ->count();
    }

    /**
     * @return array<int, array{title: string, time_label: string, icon: string, class: string, url: string, sort: int}>
     */
    private function buildInquiryNotifications(): array
    {
        if (!Schema::hasTable('inquiries')) {
            return [];
        }

        return DB::table('inquiries')
            ->where('status', '未対応')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(function ($row) {
                $created = Carbon::parse($row->created_at ?? now());
                $fromName = trim((string) ($row->from_name ?? $row->name ?? $row->user_name ?? ''));
                $subject = trim((string) ($row->subject ?? $row->title ?? ''));

                return [
                    'title' => '[問合せ] ' . $fromName . ' — ' . $subject,
                    'time_label' => $created->diffForHumans(),
                    'icon' => 'fa-comments',
                    'class' => 'is-warning',
                    'url' => route('admin.inquiries.index'),
                    'sort' => 400,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array{title: string, time_label: string, icon: string, class: string, url: string, sort: int}
     */
    private function mapBillingTaskToNotification(array $task): array
    {
        $id = (int) ($task['id'] ?? 0);
        $shop = trim((string) ($task['shop_name'] ?? ''));
        $cast = trim((string) ($task['cast_name'] ?? ''));
        $label = $shop !== '' ? $shop : ($cast !== '' ? $cast : '案件');
        $title = trim((string) ($task['task_title'] ?? '対応')) . '：' . $label;

        $dueRaw = $task['task_due_date'] ?? null;
        $due = $dueRaw ? Carbon::parse($dueRaw) : null;
        $now = Carbon::now();

        $class = 'is-gold';
        $sort = 300;
        $timeLabel = '期限未設定';

        if ($due) {
            $timeLabel = '期限 ' . $due->format('n/j H:i') . '（' . $due->diffForHumans() . '）';

            if ($due->lt($now)) {
                $class = 'is-danger';
                $sort = 900;
            } elseif ($due->lte($now->copy()->addDays(3))) {
                $class = 'is-warning';
                $sort = 700;
            }
        }

        return [
            'title' => $title,
            'time_label' => $timeLabel,
            'icon' => $this->billingTaskIcon((int) ($task['status_code'] ?? 0)),
            'class' => $class,
            'url' => $task['task_url'] ?? route('admin.deposits.index') . ($id > 0 ? '#deposit-' . $id : ''),
            'sort' => $sort,
        ];
    }

    private function billingTaskIcon(int $statusCode): string
    {
        return match ($statusCode) {
            BillingManagementService::STATUS_CAST_REQUESTED => 'fa-inbox',
            BillingManagementService::STATUS_SHOP_APPROVED => 'fa-file-invoice',
            BillingManagementService::STATUS_SHOP_PAYMENT_REPORTED => 'fa-money-bill-wave',
            BillingManagementService::STATUS_SHOP_PAYMENT_CONFIRMED => 'fa-money-bill-wave',
            default => 'fa-triangle-exclamation',
        };
    }

    /**
     * @param array<string, mixed> $task
     * @return array{title: string, time_label: string, icon: string, class: string, url: string, sort: int}
     */
    private function mapDocumentTaskToNotification(array $task): array
    {
        $target = (string) ($task['target'] ?? '');
        $category = (string) ($task['category'] ?? '');
        $title = '[' . $category . '] ' . $target . ' — ' . (string) ($task['status'] ?? '');

        $urgency = (string) ($task['urgency'] ?? 'normal');
        $class = match ($urgency) {
            'critical' => 'is-danger',
            'high' => 'is-warning',
            default => 'is-gold',
        };
        $sort = match ($urgency) {
            'critical' => 850,
            'high' => 650,
            default => 350,
        };

        return [
            'title' => $title,
            'time_label' => (string) ($task['date'] ?? '-'),
            'icon' => ($task['cat_id'] ?? '') === 'kyc' ? 'fa-id-card' : 'fa-file',
            'class' => $class,
            'url' => (string) ($task['url'] ?? route('admin.verification.index')),
            'sort' => $sort,
        ];
    }
}
