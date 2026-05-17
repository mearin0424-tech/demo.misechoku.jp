<?php

namespace App\Lib;

use Illuminate\Support\Facades\Facade;
use App\Consts\CommonConsts;
use App\Consts\TreatmentConsts;
use App\Repositories\Master\IndustryRepositoryInterface as MyRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ShopIndustry;
use App\Models\MemberIndustry;
use App\Models\NearestStation;
use App\Models\Treatment;
use \DateTime;
use Illuminate\Support\Str;

class StrUtil extends Facade {

   public static function getRandom ($num){
        return Str::random($num);
   }

    public static function yyyymmdd_to_date ($yyyy,$mm,$dd){
        if($yyyy==""||$mm==""||$dd=="") return "";
        return date('Y-m-d',strtotime($yyyy.$mm.$dd));
    }

    public static function y_m_d_h_i ($date){
        return date('Y/m/d H:i:s',strtotime($date));
    }

    public static function y_m_d ($date){
        return date('Y/m/d',strtotime($date));
    }

    public static function y_m_d2 ($date){
        return date('Y-m-d',strtotime($date));
    }


    public static function date_to_yyyymmdd ($str){
        return date('Y年m月d日',strtotime(str_replace('-','',$str)));
    }

    public static function date_to_yyyymmdd2 ($yyyy,$mm,$dd){
        return date('Y年m月d日',strtotime($yyyy.'-'.$mm.'-'.$dd));
    }

    public static function date_to_dod($dateTime) {

        if(empty($dateTime)) return '';
        $date = new DateTime($dateTime);
        return $date->format('Y.m.d');
    }

    public static function enc ($val) {
        //return openssl_encrypt(trim($val), \EncConsts::METHOD, \EncConsts::PASS);
        //$val = openssl_encrypt(trim($val), \EncConsts::METHOD, \EncConsts::PASS);
        //return str_replace(array('+', '/', '='), array('_', '-', '.'), $val);
	$val = base64_encode($val);
	return str_replace(array('+', '/', '='), array('_', '-', '.'), $val);

    }

    public static function dec ($val) {
        //return openssl_decrypt(trim($val), \EncConsts::METHOD, \EncConsts::PASS);
        //$val = openssl_decrypt(trim($val), \EncConsts::METHOD, \EncConsts::PASS);
        //return  str_replace(array('+', '/', '='), array('_', '-', '.'), $val);
	$val = str_replace(array('_','-', '.'), array('+', '/', '='), $val);
	return base64_decode($val);
    }

    public static function calc_age ($yyyy,$mm,$dd) {

        if($yyyy==""||$mm==""||$dd=="") return "";

        $mm = sprintf('%02d', $mm);
        $dd = sprintf('%02d', $dd);
        $birthday = $yyyy.$mm.$dd;
        $today = date('Ymd');
        return floor(($today - $birthday) / 10000) ."歳";
    }

    public static function shift ($shift_id) {

        foreach( CommonConsts::SHIFT as $key => $val) {
            if ($shift_id==$val) return $key ;
        }
        return "未回答";
    }

    public static function industry ($industry) {

        $myrepository = new MyRepository();
        $m_industory  = $myrepository->getAll();

        foreach( $m_industory as $key => $val) {
            if ($industry==$val) return $key ;
        }
        return "";
    }

    public static function industryNameByShopId ($shop_id) {
         // industry_label が登録されていれば最優先で採用
         if (Schema::hasColumn('shop_profiles', 'industry_label')) {
             $label = trim((string) DB::table('shop_profiles')
                 ->where('shop_id', $shop_id)
                 ->value('industry_label'));
             if ($label !== '') {
                 return $label . ' ';
             }
         }
         $records = DB::table('shop_profiles')
            ->join('industries', 'shop_profiles.industry_id', '=', 'industries.id')
            ->where('shop_profiles.shop_id', $shop_id)
            ->select('industries.name')
            ->get();
         $str = "";
         foreach($records as $record){
             $str .= $record->name." ";
         }
         return $str;
    }

