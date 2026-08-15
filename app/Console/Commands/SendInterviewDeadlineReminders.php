<?php

namespace App\Console\Commands;

use App\Services\NotificationPreferenceService;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendInterviewDeadlineReminders extends Command
{
    protected $signature = 'reminders:send-interview-deadline {--dry-run : 実送信せず対象のみ表示}';

    protected $description = '面接日・期限のPWA/LINEリマインダーを送信する';

    public function handle(
        NotificationPreferenceService $prefs,
        PushNotificationService $push
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $now = Carbon::now();
        $count = 0;

        $interviews = DB::table('shop_job_applications')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->join('shop_managers', 'shop_jobs.shop_id', '=', 'shop_managers.shop_id')
            ->where('shop_job_applications.status', 3)
            ->whereNotNull('shop_job_applications.result_date')
            ->select(
                'shop_job_applications.id as application_id',
                'shop_job_applications.cast_id',
                'shop_job_applications.result_date',
                'shop_managers.id as manager_id'
            )
            ->get();

        foreach ($interviews as $row) {
            $interviewAt = Carbon::parse($row->result_date);
            $hours = $now->diffInHours($interviewAt, false);
            $slot = null;
            if ($hours >= 23 && $hours <= 25) {
                $slot = '面接24時間前';
            } elseif ($hours >= 2 && $hours <= 4) {
                $slot = '面接3時間前';
            }
            if (!$slot) {
                continue;
            }

            $text = "【ミセチョク】{$slot}リマインド\n面接予定: " . $interviewAt->format('Y/m/d H:i');

            $castPref = $prefs->get('cast', (string) $row->cast_id);
            if ($castPref['interview_reminder_enabled']) {
                if ($dryRun) {
                    $this->line("[DRY] cast {$row->cast_id} {$slot}");
                } else {
                    if ($castPref['push_enabled']) {
                        $push->sendToUser('cast', (string) $row->cast_id, '面接リマインド', $text, url('/cast/interaction'));
                    }
                }
                $count++;
            }

            $shopPref = $prefs->get('shop_manager', (string) $row->manager_id);
            if ($shopPref['interview_reminder_enabled']) {
                if ($dryRun) {
                    $this->line("[DRY] shop {$row->manager_id} {$slot}");
                } else {
                    if ($shopPref['push_enabled']) {
                        $push->sendToUser('shop_manager', (string) $row->manager_id, '面接リマインド', $text, url('/shop/interaction'));
                    }
                }
                $count++;
            }
        }

        $dueRows = DB::table('application_deposits')
            ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->join('shop_managers', 'shop_jobs.shop_id', '=', 'shop_managers.shop_id')
            ->whereIn('application_deposits.status', [3, 4])
            ->whereNotNull('application_deposits.invoice_due_date')
            ->select(
                'application_deposits.id as deposit_id',
                'application_deposits.invoice_due_date',
                'application_deposits.invoice_number',
                'shop_managers.id as manager_id'
            )
            ->get();

        foreach ($dueRows as $row) {
            $due = Carbon::parse($row->invoice_due_date)->startOfDay();
            $days = $now->copy()->startOfDay()->diffInDays($due, false);
            $slot = match (true) {
                $days === 1 => '支払期限の前日',
                $days === 0 => '支払期限の当日',
                $days === -3 => '支払期限を3日超過',
                default => null,
            };
            if (!$slot) {
                continue;
            }

            $text = "【ミセチョク】{$slot}です\n請求番号: " . ($row->invoice_number ?: '#'.$row->deposit_id) . "\n期限: " . $due->format('Y/m/d');
            $shopPref = $prefs->get('shop_manager', (string) $row->manager_id);
            if (!$shopPref['deadline_reminder_enabled']) {
                continue;
            }

            if ($dryRun) {
                $this->line("[DRY] shop {$row->manager_id} due {$slot} deposit={$row->deposit_id}");
            } else {
                if ($shopPref['push_enabled']) {
                    $push->sendToUser('shop_manager', (string) $row->manager_id, '支払期限リマインド', $text, route('shop.mypage.management'));
                }
            }
            $count++;
        }

        $this->info("処理件数: {$count}");

        return self::SUCCESS;
    }
}
