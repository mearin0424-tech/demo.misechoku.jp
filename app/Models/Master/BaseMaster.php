<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * マスタ系テーブル共通の基底モデル.
 *
 * - del_flg または is_active を使った論理削除
 * - active スコープで有効レコードのみ取得
 */
abstract class BaseMaster extends Model
{
    /**
     * 削除フラグのカラム名（使わない場合は null）.
     */
    public const DELETE_FLAG = null;

    /**
     * 有効フラグのカラム名（使わない場合は null）.
     */
    public const ACTIVE_FLAG = null;

    /**
     * 一括代入保護は行わない（管理画面専用の小さなテーブルのため）.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    public $timestamps = true;

    /**
     * 有効レコードのみ取得するスコープ.
     */
    public function scopeActive(Builder $query): Builder
    {
        if (static::DELETE_FLAG !== null) {
            $query->where(static::DELETE_FLAG, 0);
        }

        if (static::ACTIVE_FLAG !== null) {
            $query->where(static::ACTIVE_FLAG, 1);
        }

        return $query;
    }

    /**
     * 論理削除（del_flg=1 または is_active=0）.
     */
    public function logicalDelete(): void
    {
        if (static::DELETE_FLAG !== null) {
            $this->{static::DELETE_FLAG} = 1;
        } elseif (static::ACTIVE_FLAG !== null) {
            $this->{static::ACTIVE_FLAG} = 0;
        } else {
            // 論理削除カラムが無い場合は物理削除
            $this->delete();

            return;
        }

        $this->save();
    }
}

