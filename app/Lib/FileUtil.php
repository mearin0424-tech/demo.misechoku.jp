<?php

namespace App\Lib;

use Illuminate\Support\Facades\Facade;
use App\Consts\FileConsts;
use App\Consts\ShopConsts;
use App\Models\Shop;
use App\Models\Member;
use App\Models\ShopSubImage;
use App\Models\MemberImage;
use Illuminate\Support\Facades\DB;
use App\Models\WShopSubImage;

class FileUtil extends Facade {

    public function uploade($request,$name,$dir)
    {
        $file_name = $request->file($name)->getClientOriginalName();
        $request->file($name)->storeAs($dir,$file_name);
    }

    public function getOriginalFileName($request,$name)
    {

        $file_name = $request->file($name)->getClientOriginalName();
        return $file_name;
    }

    public static function getShopMainImgFilePath($shop_id,$file_name) {
       return ShopConsts::SHOP_DISP_IMG_DIR.$shop_id."/".ShopConsts::SHOP_DIR_NAMES_MAIN."/".$file_name;
   }

    public static function getShopLic1ImgFilePath($shop_id,$file_name) {

       return ShopConsts::SHOP_DISP_IMG_DIR.$shop_id."/".ShopConsts::SHOP_DIR_NAMES_MIC1."/".$file_name;

   }

    public static function getShopLic2ImgFilePath($shop_id,$file_name) {
       return ShopConsts::SHOP_DISP_IMG_DIR.$shop_id."/".ShopConsts::SHOP_DIR_NAMES_MIC2."/".$file_name;
   }

   // 店舗サブ画像
    public static function getShopSubImgFilePath2($shop_id) {
/*
       if(Shop::where('id',$shop_id)->where('approval',\ShopConsts::APPROVAL_ON)->exists()) {
           $records = ShopSubImage::where('shop_id',$shop_id)->get();
       }else{
            if(WShopSubImage::where('shop_id',$shop_id)->exists()) {
                 $records = WShopSubImage::where('shop_id',$shop_id)->get();
            }else{
                $records = ShopSubImage::where('shop_id',$shop_id)->get();
            }
       }
*/

      $records = ShopSubImage::where('shop_id',$shop_id)->get();

       $file_array = [];
       foreach($records as $record){
            $file_array[] =  ShopConsts::SHOP_DISP_IMG_DIR.$shop_id."/".ShopConsts::SHOP_DIR_NAMES_MAIN."/".$record->sub_img;
       }

       return $file_array;
   }

    public static function getShopSubImgFilePath($shop_id) {

       $records = ShopSubImage::where('shop_id',$shop_id)->orderByRaw('main_order IS NULL')->orderBy('main_order', 'asc')->get();
       
       $file_array = [];
       foreach($records as $record){
            $file_array[] =  ShopConsts::SHOP_DISP_IMG_DIR.$shop_id."/".ShopConsts::SHOP_DIR_NAMES_MAIN."/".$record->sub_img;
       }
       return $file_array;
   }

    public static function getShopSubImgFileOrder($shop_id,$sub_img) {
       return ShopSubImage::where('shop_id',$shop_id)->where('sub_img',$sub_img)->first();
    }

    public static function getShopSubImgFileName($shop_id) {
       $records = ShopSubImage::where('shop_id',$shop_id)->get();
       $file_array = [];
       foreach($records as $record){
            $file_array[] = $record->sub_img;
       }

       return $file_array;
   }

    public static function imgShopInfoCnt ($shop_id) {
        return ShopSubImage::where('shop_id',$shop_id)->count();
    }

    public static function getShopInfoImgFilePath($shop_id,$file_name) {
       return ShopConsts::SHOP_DISP_IMG_DIR.$shop_id."/".ShopConsts::SHOP_DIR_NAMES_MAIN."/".$file_name;
    }

    public static function getMemberMainImgFilePath($member_id,$file_name) {
       return \FrontConsts::MEMBER_DISP_IMG_DIR.$member_id."/".$file_name;
    }

