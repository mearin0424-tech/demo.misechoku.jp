<?php

namespace App\Consts;

// 定数の宣言
class CommonConsts {

  // 削除フラグ
  public const DEL_ON  = 1;
  public const DEL_OFF = 0;

  // 公開
  public const RELEASE_ON   = '1';

  // 非公開
  public const RELEASE_OFF  = '0';

  // 退会済み
  public const WITHDRAWAL = 1;

  // シフト
  public const SHIFT_1 = 1;
  public const SHIFT_2 = 2;
  public const SHIFT_3 = 3;
  public const SHIFT_4 = 4;
  public const SHIFT_5 = 5;
  public const SHIFT_6 = 6;
  public const SHIFT_7 = 7;

  public const SHIFT = [
    '週1回出勤' => self::SHIFT_1,
    '週2回出勤' => self::SHIFT_2,
    '週3回出勤' => self::SHIFT_3,
    '週4回出勤' => self::SHIFT_4,
    '週5回出勤' => self::SHIFT_5,
    '週6回出勤' => self::SHIFT_6,
    '週7回出勤' => self::SHIFT_7,
  ];

    public const GENDER_1 = '1';
    public const GENDER_2 = '0';
    public const GENDER_3 = '2';

    public const GENDER = [

        '女性' => self::GENDER_1,
        '男性' => self::GENDER_2,
        'LGBT' => self::GENDER_3,

    ];

    public const GENDER2 = [
        '女性' => self::GENDER_1,
        '男性' => self::GENDER_2,
        'LGBT' => self::GENDER_3,

    ];

    // 既読
    public const STATUS_ON   = '1';
    // 未読
    public const STATUS_OFF  = '0';


    // 既読
    public const READ_DONE   = '1';
    // 未読
    public const READ_YET  = '0';

    // 採用
    public const ADOPTED  = 1;
    // 不採用
    public const REJECTED = 0;

    // メンバーから
    public const FORROW_BY_MEMBER = 1;

    // ショップから
    public const FORROW_BY_SHOP   = 2;

    // ショップ→メンバー
    public const FORROW_BY_MEMBER_2 = 3;

    // メンバー→ショップ
    public const FORROW_BY_SHOP_2   = 4;

    public const FORROW_BY_SHOP_NG = 9;
    public const FORROW_BY_MEMBER_NG = 10;


    public const FORROW = [

        'マッチング承認中' => self::FORROW_BY_MEMBER,
        'マッチング承認中' => self::FORROW_BY_SHOPFORROW_BY_SHOP,
        'マッチング成立' => self::FORROW_BY_MEMBER_2,
        'マッチング成立' => self::FORROW_BY_SHOP_2,
        'マッチング不成立' => self::FORROW_BY_SHOP_NG,
        'マッチング不成立' => self::FORROW_BY_MEMBER_NG,

    ];


    public const NEW = 23;


    public const SHOP_FORROW_PAGE = "shop_follow";
    public const SHOP_MATCHING_PAGE = "shop_matching";
    public const SHOP_MESSAGE_PAGE = "shop_message";
    public const SHOP_DEPOSIT_PAGE = "shop_deposit";
    public const SHOP_APPLY_PAGE = "shop_apply";
    public const SHOP_TALK_PAGE = "shop_talk";

    public const MEMBER_FORROW_PAGE = "member_follow";
    public const MEMBER_MATCHING_PAGE = "member_matching";
    public const MEMBER_MESSAGE_PAGE = "member_message";
    public const MEMBER_DEPOSIT_PAGE = "member_deposit";
    public const MEMBER_APPLY_PAGE = "member_apply";

    public const PREFS= ["北海道","青森県","岩手県","宮城県","秋田県","山形県","福島県","茨城県","栃木県","群馬県","埼玉県","千葉県","東京都","神奈川県",
              "新潟県","富山県","石川県","福井県","山梨県","長野県","岐阜県","静岡県","愛知県","三重県","滋賀県","京都府","大阪府","兵庫県",
              "奈良県","和歌山県","鳥取県","島根県","岡山県","広島県","山口県","徳島県","香川県","愛媛県","高知県","福岡県","佐賀県","長崎県",
              "熊本県","大分県","宮崎県","鹿児島県","沖縄県"];



   public const MAIL_FROM = "info@misechoku.jp";
   public const ADDRESS1 = "キャストと店舗をチョクで繋ぐ";
   public const ADDRESS2 = "https://misechoku.jp/";
   public const PASSWORD_RESET_HOURS = 48;
   public const MAIL_VERIFICATION_SUBJECT = "【ミセチョク】仮登録完了のお知らせ";
   public const MAIL_APPROVAL_ON = "【ミセチョク】情報変更承認のお知らせ";
   public const MAIL_APPROVAL_OFF = "【ミセチョク】情報変更非承認のお知らせ";
   public const FIRST_REGIST = "【ミセチョク】アカウント登録完了のお知らせ";

   public const NORUMA_MEMBER_TITLE = "【ミセチョク】ノルマ達成のお知らせ";
   public const STORE_BILLING_TITLE = "請求書発行";

   public const STORE_REPORT_TITLE = "通報がありました。";

   public const MAIL_TO_SYSTEM = "info@misechoku.dev14.neoindex.jp";


    public const DISTANCE_1  = 1;
    public const DISTANCE_5  = 5;
    public const DISTANCE_10 = 10;
    public const DISTANCE_11 = 11;

    public const DISTANCE = [

        '1km以内' => self::DISTANCE_1,
        '5km以内' => self::DISTANCE_5,
        '10km以内' => self::DISTANCE_10,
        '10km以上' => self::DISTANCE_11,

    ];


    // 店舗ファイルアップロードディレクトリ
    public const DOWNLOAD_DIR       = 'public/document/';

    // 店舗ファイル閲覧用ディレクトリ
    public const DISP_DOWNLOAD_DIR       = '/storage/document/';

    // コラムアップロードディレクトリ
    public const COLUMN_DIR       = 'public/column/';

    // コラムファイル閲覧用ディレクトリ
    public const DISP_COLUMN_DIR       = '/storage/column/';

    public const COMMISSION = "23";
    public const HELP_COMMISSION = "20";


}




?>
