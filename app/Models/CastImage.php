<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CastImage extends Model
{
    protected $table = 'cast_images';

    protected $fillable = [
        'cast_id',
        'image_path',
        'type',
        'front_and_back',
        'status',
        'is_main',
        'main_order',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
        ];
    }

    public function cast(): BelongsTo
    {
        return $this->belongsTo(Cast::class, 'cast_id', 'id');
    }
}
