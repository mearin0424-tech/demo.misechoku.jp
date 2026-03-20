<?php

namespace App\Console\Commands;

use App\Services\BillingManagementService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 振込済みでキャストがまだ「入金確認済み」を押していない案件に対してリマインドを送る督促バッチ。
 * 仕様: 振込直後 / 翌日（24時間後） / 3日後 / 7日後（警告）... 以降ループ。
 * PWA/LINE 未実装のため、現状は対象件数のログ出力とダッシュボードでの赤ハイライトで対応。
 * 通知基盤実装後に、ここでメール・PWA・LINE を呼び出す想定。
 */
class RemindCastTransferConfirmation extends Command
{
    protected $signature = 'billing:remind-cast-transfer-confirmation {--dry-run : 送信せず対象のみ表示}';

    protected $description = '振込済み・キャスト未確認の案件にリマインド（督促）を送信する';

    public function handle(BillingManagementService $billing): int
    {
        $dryRun = $this->option('dry-run');

        $deposits = DB::table('application_deposits')
            ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->join('casts', 'shop_job_applications.cast_id', '=', 'casts.id')
            ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->where('application_deposits.status', BillingManagementService::STATUS_CAST_TRANSFERRED)
            ->whereNotNull('application_deposits.cast_transferred_at')
            ->whereNull('application_deposits.completed_at')
            ->select(
                'application_deposits.id',
                'application_deposits.cast_transferred_at',
                'shop_job_applications.cast_id',
                'cast_profiles.nickname',
                'casts.email'
            )
            ->get();

        $now = Carbon::now();
        $targets = [];
        foreach ($deposits as $d) {
            $transferredAt = Carbon::parse($d->cast_transferred_at);
            $hoursAgo = (int) $transferredAt->diffInHours($now); // 振込から経過した時間（正の数）
            $daysAgo = (int) floor($hoursAgo / 24);

            $slot = null;
            if ($hoursAgo <= 2) {
                $slot = '振込直後';
            } elseif ($hoursAgo >= 22 && $hoursAgo <= 26) {
                $slot = '翌日（24時間後）';
            } elseif ($hoursAgo >= 70 && $hoursAgo <= 74) {
                $slot = '3日後';
            } elseif ($hoursAgo >= 166 && $hoursAgo <= 170) {
                $slot = '7日後（警告）';
            } elseif ($daysAgo >= 14 && $daysAgo % 7 <= 1) {
                $slot = $daysAgo . '日後';
            }

            if ($slot !== null) {
                $targets[] = [
                    'deposit_id' => $d->id,
                    'cast_id' => $d->cast_id,
                    'cast_name' => $d->nickname ?: $d->cast_id,
                    'window' => $slot,
                    'transferred_at' => $transferredAt->format('Y-m-d H:i'),
                ];
            }
        }

        if (empty($targets)) {
            $this->info('リマインド対象は0件です。');
            return self::SUCCESS;
        }

        $this->info('リマインド対象: ' . count($targets) . ' 件');
        foreach ($targets as $t) {
            $this->line("  #{$t['deposit_id']} {$t['cast_name']} ({$t['cast_id']}) - {$t['window']} - 振込日時: {$t['transferred_at']}");
        }

        if (!$dryRun) {
            foreach ($targets as $t) {
                // TODO: PWA / LINE / メールでキャストに「入金確認のお願い」を送信
                Log::info('billing.remind_cast_transfer', [
                    'deposit_id' => $t['deposit_id'],
                    'cast_id' => $t['cast_id'],
                    'window' => $t['window'],
                ]);
            }
            $this->info('リマインド送信処理を記録しました（通知基盤実装後に実際の送信に差し替えてください）。');
        } else {
            $this->info('--dry-run のため送信は行いません。');
        }

        return self::SUCCESS;
    }
}
