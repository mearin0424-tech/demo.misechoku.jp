<?php

namespace App\Console\Commands;

use App\Services\NotificationPreferenceService;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendUnreadTalkReminders extends Command
{
    protected $signature = 'reminders:send-unread-talk {--dry-run : 実送信せず対象のみ表示}';

    protected $description = '未読トークのプッシュリマインダーを送信する';

    public function handle(
        NotificationPreferenceService $prefs,
        PushNotificationService $push
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $now = Carbon::now();
        $count = 0;

        $rows = DB::table('messages')
            ->where('is_read', false)
            ->select('cast_id', 'shop_id', 'sender_type', DB::raw('MIN(created_at) as oldest_unread_at'))
            ->groupBy('cast_id', 'shop_id', 'sender_type')
            ->get();

        foreach ($rows as $row) {
            $oldest = Carbon::parse($row->oldest_unread_at);
            $minutes = $oldest->diffInMinutes($now, false);
            if (!$this->shouldSendReminderAt($minutes)) {
                continue;
            }

            if ((int) $row->sender_type === 2) {
                // 店舗送信 = キャスト側の未読
                $castId = (string) $row->cast_id;
                $castPref = $prefs->get('cast', $castId);
                if (!($castPref['push_enabled'] ?? true)) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("[DRY] cast {$castId} unread reminder");
                } else {
                    $push->sendToUser(
                        'cast',
                        $castId,
                        '未読メッセージのリマインド',
                        '店舗から未読メッセージがあります。内容を確認してください。',
                        url('/cast/talk/room/' . $row->shop_id)
                    );
                }
                $count++;
                continue;
            }

            // キャスト送信 = 店舗側の未読
            $managerIds = DB::table('shop_managers')
                ->where('shop_id', (string) $row->shop_id)
                ->pluck('id');

            foreach ($managerIds as $managerId) {
                $managerId = (string) $managerId;
                $shopPref = $prefs->get('shop_manager', $managerId);
                if (!($shopPref['push_enabled'] ?? true)) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("[DRY] shop_manager {$managerId} unread reminder");
                } else {
                    $push->sendToUser(
                        'shop_manager',
                        $managerId,
                        '未読メッセージのリマインド',
                        'キャストから未読メッセージがあります。ご確認ください。',
                        url('/shop/talk/room/' . $row->cast_id)
                    );
                }
                $count++;
            }
        }

        $this->info("処理件数: {$count}");
        return self::SUCCESS;
    }

    private function shouldSendReminderAt(int $minutes): bool
    {
        // 同一未読に対する連打を避けるため、30分後と3時間後の狭い窓だけ送る
        return ($minutes >= 30 && $minutes < 40) || ($minutes >= 180 && $minutes < 190);
    }
}

