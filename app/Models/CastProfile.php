<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CastProfile extends Model
{
    protected $table = 'cast_profiles';

    protected $fillable = [
        'cast_id',
        'nickname',
        'name',
        'name_kana',
        'birthday',
        'zip',
        'pref',
        'city',
        'addr',
        'building',
        'tel',
        'height',
        'weight',
        'bust',
        'waist',
        'hip',
        'work_time',
        'profession',
        'exp',
        'years_exp',
        'work_where',
        'pr',
        'personality_type',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            // PII 暗号化（AES-256-CBC, 復号失敗時は null を返す耐障害キャスト）
            'name'      => \App\Casts\SafeEncrypted::class,
            'name_kana' => \App\Casts\SafeEncrypted::class,
            'zip'       => \App\Casts\SafeEncrypted::class,
            'addr1'     => \App\Casts\SafeEncrypted::class,
            'addr2'     => \App\Casts\SafeEncrypted::class,
            'addr3'     => \App\Casts\SafeEncrypted::class,
            'addr'      => \App\Casts\SafeEncrypted::class,
            'building'  => \App\Casts\SafeEncrypted::class,
            'tel'       => \App\Casts\SafeEncrypted::class,
            'memo'      => \App\Casts\SafeEncrypted::class,
            'ng_reason' => \App\Casts\SafeEncrypted::class,
        ];
    }

    public function cast(): BelongsTo
    {
        return $this->belongsTo(Cast::class, 'cast_id', 'id');
    }
}
