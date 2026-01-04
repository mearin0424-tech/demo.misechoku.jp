<?php

namespace app\Lib;

/**
*
*  ユーザーアクセスに関する共通クラス
*
*/

class Mail {

    public static function sayHello(){
        echo "Hello World!";
    }

    public function send_with_attachment ( $to, $dir, $zip ) {

          mb_internal_encoding("UTF-8");

          // 送信元の設定
          $sender_email = 'info@misechoku.jp';
          $org = 'COLUMBUSMAN';
          $from = 'COLUMBUSMAN <info@misechoku.jp>';


          // ヘッダー設定
          $header = '';
          $header .= "Content-Type: multipart/mixed;boundary=\"__BOUNDARY__\"\n";
          $header .= "Return-Path: " . $sender_email . " \n";
          $header .= "From: " . $from ." \n";
          $header .= "Sender: " . $from ." \n";
          $header .= "Reply-To: " . $sender_email . " \n";
          $header .= "Organization: " . $org . " \n";
          $header .= "X-Sender: " . $org . " \n";
          $header .= "X-Priority: 3 \n";

          // テキストメッセージを記述
          $text="";
          $body = "--__BOUNDARY__\n";
          $body .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
          $body .= $text . "\n";
          $body .= "--__BOUNDARY__\n";

          // ファイルを添付

          $dir=config('app.zipdir');
          $file=$zip;
          $body .= "Content-Type: application/octet-stream; name=\"{$file}\"\n";
          $body .= "Content-Disposition: attachment; filename=\"{$file}\"\n";
          $body .= "Content-Transfer-Encoding: base64\n";
          $body .= "\n";
          $body .= chunk_split(base64_encode(file_get_contents($dir.$zip)));
          $body .= "--__BOUNDARY__--";


          //メール送信
          $subject="ファイルの送信";
          $to_arr = config('const.MAIL_TO');
          foreach ( $to_arr as $to ) {
              $res = mb_send_mail( $to, $subject, $body, $header);
          }


    }


}
?>
