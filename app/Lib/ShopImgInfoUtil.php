<?php

namespace App\Lib;

use Illuminate\Support\Facades\Facade;
use App\Consts\ShopConsts;
use Illuminate\Support\Facades\DB;
use App\Models\Shop;
use App\Models\ShopSubImage;

class ShopImgInfoUtil extends Facade {


    public static function shopSubImg ($shop_id) {
         return ShopSubImage::where('shop_id',$shop_id)->get();
    }



}