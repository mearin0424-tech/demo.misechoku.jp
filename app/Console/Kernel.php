<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // 面接日・支払期限のリマインダー（PWA/LINE）
        $schedule->command('reminders:send-interview-deadline')->hourly();

        // 振込後のキャスト入金確認督促
        $schedule->command('billing:remind-cast-transfer-confirmation')->hourly();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
