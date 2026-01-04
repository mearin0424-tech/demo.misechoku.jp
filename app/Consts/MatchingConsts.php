<?php

namespace App\Consts;

// 定数の宣言
class MatchingConsts {

    // マッチング成立
    public const MATCHING_OK   = '1';

    // マッチング承認中
    public const MATCHING_MID = '0';

    // マッチング不成立
    public const MATCHING_NG  = '9';


    public const MATCHING_STATUS = [
      'マッチング不成立' => self::MATCHING_NG,
      'マッチング承認中' => self::MATCHING_MID,
      'マッチング成立' => self::MATCHING_OK,
    ];

}
?>	


