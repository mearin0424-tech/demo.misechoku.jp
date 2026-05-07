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
        'addr',
        'building',
        'addr2',
        'addr3',
        'tel',
        'open_time',
        'close_is_last',
        'close_time',
        'station1',
        'station2',
        'station3',
        'station4',
        'station5',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'opened_on' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            // PII 暗号化（連絡先・正確な住所・運営メモ）。pref/city などのエリア情報は検索に使うため平文。
            'zip'      => \App\Casts\SafeEncrypted::class,
            'addr'     => \App\Casts\SafeEncrypted::class,
            'addr2'    => \App\Casts\SafeEncrypted::class,
            'addr3'    => \App\Casts\SafeEncrypted::class,
            'building' => \App\Casts\SafeEncrypted::class,
            'tel'      => \App\Casts\SafeEncrypted::class,
            'memo'     => \App\Casts\SafeEncrypted::class,
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }
}
