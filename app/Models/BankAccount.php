<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    protected $table = 'bank_accounts';

    protected $fillable = [
        'member_id',
        'bank_name',
        'branch_name',
        'account_type',
        'account_number',
        'account_holder_name',
        'account_name',
    ];

    public function cast(): BelongsTo
    {
        return $this->belongsTo(Cast::class, 'member_id', 'id');
    }
}
