<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccountShop extends Model
{
    protected $table = 'bank_account_shops';

    protected $fillable = [
        'shop_id',
        'bank_name',
        'branch_name',
        'account_type',
        'account_number',
        'account_holder_name',
        'account_name',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }
}
