<?php

namespace App\Consts;

// 定数の宣言
class RicruitConsts {

    // 未面談
    public const INTERVIEW_YET   = '0';
    // 面談済
    public const INTERVIEW_DONE   = '1';
    // ヘルプ採用
    public const ADOPTION_HELP   = '4';
    // 本採用
    public const ADOPTION_FULL   = '5';
    // 不採用
    public const ADOPTION_NG  = '9';

    // ヘルプ募集
    public const RECRUITMENT_HELP2   = '4';
    // 通常募集
    public const RECRUITMENT_FULL2  = '5';

    // アクティブ状態（応募中・進行中の応募）
    public const ACTIVE = 1;

    // 非アクティブ状態（応募終了・不採用・採用完了など）
    public const END_ACTIVE = 0;

    // 採用
    public const ADOPTION_OK = 1;

    // 不採用
    public const ADOPTION_REJECTED = 2;

    public const ADOPTION_STATUS = [
        '採用' => self::ADOPTION_OK,
        '不採用' => self::ADOPTION_REJECTED,
    ];

    // 本入店
    public const ABOUT_RECRUIT_FULLTIME = 1;
    // 体験入店
    public const ABOUT_RECRUIT_TRIAL = 2;
    // ヘルプ
    public const ABOUT_RECRUIT_HELP = 3;

    public const ABOUT_RECRUIT = [
        '本入店' => self::ABOUT_RECRUIT_FULLTIME,
        '体験入店' => self::ABOUT_RECRUIT_TRIAL,
        'ヘルプ' => self::ABOUT_RECRUIT_HELP,
    ];

    public const RECRUITMENT4 = [
      'ヘルプ募集' => self::RECRUITMENT_HELP2,
      '通常募集' => self::RECRUITMENT_FULL2,
    ];



    public const RECRUITMENT_ADOPTION = [
      'ヘルプ募集' => self::ADOPTION_HELP,
      '通常募集' => self::ADOPTION_FULL,
      '不採用' => self::ADOPTION_NG,

    ];

    public const ADOPTION = [
      '未面談' => self::INTERVIEW_YET,
      '面談済' => self::INTERVIEW_DONE,
      'ヘルプ採用' => self::ADOPTION_HELP,
      '本採用' => self::ADOPTION_FULL,
      '不採用' => self::ADOPTION_NG,
    ];

    public const ADOPTION2 = [
      '本採用' => self::ADOPTION_FULL,
      '不採用' => self::ADOPTION_NG,
    ];

    // ヘルプ募集
    public const RECRUITMENT_HELP = '1';

    // 通常募集
    public const RECRUITMENT_FULL  = '2';

    public const RECRUITMENT = [
      'ヘルプ募集' => self::RECRUITMENT_HELP,
      '通常募集' => self::RECRUITMENT_FULL,
    ];

    public const RECRUITMENT3 = [
      '通常求人' => self::RECRUITMENT_FULL,
      'ヘルプ求人' => self::RECRUITMENT_HELP,
    ];

    public const RECRUITMENT2 = [
      'ヘルプ求人あり' => self::RECRUITMENT_HELP,
      'ヘルプ求人なし' => self::RECRUITMENT_FULL,
    ];

    public const RECRUITMENT5 = [
      '通常求人' => self::ADOPTION_HELP,
      'ヘルプ求人' => self::ADOPTION_FULL,
    ];

    public const SCOUT   = '1';
    public const ENTRY   = '2';

    // 経験あり
    public const EXPERIENCE_Y   = '1';
    // 経験なし
    public const EXPERIENCE_N   = '0';
    public const EXPERIENCE = [
      '無し' => self::EXPERIENCE_N,
      '有り' => self::EXPERIENCE_Y,
    ];

}
?>
