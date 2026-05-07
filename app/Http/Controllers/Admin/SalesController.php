<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 売上管理（運営の収益サイドの画面）。
 *
 * - ダッシュボードは「今この瞬間のオペレーション状況と要対応タスク」を表示する
 * - 売上管理（このページ）は「運営がいくら収益を上げているか・推移はどうか」を表示する
 *
 * 主指標:
 *   - 仲介料収益（system_fee_amount の合計） … 運営の主収益
 *   - 取引総額（invoice_amount の合計）       … プラットフォーム経由の総流通額（GMV）
 *   - サブスクリプション収益                  … 課金連携が無いため現状は「未連携」を明示
 */
class SalesController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $period = $request->query('period', 'last_3m');
        if (!in_array($period, ['this_month', 'last_month', 'last_3m', 'last_6m', 'last_12m'], true)) {
            $period = 'last_3m';
        }

        [$periodStart, $periodEnd, $periodLabel, $monthsBack] = $this->resolvePeriod($period, $now);
        [$prevStart, $prevEnd, $prevLabel] = $this->resolvePreviousPeriod($period, $now);

        $hasDeposits = Schema::hasTable('application_deposits');
        $hasSystemFee = $hasDeposits && Schema::hasColumn('application_deposits', 'system_fee_amount');
        $hasInvoiceAmount = $hasDeposits && Schema::hasColumn('application_deposits', 'invoice_amount');
        $hasCastTransfer = $hasDeposits && Schema::hasColumn('application_deposits', 'cast_transfer_amount');

        // ---- KPI: 期間合計
        $current = $this->aggregateRevenue($periodStart, $periodEnd, $hasDeposits, $hasSystemFee, $hasInvoiceAmount, $hasCastTransfer);
        $previous = $this->aggregateRevenue($prevStart, $prevEnd, $hasDeposits, $hasSystemFee, $hasInvoiceAmount, $hasCastTransfer);

        $kpis = [
            [
                'id' => 'commission',
                'title' => '仲介料収益',
                'subtitle' => $periodLabel,
                'value' => number_format($current['commission']),
                'unit' => '円',
                'trend_label' => $this->signedDelta($current['commission'] - $previous['commission']),
                'trend_caption' => $prevLabel . '比',
                'is_up' => ($current['commission'] - $previous['commission']) >= 0,
                'description' => '運営が請求書発行で受け取る仲介料の合計です。',
                'icon' => 'fa-yen-sign',
                'is_primary' => true,
            ],
            [
                'id' => 'gmv',
                'title' => '取引総額（GMV）',
                'subtitle' => $periodLabel,
                'value' => number_format($current['gmv']),
                'unit' => '円',
                'trend_label' => $this->signedDelta($current['gmv'] - $previous['gmv']),
                'trend_caption' => $prevLabel . '比',
                'is_up' => ($current['gmv'] - $previous['gmv']) >= 0,
                'description' => 'プラットフォーム経由で発生した請求総額（運営収益＋キャストへの振込原資）。',
                'icon' => 'fa-coins',
            ],
            [
                'id' => 'count',
                'title' => '完了取引件数',
                'subtitle' => $periodLabel,
                'value' => number_format($current['count']),
                'unit' => '件',
                'trend_label' => $this->signedDelta($current['count'] - $previous['count']),
                'trend_caption' => $prevLabel . '比',
                'is_up' => ($current['count'] - $previous['count']) >= 0,
                'description' => '請求書が発行された取引（採用ボーナスの請求が走った件数）。',
                'icon' => 'fa-receipt',
            ],
            [
                'id' => 'avg',
                'title' => '平均仲介料単価',
                'subtitle' => $periodLabel,
                'value' => number_format($current['count'] > 0 ? (int) round($current['commission'] / $current['count']) : 0),
                'unit' => '円/件',
                'trend_label' => '—',
                'trend_caption' => '1件あたりの仲介料',
                'is_up' => true,
                'description' => '完了取引1件あたりに運営が得る平均仲介料。',
                'icon' => 'fa-chart-pie',
            ],
        ];

        // ---- 月別推移（過去12ヶ月固定。チャートで可視化）
        $monthlyChart = $this->buildMonthlyRevenueSeries(
            $now,
            12,
            $hasDeposits,
            $hasSystemFee,
            $hasInvoiceAmount,
            $hasCastTransfer
        );

        // ---- Top 10 店舗 / キャスト （仲介料貢献額）
        $topShops = $this->topContributors('shop', $periodStart, $periodEnd, $hasDeposits, $hasSystemFee);
        $topCasts = $this->topContributors('cast', $periodStart, $periodEnd, $hasDeposits, $hasSystemFee);

        return view('admin.sales.index', [
            'period' => $period,
            'periodLabel' => $periodLabel,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'periodOptions' => [
                'this_month' => '今月',
                'last_month' => '先月',
                'last_3m' => '直近3ヶ月',
                'last_6m' => '直近6ヶ月',
                'last_12m' => '直近12ヶ月',
            ],
            'kpis' => $kpis,
            'monthlyChart' => $monthlyChart,
            'topShops' => $topShops,
            'topCasts' => $topCasts,
            'subscriptionAvailable' => false, // サブスク課金が連携されたら true
        ]);
    }

    private function aggregateRevenue(Carbon $from, Carbon $to, bool $hasDeposits, bool $hasSystemFee, bool $hasInvoiceAmount, bool $hasCastTransfer): array
    {
        if (!$hasDeposits) {
            return ['commission' => 0, 'gmv' => 0, 'count' => 0];
        }
        $base = DB::table('application_deposits')->whereBetween('invoice_issued_at', [$from, $to])
            ->where(function ($q) {
                $q->whereNotNull('invoice_issued_at');
            });
        $count = (int) (clone $base)->count();
        $gmv = $hasInvoiceAmount ? (int) (clone $base)->sum('invoice_amount') : 0;

        if ($hasSystemFee) {
            $commission = (int) (clone $base)->sum('system_fee_amount');
        } elseif ($hasInvoiceAmount && $hasCastTransfer) {
            // フォールバック: 請求額 - キャスト振込額 = 仲介料
            $commission = (int) (clone $base)
                ->select(DB::raw('COALESCE(SUM(invoice_amount), 0) - COALESCE(SUM(cast_transfer_amount), 0) AS c'))
                ->value('c');
        } else {
            $commission = 0;
        }

        return ['commission' => $commission, 'gmv' => $gmv, 'count' => $count];
    }

    private function buildMonthlyRevenueSeries(Carbon $now, int $months, bool $hasDeposits, bool $hasSystemFee, bool $hasInvoiceAmount, bool $hasCastTransfer): array
    {
        $rows = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $agg = $this->aggregateRevenue($monthStart, $monthEnd, $hasDeposits, $hasSystemFee, $hasInvoiceAmount, $hasCastTransfer);

            $rows[] = [
                'month' => $monthStart->format('n月'),
                'year_month' => $monthStart->format('Y-m'),
                'commission' => $agg['commission'],
                'gmv' => $agg['gmv'],
                'count' => $agg['count'],
            ];
        }
        return $rows;
    }

    /**
     * @return array<int, array{id:string, name:string, commission:int, count:int}>
     */
    private function topContributors(string $entity, Carbon $from, Carbon $to, bool $hasDeposits, bool $hasSystemFee): array
    {
        if (!$hasDeposits) {
            return [];
        }
        if (!Schema::hasTable('shop_job_applications') || !Schema::hasTable('shop_jobs')) {
            return [];
        }

        $feeExpression = $hasSystemFee
            ? 'COALESCE(SUM(application_deposits.system_fee_amount), 0)'
            : 'COALESCE(SUM(application_deposits.invoice_amount), 0) - COALESCE(SUM(application_deposits.cast_transfer_amount), 0)';

        $query = DB::table('application_deposits')
            ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->whereBetween('application_deposits.invoice_issued_at', [$from, $to])
            ->whereNotNull('application_deposits.invoice_issued_at');

        if ($entity === 'shop') {
            $query->leftJoin('shop_profiles', 'shop_jobs.shop_id', '=', 'shop_profiles.shop_id')
                ->select(
                    'shop_jobs.shop_id as id',
                    DB::raw("COALESCE(shop_profiles.shop_name, '未設定') as name"),
                    DB::raw($feeExpression . ' as commission'),
                    DB::raw('COUNT(application_deposits.id) as count')
                )
                ->groupBy('shop_jobs.shop_id', 'shop_profiles.shop_name');
        } else {
            $query->leftJoin('cast_profiles', 'shop_job_applications.cast_id', '=', 'cast_profiles.cast_id')
                ->select(
                    'shop_job_applications.cast_id as id',
                    DB::raw("COALESCE(cast_profiles.nickname, cast_profiles.name, '未設定') as name"),
                    DB::raw($feeExpression . ' as commission'),
                    DB::raw('COUNT(application_deposits.id) as count')
                )
                ->groupBy('shop_job_applications.cast_id', 'cast_profiles.nickname', 'cast_profiles.name');
        }

        return $query->orderByDesc('commission')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->id,
                'name' => (string) $r->name,
                'commission' => (int) $r->commission,
                'count' => (int) $r->count,
            ])
            ->all();
    }

    /**
     * @return array{0:Carbon, 1:Carbon, 2:string, 3:int}  [start, end, label, monthsBack]
     */
    private function resolvePeriod(string $period, Carbon $now): array
    {
        return match ($period) {
            'this_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                '今月（' . $now->format('Y年n月') . '）',
                1,
            ],
            'last_month' => [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
                '先月（' . $now->copy()->subMonthNoOverflow()->format('Y年n月') . '）',
                1,
            ],
            'last_3m' => [
                $now->copy()->subMonthsNoOverflow(2)->startOfMonth(),
                $now->copy()->endOfMonth(),
                '直近3ヶ月',
                3,
            ],
            'last_6m' => [
                $now->copy()->subMonthsNoOverflow(5)->startOfMonth(),
                $now->copy()->endOfMonth(),
                '直近6ヶ月',
                6,
            ],
            default => [
                $now->copy()->subMonthsNoOverflow(11)->startOfMonth(),
                $now->copy()->endOfMonth(),
                '直近12ヶ月',
                12,
            ],
        };
    }

    /**
     * @return array{0:Carbon, 1:Carbon, 2:string}
     */
    private function resolvePreviousPeriod(string $period, Carbon $now): array
    {
        return match ($period) {
            'this_month' => [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
                '前月',
            ],
            'last_month' => [
                $now->copy()->subMonthsNoOverflow(2)->startOfMonth(),
                $now->copy()->subMonthsNoOverflow(2)->endOfMonth(),
                '前月',
            ],
            'last_3m' => [
                $now->copy()->subMonthsNoOverflow(5)->startOfMonth(),
                $now->copy()->subMonthsNoOverflow(3)->endOfMonth(),
                '前期間',
            ],
            'last_6m' => [
                $now->copy()->subMonthsNoOverflow(11)->startOfMonth(),
                $now->copy()->subMonthsNoOverflow(6)->endOfMonth(),
                '前期間',
            ],
            default => [
                $now->copy()->subMonthsNoOverflow(23)->startOfMonth(),
                $now->copy()->subMonthsNoOverflow(12)->endOfMonth(),
                '前年同期',
            ],
        };
    }

    private function signedDelta(int $delta): string
    {
        if ($delta > 0) return '+' . number_format($delta);
        if ($delta < 0) return '−' . number_format(abs($delta));
        return '±0';
    }
}
