<?php

namespace App\Consts;

// 定数の宣言
class NewsConsts {
      
    // 公開
    public const RELEASE_ON   = '1';
    // 非公開
    public const RELEASE_OFF  = '0';

    public const RELEASE = [
      '公開' => self::RELEASE_ON,
      '非公開' => self::RELEASE_OFF,
    ];

    public const Type_1 = 1;
    public const Type_2 = 2;
    public const Type_3 = 3;

    public const Type = [
        '両方' => self::Type_1,
        'キャスト向け' => self::Type_2,
        '店舗向け' => self::Type_3,
    ];




}


?>