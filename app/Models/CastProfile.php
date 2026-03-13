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
        'main_image_path',
        'birthday',
        'gender',
        'zip',
        'pref',
        'city',
        'addr1',
        'addr2',
        'addr3',
        'tel',
        'height',
        'weight',
        'bust',
        'waist',
        'hip',
        'shift',
        'profession',
        'exp',
        'years_exp',
        'where_work',
        'pr',
        'charm_point',
        'memo',
        'personality_type',
        'ng_reason',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function cast(): BelongsTo
    {
        return $this->belongsTo(Cast::class, 'cast_id', 'id');
    }
}
