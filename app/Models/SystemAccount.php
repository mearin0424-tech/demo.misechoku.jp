<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SystemAccount extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';

    protected $table = 'system_accounts';

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'is_active',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'holder_id', 'id')
            ->where('holder_type', BankAccount::HOLDER_SYSTEM_ACCOUNT);
    }
}
