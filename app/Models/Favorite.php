<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * favorites テーブルのモデル。
 *
 * - action_type: 'KEEP'（キープ）
 * - sender_type: 'cast'（キャスト発信） / 'shop'（店舗発信）
 */
class Favorite extends Model
{
    public $timestamps = false;

    protected $table = 'favorites';

    protected $fillable = [
        'cast_id',
        'shop_id',
        'action_type',
        'sender_type',
        'created_at',
    ];

    public const ACTION_KEEP = 'KEEP';

    public const SENDER_CAST = 'cast';
    public const SENDER_SHOP = 'shop';

    public const ACTION_TYPES = [self::ACTION_KEEP];
    public const SENDER_TYPES = [self::SENDER_CAST, self::SENDER_SHOP];
}
