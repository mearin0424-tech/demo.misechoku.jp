<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CastProvider extends Model
{
    protected $table = 'cast_providers';

    protected $fillable = [
        'cast_id',
        'provider',
        'provider_id',
    ];

    public function cast(): BelongsTo
    {
        return $this->belongsTo(Cast::class, 'cast_id', 'id');
    }
}
