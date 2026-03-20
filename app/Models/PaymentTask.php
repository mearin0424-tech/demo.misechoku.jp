<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTask extends Model
{
    public const STATUS_PENDING = 0;
    public const STATUS_READY = 1;
    public const STATUS_TRANSFERRING = 2;
    public const STATUS_PAID = 3;
    public const STATUS_INVALID = 4;

    protected $table = 'payment_tasks';

    protected $fillable = [
        'application_deposit_id',
        'status',
        'shop_received_amount',
        'platform_fee_amount',
        'bank_fee_amount',
        'payout_amount',
        'transferred_at',
        'completed_at',
        'evidence_file_path',
        'checklist_confirmed_account',
        'checklist_confirmed_amount',
        'operator_id',
        'refund_required',
    ];

    protected function casts(): array
    {
        return [
            'checklist_confirmed_account' => 'boolean',
            'checklist_confirmed_amount' => 'boolean',
            'refund_required' => 'boolean',
            'transferred_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function applicationDeposit(): BelongsTo
    {
        return $this->belongsTo(ApplicationDeposit::class, 'application_deposit_id', 'id');
    }

    public static function statusLabel(int $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => '待機',
            self::STATUS_READY => '支払準備中',
            self::STATUS_TRANSFERRING => '振込中',
            self::STATUS_PAID => '支払済',
            self::STATUS_INVALID => '無効',
            default => '不明',
        };
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_PAID || $this->status === self::STATUS_INVALID;
    }
}
