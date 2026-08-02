<?php

namespace App\Enums;

/**
 * User report (harassment / spam / etc.) triage status.
 * Values match UserReport::STATUS_* legacy constants.
 */
enum UserReportStatus: int
{
    case Pending   = 0;
    case InReview  = 1;
    case Resolved  = 2;
    case Dismissed = 3;

    public function label(): string
    {
        return match ($this) {
            self::Pending   => '未対応',
            self::InReview  => '対応中',
            self::Resolved  => '完了',
            self::Dismissed => '却下',
        };
    }
}
