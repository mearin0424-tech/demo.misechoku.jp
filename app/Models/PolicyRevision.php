<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyRevision extends Model
{
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_LOCKED = 'locked';
    public const ACTION_UNLOCKED = 'unlocked';

    public $timestamps = false;

    protected $fillable = [
        'policy_document_id',
        'action',
        'summary',
        'snapshot',
        'updated_by_id',
        'updated_by_name',
        'created_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(PolicyDocument::class, 'policy_document_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => '新規作成',
            self::ACTION_UPDATED => '更新',
            self::ACTION_LOCKED => 'ロック',
            self::ACTION_UNLOCKED => 'ロック解除',
            default => $this->action,
        };
    }
}
