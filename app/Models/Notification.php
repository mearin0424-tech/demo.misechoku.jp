<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * ユーザー宛おしらせ（インボックス）。
 *
 * - user_type: 'cast' / 'shop_manager' / 'admin'
 * - user_id:   各ユーザーの主キー（文字列ID）
 * - type:      機能キー（例: talk.message_received）
 * - read_at:   既読時刻（NULL=未読）
 */
class Notification extends Model
{
    public const USER_CAST  = 'cast';
    public const USER_SHOP  = 'shop_manager';
    public const USER_ADMIN = 'admin';

    protected $table = 'notifications';

    protected $fillable = [
        'user_type',
        'user_id',
        'type',
        'title',
        'body',
        'url',
        'payload',
        'read_at',
        'dispatched_push_at',
        'dispatched_line_at',
    ];

    protected $casts = [
        'payload'            => 'array',
        'read_at'            => 'datetime',
        'dispatched_push_at' => 'datetime',
        'dispatched_line_at' => 'datetime',
    ];

    public function scopeForUser(Builder $q, string $userType, string $userId): Builder
    {
        return $q->where('user_type', $userType)->where('user_id', $userId);
    }

    public function scopeUnread(Builder $q): Builder
    {
        return $q->whereNull('read_at');
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markRead(): bool
    {
        if ($this->read_at !== null) {
            return false;
        }
        $this->forceFill(['read_at' => now()])->save();
        return true;
    }
}
