<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Manager extends Authenticatable
{
    protected $table = 'shop_managers';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'shop_id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
        ];
    }
}
