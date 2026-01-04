<?php

namespace App\Lib;

use Illuminate\Support\Facades\Facade;
use App\Consts\NewsConsts;
use App\Consts\TreatmentConsts;
use App\Repositories\Master\ShopInterface as MyRepository;
use Illuminate\Support\Facades\DB;
use App\Models\Shop;
use App\Models\ShopTreatment;
use App\Models\Member;

class NewsInfoUtil extends Facade {

    public static function type ($type) {

        foreach (NewsConsts::Type as $key => $val ) {
            if($type==$val) {
                return $key;
            }
        }
        return "";
    }

    public static function release ($type) {

        foreach (NewsConsts::RELEASE as $key => $val ) {
            if($type==$val) {
                return $key;
            }
        }
        return "";
    }
}
