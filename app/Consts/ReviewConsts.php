<?php

namespace App\Consts;

// 定数の宣言
class ReviewConsts {

    // 掲載中
    public const APPROVAL_OK_1  = '1';

    public const APPROVAL_OK  = '6';

    // 掲載NG
    public const APPROVAL_NG  = '9';

    // 確認中
    public const APPROVAL_YET  = '2';

    // 既読
    public const READED_ON   = '1';

    // 未読
    public const READED_OFF  = '0';

    public const APPROVAL = [

      '掲載NG' => self::APPROVAL_NG,
      '掲載済' => self::APPROVAL_OK,
      '確認中' => self::APPROVAL_YET,
    ];

    public const READED = [
      '既読' => self::READED_ON,
      '未読' => self::READED_OFF,
    ];


    public const TITLE_1  = '1';
    public const TITLE_2  = '2';
    public const TITLE_3  = '3';
    public const TITLE_4  = '4';
    public const TITLE_5  = '5';
    public const TITLE_6  = '6';
    public const TITLE_7  = '7';
    public const TITLE_8  = '8';
    public const TITLE_9  = '9';
    public const TITLE_10  = '10';

    public const RATING_TITLE = [

      'xxxxxxxは十分でしたか？-1' => self::TITLE_1,
      'xxxxxxxは十分でしたか？-2' => self::TITLE_2,
      'xxxxxxxは十分でしたか？-3' => self::TITLE_3,
      'xxxxxxxは十分でしたか？-4' => self::TITLE_4,
      'xxxxxxxは十分でしたか？-5' => self::TITLE_5,
      'xxxxxxxは十分でしたか？-6' => self::TITLE_6,
      'xxxxxxxは十分でしたか？-7' => self::TITLE_7,
      'xxxxxxxは十分でしたか？-8' => self::TITLE_8,
      'xxxxxxxは十分でしたか？-9' => self::TITLE_9,
      'xxxxxxxは十分でしたか？-10' => self::TITLE_10,
    ];

}
?>	
