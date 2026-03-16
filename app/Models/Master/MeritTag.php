<?php

namespace App\Models\Master;

class MeritTag extends BaseMaster
{
    // 待遇・サポートマスタ
    protected $table = 'tags_shop_benefits';

    public const DELETE_FLAG = 'del_flg';
}

