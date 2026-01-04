<?php

namespace App\Consts;

// 定数の宣言
class DepositConsts
{

  // ノルマ消化中
  public const DEPOSITS_1 = 1;
  // 入金依頼済
  public const DEPOSITS_2 = 2;
  // 入金承認済
  public const DEPOSITS_3 = 3;
  // 店舗請求済
  public const DEPOSITS_4 = 4;
  // 店舗入金済
  public const DEPOSITS_5 = 5;
  // キャスト入金済
  public const DEPOSITS_6 = 6;

  public const DEPOSITS = [

    'ノルマ消化中' => self::DEPOSITS_1,
    '入金依頼済' => self::DEPOSITS_2,
    '入金承認済' => self::DEPOSITS_3,
    '店舗請求済' => self::DEPOSITS_4,
    '店舗入金済' => self::DEPOSITS_5,
    'キャスト入金済' => self::DEPOSITS_6,

  ];

  public const DEPOSITS2 = [

    '入金承認済' => self::DEPOSITS_3,
    '店舗請求済' => self::DEPOSITS_4,
    '店舗入金済' => self::DEPOSITS_5,
    'キャスト入金済' => self::DEPOSITS_6,

  ];

  public const DEPOSITS3 = [

    '入金承認済' => self::DEPOSITS_3,
    '入金済' => self::DEPOSITS_6,

  ];

  // 
  public const TYPE_1 = '1';
  // 
  public const TYPE_2 = '2';
  public const TYPE_3 = '3';

  public const TYPE = [
    '普通' => self::TYPE_1,
    '当座' => self::TYPE_2,
    '財蓄' => self::TYPE_3,

  ];

  public const DEPOSIT_1 = 1;
  public const DEPOSIT_2 = 2;
  public const DEPOSIT_3 = 3;
  public const DEPOSIT_4 = 4;
  public const DEPOSIT_5 = 5;

  public const DEPOSIT = [
    '入金依頼申請済' => self::DEPOSIT_1,
    '入金依頼承認済' => self::DEPOSIT_2,
    '店舗請求済' => self::DEPOSIT_3,
    '店舗入金済' => self::DEPOSIT_4,
    'キャスト入金済み' => self::DEPOSIT_5,

  ];
}
