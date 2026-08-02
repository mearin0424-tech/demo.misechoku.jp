<?php

namespace App\Enums;

/**
 * Shop manager role. One owner per shop (enforced in StaffController::store),
 * unlimited staff members.
 *
 * Introduced as an enum so callers can rely on type-safe constants instead of
 * bare int literals. The int values match the legacy ShopManager::ROLE_* constants
 * to keep DB compatibility.
 */
enum ShopManagerRole: int
{
    case Owner = 1;
    case Staff = 2;

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'オーナー',
            self::Staff => 'スタッフ',
        };
    }

    public function canManageStaff(): bool
    {
        return $this === self::Owner;
    }
}
