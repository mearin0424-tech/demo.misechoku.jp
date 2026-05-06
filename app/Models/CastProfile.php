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
        'shift',
        'profession',
        'exp',
        'years_exp',
        'where_work',
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
        ];
    }

    public function cast(): BelongsTo
    {
        return $this->belongsTo(Cast::class, 'cast_id', 'id');
    }
}
