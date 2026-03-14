<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CastIdentityDocument extends Model
{
    public const STATUS_PENDING = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_REJECTED = 3;

    protected $table = 'cast_identity_documents';

    protected $fillable = [
        'cast_id',
        'type',
        'image_path_front',
        'image_path_back',
        'status',
        'ng_reason',
        'expired_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'expired_at' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function cast(): BelongsTo
    {
        return $this->belongsTo(Cast::class, 'cast_id', 'id');
    }
}
