<?php

namespace App\Models\Master;

class FeatureTag extends BaseMaster
{
    // 店舗営業情報マスタ
    protected $table = 'tags_shop_conditions';

    public const DELETE_FLAG = 'del_flg';
}

