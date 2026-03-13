<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminBankAccount extends Model
{
    protected $table = 'admin_bank_accounts';

    protected $fillable = [
        'bank_name',
        'branch_name',
        'account_type',
        'account_number',
        'account_holder_name',
        'account_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
