<?php

namespace App\Enums;

/**
 * Cast identity-document (ID card upload) workflow status.
 * Values match CastIdentityDocument::STATUS_* legacy constants.
 *
 * Flow:
 *   Draft (uploaded, not yet submitted)
 *     -> Pending (user pressed "submit to admin", awaiting review)
 *     -> Approved | Rejected
 */
enum CastIdentityDocumentStatus: int
{
    case Draft    = 0;
    case Pending  = 1;
    case Approved = 2;
    case Rejected = 3;

    public function label(): string
    {
        return match ($this) {
            self::Draft    => 'アップロード済み（未提出）',
            self::Pending  => '審査中',
            self::Approved => '承認済み',
            self::Rejected => '差戻し',
        };
    }

    /** Whether the document can be submitted for admin review from this state. */
    public function isSubmittable(): bool
    {
        return $this === self::Draft;
    }

    /** Whether admin approval action makes sense from this state. */
    public function isReviewable(): bool
    {
        return $this === self::Pending;
    }
}