    public static function industryNameByShopIdHtml ($shop_id) {
         // industry_label が登録されていれば最優先で採用
         if (Schema::hasColumn('shop_profiles', 'industry_label')) {
             $label = trim((string) DB::table('shop_profiles')
                 ->where('shop_id', $shop_id)
                 ->value('industry_label'));
             if ($label !== '') {
                 return "<li>".$label."</li>";
             }
         }
         $records = DB::table('shop_profiles')
            ->join('industries', 'shop_profiles.industry_id', '=', 'industries.id')
            ->where('shop_profiles.shop_id', $shop_id)
            ->select('industries.name')
            ->get();
         $str = "";
         foreach($records as $record){
             $str .= "<li>".$record->name."</li>";
         }
         return $str;
    }

    public static function industryNameByMemberId ($member_id) {
         $records = MemberIndustry::where("member_id",$member_id)->get();
         $str = "";
         foreach($records as $record){
             $str .= $record->name." ";
         }
         return $str;
    }

    public static function industryNameByMemberIdHtml ($member_id) {
         $records = MemberIndustry::where("member_id",$member_id)->get();
         $str = "";
         foreach($records as $record){
             $str .= "<li>".$record->name."</li>";
         }
         return $str;
    }

    public static function industryByShopId ($shop_id) {

        // industry_label が登録されていれば最優先で採用
        if (Schema::hasColumn('shop_profiles', 'industry_label')) {
            $label = trim((string) DB::table('shop_profiles')
                ->where('shop_id', $shop_id)
                ->value('industry_label'));
            if ($label !== '') {
                return collect([(object) ['name' => $label]]);
            }
        }

        $records = DB::table('industries')
                   ->join('shop_profiles', 'industries.id', '=', 'shop_profiles.industry_id')
                   ->where('shop_profiles.shop_id',$shop_id)
                   ->select('industries.name')
                   ->get();

        return $records;
    }

    public static function nearestStation ($shop_id) {
         $records = NearestStation::where("shop_id",$shop_id)->get();
         $str = "";
         foreach($records as $record){
             $str .= $record->station." ";
         }
         $res="";
         if($str!="") $res = "(最寄駅：" . $str . ")";
         return $res;
    }

    public static function nearestStationHtml ($shop_id) {
         $records = NearestStation::where("shop_id",$shop_id)->get();
         $str = "";
         foreach($records as $record){
             $str .= $record->station." ";
         }
         $res="";
         if($str!="") $res = "<li>" . $str . "</li>";
         return $res;
    }


    public static function splitAddress ($address) {

        if (preg_match('@^(.{2,3}?[都道府県])(.+?郡.+?[町村]|.+?市.+?区|.+?[市区町村])(.+)@u', $address, $matches) !== 1) {
            return [
                'state' => null,
                'city' => null,
                'other' => null
            ];
        }

        return [
            'state' => $matches[1],
            'city' => $matches[2],
            'other' => $matches[3],
        ];


    }


/*
    public static function treatmentByShopId ($shop_id) {

        $records = DB::table('shop_treatments')
                   ->where('shop_id',$shop_id)
                   ->select('treatment')
                   ->get();
        $res = array();
        foreach($recores as $key2 => $val2){
             foreach(TreatmentConsts::SUBJECT as $key => $val){
                 if ( substr($val2,0) == $val )  {
                      

                 }

             }


        }

        return $records;
    }

*/

    public  static function free_word($cond,$search) {

        // 全角スペースを半角に変換
        $spaceConversion = mb_convert_kana($search, 's');
        // 単語を半角スペースで区切り、配列にする（例："テス ト" → ["テス", "ト"]）
        $wordArraySearched = preg_split('/[\s,]+/', $spaceConversion, -1, PREG_SPLIT_NO_EMPTY);
        // 単語をループで回し、ユーザーネームと部分一致するものがあれば、$queryとして保持される
        foreach($wordArraySearched as $value) {
            $query->where($cond, 'like', '%'.$value.'%');
        }

        return $query;
    }


    public  static function is_empty( $var = null ) {
        if ( empty( $var ) && 0 !== $var && '0' !== $var ) { 
            return true;
        } else {
            return false;
        }
    }

}