    public static function getMemberCertFrontImgFilePath($member_id) {
        $records = DB::table('member_images')->where('member_id',$member_id)->
                   where('front_and_back',\FrontConsts::FRONT_IMG)->
                   orderBy('updated_at','desc')->limit(1)->select('img')->get();
       if(empty($records[0])) return "";
       return \FrontConsts::MEMBER_DISP_IMG_DIR.$member_id."/".$records[0]->img;

   }

    public static function getMemberCertBackImgFilePath($member_id) {
        $records = DB::table('member_images')->where('member_id',$member_id)->
                   where('front_and_back',\FrontConsts::BACK_IMG)->
                   orderBy('updated_at','desc')->limit(1)->select('img')->get();
       if(empty($records[0])) return "";
       return \FrontConsts::MEMBER_DISP_IMG_DIR.$member_id."/".$records[0]->img;

   }

   public static function getMemberCertFrontImgFileName($member_id) {
        $records = DB::table('member_images')->where('member_id',$member_id)->
                   where('front_and_back',\FrontConsts::FRONT_IMG)->
                   orderBy('updated_at','desc')->limit(1)->select('img')->get();
       if(empty($records[0])) return "";
       return $records[0]->img;

   }

    public static function getMemberCertBackImgFileName($member_id) {
        $records = DB::table('member_images')->where('member_id',$member_id)->
                   where('front_and_back',\FrontConsts::BACK_IMG)->
                   orderBy('updated_at','desc')->limit(1)->select('img')->get();
       if(empty($records[0])) return "";
       return $records[0]->img;

   }

   // メンバーサブ画像
    public static function getMemberSubImgFilePath($member_id) {

       $records = MemberImage::where('member_id',$member_id)->whereIn('type',[\FrontConsts::IDENTITY_5,\FrontConsts::IDENTITY_99])
                  ->orderByRaw('main_order IS NULL')->orderBy('main_order', 'asc')
                  ->get();
       $file_array = [];
       foreach($records as $record){
            $file_array[] =  \FrontConsts::MEMBER_DISP_IMG_DIR.$member_id."/".$record->img;
       }
       return $file_array;
   }

   // メンバーサブ画像名
    public static function getMemberSubImgFileName($member_id) {
       $records = MemberImage::where('member_id',$member_id)->where('type',\FrontConsts::IDENTITY_99)->get();
       $file_array = [];
       foreach($records as $record){
            $file_array[] = $record->img;
       }

       return $file_array;
   }


   // メンバーサブ画像
    public static function getMemberAvatarmgFilePath($member_id) {
       $records = MemberImage::where('member_id',$member_id)->where('type',\FrontConsts::IDENTITY_5)->get();
       $file_array = [];
       foreach($records as $record){
            $file_array[] =  \FrontConsts::MEMBER_DISP_IMG_DIR.$member_id."/".$record->img;
       }

       return $file_array;
   }

    public static function isReviewed($member_id,$img) {

       //$records = MemberImage::where('member_id',$member_id)->where('img',$img)->where('type',\FrontConsts::IDENTITY_99)->select('status')->get();

       $records = Member::where('id',$member_id)->get();
       if($records[0]->approval==\FrontConsts::IDENTITY_99) return false;
       return true;

    }


   // アバター画像名
    public static function getMemberAvatarImgFileName($member_id) {
       $records = MemberImage::where('member_id',$member_id)->where('type',\FrontConsts::IDENTITY_5)->get();
       $file_array = [];
       foreach($records as $record){
            $file_array[] = $record->img;
       }

       return $file_array;
   }



    public static function getMemberSubImgFilePath2($member_id) {
       $records = MemberImage::where('member_id',$member_id)->whereIn('type',[\FrontConsts::IDENTITY_5,\FrontConsts::IDENTITY_99])->where('is_main',1)->orderBy('main_order')->get();
       $file_array = [];
       foreach($records as $record){
            $file_array[] =  \FrontConsts::MEMBER_DISP_IMG_DIR.$member_id."/".$record->img;
       }
       return $file_array;
   }

