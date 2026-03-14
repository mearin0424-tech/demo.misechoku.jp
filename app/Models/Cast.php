<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cast extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'casts';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'email',
        'password',
        'status',
        'identity_status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(CastProfile::class, 'cast_id', 'id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CastImage::class, 'cast_id', 'id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ShopJobApplication::class, 'cast_id', 'id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'cast_id', 'id');
    }

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'holder_id', 'id')
            ->where('holder_type', BankAccount::HOLDER_CAST);
    }
}
