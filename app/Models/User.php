<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public const ROLE_ADMIN = 10;

    protected $table = 'users';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role_type',
    ];

    protected $hidden = [
        'password',
    ];

    public function isAdmin(): bool
    {
        return (int) $this->role_type === self::ROLE_ADMIN;
    }
}
