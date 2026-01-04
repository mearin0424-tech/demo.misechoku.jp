<?php

namespace App\Consts;

// 定数の宣言
class ShopConsts {

    // 登録できる担当者数
    public const MANAGER_LIMIT   = '5';

    // 仮登録
    public const TENP_REG   = '0';

    // 本登録
    public const OFFICIAL_REG  = '1';

    // 公開
    public const RELEASE_ON   = '1';

    // 非公開
    public const RELEASE_OFF  = '0';

    // 掲載申請未承認
    public const APPROVAL_OFF  = '0';

    // 変更申請未承認
    public const APPROVAL_OFF2  = '1';

    // 退会未承認
    public const WITHDRAWAL_YET  = '2';

    // 承認済み
    public const APPROVAL_ON  = '5';

    // 掲載中
    public const PUBLISH_ON  = '6';

    // 掲載NG
    public const APPROVAL_NG  = '9';

    // 退会
    public const WITHDRAWAL_DONE  = '10';

    // マッチング可
    public const MATCHING_ON   = '1';

    // マッチング不可
    public const MATCHING_OFF  = '0';

    // 既読
    public const STATUS_ON   = '1';

    // 未読
    public const STATUS_OFF  = '0';

    // 体験入店
    public const TRIAL_JOB_ON  = 1;
    public const TRIAL_JOB_OFF  = 2;

    // ヘルプ求人
    public const HELP_JOB_ON  = 1;
    public const HELP_JOB_OFF  = 2;

    // 0:仮登録 1:本登録 2:メール認証済 9:退会済
    public const PRE_REGISTER   = '0';
    public const REGISTER   = '1';
    public const MAIL_AUTHED   = '2';
    public const DEACTIVE   = '9';


    public const APPROVAL = [

      '掲載申請:未承認' => self::APPROVAL_OFF,
      '変更申請:未承認' => self::APPROVAL_OFF2,
      '掲載NG' => self::APPROVAL_NG,
      '承認済み' => self::APPROVAL_ON,
      '承認前' => self::WITHDRAWAL_YET,
      '退会' => self::WITHDRAWAL_DONE,
      '掲載中' => self::PUBLISH_ON,

    ];

    public const MATCHING = [
      '不可' => self::MATCHING_OFF,
      '可' => self::MATCHING_ON,
    ];

    public const RELEASE = [
      '公開' => self::RELEASE_ON,
      '非公開' => self::RELEASE_OFF,
    ];

    public const STATUS = [
      '既読' => self::STATUS_ON,
      '未読' => self::STATUS_OFF,
    ];

    public const WITHDRAWAL = [
      '承認前' => self::WITHDRAWAL_YET,
      '退会' => self::WITHDRAWAL_DONE,
    ];

    // 最新の表示件数
    public const LATEST_DISPLAY_COUNT  = 3;
    // ページネーション
    public const PAGENATION_COUNT  = 50;

    // 情報,画像
    public const SUB_IMG_TYPE_1  = 1;
    // 情報,動画
    public const SUB_IMG_TYPE_2  = 2;
    public const SUB_IMG_TYPE_3  = 3;


    public const INFO_DISP_LIMIT  = 30;

    // 店舗ファイルアップロードディレクトリ
    public const SHOP_MAIN_IMG_DIR       = 'public/shop/';

    // 店舗ファイル閲覧用ディレクトリ
    public const SHOP_DISP_IMG_DIR       = '/storage/shop/';

    // 店舗用ファイル名 storage/app/public/shop/店舗ID/
    //public const SHOP_FILE_NAMES         = array('shop_main_img','shop_license_img','shop_license2_img');

    public const SHOP_MAIN_IMG  = "shop_main_img";
    public const SHOP_LICENSE_IMG  = "shop_license_img";
    public const SHOP_LICENSE2_IMG  = "shop_license2_img";

    public const SHOP_FILE_NAMES = [
        self::SHOP_MAIN_IMG,
        self::SHOP_LICENSE_IMG,
        self::SHOP_LICENSE2_IMG,
    ];

    public const SHOP_MAIN_IMG_HID  = "hid_shop_main_img";
    public const SHOP_LICENSE_IMG_HID  = "hid_shop_license_img";
    public const SHOP_LICENSE2_IMG_HID  = "hid_shop_license2_img";

    public const SHOP_FILE_NAMES_HID = [
        self::SHOP_MAIN_IMG_HID,
        self::SHOP_LICENSE_IMG_HID,
        self::SHOP_LICENSE2_IMG_HID,
    ];

    public const SHOP_DIR_NAMES_MAIN    = 'main';
    public const SHOP_DIR_NAMES_MIC1    = 'lic1';
    public const SHOP_DIR_NAMES_MIC2    = 'lic2';

    public const SHOP_DIR_NAMES = [
        self::SHOP_DIR_NAMES_MAIN,
        self::SHOP_DIR_NAMES_MIC1,
        self::SHOP_DIR_NAMES_MIC2,
    ];


    public const SHOP_SUB_IMG_DIR       = 'sub';
    public const SHOP_FILE_NAME_MULTIPLE = 'sub_img';
    public const SHOP_SUB_IMAGE_FILE_CNT = 8;



