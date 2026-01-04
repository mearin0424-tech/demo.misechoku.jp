<?php

namespace App\Consts;

// 定数の宣言
class FrontConsts {

    // 承認済み  
    //public const APPROVAL_ON   = '1';
    // 審査中
    //public const APPROVAL_OFF  = '0';
    // 公開
    public const RELEASE_ON   = '1';
    // 非公開
    public const RELEASE_OFF  = '0';

    public const RELEASE = [
      '公開' => self::RELEASE_ON,
      '非公開' => self::RELEASE_OFF,
    ];

    // 本人確認画像種別
    public const IDENTITY_1 = 1;
    public const IDENTITY_2 = 2;
    public const IDENTITY_3 = 3;

    // 運転免許証裏
    public const IDENTITY_11 = 11;
    // 健康保険証裏
    public const IDENTITY_12 = 12;
    // パスポート裏
    public const IDENTITY_13 = 13;

    //アバター
    public const IDENTITY_5 = 5;

    // 身分証明書の表裏
    public const FRONT_IMG = 1;
    public const BACK_IMG  = 2;

    // その他の画像
    public const IDENTITY_99 = 99;

    public const IDENTITY = [
        '運転免許証' => self::IDENTITY_1,
        '健康保険証' => self::IDENTITY_2,
        'パスポート' => self::IDENTITY_3,
    ];

    public const IDENTITY2 = [
        '運転免許証' => self::IDENTITY_11,
        '健康保険証' => self::IDENTITY_12,
        'パスポート' => self::IDENTITY_13,
    ];

    public const FRONT  = 'front';
    public const BACK   = 'back';
    // 身分証明書表裏
    public const CERT_FILE_NAMES = [
         self::FRONT,
         self::BACK,
    ];

    // ファイルアップロードディレクトリ
    public const MEMBER_MAIN_IMG_DIR       = 'public/member/';

    // ファイル閲覧用ディレクトリ
    public const MEMBER_DISP_IMG_DIR       = '/storage/member/';

    // profile
    public const MEMBER_DIR_PROFILE    = 'profie';
    public const MEMBER_DIR_IDENTITY_1   = 'identity';
    public const MEMBER_DIR_IDENTITY_2   = 'identity2';

    // 掲載申請未承認
    public const APPROVAL_OFF  = '0';

    // 変更申請未承認
    public const APPROVAL_OFF2  = '1';

    // 掲載NG
    public const APPROVAL_NG  = '9';

    // 承認済み
    public const APPROVAL_ON  = '5';

    // 掲載
    public const APPROVAL = [

        '掲載申請:未承認' => self::APPROVAL_OFF,
        '変更申請:未承認' => self::APPROVAL_OFF2,
        '掲載NG' => self::APPROVAL_NG,
        '承認済み' => self::APPROVAL_ON,

    ];

    public const EXP_ON   = '1';
    public const EXP_OFF  = '0';


    public const MEMBER_SUB_IMAGE_FILE_CNT = 8;

    public const cast_tab_1  = '1';
    public const cast_tab_2  = '2';
    public const cast_tab_3  = '3';
    public const cast_tab_4  = '4';
    public const cast_tab_5  = '5';
    public const cast_tab_6  = '6';
    public const cast_tab_7  = '7';
    public const cast_tab_8  = '8';
    public const cast_tab_9  = '9';

    public const CAST_TAB = [

        'スレンダー' => self::cast_tab_1,
        'グラマー' => self::cast_tab_2,
        'ぽっちゃり' => self::cast_tab_3,
        '長身' => self::cast_tab_4,
        'キレイ系' => self::cast_tab_5,
        '可愛い系' => self::cast_tab_6,
        'ギャル' => self::cast_tab_7,
        '清楚' => self::cast_tab_8,
        '話好き' => self::cast_tab_9,

    ];


}


?>