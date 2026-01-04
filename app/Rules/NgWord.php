<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use DB;
class NgWord implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */

    public function passes($attribute, $value)
    {
        // 固定電話番号、携帯電話番号、URL、SNSのカタカナ・ひらがな表記をチェックする正規表現パターン
        $ngPatterns = [
            '/\b\d{2,4}-\d{2,4}-\d{4}\b/', // 固定電話番号
            '/\b(080|090|070|050)-\d{4}-\d{4}\b/', // 携帯電話番号
            '/\bhttps?:\/\/[^\s]+\b/', // URL
            '/\b(ライン|らいん|エックス|えっくす|インスタグラム|いんすたぐらむ|フェイスブック|ふぇいすぶっく|ツイッター|ついったー|インスタ|いんすた|FB|fb|フェブ|ふぇぶ|ティクトク|てぃくとく|トコトコ|とことこ|TikTok|tiktok|カカオ|かかお|カカオトーク|かかおとーく|SNOW|snow|スノー|すのー|Pinterest|pinterest|ピンタレスト|ぴんたれすと|WeChat|wechat|ウィーチャット|うぃーちゃっと|WhatsApp|whatsapp|ワッツアップ|わっつあっぷ|Clubhouse|clubhouse|クラブハウス|くらぶはうす)\b/u', // SNSのカタカナ、ひらがな表記と略語
        ];
    
        // NGパターンにマッチするかチェック
        foreach ($ngPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return false;
            }
        }
    
        // 入力値を分割して個々の単語を抽出
        preg_match_all('/[一-龠]+|[ぁ-ん]+|[ァ-ヴー]+|[a-zA-Z0-9]+|[ａ-ｚＡ-Ｚ０-９]+/u', $value, $matches);
    
        // データベースからNGワードを取得
        $records = DB::table('ng_words')->first();
        //$ng_word = [];
        //foreach ($records as $record) {
        //    $ng_word[] = $record->content;
        //}
        $ng_word = explode(',',$records->content);

        // 抽出した単語がNGワードに含まれるかチェック
        foreach ($matches as $needle) {

            foreach ($needle as $w) {
                if (in_array($w, $ng_word, true)) {
                    return false;
                }
            }
        }

        return true;
    }


/*
    public function passes($attribute, $value)
    {
        //

        $str=$value;
        preg_match_all('/[一-龠]+|[ぁ-ん]+|[ァ-ヴー]+|[a-zA-Z0-9]+|[ａ-ｚＡ-Ｚ０-９]+/u', $str, $matches);
        //print_r($matches); // マッチ結果が全出力

        $records = DB::table('ng_words')->get();
        $ng_word=[];
        foreach($records as $record){
             $ng_word[] = $record->content;
        }

        foreach($matches as $needle){
            foreach($needle as $w){
            if(in_array($w,$ng_word,true)) {
                 return false;
            }
           }
        }
        return true;
    }
*/
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '入力内容に使用できない文言が含まれています。';
    }
}
