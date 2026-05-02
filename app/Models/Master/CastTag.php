<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Builder;

/**
 * キャスト用統合タグマスタ.
 *
 * - category: looks / personality
 * - 既存の tags_cast_looks / tags_cast_personality を統合したもの
 */
class CastTag extends BaseMaster
{
    protected $table = 'cast_tags';

    public const DELETE_FLAG = 'del_flg';

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
