<?php

namespace App\Consts;

// 定数の宣言
class TreatmentConsts
{

  public const SUBJECT_1 = '報酬';
  public const SALARY_1 = 11;
  public const SALARY_2 = 12;
  public const SALARY_3 = 13;

  public const SALARY = [
    '日払い' => self::SALARY_1,
    '全額日払い' => self::SALARY_2,
    '交通費支給' => self::SALARY_3,
  ];


  public const SUBJECT_2 = '働き方';
  public const HOWTO_1 = 21;
  public const HOWTO_2 = 22;
  public const HOWTO_3 = 23;
  public const HOWTO_4 = 24;

  public const HOWTO = [
    '週一からOK' => self::HOWTO_1,
    '短期OK' => self::HOWTO_2,
    '1日3h以内(短時間)OK' => self::HOWTO_3,
    '未経験者歓迎' => self::HOWTO_4,

  ];

  public const SUBJECT_3 = 'メリット';
  public const MERIT_1 = 31;
  public const MERIT_2 = 32;
  public const MERIT_3 = 33;
  public const MERIT_4 = 34;
  public const MERIT_5 = 35;
  public const MERIT_6 = 36;
  public const MERIT_7 = 37;
  public const MERIT_8 = 38;
  public const MERIT_9 = 39;
  public const MERIT_10 = 30;
  public const MERIT_11 = 31;
  public const MERIT_12 = 32;
  public const MERIT_13 = 33;

  public const MERIT = [
    'レンタル衣装あり' => self::MERIT_1,
    '服装自由' => self::MERIT_2,
    'ヘアメイクあり' => self::MERIT_3,
    'ヘアメイク不要' => self::MERIT_4,
    '手ぶらで体入OK' => self::MERIT_5,
    '送りあり' => self::MERIT_6,
    '迎えあり' => self::MERIT_7,
    '駅からスグ' => self::MERIT_8,
    '終電上がりOK' => self::MERIT_9,
    '早上げなし' => self::MERIT_10,
    'ノルマなし' => self::MERIT_11,
    '採用報酬' => self::MERIT_12,
    '福利厚生・提携先割引あり' => self::MERIT_13,
  ];


  public const SUBJECT_4 = '特徴';
  public const FEATURE_1 = 41;
  public const FEATURE_2 = 42;
  public const FEATURE_3 = 43;
  public const FEATURE_4 = 44;
  public const FEATURE_5 = 45;
  public const FEATURE_6 = 46;
  public const FEATURE_7 = 47;
  public const FEATURE_8 = 48;

  public const FEATURE = [
    'ニューオープン' => self::FEATURE_1,
    '登録制あり' => self::FEATURE_2,
    '日曜営業' => self::FEATURE_3,
    '30代OK' => self::FEATURE_4,
    '40代OK' => self::FEATURE_5,
    'コロナウイルス対策実施' => self::FEATURE_6,
    'タトゥーOK' => self::FEATURE_7,
    '禁煙店' => self::FEATURE_8,
  ];


  public const SUBJECT_5 = '設備';
  public const FACILITY_1 = 51;
  public const FACILITY_2 = 52;
  public const FACILITY_3 = 53;
  public const FACILITY_4 = 54;
  public const FACILITY_5 = 55;
  public const FACILITY_6 = 56;
  public const FACILITY_7 = 57;

  public const SUBJECT_6 = 'キャストタグ ご自分の系統';
  public const CASTTAG_1 = 61;
  public const CASTTAG_2 = 62;
  public const CASTTAG_3 = 63;
  public const CASTTAG_4 = 64;
  public const CASTTAG_5 = 65;
  public const CASTTAG_6 = 66;
  public const CASTTAG_7 = 67;
  public const CASTTAG_8 = 68;
  public const CASTTAG_9 = 69;

  public const SUBJECT_7 = 'キャストタグ ご自分の内面•特技';
  public const SUBJECT_8 = 'お店の雰囲気';

  // 設備
  public const FACILITY = [
    '駐車場あり' => self::FACILITY_1,
    '登車通勤OK' => self::FACILITY_2,
    '寮あり' => self::FACILITY_3,
    '即日入居可寮あり' => self::FACILITY_4,
    '託児所あり' => self::FACILITY_5,
    '個人ロッカーあり' => self::FACILITY_6,
    'キャスト専用トイレあり' => self::FACILITY_7,
  ];

  public const CASTTAG = [
    'スレンダー' => self::CASTTAG_1,
    'グラマー' => self::CASTTAG_2,
    'ぽっちゃり' => self::CASTTAG_3,
    '身長' => self::CASTTAG_4,
    'キレイ系' => self::CASTTAG_5,
    '可愛い系' => self::CASTTAG_6,
    'ギャル' => self::CASTTAG_7,
    '清楚' => self::CASTTAG_8,
    '話好き' => self::CASTTAG_9,
  ];

  public const TREATMENT = [

    '報酬' => self::SALARY,
    '働き方' => self::HOWTO,
    'メリット' => self::MERIT,
    '特徴' => self::FEATURE,
    '設備' => self::FACILITY,

  ];

  public const SUBJECT = [

    self::SUBJECT_1,
    self::SUBJECT_2,
    self::SUBJECT_8,
    self::SUBJECT_3,
    self::SUBJECT_4,
    self::SUBJECT_5,
    self::SUBJECT_6,
    self::SUBJECT_7,


  ];

  public const VARIABLE_1 = 'salary';
  public const VARIABLE_2 = 'howto';
  public const VARIABLE_3 = 'merit';
  public const VARIABLE_4 = 'feature';
  public const VARIABLE_5 = 'facility';
  public const VARIABLE_6 = 'casttag';
  public const VARIABLE_7 = 'casttag2';
  public const VARIABLE_8 = 'atmosphere';

  public const VARIABLE_NAME = [
    self::VARIABLE_1, self::VARIABLE_2, self::VARIABLE_3, self::VARIABLE_4,
    self::VARIABLE_5, self::VARIABLE_6, self::VARIABLE_7,self::VARIABLE_8,
  ];

  public const SUBJECT2 = [

    "salary" => self::SUBJECT_1,
    "howto" => self::SUBJECT_2,
    "atmosphere" => self::SUBJECT_8,
    "merit" => self::SUBJECT_3,
    "feature" => self::SUBJECT_4,
    "facility" => self::SUBJECT_5,
    "casttag" => self::SUBJECT_6,
    "casttag2" => self::SUBJECT_7,

  ];
}
