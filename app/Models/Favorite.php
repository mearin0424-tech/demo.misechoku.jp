<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * favorites テーブルのモデル。
 *
 * - action_type: 'KEEP'（キープ） / 'LIKE'（いいね）
 * - sender_type: 'cast'（キャスト発信） / 'shop'（店舗発信）
 *   ※ LIKE は仕様上 sender_type='shop' のみ（キャスト→店舗 LIKE は無効化）。
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
    public const ACTION_LIKE = 'LIKE';

    public const SENDER_CAST = 'cast';
    public const SENDER_SHOP = 'shop';

    public const ACTION_TYPES = [self::ACTION_KEEP, self::ACTION_LIKE];
    public const SENDER_TYPES = [self::SENDER_CAST, self::SENDER_SHOP];
}
