<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationPreferenceService
{
    public function get(string $userType, string $userId): array
    {
        if (!Schema::hasTable('notification_preferences')) {
            return $this->defaults();
        }

        $row = DB::table('notification_preferences')
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->first();

        if (!$row) {
            return $this->defaults();
        }

        return [
            'push_enabled' => (bool) $row->push_enabled,
            'line_enabled' => (bool) $row->line_enabled,
            'interview_reminder_enabled' => (bool) $row->interview_reminder_enabled,
            'deadline_reminder_enabled' => (bool) $row->deadline_reminder_enabled,
        ];
    }

    public function save(string $userType, string $userId, array $prefs): void
    {
        if (!Schema::hasTable('notification_preferences')) {
            return;
        }

        DB::table('notification_preferences')->updateOrInsert(
            ['user_type' => $userType, 'user_id' => $userId],
            [
                'push_enabled' => (bool) ($prefs['push_enabled'] ?? true),
                'line_enabled' => (bool) ($prefs['line_enabled'] ?? true),
                'interview_reminder_enabled' => (bool) ($prefs['interview_reminder_enabled'] ?? true),
                'deadline_reminder_enabled' => (bool) ($prefs['deadline_reminder_enabled'] ?? true),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function defaults(): array
    {
        return [
            'push_enabled' => true,
            'line_enabled' => true,
            'interview_reminder_enabled' => true,
            'deadline_reminder_enabled' => true,
        ];
    }
}
