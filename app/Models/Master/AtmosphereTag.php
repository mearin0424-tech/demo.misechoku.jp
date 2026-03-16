<?php

namespace App\Models\Master;

class AtmosphereTag extends BaseMaster
{
    // お店の雰囲気・規模マスタ
    protected $table = 'tags_shop_atmospheres';

    public const DELETE_FLAG = 'del_flg';
}

