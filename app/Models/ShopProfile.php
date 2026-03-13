<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopProfile extends Model
{
    protected $table = 'shop_profiles';

    protected $fillable = [
        'shop_id',
        'shop_name',
        'main_image_path',
        'opened_on',
        'zip',
        'pref',
        'city',
        'addr2',
        'addr3',
        'tel',
        'station1',
        'station2',
        'station3',
        'station4',
        'station5',
        'catch',
        'overview',
        'message',
        'memo',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'opened_on' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }
}