    // メイン画像かどうかをチェック
    public static function isMainImage($member_id, $index)
    {
        $image = MemberImage::where('member_id', $member_id)
            ->where('type', \FrontConsts::IDENTITY_99)
            ->where('img', \FileUtil::getMemberSubImgFileName($member_id)[$index])
            ->where('type', \FrontConsts::IDENTITY_99)

            ->first();

        return $image && $image->is_main;
    }

    // メイン画像の順番を取得
    public static function getMainImageOrder($member_id, $index)
    {
        $image = MemberImage::where('member_id', $member_id)
            ->where('type', \FrontConsts::IDENTITY_99)
            ->where('img', \FileUtil::getMemberSubImgFileName($member_id)[$index])
            ->first();

        return $image ? $image->main_order : null;
    }

    // メイン画像かどうかをチェック
    public static function isMainImageShop($shop_id, $index)
    {
        $image = ShopSubImage::where('shop_id', $shop_id)
            ->where('type', \FrontConsts::IDENTITY_99)
            ->where('sub_img', \FileUtil::getShopSubImgFileName($shop_id)[$index])
            ->first();

        return $image && $image->is_main;
    }

    // メイン画像の順番を取得
    public static function getMainImageOrderShop($shop_id, $index)
    {
        $image = ShopSubImage::where('shop_id', $shop_id)
            ->where('type', \FrontConsts::IDENTITY_99)
            ->where('sub_img', \FileUtil::getShopSubImgFileName($shop_id)[$index])
            ->first();

        return $image ? $image->main_order : null;
    }


    public static function getMemberImgFilePath($member_id) {

       $records = MemberImage::where('member_id',$member_id)->whereIn('type',[ \FrontConsts::IDENTITY_5, \FrontConsts::IDENTITY_99])
                  ->orderByRaw('main_order IS NULL')->orderBy('main_order', 'asc')->get();
       
       $file_array = [];
       foreach($records as $record){
            $file_array[] =  \FrontConsts::MEMBER_DISP_IMG_DIR.$member_id."/".$record->img;
       }
       return $file_array;
   }

    public static function getMemberImgFileOrder($member_id,$img) {
       return MemberImage::where('member_id',$member_id)->where('img',$img)->whereIn('type',[\FrontConsts::IDENTITY_5,\FrontConsts::IDENTITY_99])->first();
    }


    public static function getMemberSubImgFilePath3($member_id) {

       $records = MemberImage::where('member_id',$member_id)->whereIn('type',[\FrontConsts::IDENTITY_5,\FrontConsts::IDENTITY_99])
                  ->where('status','1')
                  ->orderByRaw('main_order IS NULL')->orderBy('main_order', 'asc')
                  ->get();
       $file_array = [];
       foreach($records as $record){
            $file_array[] =  \FrontConsts::MEMBER_DISP_IMG_DIR.$member_id."/".$record->img;
       }

       return $file_array;
   }


   // メンバーサブ画像名
    public static function getMemberSubImgFileName3($member_id) {

       $records = MemberImage::where('member_id',$member_id)->whereIn('type',[\FrontConsts::IDENTITY_5,\FrontConsts::IDENTITY_99])->get();
       $file_array = [];
       foreach($records as $record){
            $file_array[] = $record->img;
       }

       return $file_array;
   }

    public static function getMemberFileOrder($member_id,$sub_img) {
       return MemberImage::where('member_id',$member_id)->where('img',$sub_img)->first();
    }

    public static function getMemberImgFileName($member_id) {
//       $records = MemberImage::where('member_id',$member_id)->get();
       $records = MemberImage::where('member_id',$member_id)->whereIn('type',[\FrontConsts::IDENTITY_5,\FrontConsts::IDENTITY_99])
                  ->orderByRaw('main_order IS NULL')->orderBy('main_order', 'asc')
                  ->get();

       $file_array = [];
       foreach($records as $record){
            $file_array[] = $record->img;
       }

       return $file_array;
   }

    public static function getMemberFileOrderPath($member_id,$img) {
        return \FrontConsts::MEMBER_DISP_IMG_DIR.$member_id."/".$img;
   }
}





