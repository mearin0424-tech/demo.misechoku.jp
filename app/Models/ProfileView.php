<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * profile_views テーブルのモデル（プロフィール閲覧ログ）。
 *
 * - viewer_type / target_type: 'cast' / 'shop'
 * - 全閲覧を1行ずつ記録し、COUNT(*) を「プロフィールが閲覧された回数」として表示する
 */
class ProfileView extends Model
{
    public $timestamps = false;

    protected $table = 'profile_views';

    protected $fillable = [
        'viewer_type',
        'viewer_id',
        'target_type',
        'target_id',
        'created_at',
    ];

    public const TYPE_CAST = 'cast';
    public const TYPE_SHOP = 'shop';
}
