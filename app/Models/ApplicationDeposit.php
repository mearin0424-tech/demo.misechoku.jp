<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationDeposit extends Model
{
    protected $table = 'application_deposits';

    protected $fillable = [
        'shop_job_application_id',
        'status',
        'is_read',
        'invoice_number',
        'bonus_amount',
        'system_fee_amount',
        'invoice_amount',
        'cast_transfer_amount',
        'invoice_issued_at',
        'invoice_due_date',
        'invoice_sent_at',
        'shop_payment_reported_at',
        'shop_payment_reported_amount',
        'shop_payment_reference',
        'shop_payment_confirmed_at',
        'cast_transferred_at',
        'cast_transfer_reference',
        'cast_transfer_note',
        'completed_at',
        'invoice_display_shop_name',
        'invoice_display_shop_address',
        'invoice_display_shop_email',
        'invoice_display_cast_name',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'invoice_issued_at' => 'datetime',
            'invoice_due_date' => 'date',
            'invoice_sent_at' => 'datetime',
            'shop_payment_reported_at' => 'datetime',
            'shop_payment_confirmed_at' => 'datetime',
            'cast_transferred_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ShopJobApplication::class, 'shop_job_application_id', 'id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ApplicationDepositHistory::class, 'application_deposit_id', 'id');
    }
}
