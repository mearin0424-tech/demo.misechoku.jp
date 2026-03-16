<?php

namespace App\Models\Master;

class FacilityTag extends BaseMaster
{
    // 設備・空間マスタ
    protected $table = 'tags_shop_facilities';

    public const DELETE_FLAG = 'del_flg';
}

