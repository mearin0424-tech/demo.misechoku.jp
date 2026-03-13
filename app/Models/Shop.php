<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use SoftDeletes;

    protected $table = 'shops';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'email',
        'status',
        'license_status',
        'business_license_status',
        'entertainment_license_status',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(ShopProfile::class, 'shop_id', 'id');
    }

    public function managers(): HasMany
    {
        return $this->hasMany(ShopManager::class, 'shop_id', 'id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(ShopJob::class, 'shop_id', 'id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ShopImage::class, 'shop_id', 'id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'shop_id', 'id');
    }

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccountShop::class, 'shop_id', 'id');
    }
}
