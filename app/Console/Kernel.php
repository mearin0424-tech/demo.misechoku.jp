<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // LINEの重要通知は日次ダイジェストで1回送信
        $schedule->command('line:send-daily-digest')->dailyAt('09:00');

        // 面接日・支払期限のリマインダー（PWA/LINE）
        $schedule->command('reminders:send-interview-deadline')->hourly();
        // 未読トークリマインダー（30分後/3時間後）
        $schedule->command('reminders:send-unread-talk')->everyTenMinutes();

        // 振込後のキャスト入金確認督促
        $schedule->command('billing:remind-cast-transfer-confirmation')->hourly();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
