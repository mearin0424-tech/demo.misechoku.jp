<?php

namespace App\Lib;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;
use App\Models\Shop;
use App\Models\Member;
use App\Models\Industry;
use App\Models\ShopIndustry;
use App\Models\MemberIndustry;

class IndustryInfoUtil extends Facade {


    public static function getAll () {
        return DB::table('industries')->orderBy('updated_at', 'asc')->get();
    }


    public static function getByShopIdw ($shop_id) {
        return ShopIndustry::where("shop_id",$shop_id)->select('industry_id')->get();
    }


    public static function getByShopId ($shop_id) {

        $records = DB::table('industries')
                   ->join('shop_industries', 'industries.id', '=', 'shop_industries.industry_id')
                   ->where('shop_industries.shop_id',$shop_id)
                   ->select('industries.*','shop_industries.industry_id')
                   ->get();
        return  $records;

    }

    public static function getByMemberId ($member_id) {

        $records = DB::table('industries')
                   ->join('member_industries', 'industries.id', '=', 'member_industries.industry_id')
                   ->where('member_industries.member_id',$member_id)
                   ->select('industries.*','member_industries.industry_id')
                   ->get();
        return  $records;

    }

}
