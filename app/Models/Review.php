<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $table = 'reviews';

    public $timestamps = false;

    protected $fillable = [
        'cast_id',
        'shop_id',
        'contents',
        'eva',
        'is_anonymous',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function cast(): BelongsTo
    {
        return $this->belongsTo(Cast::class, 'cast_id', 'id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ReviewDetail::class, 'review_id', 'id');
    }
}