    public const PERMISSION_1       = 1;
    public const PERMISSION_2       = 2;
    public const PERMISSION_3       = 3;

    public const PERMISSION = [
      '深夜酒類提供飲食店営業営業開始届出' => self::PERMISSION_1,
      '接待飲食等営業' => self::PERMISSION_2,
      '許可なし' => self::PERMISSION_3,

    ];

    // 在籍人数
    public const ENROLLED_1       = 1;
    public const ENROLLED_2       = 2;
    public const ENROLLED_3       = 3;
    public const ENROLLED_4       = 4;
    public const ENROLLED_5       = 5;
    public const ENROLLED_6       = 6;
    public const ENROLLED_7       = 7;

    public const ENROLLED = [
      '1名〜5名' => self::ENROLLED_1,
      '6名〜10名' => self::ENROLLED_2,
      '11名〜15名' => self::ENROLLED_3,
      '16名〜25名' => self::ENROLLED_4,
      '26名〜40名' => self::ENROLLED_5,
      '40名〜60名' => self::ENROLLED_6,
      '60名以上' => self::ENROLLED_7,

    ];


    public const ATTENDANCE_1       = 1;
    public const ATTENDANCE_2       = 2;
    public const ATTENDANCE_3       = 3;
    public const ATTENDANCE_4       = 4;
    public const ATTENDANCE_5       = 5;
    public const ATTENDANCE_6       = 6;
    public const ATTENDANCE_7       = 7;

    public const ATTENDANCE = [
      '1名〜5名' => self::ATTENDANCE_1,
      '6名〜10名' => self::ATTENDANCE_2,
      '11名〜15名' => self::ATTENDANCE_3,
      '16名〜25名' => self::ATTENDANCE_4,
      '26名〜40名' => self::ATTENDANCE_5,
      '40名〜60名' => self::ATTENDANCE_6,
      '60名以上' => self::ATTENDANCE_7,
    ];


    public const SCOUTUSE_1       = 1;
    public const SCOUTUSE_2       = 2;
    public const SCOUTUSE_3       = 3;

    public const SCOUTUSE = [
      'はい' => self::SCOUTUSE_1,
      'いいえ' => self::SCOUTUSE_2,
      '時々利用' => self::SCOUTUSE_3,
    ];

    public const SCOUTBACK_1       = 1;
    public const SCOUTBACK_2       = 2;
    public const SCOUTBACK_3       = 3;
    public const SCOUTBACK_4       = 4;
    public const SCOUTBACK_5       = 5;
    public const SCOUTBACK_6       = 6;

    public const SCOUTBACK = [
      '3万円〜5万円' => self::SCOUTBACK_1,
      '6万円〜10万円' => self::SCOUTBACK_2,
      '11万円〜15万円' => self::SCOUTBACK_3,
      '16万円〜20万円' => self::SCOUTBACK_4,
      '21万円以上' => self::SCOUTBACK_5,
      'その他' => self::SCOUTBACK_6,

    ];

    public const ICHIKIN_1       = 1;
    public const ICHIKIN_2       = 2;
    public const ICHIKIN_3       = 3;
    public const ICHIKIN_4       = 4;
    public const ICHIKIN_5       = 5;
    public const ICHIKIN_6       = 6;
    public const ICHIKIN_7       = 7;
    public const ICHIKIN_8       = 8;
    public const ICHIKIN_9       = 9;
    public const ICHIKIN_10       = 10;

    public const ICHIKIN = [
      '1日出勤毎 1,000円平均' => self::ICHIKIN_1,
      '1日出勤毎 2,000円平均' => self::ICHIKIN_2,
      '1日出勤毎 3,000円平均' => self::ICHIKIN_3,
      '1日出勤毎 4,000円平均' => self::ICHIKIN_4,
      '1日出勤毎 5,000円平均' => self::ICHIKIN_5,
      '1日出勤毎 5,000円平均以上' => self::ICHIKIN_6,
      '月額給料の5％' => self::ICHIKIN_7,
      '月額給料の10％' => self::ICHIKIN_8,
      '月額給料の15％' => self::ICHIKIN_9,
      '月額給料の20％以上' => self::ICHIKIN_10,

    ];

    public const JOBADVERTISEMENT_1       = 1;
    public const JOBADVERTISEMENT_2       = 2;
    public const JOBADVERTISEMENT_3       = 3;
    public const JOBADVERTISEMENT_4       = 4;
    public const JOBADVERTISEMENT_5       = 5;
    public const JOBADVERTISEMENT_6       = 6;
    public const JOBADVERTISEMENT_7       = 7;

    public const JOBADVERTISEMENT = [
      '利用していない' => self::JOBADVERTISEMENT_1,
      '月3万円未満' => self::JOBADVERTISEMENT_2,
      '月3万円〜5万円' => self::JOBADVERTISEMENT_3,
      '月6万円〜10万円' => self::JOBADVERTISEMENT_4,
      '月11万円〜15万円' => self::JOBADVERTISEMENT_5,
      '月16万円〜20万円' => self::JOBADVERTISEMENT_6,
      '月21万円以上' => self::JOBADVERTISEMENT_7,

    ];

    public const REWARDING       = 5000;

}



?>
