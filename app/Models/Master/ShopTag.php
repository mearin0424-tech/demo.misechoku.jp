<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Builder;

/**
 * 店舗・求人用統合タグマスタ.
 *
 * - target = 'shop': category = atmosphere / facility (店舗プロフィール用)
 * - target = 'job' : category = work_style / welcome / benefit (求人票用)
 *
 * 旧 tags_salary / tags_shop_working_styles / tags_shop_benefits / tags_shop_conditions /
 * tags_shop_facilities / tags_shop_atmospheres / tags_shop_accesses を統合したもの.
 */
class ShopTag extends BaseMaster
{
    protected $table = 'shop_tags';

    public const DELETE_FLAG = 'del_flg';

    public function scopeTarget(Builder $query, string $target): Builder
    {
        return $query->where('target', $target);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeShopTags(Builder $query): Builder
    {
        return $query->where('target', 'shop');
    }

    public function scopeJobTags(Builder $query): Builder
    {
        return $query->where('target', 'job');
    }
}
